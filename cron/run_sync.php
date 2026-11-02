<?php
declare(strict_types=1);

/**
 * Script CLI para execução via Cron Job Noturno
 * Uso no crontab do Linux:
 * 0 2 * * * php /caminho/para/Presidencia/cron/run_sync.php >> /caminho/para/Presidencia/storage/logs/cron.log 2>&1
 */

require_once dirname(__DIR__) . '/app/bootstrap.php';

use App\Repositories\Database;
use App\Integrations\BcbClient;
use App\Integrations\TransparenciaClient;
use App\Services\RadarService;

$logFile = dirname(__DIR__) . '/storage/logs/cron.log';
$logCron = function(string $msg) use ($logFile): void {
    $formatted = "[" . date('Y-m-d H:i:s') . "] {$msg}\n";
    echo $formatted;
    file_put_contents($logFile, $formatted, FILE_APPEND | LOCK_EX);
};

$logCron("=== Início da rotina automatizada de sincronização (Cron) ===");

try {
    $pdo = Database::getConnection();

    // 1. Atualizar cache e índices do IPCA junto ao Banco Central do Brasil
    $logCron("1. Consultando série histórica do IPCA junto ao Banco Central...");
    $bcb = new BcbClient();
    $serie = $bcb->getSerieIpca();
    $logCron("   -> " . count($serie) . " meses da série IPCA verificados com sucesso.");

    // 2. Verificar conectividade com API do Portal da Transparência
    $logCron("2. Verificando conectividade da API do Portal da Transparência...");
    $transparencia = new TransparenciaClient();
    if ($transparencia->hasKey()) {
        $logCron("   -> Chave de API configurada. Consultando últimas despesas...");
        $res = $transparencia->getDespesas(1);
        if ($res['success']) {
            $logCron("   -> Conexão com a API estabelecida com sucesso.");
        } else {
            $logCron("   -> Aviso: " . ($res['error'] ?? 'Falha na resposta da API'));
        }
    } else {
        $logCron("   -> Nenhuma chave de API configurada no .env (modo CSV/Dados Abertos ativo).");
    }

    // 3. Executar o Radar Investigativo de Anomalias
    $logCron("3. Executando bateria de auditoria estatística do Radar Investigativo...");
    $radar = new RadarService();
    $radarResult = $radar->executarAuditoria();
    $logCron("   -> Auditoria concluída: {$radarResult['alertas_novos']} novos alertas de triagem gerados.");

    // 4. Registrar tarefa na tabela tarefas_importacao
    $stmt = $pdo->prepare("
        INSERT INTO tarefas_importacao (tipo_tarefa, status, agendada_para, executada_em, resultado_resumo)
        VALUES ('CRON_SINCRONIZACAO_GERAL', 'CONCLUIDA', ?, ?, ?)
    ");
    $stmt->execute([
        date('Y-m-d H:i:s'),
        date('Y-m-d H:i:s'),
        "Sincronização executada com sucesso. Alertas novos gerados: {$radarResult['alertas_novos']}."
    ]);

    $logCron("=== Rotina automatizada finalizada com êxito ===");
} catch (\Throwable $e) {
    $logCron("❌ ERRO CRÍTICO no cron: " . $e->getMessage() . " em " . $e->getFile() . ":" . $e->getLine());
    exit(1);
}

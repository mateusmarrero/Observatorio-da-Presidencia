# Configuração de Automação e Cron Jobs

## 1. Visão Geral das Rotinas Automatizadas

O sistema inclui scripts executáveis via linha de comando (CLI) preparados para agendamento periódico no `crontab` de servidores Linux:

- **`cron/run_sync.php`:**
  1. Atualiza e armazena em cache os últimos índices de inflação oficial do IPCA (SGS 433 do Banco Central).
  2. Consulta APIs governamentais ativas (se chave de API estiver presente no `.env`).
  3. Executa a bateria de regras do **Radar Investigativo de Anomalias**, identificando novos outliers ou concentrações atípicas.
  4. Registra log detalhado em `storage/logs/cron.log` e insere o relatório na tabela `tarefas_importacao`.

---

## 2. Configuração no Crontab do Linux

Para agendar a execução noturna automática (por exemplo, todos os dias às 02:00 da madrugada), edite a tabela de agendamento do usuário web:

```bash
crontab -e -u www-data
```

Adicione a linha:

```cron
# Execução diária da rotina de sincronização e auditoria do Observatório
0 2 * * * /usr/bin/php /var/www/presidencia/cron/run_sync.php >> /var/www/presidencia/storage/logs/cron.log 2>&1
```

---

## 3. Teste Manual da Rotina

É possível testar a execução da rotina a qualquer momento via terminal:

```bash
php cron/run_sync.php
```

Saída esperada no terminal e no arquivo `storage/logs/cron.log`:
```
[2026-11-02 12:27:47] === Início da rotina automatizada de sincronização (Cron) ===
[2026-11-02 12:27:47] 1. Consultando série histórica do IPCA junto ao Banco Central...
[2026-11-02 12:27:47]    -> 285 meses da série IPCA verificados com sucesso.
[2026-11-02 12:27:47] 2. Verificando conectividade da API do Portal da Transparência...
[2026-11-02 12:27:47] 3. Executando bateria de auditoria estatística do Radar Investigativo...
[2026-11-02 12:27:47]    -> Auditoria concluída: 0 novos alertas de triagem gerados.
[2026-11-02 12:27:47] === Rotina automatizada finalizada com êxito ===
```

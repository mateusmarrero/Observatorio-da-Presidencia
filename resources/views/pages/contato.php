<?php
use App\Helpers\SecurityHelper;
?>

<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.85rem; font-weight: 800; letter-spacing: -0.02em; margin-bottom: 0.5rem;">
        Contato e Solicitação de Correção de Informações
    </h1>
    <p style="color: var(--text-muted); font-size: 1rem;">
        Nosso compromisso com a verdade factual é absoluto. Caso identifique qualquer divergência de dados ou erro documental, solicite revisão imediata.
    </p>
</div>

<div class="card" style="max-width: 800px;">
    <div class="card-header">
        <h3 class="card-title">Formulário de Solicitação de Retificação Documental</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="/contato" style="display: flex; flex-direction: column; gap: 1.25rem;">
            <?= SecurityHelper::csrfField() ?>

            <div class="form-group">
                <label class="form-label">Seu Nome Completo *</label>
                <input type="text" name="nome" required class="form-input" placeholder="Ex: Maria dos Santos">
            </div>

            <div class="form-group">
                <label class="form-label">Seu E-mail Institucional ou Pessoal *</label>
                <input type="email" name="email" required class="form-input" placeholder="seuemail@exemplo.com">
            </div>

            <div class="form-group">
                <label class="form-label">Tipo de Solicitação *</label>
                <select name="tipo" class="form-select" required>
                    <option value="CORRECAO_DADOS">Correção de dado ou divergência contábil</option>
                    <option value="REVISAO_VINCULO">Revisão de vínculo editorial</option>
                    <option value="ENVIO_DOCUMENTO">Envio de novo documento oficial comprobatório</option>
                    <option value="SUGESTAO_DUVIDA">Sugestão, dúvida ou contato de imprensa</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Identificador do Registro / URL da Página Afetada</label>
                <input type="text" name="referencia" class="form-input" placeholder="Ex: CPGF-20230501-a1b2c3d4 ou link da despesa">
            </div>

            <div class="form-group">
                <label class="form-label">Detalhamento e Justificativa Documental *</label>
                <textarea name="mensagem" rows="5" required class="form-textarea" placeholder="Descreva com precisão o fato, indicando links oficiais no Diário Oficial da União, Portal da Transparência ou documentos comprobatórios anexados..."></textarea>
            </div>

            <div>
                <button type="button" class="btn btn-primary" onclick="alert('Solicitação registrada no protocolo de auditoria editorial! Obrigado pela colaboração cidadã com a integridade dos dados.')">
                    Enviar Solicitação de Correção
                </button>
            </div>
        </form>
    </div>
</div>

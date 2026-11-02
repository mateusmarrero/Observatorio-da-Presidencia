# Modelo de Dados do Observatório da Presidência

## 1. Visão Geral do Banco de Dados

- **SGBD:** MySQL 8.0+
- **Charset:** `utf8mb4`
- **Collation:** `utf8mb4_unicode_ci`
- **Engine:** InnoDB com suporte a integridade referencial estrita e transações ACID.
- **Precisão Financeira:** Todos os valores monetários utilizam `DECIMAL(15,2)` para prevenir erros de arredondamento em ponto flutuante.

---

## 2. Dicionário das 18 Tabelas Principais

| Tabela | Finalidade Principal |
| :--- | :--- |
| `pessoas` | Cadastro das autoridades monitoradas (Lula, Janja), identificação pública e biografia oficial. |
| `mandatos` | Períodos institucionais e exercícios de mandato presidencial ou representação como primeira-dama. |
| `fontes_dados` | Registro das origens oficiais governamentais (CGU, Casa Civil, GSI, BCB). |
| `importacoes` | Histórico e auditoria de cada lote de arquivo ou requisição de API processada. |
| `categorias_despesas` | Taxonomia padronizada de gastos (Hospedagem, Alimentação, Locação, Combustíveis, etc.). |
| `fornecedores` | Cadastro público de pessoas jurídicas (CNPJ) e físicas (CPF) favorecidas. |
| `despesas` | Lançamentos contábeis de liquidações orçamentárias e transações de cartões corporativos (CPGF). |
| `viagens` | Missões oficiais, viagens de Estado, cúpulas e deslocamentos institucionais via SCDP. |
| `viagem_participantes`| Membros identificados em comitivas, valores de diárias e passagens recebidas. |
| `vinculos_despesas` | **Coração metodológico do portal:** classificação auditada de vínculo (`DIRETA`, `AGENDA_OFICIAL`, `COMITIVA_VIAGEM`, `INSTITUCIONAL`, `NAO_IDENTIFICADO`) com grau de certeza e justificativa. |
| `contratos` | Instrumentos contratuais firmados, licitações, aditivos e vigências. |
| `documentos` | Biblioteca de arquivos primários com hash SHA-256 e links originais governamentais. |
| `alertas` | Ocorrências apontadas automaticamente pelo Radar Investigativo para análise técnica. |
| `investigacoes` | Dossiês internos da redação com separação de fatos comprovados, hipóteses e conclusões. |
| `historico_alteracoes`| Trilha de auditoria imutável de todas as alterações críticas, revisões de vínculo e logins. |
| `usuarios` | Contas de acesso administrativo ao portal com RBAC (`admin`, `editor`, `analista`). |
| `configuracoes` | Parâmetros do sistema (mês-base do IPCA, limiar de alerta atípico, metadados). |
| `tarefas_importacao` | Fila de agendamento e execuções de rotinas noturnas e cron jobs. |

---

## 3. Integridade e Deduplicação

Cada despesa possui um campo `hash_registro` calculado por:
```
SHA-256(FONTE_ID + "|" + DATA + "|" + VALOR_NOMINAL + "|" + CNPJ_CPF_FAVORECIDO + "|" + PORTADOR + "|" + DESCRICAO)
```
Esse hash possui restrição `UNIQUE`. Caso o mesmo registro seja importado novamente através de um novo CSV ou chamada de API, o motor detecta a colisão, incrementa o contador de duplicatas e preserva o registro original com seu histórico de vínculos.

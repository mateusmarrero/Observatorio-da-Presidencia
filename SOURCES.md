# Inventário de Fontes Oficiais Governamentais

## 1. Princípio de Exclusividade de Dados Primários

O **Observatório da Presidência** prioriza exclusivamente fontes governamentais autênticas para seus dados primários, garantindo total rastreabilidade, veracidade jurídica e auditabilidade dos lançamentos contábeis.

---

## 2. Relação Detalhada das Fontes Monitoradas

### 2.1 Portal da Transparência do Governo Federal (CGU)
- **Portal Principal:** `https://portaldatransparencia.gov.br/`
- **API REST Oficial:** `https://api.portaldatransparencia.gov.br/api-de-dados`
- **Repositório de Dados Abertos (Download Diário/Mensal):** `https://portaldatransparencia.gov.br/download-de-dados/`
- **Dados Coletados:**
  - Extratos do Cartão de Pagamento do Governo Federal (CPGF).
  - Execução Orçamentária e Financeira (Empenho, Liquidação e Pagamento).
  - Processos de Viagens Oficiais via SCDP (Diárias, Passagens e Comitivas).
  - Contratos Administrativos e Compras Públicas.

### 2.2 Casa Civil da Presidência da República
- **Painel Oficial de Cartões:** `https://www.gov.br/casacivil/pt-br/acesso-a-informacao/informacoes-classificadas/despesas-com-cartao-de-pagamento`
- **Receitas e Despesas:** `https://www.gov.br/casacivil/pt-br/acesso-a-informacao/receitas-e-despesas/receitas-e-despesas`
- **Dados Coletados:**
  - Planilhas e relatórios oficiais desclassificados de despesas com cartões corporativos de mandatos presidenciais históricos (2003 a 2010 e subsequentes).

### 2.3 Gabinete de Segurança Institucional (GSI / PR)
- **Painel de Diárias e Passagens:** `https://www.gov.br/gsi/pt-br/acesso-a-informacao/receitas-e-despesas/despesas-com-diarias-e-passagens`
- **Dados Coletados:**
  - Custos operacionais e relatórios de deslocamento de agentes de escolta e segurança institucional da Presidência da República.

### 2.4 Banco Central do Brasil (SGS)
- **API Série Temporal 433:** `https://api.bcb.gov.br/dados/serie/bcdata.sgs.433/dados?formato=json`
- **Finalidade:**
  - Variação mensal oficial do IPCA (Índice Nacional de Preços ao Consumidor Amplo - IBGE) utilizada para correção de valores nominais históricos e deflação justa entre períodos de mandatos.

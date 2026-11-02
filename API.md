# Documentação de APIs do Observatório da Presidência

## 1. Endpoints Públicos

### 1.1 Métricas Consolidadas do Dashboard
- **Rota:** `GET /api/metrics`
- **Descrição:** Retorna os totais consolidados para renderização de gráficos e cartões estatísticos.
- **Formato:** JSON (UTF-8)
- **Resposta Exemplo:**
```json
{
  "totais": {
    "total_despesas": 11,
    "valor_total_nominal": 308810.50,
    "valor_total_corrigido": 425310.85
  },
  "vinculos": [
    {
      "vinculo": "DIRETA",
      "qtd": 4,
      "total_nominal": 30020.00,
      "total_corrigido": 64510.20
    },
    {
      "vinculo": "INSTITUCIONAL",
      "qtd": 7,
      "total_nominal": 278790.50,
      "total_corrigido": 360800.65
    }
  ],
  "evolucao_anual": [
    { "ano": "2003", "qtd": 1, "nominal": 25000.00, "corrigido": 94322.90 },
    { "ano": "2024", "qtd": 4, "nominal": 204000.00, "corrigido": 204000.00 }
  ],
  "viagens": {
    "total_viagens": 6,
    "total_diarias": 9900.00,
    "total_passagens": 18500.00,
    "viagens_internacionais": 4,
    "viagens_nacionais": 2
  },
  "ultima_atualizacao": "2026-11-02 12:28:29"
}
```

---

### 1.2 Exportação de Dados em CSV
- **Rota:** `GET /despesas/export`
- **Descrição:** Gera download imediato de arquivo CSV oficial com codificação UTF-8 (com BOM) e delimitador `;`.
- **Parâmetros de Filtro Suportados:**
  - `q`: termo de busca textual em favorecido, portador ou documento.
  - `vinculo`: filtrar por `DIRETA`, `AGENDA_OFICIAL`, `COMITIVA_VIAGEM`, `INSTITUCIONAL`, `NAO_IDENTIFICADO`.
  - `categoria`: ID da categoria de despesa.
  - `fase`: fase contábil (`PAGAMENTO`, `CPGF`, `LIQUIDACAO`, `EMPENHO`).
  - `data_inicio`: data em formato `YYYY-MM-DD`.
  - `data_fim`: data em formato `YYYY-MM-DD`.

---

## 2. Conectores Governamentais Consumidos

### 2.1 API do Portal da Transparência (CGU)
- **Base:** `https://api.portaldatransparencia.gov.br/api-de-dados`
- **Autenticação:** Header `chave-api-dados: <SUA_CHAVE>`
- **Endpoints Mapeados:**
  - `/cartoes`: Extratos do Cartão de Pagamento do Governo Federal (CPGF).
  - `/viagens`: Processos de viagens SCDP da Presidência da República.
  - `/despesas`: Execução de pagamentos orçamentários.
  - `/contratos`: Contratos administrativos registrados no Comprasnet.

### 2.2 API do Banco Central do Brasil (SGS)
- **Base:** `https://api.bcb.gov.br/dados/serie/bcdata.sgs.433/dados`
- **Autenticação:** Acesso público aberto, sem chave.
- **Série Consumida:** 433 (Variação mensal do IPCA em %).

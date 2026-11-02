# Observatório da Presidência

> **O dinheiro é público. A informação também deve ser.**

O **Observatório da Presidência** é uma plataforma web independente de transparência pública, jornalismo de dados e fiscalização contábil desenvolvida em **PHP 8.3** e **MySQL 8**. Seu propósito é monitorar historicamente despesas, viagens, cartões de pagamento (CPGF), diárias, contratos e fornecedores relacionados aos mandatos de:

- **Luiz Inácio Lula da Silva** (Mandatos presidenciais de 2003–2006, 2007–2010 e 2023–atualidade).
- **Rosângela da Silva (Janja)** (Atividades públicas e de representação como primeira-dama desde 2023).

---

## 🏛️ Princípio Editorial Fundamental

O portal estabelece e fiscaliza a separação rigorosa da natureza de cada gasto:
1. **DIRETA:** despesas comprovadamente individuais da autoridade.
2. **AGENDA_OFICIAL:** despesas associadas a compromissos e eventos públicos oficiais específicos.
3. **COMITIVA_VIAGEM:** custos de transporte, segurança e apoio logístico de comitivas e delegações.
4. **INSTITUCIONAL:** despesas de manutenção predial e custeio administrativo geral dos órgãos da Presidência da República (Casa Civil, Secretaria de Administração, GSI).
5. **NAO_IDENTIFICADO:** despesas cujos dados abertos governamentais não fornecem detalhamento individual suficiente.

> ⚠️ **Regra Inegociável:** Nunca atribuir despesas institucionais gerais do Estado brasileiro à pessoa física do governante ou da primeira-dama sem documento primário inequívoco que sustente o vínculo.

---

## 🚀 Tecnologias Empregadas

- **Linguagem:** PHP 8.3+ (estrito com tipagem forte e PSR-4).
- **Banco de Dados:** MySQL 8 (UTF8MB4, Decimal para moedas, chaves estrangeiras e índices otimizados).
- **Frontend:** HTML5 semântico, CSS3 moderno com Design System customizado (temas claro e escuro, mobile-first, foco em acessibilidade WCAG 2.2 AA).
- **Interatividade:** JavaScript Vanilla (puro, sem frameworks pesados como React ou Vue em produção).
- **Visualização de Dados:** Chart.js 4.4 integrado nativamente.
- **Inflação & Correção Monetária:** API oficial do Banco Central do Brasil (Série SGS 433 do IPCA).
- **Automação:** Cron Jobs agendados para sincronização e detecção de anomalias estatísticas.

---

## 📑 22 Páginas Públicas Implementadas

1. **Página Inicial (`/`):** Visão geral, estatísticas consolidadas e compromissos editoriais.
2. **Dashboard Geral (`/dashboard`):** Métricas de despesas nominais e corrigidas, despesas por vínculo e gráficos analíticos.
3. **Perfil de Lula (`/perfil/lula`):** Linha do tempo dos 3 mandatos, histórico de viagens e despesas nominais comprovadas.
4. **Perfil de Janja (`/perfil/janja`):** Histórico de representações, missões internacionais e enquadramento institucional desde 2023.
5. **Despesas Públicas (`/despesas`):** Tabela pesquisável com filtros multicritério, paginação e exportação CSV.
6. **Cartões de Pagamento (`/cartoes`):** Módulo especializado no Cartão de Pagamento do Governo Federal (CPGF).
7. **Viagens Nacionais (`/viagens/nacionais`):** Deslocamentos pelo território nacional via SCDP.
8. **Viagens Internacionais (`/viagens/internacionais`):** Missões diplomáticas e cúpulas de Estado no exterior.
9. **Diárias e Passagens (`/diarias-passagens`):** Custos unitários de deslocamento e indenização.
10. **Comitivas (`/comitivas`):** Participantes públicos identificados em delegações oficiais.
11. **Contratos (`/contratos`):** Licitações, compras públicas, vigências e valores contratados.
12. **Fornecedores (`/fornecedores`):** Empresas contratadas, CNPJs, localização e faturamento acumulado.
13. **Documentos Oficiais (`/documentos`):** Repositório com validação de integridade por hash SHA-256.
14. **Radar Investigativo (`/radar`):** Motor de triagem estatística de outliers e concentrações atípicas.
15. **Histórico Financeiro (`/historico-financeiro`):** Série temporal completa por exercício fiscal.
16. **Comparação Histórica (`/comparacao-historica`):** Comparação entre mandatos com correção oficial pelo IPCA.
17. **Metodologia (`/metodologia`):** Critérios técnicos, regras de imputação e cálculos matemáticos.
18. **Fontes de Dados (`/fontes`):** Inventário completo das bases oficiais integradas.
19. **Sobre o Projeto (`/sobre`):** Origem, objetivos cívicos e independência institucional.
20. **Política de Privacidade (`/privacidade`):** Conformidade com a LGPD e Lei de Acesso à Informação.
21. **Termos de Uso (`/termos`):** Licença aberta para jornalistas, pesquisadores e cidadãos.
22. **Contato e Correção (`/contato`):** Canal formal para retificação de dados e apontamento de divergências.

---

## 🔒 Área Administrativa e Central de Investigação

Acesso em `/admin/login` com controle de permissões por perfil (RBAC: `admin`, `editor`, `analista`):
- **Monitor de Importações:** Upload manual de CSVs e monitoramento de lotes.
- **Revisão de Vínculos:** Classificação auditada de despesas com justificativa obrigatória.
- **Despacho de Alertas do Radar:** Triagem técnica de potenciais anomalias.
- **Central de Investigação:** Abertura de dossiês com separação estrita de fatos, hipóteses e conclusões.
- **Trilha de Auditoria:** Registro imutável de todas as ações no sistema.

---

## 📦 Como Executar

Consulte o arquivo [INSTALL.md](INSTALL.md) para instruções detalhadas de instalação e configuração em ambientes de desenvolvimento ou produção Linux/Apache/Nginx.

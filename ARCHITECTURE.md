# Arquitetura de Software do Observatório da Presidência

## 1. Visão Geral da Arquitetura

O sistema adota o padrão **Clean MVC com Repository e Service Layer**, projetado com foco em simplicidade, segurança da informação, alta performance e zero dependência de Node.js em ambiente de produção.

```
                  ┌──────────────────────┐
                  │    HTTP Client       │
                  │   (Navegador/API)    │
                  └──────────┬───────────┘
                             │
                      [ public/index.php ]
                             │
               ┌─────────────┴─────────────┐
               ▼                           ▼
      [ Middleware Pipeline ]     [ Route Dispatcher ]
       - SecurityHeaders           - Public Routes (22)
       - RateLimit (DDoS)          - Admin Routes (RBAC)
       - SecureSession & CSRF
               │
               ▼
     ┌───────────────────┐
     │   Controllers     │
     │  (Public / Admin) │
     └─────────┬─────────┘
               │
      ┌────────┴────────┐
      ▼                 ▼
[ Services Layer ]  [ Repositories Layer ]
 - ImportService     - DespesaRepository
 - RadarService      - ViagemRepository
 - AuthService       - FornecedorRepository
 - BcbClient (IPCA)  - Database (PDO Wrapper)
      │                 │
      ▼                 ▼
[ Storage & Cache ] [ MySQL 8 Database ]
```

---

## 2. Camadas do Sistema

### 2.1 Camada de Roteamento e Entrada (`public/index.php` e `app/Router.php`)
- Ponto único de entrada (Front Controller pattern).
- Resolução rápida de rotas via expressões regulares compiladas.
- Injeção de middlewares antes da execução de handlers.

### 2.2 Camada de Middlewares (`app/Middleware/`)
- **`SecurityHeadersMiddleware`:** Injeta cabeçalhos rígidos (`X-Frame-Options`, `Content-Security-Policy`, `X-Content-Type-Options`).
- **`RateLimitMiddleware`:** Limitação de requisições por IP baseada em janelas de tempo deslizantes para mitigar scraping abusivo ou negação de serviço.
- **`AuthMiddleware`:** Validação de sessões de analistas e controle de perfis com RBAC (`admin`, `editor`, `analista`).

### 2.3 Camada de Serviços (`app/Services/`)
- **`ImportService`:** Validação, normalização e carga memory-safe de grandes arquivos CSV, cálculo automático de deflação pelo IPCA e geração de chaves hash SHA-256 para deduplicação.
- **`RadarService`:** Motor de auditoria estatística para detecção de anomalias (outliers, concentração em 24h, fornecedores com alta recorrência).
- **`AuthService`:** Autenticação segura com `password_hash` (Bcrypt/Argon2id), proteção contra session fixation e registro em auditoria.

### 2.4 Camada de Integrações Oficiais (`app/Integrations/`)
- **`BcbClient`:** Conector REST oficial com o Banco Central do Brasil para obter a série histórica 433 do IPCA e calcular fatores acumulados de correção monetária.
- **`TransparenciaClient`:** Cliente HTTP para a API de Dados Abertos da CGU com suporte a cabeçalhos de autenticação, controle de rate limit e tratamento de erros 401/403/429.
- **`CsvParser`:** Parser com streaming linha a linha, conversão automática de codificação ISO-8859-1 para UTF-8 e detecção dinâmica de delimitadores (`;` ou `,`).

### 2.5 Camada de Repositórios (`app/Repositories/`)
- Abstração completa de persistência sobre o PDO.
- Todas as consultas executadas com *Prepared Statements* parametrizados contra SQL Injection.
- Índices especializados e paginação com `LIMIT` e `OFFSET` no servidor.

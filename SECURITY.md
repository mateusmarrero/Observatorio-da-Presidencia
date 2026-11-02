# Política de Segurança da Informação e Hardening

## 1. Diretrizes de Desenvolvimento Seguro

O **Observatório da Presidência** foi arquitetado seguindo as melhores práticas do OWASP Top 10 e padrões rigorosos de segurança corporativa em PHP:

### 1.1 Prevenção contra SQL Injection
- Todas as consultas ao banco de dados utilizam exclusivamente **PHP PDO com Prepared Statements**.
- O parâmetro `PDO::ATTR_EMULATE_PREPARES` está desativado (`false`), forçando a preparação real no servidor MySQL.
- Nenhuma variável de entrada de usuário é concatenada diretamente em comandos SQL.

### 1.2 Proteção contra Cross-Site Scripting (XSS)
- Todo output textual nas views passa por sanitização com `htmlspecialchars($dado, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')` através do helper `SecurityHelper::escape()`.
- O cabeçalho de segurança `Content-Security-Policy` restringe fontes de scripts autorizadas.

### 1.3 Proteção contra Cross-Site Request Forgery (CSRF)
- Todas as rotas `POST` (login, upload de CSV, alteração de vínculos, despacho de alertas, criação de investigações) exigem e validam um token criptográfico gerado com `random_bytes(32)`.
- A validação utiliza comparação em tempo constante (`hash_equals`) para prevenir ataques de temporização (*timing attacks*).

### 1.4 Gerenciamento Seguro de Sessões e Autenticação
- Armazenamento de senhas via `password_hash()` com algoritmo Bcrypt (cost 12) ou Argon2id.
- Regeneração forçada de ID de sessão (`session_regenerate_id(true)`) logo após login bem-sucedido contra *Session Fixation*.
- Bloqueio automático de conta por 15 minutos após 5 tentativas consecutivas de senha incorreta.
- Cookies de sessão configurados estritamente com `HttpOnly = true`, `SameSite = Lax` e `Secure = true` em produção.

### 1.5 Rate Limiting e Prevenção de DDoS
- O middleware `RateLimitMiddleware` monitora as requisições por endereço IP.
- Limite padrão configurado para 120 requisições por minuto no portal público, respondendo com HTTP `429 Too Many Requests` e cabeçalho `Retry-After` em caso de abuso.

### 1.6 Proteção de Diretórios e Armazenamento
- O `DocumentRoot` do servidor aponta estritamente para a pasta `public/`.
- Os diretórios `storage/`, `config/`, `app/` e `database/` permanecem fora da raiz acessível via web, impedindo download direto de arquivos confidenciais ou bancos de dados.

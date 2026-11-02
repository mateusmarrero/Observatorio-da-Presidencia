# Guia de Instalação e Implantação

## 1. Requisitos do Sistema

- **PHP:** 8.3 ou superior (com extensões `pdo_mysql`, `curl`, `json`, `mbstring`, `openssl`, `bcmath`, `fileinfo`).
- **Banco de Dados:** MySQL 8.0+ (ou MariaDB 10.5+ compatível).
- **Servidor Web:** Apache 2.4+ (com `mod_rewrite` ativo) ou Nginx 1.20+.
- **Sistema Operacional:** Linux (Ubuntu 22.04 LTS / Debian 12 / RHEL 9 recomendados) ou Windows Server.

---

## 2. Passo a Passo de Instalação

### Passo 1: Obter o Código
Clone ou extraia o repositório no diretório de destino:
```bash
git clone https://github.com/observatorio/presidencia.git /var/www/presidencia
cd /var/www/presidencia
```

### Passo 2: Configurar Variáveis de Ambiente
Copie o arquivo de exemplo e ajuste as credenciais:
```bash
cp .env.example .env
nano .env
```
Preencha os parâmetros de conexão com o MySQL:
```ini
APP_ENV=production
APP_DEBUG=false
APP_URL=https://seu-dominio.org.br
APP_SECRET=defina-uma-chave-aleatoria-forte-de-32-caracteres

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=observatorio_presidencia
DB_USERNAME=usuario_obs
DB_PASSWORD=sua_senha_segura
DB_CHARSET=utf8mb4

# Opcional: Chave gratuita do Portal da Transparência
TRANSPARENCIA_API_KEY=sua_chave_cgu
```

### Passo 3: Executar Migrações e Seeds
Execute o script CLI de inicialização do banco:
```bash
php database/migrate.php
```
Isso criará automaticamente o banco `observatorio_presidencia`, todas as 18 tabelas relacionais, índices, mandatos históricos, categorias e os usuários administrativos padrão.

### Passo 4: Permissões de Diretório
Garanta permissão de escrita para o usuário do servidor web (`www-data` no Ubuntu):
```bash
chown -R www-data:www-data storage/
chmod -R 775 storage/
```

### Passo 5: Configurar o Servidor Web

#### Opção A: Apache (VirtualHost)
Certifique-se de que o `mod_rewrite` está ativo:
```bash
sudo a2enmod rewrite
```
Configuração do VirtualHost:
```apache
<VirtualHost *:80>
    ServerName observatorio.org.br
    DocumentRoot /var/www/presidencia/public

    <Directory /var/www/presidencia/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/observatorio_error.log
    CustomLog ${APACHE_LOG_DIR}/observatorio_access.log combined
</VirtualHost>
```

#### Opção B: Nginx
```nginx
server {
    listen 80;
    server_name observatorio.org.br;
    root /var/www/presidencia/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

---

## 3. Credenciais Iniciais de Acesso Administrativo

Após a execução das seeds, os usuários administrativos são criados:

| Usuário | E-mail | Senha Padrão | Perfil (RBAC) |
| :--- | :--- | :--- | :--- |
| **Administrador Geral** | `admin@observatorio.org` | `Admin@2026!` | `admin` |
| **Editor Chefe** | `editor@observatorio.org` | `Editor@2026!` | `editor` |
| **Analista de Dados** | `analista@observatorio.org` | `Analista@2026!` | `analista` |

> 🔒 **Importante:** Altere as senhas no primeiro login no painel em `/admin/login`.

---

## 4. Testes de Verificação Local
Para validar a integridade completa da instalação, execute a bateria de testes:
```bash
php tests/test_suite.php
```
Todos os 20 testes devem retornar `[PASS]`.

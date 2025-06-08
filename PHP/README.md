# Ambiente de Desenvolvimento PHP com Docker e Apache

Este projeto configura um ambiente de desenvolvimento local para aplicações PHP "puras" (sem frameworks específicos como Laravel ou Symfony por padrão) utilizando Docker e Docker Compose. Ele inclui:

- Servidor Web Apache
- PHP-FPM (com extensões comuns e Composer instalados)
- Banco de Dados MySQL (ou MariaDB, se preferir alterar a imagem)
- Opcionalmente, Xdebug para debugging.

Este ambiente é projetado para ser uma base facilmente replicável para qualquer projeto PHP.

## Pré-requisitos

Antes de começar, certifique-se de que você tem os seguintes softwares instalados em sua máquina:

- **Docker**: [Instruções de Instalação do Docker](https://docs.docker.com/get-docker/)
- **Docker Compose**: Geralmente vem incluído com o Docker Desktop. Se não, siga as [Instruções de Instalação do Docker Compose](https://docs.docker.com/compose/install/).

## Estrutura de Pastas e Arquivos

```
PHP/
├── docker-compose.yml  # Define os serviços, redes e volumes Docker
├── .env                # Contém as variáveis de ambiente (senhas, portas, etc.)
├── app/                # Raiz do seu código PHP (DocumentRoot do Apache)
│   └── index.php       # Exemplo de arquivo PHP
├── apache/
│   └── my-php-app.conf # Configuração do VirtualHost do Apache para PHP-FPM
├── php/
│   ├── Dockerfile      # Define a imagem PHP customizada (extensões, Composer)
│   └── xdebug.ini      # Configuração do Xdebug (opcional)
└── README.md           # Este arquivo de instruções
```

## Configuração

1.  **Copie este diretório**: Para iniciar um novo projeto PHP, copie toda esta pasta `PHP/` para o local do seu novo projeto.
2.  **Configure as Variáveis de Ambiente**:

    - Crie um arquivo chamado `.env` na raiz desta pasta (ao lado do `docker-compose.yml`) se ele não existir (você pode copiar o `.env.example` se houver um).
    - Abra o arquivo `.env` e ajuste as variáveis conforme necessário. Principalmente, **altere as senhas padrão** para o banco de dados.

    Exemplo de conteúdo do `.env`:

    ```env
    # Configurações do Servidor Web Apache
    APACHE_PORT=8080

    # Configurações do Banco de Dados MySQL
    MYSQL_DATABASE=php_app_db
    MYSQL_USER=php_app_user
    MYSQL_PASSWORD=sua_senha_segura_aqui
    MYSQL_ROOT_PASSWORD=sua_senha_root_segura_aqui

    # Configurações do Xdebug (opcional, descomente e ajuste se for usar)
    # PHP_IDE_CONFIG=serverName=docker-php-apache
    # XDEBUG_MODE=develop,debug # Ou apenas 'debug' para debugging on-demand
    # XDEBUG_CONFIG=client_host=host.docker.internal client_port=9003 log_level=0
    # Para XDEBUG_CONFIG em Linux sem Docker Desktop, pode ser necessário usar:
    # XDEBUG_CONFIG="client_host=$(ip route|awk '/default/ {print $3}') client_port=9003 log_level=0"
    ```

3.  **Permissões de Pasta (UID/GID - Opcional, mas recomendado para desenvolvimento)**:
    - Para evitar problemas de permissão de escrita na pasta `app/` (que é montada como um volume), o `Dockerfile` do PHP tenta ajustar o UID/GID do usuário `www-data` dentro do contêiner para corresponder ao seu usuário do host.
    - Antes de executar `docker-compose up` pela primeira vez, você pode exportar as variáveis `UID` e `GID` no seu terminal:
      ```bash
      export UID=$(id -u)
      export GID=$(id -g)
      ```
    - Se você não fizer isso, o `docker-compose.yml` usará `1000` como padrão, o que geralmente funciona em muitos sistemas Linux.

## Como Iniciar o Ambiente

1.  Abra um terminal na raiz desta pasta (`PHP/`).
2.  Se estiver usando a configuração de UID/GID, execute os comandos `export` acima.
3.  Execute o seguinte comando para construir as imagens (se necessário) e iniciar os contêineres em segundo plano:
    ```bash
    docker-compose up -d --build
    ```
    O `--build` é importante na primeira vez ou sempre que você modificar o `php/Dockerfile`. Nas execuções subsequentes, você pode omiti-lo se o Dockerfile não mudou: `docker-compose up -d`.
    Na primeira vez, o Docker fará o download das imagens base (PHP, Apache, MySQL), o que pode levar alguns minutos.

## Acessando a Aplicação

Após os contêineres estarem em execução:

- **Aplicação PHP**: Abra seu navegador e acesse `http://localhost:PORTA_APACHE`
  (Onde `PORTA_APACHE` é o valor que você definiu para `APACHE_PORT` no seu arquivo `.env` - o padrão é `8080`). Você deverá ver o `app/index.php`.
- **Banco de Dados**: Você pode se conectar ao banco de dados MySQL usando um cliente de sua preferência (como DBeaver, DataGrip, MySQL Workbench) com os seguintes detalhes:
  - **Host**: `127.0.0.1` ou `localhost`
  - **Porta**: A porta do MySQL não é exposta diretamente ao host por padrão neste `docker-compose.yml` para maior segurança. Os serviços `php` e `apache` se comunicam com o `db` pela rede interna do Docker. Se precisar de acesso externo, descomente e ajuste a seção `ports` no serviço `db` do `docker-compose.yml` (ex: `- "33060:3306"` para acessar na porta `33060` do host).
  - **Usuário**: O valor de `MYSQL_USER` do seu `.env`.
  - **Senha**: O valor de `MYSQL_PASSWORD` do seu `.env`.
  - **Database**: O valor de `MYSQL_DATABASE` do seu `.env`.

## Gerenciando os Contêineres

- **Para parar os contêineres**:
  ```bash
  docker-compose down
  ```
- **Para parar e remover os volumes** (isso apagará os dados do banco de dados, use com cuidado!):
  ```bash
  docker-compose down -v
  ```
- **Para ver os logs dos contêineres**:
  ```bash
  docker-compose logs -f
  ```
  Ou para um serviço específico (ex: `php`, `apache`, `db`):
  ```bash
  docker-compose logs -f php
  ```
- **Para executar comandos dentro de um contêiner** (ex: Composer no contêiner PHP):
  ```bash
  docker-compose exec php composer install
  docker-compose exec php php artisan migrate # Exemplo para um projeto Laravel
  ```

## Persistência de Dados

- **Banco de Dados**: Os dados do MySQL são armazenados em um volume Docker nomeado (`php_mysql_data` conforme definido no `docker-compose.yml`). Isso garante que seus dados persistam mesmo que os contêineres sejam parados e recriados (a menos que você use `docker-compose down -v`).
- **Código PHP**: A pasta `app/` local é mapeada diretamente para `/var/www/html/app` dentro dos contêineres `php` e `apache`. Quaisquer alterações feitas nos arquivos dentro de `app/` no seu host serão refletidas imediatamente nos contêineres.

## Xdebug (Opcional)

O `php/Dockerfile` inclui a instalação do Xdebug. Para habilitá-lo:

1.  Descomente e ajuste as variáveis `XDEBUG_MODE` e `XDEBUG_CONFIG` no seu arquivo `.env`.
    - `XDEBUG_MODE=debug` (para debugging on-demand) ou `XDEBUG_MODE=develop,debug` (para development helpers e debugging).
    - `XDEBUG_CONFIG`: `client_host` deve ser `host.docker.internal` para Docker Desktop (Windows/Mac). Para Linux, pode ser necessário usar o IP da sua máquina na rede Docker (ex: `client_host=$(ip route|awk '/default/ {print $3}')`). `client_port` é geralmente `9003`.
2.  Reconstrua a imagem PHP se você alterou o `php/xdebug.ini` ou as variáveis no Dockerfile (geralmente não necessário se usar `.env`): `docker-compose build php` (ou `docker-compose up -d --build`).
3.  Configure seu IDE (VSCode, PhpStorm, etc.) para escutar conexões Xdebug na porta configurada (padrão 9003).

## API de Login (Autenticação JWT)

Este ambiente inclui uma estrutura base para uma API de login e registro de usuários utilizando JSON Web Tokens (JWT) para autenticação.

### Estrutura da API

- **`app/api/`**: Contém os scripts dos endpoints da API.
  - `register.php`: Endpoint para registrar novos usuários.
  - `login.php`: Endpoint para autenticar usuários e obter um JWT.
  - `profile.php`: Exemplo de endpoint protegido que requer um JWT válido.
- **`app/src/`**: Contém as classes principais da lógica da aplicação.
  - `Database.php`: Gerencia a conexão PDO com o banco de dados.
  - `Entity/User.php`: Representa a entidade usuário.
  - `Repository/UserRepository.php`: Interage com a tabela `users` no banco de dados.
  - `Service/AuthService.php`: Lida com a lógica de registro, login, e geração/validação de JWT.
  - `Utils/ApiResponse.php`: Classe utilitária para enviar respostas JSON padronizadas.
- **`app/config/config.php`**: Carrega as configurações da aplicação (banco de dados, JWT) a partir do arquivo `.env`.
- **`app/bootstrap.php`**: Inicializa o autoloader do Composer e carrega as variáveis de ambiente.
- **`app/composer.json`**: Define as dependências PHP (como `firebase/php-jwt` e `vlucas/phpdotenv`).

### Configuração da API

1.  **Variáveis de Ambiente**: Certifique-se de que as variáveis `MYSQL_HOST`, `MYSQL_PORT`, `MYSQL_DATABASE`, `MYSQL_USER`, `MYSQL_PASSWORD`, `JWT_SECRET`, `JWT_EXPIRY_SECONDS`, `JWT_ISSUER`, `JWT_AUDIENCE` estão configuradas corretamente no arquivo `PHP/.env`.

    - **IMPORTANTE**: Altere `JWT_SECRET` para uma chave secreta longa, forte e aleatória. Não use o valor padrão em produção.

2.  **Instalar Dependências do Composer**:
    Se ainda não o fez, execute o Composer para instalar as bibliotecas necessárias. Dentro da pasta `PHP/` no seu terminal:

    ```bash
    docker-compose exec php composer install -d /var/www/html/app
    ```

    ou

    ```bash
    docker-compose exec php composer update -d /var/www/html/app
    ```

3.  **Criar Tabela de Usuários**:
    Execute o seguinte SQL no seu banco de dados (ou use o sql que está em 'banco-de-dados/users.sql'):
    ```sql
    CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ```

### Endpoints da API

Todos os endpoints esperam e retornam dados no formato JSON.

- **`POST /app/api/register.php`**

  - Registra um novo usuário.
  - **Corpo da Requisição (JSON):**
    ```json
    {
      "username": "testuser",
      "email": "test@example.com",
      "password": "password123"
    }
    ```
  - **Resposta de Sucesso (201):**
    ```json
    {
      "success": true,
      "message": "User registered successfully.",
      "userId": 1
    }
    ```
  - **Resposta de Erro (400):**
    ```json
    {
      "success": false,
      "message": "Email already in use."
    }
    ```

- **`POST /app/api/login.php`**

  - Autentica um usuário e retorna um JWT.
  - **Corpo da Requisição (JSON):**
    ```json
    {
      "email": "test@example.com",
      "password": "password123"
    }
    ```
  - **Resposta de Sucesso (200):**
    ```json
    {
      "success": true,
      "message": "Login successful.",
      "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
    }
    ```
  - **Resposta de Erro (401):**
    ```json
    {
      "success": false,
      "message": "Invalid email or password."
    }
    ```

- **`GET /app/api/profile.php`**
  - Endpoint de exemplo protegido. Requer um token JWT válido no cabeçalho `Authorization`.
  - **Cabeçalho da Requisição:**
    `Authorization: Bearer <seu_jwt_aqui>`
  - **Resposta de Sucesso (200):**
    ```json
    {
      "message": "Access granted.",
      "user_data": {
        "userId": 1,
        "username": "testuser",
        "email": "test@example.com"
      }
    }
    ```
  - **Resposta de Erro (401):**
    ```json
    {
      "message": "Access denied. Invalid or expired token."
    }
    ```

### Como Usar

1.  Inicie o ambiente Docker: `docker-compose up -d` (na pasta `PHP/`).
2.  Use uma ferramenta como Postman, Insomnia ou `curl` para interagir com os endpoints da API.
    - Exemplo de registro com `curl`:
      ```bash
      curl -X POST -H "Content-Type: application/json" \
      -d '{"username":"newuser","email":"new@example.com","password":"securepassword"}' \
      http://localhost:8081/app/api/register.php
      ```
    - Exemplo de login com `curl`:
      ```bash
      curl -X POST -H "Content-Type: application/json" \
      -d '{"email":"new@example.com","password":"securepassword"}' \
      http://localhost:8081/app/api/login.php
      ```
    - Exemplo de acesso ao perfil com `curl` (substitua `YOUR_TOKEN` pelo token obtido no login):
      ```bash
      curl -X GET -H "Authorization: Bearer YOUR_TOKEN" \
      http://localhost:8081/app/api/profile.php
      ```
      (Lembre-se de que a porta `8081` é o valor de `APACHE_PORT` no seu `.env`).

### Próximos Passos e Melhorias

- **Validação de Entrada Detalhada**: Implementar validação mais robusta para todos os dados de entrada.
- **Tratamento de Erros**: Melhorar o tratamento de exceções e logs de erro.
- **Router**: Implementar um router mais sofisticado (ex: usando `.htaccess` e um script PHP central) para URLs amigáveis (ex: `/api/login` em vez de `/app/api/login.php`).
- **Refresh Tokens**: Para sessões mais longas e seguras, implementar refresh tokens.
- **Testes**: Adicionar testes unitários e de integração.
- **Rate Limiting**: Implementar limitação de taxa para proteger contra ataques de força bruta.
- **HTTPS**: Sempre usar HTTPS em produção.

---

Este ambiente fornece uma base robusta e isolada para seus projetos PHP.

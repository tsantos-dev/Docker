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

## Solução de Problemas Comuns

- **Erro de Porta em Uso**: Se a porta definida em `APACHE_PORT` já estiver em uso por outro aplicativo, você receberá um erro. Altere o valor de `APACHE_PORT` no arquivo `.env` para uma porta diferente (ex: `8081`) e reinicie os contêineres (`docker-compose down && docker-compose up -d`).
- **Problemas de Permissão na pasta `app/`**: Se você não configurou o `UID`/`GID` ou se os valores não correspondem, o servidor web/PHP dentro do contêiner pode não ter permissão para escrever em arquivos/pastas dentro de `app/` (ex: para uploads, cache). Certifique-se de que as permissões da pasta `app/` no seu host permitem a escrita pelo usuário/grupo correto ou use a configuração UID/GID.

---

Este ambiente fornece uma base robusta e isolada para seus projetos PHP.

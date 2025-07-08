# :dart: API de Rifas Online — Slim Framework 4

[![Coverage Status](https://coveralls.io/repos/github/slimphp/Slim-Skeleton/badge.svg?branch=master)](https://coveralls.io/github/slimphp/Slim-Skeleton?branch=master)

API RESTful desenvolvida em [Slim Framework 4](https://www.slimframework.com/) para gerenciar um sistema de rifas online. Permite criar rifas, vender números, acompanhar participantes e realizar sorteios de forma segura, com autenticação via **JWT**.

:lock: Autenticação segura com JSON Web Token  
:rocket: Roda localmente na porta **8082**

---

## :package: Tecnologias Utilizadas

- :gear: Slim Framework 4
- :package: PHP-DI
- :page_facing_up: Monolog
- :lock: JWT (firebase/php-jwt)
- :books: PSR-7 e PSR-15
- :wrench: Composer

---

## :rocket: Instalação

### Pré-requisitos

- :elephant: PHP 8+
- :package: Composer
- :floppy_disk: MariaDB 11+
- :test_tube: Git
- :whale: Docker e Docker Compose (opcional)

### Passo a passo

```bash
git clone https://github.com/danielkzero/api-rifa.git
cd api-rifa
composer install
```

Crie um arquivo `.env` com base no `.env.example` e configure o banco de dados, chave JWT e outras variáveis.

---

## :arrow_forward: Rodando Localmente

### :wrench: PHP embutido (modo dev)

```bash
composer start
```

Acesse no navegador:
```
http://localhost:8082
```

---

## :closed_lock_with_key: Autenticação JWT

Após autenticar o usuário (login), você receberá um **token JWT** no retorno. Esse token deve ser enviado no cabeçalho de todas as requisições protegidas:

```http
Authorization: Bearer seu_token_jwt_aqui
```

---

## :pushpin: Principais Endpoints

Método | Rota                  | Descrição                                     | Autenticação 
-------|-----------------------|-----------------------------------------------|--------------
GET    | `/usuarios`           | Lista todos os usuários ativos                | :heavy_check_mark: Sim
GET    | `/usuarios/{id}`      | Retorna dados de um usuário específico        | :heavy_check_mark: Sim
POST   | `/usuarios/auth`      | Autenticação e geração de token JWT           | :x: Não
POST   | `/usuarios`           | Cria um novo usuário                          | :heavy_check_mark: Sim
PUT    | `/usuarios/{id}`      | Atualiza os dados de um usuário               | :heavy_check_mark: Sim
DELETE | `/usuarios/{id}`      | Desativa logicamente um usuário               | :heavy_check_mark: Sim
GET    | `/clientes`           | Lista todos os clientes ativos                | :heavy_check_mark: Sim
GET    | `/clientes/{id}`      | Retorna os dados de um cliente                | :heavy_check_mark: Sim
POST   | `/clientes`           | Cria um novo cliente                          | :x: Não
PUT    | `/clientes/{id}`      | Atualiza os dados de um cliente               | :heavy_check_mark: Sim
DELETE | `/clientes/{id}`      | Exclui (hard delete) um cliente               | :heavy_check_mark: Sim
GET    | `/campanhas`          | Lista todas as campanhas ativas               | :x: Não
GET    | `/campanhas/{id}`     | Retorna uma campanha específica               | :x: Não
POST   | `/campanhas`          | Cria uma nova campanha                        | :heavy_check_mark: Sim
PUT    | `/campanhas/{id}`     | Atualiza os dados de uma campanha             | :heavy_check_mark: Sim
DELETE | `/campanhas/{id}`     | Exclui logicamente uma campanha (soft delete) | :heavy_check_mark: Sim

---

## :file_folder: Estrutura de Pastas

```bash
.
├── app/
│   ├── dependencies.php            # Definições de serviços e dependências
│   ├── routes.php                  # Registro das rotas
│   └── settings.php                # Configurações gerais da aplicação
│
├── src/
│   ├── Application/
│   │   ├── Actions/
│   │   ├── Handlers/
│   │   │   ├── Api/
│   │   │   │   ├── Campanhas.php   # Endpoints de campanhas
│   │   │   │   ├── Clientes.php    # Endpoints de clientes
│   │   │   │   ├── Online.php      # Página de apresentação da API
│   │   │   │   └── Usuarios.php    # Endpoints de usuários
│   │   ├── Middleware/
│   │   └── Settings/
│
├── Auth/
│   ├── JwtMiddleware.php           # Middleware de autenticação JWT
│   └── TokenValidator.php          # Validação e geração de tokens JWT
│
├── Domain/
├── Infrastructure/
├── logs/
├── public/                         # Pasta pública com `index.php`
├── tests/
├── composer.json                   # Dependências
└── README.md                       # Este arquivo
```

---

## :test_tube: Testes

Execute os testes com:

```bash
composer test
```

---

## :page_facing_up: Licença

Este projeto está licenciado sob a **MIT License**.

---

## :man_technologist: Desenvolvido por

Daniel S. Ramos  
:link: https://github.com/danielkzero/api-rifa


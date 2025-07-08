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

### :whale: Docker

> :warning: Requer `docker-compose.yml` configurado

```bash
docker-compose up -d
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
GET    | `/usuarios`           | Lista todos os usuários ativos                | :lock: Sim
GET    | `/usuarios/{id}`      | Retorna dados de um usuário específico        | :lock: Sim
POST   | `/usuarios/auth`      | Autenticação e geração de token JWT           | :unlock: Não
POST   | `/usuarios`           | Cria um novo usuário                          | :lock: Sim
PUT    | `/usuarios/{id}`      | Atualiza os dados de um usuário               | :lock: Sim
DELETE | `/usuarios/{id}`      | Desativa logicamente um usuário               | :lock: Sim
GET    | `/clientes`           | Lista todos os clientes ativos                | :lock: Sim
GET    | `/clientes/{id}`      | Retorna os dados de um cliente                | :lock: Sim
POST   | `/clientes`           | Cria um novo cliente                          | :unlock: Não
PUT    | `/clientes/{id}`      | Atualiza os dados de um cliente               | :lock: Sim
DELETE | `/clientes/{id}`      | Exclui (hard delete) um cliente               | :lock: Sim
GET    | `/campanhas`          | Lista todas as campanhas ativas               | :unlock: Não
GET    | `/campanhas/{id}`     | Retorna uma campanha específica               | :unlock: Não
POST   | `/campanhas`          | Cria uma nova campanha                        | :lock: Sim
PUT    | `/campanhas/{id}`     | Atualiza os dados de uma campanha             | :lock: Sim
DELETE | `/campanhas/{id}`     | Exclui logicamente uma campanha (soft delete) | :lock: Sim

---

## :file_folder: Estrutura de Pastas

```bash
.
├── app/
│   ├── dependencies.php         # :wrench: Definições de serviços e dependências
│   ├── routes.php               # :triangular_flag_on_post: Registro das rotas
│   └── settings.php             # :gear: Configurações gerais da aplicação
│
├── src/
│   ├── Application/
│   │   ├── Actions/
│   │   ├── Handlers/
│   │   │   ├── Api/
│   │   │   │   ├── Campanhas.php   # :trophy: Endpoints de campanhas
│   │   │   │   ├── Clientes.php    # :busts_in_silhouette: Endpoints de clientes
│   │   │   │   ├── Online.php      # :globe_with_meridians: Página de apresentação da API
│   │   │   │   └── Usuarios.php    # :man_technologist: Endpoints de usuários
│   │   ├── Middleware/
│   │   └── Settings/
│
├── Auth/
│   ├── JwtMiddleware.php        # :lock: Middleware de autenticação JWT
│   └── TokenValidator.php       # :key: Validação e geração de tokens JWT
│
├── Domain/
├── Infrastructure/
├── logs/
├── public/                      # :globe_with_meridians: Pasta pública com `index.php`
├── tests/
├── composer.json        # :package: Dependências
└── README.md            # :blue_book: Este arquivo
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


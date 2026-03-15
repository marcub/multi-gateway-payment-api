# Multi-Gateway Payment API

API RESTful para processamento de pagamentos com suporte a múltiplos gateways simultâneos, failover automático por prioridade e reembolso. Construída com Laravel 12, arquitetura DDD (Domain-Driven Design) e autenticação via Laravel Sanctum.

![PHP Version](https://img.shields.io/badge/PHP-8.4-777BB4?style=for-the-badge&logo=php&logoColor=white)
![Laravel Version](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![Docker](https://img.shields.io/badge/Docker-2496ED?style=for-the-badge&logo=docker&logoColor=white)
![Architecture](https://img.shields.io/badge/Architecture-DDD%20%2F%20Clean-orange?style=for-the-badge)
![Methodology](https://img.shields.io/badge/Methodology-TDD-success?style=for-the-badge)
![CI/CD](https://img.shields.io/badge/CI-GitHub%20Actions-2088FF?style=for-the-badge&logo=github-actions&logoColor=white)
![Tests](https://img.shields.io/badge/Tests-PHPUnit-3776AB?style=for-the-badge&logo=phpunit&logoColor=white)

---

## Índice

- [Tecnologias](#tecnologias)
- [Arquitetura](#arquitetura)
- [Pré-requisitos](#pré-requisitos)
- [Instalação](#instalação)
- [Dados de Seed](#dados-de-seed)
- [Credenciais de Teste](#credenciais-de-teste)
- [Variáveis de Ambiente](#variáveis-de-ambiente)
- [Gateways de Pagamento](#gateways-de-pagamento)
- [Roles e Permissões](#roles-e-permissões)
- [API Reference](#api-reference)
  - [Autenticação](#autenticação)
  - [Usuários](#usuários)
  - [Clientes](#clientes)
  - [Produtos](#produtos)
  - [Gateways](#gateways)
  - [Transações](#transações)
- [Formato de Resposta](#formato-de-resposta)
- [Testes](#testes)
- [Estrutura do Projeto](#estrutura-do-projeto)

---

## Tecnologias

| Tecnologia | Versão |
|---|---|
| PHP | ^8.2 |
| Laravel | ^12.0 |
| Laravel Sanctum | ^4.0 |
| PHPUnit | ^11.5 |
| MySQL | 8.0 |
| Docker / Docker Compose | - |
| Nginx | - |

---

## Arquitetura

O projeto segue os princípios de **Domain-Driven Design (DDD)** com separação estrita em três camadas:

```
app/
├── Domain/          # Regras de negócio puras (Entities, ValueObjects, entre outros)
├── Application/     # Casos de uso, DTOs, orquestração
└── Infrastructure/  # Controllers, Eloquent, HTTP Clients, Middlewares
```

**Fluxo de cobrança com failover:**
1. O `TransactionService` busca os gateways ativos ordenados por prioridade
2. Tenta processar no Gateway de maior prioridade
3. Em caso de falha, tenta o próximo gateway automaticamente
4. Se todos falharem, salva a transação com status `failed`

---

## Pré-requisitos

- [Docker](https://docs.docker.com/get-docker/) e [Docker Compose](https://docs.docker.com/compose/)

---

## Instalação

```bash
# 1. Clone o repositório
git clone https://github.com/marcub/multi-gateway-payment-api.git
cd multi-gateway-payment-api

# 2. Copie o arquivo de ambiente
cp .env.example .env

# 3. Configure as variáveis de ambiente (veja seção abaixo)
# Edite o .env com as credenciais do banco e dos gateways

# 4. Suba os containers
docker compose up -d

# 5. Instale as dependências PHP
docker compose exec app composer install

# 6. Gere a chave da aplicação
docker compose exec app php artisan key:generate

# 7. Rode as migrations e seeds
docker compose exec app php artisan migrate --seed

# (opcional) recriar banco do zero e popular novamente
docker compose exec app php artisan migrate:fresh --seed
```

A API estará disponível em: **http://localhost:8080**  
O mock dos gateways estará nos portos **3001** (Gateway1) e **3002** (Gateway2).  
O phpMyAdmin estará disponível em: **http://localhost:8888**

## Dados de Seed

O projeto possui seeders para popular automaticamente dados de teste nas principais tabelas.

Seeders chamados:
- `UserSeeder`
- `GatewaySeeder`
- `ClientSeeder`
- `ProductSeeder`
- `TransactionSeeder`

Tabelas populadas:
- `users` (4 usuários)
- `gateways` (2 gateways ativos)
- `clients` (3 clientes)
- `products` (5 produtos)
- `transactions` (4 transações com status variados)
- `transaction_items` (itens das transações)

Cenários de transação criados:
- `paid` via Gateway1
- `paid` via Gateway2
- `failed` (sem gateway)
- `refunded`

## Credenciais de Teste

Usuários inseridos automaticamente pelo `UserSeeder`:

| Perfil | Email | Senha |
|---|---|---|
| Admin | `admin@example.com` | `admin12345` |
| Manager | `manager@example.com` | `manager12345` |
| Finance | `finance@example.com` | `finance12345` |
| User | `user@example.com` | `user12345` |


## Variáveis de Ambiente

As variáveis abaixo precisam ser configuradas além das padrão do Laravel:

```dotenv
# Banco de dados
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=payment_api
DB_USERNAME=laravel
DB_PASSWORD=secret

# Gateway 1 (JWT Auth — porta 3001)
GATEWAY1_BASE_URL=http://localhost:3001
GATEWAY1_EMAIL=dev@betalent.tech
GATEWAY1_TOKEN=FEC9BB078BF338F464F96B48089EB498

# Gateway 2 (Static Header Auth — porta 3002)
GATEWAY2_BASE_URL=http://localhost:3002
GATEWAY2_TOKEN=tk_f2198cc671b5289fa856
GATEWAY2_SECRET=3d15e8ed6131446ea7e3456728b1211f
```

---

## Gateways de Pagamento

O sistema suporta dois gateways externos mockados:

| | Gateway 1 | Gateway 2 |
|---|---|---|
| **Porta** | 3001 | 3002 |
| **Autenticação** | JWT (login com email + token) | Headers estáticos (`Gateway-Auth-Token` + `Gateway-Auth-Secret`) |
| **Cobrança** | `POST /transactions` | `POST /transacoes` |
| **Reembolso** | `POST /transactions/{id}/charge_back` | `POST /transacoes/reembolso` com body `{"id": "..."}` |
| **Campos payload** | `amount`, `name`, `email`, `cardNumber`, `cvv` | `valor`, `nome`, `email`, `numeroCartao`, `cvv` |

**Failover:** O sistema tenta os gateways em ordem de prioridade (menor número = maior prioridade). Se um gateway falhar, o próximo ativo é tentado automaticamente.

---

## Roles e Permissões

| Role | Descrição |
|---|---|
| `admin` | Acesso total a todos os endpoints |
| `manager` | Gerenciamento de usuários e produtos |
| `finance` | Produtos, transações (cobrança não) e reembolso |
| `user` | Consulta de clientes |

> **Nota:** Só existe um usuário com role `admin` gerado automaticamente quando com login: `admin@example.com` e senha: `admin12345`. O endpoint `POST /register` aceita apenas roles `manager`, `finance` e `user`.

---

## API Reference

Todos os endpoints (exceto `/login`) requerem autenticação via Sanctum.

**Header obrigatório para endpoints autenticados:**
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

---

### Autenticação

#### `POST /api/register`

Registra um novo usuário.

**Roles permitidas:** `admin`, `manager`

**Request body:**
```json
{
  "email": "usuario@example.com",
  "password": "minimo8chars",
  "role": "manager"
}
```

| Campo | Tipo | Obrigatório | Validação |
|---|---|---|---|
| `email` | string | sim | formato email válido |
| `password` | string | sim | mínimo 8 caracteres |
| `role` | string | sim | `manager`, `finance` ou `user` |

**Response `201`:**
```json
{
  "success": true,
  "message": "User registered successfully",
  "data": {
    "email": "usuario@example.com",
    "role": "manager",
    "is_active": true,
    "created_at": "2026-03-15 10:00:00"
  }
}
```

---

#### `POST /api/login`

Autentica o usuário e retorna o token Sanctum.

**Request body:**
```json
{
  "email": "usuario@example.com",
  "password": "minimo8chars"
}
```

**Response `200`:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "token": "1|abc123...",
    "role": "admin"
  }
}
```

---

#### `POST /api/logout`

Revoga todos os tokens do usuário autenticado.

**Roles permitidas:** Qualquer role autenticada

**Response `200`:**
```json
{
  "success": true,
  "message": "Logout successful"
}
```

---

### Usuários

#### `GET /api/users`

Lista todos os usuários.

**Roles permitidas:** `admin`, `manager`

**Response `200`:**
```json
{
  "success": true,
  "message": "Users retrieved successfully",
  "data": [
    {
      "id": "uuid",
      "email": "usuario@example.com",
      "role": "manager",
      "is_active": true,
      "created_at": "2026-03-15 10:00:00",
      "updated_at": "2026-03-15 10:00:00"
    }
  ]
}
```

---

#### `GET /api/users/{id}`

Retorna um usuário específico.

**Roles permitidas:** `admin`, `manager`

---

#### `PATCH /api/users/{id}`

Atualiza dados de um usuário. Pelo menos um campo deve ser enviado.

**Roles permitidas:** `admin`, `manager`

**Request body (todos opcionais, mas ao menos um obrigatório):**
```json
{
  "email": "novo@example.com",
  "password": "novasenha123",
  "role": "finance"
}
```

| Campo | Tipo | Validação |
|---|---|---|
| `email` | string | formato email válido |
| `password` | string | mínimo 8 caracteres |
| `role` | string | `admin`, `manager`, `finance` ou `user` |

---

#### `POST /api/users/{id}/activate`

Ativa um usuário.

**Roles permitidas:** `admin`, `manager`

---

#### `POST /api/users/{id}/deactivate`

Desativa um usuário.

**Roles permitidas:** `admin`, `manager`

---

### Clientes

#### `GET /api/clients`

Lista todos os clientes.

**Roles permitidas:** `admin`, `user`

**Response `200`:**
```json
{
  "success": true,
  "message": "Clients retrieved successfully",
  "data": [
    {
      "id": "uuid",
      "name": "João Silva",
      "email": "joao@example.com",
      "created_at": "2026-03-15 10:00:00",
      "updated_at": "2026-03-15 10:00:00"
    }
  ]
}
```

---

#### `GET /api/clients/{id}`

Retorna um cliente específico.

**Roles permitidas:** `admin`, `user`

---

### Produtos

#### `GET /api/products`

Lista todos os produtos.

**Roles permitidas:** `admin`, `manager`, `finance`

**Response `200`:**
```json
{
  "success": true,
  "message": "Products retrieved successfully",
  "data": [
    {
      "id": "uuid",
      "sku": "PROD-001",
      "name": "Produto Exemplo",
      "amount": 9990,
      "created_at": "2026-03-15 10:00:00",
      "updated_at": "2026-03-15 10:00:00"
    }
  ]
}
```

> **Nota:** `amount` é armazenado em centavos (ex: `9990` = R$ 99,90).

---

#### `GET /api/products/{id}`

Retorna um produto específico.

**Roles permitidas:** `admin`, `manager`, `finance`

---

#### `POST /api/products`

Cria um novo produto.

**Roles permitidas:** `admin`, `manager`, `finance`

**Request body:**
```json
{
  "sku": "PROD-001",
  "name": "Produto Exemplo",
  "amount": 9990
}
```

| Campo | Tipo | Obrigatório | Validação |
|---|---|---|---|
| `sku` | string | sim | máx. 255 chars, único |
| `name` | string | sim | máx. 255 chars |
| `amount` | integer | sim | mínimo 1 (em centavos) |

**Response `201`:**
```json
{
  "success": true,
  "message": "Product created successfully",
  "data": {
    "sku": "PROD-001",
    "name": "Produto Exemplo",
    "amount": 9990,
    "created_at": "2026-03-15 10:00:00"
  }
}
```

---

#### `PATCH /api/products/{id}`

Atualiza um produto. Pelo menos um campo deve ser enviado.

**Roles permitidas:** `admin`, `manager`, `finance`

**Request body (todos opcionais, mas ao menos um obrigatório):**
```json
{
  "sku": "PROD-001-V2",
  "name": "Nome Atualizado",
  "amount": 12990
}
```

---

#### `DELETE /api/products/{id}`

Remove um produto.

**Roles permitidas:** `admin`, `manager`, `finance`

**Response `200`:**
```json
{
  "success": true,
  "message": "Product deleted successfully"
}
```

---

### Gateways

#### `POST /api/gateways/{id}/activate`

Ativa um gateway de pagamento.

**Roles permitidas:** `admin`

**Response `200`:**
```json
{
  "success": true,
  "message": "Gateway activated successfully"
}
```

---

#### `POST /api/gateways/{id}/deactivate`

Desativa um gateway de pagamento.

**Roles permitidas:** `admin`

---

#### `PATCH /api/gateways/{id}/priority`

Altera a prioridade de um gateway. Menor número = maior prioridade.

**Roles permitidas:** `admin`

**Request body:**
```json
{
  "priority": 1
}
```

| Campo | Tipo | Obrigatório | Validação |
|---|---|---|---|
| `priority` | integer | sim | mínimo 1 |

---

### Transações

#### `GET /api/transactions`

Lista todas as transações.

**Roles permitidas:** `admin`

**Response `200`:**
```json
{
  "success": true,
  "message": "Transactions retrieved successfully",
  "data": [
    {
      "id": "uuid",
      "client_id": "uuid",
      "gateway_id": "uuid",
      "external_id": "ext-abc123",
      "status": "paid",
      "amount": 19980,
      "card_last_numbers": "4242",
      "items": [
        {
          "product_id": "uuid",
          "quantity": 2,
          "unit_amount": 9990,
          "subtotal": 19980
        }
      ],
      "created_at": "2026-03-15 10:00:00",
      "updated_at": "2026-03-15 10:00:00"
    }
  ]
}
```

---

#### `GET /api/transactions/{id}`

Retorna uma transação específica.

**Roles permitidas:** `admin`

---

#### `POST /api/transactions`

Processa uma nova cobrança com failover automático entre gateways.

**Roles permitidas:** `admin`

**Request body:**
```json
{
  "client_id": "uuid-do-cliente",
  "card_number": "4111111111111111",
  "cvv": "123",
  "items": [
    {
      "product_id": "uuid-do-produto",
      "quantity": 2,
      "unit_amount": 9990
    }
  ]
}
```

| Campo | Tipo | Obrigatório | Validação |
|---|---|---|---|
| `client_id` | string (UUID) | sim | UUID válido, cliente deve existir |
| `card_number` | string | sim | 13–19 dígitos |
| `cvv` | string | sim | 3–4 dígitos |
| `items` | array | sim | mínimo 1 item |
| `items.*.product_id` | string (UUID) | sim | UUID válido |
| `items.*.quantity` | integer | sim | mínimo 1 |
| `items.*.unit_amount` | integer | sim | mínimo 1 (em centavos) |

> **Importante:** O `amount` total da transação é calculado automaticamente como soma de `quantity * unit_amount` de cada item.

**Response `201`:**
```json
{
  "success": true,
  "message": "Transaction charged successfully",
  "data": {
    "id": "uuid",
    "client_id": "uuid",
    "gateway_id": "uuid",
    "external_id": "ext-abc123",
    "status": "paid",
    "amount": 19980,
    "card_last_numbers": "1111",
    "items": [
      {
        "product_id": "uuid",
        "quantity": 2,
        "unit_amount": 9990,
        "subtotal": 19980
      }
    ],
    "created_at": "2026-03-15 10:00:00",
    "updated_at": "2026-03-15 10:00:00"
  }
}
```

> Se todos os gateways falharem, a transação é salva com `status: "failed"` e `gateway_id: null`.

---

#### `POST /api/transactions/{id}/refund`

Estorna uma transação existente.

**Roles permitidas:** `admin`, `finance`

**Response `200`:**
```json
{
  "success": true,
  "message": "Transaction refunded successfully",
  "data": {
    "id": "uuid",
    "status": "refunded",
    ...
  }
}
```

---

## Formato de Resposta

Todas as respostas seguem o padrão:

**Sucesso:**
```json
{
  "success": true,
  "message": "Mensagem descritiva",
  "data": { ... }
}
```

**Erro:**
```json
{
  "success": false,
  "message": "Mensagem de erro",
  "errors": { ... }
}
```

**Códigos HTTP usados:**

| Código | Situação |
|---|---|
| `200` | Sucesso |
| `201` | Recurso criado |
| `401` | Não autenticado |
| `403` | Sem permissão (role insuficiente) |
| `404` | Recurso não encontrado |
| `422` | Falha de validação |
| `500` | Erro interno |

---

## Testes

```bash
# Rodar todos os testes
docker compose exec app php artisan test

# Rodar apenas testes unitários
docker compose exec app php artisan test --testsuite=Unit

# Rodar apenas testes de feature
docker compose exec app php artisan test --testsuite=Feature

```

## Estrutura do Projeto

```
app/
├── Domain/                              # Regras de negócio puras
│   ├── Shared/
│   │   ├── Email.php
│   │   └── UuidId.php
│   ├── Client/
│   │   ├── Entities/Client.php
│   │   ├── Exceptions/ClientException.php
│   │   ├── Repositories/ClientRepositoryInterface.php
│   │   ├── Service/ClientService.php
│   │   └── ValueObjects/ClientId.php
│   ├── Gateway/
│   │   ├── Entities/Gateway.php
│   │   ├── Exceptions/GatewayException.php
│   │   ├── Repositories/GatewayRepositoryInterface.php
│   │   ├── Service/GatewayService.php
│   │   └── ValueObjects/GatewayId.php
│   ├── Product/
│   │   ├── Entities/Product.php
│   │   ├── Exceptions/ProductException.php
│   │   ├── Repositories/ProductRepositoryInterface.php
│   │   ├── Service/ProductService.php
│   │   └── ValueObjects/ProductId.php
│   ├── Transaction/
│   │   ├── Contracts/GatewayClientInterface.php
│   │   ├── Entities/Transaction.php
│   │   ├── Exceptions/TransactionException.php
│   │   ├── Exceptions/TransactionItemException.php
│   │   ├── Repositories/TransactionRepositoryInterface.php
│   │   ├── Service/TransactionService.php
│   │   ├── Support/GatewayClientRegistry.php
│   │   └── ValueObjects/
│   │       ├── TransactionId.php
│   │       ├── TransactionItem.php
│   │       └── TransactionStatus.php
│   └── User/
│       ├── Entities/User.php
│       ├── Exceptions/UserException.php
│       ├── Repositories/UserRepositoryInterface.php
│       ├── Service/UserService.php
│       └── ValueObjects/
│           ├── Role.php
│           └── UserId.php
│
├── Application/                         # Casos de uso e DTOs
│   ├── Client/
│   │   └── UseCases/
│   │       ├── GetClientUseCase.php
│   │       └── ListClientsUseCase.php
│   ├── Gateway/
│   │   ├── DTOs/ChangePriorityGatewayDTO.php
│   │   └── UseCases/
│   │       ├── ActivateGatewayUseCase.php
│   │       ├── ChangePriorityGatewayUseCase.php
│   │       └── DeactivateGatewayUseCase.php
│   ├── Product/
│   │   ├── DTOs/
│   │   │   ├── CreateProductDTO.php
│   │   │   └── UpdateProductDTO.php
│   │   └── UseCases/
│   │       ├── CreateProductUseCase.php
│   │       ├── DeleteProductUseCase.php
│   │       ├── GetProductUseCase.php
│   │       ├── ListProductsUseCase.php
│   │       └── UpdateProductUseCase.php
│   ├── Transaction/
│   │   ├── DTOs/
│   │   │   ├── ChargeTransactionDTO.php
│   │   │   └── ChargeTransactionItemDTO.php
│   │   └── UseCases/
│   │       ├── ChargeTransactionUseCase.php
│   │       ├── GetTransactionUseCase.php
│   │       ├── ListTransactionsUseCase.php
│   │       └── RefundTransactionUseCase.php
│   └── User/
│       ├── DTOs/
│       │   ├── AuthenticateUserDTO.php
│       │   ├── RegisterUserDTO.php
│       │   └── UpdateUserDTO.php
│       └── UseCases/
│           ├── ActivateUserUseCase.php
│           ├── AuthenticateUserUseCase.php
│           ├── DeactivateUserUseCase.php
│           ├── GetUserUseCase.php
│           ├── ListUsersUseCase.php
│           ├── RegisterUserUseCase.php
│           └── UpdateUserUseCase.php
│
└── Infrastructure/                      # Implementações concretas
    ├── Database/
    │   ├── Eloquent/
    │   │   ├── Client.php
    │   │   ├── Gateway.php
    │   │   ├── Product.php
    │   │   ├── Transaction.php
    │   │   ├── TransactionItem.php
    │   │   └── User.php
    │   └── Repositories/
    │       ├── EloquentClientRepository.php
    │       ├── EloquentGatewayRepository.php
    │       ├── EloquentProductRepository.php
    │       ├── EloquentTransactionRepository.php
    │       └── EloquentUserRepository.php
    ├── Gateway/
    │   ├── Gateway1Client.php            # HTTP Client Gateway 1
    │   └── Gateway2Client.php            # HTTP Client Gateway 2
    ├── Http/
    │   ├── Controllers/
    │   │   ├── AuthController.php
    │   │   ├── ClientController.php
    │   │   ├── GatewayController.php
    │   │   ├── ProductController.php
    │   │   ├── TransactionController.php
    │   │   └── UserController.php
    │   ├── Middlewares/
    │   │   ├── ForceJsonRequestHeader.php
    │   │   └── RoleMiddleware.php
    │   ├── Requests/
    │   │   ├── AuthenticateUserRequest.php
    │   │   ├── ChangePriorityGatewayRequest.php
    │   │   ├── ChargeTransactionRequest.php
    │   │   ├── CreateProductRequest.php
    │   │   ├── RegisterUserRequest.php
    │   │   ├── UpdateProductRequest.php
    │   │   └── UpdateUserRequest.php
    │   └── Responses/ApiResponse.php
    └── Providers/
        └── DomainServiceProvider.php     # Bind interfaces → implementações

database/
├── factories/
│   └── UserFactory.php
├── migrations/
│   ├── *_create_users_table.php
│   ├── *_create_cache_table.php
│   ├── *_create_jobs_table.php
│   ├── *_create_personal_access_tokens_table.php
│   ├── *_alter_users_table_for_domain_auth.php
│   ├── *_create_gateways_table.php
│   ├── *_create_clients_table.php
│   ├── *_create_products_table.php
│   ├── *_create_transactions_table.php
│   └── *_create_transaction_items_table.php
└── seeders/
    ├── DatabaseSeeder.php
    ├── UserSeeder.php
    ├── GatewaySeeder.php
    ├── ClientSeeder.php
    ├── ProductSeeder.php
    └── TransactionSeeder.php

tests/
├── TestCase.php
├── Unit/Domain/
│   ├── Client/Service/ClientServiceTest.php
│   ├── Gateway/Service/GatewayServiceTest.php
│   ├── Product/Service/ProductServiceTest.php
│   ├── Transaction/Service/TransactionServiceTest.php
│   └── User/Service/UserServiceTest.php
└── Feature/
    ├── Auth/AuthFlowTest.php
    ├── Gateway/GatewayFlowTest.php
    ├── Products/ProductFlowTest.php
    └── Transaction/TransactionFlowTest.php
```
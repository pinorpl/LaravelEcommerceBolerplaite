# Architecture Documentation

## Overview

Ecommerce Boilerplate is a **modular monolith** that applies Domain-Driven Design (DDD) concepts within a single deployable unit. The primary architectural drivers are:

1. **Developer experience**: Clear module boundaries enable teams to work in parallel without stepping on each other.
2. **Testability**: Domain logic depends on abstractions (Repository interfaces), not on Eloquent directly.
3. **Security**: OAuth secrets never reach the browser thanks to the Next.js API Route proxy pattern.
4. **Async by default**: Post-registration emails are queued on Redis, keeping the HTTP response fast.
5. **Hybrid rendering**: Public pages are SSR/ISR for SEO; admin/cart are Client-side for interactivity.

---

## System Context Diagram

```mermaid
C4Context
    title System Context – Ecommerce Boilerplate

    Person(buyer, "Buyer", "Browses products, manages cart, places orders")
    Person(admin, "Admin", "Manages products and users")

    System_Boundary(app, "Ecommerce Boilerplate") {
        System(nextjs, "Next.js 14", "Frontend: SSR public pages, CSR admin/cart")
        System(laravel, "Laravel 11 API", "REST API: auth, products, cart, orders")
        SystemDb(mysql, "MySQL 8", "Persistent data store")
        SystemDb(redis, "Redis 7", "Queue broker for async jobs")
        System(mailpit, "Mailpit", "Dev SMTP server")
    }

    Rel(buyer, nextjs, "Uses", "HTTPS")
    Rel(admin, nextjs, "Uses", "HTTPS")
    Rel(nextjs, laravel, "Calls REST API", "HTTP/JSON")
    Rel(laravel, mysql, "Reads/Writes", "TCP/3306")
    Rel(laravel, redis, "Enqueues jobs", "TCP/6379")
    Rel(laravel, mailpit, "Sends emails", "SMTP/1025")
```

---

## Container (Docker) Diagram

```mermaid
C4Container
    title Container Diagram – Docker Services

    Container(nginx, "Nginx", "nginx:1.25-alpine", "Reverse proxy\n/api → php-fpm\nall else → nextjs")
    Container(php, "PHP-FPM", "php:8.3-fpm", "Laravel 11 application server")
    Container(queue, "Queue Worker", "php:8.3-fpm", "php artisan queue:work\nProcesses Redis jobs")
    Container(nextjs, "Next.js", "node:20-alpine", "Next.js 14 dev server\nport 3000")
    ContainerDb(mysql, "MySQL 8", "mysql:8.0", "Primary DB\nport 3306")
    ContainerDb(redis, "Redis 7", "redis:7-alpine", "Queue + cache\nport 6379")
    Container(mailpit, "Mailpit", "axllent/mailpit", "SMTP :1025\nWeb UI :8025")

    Rel(nginx, php, "FastCGI", "TCP/9000")
    Rel(nginx, nextjs, "Proxy", "HTTP/3000")
    Rel(php, mysql, "PDO", "TCP/3306")
    Rel(php, redis, "phpredis", "TCP/6379")
    Rel(php, mailpit, "SMTP", "TCP/1025")
    Rel(queue, redis, "Polls jobs", "TCP/6379")
    Rel(queue, mysql, "Reads/Writes", "TCP/3306")
    Rel(queue, mailpit, "Sends emails", "TCP/1025")
    Rel(nextjs, nginx, "Calls /api/*", "HTTP")
```

---

## Bounded Contexts

### UserManagement
**Responsibility**: User identity, authentication lifecycle, welcome communication.

| Layer | Contents |
|-------|---------|
| Domain | `User` model (aggregate root), `UserRegistered` event, `UserRepositoryInterface` |
| Application | `RegisterUserCommand`, `GetUsersQuery`, `SendWelcomeEmailListener` |
| Infrastructure | `EloquentUserRepository`, `WelcomeMail` mailable |

### ProductCatalog
**Responsibility**: Product inventory, visibility, search.

| Layer | Contents |
|-------|---------|
| Domain | `Product` model, `ProductRepositoryInterface` |
| Application | `CreateProductCommand`, `UpdateProductCommand`, `GetProductsQuery` |
| Infrastructure | `EloquentProductRepository` |

### Ordering
**Responsibility**: Shopping cart, order lifecycle, checkout.

| Layer | Contents |
|-------|---------|
| Domain | `Cart`, `CartItem`, `Order`, `OrderItem` models; `CartRepositoryInterface`, `OrderRepositoryInterface`; `OrderFactory` |
| Application | (CartController acts as command handler for simplicity) |
| Infrastructure | `EloquentCartRepository`, `EloquentOrderRepository` |

---

## Entity Relationship Diagram

```mermaid
erDiagram
    users {
        bigint id PK
        string name
        string email UK
        string password
        timestamp email_verified_at
        timestamp deleted_at
        timestamps
    }

    roles {
        bigint id PK
        string name
        string guard_name
    }

    permissions {
        bigint id PK
        string name
        string guard_name
    }

    model_has_roles {
        bigint role_id FK
        string model_type
        bigint model_id
    }

    role_has_permissions {
        bigint permission_id FK
        bigint role_id FK
    }

    products {
        bigint id PK
        string name
        string slug UK
        text description
        decimal price
        int stock
        string image
        boolean is_active
        bigint created_by FK
        timestamp deleted_at
        timestamps
    }

    carts {
        bigint id PK
        bigint user_id FK
        string session_id
        timestamps
    }

    cart_items {
        bigint id PK
        bigint cart_id FK
        bigint product_id FK
        int quantity
        decimal price
        timestamps
    }

    orders {
        bigint id PK
        bigint user_id FK
        enum status
        decimal total_amount
        text shipping_address
        timestamps
    }

    order_items {
        bigint id PK
        bigint order_id FK
        bigint product_id FK
        string product_name
        int quantity
        decimal unit_price
        timestamps
    }

    users ||--o{ model_has_roles : "has"
    roles ||--o{ model_has_roles : "assigned via"
    roles ||--o{ role_has_permissions : "has"
    permissions ||--o{ role_has_permissions : "granted via"
    users ||--o{ products : "creates (admin)"
    users ||--o{ carts : "owns"
    carts ||--o{ cart_items : "contains"
    products ||--o{ cart_items : "added to"
    users ||--o{ orders : "places"
    orders ||--o{ order_items : "contains"
    products ||--o{ order_items : "referenced in"
```

---

## Sequence: User Registration with Welcome Email

```mermaid
sequenceDiagram
    actor Browser
    participant NextJS as Next.js
    participant Laravel as Laravel API
    participant Redis
    participant Worker as Queue Worker
    participant Mailpit

    Browser->>NextJS: POST /register {name, email, password}
    NextJS->>Laravel: POST /api/register
    Laravel->>Laravel: FormRequest validates input
    Laravel->>Laravel: RegisterUserCommand.execute()
    Laravel->>Laravel: User::create() → INSERT users
    Laravel->>Laravel: $user->assignRole('buyer')
    Laravel->>Redis: Dispatch UserRegistered event<br/>(SendWelcomeEmailListener queued)
    Laravel-->>Browser: 201 {user, message}
    Note over Browser,Laravel: HTTP response returned immediately

    Redis-->>Worker: Job available on 'emails' queue
    Worker->>Worker: SendWelcomeEmailListener.handle()
    Worker->>Mailpit: SMTP: WelcomeMail to user@email
    Mailpit-->>Worker: 250 OK
```

---

## Sequence: Login with Passport (Password Grant)

```mermaid
sequenceDiagram
    actor Browser
    participant NextJS_API as Next.js API Route<br/>/api/auth/login
    participant Passport as Laravel Passport<br/>/oauth/token
    participant UserAPI as Laravel API<br/>/api/user

    Browser->>NextJS_API: POST {email, password}
    Note over NextJS_API: client_secret loaded from<br/>server environment variable
    NextJS_API->>Passport: POST /oauth/token<br/>{grant_type: password,<br/>client_id, client_secret,<br/>username, password}
    Passport-->>NextJS_API: {access_token, refresh_token, expires_in}
    NextJS_API->>UserAPI: GET /api/user<br/>Authorization: Bearer <token>
    UserAPI-->>NextJS_API: {id, name, email, roles}
    NextJS_API-->>Browser: {user, access_token}<br/>Set-Cookie: access_token (httpOnly)<br/>Set-Cookie: refresh_token (httpOnly)
    Note over Browser: access_token stored in React state<br/>refresh_token stored in httpOnly cookie only
```

---

## Design Patterns Applied

| Pattern | Location | Purpose |
|---------|----------|---------|
| **Repository** | `Domain/Repositories/*.Interface.php` → `Infrastructure/Repositories/Eloquent*.php` | Decouples domain from Eloquent |
| **Command** | `Application/Commands/*Command.php` | Encapsulates write operations (CQRS write side) |
| **Query Handler** | `Application/Queries/*Query.php` | Encapsulates read operations (CQRS read side) |
| **Factory** | `Ordering/Domain/Factories/OrderFactory.php` | Creates Orders from Carts atomically |
| **Observer/Event** | `UserRegistered` + `SendWelcomeEmailListener` | Decouples registration from emailing |
| **Pipeline** | FormRequest → Controller → Command | Validation stages before business logic |
| **API Resource** | `Http/Resources/*.php` | Transforms domain models to JSON contracts |
| **Proxy** | Next.js `/api/auth/login` route | Hides OAuth client_secret from browser |

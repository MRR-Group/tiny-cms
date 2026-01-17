## 🧠 Architektura – opis ogólny

Backend składa się z **4 warstw**:

### 1. Domain (DDD-light)
- Czysta domena biznesowa
- Encje / agregaty
- Value Objecty
- Reguły biznesowe
- Interfejsy repozytoriów

❌ Brak:
- Slim / HTTP
- ORM / SQL
- Storage / config

---

### 2. Application (Use-case layer)
- Przypadki użycia jako **Command / Query**
- **Handler** = wykonanie use-case
- **Validator** = walidacja wejścia
- **Policy** = autoryzacja / uprawnienia
- **Bus + middleware pipeline** (walidacja, transakcje, audit, cache)

To jest **mózg aplikacji**.

---

### 3. Infrastructure (adaptery)
- ORM + Postgres (implementacje repozytoriów)
- Storage (Cloudflare R2 / S3)
- Security (hash, tokeny)
- Config (env → config)

Tu są **konkrety technologiczne**.

---

### 4. Delivery (transport)
- Slim Framework
- REST API (admin)
- GraphQL (public content – Gatsby)
- Request / Resource jako osobne klasy

Delivery **mapuje wejście i wyjście**, nie robi biznesu.

---

## 🔁 Pipeline (middleware)

### HTTP middleware (Slim)
- Auth
- Role / permissions
- CORS
- Rate limit
- JSON body
- Error handler

### Application middleware (Command / Query Bus)
- Validation
- Authorization
- Transaction
- Audit
- Cache (Query)

**Przykładowy flow:**
HTTP Request
- Request (mapowanie)
- CommandBus
- ValidationMiddleware
- AuthorizationMiddleware
- TransactionMiddleware
- Handler
- Resource (mapowanie)
- JSON Response

## 🌳 Drzewko katalogów
```
apps/
  api/
    src/
      Domain/
        Content/
          Entity/        # Page, Entry, Asset (agregaty/encje CMS)
          ValueObject/   # Slug, Locale, Status, ContentType, itp.
          Repository/    # interfejsy repo: PageRepository, AssetRepository
          Rule/          # reguły domenowe (np. PublishRules)
          Exception/     # wyjątki domenowe (np. CannotPublishException)

        Auth/
          Entity/        # User (model domenowy)
          ValueObject/   # Role, Permission
          Policy/        # domenowe zasady dostępu (opcjonalnie)

        Shared/
          ValueObject/   # Id/Uuid, Email, DateRange (wspólne VO)
          Exception/     # wspólne wyjątki domenowe

      Application/
        Content/
          Command/       # CreatePageCommand, PublishPageCommand, UploadAssetCommand...
          Query/         # GetPageQuery, ListPagesQuery, SearchEntriesQuery...
          Handler/       # *Handler wykonuje Command/Query (use-case)
          Validator/     # waliduje Command/DTO (422)
          Policy/        # autoryzacja do use-case (RBAC, ownership)
          DTO/           # wej/wyj use-case (np. PageView, AssetView)

        Auth/
          Command/       # LoginCommand, LogoutCommand...
          Handler/
          Validator/
          DTO/

        Bus/
          Command/       # CommandBus (dispatch) + kontrakty
          Query/         # QueryBus (dispatch) + kontrakty

        Middleware/
          # application-level middleware dla busów:
          # ValidationMiddleware, AuthorizationMiddleware,
          # TransactionMiddleware, AuditMiddleware, CachingMiddleware(Query)

      Infrastructure/
        Persistence/
          # ORM + Postgres:
          # modele/rekordy ORM, migracje, implementacje repozytoriów

        Storage/
          # adapter R2 (S3-compatible):
          # presigned url, upload complete, metadane

        Security/
          # hasher haseł, generowanie/parsowanie tokenów, itp.

        Config/
          # adapter konfiguracji (env -> config), definicje ustawień

      Delivery/
        Http/
          Controller/
            Admin/       # REST admin controllers (bez biznesu)
            Public/      # np. /health, ewentualnie public endpoints

          Request/
            # per-endpoint request mappers:
            # CreatePageRequest, PublishPageRequest...
            # mapują PSR-7 -> Command/Query

          Resource/
            # per-entity / per-response presenters:
            # PageResource, AssetResource...
            # mapują DTO/Entity -> array JSON

          Middleware/
            # HTTP middleware:
            # Auth, Role, Cors, RateLimit, JsonBody, ErrorHandler

        GraphQL/
          Schema/        # definicje schematu
          Type/          # typy GraphQL
          Input/         # inputy (odpowiednik Request w GraphQL)
          Resolver/      # resolvery (mapują args -> Query/Handler)
          Resource/      # opcjonalne mapowanie wyników do shape GraphQL
```


## 📌 Konwencje (WAŻNE)
- **Każdy endpoint REST**:
  - `Request` → tworzy Command/Query
  - `Handler` → wykonuje logikę
  - `Resource` → zwraca JSON
- **Delivery** nigdy nie dotyka ORM
- **Domain** nigdy nie widzi Slim / DB / Storage
- Cross-cutting concerns są **TYLKO w middleware**
- Use-case = **Command/Query + Handler**
- Architektura ma być **lekka, czytelna i powtarzalna**
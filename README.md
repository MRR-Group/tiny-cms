# Tiny CMS

Monorepo dla projektu CMS z PHP Slim backend i React admin panel.

## Wymagania

- Docker & Docker Compose
- Node.js 20+ z pnpm
- PHP 8.3+ (dla development lokalnego bez Dockera)
- [Task](https://taskfile.dev/) (opcjonalnie)

## Konfiguracja uprawnień (USER_ID)

Aby uniknąć problemów z uprawnieniami do plików tworzonych przez kontenery Docker:

1. **Skopiuj plik `.env.example` do `.env`:**
   ```bash
   cp .env.example .env
   ```

2. **Ustaw swój USER_ID i GROUP_ID w pliku `.env`:**
   ```bash
   # Sprawdź swój USER_ID i GROUP_ID
   id -u  # USER_ID
   id -g  # GROUP_ID
   
   # Edytuj plik .env i ustaw odpowiednie wartości
   # Przykład:
   # USER_ID=1002
   # GROUP_ID=1002
   ```

**Rozwiązywanie problemów z uprawnieniami:**

Jeśli napotkasz błędy uprawnień w istniejących katalogach `vendor` lub `node_modules`:

```bash
# Zatrzymaj kontenery
docker compose down

# Usuń katalogi z błędnymi uprawnieniami
docker run --rm -v "$(pwd)/apps/api:/app" alpine sh -c "rm -rf /app/vendor"
docker run --rm -v "$(pwd)/apps/admin:/app" alpine sh -c "rm -rf /app/node_modules"

# Uruchom ponownie
docker compose up -d
```

## Quick Start

```bash
# Uruchom całe środowisko
docker compose up -d

# Sprawdź status
docker compose ps
```

**Dostępne usługi:**
- Backend API: http://localhost:8080
- Admin Panel: http://localhost:5180
- Mailpit: http://localhost:8025
- PostgreSQL: localhost:5432

## Struktura projektu

```
tiny-cms/
├── apps/
│   ├── api/           # PHP Slim backend
│   └── admin/         # React admin panel
├── packages/
│   └── shared/        # Wspólne typy/utils
├── docker/
│   ├── nginx/         # Nginx config
│   └── php/           # PHP Dockerfile
├── .github/workflows/ # CI/CD
├── docker-compose.yml
└── Taskfile.yml
```

## Development

### Backend (PHP)

```bash
cd apps/api

# Instalacja zależności
composer install

# Testy
composer test          # PHPUnit
composer lint          # PHPStan
composer cs            # Sprawdzanie codestyle
composer cs:fix        # Auto-fix codestyle
composer mutation      # Mutation testing (Infection)
```

### Frontend (React)

```bash
cd apps/admin

# Instalacja zależności
pnpm install

# Development
pnpm dev              # Vite dev server
pnpm storybook        # Storybook

# Testy & Build
pnpm test             # Vitest
pnpm lint             # ESLint
pnpm build            # Production build
pnpm mutation         # Mutation testing (Stryker)
```

## CI/CD

GitHub Actions uruchamiają się na każdym PR i push do main:

- **ci_php.yml** - testy backend (PHPUnit, PHPStan, codestyle, mutations)
- **ci_ts.yml** - testy frontend (Vitest, ESLint, build, mutations)
- **storybook.yml** - build Storybook
- **pr_title.yml** - walidacja tytułu PR

### Format tytułu PR

```
#123 - Opis zmian
- Opis zmian (bez numeru issue)
```

## Licencja

MIT

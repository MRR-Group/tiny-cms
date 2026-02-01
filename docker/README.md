# Docker Configuration

## Obrazy Docker

Projekt używa trzech niestandardowych obrazów Docker:

### 1. PHP API (`docker/php/Dockerfile`)
- Bazuje na `php:8.3-fpm-alpine`
- Zainstalowane rozszerzenia: `pdo_pgsql`, `pcov`
- Composer zainstalowany globalnie
- Użytkownik `appuser` z dopasowanym UID/GID do hosta

**Build args:**
- `USER_ID` - UID użytkownika (domyślnie 1000)
- `GROUP_ID` - GID użytkownika (domyślnie 1000)

### 2. Node.js Admin (`docker/node/Dockerfile`)
- Bazuje na `node:20-alpine`
- pnpm zainstalowany globalnie przez corepack
- Użytkownik `nodeuser` z dopasowanym UID/GID do hosta

**Build args:**
- `USER_ID` - UID użytkownika (domyślnie 1000)
- `GROUP_ID` - GID użytkownika (domyślnie 1000)

### 3. Node.js Storybook
- Używa tego samego obrazu co Admin (`docker/node/Dockerfile`)

## Konfiguracja uprawnień

Aby uniknąć problemów z uprawnieniami plików, projekt używa zmiennych środowiskowych `USER_ID` i `GROUP_ID` zdefiniowanych w pliku `.env`:

```bash
USER_ID=1002
GROUP_ID=1002
```

Te wartości są przekazywane do Dockerfile podczas budowania obrazów i używane do utworzenia użytkowników w kontenerach z dopasowanym UID/GID.

### Dlaczego to jest ważne?

Gdy kontener tworzy pliki (np. `vendor/`, `node_modules/`), domyślnie są one własnością użytkownika w kontenerze. Jeśli kontener działa jako root (UID 0), pliki na hoście również będą należeć do root, co powoduje problemy z uprawnieniami.

Rozwiązanie: kontenery działają jako użytkownicy z tym samym UID/GID co użytkownik hosta, więc pliki tworzone w kontenerze mają odpowiedniego właściciela na hoście.

## Struktura katalogów Docker

```
docker/
├── nginx/
│   └── default.conf    # Konfiguracja Nginx
├── node/
│   └── Dockerfile      # Obraz Node.js z pnpm
└── php/
    └── Dockerfile      # Obraz PHP z Composer
```

## Wolumeny

- `./apps/api:/var/www/html` - kod PHP API
- `./apps/admin:/app` - kod React Admin
- `postgres_data` - dane PostgreSQL (wolumen nazwany)

## Porty

| Serwis    | Port hosta | Port kontenera | Opis                    |
|-----------|------------|----------------|-------------------------|
| nginx     | 8080       | 80             | API HTTP                |
| admin     | 5180       | 5173           | Vite dev server         |
| storybook | 6006       | 6006           | Storybook               |
| mailpit   | 8025       | 8025           | Mailpit Web UI          |
| mailpit   | 1025       | 1025           | Mailpit SMTP            |
| db        | 5432       | 5432           | PostgreSQL              |

## Rozwiązywanie problemów

### Problem: Błędy uprawnień w vendor/ lub node_modules/

**Przyczyna:** Katalogi zostały utworzone przez kontener z innym UID/GID.

**Rozwiązanie:**
```bash
# Zatrzymaj kontenery
docker compose down

# Usuń katalogi z błędnymi uprawnieniami
docker run --rm -v "$(pwd)/apps/api:/app" alpine sh -c "rm -rf /app/vendor"
docker run --rm -v "$(pwd)/apps/admin:/app" alpine sh -c "rm -rf /app/node_modules"

# Przebuduj obrazy z odpowiednim USER_ID
docker compose build

# Uruchom ponownie
docker compose up -d
```

### Problem: Kontenery nie startują po zmianie USER_ID

**Rozwiązanie:**
```bash
# Przebuduj obrazy
docker compose build --no-cache

# Uruchom ponownie
docker compose up -d
```

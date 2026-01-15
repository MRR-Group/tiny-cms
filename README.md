# Tiny CMS Monorepo

Monorepo containing the Slim API and React admin panel with Docker-based dev setup and CI quality gates.

## Structure

```
/apps
  /api       Slim PHP API
  /admin     React + Vite admin
/packages
  /shared    Placeholder shared package
```

## Local development

### Requirements

- Docker + Docker Compose
- Task (optional, for shortcuts)

### Start stack

```bash
docker compose up --build
```

Services:

- API: http://localhost:8080/health
- Admin: http://localhost:5173
- Mailpit UI: http://localhost:8025

### Taskfile shortcuts

```bash
task docker:up
```

## API scripts

Run inside `apps/api` or via Docker:

```bash
composer lint
composer cs
composer cs:fix
composer test
composer mutation
```

## Admin scripts

Run inside `apps/admin`:

```bash
pnpm install
pnpm lint
pnpm test
pnpm build
pnpm storybook
pnpm build-storybook
pnpm mutation
```

## CI gates

Pull requests are blocked if any of the following fail:

- API lint, codestyle, tests, mutation tests
- Admin lint, tests, build, mutation tests
- Storybook build
- PR title format: `- Title` or `#123 - Title`

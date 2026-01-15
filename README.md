# Tiny CMS Monorepo

A minimal Tiny CMS stack with a Slim API, Vite-based admin panel, and shared packages.

## Structure
- `apps/api` — PHP Slim API with PHPUnit, PHPStan, Infection, Phinx migrations.
- `apps/admin` — React + TypeScript admin UI with Vite, Tailwind, ESLint/Prettier, Vitest, Storybook, and Stryker.
- `packages/shared` — Placeholder TypeScript shared library.
- `docker/` — Runtime Dockerfiles for PHP-FPM and Nginx.

## Getting started
Prerequisites: PHP 8.3+, Composer, Node 20+, and pnpm (enable via `corepack enable`).

1. Copy environment files:
   ```bash
   cp apps/api/.env.example apps/api/.env
   cp apps/admin/.env.example apps/admin/.env
   ```
2. Install dependencies:
   ```bash
   task api:install
   task admin:install
   ```
   Composer may need `GITHUB_TOKEN` set to avoid GitHub download rate limits.
3. Run quality checks:
   ```bash
   task api:lint
   task api:test
   task api:mutation
   task admin:lint
   task admin:test
   task admin:mutation
   ```
4. Run the stack with Docker:
   ```bash
   docker compose up --build
   ```
   - API: http://localhost:8080
   - Admin: http://localhost:5173
   - Mailpit: http://localhost:8025
   - Postgres: localhost:5432

## Taskfile shortcuts
Common commands are defined in `Taskfile.yml` for API (composer scripts), admin (pnpm), and Docker helpers.

## CI
GitHub Actions run PHP (lint, cs, tests, mutation, migrations) and TypeScript (lint, tests, mutation, build, Storybook) pipelines with PR title validation.

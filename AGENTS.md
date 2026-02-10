# AGENTS.md - Agent Guide for tiny-cms
This repository is maintained with AI assistance.
Agents must follow this document strictly.
If a request conflicts with these rules, stop and explain the conflict.

## 1) Repository Context
- Monorepo apps:
  - `apps/api`: PHP 8.3 + Slim backend
  - `apps/admin`: React + TypeScript + Vite frontend
- Shared package: `packages/shared`
- Main orchestration: Docker Compose + `Taskfile.yml`
- Backend architectural boundaries enforced by Deptrac.

## 2) Hard Quality Gates
### 2.1 Mutation quality is non-negotiable
- Never lower Infection thresholds in `apps/api/infection.json`.
- Never lower Stryker thresholds in `apps/admin/stryker.config.mjs`.
- Current config is strict (100% threshold expectations).
- If mutation fails: fix code, improve tests, or refactor.

### 2.2 CI integrity
- Never disable/skip/weaken checks in `.github/workflows/*`.
- Never convert failing checks to warnings.
- Never add `continue-on-error` to quality steps.

### 2.3 Tests are mandatory
- Do not delete tests.
- Do not skip tests.
- Do not narrow tests just to bypass failures.
- Do not silence failing assertions.

## 3) Build, Lint, and Test Commands
Run from repo root unless noted.

### 3.1 Preferred task commands (Docker)
- Start stack: `task up`
- Backend install: `task api:install`
- Frontend install: `task admin:install`
- Backend full checks: `task ci:api`
- Frontend full checks: `task ci:admin`
- Full suite shortcut: `task test`

### 3.2 Backend commands (`apps/api`)
- Install dependencies: `composer install`
- Static analysis: `composer lint`
- Code style check: `composer cs`
- Code style fix: `composer cs:fix`
- Unit/feature tests: `composer test`
- Mutation tests: `composer mutation`
- Architecture checks: `composer deptrac`

### 3.3 Frontend commands (`apps/admin`)
- Install dependencies: `pnpm install`
- Dev server: `pnpm dev`
- Lint: `pnpm lint`
- Lint auto-fix: `pnpm lint:fix`
- Format files: `pnpm format`
- Unit/component tests: `pnpm test`
- Coverage run: `pnpm test:coverage`
- Production build: `pnpm build`
- Storybook: `pnpm storybook`
- Storybook build: `pnpm build-storybook`
- Mutation tests: `pnpm mutation`
- E2E tests: `pnpm test:e2e`

## 4) Single-Test Execution (Important)
### 4.1 Backend (PHPUnit)
- Single file:
  - `cd apps/api && vendor/bin/phpunit tests/Unit/Domain/Auth/ValueObject/EmailTest.php`
- Single test name/method via filter:
  - `cd apps/api && vendor/bin/phpunit --filter testNameHere`
- Composer passthrough to phpunit:
  - `cd apps/api && composer test -- --filter testNameHere`

### 4.2 Frontend (Vitest)
- Single file:
  - `cd apps/admin && pnpm vitest run src/domain/site/siteService.test.ts`
- Single test name:
  - `cd apps/admin && pnpm vitest run -t "creates site"`
- File + test name:
  - `cd apps/admin && pnpm vitest run src/components/Button/Button.test.tsx -t "renders"`

### 4.3 Frontend E2E (Playwright)
- Single spec:
  - `cd apps/admin && pnpm playwright test e2e/path/to/spec.test.ts`
- Single title match:
  - `cd apps/admin && pnpm playwright test -g "should log in"`

## 5) Backend Architecture Rules (Mandatory)
Architecture style: DDD-light + Clean Architecture + CQRS-light.

### 5.1 Layers
- Domain: business rules only.
- Application: use-cases (`Command/Query + Handler`).
- Infrastructure: DB/ORM/security/integration adapters.
- Delivery: HTTP controllers, request mapping, response resources.

### 5.2 Dependency boundaries
- Domain must not depend on Slim, HTTP, ORM, DB, or storage.
- Delivery must not directly access ORM internals.
- Infrastructure must not leak technical concerns into Domain APIs.
- Handlers should consume typed commands/queries, never raw HTTP payloads.
- Deptrac in `apps/api/deptrac.yaml` is authoritative.

## 6) Code Style Guidelines
### 6.1 General
- Keep edits minimal, focused, and consistent with nearby code.
- Prefer clarity and explicitness over clever abstractions.
- Add comments only for non-obvious intent (why), not mechanics (what).

### 6.2 Imports
- Keep imports tidy and consistent with surrounding files.
- Use one PHP `use` per line.
- In TS, prefer `@/` alias (`src/*`) for internal modules when practical.
- Reuse existing module boundaries; do not duplicate logic across layers.

### 6.3 PHP conventions (`apps/api`)
- Use `declare(strict_types=1);`.
- Follow PSR-4 namespace mapping (`App\\`, `Tests\\`).
- Keep explicit parameter/property/return types.
- Prefer constructor property promotion and immutable value objects.
- Preserve naming conventions:
  - `*Command`, `*Query`, `*Handler`
  - `*Request`, `*Resource`
  - `*RepositoryInterface`
- Keep controllers thin; put business logic in Application/Domain.
- Validate invariants in value objects/domain constructors.

### 6.4 TS/React conventions (`apps/admin`)
- Keep strict typing (`strict: true`) intact.
- Use explicit interfaces/types for props and API payloads.
- Component names/files: PascalCase.
- Variables/functions: camelCase and descriptive names.
- Co-locate tests near source: `*.test.ts` / `*.test.tsx`.
- Co-locate stories: `*.stories.tsx`.
- Follow existing ESLint/Prettier behavior; do not introduce conflicting style tools.

### 6.5 Error handling
- Fail fast on invalid input with actionable messages.
- Backend: map known exceptions to correct HTTP status + JSON body.
- Frontend: propagate meaningful `Error` messages from failed API requests.
- Never swallow errors silently.

## 7) Agent Workflow Expectations
- Run targeted checks for changed areas before finishing.
- Prefer single-test execution first, then broader suites.
- If checks cannot be run, clearly list exact follow-up commands.
- Keep changes scoped; avoid unrelated refactors.
- Never use destructive git commands unless explicitly requested.

## 8) Cursor / Copilot Rule Files
Checked:
- `.cursor/rules/`
- `.cursorrules`
- `.github/copilot-instructions.md`

Current status in this repository:
- No Cursor rules found.
- No Copilot instruction file found.

If these files are added later, merge their constraints here and follow the most restrictive rule.

This document is mandatory for all AI-generated code in this repository.

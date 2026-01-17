# AGENTS.md – Rules for AI Agents (Codex / Copilot / LLM)

This repository is developed with AI assistance.
AI agents MUST strictly follow the rules defined in this document.

If any requirement is unclear, choose the safest and most explicit solution.
If a task cannot be completed without violating these rules, STOP and explain why.

---

# 1. NON-NEGOTIABLE QUALITY GATES

## 1.1 Mutation testing thresholds
- NEVER lower mutation thresholds (Infection minMsi, minCoveredMsi, Stryker thresholds).
- NEVER modify thresholds to make CI pass.
- If mutation tests fail:
  1. Fix implementation
  2. Add or improve tests
  3. Refactor code to improve testability

Lowering quality metrics is strictly forbidden.

---

## 1.2 CI integrity
- NEVER disable or weaken CI checks
- NEVER convert failing steps to warnings
- NEVER use continue-on-error
- NEVER skip test steps

Example of REQUIRED strict step:

- Run mutation tests
  composer mutation
  working-directory: apps/api

---

## 1.3 Tests must never be removed
- Do not delete tests
- Do not skip tests
- Do not filter tests to avoid failures
- Do not silence failures

---

# 2. ARCHITECTURE (MANDATORY)

This project uses DDD-light + Clean Architecture + CQRS-light.

## 2.1 Layers (STRICT)
- Domain: business rules only
- Application: use-cases (Command / Query + Handler)
- Infrastructure: DB, ORM, storage, security
- Delivery: Slim (REST + GraphQL), Request/Resource mapping

---

## 2.2 Forbidden dependencies
- Domain MUST NOT depend on Slim, HTTP, ORM, DB, Storage
- Delivery MUST NOT use ORM directly
- Infrastructure MUST NOT leak into Domain
- Handlers MUST NOT parse HTTP input directly

---

## 2.3 Command / Query pattern (REQUIRED)
- Every use-case = Command/Query + Handler
- Cross-cutting concerns MUST be middleware
- Validation, authorization, transactions, audit go through pipeline

---

## 2.4 Request / Validator / Resource separation
- Request (Delivery) → parse input → create Command/Query
- Validator (Application) → validate Command/DTO
- Handler (Application) → execute use-case
- Resource (Delivery) → map output to JSON/GraphQL

---

# 3. STYLE RULES

## 3.1 No noise comments
Do NOT add comments like:
- // here I made an if
- // create variable
- decorative headers

Comments are allowed ONLY to explain WHY, not WHAT.

---

# 4. PR / COMMIT RULES
- Do not cheat CI
- Do not weaken checks
- Do not remove steps
- Follow folder structure and naming conventions
- Prefer small, correct commits

---

# 5. FAILURE POLICY
If something fails:
1. Fix code
2. Fix tests
3. Refactor
4. NEVER reduce quality

---

# 6. Follow code architecture saved in ARCHITECTURE.md

AI agents MUST strictly follow the architecture rules defined in `ARCHITECTURE.md`.

- Do not introduce new architectural patterns without explicit approval
- Do not bypass layers or shortcuts around defined boundaries
- If unsure where code belongs, follow ARCHITECTURE.md as the source of truth
- If a task conflicts with ARCHITECTURE.md, STOP and explain the conflict instead of guessing

ARCHITECTURE.md is binding and has higher priority than convenience or speed.
---

This document is mandatory for all AI-generated code.

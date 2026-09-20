---
glob: Modules/**, app/**
---

# Modular Monolith — Boundary Rules

This app is being incrementally retrofitted from a legacy structure into a
modular monolith. Most code still lives under `app/` — that is expected and
temporary, not a mistake to fix opportunistically while doing something else.

## What a module is

`Modules/<Name>/` owns its own database tables, models, controllers,
business logic, and Inertia pages. Its `app/Contracts/` folder is the ONLY
part other modules may depend on. See `modular-monolith-module-structure.md`
for the exact folder tree and naming.

## MUST NOT (no exceptions)

1. Import or reference a class from another module's `Domain/`,
   `Infrastructure/`, or `Http/` namespace. Only `Contracts/` is public.
2. Query, join, or add a foreign key against another module's tables directly
   (no cross-module Eloquent relationships, no raw `DB::table()` joins).
3. Put business logic in a Controller or Inertia response closure —
   Controllers validate → call an Action → return, nothing else.
4. Import a React page/component from another module's `resources/js/` tree
   into a different module. Shared UI goes through `Modules/Shared/` only.
5. Create a new domain concept directly under legacy `app/`. New or
   newly-touched logic goes into a module.
6. Move more than one module's worth of code in a single session — see
   `modular-monolith-migration.md`.
7. Guess which module something belongs to. Stop and ask instead.

## MUST

1. Expose module functionality via `Contracts/`, resolved through the
   container — never via direct instantiation of another module's internals.
2. Route cross-module side effects through Laravel events, not direct calls
   into another module's internals.
3. Keep each module's `CONTRACT.md` current when its public interface changes.
4. Run the boundary checks below before considering a module-touching task done.

## Decision table

| Situation                                          | Action                                                                                                           |
| -------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------- |
| Module A needs data owned by Module B              | Inject B's `Contracts` interface. Never touch B's Eloquent model directly.                                       |
| Something in A should trigger a reaction in B      | Dispatch a domain event from A; B listens via its own listener.                                                  |
| A DTO/value object is needed by two modules        | `Modules/Shared/Domain/`. Never duplicate it into each module.                                                   |
| Not sure which module owns something               | Stop. Ask the user. Do not place it wherever is convenient.                                                      |
| Touching a legacy `app/` file for an unrelated fix | Fix only what's needed. Don't opportunistically migrate it — that's a separate, deliberate task.                 |
| Extracting legacy code into a module               | Follow `modular-monolith-migration.md` exactly, step by step.                                                    |
| A boundary check fails                             | Fix the violation. Do not edit the check config or suppress the failing rule without explicit user confirmation. |

## Required checks

In addition to this project's normal Pint/Pest workflow, run these after any
change touching `Modules/**` or moving code between `app/` and `Modules/**`:

- PHP boundaries: `vendor/bin/deptrac analyse`
- Frontend boundaries: `npx eslint . --ext .jsx,.tsx`

A task with a failing boundary check is not done, regardless of whether the
feature itself works.

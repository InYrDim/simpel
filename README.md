# Modular Monolith Kit — Laravel Boost Edition

Built to slot into a repo that already runs **Laravel Boost** (your
`AGENTS.md` has the `<laravel-boost-guidelines>` block) and already uses
**Pest** as its test runner (confirmed by your Boost `pest/core` rules).
Boost's foundation rules already require the agent to read
`.ai/rules/index.md` and every matching rule file before planning or editing
anything — so instead of building a separate AGENTS.md/CLAUDE.md/Skill
system, this kit plugs directly into that existing mechanism. Nothing here
touches your Boost-generated `AGENTS.md` block, so `php artisan boost:update`
won't wipe it out.

PHP boundary enforcement uses **Pest Arch** rather than a separate static
analysis tool (Deptrac), because Pest is already installed here and Arch
tests run automatically as part of the test suite you already require after
every change — no extra command for the agent to remember.

## What's in here

| File                                             | Purpose                                                                                                        |
| ------------------------------------------------ | -------------------------------------------------------------------------------------------------------------- |
| `.ai/rules/index.md`                             | Glob → rule file map. Add a row here if you add more rule files later.                                         |
| `.ai/rules/modular-monolith-boundaries.md`       | Hard MUST/MUST-NOT rules, decision table, required checks — loaded for anything under `Modules/**` or `app/**` |
| `.ai/rules/modular-monolith-module-structure.md` | Exact folder tree + `CONTRACT.md` template for a module                                                        |
| `.ai/rules/modular-monolith-migration.md`        | Strangler-pattern steps for extracting legacy code into a module                                               |
| `tests/Unit/ModularMonolithArchTest.php`         | Pest Arch test enforcing PHP module boundaries — runs with `vendor/bin/pest` / `php artisan test --compact`    |
| `eslint-boundaries.example.cjs`                  | Enforces React/Inertia module boundaries                                                                       |

## Install

1. Copy the `.ai/rules/` folder into your repo root. If you already have an
   `.ai/rules/index.md`, merge the table rows instead of overwriting it.
2. Install the Pest Arch plugin and copy `tests/Unit/ModularMonolithArchTest.php`
   into your repo's `tests/Unit/` (check first whether you already have an
   `ArchTest.php` there — if so, merge the rules into it instead of having two files):
    ```bash
    composer require pestphp/pest-plugin-arch --dev
    ```
    This one is low-risk to add now, even before your first module exists —
    it's a dev-only addition to a tool you already run, and the one rule it
    ships with (`App` must not depend on `Modules`) is trivially true today.
    It becomes a real guard the moment a module appears, with nothing extra
    to set up.
3. Install `eslint-plugin-boundaries` and merge `eslint-boundaries.example.cjs`
   into your actual ESLint config — this one, unlike the Pest plugin, is
   worth waiting on until your first module exists, since there's nothing
   for it to check yet and it's a new tooling category rather than an
   extension of something already installed:
    ```bash
    npm install --save-dev eslint-plugin-boundaries
    ```

## Extending as you extract modules

Each time you extract a module (`.ai/rules/modular-monolith-migration.md`),
uncomment and adapt the template block in `ModularMonolithArchTest.php` for
that module — e.g. "other modules must not depend on `Billing`'s
`Domain`/`Infrastructure`, only its `Contracts`." No separate config file to
maintain in parallel; it's just more Pest test cases.

One gap worth knowing either way: Arch tests (like Deptrac) see PHP class
references — `use`, `extends`, type hints — not raw strings. A
`DB::table('other_modules_table')` call bypassing a module's `Contracts/`
isn't caught by either tool. That has to be caught in code review.

## Why `.ai/rules` instead of a custom Claude Code Skill

An earlier version of this kit used a `.claude/skills/` Skill, which only
Claude Code reads, and which only loads when Claude judges the task
"complex enough" to consult it — real, but not guaranteed. Boost's
`.ai/rules` mechanism is stronger for a low-reasoning agent because:

- It's **mandatory**, not description-matched: Boost's foundation rules
  already say "you MUST first open `.ai/rules/index.md`... before you enter
  plan mode or create/edit any file."
- It's **path-triggered**, which fits this use case exactly — module
  boundaries are inherently about which files you're touching.
- It works for **both Claude Code and opencode**, since it's just plain text
  inside the `AGENTS.md` both tools already read — not a Claude-specific
  feature.

If you later want a Boost-style skill entry (alongside its bundled
`testing-best-practices` / `inertia-react-development` skills) rather than a
rule file, check Boost's own skill format first — I didn't guess at that
schema here since the boundary rules already get mandatory enforcement
through `.ai/rules` regardless.

## Extending this kit

- Keep rule files scoped and short — Boost loads every matching one per
  task, so a bloated file costs context on every single edit under its glob.
- Register any new rule file in `.ai/rules/index.md` alongside your existing
  `git-workflow.md` / `ui-ux.md` rows.

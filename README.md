# Modular Monolith Kit — Laravel Boost Edition

Built to slot into a repo that already runs **Laravel Boost** (your
`AGENTS.md` has the `<laravel-boost-guidelines>` block). Boost's foundation
rules already require the agent to read `.ai/rules/index.md` and every
matching rule file before planning or editing anything — so instead of
building a separate AGENTS.md/CLAUDE.md/Skill system, this kit plugs directly
into that existing mechanism. Nothing here touches your Boost-generated
`AGENTS.md` block, so `php artisan boost:update` won't wipe it out.

## What's in here

| File | Purpose |
|---|---|
| `.ai/rules/index.md` | Glob → rule file map. Add a row here if you add more rule files later. |
| `.ai/rules/modular-monolith-boundaries.md` | Hard MUST/MUST-NOT rules, decision table, required checks — loaded for anything under `Modules/**` or `app/**` |
| `.ai/rules/modular-monolith-module-structure.md` | Exact folder tree + `CONTRACT.md` template for a module |
| `.ai/rules/modular-monolith-migration.md` | Strangler-pattern steps for extracting legacy code into a module |
| `deptrac.yaml` | Enforces PHP module boundaries — the part that stops the agent from cheating, not just asking nicely |
| `eslint-boundaries.example.cjs` | Enforces React/Inertia module boundaries |

## Install

1. Copy the `.ai/rules/` folder into your repo root. If you already have an
   `.ai/rules/index.md`, merge the table rows instead of overwriting it.
2. Install Deptrac and copy `deptrac.yaml` to your repo root:
   ```bash
   composer require --dev qossmic/deptrac --no-interaction
   ```
   Edit the `layers` section to match your first real module — the file
   ships with a placeholder `Billing`/`Invoicing` example.
3. Install `eslint-plugin-boundaries` and merge `eslint-boundaries.example.cjs`
   into your actual ESLint config:
   ```bash
   npm install --save-dev eslint-plugin-boundaries
   ```

## First run (retrofit, not greenfield)

Don't wire Deptrac into CI as a hard failure on day one — your existing
`app/` tree predates this system. Run it in report-only mode first:

```bash
vendor/bin/deptrac analyse --report-uncovered
```

Once you extract your first module (`modular-monolith-migration.md`), that
module's layer can become a hard CI failure independently, while the rest of
the codebase catches up over time.

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

- Every time you extract a new module, add its Internal/Contract layer pair
  to `deptrac.yaml` (migration playbook, step 7) and register it in
  `.ai/rules/index.md` if it needs its own rule file.
- Keep rule files scoped and short — Boost loads every matching one per
  task, so a bloated file costs context on every single edit under its glob.

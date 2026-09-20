---
glob: Modules/**
---

# Modular Monolith — Module Structure

Copy this tree exactly for every new or extracted module. Replace `<Name>`
with a PascalCase module name (e.g. `Billing`, `Invoicing`, `Notifications`).
If `nwidart/laravel-modules` is installed, prefer `php artisan module:make
<Name> --no-interaction` and then adjust to match this shape rather than
building it by hand.

```
Modules/<Name>/
├── CONTRACT.md
├── app/
│   ├── Contracts/            # PUBLIC. Only namespace other modules may use.
│   │   └── <Name>Contract.php
│   ├── Domain/                # Business logic. Private to this module.
│   │   ├── Models/
│   │   ├── Actions/            # one class per business operation
│   │   └── Events/
│   ├── Infrastructure/         # Private to this module.
│   │   ├── Repositories/
│   │   └── Providers/
│   └── Http/                   # Private to this module.
│       ├── Controllers/
│       ├── Requests/
│       └── Resources/
├── resources/
│   └── js/
│       ├── Pages/               # Inertia pages for this module only
│       └── Components/          # Components used only within this module
├── routes/
│   ├── web.php
│   └── api.php
├── database/
│   ├── migrations/
│   ├── factories/
│   └── seeders/
├── tests/
│   ├── Feature/
│   └── Unit/
└── Providers/
    └── <Name>ServiceProvider.php   # registers routes, views, migrations
```

Rules for this structure:

- Nothing outside `app/Contracts/` is importable by another module — enforced
  by `deptrac.yaml` at the repo root.
- `<Name>ServiceProvider.php` is the only place that wires this module into
  the app. Register it in `bootstrap/providers.php`.
- `Modules/Shared/` uses this same shape for code with no single owning
  module. Treat `Modules/Shared/app/Domain` as its own contract surface too —
  don't reach into its internals from elsewhere either.

## CONTRACT.md template

Every module needs one at `Modules/<Name>/CONTRACT.md`. Fill in every
section — no "TBD" placeholders in a committed contract.

```markdown
# Module: <Name>

## Owns

- Database tables: <list>
- Core domain concepts: <one-line description of what this module is responsible for>

## Public interface (Contracts/)

- `<Name>Contract` — <what it does, in one line>

## Allowed dependencies

- Modules/Shared
- <any other module's Contracts/ this module is allowed to depend on, if any>

## Events published

- `<EventName>` — fired when <condition>, payload: <fields>

## Events consumed

- `<EventName>` from `<OtherModule>` — handled by <ListenerName>, does <what>

## Explicitly NOT exposed

- <anything intentionally kept internal that might look tempting to reach into>

## Notes for maintainers

- <anything a future agent/dev needs to know that isn't obvious from the code>
```

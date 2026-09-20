<?php

/**
 * Modular monolith boundary rules, enforced via Pest Arch.
 *
 * These run automatically with `vendor/bin/pest` / `php artisan test --compact`
 * — no separate command to remember. See
 * .ai/rules/modular-monolith-boundaries.md for the full rule set this file
 * enforces, and .ai/rules/modular-monolith-migration.md for when/how to add
 * a block for a newly-extracted module.
 *
 * Requires: composer require pestphp/pest-plugin-arch --dev
 *
 * IMPORTANT: as each module is extracted, add a matching block below for it.
 * Until a module exists, the rule below is trivially true — it exists so the
 * guard is already active the moment a module appears, with nothing extra
 * to remember to set up.
 */
arch('legacy app code does not depend on Modules internals')
    ->expect('App')
    ->not->toUse('Modules');

// --- Add one block per extracted module. Example — uncomment and rename
//     once you extract your first module (e.g. Billing):
//
// arch('other modules do not depend on Billing internals')
//     ->expect('Modules')
//     ->not->toUse('Modules\Billing\app\Domain')
//     ->ignoring('Modules\Billing');
//
// arch('other modules do not depend on Billing infrastructure')
//     ->expect('Modules')
//     ->not->toUse('Modules\Billing\app\Infrastructure')
//     ->ignoring('Modules\Billing');
//
// arch('Billing contracts have no outward dependency on the rest of the app')
//     ->expect('Modules\Billing\app\Contracts')
//     ->not->toUse('App');

/**
 * NOTE: this catches cross-module dependencies expressed as PHP class
 * references (use, extends, type hints). It does NOT catch a raw
 * `DB::table('other_modules_table')` call — that's a string, not a class
 * reference, and no static tool (Arch or Deptrac) sees it. Watch for that
 * pattern in code review.
 */

---
glob: Modules/**, app/**
---

# Modular Monolith — Migration Playbook

Read this in full before extracting any legacy code into a module. Do not
skip steps or batch them "to save time" — the incremental approach is the
entire point of a retrofit.

## Ground rule

**One module. One session (or a few small sessions). Never a big-bang rewrite.**
If a proposed extraction touches more than one bounded concept, stop and ask
whether it should be split into separate extractions.

## Steps

1. **Pick the target.** One bounded concept (e.g. "Invoicing"), ideally the
   one with the fewest existing cross-references in the legacy code. Confirm
   scope with the user if ambiguous.

2. **Create the empty module skeleton first**, per
   `modular-monolith-module-structure.md`. Don't move any files yet.

3. **Inventory what needs to move** — every model, controller, migration,
   Inertia page, and test file for this concept. Share this list before
   moving anything if the task is large.

4. **Move one file at a time:**
    - Move the file into its new module location.
    - Update its namespace.
    - Update every place that referenced the old namespace (search the whole
      repo, not just the obvious callers).
    - Run the relevant test immediately: `vendor/bin/pest --filter=testName`
      or the narrowest file path, per this project's Pest conventions.
    - Only move to the next file once this one passes.
    - Run `vendor/bin/pint --dirty --format agent` after each batch of PHP
      file moves, per this project's normal formatting workflow.

5. **Add `Contracts/` last**, after internals are moved. Define the minimal
   public interface other code actually needs. Update legacy callers to
   depend on the contract instead of the moved class, where they can't be
   migrated into the module themselves.

6. **Write `CONTRACT.md`** using the template in
   `modular-monolith-module-structure.md`.

7. **Add the module to `deptrac.yaml`** as its own Internal + Contract layer
   pair, and add its Contract layer to the `Legacy` ruleset's allowed
   dependencies (Legacy may depend on the new module's Contract layer, never
   its Internal layer).

8. **Run `vendor/bin/deptrac analyse`.** Expect pre-existing violations
   elsewhere in the codebase unrelated to this extraction — normal in a
   retrofit. Only the module you just extracted needs to be clean. Do not
   fix unrelated violations in this task.

9. **Run the full suite once** — `php artisan test --compact` — not just the
   module's tests, before calling the extraction done. Legacy code may have
   implicitly relied on the old file location.

10. **Stop.** Do not chain into extracting a second module in the same
    session unless explicitly asked.

## Signs you're doing it wrong

- More than ~5–10 files moved without running a test in between.
- Touching files that belong to a different bounded concept than the one you
  started extracting.
- Tempted to "just inline" a dependency on another module's internals to make
  the extraction easier — that's exactly what the boundary check exists to
  catch; don't route around it.
- Editing `deptrac.yaml` to silence a failure instead of fixing the actual
  dependency.

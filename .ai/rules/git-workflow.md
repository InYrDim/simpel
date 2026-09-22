# Git Workflow

## Branches

Alur promosi satu arah — jangan lompati tahap:

```
feat/<nama>  ->  test  ->  staging  ->  main
```

- **PR fitur selalu menuju `test`**, bukan `staging`. Branch kerja dipotong dari
  `test` (lihat `base_branch` di `treehouse.toml`).
- `test` -> `staging` dijalankan sebagai promosi setelah `test` stabil.
- `staging` -> `main` hanya untuk rilis.
- Rujukan "PR ke `staging`" di dokumen yang lebih lama dibaca sebagai PR ke
  `test`. Alur ini berlaku sejak 22 Sep 2026.

## Commits

- **Never auto-commit.** Always ask the user before running `git commit`.
- Do not stage and commit in the same turn without explicit user approval.
- After making changes, just tell the user what was done and wait for them to say "commit" or similar.

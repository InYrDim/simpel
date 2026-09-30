---
target: Mahasiswa list page
total_score: 22
max_score: 40
na_heuristics:
p0_count: 1
p1_count: 2
target_identity: "file:C:\\Users\\iyede\\code\\___mine____\\skibidi\\resources\\js\\pages\\akademik\\mahasiswa\\index.tsx"
target_fingerprint: 'sha256:43fae4d3ed12bd133dc5760e662d835bff466a09f079fd205a3f83fe3cbc7be0'
target_path: "C:\\Users\\iyede\\code\\___mine____\\skibidi\\resources\\js\\pages\\akademik\\mahasiswa\\index.tsx"
timestamp: 2026-09-30T12-03-06Z
slug: resources-js-pages-akademik-mahasiswa-index-tsx
---

Method: dual-agent (A: design review, B: detector + grep evidence, isolated)

# Critique: halaman daftar Mahasiswa

Target: resources/js/pages/akademik/mahasiswa/index.tsx + components/mahasiswa-columns.tsx. Source-only review (no browser tool available).

## Design Health Score: 22/40 (55%, Acceptable)

| #   | Heuristic                       | Score | Key Issue                                                      |
| --- | ------------------------------- | ----- | -------------------------------------------------------------- |
| 1   | Visibility of System Status     | 3     | No processing state on edit/delete; no search loading feedback |
| 2   | Match System / Real World       | 3     | "Akun User" mixed language; "Dosen PA" unexplained             |
| 3   | User Control and Freedom        | 2     | No undo after delete; search cannot be cleared                 |
| 4   | Consistency and Standards       | 3     | Identical to Prodi/Dosen; raw <p> errors instead of InputError |
| 5   | Error Prevention                | 2     | Delete dialog lists no linked data; angkatan/NIM unconstrained |
| 6   | Recognition Rather Than Recall  | 2     | Icon-only row actions; placeholder-only search label           |
| 7   | Flexibility and Efficiency      | 1     | No page controls, filter, sort, bulk, import                   |
| 8   | Aesthetic and Minimalist Design | 3     | Clean but flat                                                 |
| 9   | Error Recovery                  | 2     | No error summary or focus-to-first-error                       |
| 10  | Help and Documentation          | 1     | No inline help                                                 |

## Design Specificity Verdict

Fails: generic shadcn admin scaffold; identical to the Prodi page. Detector: 0 findings on target, components/ui and app.css; it cannot see the structural problems.

## Priority Issues

- [P0] Students beyond row 10 are unreachable: controller paginate(10) (MahasiswaController.php:42), UI has no page controls (index.tsx:110-113). Fix: pagination via current_page/last_page keeping query string. Command: layout, then harden.
- [P1] Table too shallow for the domain: only free-text search, no prodi/angkatan filter or sort, generic empty state without colSpan. Command: shape, then layout.
- [P1] Row actions risky and heavy: delete beside edit at same size; per-row useForm plus two dialogs; edit form initialised once from props (stale on reopen). Command: harden.
- [P2] Search input has no accessible name and fires per keystroke (onChange on form, no debounce, no preserveState/replace). Command: harden.
- [P2] Create dialog does not scale: unsearchable Akun User/Dosen/Prodi selects, raw <p> errors, no save-and-add-another. Command: clarify, then polish.

## Persona Red Flags

- Alex: no pagination/sort/filter/bulk; modal per row.
- Sam: unlabeled search; delete vs edit differs by glyph only; empty-state cell lacks colSpan; no aria-live on dialog errors; nowrap cells scroll actions off at 200% zoom.
- Bu Rina (coordinator, 200 students): unsearchable user select, retyped fields, dialog reopen per student, no CSV import.

## Minor Observations

- Akademik and Mahasiswa breadcrumbs share one URL.
- Missing Dosen PA shows "-".
- DosenOption/ProdiOption types declared twice.
- "Tambah Mahasiswa" uses size sm.

## Questions to Consider

- Why is a multi-field student record a modal rather than a detail page with history?
- Should hard-delete exist, or Nonaktifkan/Lulus/Cuti with a status column?
- What survives if this page were designed only for a registrar's Monday morning?

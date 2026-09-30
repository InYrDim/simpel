# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users

**Primary:** Institutional administrators (university/school leaders, departmental heads, academic coordinators) managing programs, departments, roles, permissions, and institutional data.

**Secondary:** Faculty and academic supervisors managing students, thesis submissions, and academic progression.

**Tertiary:** Students viewing coursework, submitting thesis work, and tracking their academic records.

## Product Purpose

A modular institutional management system (ERP-style) for universities and schools to manage complete academic and operational workflows: student enrollment, academic program (Prodi) administration, departmental (Jurusan) organization, thesis tracking (Skripsi), user roles and permissions, and institutional reporting. Success means institutional staff can manage academic workflows without custom development, faculty can track student progress transparently, and students have a clear interface for their academic responsibilities.

## Positioning

Modular monolith designed to evolve institutional workflows without the rigidity of traditional monolithic ERPs. Role-based access ensures each user group sees only relevant tools; clear module boundaries (Akademik, Manajemen, Skripsi) allow gradual feature development without affecting the entire system.

## Operating Context

- University/school administration and academic governance
- Role-based workflows: admin → full system, kapprodi/kapjurusan → program/department oversight, faculty → student/thesis management, students → personal academic record
- Thesis tracking as a core academic workflow (submission, review, completion)
- Regular institutional reporting and data export (CSV) for compliance and analysis
- Multi-tenant data isolation by institution (if applicable; to be confirmed)

## Capabilities and Constraints

**Confirmed Capabilities:**

- Multi-module architecture: Akademik (programs, students), Manajemen (departments, users, roles), Skripsi (thesis submissions, monitoring)
- Spatie Laravel permission system with UI-driven role/permission management
- CSV export for data reporting
- Student/Mahasiswa records with program (Prodi) assignment and supervision tracking
- Department/Jurusan management with hierarchical organization

**Technical Constraints:**

- Laravel 13 + React 19 + Inertia 3 (modern stack; future versions lock to these major versions unless user decides otherwise)
- Module boundaries enforce via Pest Arch tests (no cross-module imports of non-Contract classes)
- TypeScript frontend with strict type checking

**Explicitly Undecided:**

- Multi-tenancy model (single or multiple institutions per deployment)
- Student-facing features vs. admin-only portal (current work is admin/faculty focused; student portal scope TBD)
- Expansion to additional workflows (finances, scheduling, communications, etc.)

## Evidence on Hand

- Active Laravel Boost project with modular structure and Pest test suite (162+ tests passing)
- Committed modules: Akademik, Manajemen, Skripsi, Contracts (core interfaces)
- Recent work: Prodi CRUD, Jurusan CRUD, Peran (role) management, CSV export
- React components for admin UI built with Radix UI + Tailwind
- No design system documentation or visual identity standards; UI is utilitarian and pattern-emerging

## Product Principles

1. **Modular evolution:** Each domain (academics, management, thesis) is a clear boundary; new features extend modules rather than span them.
2. **Role-driven access:** Every user group sees only what they need; permission system is the source of truth.
3. **Institutional scale:** Designed for actual university workflows, not generic dashboards; support for hierarchical roles (kapprodi, kapjurusan) and supervision chains.
4. **Data transparency:** Clear pathways to export and analyze institutional data (CSV, reporting views).
5. **Type safety:** Backend (PHPStan level 7) and frontend (TypeScript strict mode) enforce correctness; tests are primary specification.

# Clinic Information System Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build the operational Laravel 13 clinic system defined by the PRD and visual design, backed by MariaDB and verified through automated tests and Playwright.

**Architecture:** Use server-rendered Blade modules protected by policies and permission middleware. Keep controllers thin by placing transactions, immutable hash chains, queue allocation, dispensing, merge resolution, and reporting in focused actions/services. Use UUID Eloquent models and MariaDB constraints/triggers, with SQLite-compatible application guards for the automated suite.

**Tech Stack:** PHP 8.3, Laravel 13, Blade, Tailwind CSS 4, Alpine.js 3, Vite 8, MariaDB/InnoDB, Redis-compatible Laravel drivers, PHPUnit 12, Playwright.

---

### Task 1: Foundation, environment, and authentication

**Files:**
- Modify: `.env`, `.env.example`, `config/app.php`, `config/database.php`, `bootstrap/app.php`, `composer.json`, `package.json`, `resources/js/app.js`, `resources/css/app.css`
- Modify: `database/migrations/0001_01_01_000000_create_users_table.php`, `app/Models/User.php`, `routes/web.php`
- Create: `app/Http/Controllers/Auth/*`, `app/Http/Requests/Auth/*`, `app/Http/Middleware/*`, `resources/views/auth/*`, `resources/views/layouts/*`, `resources/views/components/*`
- Test: `tests/Feature/Auth/AuthenticationTest.php`, `tests/Feature/SecurityHeadersTest.php`, `tests/Feature/TimezoneTest.php`

- [ ] Write feature tests for login, inactive-account rejection, throttling, logout, protected routes, WIB configuration, and security headers.
- [ ] Run the focused tests and confirm they fail because the authentication routes and middleware do not exist.
- [ ] Implement UUID users, account status fields, login/logout/reset request flow, role helpers, throttling, no-store/CSP middleware, and WIB/MariaDB session configuration.
- [ ] Install Alpine.js, register bundled UI components, and define Tailwind v4 semantic tokens from `Desain.md`.
- [ ] Re-run focused tests and the frontend build; confirm they pass.

### Task 2: RBAC, audit trail, and seed data

**Files:**
- Create: `database/migrations/*_create_access_and_audit_tables.php`
- Create: `app/Models/Role.php`, `app/Models/Permission.php`, `app/Models/AuditEvent.php`
- Create: `app/Services/AuditTrail.php`, `app/Http/Middleware/RequirePermission.php`
- Create: `database/seeders/ClinicSeeder.php`
- Modify: `database/seeders/DatabaseSeeder.php`, `app/Models/User.php`, `bootstrap/app.php`
- Test: `tests/Feature/Authorization/RoleAccessTest.php`, `tests/Feature/Audit/AuditTrailTest.php`

- [ ] Write tests proving registration cannot read diagnoses, clinical users can access assigned clinical screens, owner reports are aggregate, and denied access is audited.
- [ ] Run the tests and confirm missing roles, permissions, and audit storage produce expected failures.
- [ ] Implement role/permission schema, middleware, audit hash chaining, actor/request metadata capture, and seed all clinic roles/demo accounts.
- [ ] Re-run focused tests and confirm authorization and audit assertions pass.

### Task 3: Patients, identifiers, allergies, duplicate detection, and merge

**Files:**
- Create: `database/migrations/*_create_patient_tables.php`
- Create: `app/Models/Patient.php`, `PatientIdentifier.php`, `PatientGuardian.php`, `AllergyEntry.php`, `PatientMergeCase.php`, `PatientMergeEvent.php`
- Create: `app/Actions/Patients/RegisterPatient.php`, `AttachPatientIdentifier.php`, `MergePatients.php`
- Create: `app/Http/Controllers/PatientController.php`, `PatientIdentifierController.php`, `PatientMergeController.php`
- Create: `app/Http/Requests/Patients/*`, `app/Policies/PatientPolicy.php`
- Create: `resources/views/patients/*`, `resources/views/merges/*`
- Test: `tests/Feature/Patients/PatientIdentityTest.php`, `tests/Feature/Patients/PatientMergeTest.php`

- [ ] Write the baby-without-NIK, later-NIK, unique-NIK, guardian requirement, duplicate-hint, allergy-indicator, and Siti Aminah merge tests.
- [ ] Run the focused tests and verify they fail for missing patient domain behavior.
- [ ] Implement MRN generation, encrypted identifiers with normalized hashes, guardian validation, append-only allergies, duplicate scoring, canonical resolution, merge/unmerge events, policies, controllers, and Blade screens.
- [ ] Re-run the focused tests and confirm the patient scenarios pass.

### Task 4: Schedules, registration, queue, triage, visits, and encounters

**Files:**
- Create: `database/migrations/*_create_operations_tables.php`
- Create: models for schedules, registrations, queue tickets/events/counters, visits, triage records, and encounters
- Create: `app/Actions/Queue/AllocateQueueTicket.php`, `CheckInRegistration.php`, `PrioritizeQueueTicket.php`, `CreateInternalReferral.php`
- Create: controllers, Form Requests, policies, and `resources/views/queue/*`, `resources/views/encounters/*`
- Test: `tests/Feature/Queue/QueueWorkflowTest.php`, `tests/Feature/Encounters/MultiEncounterTest.php`

- [ ] Write tests for active provider schedules, atomic unique queue numbers, check-in, patient 23 priority override with reason, one visit, and two encounters.
- [ ] Run the focused tests and verify the operations domain is missing.
- [ ] Implement transactional counter locking with retry, status events, triage/vitals, visit creation, queue board queries, encounter ownership, internal referrals, and responsive operations views.
- [ ] Re-run focused tests and confirm queue and multi-encounter behavior passes.

### Task 5: Immutable clinical records, addenda, and diagnoses

**Files:**
- Create: `database/migrations/*_create_clinical_tables.php`
- Create: `app/Models/ClinicalDraft.php`, `ClinicalEntry.php`, `DiagnosisEntry.php`
- Create: `app/Actions/Clinical/FinalizeClinicalDraft.php`, `CreateClinicalAddendum.php`
- Create: controllers, requests, policy, timeline Blade components, and immutable trigger migration
- Test: `tests/Feature/Clinical/ClinicalRecordTest.php`, `tests/Feature/Clinical/DatabaseImmutabilityTest.php`

- [ ] Write tests for author-only drafts, required finalization fields, immutable final entries, hash chaining, absent edit/delete routes, diagnosis privacy, and addenda retaining the original.
- [ ] Run tests and confirm they fail because clinical persistence is absent.
- [ ] Implement draft save/finalize transactions, structured entries/diagnoses, application immutable guards, MariaDB update/delete triggers, audit events, and chronological timeline UI.
- [ ] Run SQLite focused tests, then run MariaDB trigger checks and confirm update/delete statements are rejected.

### Task 6: Prescriptions, pharmacy, substitutions, dispensing, and stock

**Files:**
- Create: `database/migrations/*_create_pharmacy_tables.php`
- Create: models for prescriptions/items/events, medicines/batches/movements, substitutions/events, dispensings/items
- Create: `app/Actions/Pharmacy/*`
- Create: controllers, requests, policies, and `resources/views/pharmacy/*`, `resources/views/medicines/*`
- Test: `tests/Feature/Pharmacy/PrescriptionCorrectionTest.php`, `SubstitutionWorkflowTest.php`, `StockConsistencyTest.php`

- [ ] Write tests for required prescription fields, allergy acknowledgement, immutable final prescriptions, 09:15/11:40 correction history, verbal approval/ratification, expired-batch rejection, and non-negative stock.
- [ ] Run focused tests and verify the pharmacy behavior is missing.
- [ ] Implement prescription finalization/correction, status events, verbal substitution events, dispensing transaction, FEFO batch validation, and append-only stock movements.
- [ ] Re-run focused tests and confirm all pharmacy scenarios pass.

### Task 7: Record copies, reports, dashboard, and public design

**Files:**
- Create: `database/migrations/*_create_document_tables.php`
- Create: `app/Models/MedicalRecordCopyRequest.php`, `GeneratedDocument.php`, `DocumentAccessEvent.php`
- Create: `app/Services/ClinicReport.php`, `app/Http/Controllers/DashboardController.php`, `ReportController.php`, `MedicalRecordCopyController.php`
- Create/modify: `resources/views/welcome.blade.php`, `resources/views/dashboard.blade.php`, `resources/views/reports/*`, `resources/views/record-copies/*`, reusable components and icons
- Test: `tests/Feature/Documents/MedicalRecordCopyTest.php`, `tests/Feature/Reports/ClinicReportTest.php`, `tests/Feature/PublicLandingPageTest.php`

- [ ] Write tests for copy approval gates, document metadata/checksum, completed-visit versus finalized-encounter counts, canonical unique-patient count, top diagnoses, provider workload, and landing-page content.
- [ ] Run focused tests and verify they fail for missing documents/reports/UI.
- [ ] Implement controlled copy workflow, private document metadata, reporting queries, role-aware dashboard, and the responsive public page matching the clean white/teal design.
- [ ] Re-run focused tests and production asset build.

### Task 8: MariaDB deployment, backups, full verification, and memory

**Files:**
- Create: `docs/operations/database.md`, `docs/operations/backup-and-restore.md`, `scripts/backup-mariadb.ps1`, `scripts/verify-restore.ps1`
- Modify: `.env.example`, `README.md`
- Test: full PHPUnit suite, Pint, Vite build, MariaDB migration/seed, Playwright desktop/mobile smoke tests

- [ ] Start/connect to local MariaDB, create the `medis` database, update local environment, and run `migrate:fresh --seed`.
- [ ] Verify session timezone, InnoDB/utf8mb4, immutable triggers, table constraints, and seeded records through SQL queries.
- [ ] Document separated production accounts, Redis/private-storage settings, encrypted backup/retention, and restore drill commands; provide safe PowerShell helpers.
- [ ] Run `php artisan test`, `vendor/bin/pint --test`, `npm run build`, and database checks from a clean state.
- [ ] Use Playwright at `http://medis.test/` on desktop and mobile to verify public page, authentication, dashboard, core workflows, accessibility snapshot, network errors, and console errors.
- [ ] Review the PRD acceptance checklist, record any environment-only gaps, and save final architecture/changes/verification results to Agent Memory.


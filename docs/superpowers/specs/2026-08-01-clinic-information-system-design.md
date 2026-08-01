# Clinic Information System Design

**Source of truth:** `PRD_Sistem_Informasi_Klinik_Laravel13_MariaDB10.11_Tailwind_Alpine_WIB.md` and `Desain.md`

## Goal

Deliver an operational Laravel 13 clinic information system for Klinik Pratama Sehat Bersama that demonstrates the complete daily flow from patient registration to dispensing, preserves immutable clinical records, enforces role-based privacy, and exposes management reports in WIB.

## Delivery Scope

The first delivery implements every PRD Must Have as a cohesive server-rendered application:

- Individual login, account status, role/permission checks, authorization policies, login throttling, and audit events.
- Patient registration without NIK, later identifier attachment, guardians, allergy safety flags, duplicate hints, and logical patient merge.
- Provider schedules, phone/front-desk registration, atomic queue numbering, check-in, triage, priority override, visits, and multiple encounters.
- Editable clinical drafts, transactional finalization, append-only clinical entries, database immutability triggers, addenda, structured diagnoses, and integrity hashes.
- Electronic prescriptions, corrections, pharmacy status events, substitution requests, verbal approval/ratification, dispensing, medicine batches, and append-only stock movements.
- Controlled medical-record-copy requests and a report dashboard for visit, encounter, unique-patient, diagnosis, and provider-workload metrics.
- A public landing page and an authenticated operations shell following `Desain.md`.

Two-factor authentication, production object storage, Redis availability, physical backup tooling, and MariaDB account separation are represented by configuration and operational documentation where the local workstation cannot prove the external infrastructure.

## Architecture

Laravel Blade remains the source of markup. Controllers accept Form Requests and delegate state changes to focused action/service classes. Eloquent models own relationships and read-only query helpers; policies and a permission middleware protect resources. Critical operations use `DB::transaction(..., attempts: 5)` and database constraints.

The database is organized into identity/access, patient, operations, clinical, pharmacy, documents, and audit domains. UUID primary keys are generated with Laravel `HasUuids`. Clinical entries, prescriptions, stock movements, and audit events form append-only chains using SHA-256 hashes. MariaDB triggers reject update/delete operations on immutable tables; application model events provide a matching guard for SQLite tests.

## Data Flow

1. Registration finds or creates a patient, attaches optional identifiers/guardian data, and allocates a queue number inside a locked transaction.
2. Check-in creates one visit. Triage records vital context and may add a priority override event without replacing the original queue number.
3. Each service creates a separate encounter under the visit. A provider saves a draft and finalizes it into immutable clinical entries and diagnoses.
4. A finalized prescription appears in pharmacy. Pharmacy records validation, substitution, dispensing, and stock movements as new events.
5. Reports count completed visits separately from finalized encounters and resolve merged patients through their canonical identity.
6. An audit service records sensitive reads and state changes with actor, active role, request context, outcome, previous hash, and integrity hash.

## Authorization

Seeded roles are owner, doctor, dentist, nurse, pharmacist, registration, and system administrator. Permissions use explicit resource/action codes. Registration staff can manage identity, registration, and queue data but cannot read diagnoses or clinical entries. Owners receive aggregate reports by default. Clinical and pharmacy screens use policies in addition to navigation visibility.

## User Interface

The public page uses a clean white clinical direction, teal `#2FA791`, pale teal surfaces, blue information accents, soft shadows, Poppins headings, and Nunito Sans body text. It includes hero, services, care team, schedule/location, testimonials, contact/footer, mobile navigation, and an appointment modal.

The internal application uses the same tokens with a high-information operations layout: responsive sidebar, status bar, metric cards, queue board, safety alerts, tables, forms, and timelines. Components have visible labels, 44px minimum targets, keyboard focus rings, text/icon status cues, `x-cloak`, reduced-motion handling, and no storage of clinical data in browser storage.

## Error Handling and Security

- Validation errors return to the form with Indonesian messages and retained non-sensitive input.
- Authorization failures are audited and return HTTP 403 without leaking resource data.
- Critical forms use CSRF protection, idempotency tokens, server-side transactions, and submit disabling.
- Clinical pages send private/no-store cache headers and a restrictive Content Security Policy.
- NIK values are encrypted at rest and searched through normalized hashes.
- Expired/blocked medicine batches cannot be dispensed and stock cannot become negative.

## Testing

The automated suite covers authentication/authorization, patient-without-NIK and later NIK, atomic queue allocation, priority override, one visit with multiple encounters, immutable clinical entries and addenda, prescription correction, verbal substitution approval, stock consistency, record-copy workflow, patient merge, report calculations, and WIB configuration. Browser verification covers desktop/mobile public pages, login, dashboard, core navigation, console errors, and responsive layout.

## Environment Decisions

- Target runtime remains Laravel 13, PHP 8.3, Tailwind CSS 4, Alpine.js 3, MariaDB 10.11, Redis, and private storage.
- The current XAMPP bundle exposes MariaDB 10.4.32, so local verification uses compatible SQL while recording the version gap. Deployment must use MariaDB 10.11.
- The current Codex session does not expose DBHub or Laravel Boost MCP tools. Equivalent inspection uses MariaDB CLI, Artisan, Context7, and Playwright; the repository remains ready for Boost installation/configuration separately.


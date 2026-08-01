# Frontend and AI Chatbot Integration Design

## Status

Approved through Superpowers brainstorming on 2026-08-01.

## Goal

Integrate the visual language from the untracked `template/` folder into the existing Laravel 13 clinic application without replacing its working routes, RBAC, actions, transactions, audit trail, or database model. Add an OpenAI-powered chatbot that can directly execute bounded front-office operations for authenticated patients and authorized staff.

The result must provide:

- A public clinic landing page with public schedule information and no chatbot access.
- Patient account registration, email verification, staff approval, and secure linking to one patient record.
- A patient portal with appointment management, self check-in, queue status, visit history, and chatbot access.
- A staff application that preserves current role-based modules and adds patient-link approval plus an operational AI assistant.
- OpenAI function tools that can execute only approved scheduling, registration, check-in, queue, and visit-history operations.
- Minimal conversation retention and complete structured auditing of AI-assisted reads and mutations.

## Approved Decisions

- Frontend strategy: unified functional skin.
- AI provider: OpenAI directly through the Responses API.
- AI execution strategy: strict Laravel tool gateway.
- Public users: schedule information only.
- Chatbot users: authenticated, staff-approved patients and authenticated staff.
- Patient identity verification: staff approval only. No OTP integration.
- Patient chatbot scope: own appointments, own check-in, own queue status, and own visit-history summary.
- Staff chatbot scope: schedule, patient registration, appointment operations, check-in, queue information, and visit-history summary according to RBAC.
- Conversation retention: no permanent raw transcript.
- Git delivery: focused commits pushed directly to `origin/main` at `https://github.com/GLIWebDeveloper2026/SC_Rekam-Medis.git`.

## Existing System Constraints

The current application is the source of truth. It already contains:

- Laravel 13, PHP 8.3, Blade, Tailwind CSS 4, Alpine.js CSP, Vite, PHPUnit 12, and MySQL/MariaDB.
- UUID user, role, permission, patient, schedule, registration, queue, visit, clinical, pharmacy, document, and audit domains.
- Existing authenticated staff workflows and 35 application routes.
- Transactional queue registration and check-in actions.
- Append-only audit events and immutable clinical/pharmacy records.

The `template/` folder is a visual reference, not a second application to merge wholesale. Its routes, Tailwind v3 setup, localStorage role simulation, inline Alpine state, fake alerts, and simulated AI responses must not replace production behavior. Only reusable visual structure, copy, hierarchy, and component ideas are adapted into the primary application.

## Visual Direction

Reading this as a trust-first healthcare portal for patients and staff, using a modern clinical language and evolving the existing teal identity.

- `DESIGN_VARIANCE: 5`
- `MOTION_INTENSITY: 4`
- `VISUAL_DENSITY: 6`
- Primary accent: existing clinic teal family based on `#2FA791`.
- Typography: Poppins headings and Nunito Sans body text, self-hosted through approved frontend assets rather than remote Google Fonts.
- Icons: keep the existing Lucide package as the single icon family because it is already installed and integrated.
- Shape rule: buttons use a 10px radius, inputs use a 10px radius, panels use a 16px radius.
- Theme rule: light-first clinical theme with semantic tokens prepared for system dark mode. Each page uses one consistent theme at a time.
- Motion rule: short drawer, reveal, loading, and success transitions only. All motion respects `prefers-reduced-motion`.
- Responsive rule: desktop sidebars and chat drawers collapse to mobile navigation and a full-width chatbot bottom sheet.

The public landing page uses an asymmetric hero with a real healthcare image, concise value copy, registration and login actions, services, public provider schedules, location, and contact information. It does not include an AI chat entry point.

The patient portal uses a compact top navigation, next appointment, current queue state, visit-history summary, account-approval state, and an AI drawer. The staff application keeps its permission-aware sidebar and adds account-link approval and a contextual AI panel.

## User and Patient Account Model

The existing `users` table remains the authentication source. A seeded `patient` role is added with no staff permissions.

A new `patient_portal_accounts` table links an account to a patient record:

- `id` UUID primary key.
- `user_id` unique foreign key.
- `patient_id` nullable unique foreign key until approval.
- `status`: `pending`, `approved`, `rejected`, or `suspended`.
- Claimed identity fields required for staff review: full name, birth date, phone, optional medical record number, and an optional normalized identifier hash. Raw NIK is not stored in this table.
- `reviewed_by`, `reviewed_at`, and `review_notes`.
- Timestamps.

Patient registration flow:

1. A visitor creates an account with name, username, email, password, birth date, phone, and optional medical record number.
2. Laravel email verification must complete before a link request can be reviewed.
3. The account receives the `patient` role and a pending portal record.
4. Authorized registration staff review candidate matches using existing patient identifiers and demographic data.
5. Approval links exactly one `user_id` to exactly one `patient_id`.
6. Rejection stores a concise reason and permits a corrected resubmission.
7. Only approved and active patient accounts may use the patient portal or chatbot.

Patient account approval is always a manual staff action in a dedicated screen. It is not exposed as an AI tool.

## Frontend Surfaces and Components

### Public

- `resources/views/welcome.blade.php`: redesigned landing page with public schedules.
- Public schedule section backed by active provider schedules and schedule exceptions.
- Patient registration, patient login, and staff login entry points.
- No protected data, queue state, visit history, or chatbot markup.

### Patient Portal

- Patient-specific layout and navigation.
- Pending, rejected, suspended, and approved account states.
- Dashboard with next appointment, current queue status, recent visits, and common actions.
- Appointment list and appointment detail.
- Chat drawer on desktop and bottom sheet on mobile.
- No use of localStorage for user identity, clinical data, messages, or tool results.

### Staff Application

- Existing permission-aware sidebar remains the navigation foundation.
- Visual tokens and spacing are updated to match the unified skin.
- New patient-link approval queue for registration staff.
- Contextual AI assistant exposes only tools allowed for the current active role.
- Existing clinical, pharmacy, document, and report pages are visually integrated but receive no new AI mutation capabilities.

### Chat States

- Empty state with role-appropriate examples.
- Skeleton loading state rather than a generic spinner.
- Assistant and user messages with accessible contrast.
- Structured tool-result cards for success, conflict, validation failure, or permission denial.
- Inline errors with a manual workflow link.
- Visible booking code, date, time, queue number, and related-page link after successful actions.

## OpenAI Integration

The application calls `POST /v1/responses` using Laravel's HTTP client. No browser code receives the OpenAI API key.

Configuration:

- `OPENAI_API_KEY` is required in the runtime environment and is never committed.
- `OPENAI_MODEL` defaults to `gpt-5.6-terra` for a balance of tool-use quality, latency, and cost.
- The model remains configurable without code changes.
- Requests use `store: false`.
- Requests send a stable, privacy-preserving `safety_identifier` derived from the authenticated user ID through an application HMAC.
- Reasoning starts at `low` for latency-sensitive front-office work and is configurable.
- Tools use strict JSON Schema with `additionalProperties: false`.
- Initial delivery uses direct function calling, not Programmatic Tool Calling, because the available tools include side effects.
- Initial delivery is request-response rather than token streaming. The UI uses a clear processing state and receives one completed turn.

Laravel service boundaries:

- `OpenAiClient`: builds authenticated Responses API requests, timeouts, safe retries, and error normalization.
- `ClinicChatOrchestrator`: builds the role-specific instructions and tool list, runs the function-call loop, and produces a safe UI response.
- `ClinicToolRegistry`: returns the exact tool schemas available to the current actor.
- `ClinicToolGateway`: dispatches allowlisted tools after authentication, authorization, ownership, validation, idempotency, and domain checks.
- Focused tool handlers call existing or newly extracted Laravel actions and query services. Controllers do not contain tool business logic.

The system prompt explicitly states that the chatbot is an operational assistant, not a diagnostic or treatment assistant. It must refuse diagnosis, treatment, prescription, medication substitution, triage decisions, and emergency assessment, then direct the user to qualified staff or emergency services when appropriate.

## Tool Catalog

### Read Tools

- `list_public_schedules`: active providers, services, public hours, and exceptions.
- `find_available_slots`: available future slots for a service or provider.
- `get_own_appointments`: approved patient account only.
- `get_own_queue_status`: approved patient account only.
- `list_own_visit_history`: approved patient account only, summarized fields only.
- `search_patients`: authorized staff only, minimized results.
- `get_patient_visit_history`: staff only with required permission and audited patient access.
- `get_queue_board`: staff only with queue permission.

### Mutation Tools

- `create_own_appointment`: patient creates an appointment for the linked patient record.
- `reschedule_own_appointment`: patient changes an eligible future appointment.
- `cancel_own_appointment`: patient cancels an eligible future appointment.
- `check_in_own_appointment`: patient checks in only on the service date and within the configured time window.
- `register_patient`: authorized registration staff only, using existing duplicate, guardian, and identifier rules.
- `create_patient_appointment`: authorized staff only.
- `reschedule_patient_appointment`: authorized staff only.
- `cancel_patient_appointment`: authorized staff only.
- `check_in_patient`: authorized staff only.

The following are never AI tools in this delivery:

- Patient account approval or identity-link approval.
- Triage creation or priority override.
- Clinical drafts, clinical entries, diagnoses, or addenda.
- Prescriptions, substitutions, dispensing, medicine batches, or stock movements.
- Medical-record-copy approval or generation.
- Management report generation beyond existing read-only screens.
- Arbitrary SQL, HTTP requests, file access, shell commands, class names, or route names.

## Appointment and Queue Domain Changes

The current registration action only accepts today's active schedule and immediately allocates a queue ticket. The approved chatbot design requires future appointments, rescheduling, and cancellation. The operations domain must therefore be separated into explicit stages:

1. Appointment booking creates a registration and appointment for a future date and slot without allocating a daily queue number.
2. Check-in locks the registration and appointment, validates the service date and status, allocates the queue ticket atomically, and creates the visit idempotently.
3. Rescheduling changes only eligible future appointments, validates schedule exceptions and slot capacity, and records an event.
4. Cancellation marks the appointment and registration cancelled and preserves the historical record.
5. Existing front-desk same-day registration remains supported through the same services with a same-day check-in path.

Slot availability must account for:

- Provider schedule day and effective range.
- Schedule exceptions and replacement hours.
- Appointment status.
- Configured slot duration and provider capacity.
- Concurrent booking through a transaction and database uniqueness rule.

## Visit History Scope

Patient history contains only:

- Visit date.
- Requested service.
- Responsible provider display name when available.
- Visit status.
- Arrival and completion times when available.
- Booking code or visit reference.

Patient history excludes chief complaints, vital signs, diagnoses, procedures, clinical notes, prescriptions, allergies, attachments, and internal audit details. Staff history uses existing permissions and still minimizes fields returned to OpenAI.

## Conversation and Audit Data

Raw conversation content exists only in the in-memory Alpine component while the page is open. It is sent to Laravel for the current turn but is not stored in localStorage, sessionStorage, a chat table, application logs, or Agent Memory.

Each request is limited by message count, message length, total context size, and tool-call count. Laravel reconstructs the allowed context and does not trust client-supplied role or tool definitions.

A new `ai_tool_executions` table provides idempotency and recovery:

- `id` UUID primary key.
- `idempotency_key` unique.
- `user_id`, optional `patient_id`, and active role.
- `tool_name` and request fingerprint.
- `status`: `pending`, `succeeded`, or `failed`.
- Resource type and resource ID when an action creates or changes data.
- Redacted input and output JSON.
- Failure code and safe failure summary.
- Started, completed, and expiration timestamps.

Existing `audit_events` records sensitive reads and every AI-assisted mutation. Metadata contains the tool name and AI execution ID, but never raw prompt text, full NIK, passwords, API keys, clinical notes, or unredacted visit data.

## Direct Action Rules

The user approved direct execution without an extra confirmation screen. Direct execution remains bounded by these rules:

- A mutation tool may run only when the current user instruction explicitly requests that action and all required fields are known.
- Ambiguous patient, date, service, provider, or slot requests trigger one clarifying question instead of a guess.
- The model cannot select a patient ID for patient users. Laravel injects the linked patient ID server-side.
- Staff patient IDs must come from a fresh authorized search result or current page context.
- Every mutation uses an idempotency key. Repeated requests return the prior result.
- The tool gateway rechecks permissions and domain state immediately before execution.
- OpenAI output never overrides a Laravel validation, authorization, conflict, or transaction result.

## Error Handling

- OpenAI connection timeout: no tool is executed, show a retryable error and a manual workflow link.
- OpenAI 429 or transient 5xx: use bounded exponential retry only before any mutation tool begins.
- Malformed output or unknown tool: reject the call, record a safe technical event, and return a generic assistant error.
- Authorization failure: return HTTP 403, audit the denial, and disclose no resource details.
- Validation failure: return structured field errors and let the assistant ask only for missing or invalid information.
- Slot conflict: return HTTP 409 with a safe list of current alternatives.
- Duplicate request: return the previous successful `ai_tool_executions` result.
- Domain action succeeds but the final OpenAI response fails: the database result remains authoritative and the UI retrieves the completed result using the execution ID.
- Patient account pending or rejected: chatbot routes are unavailable and the portal displays the approval state and next action.

## Security Controls

- Authenticate every chatbot request.
- Require verified email and approved patient link for patient chat routes.
- Build tool availability server-side from the authenticated user and active role.
- Enforce policies, permissions, ownership, and patient isolation in every handler.
- Apply separate rate limits per user and IP for patient and staff routes.
- Limit input length, message count, tool count, response size, and request duration.
- Use connect and request timeouts with a circuit-breaker strategy for repeated OpenAI failures.
- Keep the API key only in server environment configuration.
- Redact sensitive data before OpenAI requests, logs, audit metadata, and tool execution snapshots.
- Treat user messages and model output as untrusted input.
- Escape all chat rendering through Alpine text binding. No raw HTML output from the model.
- Apply CSRF protection and existing security headers.
- Audit patient search and visit-history reads.

## Testing Strategy

All tests are PHPUnit classes.

### Patient Portal Tests

- Patient registration and email verification.
- Pending account cannot access portal or chat.
- Authorized staff can approve or reject a link request.
- Approved account links to exactly one patient.
- Patient cannot access another patient's appointments, queue, or history.
- Rejected and suspended states behave safely.

### Appointment and Queue Tests

- Future appointment creation with active schedule.
- Schedule exception and replacement-hour handling.
- Concurrent slot booking permits only configured capacity.
- Reschedule and cancellation status histories.
- Patient same-day check-in window.
- Atomic queue number allocation at check-in.
- Duplicate check-in and retry are idempotent.
- Existing front-desk registration remains functional.

### AI Contract Tests

- Laravel HTTP fakes for the Responses API.
- Model, `store: false`, safety identifier, strict schemas, and role-specific tool lists.
- Happy, validation, denial, conflict, malformed, refusal, timeout, 429, and 5xx paths.
- Unknown tools and extra arguments are rejected.
- Mutations call the expected domain action once.
- Successful mutations are recoverable after final-response failure.
- API keys, raw prompts, and sensitive identifiers do not enter rendered HTML or logs.

### Audit and Privacy Tests

- Every sensitive read and mutation records an audit event.
- `ai_tool_executions` stores redacted structured data only.
- No chat transcript table exists.
- Chat content is not stored in localStorage or sessionStorage.
- Patient visit history excludes clinical details.

### Frontend Tests

- Public schedule remains accessible without login while chatbot endpoints remain protected.
- Landing, registration, approval state, patient portal, staff shell, and chat states render on desktop and mobile.
- Chat drawer and bottom sheet support keyboard navigation, focus management, escape/close, and reduced motion.
- Loading, empty, success, validation, conflict, rate-limit, and unavailable states render correctly.
- Browser logs and network failures are checked after the focused feature work.

### Required Verification Per Implementation Slice

- Run the smallest relevant `php artisan test --compact` target.
- Run the singular updated test file whenever a test changes.
- Run `vendor/bin/pint --dirty --format agent` after PHP changes.
- Run `npm run build` after frontend changes.
- Inspect database structure and safe read-only data through DBHub.
- Verify frontend behavior in desktop and mobile browser sizes.
- Run the full existing test suite only after related focused tests pass and before final completion.

## Git Delivery Workflow

Implementation happens directly on `main` as explicitly requested.

For each coherent implementation slice:

1. Verify the worktree and preserve unrelated user changes.
2. Implement the smallest complete slice.
3. Run its focused tests and required formatter/build checks.
4. Stage only files belonging to that slice. Do not stage the untracked `template/` reference folder wholesale.
5. Create a descriptive commit.
6. Push the commit to `origin/main`.
7. Continue only after the push succeeds and the worktree still matches expectations.

The `template/` directory remains local reference material unless a specific required asset is intentionally migrated into the primary application. Adapted production files belong under the existing Laravel structure.

## Acceptance Criteria

- Public users can view accurate provider schedule information without authentication.
- Only approved patient accounts and staff can use the chatbot.
- Staff can approve patient-account links without OTP.
- Patients can directly create, reschedule, cancel, and check in their own eligible appointments through chat.
- Patients can view only their own queue status and summarized visit history.
- Authorized staff can use AI for the approved front-office operations.
- AI cannot perform clinical, pharmacy, stock, identity-approval, or report mutations.
- All write operations are transactional, permission-checked, ownership-checked, idempotent, and audited.
- OpenAI failures do not duplicate or roll back already successful domain operations.
- Raw conversation transcripts are not persisted.
- The frontend follows the unified teal visual system across public, patient, and staff surfaces.
- Focused tests, Pint, Vite build, browser verification, and the final full suite pass.
- Each completed implementation slice is committed and pushed directly to `origin/main`.

## Documentation References

- OpenAI latest model guidance: `https://developers.openai.com/api/docs/guides/latest-model.md`
- OpenAI function calling: `https://developers.openai.com/api/docs/guides/function-calling`
- OpenAI production best practices: `https://developers.openai.com/api/docs/guides/production-best-practices`
- Laravel 13 HTTP client, rate limiting, email verification, and response documentation were verified through Laravel Boost.

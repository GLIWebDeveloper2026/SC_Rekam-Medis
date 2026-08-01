# OpenAI Clinic Chatbot Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add a non-streaming OpenAI Responses API chatbot that directly performs allowlisted front-office tools for approved patients and authorized staff without persisting raw conversations.

**Architecture:** Laravel owns authentication, role-specific tool schemas, validation, idempotency, domain execution, and audit. OpenAI receives minimized context with strict function definitions, requests tools by name, and turns structured tool results into user-facing text. Direct function calling is used instead of Programmatic Tool Calling because mutation tools have side effects.

**Tech Stack:** Laravel 13 HTTP client, OpenAI Responses API, GPT-5.6 Terra default, Blade, Alpine.js CSP, MySQL/MariaDB, PHPUnit 12.

---

## Git Safety

- Begin implementation only after the parallel Fortify auth session is complete and the intended implementation worktree is on `main`.
- Before every commit, run `git status --short` and `git diff --cached --name-only`.
- Never stage or commit `.agent/`, `.agents/`, `.pi/`, `.playwright/`, `.playwright-mcp/`, or `.codex/`.
- Never stage `template/` wholesale.
- Stage the explicit paths listed in each task. If another session changes a listed file concurrently, stop and reconcile before committing.

## File Structure

- `config/services.php`: OpenAI endpoint, key, model, reasoning effort, and timeout configuration.
- `app/Contracts/Ai/OpenAiClient.php`: external API boundary.
- `app/Services/Ai/OpenAiResponsesClient.php`: Laravel HTTP implementation.
- `app/Data/Ai/ChatActorContext.php`: authenticated actor, active role, and approved patient context.
- `app/Data/Ai/ToolResult.php`: normalized tool response contract.
- `app/Models/AiToolExecution.php`: idempotency and recoverable action result.
- `app/Services/Ai/ClinicToolRegistry.php`: strict tool schemas filtered by actor.
- `app/Services/Ai/ClinicToolGateway.php`: allowlisted tool dispatch and execution tracking.
- `app/Services/Ai/MutationIntentGuard.php`: deterministic verification that the latest user message explicitly requests a mutation.
- `app/Services/Ai/ClinicChatOrchestrator.php`: Responses API function-call loop.
- `app/Services/Ai/Tools/*`: schedule, appointment, patient, queue, and visit-history handlers.
- `app/Http/Requests/Ai/ClinicChatRequest.php`: message and idempotency validation.
- `app/Http/Controllers/ClinicChatController.php`: thin JSON endpoint.
- `resources/views/components/ai/chat-panel.blade.php`: shared patient/staff chat UI.
- `resources/js/app.js`: in-memory Alpine chat state.
- `tests/Feature/Ai/*`: API contract, permissions, tools, privacy, and end-to-end tests.

### Task 1: Configure the OpenAI HTTP Boundary

**Files:**
- Modify: `.env.example`
- Modify: `config/services.php`
- Create: `app/Contracts/Ai/OpenAiClient.php`
- Create: `app/Services/Ai/OpenAiResponsesClient.php`
- Modify: `app/Providers/AppServiceProvider.php`
- Test: `tests/Feature/Ai/OpenAiResponsesClientTest.php`

- [ ] **Step 1: Generate files**

```powershell
php artisan make:interface Contracts/Ai/OpenAiClient --no-interaction
php artisan make:class Services/Ai/OpenAiResponsesClient --no-interaction
php artisan make:test --phpunit Ai/OpenAiResponsesClientTest --no-interaction
```

- [ ] **Step 2: Write failing HTTP contract tests**

```php
<?php

namespace Tests\Feature\Ai;

use App\Contracts\Ai\OpenAiClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OpenAiResponsesClientTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_sends_secure_responses_api_payload(): void
    {
        config()->set('services.openai.key', 'test-key');
        config()->set('services.openai.model', 'gpt-5.6-terra');
        Http::preventStrayRequests();
        Http::fake(['api.openai.com/v1/responses' => Http::response(['id' => 'resp_1', 'output' => []])]);

        app(OpenAiClient::class)->createResponse([
            'input' => [['role' => 'user', 'content' => 'Lihat jadwal besok']],
            'tools' => [],
            'safety_identifier' => 'safe-user-id',
        ]);

        Http::assertSent(function (Request $request): bool {
            return $request->url() === 'https://api.openai.com/v1/responses'
                && $request->hasHeader('Authorization', 'Bearer test-key')
                && $request['model'] === 'gpt-5.6-terra'
                && $request['store'] === false
                && $request['reasoning']['effort'] === 'low';
        });
    }
}
```

Add tests for a failed connection, 429, 5xx, invalid JSON, and missing API key.

- [ ] **Step 3: Run the test to verify it fails**

```powershell
php artisan test --compact tests/Feature/Ai/OpenAiResponsesClientTest.php
```

Expected: FAIL because the contract and service binding do not exist.

- [ ] **Step 4: Add environment and config values**

Add to `.env.example` without a real key:

```dotenv
OPENAI_API_KEY=
OPENAI_MODEL=gpt-5.6-terra
OPENAI_REASONING_EFFORT=low
OPENAI_CONNECT_TIMEOUT=3
OPENAI_TIMEOUT=20
```

Add to `config/services.php`:

```php
'openai' => [
    'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
    'key' => env('OPENAI_API_KEY'),
    'model' => env('OPENAI_MODEL', 'gpt-5.6-terra'),
    'reasoning_effort' => env('OPENAI_REASONING_EFFORT', 'low'),
    'connect_timeout' => (int) env('OPENAI_CONNECT_TIMEOUT', 3),
    'timeout' => (int) env('OPENAI_TIMEOUT', 20),
],
```

- [ ] **Step 5: Implement the contract**

```php
<?php

namespace App\Contracts\Ai;

interface OpenAiClient
{
    /** @param array<string, mixed> $payload
     *  @return array<string, mixed>
     */
    public function createResponse(array $payload): array;
}
```

- [ ] **Step 6: Implement the Laravel HTTP client**

```php
<?php

namespace App\Services\Ai;

use App\Contracts\Ai\OpenAiClient;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class OpenAiResponsesClient implements OpenAiClient
{
    public function createResponse(array $payload): array
    {
        $key = config('services.openai.key');

        if (! is_string($key) || $key === '') {
            throw new RuntimeException('OpenAI API key is not configured.');
        }

        $requestPayload = array_merge([
            'model' => config('services.openai.model'),
            'store' => false,
            'reasoning' => ['effort' => config('services.openai.reasoning_effort')],
        ], $payload);

        return Http::baseUrl(config('services.openai.base_url'))
            ->withToken($key)
            ->acceptJson()
            ->asJson()
            ->connectTimeout(config('services.openai.connect_timeout'))
            ->timeout(config('services.openai.timeout'))
            ->retry([200, 500, 1000], function (Throwable $exception, PendingRequest $request): bool {
                return $exception instanceof ConnectionException
                    || ($exception instanceof RequestException
                        && ($exception->response->serverError() || $exception->response->status() === 429));
            }, throw: false)
            ->post('/responses', $requestPayload)
            ->throw()
            ->json();
    }
}
```

- [ ] **Step 7: Bind the interface**

In `AppServiceProvider::register()`:

```php
$this->app->bind(OpenAiClient::class, OpenAiResponsesClient::class);
```

- [ ] **Step 8: Run tests and format**

```powershell
php artisan test --compact tests/Feature/Ai/OpenAiResponsesClientTest.php
vendor/bin/pint --dirty --format agent
```

Expected: PASS.

- [ ] **Step 9: Commit and push**

```powershell
git add .env.example config/services.php app/Contracts/Ai/OpenAiClient.php app/Services/Ai/OpenAiResponsesClient.php app/Providers/AppServiceProvider.php tests/Feature/Ai/OpenAiResponsesClientTest.php
git commit -m "feat add openai responses api client"
git push origin main
```

### Task 2: Persist Idempotent Tool Executions

**Files:**
- Create: `app/Models/AiToolExecution.php`
- Create: `database/factories/AiToolExecutionFactory.php`
- Create: `database/migrations/*_create_ai_tool_executions_table.php`
- Test: `tests/Feature/Ai/AiToolExecutionTest.php`

- [ ] **Step 1: Generate files**

```powershell
php artisan make:model AiToolExecution --factory --migration --no-interaction
php artisan make:test --phpunit Ai/AiToolExecutionTest --no-interaction
```

- [ ] **Step 2: Write the failing persistence test**

```php
public function test_execution_has_unique_idempotency_key_and_redacted_payloads(): void
{
    $execution = AiToolExecution::factory()->create([
        'idempotency_key' => 'user-message-create-appointment',
        'tool_name' => 'create_own_appointment',
        'safe_input_json' => ['appointment_date' => '2026-08-10'],
        'safe_output_json' => ['booking_code' => 'BK-20260810-ABC123'],
        'status' => AiToolExecution::StatusSucceeded,
    ]);

    $this->assertModelExists($execution);
    $this->assertSame('2026-08-10', $execution->safe_input_json['appointment_date']);
}
```

- [ ] **Step 3: Run the test to verify it fails**

```powershell
php artisan test --compact tests/Feature/Ai/AiToolExecutionTest.php
```

- [ ] **Step 4: Implement the migration**

```php
Schema::create('ai_tool_executions', function (Blueprint $table): void {
    $table->uuid('id')->primary();
    $table->string('idempotency_key')->unique();
    $table->foreignUuid('user_id')->constrained()->restrictOnDelete();
    $table->foreignUuid('patient_id')->nullable()->constrained()->nullOnDelete();
    $table->string('active_role', 50)->nullable()->index();
    $table->string('tool_name', 100)->index();
    $table->char('request_fingerprint', 64)->index();
    $table->string('status', 20)->default('pending')->index();
    $table->string('resource_type', 80)->nullable()->index();
    $table->string('resource_id', 64)->nullable()->index();
    $table->json('safe_input_json')->nullable();
    $table->json('safe_output_json')->nullable();
    $table->string('failure_code', 80)->nullable();
    $table->text('failure_summary')->nullable();
    $table->dateTime('started_at', precision: 6);
    $table->dateTime('completed_at', precision: 6)->nullable();
    $table->dateTime('expires_at', precision: 6)->index();
    $table->timestamps(6);
    $table->index(['user_id', 'created_at']);
});
```

- [ ] **Step 5: Implement the model**

Use `HasUuids`, fillable fields, status constants, `user` and `patient` relationships, array casts for safe JSON, and datetime casts for lifecycle timestamps. Do not add raw prompt or raw response columns.

- [ ] **Step 6: Run tests and format**

```powershell
php artisan test --compact tests/Feature/Ai/AiToolExecutionTest.php
vendor/bin/pint --dirty --format agent
```

- [ ] **Step 7: Commit and push**

```powershell
git add app/Models/AiToolExecution.php database/factories/AiToolExecutionFactory.php database/migrations tests/Feature/Ai/AiToolExecutionTest.php
git commit -m "feat persist idempotent ai tool executions"
git push origin main
```

### Task 3: Define Actor Context, Tool Result, and Strict Registry

**Files:**
- Create: `app/Data/Ai/ChatActorContext.php`
- Create: `app/Data/Ai/ToolResult.php`
- Create: `app/Services/Ai/ClinicToolRegistry.php`
- Test: `tests/Feature/Ai/ClinicToolRegistryTest.php`

- [ ] **Step 1: Generate files and test**

```powershell
php artisan make:class Data/Ai/ChatActorContext --no-interaction
php artisan make:class Data/Ai/ToolResult --no-interaction
php artisan make:class Services/Ai/ClinicToolRegistry --no-interaction
php artisan make:test --phpunit Ai/ClinicToolRegistryTest --no-interaction
```

- [ ] **Step 2: Write failing role-specific registry tests**

Test that approved patients receive only own-scope tools, registration staff receive patient and queue tools, doctors receive read-only schedule/queue/history tools where permitted, and no actor receives clinical, prescription, stock, report-mutation, identity-approval, SQL, HTTP, file, or shell tools.

- [ ] **Step 3: Run the test to verify it fails**

```powershell
php artisan test --compact tests/Feature/Ai/ClinicToolRegistryTest.php
```

- [ ] **Step 4: Implement actor context**

```php
<?php

namespace App\Data\Ai;

use App\Models\Patient;
use App\Models\User;

final readonly class ChatActorContext
{
    public function __construct(
        public User $user,
        public ?Patient $patient,
        public ?string $activeRole,
    ) {}

    public function isApprovedPatient(): bool
    {
        return $this->activeRole === 'patient' && $this->patient !== null;
    }
}
```

- [ ] **Step 5: Implement tool result**

```php
<?php

namespace App\Data\Ai;

final readonly class ToolResult
{
    /** @param array<string, mixed> $data */
    public function __construct(
        public bool $ok,
        public string $code,
        public string $message,
        public array $data = [],
        public ?string $resourceType = null,
        public ?string $resourceId = null,
    ) {}

    public function toArray(): array
    {
        return [
            'ok' => $this->ok,
            'code' => $this->code,
            'message' => $this->message,
            'data' => $this->data,
        ];
    }
}
```

- [ ] **Step 6: Implement strict tool registry**

The registry returns OpenAI function definitions in this exact shape:

```php
private function functionTool(string $name, string $description, array $properties, array $required): array
{
    return [
        'type' => 'function',
        'name' => $name,
        'description' => $description,
        'strict' => true,
        'parameters' => [
            'type' => 'object',
            'properties' => $properties,
            'required' => $required,
            'additionalProperties' => false,
        ],
    ];
}
```

Define schemas for all approved tools from the design spec. Patient schemas never contain `patient_id`. Staff schemas include `patient_id` only when the user has the matching permission.

- [ ] **Step 7: Run tests and format**

```powershell
php artisan test --compact tests/Feature/Ai/ClinicToolRegistryTest.php
vendor/bin/pint --dirty --format agent
```

- [ ] **Step 8: Commit and push**

```powershell
git add app/Data/Ai app/Services/Ai/ClinicToolRegistry.php tests/Feature/Ai/ClinicToolRegistryTest.php
git commit -m "feat define role scoped clinic ai tools"
git push origin main
```

### Task 4: Implement Read-Only Tool Handlers

**Files:**
- Create: `app/Services/Ai/Tools/ScheduleToolHandler.php`
- Create: `app/Services/Ai/Tools/QueueToolHandler.php`
- Create: `app/Services/Ai/Tools/VisitToolHandler.php`
- Create: `app/Services/Ai/Tools/PatientSearchToolHandler.php`
- Test: `tests/Feature/Ai/ReadOnlyClinicToolsTest.php`

- [ ] **Step 1: Generate files and test**

```powershell
php artisan make:class Services/Ai/Tools/ScheduleToolHandler --no-interaction
php artisan make:class Services/Ai/Tools/QueueToolHandler --no-interaction
php artisan make:class Services/Ai/Tools/VisitToolHandler --no-interaction
php artisan make:class Services/Ai/Tools/PatientSearchToolHandler --no-interaction
php artisan make:test --phpunit Ai/ReadOnlyClinicToolsTest --no-interaction
```

- [ ] **Step 2: Write failing read and privacy tests**

Cover public schedules, available slots, patient-owned appointments, own queue status, own visit history, staff queue board, patient search, staff visit history, denied permissions, another patient's records, and absence of diagnoses, complaints, prescriptions, identifiers, and clinical JSON.

- [ ] **Step 3: Run the test to verify it fails**

```powershell
php artisan test --compact tests/Feature/Ai/ReadOnlyClinicToolsTest.php
```

- [ ] **Step 4: Implement schedule tools**

`ScheduleToolHandler` depends on `AppointmentAvailability`. It selects only provider display name, service, weekday, effective public hours, date, and slots. It returns at most 20 slots and never returns user email, license number, internal schedule reason, or patient data.

`AppointmentToolHandler` implements `get_own_appointments` as a read method. It injects the linked patient ID, selects only future or recently completed appointments, eager-loads schedule/provider display fields, limits output to 20 records, and returns booking code, service, provider, date, slot, and status.

- [ ] **Step 5: Implement queue tools**

For patients, inject the linked patient ID and return only own booking code, queue number, service, status, and check-in time. For staff, require `queue.view`, eager-load registration and patient display fields, limit results to one service day, and cap output at 100 rows.

- [ ] **Step 6: Implement visit tools**

Use `PatientVisitHistory` from the patient portal plan. Patient mode always uses the linked patient. Staff mode requires `patients.view`, audits the sensitive read, and returns the same minimized field set.

- [ ] **Step 7: Implement patient search**

Require `patients.view`. Search normalized name, medical record number, or phone with parameterized Eloquent queries. Return at most 10 records with `id`, medical record number, full name, birth date, phone suffix, and status. Never search or return raw encrypted identifiers.

- [ ] **Step 8: Run tests and format**

```powershell
php artisan test --compact tests/Feature/Ai/ReadOnlyClinicToolsTest.php
vendor/bin/pint --dirty --format agent
```

- [ ] **Step 9: Commit and push**

```powershell
git add app/Services/Ai/Tools tests/Feature/Ai/ReadOnlyClinicToolsTest.php
git commit -m "feat add privacy scoped clinic read tools"
git push origin main
```

### Task 5: Implement Mutation Tool Handlers

**Files:**
- Create: `app/Services/Ai/Tools/AppointmentToolHandler.php`
- Create: `app/Services/Ai/Tools/RegistrationToolHandler.php`
- Modify: `app/Services/Ai/Tools/QueueToolHandler.php`
- Test: `tests/Feature/Ai/MutationClinicToolsTest.php`

- [ ] **Step 1: Write failing mutation tests**

Test patient create/reschedule/cancel/check-in, staff patient registration and appointment actions, idempotent duplicate requests, patient ID injection, staff permission denial, invalid slot, slot conflict, past appointment, already checked-in appointment, and guardian validation for infant registration.

- [ ] **Step 2: Run the test to verify it fails**

```powershell
php artisan test --compact tests/Feature/Ai/MutationClinicToolsTest.php
```

- [ ] **Step 3: Implement appointment mutation handler**

The handler injects the patient for patient tools and uses staff-supplied `patient_id` only after permission and existence checks. It delegates to `BookAppointment`, `RescheduleAppointment`, `CancelAppointment`, and `CheckInAppointment`, then returns safe booking, slot, queue, and status fields.

Use one method per tool name:

```php
public function createOwn(ChatActorContext $actor, array $arguments): ToolResult;
public function rescheduleOwn(ChatActorContext $actor, array $arguments): ToolResult;
public function cancelOwn(ChatActorContext $actor, array $arguments): ToolResult;
public function checkInOwn(ChatActorContext $actor, array $arguments): ToolResult;
public function createForPatient(ChatActorContext $actor, array $arguments): ToolResult;
public function rescheduleForPatient(ChatActorContext $actor, array $arguments): ToolResult;
public function cancelForPatient(ChatActorContext $actor, array $arguments): ToolResult;
public function checkInPatient(ChatActorContext $actor, array $arguments): ToolResult;
```

- [ ] **Step 4: Implement registration mutation handler**

Require `patients.manage`, validate the strict tool arguments through a Laravel validator, call the existing `RegisterPatient` action, and return patient ID, medical record number, full name, and duplicate-warning state. Do not return raw NIK or guardian identifiers.

- [ ] **Step 5: Run mutation tests and domain regressions**

```powershell
php artisan test --compact tests/Feature/Ai/MutationClinicToolsTest.php tests/Feature/PatientPortal tests/Feature/Queue tests/Feature/Patients
vendor/bin/pint --dirty --format agent
```

Expected: PASS.

- [ ] **Step 6: Commit and push**

```powershell
git add app/Services/Ai/Tools tests/Feature/Ai/MutationClinicToolsTest.php
git commit -m "feat add bounded clinic mutation tools"
git push origin main
```

### Task 6: Add the Idempotent Tool Gateway

**Files:**
- Create: `app/Services/Ai/ClinicToolGateway.php`
- Create: `app/Services/Ai/MutationIntentGuard.php`
- Test: `tests/Feature/Ai/ClinicToolGatewayTest.php`

- [ ] **Step 1: Generate gateway files and test**

```powershell
php artisan make:class Services/Ai/ClinicToolGateway --no-interaction
php artisan make:class Services/Ai/MutationIntentGuard --no-interaction
php artisan make:test --phpunit Ai/ClinicToolGatewayTest --no-interaction
```

- [ ] **Step 2: Write failing dispatch tests**

Test allowlisted dispatch, unknown-tool rejection, unique execution creation, duplicate idempotency return, failed execution recording, resource linkage, payload redaction, audit linkage, and rejection of mutation calls when the latest user message does not explicitly request that action.

- [ ] **Step 3: Run the test to verify it fails**

```powershell
php artisan test --compact tests/Feature/Ai/ClinicToolGatewayTest.php
```

- [ ] **Step 4: Implement gateway dispatch map**

```php
private function dispatch(ChatActorContext $actor, string $toolName, array $arguments): ToolResult
{
    return match ($toolName) {
        'list_public_schedules', 'find_available_slots' => $this->scheduleTools->execute($actor, $toolName, $arguments),
        'get_own_appointments' => $this->appointmentTools->execute($actor, $toolName, $arguments),
        'get_own_queue_status', 'get_queue_board' => $this->queueTools->execute($actor, $toolName, $arguments),
        'list_own_visit_history', 'get_patient_visit_history' => $this->visitTools->execute($actor, $toolName, $arguments),
        'search_patients' => $this->patientSearchTools->execute($actor, $arguments),
        'register_patient' => $this->registrationTools->execute($actor, $arguments),
        'create_own_appointment', 'reschedule_own_appointment', 'cancel_own_appointment',
        'check_in_own_appointment', 'create_patient_appointment', 'reschedule_patient_appointment',
        'cancel_patient_appointment', 'check_in_patient' => $this->appointmentTools->execute($actor, $toolName, $arguments),
        default => throw new DomainException('Unknown AI tool.'),
    };
}
```

- [ ] **Step 5: Implement execution tracking**

`execute()` receives the latest user message, derives the execution key from user ID, client message idempotency key, tool name, and canonical argument hash. It returns an existing succeeded result, rejects a fingerprint mismatch, creates a pending row, dispatches once, stores redacted result/resource data, writes the audit event, and marks failures with a safe code and summary.

Before any mutation dispatch, call `MutationIntentGuard::allows($toolName, $latestUserMessage)`. Implement the guard exactly:

```php
<?php

namespace App\Services\Ai;

use Illuminate\Support\Str;

class MutationIntentGuard
{
    public function allows(string $toolName, string $message): bool
    {
        $normalized = Str::of($message)->lower()->squish()->toString();

        $patterns = match ($toolName) {
            'create_own_appointment', 'create_patient_appointment' => ['buat janji', 'pesan jadwal', 'jadwalkan', 'book appointment'],
            'reschedule_own_appointment', 'reschedule_patient_appointment' => ['ubah jadwal', 'pindah jadwal', 'reschedule'],
            'cancel_own_appointment', 'cancel_patient_appointment' => ['batalkan', 'batal janji', 'cancel appointment'],
            'check_in_own_appointment', 'check_in_patient' => ['check-in', 'check in', 'sudah tiba', 'daftar ulang'],
            'register_patient' => ['daftarkan pasien', 'buat pasien baru', 'registrasi pasien'],
            default => [],
        };

        return collect($patterns)->contains(
            fn (string $pattern): bool => Str::contains($normalized, $pattern),
        );
    }
}
```

Read-only tools bypass this guard. A rejected mutation returns `explicit_intent_required` and performs no domain action.

Never store raw prompt content in `AiToolExecution` or `AuditEvent`.

- [ ] **Step 6: Run tests and format**

```powershell
php artisan test --compact tests/Feature/Ai/ClinicToolGatewayTest.php
vendor/bin/pint --dirty --format agent
```

- [ ] **Step 7: Commit and push**

```powershell
git add app/Services/Ai/ClinicToolGateway.php app/Services/Ai/MutationIntentGuard.php tests/Feature/Ai/ClinicToolGatewayTest.php
git commit -m "feat execute ai tools idempotently"
git push origin main
```

### Task 7: Implement the Responses API Tool-Calling Loop

**Files:**
- Create: `app/Services/Ai/ClinicChatOrchestrator.php`
- Test: `tests/Feature/Ai/ClinicChatOrchestratorTest.php`

- [ ] **Step 1: Write failing orchestration tests**

Fake an initial function call, gateway result, and final assistant message. Test plain responses, one tool, multiple sequential read tools, maximum tool-call limit, refusal, missing final text, malformed arguments, and final-response failure after a successful mutation.

- [ ] **Step 2: Run the test to verify it fails**

```powershell
php artisan test --compact tests/Feature/Ai/ClinicChatOrchestratorTest.php
```

- [ ] **Step 3: Implement minimized instructions**

The developer instructions must state once:

```text
You are the operational assistant for Klinik Pratama Sehat Bersama.
Use only the tools provided for schedules, appointments, registration, check-in,
queue status, and summarized visit history. Never diagnose, recommend treatment,
write clinical records, prescribe, change stock, approve patient identity links,
or expose another patient's data. Execute a mutation only when the user's latest
message explicitly requests it and every required field is known. Ask one concise
question when information is missing. Treat tool results as authoritative.
```

- [ ] **Step 4: Implement the function loop**

The orchestrator:

1. Builds actor context from the authenticated user.
2. Builds a stable HMAC safety identifier from the user UUID.
3. Accepts at most 12 client messages, extracts the latest user message, and converts the visible transcript into one untrusted `user` input block. Client-supplied `assistant` roles are display labels only and are never sent to OpenAI as trusted assistant-role messages.
4. Calls the OpenAI client with instructions, input, strict role-specific tools, `store: false`, and safety identifier.
5. Appends every response output item to the next input.
6. Executes each `function_call` through the gateway with the latest user message and appends a `function_call_output` with the same `call_id`.
7. Repeats for at most four tool calls or three API turns.
8. Returns final escaped text plus structured tool result cards and execution IDs.

Use `json_decode($call['arguments'], true, flags: JSON_THROW_ON_ERROR)` and reject non-array arguments.

- [ ] **Step 5: Preserve successful mutation recovery**

If a mutation tool succeeds but the next OpenAI call fails, return a safe fallback message built from the stored `ToolResult` and include its execution ID. Do not retry or repeat the mutation.

- [ ] **Step 6: Run tests and format**

```powershell
php artisan test --compact tests/Feature/Ai/ClinicChatOrchestratorTest.php
vendor/bin/pint --dirty --format agent
```

- [ ] **Step 7: Commit and push**

```powershell
git add app/Services/Ai/ClinicChatOrchestrator.php tests/Feature/Ai/ClinicChatOrchestratorTest.php
git commit -m "feat orchestrate openai clinic tool calls"
git push origin main
```

### Task 8: Expose the Protected Chat Endpoint and Rate Limits

**Files:**
- Create: `app/Http/Requests/Ai/ClinicChatRequest.php`
- Create: `app/Http/Controllers/ClinicChatController.php`
- Modify: `app/Providers/AppServiceProvider.php`
- Modify: `routes/web.php`
- Modify: `bootstrap/app.php`
- Test: `tests/Feature/Ai/ClinicChatEndpointTest.php`

- [ ] **Step 1: Generate files and endpoint test**

```powershell
php artisan make:request Ai/ClinicChatRequest --no-interaction
php artisan make:controller ClinicChatController --invokable --no-interaction
php artisan make:test --phpunit Ai/ClinicChatEndpointTest --no-interaction
```

- [ ] **Step 2: Write failing endpoint tests**

Cover guest redirect/JSON 401, unverified patient denial, pending patient denial, approved patient success, staff success, unrelated role with no tools, CSRF, invalid message roles, overlong content, too many messages, invalid idempotency UUID, and 429 rate limiting.

- [ ] **Step 3: Run the test to verify it fails**

```powershell
php artisan test --compact tests/Feature/Ai/ClinicChatEndpointTest.php
```

- [ ] **Step 4: Implement request validation**

```php
public function authorize(): bool
{
    $user = $this->user();

    return $user !== null && ($user->hasRole('patient')
        ? $user->patientPortalAccount?->isApproved() === true
        : $user->roles()->exists());
}

public function rules(): array
{
    return [
        'idempotency_key' => ['required', 'uuid'],
        'messages' => ['required', 'array', 'min:1', 'max:12'],
        'messages.*.role' => ['required', 'in:user,assistant'],
        'messages.*.content' => ['required', 'string', 'max:2000'],
        'current_page' => ['nullable', 'string', 'max:100'],
    ];
}
```

- [ ] **Step 5: Implement thin controller**

```php
public function __invoke(ClinicChatRequest $request, ClinicChatOrchestrator $chat): JsonResponse
{
    return response()->json($chat->respond(
        user: $request->user(),
        messages: $request->validated('messages'),
        idempotencyKey: $request->validated('idempotency_key'),
        currentPage: $request->validated('current_page'),
    ));
}
```

- [ ] **Step 6: Register rate limits**

In `AppServiceProvider::boot()`:

```php
RateLimiter::for('clinic-chat', function (Request $request): array {
    return [
        Limit::perMinute($request->user()?->hasRole('patient') ? 10 : 20)
            ->by('chat:user:'.$request->user()?->id),
        Limit::perMinute(30)->by('chat:ip:'.$request->ip()),
    ];
});
```

- [ ] **Step 7: Add route**

```php
Route::post('/assistant/messages', ClinicChatController::class)
    ->middleware(['auth', 'verified', 'throttle:clinic-chat'])
    ->name('assistant.messages');
```

Force JSON rendering for `/assistant/*` requests through the project's existing exception configuration.

- [ ] **Step 8: Run tests and format**

```powershell
php artisan test --compact tests/Feature/Ai/ClinicChatEndpointTest.php
vendor/bin/pint --dirty --format agent
```

- [ ] **Step 9: Commit and push**

```powershell
git add app/Http/Requests/Ai/ClinicChatRequest.php app/Http/Controllers/ClinicChatController.php app/Providers/AppServiceProvider.php bootstrap/app.php routes/web.php tests/Feature/Ai/ClinicChatEndpointTest.php
git commit -m "feat expose protected clinic assistant endpoint"
git push origin main
```

### Task 9: Build the In-Memory Chat UI

**Files:**
- Create: `resources/views/components/ai/chat-panel.blade.php`
- Modify: `resources/views/components/layouts/patient.blade.php`
- Modify: `resources/views/components/layouts/app.blade.php`
- Modify: `resources/js/app.js`
- Test: `tests/Feature/Ai/ClinicChatUiTest.php`

- [ ] **Step 1: Write failing UI tests**

Assert public pages have no chat panel. Approved patient and authorized staff layouts include the chat launcher. Pending patients do not. Markup contains no raw API key, no inline script, no localStorage/sessionStorage references, and uses text bindings rather than HTML bindings.

- [ ] **Step 2: Run the test to verify it fails**

```powershell
php artisan test --compact tests/Feature/Ai/ClinicChatUiTest.php
```

- [ ] **Step 3: Implement CSP-safe Alpine state**

```js
Alpine.data('clinicChat', () => ({
    open: false,
    input: '',
    busy: false,
    error: null,
    messages: [],
    toolResults: [],
    openPanel() {
        this.open = true;
        this.$nextTick(() => this.$refs.chatInput?.focus());
    },
    closePanel() {
        this.open = false;
    },
    async send() {
        const content = this.input.trim();
        if (! content || this.busy) return;

        this.messages.push({ role: 'user', content });
        this.input = '';
        this.busy = true;
        this.error = null;

        try {
            const response = await fetch('/assistant/messages', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({
                    idempotency_key: crypto.randomUUID(),
                    messages: this.messages.slice(-12),
                    current_page: window.location.pathname,
                }),
            });

            const data = await response.json();
            if (! response.ok) throw new Error(data.message || 'Asisten tidak dapat memproses permintaan.');

            this.messages.push({ role: 'assistant', content: data.message });
            this.toolResults = data.tool_results || [];
        } catch (error) {
            this.error = error.message;
        } finally {
            this.busy = false;
        }
    },
}));
```

Messages remain only in Alpine memory. Do not add persistence hooks.

- [ ] **Step 4: Implement the chat panel**

The Blade component renders a launcher, desktop right drawer, mobile bottom sheet, role-specific empty examples, user/assistant bubbles using `x-text`, skeleton response state, tool result cards, error feedback, input label, send button, escape close, and focus management. It uses no `x-html`.

- [ ] **Step 5: Include only for eligible actors**

The patient layout includes chat only for an approved portal account. The staff layout includes chat only when the user has at least one supported permission. The public layout never includes it.

- [ ] **Step 6: Run tests and build**

```powershell
php artisan test --compact tests/Feature/Ai/ClinicChatUiTest.php
npm run build
```

Expected: PASS.

- [ ] **Step 7: Commit and push**

```powershell
git add resources/views/components/ai/chat-panel.blade.php resources/views/components/layouts/patient.blade.php resources/views/components/layouts/app.blade.php resources/js/app.js tests/Feature/Ai/ClinicChatUiTest.php
git commit -m "feat add patient and staff clinic chat ui"
git push origin main
```

### Task 10: Privacy, Security, and Failure Recovery Verification

**Files:**
- Create: `tests/Feature/Ai/ClinicChatPrivacyTest.php`
- Create: `tests/Feature/Ai/ClinicChatRecoveryTest.php`
- Verify: AI files changed in Tasks 1-9.

- [ ] **Step 1: Write privacy tests**

Assert raw prompts, raw visit records, full NIK, passwords, API keys, clinical JSON, and another patient's data do not appear in `ai_tool_executions`, `audit_events`, rendered HTML, Laravel logs, or OpenAI request payloads.

- [ ] **Step 2: Write recovery tests**

Assert a successful appointment mutation followed by an OpenAI failure returns the stored booking result, repeated same-key requests do not duplicate data, changed arguments with the same key are rejected, and pending executions expire safely.

- [ ] **Step 3: Run tests to verify failures**

```powershell
php artisan test --compact tests/Feature/Ai/ClinicChatPrivacyTest.php tests/Feature/Ai/ClinicChatRecoveryTest.php
```

- [ ] **Step 4: Implement minimal fixes**

Add only the redaction, recovery, and expiration behavior required by the failing tests. Use structured failure codes and never include stack traces in JSON responses.

- [ ] **Step 5: Run AI and domain suites**

```powershell
php artisan test --compact tests/Feature/Ai
php artisan test --compact tests/Feature/PatientPortal tests/Feature/Queue tests/Feature/Patients
vendor/bin/pint --dirty --format agent
npm run build
```

Expected: PASS.

- [ ] **Step 6: Commit and push**

```powershell
git add app/Contracts/Ai app/Data/Ai app/Http/Controllers/ClinicChatController.php app/Http/Requests/Ai app/Models/AiToolExecution.php app/Services/Ai app/Providers/AppServiceProvider.php bootstrap/app.php config/services.php routes/web.php tests/Feature/Ai resources/js/app.js resources/views/components/ai
git commit -m "fix harden clinic assistant privacy and recovery"
git push origin main
```

### Task 11: End-to-End AI Verification

**Files:**
- Verify: files changed in Tasks 1-10.

- [ ] **Step 1: Verify schema and read-only data through DBHub**

Confirm `ai_tool_executions` structure, indexes, no raw transcript table, patient link uniqueness, and appointment lifecycle schema. Use read-only SQL to verify sample execution rows contain redacted JSON only.

- [ ] **Step 2: Run the full focused suite**

```powershell
php artisan test --compact tests/Feature/Ai tests/Feature/PatientPortal tests/Feature/Frontend
vendor/bin/pint --dirty --format agent
npm run build
```

- [ ] **Step 3: Run the entire suite**

```powershell
php artisan test --compact
```

Expected: all tests pass.

- [ ] **Step 4: Verify browser flows**

Use Laravel Boost `get-absolute-url`, then verify:

- Public schedule without chatbot.
- Pending patient denial.
- Approved patient schedule query, booking, reschedule, cancellation, self check-in, queue status, and visit history.
- Registration staff patient search, registration, booking, and check-in.
- Unsupported clinical request refusal.
- Timeout, conflict, validation, rate limit, and unavailable states.
- Desktop drawer, mobile bottom sheet, keyboard use, focus, reduced motion, browser logs, and network errors.

- [ ] **Step 5: Verify the OpenAI production checklist**

Confirm API key absence from Git and frontend, explicit timeouts, bounded retries, stable safety identifier, `store: false`, strict schemas, maximum tool calls, rate limits, redaction, audit, and manual fallback links.

- [ ] **Step 6: Resolve final failures in their owning task**

If final verification fails, return to the task that owns the affected file, add or update its focused failing test, apply the minimal fix, commit and push that task-specific change, then rerun this end-to-end verification. This task creates no standalone commit.

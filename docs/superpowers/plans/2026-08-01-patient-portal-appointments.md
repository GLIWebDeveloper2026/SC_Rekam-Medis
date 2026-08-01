# Patient Portal and Appointments Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add staff-approved patient portal accounts, future appointment management, self check-in, queue status, and summarized visit history while preserving existing staff workflows.

**Architecture:** Reuse the shared `users` authentication table and Fortify, add a patient-only role plus a one-to-one portal account link, and isolate patient routes behind verified-email and approved-link middleware. Keep same-day front-desk registration intact, while new appointment actions create future registrations and allocate queue tickets only at check-in.

**Tech Stack:** PHP 8.3, Laravel 13, Laravel Fortify, Blade, Eloquent UUID models, MySQL/MariaDB, PHPUnit 12, Tailwind CSS 4.

---

## Git Safety

- Begin implementation only after the parallel Fortify auth session is complete and the intended implementation worktree is on `main`.
- Before every commit, run `git status --short` and `git diff --cached --name-only`.
- Never stage or commit `.agent/`, `.agents/`, `.pi/`, `.playwright/`, `.playwright-mcp/`, or `.codex/`.
- Never stage `template/` wholesale. Copy only intentionally adapted production assets into the Laravel application.
- Stage the explicit paths listed in each task. If another session changes a listed file concurrently, stop and reconcile before committing.

## File Structure

- `app/Models/PatientPortalAccount.php`: patient account status, claimed identity, reviewer, and linked patient relationships.
- `app/Models/Appointment.php`: existing appointment table model and appointment lifecycle relationships.
- `app/Models/AppointmentEvent.php`: append-only booking, reschedule, cancellation, and check-in history.
- `app/Http/Middleware/RequireApprovedPatient.php`: denies patient portal routes unless the patient link is approved.
- `app/Actions/PatientPortal/ApprovePatientPortalAccount.php`: transactionally links a portal account to one patient.
- `app/Actions/Appointments/BookAppointment.php`: creates a future registration and appointment.
- `app/Actions/Appointments/RescheduleAppointment.php`: changes an eligible future appointment and appends an event.
- `app/Actions/Appointments/CancelAppointment.php`: cancels an eligible future appointment and registration.
- `app/Actions/Appointments/CheckInAppointment.php`: allocates a queue ticket and visit at check-in.
- `app/Services/Appointments/AppointmentAvailability.php`: schedule, exception, capacity, and slot calculations.
- `app/Queries/PatientVisitHistory.php`: minimized patient visit-history query.
- `app/Http/Controllers/PatientPortalController.php`: approved patient dashboard.
- `app/Http/Controllers/PatientAppointmentController.php`: patient appointment CRUD-like actions.
- `app/Http/Controllers/PatientPortalAccountReviewController.php`: staff approval and rejection.
- `app/Http/Requests/PatientPortal/*`: approval and rejection validation.
- `app/Http/Requests/Appointments/*`: booking, reschedule, cancellation, and check-in validation.
- `resources/views/patient-portal/*`: patient states and working portal pages.
- `resources/views/patient-portal-reviews/*`: registration-staff review pages.
- `tests/Feature/PatientPortal/*`: account, authorization, appointments, queue, and history coverage.

### Task 1: Lock the Fortify Authentication Baseline

**Files:**
- Verify: `app/Models/User.php`
- Verify: `app/Actions/Fortify/CreateNewUser.php`
- Test: `tests/Feature/Auth/AuthenticationTest.php`
- Test: `tests/Feature/Auth/RegistrationTest.php`
- Test: `tests/Feature/Auth/EmailVerificationTest.php`
- Test: `tests/Feature/Auth/PasswordResetTest.php`

- [ ] **Step 1: Confirm the parallel Fortify revision is complete**

Run:

```powershell
git status --short
php artisan test --compact tests/Feature/Auth
```

Expected: all authentication tests pass. Existing untracked `template/` is allowed. If Fortify files are still changing or tests fail, stop this plan and finish the auth revision first.

- [ ] **Step 2: Ensure the User model supports email verification**

If `User` does not already implement `MustVerifyEmail`, apply this exact import and declaration change:

```diff
+ use Illuminate\Contracts\Auth\MustVerifyEmail;
  use Illuminate\Foundation\Auth\User as Authenticatable;

- class User extends Authenticatable
+ class User extends Authenticatable implements MustVerifyEmail
```

- [ ] **Step 3: Re-run the auth baseline**

Run:

```powershell
php artisan test --compact tests/Feature/Auth
```

Expected: PASS.

- [ ] **Step 4: Commit and push when the interface change was required**

```powershell
git add app/Models/User.php app/Actions/Fortify/CreateNewUser.php tests/Feature/Auth
git commit -m "fix stabilize fortify authentication baseline"
git push origin main
```

### Task 2: Create Patient Portal Account Persistence

**Files:**
- Create: `app/Models/PatientPortalAccount.php`
- Create: `database/factories/PatientPortalAccountFactory.php`
- Create: `database/factories/PatientFactory.php`
- Create: `database/migrations/*_create_patient_portal_accounts_table.php`
- Modify: `app/Models/User.php`
- Modify: `app/Models/Patient.php`
- Modify: `database/seeders/ClinicSeeder.php`
- Test: `tests/Feature/PatientPortal/PatientPortalAccountTest.php`

- [ ] **Step 1: Generate the model, factory, migration, and PHPUnit test**

Run:

```powershell
php artisan make:model PatientPortalAccount --factory --migration --no-interaction
php artisan make:factory PatientFactory --model=Patient --no-interaction
php artisan make:test --phpunit PatientPortal/PatientPortalAccountTest --no-interaction
```

Expected: Artisan creates the four files.

- [ ] **Step 2: Write the failing persistence test**

```php
<?php

namespace Tests\Feature\PatientPortal;

use App\Models\Patient;
use App\Models\PatientPortalAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientPortalAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_portal_account_can_be_pending_then_linked_to_one_patient(): void
    {
        $user = User::factory()->unverified()->create();
        $patient = Patient::factory()->create();

        $account = PatientPortalAccount::factory()->for($user)->pending()->create();

        $this->assertSame(PatientPortalAccount::StatusPending, $account->status);
        $this->assertNull($account->patient_id);

        $account->update([
            'patient_id' => $patient->id,
            'status' => PatientPortalAccount::StatusApproved,
            'reviewed_at' => now(),
        ]);

        $this->assertTrue($account->fresh()->patient->is($patient));
        $this->assertTrue($user->fresh()->patientPortalAccount->is($account));
    }
}
```

- [ ] **Step 3: Run the test to verify it fails**

Run:

```powershell
php artisan test --compact tests/Feature/PatientPortal/PatientPortalAccountTest.php
```

Expected: FAIL because the table, relationships, and status constants do not exist.

- [ ] **Step 4: Implement the migration**

Use the generated timestamped migration with this `up()` body:

```php
public function up(): void
{
    Schema::create('patient_portal_accounts', function (Blueprint $table): void {
        $table->uuid('id')->primary();
        $table->foreignUuid('user_id')->unique()->constrained()->cascadeOnDelete();
        $table->foreignUuid('patient_id')->nullable()->unique()->constrained()->restrictOnDelete();
        $table->string('status', 20)->default('pending')->index();
        $table->date('claimed_birth_date');
        $table->string('claimed_phone', 30);
        $table->string('claimed_medical_record_number')->nullable()->index();
        $table->char('claimed_identifier_hash', 64)->nullable()->index();
        $table->foreignUuid('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
        $table->dateTime('reviewed_at', precision: 6)->nullable();
        $table->text('review_notes')->nullable();
        $table->timestamps(6);
        $table->index(['status', 'created_at']);
    });
}

public function down(): void
{
    Schema::dropIfExists('patient_portal_accounts');
}
```

- [ ] **Step 5: Implement the model**

```php
<?php

namespace App\Models;

use Database\Factories\PatientPortalAccountFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientPortalAccount extends Model
{
    /** @use HasFactory<PatientPortalAccountFactory> */
    use HasFactory, HasUuids;

    public const string StatusPending = 'pending';
    public const string StatusApproved = 'approved';
    public const string StatusRejected = 'rejected';
    public const string StatusSuspended = 'suspended';

    protected $fillable = [
        'user_id', 'patient_id', 'status', 'claimed_birth_date', 'claimed_phone',
        'claimed_medical_record_number', 'claimed_identifier_hash', 'reviewed_by',
        'reviewed_at', 'review_notes',
    ];

    protected $attributes = ['status' => self::StatusPending];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isApproved(): bool
    {
        return $this->status === self::StatusApproved && $this->patient_id !== null;
    }

    protected function casts(): array
    {
        return [
            'claimed_birth_date' => 'date',
            'reviewed_at' => 'datetime',
        ];
    }
}
```

- [ ] **Step 6: Add relationships**

Add to `User`:

```php
public function patientPortalAccount(): HasOne
{
    return $this->hasOne(PatientPortalAccount::class);
}
```

Add to `Patient`:

```php
public function portalAccount(): HasOne
{
    return $this->hasOne(PatientPortalAccount::class);
}
```

Add the required `HasOne`, `HasFactory`, and `PatientFactory` imports, then change the trait line to:

```php
/** @use HasFactory<PatientFactory> */
use HasFactory, HasUuids;
```

- [ ] **Step 7: Implement the factory**

```php
<?php

namespace Database\Factories;

use App\Models\PatientPortalAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PatientPortalAccountFactory extends Factory
{
    protected $model = PatientPortalAccount::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'status' => PatientPortalAccount::StatusPending,
            'claimed_birth_date' => fake()->dateTimeBetween('-80 years', '-1 year')->format('Y-m-d'),
            'claimed_phone' => fake()->numerify('08##########'),
            'claimed_medical_record_number' => null,
            'claimed_identifier_hash' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (): array => ['status' => PatientPortalAccount::StatusPending]);
    }
}
```

Create `PatientFactory`:

```php
<?php

namespace Database\Factories;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PatientFactory extends Factory
{
    protected $model = Patient::class;

    public function definition(): array
    {
        $name = fake()->name();

        return [
            'medical_record_number' => 'RM-'.fake()->unique()->numerify('##########'),
            'full_name' => $name,
            'normalized_name' => str($name)->lower()->squish()->toString(),
            'birth_date' => fake()->dateTimeBetween('-80 years', '-1 year')->format('Y-m-d'),
            'sex' => fake()->randomElement(['female', 'male']),
            'phone' => fake()->numerify('08##########'),
            'status' => 'active',
            'created_by' => User::factory(),
        ];
    }
}
```

- [ ] **Step 8: Seed a patient role without staff permissions**

In `ClinicSeeder`, add `patient` to the role map with an empty permission array:

```php
'patient' => [],
```

Do not seed a demo patient account with a real identifier.

- [ ] **Step 9: Run the focused test**

```powershell
php artisan test --compact tests/Feature/PatientPortal/PatientPortalAccountTest.php
vendor/bin/pint --dirty --format agent
```

Expected: PASS.

- [ ] **Step 10: Commit and push**

```powershell
git add app/Models/PatientPortalAccount.php app/Models/User.php app/Models/Patient.php database/factories/PatientPortalAccountFactory.php database/factories/PatientFactory.php database/migrations database/seeders/ClinicSeeder.php tests/Feature/PatientPortal/PatientPortalAccountTest.php
git commit -m "feat add patient portal account persistence"
git push origin main
```

### Task 3: Register Patient Accounts Through Fortify

**Files:**
- Modify: `app/Actions/Fortify/CreateNewUser.php`
- Modify: `resources/views/auth/register.blade.php`
- Modify: `app/Http/Controllers/DashboardController.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/Auth/RegistrationTest.php`
- Test: `tests/Feature/PatientPortal/PatientPortalAccessTest.php`

- [ ] **Step 1: Generate the patient access test**

```powershell
php artisan make:test --phpunit PatientPortal/PatientPortalAccessTest --no-interaction
```

- [ ] **Step 2: Replace the registration expectation with patient onboarding behavior**

Add these assertions to `RegistrationTest`:

```php
public function test_new_registration_creates_a_pending_patient_portal_account(): void
{
    Notification::fake();
    $this->seed(ClinicSeeder::class);

    $this->post('/register', [
        'name' => 'Rani Kusuma',
        'username' => 'rani.kusuma',
        'email' => 'rani@example.test',
        'birth_date' => '1994-04-12',
        'phone' => '081234567890',
        'medical_record_number' => '',
        'password' => 'Klinik!2026',
        'password_confirmation' => 'Klinik!2026',
    ])->assertRedirect('/dashboard');

    $user = User::query()->where('email', 'rani@example.test')->firstOrFail();

    $this->assertTrue($user->hasRole('patient'));
    $this->assertSame('pending', $user->patientPortalAccount->status);
    $this->assertSame('081234567890', $user->patientPortalAccount->claimed_phone);
    Notification::assertSentTo($user, VerifyEmail::class);

    $this->get('/dashboard')->assertRedirect('/email/verify');
}
```

- [ ] **Step 3: Write the access-state test**

```php
<?php

namespace Tests\Feature\PatientPortal;

use App\Models\PatientPortalAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientPortalAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_pending_patient_is_redirected_to_account_status(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        PatientPortalAccount::factory()->for($user)->pending()->create();

        $this->actingAs($user)
            ->get('/patient-portal')
            ->assertRedirect('/patient-portal/account-status');
    }
}
```

- [ ] **Step 4: Run tests to verify they fail**

```powershell
php artisan test --compact tests/Feature/Auth/RegistrationTest.php tests/Feature/PatientPortal/PatientPortalAccessTest.php
```

Expected: FAIL because Fortify does not create portal records or redirect patient users correctly.

- [ ] **Step 5: Update `CreateNewUser` transactionally**

Implement the method with validated patient fields, a generated normalized username, role assignment, and portal account creation:

```php
public function create(array $input): User
{
    $validated = Validator::make($input, [
        'name' => ['required', 'string', 'max:255'],
        'username' => ['required', 'string', 'max:255', Rule::unique(User::class)],
        'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)],
        'birth_date' => ['required', 'date', 'before:today'],
        'phone' => ['required', 'string', 'max:30'],
        'medical_record_number' => ['nullable', 'string', 'max:255'],
        'password' => $this->passwordRules(),
    ])->validate();

    return DB::transaction(function () use ($validated): User {
        $user = User::query()->create([
            'name' => $validated['name'],
            'username' => Str::lower($validated['username']),
            'email' => Str::lower($validated['email']),
            'password' => Hash::make($validated['password']),
            'status' => 'active',
        ]);

        $patientRole = Role::query()->where('code', 'patient')->firstOrFail();
        $user->roles()->attach($patientRole, [
            'id' => (string) Str::uuid(),
            'assigned_at' => now(),
        ]);

        $user->patientPortalAccount()->create([
            'claimed_birth_date' => $validated['birth_date'],
            'claimed_phone' => $validated['phone'],
            'claimed_medical_record_number' => $validated['medical_record_number'] ?: null,
        ]);

        return $user;
    });
}
```

Add imports for `DB`, `Role`, and `Str`.

- [ ] **Step 6: Update the registration view**

Replace staff-oriented copy with patient onboarding fields. The form must include escaped values, `@csrf`, visible labels, errors, and these names:

```blade
<form method="POST" action="{{ route('register') }}" class="grid gap-4">
    @csrf
    <x-form.input name="name" label="Nama lengkap" :value="old('name')" required autocomplete="name" />
    <x-form.input name="username" label="Nama pengguna" :value="old('username')" required autocomplete="username" />
    <x-form.input name="email" type="email" label="Email" :value="old('email')" required autocomplete="email" />
    <x-form.input name="birth_date" type="date" label="Tanggal lahir" :value="old('birth_date')" required />
    <x-form.input name="phone" type="tel" label="Nomor HP" :value="old('phone')" required autocomplete="tel" />
    <x-form.input name="medical_record_number" label="Nomor rekam medis (opsional)" :value="old('medical_record_number')" />
    <x-form.input name="password" type="password" label="Kata sandi" required autocomplete="new-password" />
    <x-form.input name="password_confirmation" type="password" label="Ulangi kata sandi" required autocomplete="new-password" />
    <button class="btn-primary w-full" type="submit">Daftar sebagai pasien</button>
</form>
```

If the shared `x-form.input` component does not exist yet, keep the same markup using labeled native inputs and extract it during the frontend plan.

- [ ] **Step 7: Add patient dashboard routing**

Add named routes:

```php
Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('/patient-portal/account-status', [PatientPortalController::class, 'status'])
        ->name('patient-portal.status');
    Route::get('/patient-portal', [PatientPortalController::class, 'index'])
        ->middleware('patient.approved')
        ->name('patient-portal.index');
});
```

Update `DashboardController` so patient-role users redirect to `patient-portal.index` when approved and `patient-portal.status` otherwise. Staff behavior remains unchanged.

- [ ] **Step 8: Run tests and format**

```powershell
php artisan test --compact tests/Feature/Auth/RegistrationTest.php tests/Feature/PatientPortal/PatientPortalAccessTest.php
vendor/bin/pint --dirty --format agent
```

Expected: PASS.

- [ ] **Step 9: Commit and push**

```powershell
git add app/Actions/Fortify/CreateNewUser.php app/Http/Controllers/DashboardController.php resources/views/auth/register.blade.php routes/web.php tests/Feature/Auth/RegistrationTest.php tests/Feature/PatientPortal/PatientPortalAccessTest.php
git commit -m "feat register pending patient portal accounts"
git push origin main
```

### Task 4: Add Approved-Patient Middleware and Staff Review

**Files:**
- Create: `app/Http/Middleware/RequireApprovedPatient.php`
- Create: `app/Actions/PatientPortal/ApprovePatientPortalAccount.php`
- Create: `app/Http/Controllers/PatientPortalAccountReviewController.php`
- Create: `app/Http/Requests/PatientPortal/ApprovePatientPortalAccountRequest.php`
- Create: `app/Http/Requests/PatientPortal/RejectPatientPortalAccountRequest.php`
- Create: `resources/views/patient-portal-reviews/index.blade.php`
- Modify: `bootstrap/app.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/PatientPortal/PatientPortalApprovalTest.php`

- [ ] **Step 1: Generate files with Artisan**

```powershell
php artisan make:middleware RequireApprovedPatient --no-interaction
php artisan make:class Actions/PatientPortal/ApprovePatientPortalAccount --no-interaction
php artisan make:controller PatientPortalAccountReviewController --no-interaction
php artisan make:request PatientPortal/ApprovePatientPortalAccountRequest --no-interaction
php artisan make:request PatientPortal/RejectPatientPortalAccountRequest --no-interaction
php artisan make:test --phpunit PatientPortal/PatientPortalApprovalTest --no-interaction
```

- [ ] **Step 2: Write approval and ownership tests**

```php
public function test_registration_staff_can_approve_a_matching_patient_link(): void
{
    $this->seed(ClinicSeeder::class);
    $staff = User::query()->where('email', 'pendaftaran@sehatbersama.test')->firstOrFail();
    $patient = Patient::factory()->create(['birth_date' => '1994-04-12', 'phone' => '081234567890']);
    $user = User::factory()->create(['email_verified_at' => now()]);
    $account = PatientPortalAccount::factory()->for($user)->create([
        'claimed_birth_date' => '1994-04-12',
        'claimed_phone' => '081234567890',
    ]);

    $this->actingAs($staff)->post("/patient-portal-reviews/{$account->id}/approve", [
        'patient_id' => $patient->id,
        'review_notes' => 'Identitas cocok dengan data pendaftaran.',
    ])->assertRedirect('/patient-portal-reviews');

    $this->assertSame($patient->id, $account->fresh()->patient_id);
    $this->assertSame('approved', $account->fresh()->status);
}

public function test_non_registration_staff_cannot_review_patient_links(): void
{
    $user = User::factory()->create();
    $account = PatientPortalAccount::factory()->create();

    $this->actingAs($user)
        ->post("/patient-portal-reviews/{$account->id}/reject", ['review_notes' => 'Tidak cocok.'])
        ->assertForbidden();
}
```

- [ ] **Step 3: Run the test to verify it fails**

```powershell
php artisan test --compact tests/Feature/PatientPortal/PatientPortalApprovalTest.php
```

Expected: FAIL because routes and approval behavior do not exist.

- [ ] **Step 4: Implement middleware**

```php
<?php

namespace App\Http\Middleware;

use App\Models\PatientPortalAccount;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireApprovedPatient
{
    public function handle(Request $request, Closure $next): Response|RedirectResponse
    {
        $account = $request->user()?->patientPortalAccount;

        if ($account?->status !== PatientPortalAccount::StatusApproved || $account->patient_id === null) {
            return redirect()->route('patient-portal.status');
        }

        return $next($request);
    }
}
```

Register alias in `bootstrap/app.php`:

```php
$middleware->alias([
    'permission' => RequirePermission::class,
    'patient.approved' => RequireApprovedPatient::class,
]);
```

- [ ] **Step 5: Implement approval action**

```php
<?php

namespace App\Actions\PatientPortal;

use App\Models\Patient;
use App\Models\PatientPortalAccount;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ApprovePatientPortalAccount
{
    public function execute(PatientPortalAccount $account, Patient $patient, User $reviewer, ?string $notes): PatientPortalAccount
    {
        return DB::transaction(function () use ($account, $patient, $reviewer, $notes): PatientPortalAccount {
            $lockedAccount = PatientPortalAccount::query()->whereKey($account)->lockForUpdate()->firstOrFail();

            if ($lockedAccount->status !== PatientPortalAccount::StatusPending) {
                throw ValidationException::withMessages(['account' => 'Permintaan ini sudah diproses.']);
            }

            if ($patient->status !== 'active') {
                throw ValidationException::withMessages(['patient_id' => 'Pasien tidak aktif.']);
            }

            $lockedAccount->update([
                'patient_id' => $patient->id,
                'status' => PatientPortalAccount::StatusApproved,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
                'review_notes' => $notes,
            ]);

            return $lockedAccount->fresh();
        }, attempts: 5);
    }
}
```

- [ ] **Step 6: Implement Form Requests**

Approval rules:

```php
public function authorize(): bool
{
    return $this->user()?->hasPermission('patients.manage') === true;
}

public function rules(): array
{
    return [
        'patient_id' => ['required', 'uuid', 'exists:patients,id'],
        'review_notes' => ['nullable', 'string', 'max:1000'],
    ];
}
```

Rejection rules:

```php
public function authorize(): bool
{
    return $this->user()?->hasPermission('patients.manage') === true;
}

public function rules(): array
{
    return ['review_notes' => ['required', 'string', 'max:1000']];
}
```

- [ ] **Step 7: Implement controller and routes**

Controller methods remain thin and record audit events after approval or rejection. Add routes under `auth` plus `permission:patients.manage`:

```php
Route::get('/patient-portal-reviews', [PatientPortalAccountReviewController::class, 'index'])
    ->name('patient-portal-reviews.index');
Route::post('/patient-portal-reviews/{patientPortalAccount}/approve', [PatientPortalAccountReviewController::class, 'approve'])
    ->name('patient-portal-reviews.approve');
Route::post('/patient-portal-reviews/{patientPortalAccount}/reject', [PatientPortalAccountReviewController::class, 'reject'])
    ->name('patient-portal-reviews.reject');
```

Use this exact index query:

```php
$accounts = PatientPortalAccount::query()
    ->select([
        'id', 'user_id', 'status', 'claimed_birth_date', 'claimed_phone',
        'claimed_medical_record_number', 'created_at',
    ])
    ->with('user:id,name,email')
    ->where('status', PatientPortalAccount::StatusPending)
    ->latest()
    ->paginate(25);

return view('patient-portal-reviews.index', ['accounts' => $accounts]);
```

- [ ] **Step 8: Add the review view**

Render pending records with claimed identity, safe candidate search links, approve/reject forms, explicit labels, CSRF fields, and no raw NIK.

- [ ] **Step 9: Run focused tests and format**

```powershell
php artisan test --compact tests/Feature/PatientPortal/PatientPortalApprovalTest.php tests/Feature/PatientPortal/PatientPortalAccessTest.php
vendor/bin/pint --dirty --format agent
```

Expected: PASS.

- [ ] **Step 10: Commit and push**

```powershell
git add app/Actions/PatientPortal app/Http/Controllers/PatientPortalAccountReviewController.php app/Http/Middleware/RequireApprovedPatient.php app/Http/Requests/PatientPortal bootstrap/app.php resources/views/patient-portal-reviews routes/web.php tests/Feature/PatientPortal
git commit -m "feat approve patient portal identity links"
git push origin main
```

### Task 5: Add Appointment Models, Events, and Capacity Schema

**Files:**
- Create: `app/Models/Appointment.php`
- Create: `app/Models/AppointmentEvent.php`
- Create: `database/factories/AppointmentFactory.php`
- Create: `database/migrations/*_extend_appointments_for_patient_booking.php`
- Create: `database/migrations/*_create_appointment_events_table.php`
- Modify: `app/Models/Registration.php`
- Modify: `app/Models/ProviderSchedule.php`
- Test: `tests/Feature/PatientPortal/AppointmentPersistenceTest.php`

- [ ] **Step 1: Generate files**

```powershell
php artisan make:model Appointment --factory --no-interaction
php artisan make:model AppointmentEvent --no-interaction
php artisan make:migration extend_appointments_for_patient_booking --table=appointments --no-interaction
php artisan make:migration create_appointment_events_table --no-interaction
php artisan make:test --phpunit PatientPortal/AppointmentPersistenceTest --no-interaction
```

- [ ] **Step 2: Write a failing persistence test**

```php
public function test_appointment_tracks_slot_and_append_only_events(): void
{
    $appointment = Appointment::factory()->create([
        'appointment_date' => '2026-08-10',
        'slot_start' => '09:00:00',
        'slot_end' => '09:30:00',
    ]);

    $event = $appointment->events()->create([
        'event_type' => 'booked',
        'performed_by' => $appointment->registration->created_by,
        'metadata_json' => ['source' => 'patient_portal'],
        'created_at' => now(),
    ]);

    $this->assertModelExists($event);
    $this->assertSame('09:30:00', $appointment->slot_end);
}
```

- [ ] **Step 3: Run the test to verify it fails**

```powershell
php artisan test --compact tests/Feature/PatientPortal/AppointmentPersistenceTest.php
```

Expected: FAIL because models and columns do not exist.

- [ ] **Step 4: Extend appointment and schedule schema**

In the appointment migration:

```php
Schema::table('appointments', function (Blueprint $table): void {
    $table->time('slot_end')->after('slot_start');
    $table->foreignUuid('rescheduled_from_id')->nullable()->after('status')->constrained('appointments')->nullOnDelete();
    $table->foreignUuid('cancelled_by')->nullable()->after('rescheduled_from_id')->constrained('users')->nullOnDelete();
    $table->dateTime('cancelled_at', precision: 6)->nullable()->after('cancelled_by');
    $table->text('cancellation_reason')->nullable()->after('cancelled_at');
    $table->timestamps(6);
    $table->index(['provider_schedule_id', 'appointment_date', 'slot_start', 'status'], 'appointments_slot_lookup_index');
});

Schema::table('provider_schedules', function (Blueprint $table): void {
    $table->unsignedSmallInteger('slot_duration_minutes')->default(30)->after('end_time');
    $table->unsignedSmallInteger('slot_capacity')->default(1)->after('slot_duration_minutes');
});
```

The `down()` method removes the index, new appointment columns, and schedule capacity fields.

- [ ] **Step 5: Create appointment events**

```php
Schema::create('appointment_events', function (Blueprint $table): void {
    $table->uuid('id')->primary();
    $table->foreignUuid('appointment_id')->constrained()->cascadeOnDelete();
    $table->string('event_type', 40)->index();
    $table->foreignUuid('performed_by')->constrained('users')->restrictOnDelete();
    $table->json('metadata_json')->nullable();
    $table->dateTime('created_at', precision: 6)->index();
});
```

- [ ] **Step 6: Implement models and relationships**

`Appointment` uses `HasUuids`, fillable lifecycle fields, date/time casts, and `registration`, `schedule`, `events`, and `rescheduledFrom` relationships. `AppointmentEvent` uses `HasUuids`, casts `metadata_json` to array and `created_at` to datetime, and exposes `appointment` and `performer` relationships.

Add to `Registration`:

```php
public function appointment(): HasOne
{
    return $this->hasOne(Appointment::class);
}
```

Add capacity fields to `ProviderSchedule::$fillable` and integer casts.

- [ ] **Step 7: Implement AppointmentFactory**

The factory creates a patient, provider schedule, registration, and a future 30-minute booked slot. Recycle shared users and patients in tests to prevent accidental duplicates.

- [ ] **Step 8: Run tests and format**

```powershell
php artisan test --compact tests/Feature/PatientPortal/AppointmentPersistenceTest.php
vendor/bin/pint --dirty --format agent
```

Expected: PASS.

- [ ] **Step 9: Commit and push**

```powershell
git add app/Models/Appointment.php app/Models/AppointmentEvent.php app/Models/Registration.php app/Models/ProviderSchedule.php database/factories/AppointmentFactory.php database/migrations tests/Feature/PatientPortal/AppointmentPersistenceTest.php
git commit -m "feat add appointment lifecycle persistence"
git push origin main
```

### Task 6: Implement Availability and Future Booking

**Files:**
- Create: `app/Services/Appointments/AppointmentAvailability.php`
- Create: `app/Actions/Appointments/BookAppointment.php`
- Create: `app/Http/Requests/Appointments/StorePatientAppointmentRequest.php`
- Create: `app/Http/Controllers/PatientAppointmentController.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/PatientPortal/AppointmentBookingTest.php`

- [ ] **Step 1: Generate files**

```powershell
php artisan make:class Services/Appointments/AppointmentAvailability --no-interaction
php artisan make:class Actions/Appointments/BookAppointment --no-interaction
php artisan make:request Appointments/StorePatientAppointmentRequest --no-interaction
php artisan make:controller PatientAppointmentController --no-interaction
php artisan make:test --phpunit PatientPortal/AppointmentBookingTest --no-interaction
```

- [ ] **Step 2: Write failing booking tests**

Cover an available slot, wrong weekday, schedule exception, full capacity, patient ownership, and a concurrent duplicate request. The happy path must assert one registration, one appointment, zero queue tickets, and a `booked` appointment event.

```php
public function test_approved_patient_can_book_a_future_available_slot_without_queue_allocation(): void
{
    [$user, $patient, $schedule] = $this->approvedPatientContext();
    $date = now()->next($this->carbonDayFor($schedule->day_of_week))->toDateString();

    $this->actingAs($user)->post('/patient-portal/appointments', [
        'provider_schedule_id' => $schedule->id,
        'appointment_date' => $date,
        'slot_start' => '09:00',
        'payer_type' => 'general',
    ])->assertRedirect('/patient-portal');

    $this->assertSame(1, Appointment::query()->count());
    $this->assertSame(0, QueueTicket::query()->count());
    $this->assertSame($patient->id, Appointment::query()->sole()->registration->patient_id);
}
```

- [ ] **Step 3: Run the test to verify it fails**

```powershell
php artisan test --compact tests/Feature/PatientPortal/AppointmentBookingTest.php
```

Expected: FAIL because availability and booking do not exist.

- [ ] **Step 4: Implement `AppointmentAvailability`**

The service must:

```php
public function isAvailable(ProviderSchedule $schedule, CarbonInterface $date, string $slotStart): bool
{
    if (! $schedule->isAvailableOn($date)) {
        return false;
    }

    $hours = $this->effectiveHours($schedule, $date);
    $start = Carbon::parse($date->toDateString().' '.$slotStart, 'Asia/Jakarta');
    $end = $start->copy()->addMinutes($schedule->slot_duration_minutes);

    if ($start->lt($hours['start']) || $end->gt($hours['end'])) {
        return false;
    }

    return Appointment::query()
        ->whereBelongsTo($schedule, 'schedule')
        ->whereDate('appointment_date', $date)
        ->whereTime('slot_start', $start->format('H:i:s'))
        ->whereIn('status', ['booked', 'checked_in'])
        ->count() < $schedule->slot_capacity;
}
```

`effectiveHours()` loads the matching schedule exception. A `closed` exception returns no availability. A replacement exception supplies replacement start and end times.

- [ ] **Step 5: Implement booking transaction**

`BookAppointment::execute()` accepts the linked patient, schedule, date, slot, payer type, actor, and idempotency-independent source string. It locks the schedule row, rechecks availability, creates a registration with `registration_date` equal to the appointment date and `channel` equal to `patient_portal`, creates the appointment and event, and returns the appointment. It does not create a queue ticket.

- [ ] **Step 6: Implement request and controller**

The request authorizes only approved patient users and validates schedule UUID, future date, `H:i` slot, and payer type. The controller injects the patient from `$request->user()->patientPortalAccount->patient`, never from client input.

Add routes:

```php
Route::post('/patient-portal/appointments', [PatientAppointmentController::class, 'store'])
    ->middleware(['auth', 'verified', 'patient.approved', 'throttle:patient-actions'])
    ->name('patient-portal.appointments.store');
```

- [ ] **Step 7: Run focused tests**

```powershell
php artisan test --compact tests/Feature/PatientPortal/AppointmentBookingTest.php
vendor/bin/pint --dirty --format agent
```

Expected: PASS.

- [ ] **Step 8: Commit and push**

```powershell
git add app/Services/Appointments app/Actions/Appointments/BookAppointment.php app/Http/Requests/Appointments/StorePatientAppointmentRequest.php app/Http/Controllers/PatientAppointmentController.php routes/web.php tests/Feature/PatientPortal/AppointmentBookingTest.php
git commit -m "feat book future patient appointments"
git push origin main
```

### Task 7: Implement Reschedule and Cancellation

**Files:**
- Create: `app/Actions/Appointments/RescheduleAppointment.php`
- Create: `app/Actions/Appointments/CancelAppointment.php`
- Create: `app/Http/Requests/Appointments/ReschedulePatientAppointmentRequest.php`
- Create: `app/Http/Requests/Appointments/CancelPatientAppointmentRequest.php`
- Modify: `app/Http/Controllers/PatientAppointmentController.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/PatientPortal/AppointmentLifecycleTest.php`

- [ ] **Step 1: Generate files and failing tests**

```powershell
php artisan make:class Actions/Appointments/RescheduleAppointment --no-interaction
php artisan make:class Actions/Appointments/CancelAppointment --no-interaction
php artisan make:request Appointments/ReschedulePatientAppointmentRequest --no-interaction
php artisan make:request Appointments/CancelPatientAppointmentRequest --no-interaction
php artisan make:test --phpunit PatientPortal/AppointmentLifecycleTest --no-interaction
```

Tests must cover owner-only access, future-only mutation, unavailable target slot, cancelled records preserved, status synchronization with registration, and append-only events.

- [ ] **Step 2: Run the test to verify it fails**

```powershell
php artisan test --compact tests/Feature/PatientPortal/AppointmentLifecycleTest.php
```

- [ ] **Step 3: Implement rescheduling**

Inside a retrying transaction, lock the appointment, registration, old schedule, and target schedule. Reject non-booked or past appointments. Recheck target availability, update schedule/date/times, create a `rescheduled` event with old and new slot metadata, and keep the same booking code.

- [ ] **Step 4: Implement cancellation**

Inside a retrying transaction, lock the appointment and registration. Reject checked-in, completed, already cancelled, or past appointments. Update both statuses to `cancelled`, set cancellation actor/time/reason, and append a `cancelled` event.

- [ ] **Step 5: Add routes and thin controller methods**

```php
Route::put('/patient-portal/appointments/{appointment}', [PatientAppointmentController::class, 'update'])
    ->name('patient-portal.appointments.update');
Route::delete('/patient-portal/appointments/{appointment}', [PatientAppointmentController::class, 'destroy'])
    ->name('patient-portal.appointments.destroy');
```

The controller must verify the appointment registration belongs to the current linked patient before invoking the action.

- [ ] **Step 6: Run tests and format**

```powershell
php artisan test --compact tests/Feature/PatientPortal/AppointmentLifecycleTest.php
vendor/bin/pint --dirty --format agent
```

- [ ] **Step 7: Commit and push**

```powershell
git add app/Actions/Appointments app/Http/Requests/Appointments app/Http/Controllers/PatientAppointmentController.php routes/web.php tests/Feature/PatientPortal/AppointmentLifecycleTest.php
git commit -m "feat reschedule and cancel patient appointments"
git push origin main
```

### Task 8: Allocate Queue and Visit at Patient Check-in

**Files:**
- Create: `app/Actions/Appointments/CheckInAppointment.php`
- Create: `app/Http/Requests/Appointments/CheckInPatientAppointmentRequest.php`
- Modify: `app/Http/Controllers/PatientAppointmentController.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/PatientPortal/PatientCheckInTest.php`
- Test: `tests/Feature/Queue/QueueWorkflowTest.php`

- [ ] **Step 1: Write failing tests**

Test same-day check-in, too-early and too-late windows, non-owner denial, one queue ticket, one visit, repeated request idempotency, and unchanged front-desk queue behavior.

- [ ] **Step 2: Run tests to verify they fail**

```powershell
php artisan test --compact tests/Feature/PatientPortal/PatientCheckInTest.php tests/Feature/Queue/QueueWorkflowTest.php
```

- [ ] **Step 3: Implement `CheckInAppointment`**

Reuse the current counter-locking behavior from `CheckInRegistration`, but create the queue ticket only if none exists. The action must lock appointment, registration, and counter rows, validate `appointment_date` equals the current WIB date, enforce the configured check-in window, update statuses, create one queue event, and use `Visit::firstOrCreate()`.

The returned result must include the appointment, queue ticket, and visit so UI and AI integrations do not re-query ambiguously.

- [ ] **Step 4: Add a patient check-in route**

```php
Route::post('/patient-portal/appointments/{appointment}/check-in', [PatientAppointmentController::class, 'checkIn'])
    ->name('patient-portal.appointments.check-in');
```

- [ ] **Step 5: Run tests and format**

```powershell
php artisan test --compact tests/Feature/PatientPortal/PatientCheckInTest.php tests/Feature/Queue/QueueWorkflowTest.php
vendor/bin/pint --dirty --format agent
```

Expected: PASS and existing staff queue behavior remains green.

- [ ] **Step 6: Commit and push**

```powershell
git add app/Actions/Appointments/CheckInAppointment.php app/Http/Requests/Appointments/CheckInPatientAppointmentRequest.php app/Http/Controllers/PatientAppointmentController.php routes/web.php tests/Feature/PatientPortal/PatientCheckInTest.php tests/Feature/Queue/QueueWorkflowTest.php
git commit -m "feat check in patient appointments atomically"
git push origin main
```

### Task 9: Add Minimized Visit History and Patient Dashboard Data

**Files:**
- Create: `app/Queries/PatientVisitHistory.php`
- Create: `app/Http/Controllers/PatientPortalController.php`
- Create: `resources/views/patient-portal/account-status.blade.php`
- Create: `resources/views/patient-portal/index.blade.php`
- Modify: `app/Models/Visit.php`
- Test: `tests/Feature/PatientPortal/PatientVisitHistoryTest.php`

- [ ] **Step 1: Write history privacy tests**

Create visits for two patients and clinical entries for the linked patient. Assert the patient sees only their own date, service, provider name, status, booking code, arrival, and completion fields. Assert diagnosis, chief complaint, clinical content, prescription, and another patient name are absent.

- [ ] **Step 2: Run the test to verify it fails**

```powershell
php artisan test --compact tests/Feature/PatientPortal/PatientVisitHistoryTest.php
```

- [ ] **Step 3: Implement minimized query**

```php
<?php

namespace App\Queries;

use App\Models\Patient;
use App\Models\Visit;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PatientVisitHistory
{
    public function paginate(Patient $patient, int $perPage = 10): LengthAwarePaginator
    {
        return Visit::query()
            ->select(['id', 'patient_id', 'registration_id', 'visit_date', 'status', 'arrived_at', 'completed_at'])
            ->whereBelongsTo($patient)
            ->with([
                'registration:id,patient_id,provider_schedule_id,requested_service,booking_code',
                'registration.appointment:id,registration_id,appointment_date,slot_start,status',
                'registration.schedule:id,provider_user_id,service_type',
                'registration.schedule.provider:id,name',
            ])
            ->latest('visit_date')
            ->paginate($perPage);
    }
}
```

Add missing `registration` relationships on `Visit` and `schedule` on `Registration` with explicit return types.

- [ ] **Step 4: Implement portal controller**

The controller loads the approved account with patient, next eligible appointment, current queue ticket, and paginated minimized history. It records an audit event for patient self-history access without storing returned content in metadata.

- [ ] **Step 5: Add functional views**

The account-status view displays pending, rejected, and suspended states. The portal index displays next appointment, current queue, recent visits, and manual appointment actions. Use existing components and tokens; the frontend plan will apply final visual polish.

- [ ] **Step 6: Run tests and format**

```powershell
php artisan test --compact tests/Feature/PatientPortal/PatientVisitHistoryTest.php tests/Feature/PatientPortal
vendor/bin/pint --dirty --format agent
```

- [ ] **Step 7: Commit and push**

```powershell
git add app/Queries/PatientVisitHistory.php app/Http/Controllers/PatientPortalController.php app/Models/Visit.php app/Models/Registration.php resources/views/patient-portal tests/Feature/PatientPortal
git commit -m "feat add private patient visit history"
git push origin main
```

### Task 10: Final Patient Portal Verification

**Files:**
- Verify: files changed in Tasks 1-9.

- [ ] **Step 1: Run the patient and regression suites**

```powershell
php artisan test --compact tests/Feature/PatientPortal
php artisan test --compact tests/Feature/Auth tests/Feature/Queue tests/Feature/Encounters
```

Expected: PASS.

- [ ] **Step 2: Run formatter and build**

```powershell
vendor/bin/pint --dirty --format agent
npm run build
```

Expected: Pint completes and Vite builds successfully.

- [ ] **Step 3: Verify schema through DBHub**

Use DBHub to confirm `patient_portal_accounts`, appointment lifecycle columns, appointment events, indexes, and foreign keys. Use read-only queries to confirm no patient account has duplicate `patient_id` or `user_id`.

- [ ] **Step 4: Verify routes**

```powershell
php artisan route:list --path=patient-portal --except-vendor
```

Expected: status, dashboard, appointment, check-in, and review routes use the intended middleware.

- [ ] **Step 5: Resolve failures in their owning task**

If verification fails, return to the task that owns the failing file, apply its exact test-first cycle, commit that focused fix there, and rerun this verification task. This task creates no standalone commit.

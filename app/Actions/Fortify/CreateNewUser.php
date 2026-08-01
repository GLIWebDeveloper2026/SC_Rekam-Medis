<?php

namespace App\Actions\Fortify;

use App\Actions\Patients\RegisterPatient;
use App\Models\PatientPortalAccount;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    public function __construct(private readonly RegisterPatient $registerPatient) {}

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, mixed>  $input
     *
     * @throws ValidationException
     */
    public function create(array $input): User
    {
        $validated = Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9._-]+$/',
                Rule::unique(User::class),
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'birth_date' => ['required', 'date', 'before:today'],
            'sex' => ['required', 'in:male,female,unknown'],
            'phone' => ['required', 'string', 'max:30', 'regex:/^[0-9+(). -]+$/'],
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

            $patientRole = Role::query()->firstOrCreate(
                ['code' => 'patient'],
                ['name' => 'Patient', 'description' => 'Akun pasien dengan akses langsung ke portal.'],
            );
            $user->roles()->attach($patientRole, [
                'id' => (string) Str::uuid(),
                'assigned_at' => now(),
            ]);

            $patient = $this->registerPatient->execute([
                'full_name' => $validated['name'],
                'birth_date' => $validated['birth_date'],
                'sex' => $validated['sex'],
                'phone' => $validated['phone'],
            ], $user->id);

            $user->patientPortalAccount()->create([
                'patient_id' => $patient->id,
                'status' => PatientPortalAccount::StatusApproved,
                'claimed_birth_date' => $validated['birth_date'],
                'claimed_phone' => Str::of($validated['phone'])->replaceMatches('/\D+/', '')->toString(),
                'claimed_medical_record_number' => $patient->medical_record_number,
                'claimed_identifier_hash' => hash('sha256', $patient->medical_record_number),
            ]);

            return $user;
        }, attempts: 5);
    }
}

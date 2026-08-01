<?php

namespace App\Actions\Fortify;

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
            'phone' => ['required', 'string', 'max:30', 'regex:/^[0-9+(). -]+$/'],
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

            $patientRole = Role::query()->firstOrCreate(
                ['code' => 'patient'],
                ['name' => 'Patient', 'description' => 'Akun portal pasien yang memerlukan persetujuan staf.'],
            );
            $user->roles()->attach($patientRole, [
                'id' => (string) Str::uuid(),
                'assigned_at' => now(),
            ]);

            $medicalRecordNumber = filled($validated['medical_record_number'] ?? null)
                ? Str::upper(Str::squish($validated['medical_record_number']))
                : null;

            $user->patientPortalAccount()->create([
                'claimed_birth_date' => $validated['birth_date'],
                'claimed_phone' => Str::of($validated['phone'])->replaceMatches('/\D+/', '')->toString(),
                'claimed_medical_record_number' => $medicalRecordNumber,
                'claimed_identifier_hash' => $medicalRecordNumber === null
                    ? null
                    : hash('sha256', $medicalRecordNumber),
            ]);

            return $user;
        }, attempts: 5);
    }
}

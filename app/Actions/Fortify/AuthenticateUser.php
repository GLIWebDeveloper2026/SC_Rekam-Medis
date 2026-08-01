<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthenticateUser
{
    public function authenticate(Request $request): ?User
    {
        $user = User::query()
            ->where('email', Str::lower($request->string('email')->trim()->toString()))
            ->first();

        if ($user === null || ! $user->isActive() || ! Hash::check($request->string('password')->toString(), $user->password)) {
            return null;
        }

        if (Hash::needsRehash($user->password)) {
            $user->forceFill(['password' => $request->string('password')->toString()])->save();
        }

        return $user;
    }
}

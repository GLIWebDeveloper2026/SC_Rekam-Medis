<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Services\AuditTrail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function __construct(private readonly AuditTrail $auditTrail) {}

    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $key = 'login:'.strtolower($request->string('email')->toString()).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'email' => 'Terlalu banyak percobaan masuk. Coba kembali dalam '.RateLimiter::availableIn($key).' detik.',
            ]);
        }

        $user = User::query()->where('email', $request->string('email'))->first();

        if ($user === null || ! $user->isActive() || ! Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            RateLimiter::hit($key, 60);

            if ($user !== null) {
                $attempts = min(255, $user->failed_login_attempts + 1);
                $user->forceFill([
                    'failed_login_attempts' => $attempts,
                    'locked_until' => $attempts >= 5 ? now()->addMinutes(15) : $user->locked_until,
                ])->save();
            }

            $this->auditTrail->record(
                action: 'session.login_failed',
                resourceType: 'user',
                resourceId: $user?->id,
                result: 'failed',
                user: $user,
                reason: 'Invalid credentials or inactive account.',
            );

            throw ValidationException::withMessages([
                'email' => 'Email, kata sandi, atau status akun tidak valid.',
            ]);
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();
        $user->forceFill([
            'failed_login_attempts' => 0,
            'locked_until' => null,
            'last_login_at' => now(),
        ])->save();

        $this->auditTrail->record(
            action: 'session.login',
            resourceType: 'user',
            resourceId: $user->id,
            result: 'success',
            user: $user,
        );

        return redirect()->intended(route('dashboard', absolute: false));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();
        $this->auditTrail->record(
            action: 'session.logout',
            resourceType: 'user',
            resourceId: $user?->id,
            result: 'success',
            user: $user,
        );

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}

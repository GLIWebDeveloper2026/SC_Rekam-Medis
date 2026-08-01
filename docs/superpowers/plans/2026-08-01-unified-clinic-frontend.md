# Unified Clinic Frontend Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Adapt the strongest visual ideas from `template/` into one responsive Tailwind CSS 4 design system for the public landing page, patient portal, authentication pages, and staff application.

**Architecture:** Keep Blade server rendering and Alpine CSP. Build reusable Blade components around the existing teal brand, self-host typography, and move every interaction into `resources/js/app.js`. Public, patient, and staff surfaces share tokens while retaining layouts appropriate to their information density.

**Tech Stack:** Laravel Blade, Tailwind CSS 4, Alpine.js CSP 3, Vite 8, Lucide, Poppins, Nunito Sans, OpenAI image generation for production visual assets.

---

## Git Safety

- Begin implementation only after the parallel Fortify auth session is complete and the intended implementation worktree is on `main`.
- Before every commit, run `git status --short` and `git diff --cached --name-only`.
- Never stage or commit `.agent/`, `.agents/`, `.pi/`, `.playwright/`, `.playwright-mcp/`, or `.codex/`.
- Never stage `template/` wholesale. Migrate only deliberate assets and markup into `public/` and `resources/`.
- Stage the explicit paths listed in each task. If another session changes a listed file concurrently, stop and reconcile before committing.

## File Structure

- `public/images/clinic/*`: generated hero and care-team photography with reserved dimensions.
- `resources/css/app.css`: Tailwind v4 sources, self-hosted fonts, semantic tokens, base styles, and reusable component classes.
- `resources/js/app.js`: CSP-safe Alpine components and Lucide registration.
- `resources/views/components/ui/*`: buttons, panels, badges, form inputs, empty states, and feedback blocks.
- `resources/views/components/layouts/public.blade.php`: public landing and public auth shell.
- `resources/views/components/layouts/patient.blade.php`: approved patient shell.
- `resources/views/components/layouts/app.blade.php`: unified staff operations shell.
- `app/Http/Controllers/PublicHomeController.php`: public schedule and provider data.
- `resources/views/welcome.blade.php`: dynamic public landing page.
- `resources/views/auth/*`: Fortify views using the shared component system.
- `resources/views/patient-portal/*`: polished account state and patient dashboard.
- `tests/Feature/Frontend/*`: public, auth, patient, and staff markup contracts.

### Task 1: Create Real Visual Assets and Self-Hosted Typography

**Files:**
- Create: `public/images/clinic/hero-care.webp`
- Create: `public/images/clinic/care-team.webp`
- Modify: `package.json`
- Modify: `package-lock.json`
- Modify: `resources/css/app.css`

- [ ] **Step 1: Generate healthcare imagery with the imagegen skill**

Invoke the `imagegen` skill and create:

- `hero-care.webp`, 1600x1200, documentary Indonesian primary-care setting, natural daylight, teal detail accents, no readable logos, no text overlay, no visible patient identifiers.
- `care-team.webp`, 1400x900, small Indonesian clinic team in a bright consultation corridor, realistic and calm, no text overlay, no visible patient records.

Expected: both images are real raster assets, not CSS illustrations or hand-rolled SVGs.

- [ ] **Step 2: Install approved self-hosted font packages**

Run:

```powershell
npm install @fontsource/poppins @fontsource-variable/nunito-sans
```

Expected: `package.json` and `package-lock.json` include only these two new font dependencies.

- [ ] **Step 3: Add font imports at the top of `resources/css/app.css`**

```css
@import '@fontsource/poppins/500.css';
@import '@fontsource/poppins/600.css';
@import '@fontsource/poppins/700.css';
@import '@fontsource-variable/nunito-sans';
@import 'tailwindcss';
```

Remove remote Google Fonts imports from every Blade and CSS file.

- [ ] **Step 4: Build assets**

```powershell
npm run build
```

Expected: Vite builds fonts, CSS, JS, and images with no missing asset error.

- [ ] **Step 5: Commit and push**

```powershell
git add package.json package-lock.json resources/css/app.css public/images/clinic/hero-care.webp public/images/clinic/care-team.webp
git commit -m "feat add clinic imagery and self hosted fonts"
git push origin main
```

### Task 2: Define Tailwind Tokens and Reusable Blade Components

**Files:**
- Modify: `resources/css/app.css`
- Create: `resources/views/components/ui/button.blade.php`
- Create: `resources/views/components/ui/panel.blade.php`
- Create: `resources/views/components/ui/status-badge.blade.php`
- Create: `resources/views/components/form/input.blade.php`
- Create: `resources/views/components/ui/empty-state.blade.php`
- Create: `resources/views/components/ui/feedback.blade.php`
- Test: `tests/Feature/Frontend/ComponentRenderingTest.php`

- [ ] **Step 1: Generate components and test**

```powershell
php artisan make:component Ui/Button --view --no-interaction
php artisan make:component Ui/Panel --view --no-interaction
php artisan make:component Ui/StatusBadge --view --no-interaction
php artisan make:component Form/Input --view --no-interaction
php artisan make:component Ui/EmptyState --view --no-interaction
php artisan make:component Ui/Feedback --view --no-interaction
php artisan make:test --phpunit Frontend/ComponentRenderingTest --no-interaction
```

- [ ] **Step 2: Write a failing component contract test**

```php
<?php

namespace Tests\Feature\Frontend;

use Tests\TestCase;

class ComponentRenderingTest extends TestCase
{
    public function test_ui_components_render_accessible_contracts(): void
    {
        $html = $this->blade(<<<'BLADE'
            <x-ui.button href="/register">Daftar pasien</x-ui.button>
            <x-form.input name="email" type="email" label="Email" required />
            <x-ui.status-badge tone="success">Disetujui</x-ui.status-badge>
        BLADE);

        $html->assertSee('Daftar pasien')
            ->assertSee('Email')
            ->assertSee('name="email"', false)
            ->assertSee('Disetujui');
    }
}
```

- [ ] **Step 3: Run the test to verify it fails**

```powershell
php artisan test --compact tests/Feature/Frontend/ComponentRenderingTest.php
```

Expected: FAIL because components do not exist.

- [ ] **Step 4: Replace the Tailwind theme with semantic tokens**

Keep all current `@source` declarations, then use:

```css
@theme static {
    --font-heading: 'Poppins', ui-sans-serif, sans-serif;
    --font-sans: 'Nunito Sans Variable', ui-sans-serif, sans-serif;
    --color-clinic-50: #f1fbf8;
    --color-clinic-100: #e4f5f1;
    --color-clinic-200: #c8ebe3;
    --color-clinic-300: #95d9ca;
    --color-clinic-400: #5fc1ae;
    --color-clinic-500: #2fa791;
    --color-clinic-600: #238777;
    --color-clinic-700: #206c61;
    --color-clinic-800: #1e574f;
    --color-clinic-900: #173f39;
    --color-info: #3f76a8;
    --color-danger: #c9504b;
    --color-success: #2f8f69;
    --color-warning: #a66c21;
    --color-ink: #182724;
    --color-muted: #65736f;
    --color-surface: #f4f8f7;
    --radius-control: 0.625rem;
    --radius-panel: 1rem;
    --shadow-panel: 0 24px 60px -44px rgb(20 63 57 / 0.62);
}
```

Base styles must keep escaped text, focus-visible rings, `x-cloak`, reduced motion, stable `min-height: 100dvh`, and no pure black or pure white theme tokens.

- [ ] **Step 5: Implement the button component**

```blade
@props([
    'href' => null,
    'variant' => 'primary',
    'type' => 'button',
])

@php
    $classes = match ($variant) {
        'secondary' => 'border border-slate-300 bg-white text-slate-800 hover:bg-slate-50',
        'danger' => 'bg-danger text-white hover:bg-red-700',
        'ghost' => 'text-slate-700 hover:bg-slate-100',
        default => 'bg-clinic-600 text-white hover:bg-clinic-700',
    };
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => "inline-flex min-h-11 items-center justify-center gap-2 rounded-[var(--radius-control)] px-4 py-2.5 font-bold transition active:scale-[0.98] {$classes}"]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => "inline-flex min-h-11 items-center justify-center gap-2 rounded-[var(--radius-control)] px-4 py-2.5 font-bold transition active:scale-[0.98] {$classes}"]) }}>
        {{ $slot }}
    </button>
@endif
```

- [ ] **Step 6: Implement the input component**

```blade
@props([
    'name',
    'label',
    'type' => 'text',
    'value' => null,
    'help' => null,
])

<div class="grid gap-2">
    <label for="{{ $name }}" class="text-sm font-bold text-slate-800">{{ $label }}</label>
    <input
        id="{{ $name }}"
        name="{{ $name }}"
        type="{{ $type }}"
        value="{{ old($name, $value) }}"
        {{ $attributes->merge(['class' => 'form-input']) }}
    >
    @if ($help)
        <p class="text-sm text-slate-500">{{ $help }}</p>
    @endif
    @error($name)
        <p class="text-sm font-semibold text-danger">{{ $message }}</p>
    @enderror
</div>
```

Implement the remaining components exactly as follows.

`panel.blade.php`:

```blade
<section {{ $attributes->merge(['class' => 'rounded-[var(--radius-panel)] border border-slate-200/80 bg-white/95 p-5 shadow-[var(--shadow-panel)]']) }}>
    {{ $slot }}
</section>
```

`status-badge.blade.php`:

```blade
@props(['tone' => 'neutral'])
@php
    $classes = match ($tone) {
        'success' => 'bg-emerald-100 text-emerald-800',
        'warning' => 'bg-amber-100 text-amber-800',
        'danger' => 'bg-red-100 text-red-800',
        'info' => 'bg-blue-100 text-blue-800',
        default => 'bg-slate-100 text-slate-700',
    };
@endphp
<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-md px-2.5 py-1 text-xs font-bold {$classes}"]) }}>{{ $slot }}</span>
```

`empty-state.blade.php`:

```blade
@props(['title', 'description'])
<div {{ $attributes->merge(['class' => 'grid min-h-48 place-items-center rounded-[var(--radius-panel)] border border-dashed border-slate-300 bg-slate-50 p-8 text-center']) }}>
    <div class="max-w-md">
        <h3 class="font-heading text-lg font-bold text-slate-900">{{ $title }}</h3>
        <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $description }}</p>
        @if ($slot->isNotEmpty())
            <div class="mt-5 flex justify-center">{{ $slot }}</div>
        @endif
    </div>
</div>
```

`feedback.blade.php`:

```blade
@props(['tone' => 'info'])
@php
    $classes = match ($tone) {
        'success' => 'border-emerald-200 bg-emerald-50 text-emerald-900',
        'warning' => 'border-amber-200 bg-amber-50 text-amber-900',
        'danger' => 'border-red-200 bg-red-50 text-red-900',
        default => 'border-blue-200 bg-blue-50 text-blue-900',
    };
@endphp
<div role="status" {{ $attributes->merge(['class' => "rounded-[var(--radius-control)] border p-4 text-sm font-semibold {$classes}"]) }}>{{ $slot }}</div>
```

- [ ] **Step 7: Run tests, format, and build**

```powershell
php artisan test --compact tests/Feature/Frontend/ComponentRenderingTest.php
vendor/bin/pint --dirty --format agent
npm run build
```

Expected: PASS.

- [ ] **Step 8: Commit and push**

```powershell
git add resources/css/app.css resources/views/components tests/Feature/Frontend/ComponentRenderingTest.php
git commit -m "feat add unified clinic ui components"
git push origin main
```

### Task 3: Build a Dynamic Public Landing Page

**Files:**
- Create: `app/Http/Controllers/PublicHomeController.php`
- Create: `resources/views/components/layouts/public.blade.php`
- Modify: `resources/views/welcome.blade.php`
- Modify: `routes/web.php`
- Modify: `resources/js/app.js`
- Test: `tests/Feature/PublicLandingPageTest.php`

- [ ] **Step 1: Generate the controller**

```powershell
php artisan make:controller PublicHomeController --invokable --no-interaction
```

- [ ] **Step 2: Update the failing landing contract**

```php
public function test_public_homepage_shows_live_schedule_and_no_chatbot(): void
{
    $this->get('/')
        ->assertOk()
        ->assertSee('Klinik Pratama Sehat Bersama')
        ->assertSee('Jadwal praktik')
        ->assertSee('Daftar sebagai pasien')
        ->assertSee('Masuk staf')
        ->assertDontSee('AI Scheduling')
        ->assertDontSee('Asisten Klinik');
}
```

Add a database-backed schedule test that creates an active provider schedule and asserts provider name, service, weekday, and hours appear.

- [ ] **Step 3: Run the test to verify it fails**

```powershell
php artisan test --compact tests/Feature/PublicLandingPageTest.php
```

- [ ] **Step 4: Implement `PublicHomeController`**

```php
<?php

namespace App\Http\Controllers;

use App\Models\ProviderSchedule;
use Illuminate\View\View;

class PublicHomeController extends Controller
{
    public function __invoke(): View
    {
        $schedules = ProviderSchedule::query()
            ->select(['id', 'provider_user_id', 'service_type', 'day_of_week', 'start_time', 'end_time'])
            ->with('provider:id,name')
            ->where('status', 'active')
            ->whereDate('effective_from', '<=', now())
            ->where(function ($query): void {
                $query->whereNull('effective_until')->orWhereDate('effective_until', '>=', now());
            })
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        return view('welcome', ['schedules' => $schedules]);
    }
}
```

Replace the root route with `Route::get('/', PublicHomeController::class)->name('home');`.

- [ ] **Step 5: Implement the public layout**

The layout includes metadata, Vite assets, a one-line desktop navigation under 80px, mobile menu controlled by `x-data="clinicLanding"`, and a concise footer. It must not include authenticated navigation, chat markup, or remote font/style URLs.

- [ ] **Step 6: Implement the landing sections**

Use this section order:

```blade
<x-layouts.public title="Klinik Pratama Sehat Bersama">
    <section id="beranda" class="mx-auto grid min-h-[calc(100dvh-5rem)] max-w-7xl items-center gap-10 px-4 py-14 md:grid-cols-[1.05fr_.95fr] lg:px-8">
        <div class="max-w-2xl">
            <p class="mb-4 text-sm font-bold text-clinic-700">Perawatan keluarga yang terhubung</p>
            <h1 class="font-heading text-4xl font-bold leading-[1.05] tracking-tight text-slate-950 md:text-6xl">Layanan klinik yang jelas sejak pendaftaran.</h1>
            <p class="mt-6 max-w-[58ch] text-lg leading-relaxed text-slate-600">Jadwal dokter, pendaftaran pasien, antrean, pemeriksaan, dan farmasi berada dalam satu sistem yang aman.</p>
            <div class="mt-8 flex flex-wrap gap-3">
                <x-ui.button href="{{ route('register') }}">Daftar sebagai pasien</x-ui.button>
                <x-ui.button href="{{ route('login') }}" variant="secondary">Masuk staf</x-ui.button>
            </div>
        </div>
        <img src="{{ asset('images/clinic/hero-care.webp') }}" alt="Tenaga kesehatan mendampingi pasien di ruang klinik" width="1600" height="1200" class="aspect-[4/3] w-full rounded-2xl object-cover shadow-[var(--shadow-panel)]" fetchpriority="high">
    </section>

    <section id="layanan" class="mx-auto max-w-7xl px-4 py-20 lg:px-8">
        <h2 class="font-heading text-3xl font-bold text-slate-950 md:text-4xl">Layanan untuk setiap tahap kunjungan</h2>
        <div class="mt-10 grid gap-6 md:grid-cols-[1.2fr_.8fr]">
            <x-ui.panel><h3 class="font-heading text-xl font-bold">Pemeriksaan umum dan gigi</h3><p class="mt-3 text-slate-600">Pendaftaran, jadwal, antrean, pemeriksaan, dan tindak lanjut tercatat dalam satu alur.</p></x-ui.panel>
            <x-ui.panel class="bg-clinic-50"><h3 class="font-heading text-xl font-bold">Farmasi terhubung</h3><p class="mt-3 text-slate-600">Resep dan penyerahan obat tetap mengikuti otorisasi tenaga medis.</p></x-ui.panel>
        </div>
    </section>

    <section id="jadwal" class="bg-clinic-50/70 py-20">
        <div class="mx-auto max-w-7xl px-4 lg:px-8">
            <h2 class="font-heading text-3xl font-bold text-slate-950 md:text-4xl">Jadwal praktik</h2>
            <div class="mt-10 grid gap-4 md:grid-cols-2">
                @forelse ($schedules as $schedule)
                    <x-ui.panel>
                        <p class="font-heading text-lg font-bold text-slate-900">{{ $schedule->provider->name }}</p>
                        <p class="mt-1 text-sm text-slate-600">{{ str($schedule->service_type)->headline() }}</p>
                        <p class="mt-4 font-bold text-clinic-700">Hari {{ $schedule->day_of_week }}, {{ substr($schedule->start_time, 0, 5) }}-{{ substr($schedule->end_time, 0, 5) }} WIB</p>
                    </x-ui.panel>
                @empty
                    <x-ui.empty-state title="Jadwal sedang diperbarui" description="Hubungi klinik untuk memastikan waktu layanan hari ini." />
                @endforelse
            </div>
        </div>
    </section>

    <section id="tim-medis" class="mx-auto grid max-w-7xl items-center gap-10 px-4 py-20 md:grid-cols-2 lg:px-8">
        <img src="{{ asset('images/clinic/care-team.webp') }}" alt="Tim Klinik Pratama Sehat Bersama" width="1400" height="900" class="aspect-[14/9] w-full rounded-2xl object-cover">
        <div><h2 class="font-heading text-3xl font-bold text-slate-950 md:text-4xl">Tim yang bekerja dalam satu catatan layanan</h2><p class="mt-5 leading-relaxed text-slate-600">Setiap peran melihat informasi yang diperlukan tanpa membuka data klinis di luar kewenangannya.</p></div>
    </section>

    <section id="lokasi" class="mx-auto max-w-7xl px-4 pb-24 lg:px-8">
        <x-ui.panel class="grid gap-6 bg-clinic-900 text-white md:grid-cols-2">
            <div><h2 class="font-heading text-2xl font-bold">Jam operasional</h2><p class="mt-2 text-clinic-100">Senin-Sabtu, 08:00-21:00 WIB.</p></div>
            <div><h2 class="font-heading text-2xl font-bold">Pendaftaran pasien</h2><p class="mt-2 text-clinic-100">Akun pasien perlu verifikasi email dan persetujuan staf sebelum mengakses portal.</p></div>
        </x-ui.panel>
    </section>
</x-layouts.public>
```

Keep hero subtext under 20 words if copy changes. Use at most two eyebrows across the page, no decorative scroll cues, no fake metrics, no placeholder company names, and no chatbot CTA.

- [ ] **Step 7: Simplify CSP-safe landing JS**

```js
Alpine.data('clinicLanding', () => ({
    menuOpen: false,
    toggleMenu() {
        this.menuOpen = !this.menuOpen;
    },
    closeMenu() {
        this.menuOpen = false;
    },
}));
```

Remove obsolete public appointment modal methods from `clinicLanding`.

- [ ] **Step 8: Run tests and build**

```powershell
php artisan test --compact tests/Feature/PublicLandingPageTest.php
npm run build
```

Expected: PASS and build succeeds.

- [ ] **Step 9: Commit and push**

```powershell
git add app/Http/Controllers/PublicHomeController.php resources/views/components/layouts/public.blade.php resources/views/welcome.blade.php resources/js/app.js routes/web.php tests/Feature/PublicLandingPageTest.php
git commit -m "feat redesign public clinic landing"
git push origin main
```

### Task 4: Unify Fortify Authentication Views

**Files:**
- Modify: `resources/views/auth/login.blade.php`
- Modify: `resources/views/auth/register.blade.php`
- Modify: `resources/views/auth/verify-email.blade.php`
- Modify: `resources/views/auth/forgot-password.blade.php`
- Modify: `resources/views/auth/reset-password.blade.php`
- Modify: `resources/views/components/layouts/guest.blade.php`
- Test: `tests/Feature/Frontend/AuthPageDesignTest.php`

- [ ] **Step 1: Generate and write the auth design test**

```powershell
php artisan make:test --phpunit Frontend/AuthPageDesignTest --no-interaction
```

The test must assert each Fortify page uses the public/guest layout, visible labels, one primary action, a clinic brand link, and no inline script or remote font URL.

- [ ] **Step 2: Run the test to verify it fails**

```powershell
php artisan test --compact tests/Feature/Frontend/AuthPageDesignTest.php
```

- [ ] **Step 3: Implement the guest layout**

Use an asymmetric desktop layout with the clinic image on one side and a single focused form panel on the other. Mobile collapses to one column. Keep the page light-first and use the same focus, button, and input components.

- [ ] **Step 4: Refactor every auth view**

Each view contains only its title, short explanation, form fields, status/error feedback, and one contextual link. Preserve Fortify route names and field names exactly.

- [ ] **Step 5: Run auth and frontend tests**

```powershell
php artisan test --compact tests/Feature/Auth tests/Feature/Frontend/AuthPageDesignTest.php
npm run build
```

Expected: PASS.

- [ ] **Step 6: Commit and push**

```powershell
git add resources/views/auth resources/views/components/layouts/guest.blade.php tests/Feature/Frontend/AuthPageDesignTest.php
git commit -m "feat unify fortify authentication design"
git push origin main
```

### Task 5: Build the Patient Portal Layout and States

**Files:**
- Create: `resources/views/components/layouts/patient.blade.php`
- Modify: `resources/views/patient-portal/account-status.blade.php`
- Modify: `resources/views/patient-portal/index.blade.php`
- Create: `resources/views/patient-portal/appointments/index.blade.php`
- Modify: `resources/js/app.js`
- Test: `tests/Feature/Frontend/PatientPortalDesignTest.php`

- [ ] **Step 1: Write the patient portal design test**

Assert approved patients see next appointment, current queue, recent visits, appointment actions, and no staff sidebar. Assert pending and rejected patients see explicit status feedback and no portal data.

- [ ] **Step 2: Run the test to verify it fails**

```powershell
php artisan test --compact tests/Feature/Frontend/PatientPortalDesignTest.php
```

- [ ] **Step 3: Implement patient layout**

The layout uses a compact top navigation with clinic brand, Dashboard, Janji Temu, Riwayat, account menu, and logout form. Desktop content is constrained to `max-w-7xl`; mobile navigation uses a CSP-safe `patientNavigation` Alpine component.

- [ ] **Step 4: Implement dashboard composition**

Use one dark teal welcome panel, an asymmetric appointment and queue grid, a sparse visit-history list, and a single primary appointment CTA. Render loading only for future asynchronous chat content; server-rendered data must not flash a fake loading state.

- [ ] **Step 5: Implement appointment forms**

All fields use visible labels. Available dates and slots come from controller data. Cancellation uses an inline reason field and danger button. Reschedule uses the same form request names as the backend plan.

- [ ] **Step 6: Run tests and build**

```powershell
php artisan test --compact tests/Feature/Frontend/PatientPortalDesignTest.php tests/Feature/PatientPortal
npm run build
```

- [ ] **Step 7: Commit and push**

```powershell
git add resources/views/components/layouts/patient.blade.php resources/views/patient-portal resources/js/app.js tests/Feature/Frontend/PatientPortalDesignTest.php
git commit -m "feat add responsive patient portal interface"
git push origin main
```

### Task 6: Apply the Unified Skin to the Staff Shell

**Files:**
- Modify: `resources/views/components/layouts/app.blade.php`
- Modify: `resources/views/dashboard.blade.php`
- Modify: current feature views under `resources/views/patients`, `queue`, `clinical`, `pharmacy`, `record-copies`, and `reports`
- Modify: `resources/js/app.js`
- Test: `tests/Feature/Frontend/StaffShellDesignTest.php`

- [ ] **Step 1: Write the staff shell test**

Test role-aware navigation for registration, nurse, doctor, pharmacist, owner, and system administrator users. Assert unauthorized links remain absent and the patient portal review link appears only for `patients.manage` users.

- [ ] **Step 2: Run the test to verify it fails**

```powershell
php artisan test --compact tests/Feature/Frontend/StaffShellDesignTest.php
```

- [ ] **Step 3: Refactor the layout without changing route labels or permissions**

Keep the responsive sidebar structure, update visual tokens, add a patient-review item under the existing permission check, and keep user identity plus logout at the bottom. Desktop sidebar width remains 17rem. Mobile overlay closes through the existing `navigation` Alpine component.

- [ ] **Step 4: Normalize feature pages**

Replace repeated page headings, alerts, panel wrappers, inputs, and buttons with the new Blade components. Do not change field names, route names, authorization checks, or workflow order.

- [ ] **Step 5: Run focused feature and layout tests**

```powershell
php artisan test --compact tests/Feature/Frontend/StaffShellDesignTest.php tests/Feature/Authorization tests/Feature/Queue tests/Feature/Clinical tests/Feature/Pharmacy
npm run build
```

Expected: PASS.

- [ ] **Step 6: Commit and push**

```powershell
git add resources/views/components/layouts/app.blade.php resources/views/dashboard.blade.php resources/views/patients resources/views/queue resources/views/clinical resources/views/pharmacy resources/views/record-copies resources/views/reports resources/js/app.js tests/Feature/Frontend/StaffShellDesignTest.php
git commit -m "feat unify staff clinic interface"
git push origin main
```

### Task 7: Accessibility, Responsive, and Browser Verification

**Files:**
- Verify: frontend files changed in Tasks 1-6.

- [ ] **Step 1: Run automated frontend contracts**

```powershell
php artisan test --compact tests/Feature/Frontend tests/Feature/PublicLandingPageTest.php
npm run build
```

- [ ] **Step 2: Resolve the application URL**

Use Laravel Boost `get-absolute-url` before opening the application. Do not start a development server.

- [ ] **Step 3: Verify desktop and mobile public pages**

Use the browser at 1440x900 and 390x844. Check navigation, no horizontal scroll, hero CTA visibility, image aspect ratio, schedule rendering, focus order, and console logs.

- [ ] **Step 4: Verify patient and staff pages**

Check pending and approved patient states plus registration, doctor, pharmacist, and owner roles. Validate responsive navigation, forms, error states, empty states, success feedback, keyboard use, and reduced motion.

- [ ] **Step 5: Run the design pre-flight**

Confirm one accent color, one radius system, button/form contrast, no remote fonts, no em-dash in visible copy, no public chatbot, no fake screenshots, no decorative scroll cues, no duplicate CTA intent, real images, and no broken mobile layout.

- [ ] **Step 6: Resolve browser failures in their owning task**

If browser verification finds a defect, return to the task that owns the affected file, add or update its focused automated test, apply the minimal fix, commit that task-specific change, and repeat this verification task. This task creates no standalone commit.

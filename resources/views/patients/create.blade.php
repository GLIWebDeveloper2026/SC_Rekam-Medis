<x-layouts.app title="Daftarkan Pasien">
    <div class="max-w-3xl">
        <p class="text-sm font-bold uppercase tracking-[0.2em] text-clinic-600">Pendaftaran identitas</p>
        <h1 class="mt-2 text-3xl font-bold text-slate-900">Daftarkan pasien baru</h1>
        <p class="mt-2 text-slate-500">NIK boleh dikosongkan. Untuk bayi tanpa NIK, data wali wajib diisi.</p>

        <form class="panel mt-8 grid gap-5 p-6 sm:grid-cols-2" method="POST" action="{{ route('patients.store') }}">
            @csrf
            <div class="sm:col-span-2"><label class="mb-2 block text-sm font-bold" for="full_name">Nama lengkap</label><input class="form-input" id="full_name" name="full_name" value="{{ old('full_name') }}" required></div>
            <div><label class="mb-2 block text-sm font-bold" for="birth_date">Tanggal lahir</label><input class="form-input" id="birth_date" name="birth_date" type="date" value="{{ old('birth_date') }}" max="{{ now()->toDateString() }}" required></div>
            <div><label class="mb-2 block text-sm font-bold" for="sex">Jenis kelamin</label><select class="form-input" id="sex" name="sex" required><option value="female">Perempuan</option><option value="male">Laki-laki</option><option value="unknown">Belum diketahui</option></select></div>
            <div><label class="mb-2 block text-sm font-bold" for="nik">NIK (opsional)</label><input class="form-input" id="nik" name="nik" inputmode="numeric" maxlength="16" value="{{ old('nik') }}"></div>
            <div><label class="mb-2 block text-sm font-bold" for="phone">Telepon</label><input class="form-input" id="phone" name="phone" value="{{ old('phone') }}"></div>
            <div class="sm:col-span-2"><label class="mb-2 block text-sm font-bold" for="address">Alamat</label><textarea class="form-input min-h-24" id="address" name="address">{{ old('address') }}</textarea></div>
            <div class="sm:col-span-2 border-t border-slate-100 pt-5"><h2 class="text-lg font-bold">Data wali (wajib untuk bayi tanpa NIK)</h2></div>
            <div><label class="mb-2 block text-sm font-bold" for="guardian_name">Nama wali</label><input class="form-input" id="guardian_name" name="guardian_name" value="{{ old('guardian_name') }}"></div>
            <div><label class="mb-2 block text-sm font-bold" for="guardian_relationship">Hubungan</label><input class="form-input" id="guardian_relationship" name="guardian_relationship" value="{{ old('guardian_relationship') }}"></div>
            <div><label class="mb-2 block text-sm font-bold" for="guardian_phone">Telepon wali</label><input class="form-input" id="guardian_phone" name="guardian_phone" value="{{ old('guardian_phone') }}"></div>
            @if ($errors->any())<div class="sm:col-span-2 rounded-xl bg-danger/10 p-4 text-sm font-semibold text-danger" role="alert">{{ $errors->first() }}</div>@endif
            <div class="sm:col-span-2 flex justify-end"><button class="btn-primary" type="submit">Simpan pasien</button></div>
        </form>
    </div>
</x-layouts.app>

@props(['mode' => 'staff'])

<div x-data="clinicChat" @keydown.escape.window="closePanel">
    <button class="fixed bottom-5 right-5 z-40 inline-flex min-h-12 items-center gap-2 rounded-full bg-clinic-700 px-5 font-bold text-white shadow-xl shadow-clinic-900/20 transition active:scale-[0.98]" type="button" @click="openPanel" aria-label="Buka asisten klinik">
        <i data-lucide="message-circle" class="size-5"></i>
        <span class="hidden sm:inline">Asisten Klinik</span>
    </button>

    <div class="fixed inset-0 z-50 items-end justify-end bg-slate-900/20 p-0 sm:p-4" :class="open ? 'flex' : 'hidden'">
        <button class="absolute inset-0" type="button" @click="closePanel" aria-label="Tutup asisten klinik"></button>
        <section class="relative flex h-[min(44rem,92dvh)] w-full flex-col rounded-t-2xl bg-white shadow-2xl sm:max-w-md sm:rounded-2xl" role="dialog" aria-modal="true" aria-labelledby="clinic-chat-title">
            <header class="flex items-start justify-between gap-4 border-b border-slate-200 p-5">
                <div><p class="text-sm font-bold text-clinic-700">Jadwal dan kunjungan</p><h2 class="mt-1 font-heading text-xl font-bold text-slate-900" id="clinic-chat-title">Asisten Klinik</h2></div>
                <button class="grid size-10 place-items-center rounded-xl border border-slate-200" type="button" @click="closePanel"><i data-lucide="x" class="size-5"></i><span class="sr-only">Tutup</span></button>
            </header>

            <div class="flex-1 space-y-4 overflow-y-auto p-5" aria-live="polite">
                <div class="rounded-xl bg-clinic-50 p-4 text-sm leading-6 text-clinic-900" :class="messages.length === 0 ? 'block' : 'hidden'">
                    @if ($mode === 'patient')
                        Tanyakan jadwal, buat janji temu, check-in, status antrean, atau ringkasan riwayat kunjungan Anda.
                    @else
                        Tanyakan jadwal, antrean, pencarian pasien, pendaftaran pasien, atau janji temu sesuai izin Anda.
                    @endif
                </div>

                <template x-for="(message, index) in messages" :key="index">
                    <div class="flex" :class="message.role === 'user' ? 'justify-end' : 'justify-start'">
                        <div class="max-w-[86%] rounded-2xl px-4 py-3 text-sm leading-6" :class="message.role === 'user' ? 'bg-clinic-700 text-white' : 'bg-slate-100 text-slate-800'" x-text="message.content"></div>
                    </div>
                </template>

                <div class="space-y-2" :class="toolResults.length > 0 ? 'block' : 'hidden'">
                    <template x-for="(result, index) in toolResults" :key="index">
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 text-xs"><strong class="block text-slate-900" x-text="result.message"></strong><span class="mt-1 block text-slate-500" x-text="result.code"></span></div>
                    </template>
                </div>

                <div class="grid gap-2" :class="busy ? 'block' : 'hidden'" aria-label="Asisten sedang memproses"><div class="h-3 w-3/4 animate-pulse rounded bg-slate-200"></div><div class="h-3 w-1/2 animate-pulse rounded bg-slate-200"></div></div>
                <div class="rounded-xl border border-red-200 bg-red-50 p-3 text-sm font-semibold text-red-800" :class="error ? 'block' : 'hidden'" x-text="error"></div>
            </div>

            <form class="border-t border-slate-200 p-4" @submit.prevent="send">
                <label class="sr-only" for="clinic-chat-input">Pesan untuk asisten klinik</label>
                <textarea class="form-input min-h-20 resize-none" id="clinic-chat-input" x-ref="chatInput" x-model="input" maxlength="2000" placeholder="Contoh: lihat jadwal dokter umum besok" required></textarea>
                <div class="mt-3 flex items-center justify-between gap-3"><p class="text-xs text-slate-500">Tidak untuk diagnosis atau resep.</p><x-ui.button type="submit" ::disabled="busy">Kirim</x-ui.button></div>
            </form>
        </section>
    </div>
</div>

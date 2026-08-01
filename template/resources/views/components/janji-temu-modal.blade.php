<!-- Modal Form Janji Temu Manual -->
<div x-show="janjiModalOpen" 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50 overflow-y-auto" 
     style="display: none;">
    
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs" @click="janjiModalOpen = false"></div>

    <!-- Modal Dialog -->
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl transition-all border border-slate-100">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-primary-light flex items-center justify-center text-primary">
                        <i class="fa-solid fa-calendar-plus text-lg"></i>
                    </div>
                    <div>
                        <h3 class="font-heading font-bold text-slate-900 text-lg">Buat Janji Temu Pasien</h3>
                        <span class="text-xs text-slate-500">Klinik Pratama Sehat Bersama</span>
                    </div>
                </div>
                <button @click="janjiModalOpen = false" class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form action="{{ url('/pendaftaran/simpan-janji') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Pasien</label>
                    <input type="text" required placeholder="Nama Lengkap Pasien" class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary-light">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor HP / WhatsApp</label>
                    <input type="tel" required placeholder="08123456789" class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary-light">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Layanan / Dokter</label>
                    <select required class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary-light">
                        <option value="">-- Pilih Dokter / Poli --</option>
                        <option value="umum">Dokter Umum (dr. Rina Astuti)</option>
                        <option value="gigi">Dokter Gigi (drg. Budi Santoso - Sen/Rab/Jum)</option>
                        <option value="kia">Kesehatan Ibu & Anak (KIA)</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Kunjungan</label>
                        <input type="date" required class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary-light">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Waktu / Jam (WIB)</label>
                        <select required class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary-light">
                            <option value="09:00">09:00 WIB</option>
                            <option value="11:00">11:00 WIB</option>
                            <option value="15:00">15:00 WIB</option>
                            <option value="18:30">18:30 WIB</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Keluhan Singkat (Opsional)</label>
                    <textarea rows="2" placeholder="Keluhan yang dirasakan..." class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary-light"></textarea>
                </div>

                <div class="pt-2 flex gap-2">
                    <button type="button" @click="janjiModalOpen = false" class="w-1/2 py-2.5 rounded-xl border border-slate-300 text-slate-700 font-semibold text-sm hover:bg-slate-50">Batal</button>
                    <button type="submit" class="w-1/2 py-2.5 rounded-xl bg-primary text-white font-semibold text-sm shadow-xs hover:bg-primary-dark">Simpan Janji</button>
                </div>
            </form>
        </div>
    </div>
</div>

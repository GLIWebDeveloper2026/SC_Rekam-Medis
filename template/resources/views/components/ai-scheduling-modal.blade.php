<!-- AI Auto-Scheduling Modal (Pure Solid Single Colors - No Gradients) -->
<div x-show="aiModalOpen" 
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0 scale-95"
     x-transition:enter-end="opacity-100 scale-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100 scale-100"
     x-transition:leave-end="opacity-0 scale-95"
     class="fixed inset-0 z-50 overflow-y-auto" 
     style="display: none;">
    
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-slate-900/70" @click="aiModalOpen = false"></div>

    <!-- Modal Dialog Container -->
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative w-full max-w-xl rounded-2xl bg-white p-6 sm:p-8 shadow-xl border border-slate-200"
             x-data="{ 
                 userPrompt: '', 
                 isProcessing: false, 
                 messages: [
                     { role: 'assistant', text: 'Halo! Saya Asisten AI Klinik Sehat Bersama (Model: cx/gpt-5.6-tera).\nKetik jadwal periksa yang Anda inginkan!' }
                 ],
                 parsedSchedule: null,
                 quickSuggest(promptText) {
                     this.userPrompt = promptText;
                     this.sendPrompt();
                 },
                 sendPrompt() {
                     if (!this.userPrompt.trim()) return;
                     const text = this.userPrompt;
                     this.messages.push({ role: 'user', text: text });
                     this.userPrompt = '';
                     this.isProcessing = true;
                     
                     // Simulated OpenRouter AI Scheduling Engine Response (cx/gpt-5.6-tera)
                     setTimeout(() => {
                         this.isProcessing = false;
                         this.parsedSchedule = {
                             dokter: 'Dokter Umum (dr. Rina Astuti)',
                             tanggal: 'Sabtu, 1 Agustus 2026',
                             jam: '09:00 WIB',
                             status: 'Slot Jam 09:00 WIB Tersedia'
                         };
                         this.messages.push({ 
                             role: 'assistant', 
                             text: 'AI berhasil memproses permintaan Anda! Rincian reservasi otomatis:' 
                         });
                     }, 1100);
                 },
                 confirmSchedule() {
                     alert('Reservasi Jadwal Berhasil Dikonfirmasi & Disimpan ke Sistem Klinik!');
                     this.aiModalOpen = false;
                     this.parsedSchedule = null;
                 }
             }">

            <!-- Modal Header -->
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-xl bg-teal-700 text-white flex items-center justify-center font-bold">
                        <i class="fa-solid fa-robot text-xl"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="font-heading font-bold text-slate-900 text-lg">AI Auto-Scheduling Assistant</h3>
                            <span class="px-2 py-0.5 rounded bg-teal-50 text-teal-800 font-bold text-[10px] uppercase border border-teal-200">cx/gpt-5.6-tera</span>
                        </div>
                        <span class="text-xs text-slate-500 font-medium">Asisten Cerdas Penjadwalan Pasien Klinik WIB</span>
                    </div>
                </div>
                <button @click="aiModalOpen = false" class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-700">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Quick Suggestion Pills -->
            <div class="pt-3 pb-1 flex flex-wrap gap-2">
                <span class="text-[11px] font-bold text-slate-400 self-center mr-1">Contoh Quick Prompt:</span>
                <button @click="quickSuggest('Periksa dokter umum besok jam 09:00 WIB')" class="px-3 py-1 rounded-lg bg-slate-100 hover:bg-teal-50 hover:text-teal-800 text-slate-700 text-xs font-medium border border-slate-200">
                    👨‍⚕️ Dokter Umum Jam 09:00
                </button>
                <button @click="quickSuggest('Jadwal periksa dokter gigi hari Jumat jam 15:00 WIB')" class="px-3 py-1 rounded-lg bg-slate-100 hover:bg-teal-50 hover:text-teal-800 text-slate-700 text-xs font-medium border border-slate-200">
                    🦷 Dokter Gigi Jam 15:00
                </button>
            </div>

            <!-- Chat Window Stream -->
            <div class="my-4 h-72 overflow-y-auto space-y-3.5 pr-2 scrollbar-thin scrollbar-thumb-slate-200">
                <template x-for="(msg, index) in messages" :key="index">
                    <div class="flex" :class="msg.role === 'user' ? 'justify-end' : 'justify-start'">
                        <div class="max-w-[85%] rounded-xl px-4 py-2.5 text-xs leading-relaxed"
                             :class="msg.role === 'user' 
                                 ? 'bg-teal-700 text-white rounded-br-none font-medium' 
                                 : 'bg-slate-100 text-slate-800 rounded-bl-none border border-slate-200'">
                            <p x-text="msg.text" class="whitespace-pre-line"></p>
                        </div>
                    </div>
                </template>

                <!-- Processing Indicator -->
                <div x-show="isProcessing" class="flex justify-start">
                    <div class="bg-slate-100 rounded-xl rounded-bl-none px-4 py-2.5 text-xs text-slate-600 flex items-center gap-2 border border-slate-200">
                        <i class="fa-solid fa-circle-notch fa-spin text-teal-700 text-sm"></i>
                        <span class="font-medium">OpenRouter AI sedang memparsing maksud jadwal...</span>
                    </div>
                </div>

                <!-- Parsed Schedule Card Result (Solid Teal Light) -->
                <div x-show="parsedSchedule" x-transition class="mt-4 p-4 rounded-xl bg-teal-50 border border-teal-200 text-slate-800 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-teal-900 flex items-center gap-1.5">
                            <i class="fa-solid fa-circle-check text-teal-700 text-sm"></i> Hasil Parsing AI (OpenRouter)
                        </span>
                        <span class="px-2.5 py-0.5 rounded bg-teal-200 text-teal-900 font-bold text-[11px]" x-text="parsedSchedule.status"></span>
                    </div>

                    <div class="grid grid-cols-2 gap-3 text-xs bg-white p-3 rounded-lg border border-teal-100">
                        <div>
                            <span class="text-slate-400 block text-[10px] font-bold uppercase">Poli / Dokter</span>
                            <span class="font-bold text-slate-900" x-text="parsedSchedule.dokter"></span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px] font-bold uppercase">Waktu Reservasi</span>
                            <span class="font-bold text-slate-900" x-text="parsedSchedule.jam"></span>
                        </div>
                        <div class="col-span-2 pt-1 border-t border-slate-100">
                            <span class="text-slate-400 block text-[10px] font-bold uppercase">Tanggal Kunjungan</span>
                            <span class="font-bold text-slate-900" x-text="parsedSchedule.tanggal"></span>
                        </div>
                    </div>

                    <button @click="confirmSchedule()" class="w-full py-2.5 rounded-xl bg-teal-700 hover:bg-teal-800 text-white font-bold text-xs">
                        <i class="fa-solid fa-check-double mr-1.5"></i> Konfirmasi & Simpan Reservasi
                    </button>
                </div>
            </div>

            <!-- Input Form Footer -->
            <div class="pt-3 border-t border-slate-100 flex gap-2">
                <input type="text" 
                       x-model="userPrompt" 
                       @keydown.enter="sendPrompt()"
                       placeholder="Contoh: Periksa dokter umum besok jam 09:00 WIB..."
                       class="flex-1 rounded-xl border border-slate-300 px-4 py-2.5 text-xs focus:border-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-100 font-medium">
                
                <button @click="sendPrompt()" 
                        :disabled="isProcessing"
                        class="px-4 py-2.5 rounded-xl bg-teal-700 hover:bg-teal-800 text-white text-xs font-bold disabled:opacity-50 transition-colors">
                    <i class="fa-solid fa-paper-plane text-sm"></i>
                </button>
            </div>
        </div>
    </div>
</div>

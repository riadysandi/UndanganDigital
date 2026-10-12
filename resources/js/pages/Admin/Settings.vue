<script setup lang="ts">
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps<{
    app_name: string;
    app_url: string;
    whatsapp_cs: string;
    system_info: {
        php_version: string;
        laravel_version: string;
        server_time: string;
        db_connection: string;
    };
}>();

const savedNotification = ref(false);
const whatsappNumber = ref(props.whatsapp_cs);
const maintenanceMode = ref(false);

const handleSave = () => {
    savedNotification.value = true;
    setTimeout(() => {
        savedNotification.value = false;
    }, 3000);
};
</script>

<template>
    <AppLayout>
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">
            <!-- Header Section -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 border-b border-stone-200 gap-4">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="px-2 py-0.5 rounded text-[11px] font-bold uppercase tracking-wider bg-stone-100 text-stone-800">
                            Konfigurasi & Operasional
                        </span>
                        <span class="text-xs text-stone-400">• Sistem</span>
                    </div>
                    <h1 class="text-2xl font-serif font-bold text-stone-900">Pengaturan Platform</h1>
                    <p class="text-xs text-stone-500 mt-1">Konfigurasi operasional platform, kontak CS WhatsApp, dan informasi server.</p>
                </div>

                <div v-if="savedNotification" class="px-3 py-1.5 bg-emerald-100 text-emerald-800 text-xs font-semibold rounded-lg shadow-sm flex items-center gap-1.5 animate-bounce">
                    <span>✓</span> Konfigurasi berhasil disimpan!
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-8">
                <!-- Left 2 Cols: Settings Forms -->
                <div class="md:col-span-2 space-y-6">
                    <!-- General Settings Card -->
                    <div class="bg-white rounded-2xl border border-stone-200 p-6 shadow-sm">
                        <h2 class="text-base font-bold text-stone-900 mb-1 flex items-center gap-2">
                            <span>⚙️</span> Pengaturan Umum Platform
                        </h2>
                        <p class="text-xs text-stone-500 mb-6">Identitas dan link utama platform undangan digital.</p>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-stone-700 mb-1.5">Nama Platform / Brand</label>
                                <input
                                    type="text"
                                    :value="app_name"
                                    disabled
                                    class="w-full text-xs rounded-xl border-stone-200 bg-stone-50 text-stone-500 py-2.5 px-3 cursor-not-allowed"
                                />
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-stone-700 mb-1.5">Domain / URL Utama</label>
                                <input
                                    type="text"
                                    :value="app_url"
                                    disabled
                                    class="w-full text-xs rounded-xl border-stone-200 bg-stone-50 text-stone-500 py-2.5 px-3 cursor-not-allowed font-mono"
                                />
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-stone-700 mb-1.5">Nomor WhatsApp CS & Konsultasi Jasa</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-stone-400 text-xs">
                                        +
                                    </span>
                                    <input
                                        v-model="whatsappNumber"
                                        type="text"
                                        class="w-full text-xs rounded-xl border-stone-200 focus:border-rose-500 focus:ring-rose-500 py-2.5 pl-6 pr-3 font-mono"
                                        placeholder="6281234567890"
                                    />
                                </div>
                                <p class="text-[11px] text-stone-400 mt-1">Nomor ini digunakan saat calon klien klik tombol WhatsApp di formulir bantuan atau konsultasi tim.</p>
                            </div>
                        </div>

                        <div class="mt-6 pt-4 border-t border-stone-100 flex justify-end">
                            <button
                                @click="handleSave"
                                class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-xl shadow-sm shadow-rose-200 transition-colors"
                            >
                                Simpan Perubahan
                            </button>
                        </div>
                    </div>

                    <!-- Maintenance & Feature Flags Card -->
                    <div class="bg-white rounded-2xl border border-stone-200 p-6 shadow-sm">
                        <h2 class="text-base font-bold text-stone-900 mb-1 flex items-center gap-2">
                            <span>🛠️</span> Mode Pemeliharaan & Fitur
                        </h2>
                        <p class="text-xs text-stone-500 mb-6">Kontrol aksesibilitas dan fitur platform.</p>

                        <div class="space-y-4">
                            <div class="flex items-center justify-between p-3.5 rounded-xl border border-stone-100 bg-stone-50/50">
                                <div>
                                    <div class="text-xs font-semibold text-stone-800">Mode Sandbox Pembayaran</div>
                                    <div class="text-[11px] text-stone-500">Gunakan simulator pembayaran untuk pengujian transaksi instan tanpa biaya.</div>
                                </div>
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                    Aktif
                                </span>
                            </div>

                            <div class="flex items-center justify-between p-3.5 rounded-xl border border-stone-100 bg-stone-50/50">
                                <div>
                                    <div class="text-xs font-semibold text-stone-800">Fitur 3D Canvas Interaktif</div>
                                    <div class="text-[11px] text-stone-500">Mendukung Three.js dan render model amplop interaktif 3D.</div>
                                </div>
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                    Aktif
                                </span>
                            </div>

                            <div class="flex items-center justify-between p-3.5 rounded-xl border border-stone-100 bg-stone-50/50">
                                <div>
                                    <div class="text-xs font-semibold text-stone-800">Layanan Terima Beres (Dibantu Tim)</div>
                                    <div class="text-[11px] text-stone-500">Formulir penerimaan pesanan jasa pembuatan oleh staf admin.</div>
                                </div>
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                    Aktif
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Col: Server & Environment Diagnostics -->
                <div class="space-y-6">
                    <div class="bg-white rounded-2xl border border-stone-200 p-6 shadow-sm">
                        <h2 class="text-base font-bold text-stone-900 mb-1 flex items-center gap-2">
                            <span>🖥️</span> Info Server & Sistem
                        </h2>
                        <p class="text-xs text-stone-500 mb-4">Spesifikasi runtime & infrastruktur.</p>

                        <div class="space-y-3 text-xs">
                            <div class="flex justify-between py-2 border-b border-stone-100">
                                <span class="text-stone-500">PHP Version</span>
                                <span class="font-mono font-bold text-stone-800">{{ system_info.php_version }}</span>
                            </div>
                            <div class="flex justify-between py-2 border-b border-stone-100">
                                <span class="text-stone-500">Laravel Framework</span>
                                <span class="font-mono font-bold text-stone-800">{{ system_info.laravel_version }}</span>
                            </div>
                            <div class="flex justify-between py-2 border-b border-stone-100">
                                <span class="text-stone-500">Database Driver</span>
                                <span class="font-mono font-bold text-stone-800 uppercase">{{ system_info.db_connection }}</span>
                            </div>
                            <div class="flex justify-between py-2 border-b border-stone-100">
                                <span class="text-stone-500">Inertia.js + Vue</span>
                                <span class="font-mono font-bold text-stone-800">Vue 3 (Vite)</span>
                            </div>
                            <div class="flex justify-between py-2">
                                <span class="text-stone-500">Waktu Server</span>
                                <span class="font-mono text-stone-600 text-[11px]">{{ system_info.server_time }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-stone-900 to-stone-800 rounded-2xl p-6 text-white shadow-sm">
                        <div class="flex items-center gap-2 mb-2">
                            <div class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-ping"></div>
                            <span class="text-xs font-semibold text-emerald-400">Server Status: Sehat</span>
                        </div>
                        <p class="text-xs text-stone-300 mb-4">
                            Platform terhubung ke cloud production Railway & GitHub repository dengan webhook otomatis.
                        </p>
                        <a
                            href="/"
                            target="_blank"
                            class="inline-block w-full py-2 bg-stone-700/60 hover:bg-stone-700 text-stone-200 text-xs font-semibold text-center rounded-xl transition-colors"
                        >
                            Buka Tampilan Publik ↗
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

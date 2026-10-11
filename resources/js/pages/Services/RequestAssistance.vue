<script setup lang="ts">
import { ref } from 'vue';
import { useForm, usePage, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps<{
    templates: Array<{
        id: number;
        name: string;
        slug: string;
        category: string;
        thumbnail_path: string;
        is_premium: boolean;
    }>;
    defaultName: string;
    defaultPhone: string;
    defaultEmail: string;
}>();

const page = usePage();
const flash = ref((page.props as any).flash || {});

const form = useForm({
    customer_name: props.defaultName || '',
    whatsapp: props.defaultPhone || '',
    email: props.defaultEmail || '',
    package_selected: 'Terima Beres VIP 3D',
    template_preference: 'Royal 3D Envelope & Wax Seal',
    groom_name: '',
    groom_parents: '',
    bride_name: '',
    bride_parents: '',
    akad_date: '',
    akad_venue: '',
    resepsi_date: '',
    resepsi_venue: '',
    photo_drive_link: '',
    notes: '',
});

const isSubmitted = ref(false);
const whatsappUrl = ref('');

const submit = () => {
    form.post('/bantu-buat', {
        onSuccess: (pageRes: any) => {
            isSubmitted.value = true;
            if (pageRes.props?.flash?.whatsapp_url) {
                whatsappUrl.value = pageRes.props.flash.whatsapp_url;
            }
        },
    });
};
</script>

<template>
    <AppLayout>
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-16">
            <!-- Hero Header -->
            <div class="text-center max-w-2xl mx-auto mb-12">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-700 mb-4">
                    ✨ Layanan Terima Beres (Done-For-You)
                </div>
                <h1 class="text-3xl sm:text-4xl font-serif font-bold text-stone-900 leading-tight">
                    Ga Mau Ribet? Biarkan <span class="text-rose-600">Tim Desainer Kami</span> yang Membuatkan!
                </h1>
                <p class="mt-3 text-sm text-stone-600 leading-relaxed">
                    Cukup kirimkan data acara dan foto Anda. Tim ahli kami akan merancang, menginput jadwal, memilihkan musik, hingga undangan 3D siap dibagikan ke para tamu.
                </p>
            </div>

            <!-- Success State with Direct WhatsApp Confirmation -->
            <div v-if="isSubmitted" class="bg-white rounded-3xl border border-stone-200 p-8 shadow-sm text-center space-y-6">
                <div class="w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-3xl mx-auto font-bold">
                    ✓
                </div>
                <h2 class="text-2xl font-serif font-bold text-stone-900">Data Anda Telah Diterima!</h2>
                <p class="text-sm text-stone-600 max-w-md mx-auto">
                    Terima kasih telah mempercayakan pembuatan undangan pernikahan Anda kepada tim kami. Untuk mempercepat proses pengerjaan, silakan klik tombol di bawah untuk langsung terhubung dengan Desainer kami via WhatsApp.
                </p>

                <div class="pt-4 flex flex-col sm:flex-row gap-3 justify-center">
                    <a
                        v-if="whatsappUrl"
                        :href="whatsappUrl"
                        target="_blank"
                        class="inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold shadow-md shadow-emerald-200 transition-all hover:scale-105"
                    >
                        <span>💬</span> Lanjut Chat & Kirim Data via WhatsApp
                    </a>
                    <Link
                        href="/dashboard"
                        class="inline-flex items-center justify-center px-6 py-3.5 rounded-xl border border-stone-300 text-stone-700 text-sm font-semibold hover:bg-stone-50 transition-colors"
                    >
                        Kembali ke Dashboard
                    </Link>
                </div>
            </div>

            <!-- Submission Form -->
            <form v-else @submit.prevent="submit" class="bg-white rounded-3xl border border-stone-200 p-6 sm:p-10 shadow-sm space-y-8">
                <!-- 1. Pilihan Paket Layanan -->
                <div>
                    <h3 class="text-sm font-bold text-stone-800 uppercase tracking-wider mb-4 pb-2 border-b border-stone-100 flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full bg-rose-100 text-rose-600 text-xs flex items-center justify-center font-bold">1</span>
                        Pilih Paket Pembuatan
                    </h3>

                    <div class="grid sm:grid-cols-2 gap-4">
                        <label
                            :class="[
                                'relative p-4 rounded-2xl border-2 cursor-pointer flex flex-col justify-between transition-all',
                                form.package_selected === 'Terima Beres VIP 3D' ? 'border-rose-500 bg-rose-50/30 ring-2 ring-rose-200' : 'border-stone-200 hover:border-stone-300'
                            ]"
                        >
                            <input type="radio" v-model="form.package_selected" value="Terima Beres VIP 3D" class="sr-only" />
                            <div>
                                <div class="flex items-center justify-between">
                                    <span class="font-serif font-bold text-stone-900 text-base">Paket VIP 3D & Custom</span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500 text-white">TERLARIS</span>
                                </div>
                                <div class="text-rose-600 font-bold text-lg mt-1">Rp 199.000</div>
                                <ul class="text-xs text-stone-600 space-y-1.5 mt-3">
                                    <li>✓ Menggunakan <strong>Model 3D Envelope & Wax Seal</strong></li>
                                    <li>✓ Input seluruh data acara, foto, & musik</li>
                                    <li>✓ Revisi sepuasnya sampai acara selesai</li>
                                    <li>✓ Garansi aktif 12 bulan & bantuan blast WA</li>
                                </ul>
                            </div>
                        </label>

                        <label
                            :class="[
                                'relative p-4 rounded-2xl border-2 cursor-pointer flex flex-col justify-between transition-all',
                                form.package_selected === 'Terima Beres Standar' ? 'border-rose-500 bg-rose-50/30 ring-2 ring-rose-200' : 'border-stone-200 hover:border-stone-300'
                            ]"
                        >
                            <input type="radio" v-model="form.package_selected" value="Terima Beres Standar" class="sr-only" />
                            <div>
                                <div class="flex items-center justify-between">
                                    <span class="font-serif font-bold text-stone-900 text-base">Paket Standar</span>
                                </div>
                                <div class="text-stone-800 font-bold text-lg mt-1">Rp 129.000</div>
                                <ul class="text-xs text-stone-600 space-y-1.5 mt-3">
                                    <li>✓ Pilihan tema katalog elegan</li>
                                    <li>✓ Input teks jadwal acara & galeri foto</li>
                                    <li>✓ Maksimal 2x revisi minor</li>
                                    <li>✓ Aktif 6 bulan</li>
                                </ul>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- 2. Kontak Pemesan -->
                <div>
                    <h3 class="text-sm font-bold text-stone-800 uppercase tracking-wider mb-4 pb-2 border-b border-stone-100 flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full bg-rose-100 text-rose-600 text-xs flex items-center justify-center font-bold">2</span>
                        Informasi Kontak Anda
                    </h3>

                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-stone-700 mb-1">Nama Pemesan / CP *</label>
                            <input
                                v-model="form.customer_name"
                                type="text"
                                required
                                class="w-full text-xs p-2.5 rounded-xl border border-stone-300 bg-stone-50 focus:bg-white"
                                placeholder="Contoh: Dimas Prasetyo"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-stone-700 mb-1">Nomor WhatsApp Aktif *</label>
                            <input
                                v-model="form.whatsapp"
                                type="text"
                                required
                                class="w-full text-xs p-2.5 rounded-xl border border-stone-300 bg-stone-50 focus:bg-white"
                                placeholder="08123456789"
                            />
                        </div>
                    </div>
                </div>

                <!-- 3. Pilihan Tema Template -->
                <div>
                    <h3 class="text-sm font-bold text-stone-800 uppercase tracking-wider mb-4 pb-2 border-b border-stone-100 flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full bg-rose-100 text-rose-600 text-xs flex items-center justify-center font-bold">3</span>
                        Preferensi Desain / Tema
                    </h3>

                    <div class="grid sm:grid-cols-3 gap-3">
                        <div
                            v-for="tpl in templates"
                            :key="tpl.id"
                            @click="form.template_preference = tpl.name"
                            :class="[
                                'p-3 rounded-xl border cursor-pointer transition-all flex items-center gap-3',
                                form.template_preference === tpl.name ? 'border-rose-500 bg-rose-50/40 ring-1 ring-rose-300' : 'border-stone-200 hover:border-stone-300'
                            ]"
                        >
                            <img :src="tpl.thumbnail_path" :alt="tpl.name" class="w-12 h-12 object-cover rounded-lg shrink-0" />
                            <div class="min-w-0">
                                <div class="text-xs font-bold text-stone-900 truncate">{{ tpl.name }}</div>
                                <div class="text-[10px] text-stone-400 capitalize">{{ tpl.category }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 4. Data Mempelai -->
                <div>
                    <h3 class="text-sm font-bold text-stone-800 uppercase tracking-wider mb-4 pb-2 border-b border-stone-100 flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full bg-rose-100 text-rose-600 text-xs flex items-center justify-center font-bold">4</span>
                        Data Pengantin
                    </h3>

                    <div class="grid sm:grid-cols-2 gap-4">
                        <div class="p-4 bg-stone-50 rounded-2xl border border-stone-200 space-y-3">
                            <div class="text-xs font-bold text-stone-800">Mempelai Pria</div>
                            <div>
                                <label class="block text-[11px] text-stone-600 mb-1">Nama Lengkap & Gelar *</label>
                                <input v-model="form.groom_name" type="text" required class="w-full text-xs p-2 border rounded-lg bg-white" placeholder="Contoh: Dimas Prasetyo, S.Kom" />
                            </div>
                            <div>
                                <label class="block text-[11px] text-stone-600 mb-1">Nama Orang Tua</label>
                                <input v-model="form.groom_parents" type="text" class="w-full text-xs p-2 border rounded-lg bg-white" placeholder="Putra dari Bpk. ... & Ibu ..." />
                            </div>
                        </div>

                        <div class="p-4 bg-stone-50 rounded-2xl border border-stone-200 space-y-3">
                            <div class="text-xs font-bold text-stone-800">Mempelai Wanita</div>
                            <div>
                                <label class="block text-[11px] text-stone-600 mb-1">Nama Lengkap & Gelar *</label>
                                <input v-model="form.bride_name" type="text" required class="w-full text-xs p-2 border rounded-lg bg-white" placeholder="Contoh: Anisa Rahmawati, S.E" />
                            </div>
                            <div>
                                <label class="block text-[11px] text-stone-600 mb-1">Nama Orang Tua</label>
                                <input v-model="form.bride_parents" type="text" class="w-full text-xs p-2 border rounded-lg bg-white" placeholder="Putri dari Bpk. ... & Ibu ..." />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 5. Rangkaian Acara -->
                <div>
                    <h3 class="text-sm font-bold text-stone-800 uppercase tracking-wider mb-4 pb-2 border-b border-stone-100 flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full bg-rose-100 text-rose-600 text-xs flex items-center justify-center font-bold">5</span>
                        Jadwal & Lokasi Acara
                    </h3>

                    <div class="grid sm:grid-cols-2 gap-4">
                        <div class="space-y-3">
                            <div>
                                <label class="block text-[11px] font-semibold text-stone-700 mb-1">Waktu Akad Nikah</label>
                                <input v-model="form.akad_date" type="text" class="w-full text-xs p-2.5 border rounded-xl bg-stone-50" placeholder="Minggu, 28 Des 2026, 08:00 WIB" />
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-stone-700 mb-1">Lokasi / Gedung Akad</label>
                                <input v-model="form.akad_venue" type="text" class="w-full text-xs p-2.5 border rounded-xl bg-stone-50" placeholder="Masjid Agung / KUA / Rumah" />
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div>
                                <label class="block text-[11px] font-semibold text-stone-700 mb-1">Waktu Resepsi</label>
                                <input v-model="form.resepsi_date" type="text" class="w-full text-xs p-2.5 border rounded-xl bg-stone-50" placeholder="Minggu, 28 Des 2026, 11:00 - 14:00 WIB" />
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-stone-700 mb-1">Lokasi / Gedung Resepsi</label>
                                <input v-model="form.resepsi_venue" type="text" class="w-full text-xs p-2.5 border rounded-xl bg-stone-50" placeholder="Grand Ballroom Hotel..." />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 6. Berkas Foto & Catatan -->
                <div>
                    <h3 class="text-sm font-bold text-stone-800 uppercase tracking-wider mb-4 pb-2 border-b border-stone-100 flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full bg-rose-100 text-rose-600 text-xs flex items-center justify-center font-bold">6</span>
                        Foto & Catatan Tambahan
                    </h3>

                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-semibold text-stone-700 mb-1">Link Google Drive Foto Prewedding (Opsional)</label>
                            <input
                                v-model="form.photo_drive_link"
                                type="url"
                                class="w-full text-xs p-2.5 rounded-xl border border-stone-300 bg-stone-50 focus:bg-white"
                                placeholder="https://drive.google.com/drive/folders/..."
                            />
                            <p class="text-[10px] text-stone-400 mt-1">Pastikan akses link Google Drive diset ke 'Anyone with the link can view'. Foto juga bisa dikirim via WA.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-stone-700 mb-1">Catatan / Permintaan Khusus</label>
                            <textarea
                                v-model="form.notes"
                                rows="3"
                                class="w-full text-xs p-2.5 rounded-xl border border-stone-300 bg-stone-50 focus:bg-white"
                                placeholder="Contoh: Tolong sertakan lagu 'A Thousand Years' dan rekening amplop digital..."
                            ></textarea>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="pt-4 flex items-center justify-end gap-3">
                    <Link href="/" class="px-5 py-2.5 border rounded-xl text-xs font-semibold text-stone-600">
                        Batal
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-8 py-3.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold shadow-md shadow-rose-200 transition-all hover:scale-[1.02] disabled:opacity-50"
                    >
                        {{ form.processing ? 'Mengirim Data...' : 'Kirim Data & Dibantu Tim Kami →' }}
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

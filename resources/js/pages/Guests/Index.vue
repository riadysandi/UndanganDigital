<script setup lang="ts">
import { ref } from 'vue';
import { useForm, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps<{
    invitation: {
        id: number;
        title: string;
        slug: string;
    };
    guests: Array<{
        id: number;
        name: string;
        token: string;
        phone_number: string | null;
        max_attendees: number;
        checked_in_at?: string | null;
        rsvp: {
            attendance_status: string;
            attendee_count: number;
            message: string | null;
            submitted_at: string;
        } | null;
    }>;
    rsvps: Array<any>;
    guestbooks: Array<{
        id: number;
        guest_name: string;
        message: string;
        moderation_status: string;
        created_at: string;
    }>;
    stats: {
        total_guests: number;
        total_attending: number;
        total_declined: number;
        total_checked_in?: number;
        total_messages: number;
    };
    canAddMore: boolean;
}>();

const activeTab = ref<'guests' | 'guestbook'>('guests');

// Single Guest Form
const form = useForm({
    name: '',
    phone_number: '',
    max_attendees: 2,
});

// Import Guests Modal State & Form
const showImportModal = ref(false);
const importMode = ref<'file' | 'text'>('file');
const importCsvFile = ref<File | null>(null);
const importRawText = ref('');
const importSubmitting = ref(false);
const importError = ref('');

const onFileChange = (e: any) => {
    const files = e.target.files;
    if (files && files.length > 0) {
        importCsvFile.value = files[0];
    }
};

const handleImportSubmit = () => {
    importSubmitting.value = true;
    importError.value = '';

    const formData = new FormData();
    if (importMode.value === 'file' && importCsvFile.value) {
        formData.append('csv_file', importCsvFile.value);
    } else if (importMode.value === 'text' && importRawText.value) {
        formData.append('raw_text', importRawText.value);
    } else {
        importError.value = 'Silakan pilih file CSV atau ketik daftar nama tamu.';
        importSubmitting.value = false;
        return;
    }

    router.post(`/api/invitations/${props.invitation.id}/guests/import`, formData, {
        onSuccess: () => {
            showImportModal.value = false;
            importRawText.value = '';
            importCsvFile.value = null;
        },
        onError: (err) => {
            importError.value = Object.values(err)[0] as string || 'Gagal mengimpor tamu.';
        },
        onFinish: () => {
            importSubmitting.value = false;
        },
    });
};

const submitGuest = () => {
    form.post(`/api/invitations/${props.invitation.id}/guests`, {
        onSuccess: () => form.reset(),
    });
};

const deleteGuest = (id: number, name: string) => {
    if (confirm(`Hapus tamu "${name}" dari daftar?`)) {
        router.delete(`/api/invitations/${props.invitation.id}/guests/${id}`);
    }
};

const copyGuestUrl = (name: string, token: string) => {
    const url = `${window.location.origin}/i/${props.invitation.slug}?to=${encodeURIComponent(name)}&t=${token}`;
    navigator.clipboard.writeText(url);
    alert(`Link khusus untuk "${name}" berhasil disalin!`);
};

// WhatsApp Invitation Generator Modal State
const showWaModal = ref(false);
const selectedGuest = ref<any>(null);
const waTemplateType = ref<'formal' | 'casual'>('formal');
const customWaMessage = ref('');
const copiedWaToast = ref(false);

const generateWaMessage = (guest: any, templateType: 'formal' | 'casual') => {
    const url = `${window.location.origin}/i/${props.invitation.slug}?to=${encodeURIComponent(guest.name)}&t=${guest.token}`;
    if (templateType === 'formal') {
        return `Assalamu'alaikum Wr. Wb.\n\nKepada Yth.\nBpk/Ibu/Saudara/i: *${guest.name}*\n\nTanpa mengurangi rasa hormat, perkenankan kami mengundang Bapak/Ibu/Saudara/i untuk menghadiri acara pernikahan kami:\n\n*${props.invitation.title}*\n\nUntuk detail agenda acara, lokasi Google Maps, dan konfirmasi kehadiran (RSVP), silakan buka link undangan berikut:\n${url}\n\nMerupakan suatu kehormatan dan kebahagiaan bagi kami apabila Bapak/Ibu/Saudara/i berkenan hadir dan memberikan doa restu.\n\nTerima kasih.\nWassalamu'alaikum Wr. Wb.`;
    } else {
        return `Halo *${guest.name}*! ✨\n\nKabar bahagia dari kami! Kami ingin mengundang kamu untuk hadir dan berbagi kebahagiaan di momen pernikahan kami:\n\n*${props.invitation.title}*\n\nBuka undangan dan isi konfirmasi kehadiranmu di link berikut ya:\n${url}\n\nSampai jumpa di hari bahagia kami! 🎉`;
    }
};

const openWaModal = (guest: any) => {
    selectedGuest.value = guest;
    customWaMessage.value = generateWaMessage(guest, waTemplateType.value);
    showWaModal.value = true;
};

const switchTemplate = (type: 'formal' | 'casual') => {
    waTemplateType.value = type;
    if (selectedGuest.value) {
        customWaMessage.value = generateWaMessage(selectedGuest.value, type);
    }
};

const sendViaWhatsApp = () => {
    if (!selectedGuest.value) return;
    const phone = selectedGuest.value.phone_number ? selectedGuest.value.phone_number.replace(/^0/, '62').replace(/[^0-9]/g, '') : '';
    const encoded = encodeURIComponent(customWaMessage.value);
    window.open(`https://api.whatsapp.com/send?phone=${phone}&text=${encoded}`, '_blank');
};

const copyWaMessage = () => {
    navigator.clipboard.writeText(customWaMessage.value);
    copiedWaToast.value = true;
    setTimeout(() => {
        copiedWaToast.value = false;
    }, 2000);
};

const moderateMessage = (id: number, status: 'approved' | 'rejected') => {
    router.patch(`/api/invitations/${props.invitation.id}/guestbook/${id}`, { status });
};
</script>

<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-stone-200">
                <div>
                    <Link :href="`/invitations/${invitation.id}/editor`" class="text-xs font-semibold text-rose-600 hover:text-rose-700 flex items-center gap-1 mb-1">
                        ← Kembali ke Editor
                    </Link>
                    <h1 class="text-2xl font-serif font-bold text-stone-900">Manajemen Tamu & RSVP</h1>
                    <p class="text-xs text-stone-500">{{ invitation.title }}</p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button
                        @click="showImportModal = true"
                        class="px-3.5 py-2 rounded-xl bg-white border border-stone-200 hover:bg-stone-50 text-stone-700 font-semibold text-xs shadow-xs transition-colors flex items-center gap-1.5"
                    >
                        <span>📥</span>
                        <span>Import Tamu (Excel/CSV)</span>
                    </button>
                    <a
                        :href="`/invitations/${invitation.id}/guests/export`"
                        class="px-3.5 py-2 rounded-xl bg-white border border-stone-200 hover:bg-stone-50 text-stone-700 font-semibold text-xs shadow-xs transition-colors flex items-center gap-1.5"
                    >
                        <span>📤</span>
                        <span>Download Rekap (CSV)</span>
                    </a>
                    <Link
                        :href="`/invitations/${invitation.id}/checkin`"
                        class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-md shadow-emerald-500/20 transition-colors flex items-center gap-1.5"
                    >
                        <span>📱</span>
                        <span>Meja Resepsi (Check-in QR)</span>
                    </Link>
                </div>
            </div>

            <!-- Stats Bar -->
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 my-6">
                <div class="p-4 bg-white rounded-2xl border border-stone-200 shadow-xs">
                    <div class="text-[10px] uppercase font-bold text-stone-400">Total Tamu</div>
                    <div class="font-serif text-2xl font-bold text-stone-900 mt-1">{{ stats.total_guests }}</div>
                </div>
                <div class="p-4 bg-white rounded-2xl border border-stone-200 shadow-xs">
                    <div class="text-[10px] uppercase font-bold text-emerald-600">Konfirmasi Hadir</div>
                    <div class="font-serif text-2xl font-bold text-emerald-700 mt-1">{{ stats.total_attending }} org</div>
                </div>
                <div class="p-4 bg-white rounded-2xl border border-stone-200 shadow-xs">
                    <div class="text-[10px] uppercase font-bold text-rose-500">Berhalangan</div>
                    <div class="font-serif text-2xl font-bold text-rose-700 mt-1">{{ stats.total_declined }}</div>
                </div>
                <div class="p-4 bg-white rounded-2xl border border-stone-200 shadow-xs">
                    <div class="text-[10px] uppercase font-bold text-teal-600">Sudah Check-in</div>
                    <div class="font-serif text-2xl font-bold text-teal-700 mt-1">{{ stats.total_checked_in ?? 0 }} org</div>
                </div>
                <div class="p-4 bg-white rounded-2xl border border-stone-200 shadow-xs">
                    <div class="text-[10px] uppercase font-bold text-amber-600">Doa & Ucapan</div>
                    <div class="font-serif text-2xl font-bold text-amber-700 mt-1">{{ stats.total_messages }}</div>
                </div>
            </div>

            <!-- Navigation Tabs -->
            <div class="flex border-b border-stone-200 text-sm font-semibold mb-6">
                <button
                    @click="activeTab = 'guests'"
                    :class="['py-3 px-4 border-b-2', activeTab === 'guests' ? 'border-rose-500 text-rose-600' : 'border-transparent text-stone-500 hover:text-stone-800']"
                >
                    Daftar Tamu Undangan ({{ guests.length }})
                </button>
                <button
                    @click="activeTab = 'guestbook'"
                    :class="['py-3 px-4 border-b-2', activeTab === 'guestbook' ? 'border-rose-500 text-rose-600' : 'border-transparent text-stone-500 hover:text-stone-800']"
                >
                    Moderasi Ucapan Buku Tamu ({{ guestbooks.length }})
                </button>
            </div>

            <!-- TAB 1: GUESTS -->
            <div v-if="activeTab === 'guests'" class="grid lg:grid-cols-12 gap-8 items-start">
                <!-- Add Guest Form -->
                <div class="lg:col-span-4 bg-white p-5 rounded-2xl border border-stone-200 space-y-4">
                    <h3 class="font-serif font-bold text-base text-stone-900">+ Tambah Tamu Baru</h3>

                    <div v-if="!canAddMore" class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-800">
                        Batas kuota 50 tamu untuk paket gratis telah tercapai.
                        <Link href="/pricing" class="font-bold underline ml-1">Upgrade ke Premium</Link> untuk tamu tanpa batas.
                    </div>

                    <form v-else @submit.prevent="submitGuest" class="space-y-3">
                        <div>
                            <label class="block text-xs font-medium text-stone-700 mb-1">Nama Tamu *</label>
                            <input
                                v-model="form.name"
                                type="text"
                                required
                                class="w-full text-xs p-2.5 rounded-xl border border-stone-300 bg-stone-50 focus:bg-white"
                                placeholder="Contoh: Budi Santoso & Keluarga"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-stone-700 mb-1">Nomor WhatsApp (Opsional)</label>
                            <input
                                v-model="form.phone_number"
                                type="text"
                                class="w-full text-xs p-2.5 rounded-xl border border-stone-300 bg-stone-50 focus:bg-white"
                                placeholder="08123456789"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-stone-700 mb-1">Maksimal Pax / Kuota Hadir</label>
                            <select v-model.number="form.max_attendees" class="w-full text-xs p-2.5 rounded-xl border border-stone-300 bg-stone-50">
                                <option :value="1">1 Orang</option>
                                <option :value="2">2 Orang</option>
                                <option :value="3">3 Orang</option>
                                <option :value="4">4 Orang</option>
                                <option :value="5">5 Orang</option>
                            </select>
                        </div>

                        <div v-if="form.errors.name" class="text-xs text-rose-600">
                            {{ form.errors.name }}
                        </div>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-medium text-xs rounded-xl shadow-sm transition-colors disabled:opacity-50"
                        >
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Tamu' }}
                        </button>
                    </form>
                </div>

                <!-- Guests Table -->
                <div class="lg:col-span-8 bg-white rounded-2xl border border-stone-200 overflow-hidden shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs text-stone-600">
                            <thead class="bg-stone-50 border-b border-stone-200 text-stone-500 font-semibold uppercase text-[10px]">
                                <tr>
                                    <th class="p-3.5">Nama Tamu</th>
                                    <th class="p-3.5">Status RSVP</th>
                                    <th class="p-3.5">Link & WhatsApp</th>
                                    <th class="p-3.5 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-100">
                                <tr v-for="g in guests" :key="g.id" class="hover:bg-stone-50/50">
                                    <td class="p-3.5">
                                        <div class="font-bold text-stone-900">{{ g.name }}</div>
                                        <div class="text-[11px] text-stone-400">Kuota: {{ g.max_attendees }} org</div>
                                    </td>
                                    <td class="p-3.5 space-y-1">
                                        <div>
                                            <span
                                                v-if="g.rsvp && g.rsvp.attendance_status === 'attending'"
                                                class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800"
                                            >
                                                Hadir ({{ g.rsvp.attendee_count }} org)
                                            </span>
                                            <span
                                                v-else-if="g.rsvp && g.rsvp.attendance_status === 'declined'"
                                                class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800"
                                            >
                                                Berhalangan
                                            </span>
                                            <span
                                                v-else
                                                class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-stone-100 text-stone-500"
                                            >
                                                Belum Konfirmasi
                                            </span>
                                        </div>
                                        <div>
                                            <span
                                                v-if="g.checked_in_at"
                                                class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[9px] font-bold bg-teal-100 text-teal-800 border border-teal-200"
                                                title="Tamu telah hadir di resepsi"
                                            >
                                                ✓ Hadir Resepsi
                                            </span>
                                        </div>
                                    </td>
                                    <td class="p-3.5">
                                        <div class="flex items-center gap-1.5">
                                            <button
                                                @click="copyGuestUrl(g.name, g.token)"
                                                class="px-2 py-1 bg-stone-100 hover:bg-stone-200 text-stone-700 rounded text-[11px] font-medium transition-colors"
                                                title="Salin Link Personal"
                                            >
                                                📋 Salin Link
                                            </button>
                                            <button
                                                @click="openWaModal(g)"
                                                class="px-2.5 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-lg text-[11px] font-semibold transition-colors flex items-center gap-1 border border-emerald-200"
                                                title="Kirim Undangan WhatsApp"
                                            >
                                                <span>💬</span>
                                                <span>Kirim WA</span>
                                            </button>
                                        </div>
                                    </td>
                                    <td class="p-3.5 text-right">
                                        <button
                                            @click="deleteGuest(g.id, g.name)"
                                            class="text-stone-400 hover:text-rose-600 transition-colors"
                                        >
                                            ✕
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="guests.length === 0">
                                    <td colspan="4" class="p-8 text-center text-stone-400">
                                        Belum ada tamu yang ditambahkan. Gunakan form di sebelah kiri untuk menambah tamu pertama.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TAB 2: GUESTBOOK MODERATION -->
            <div v-if="activeTab === 'guestbook'" class="bg-white rounded-2xl border border-stone-200 p-6 space-y-4">
                <h3 class="font-serif font-bold text-base text-stone-900">Moderasi Ucapan & Doa Restu</h3>

                <div class="space-y-3">
                    <div
                        v-for="entry in guestbooks"
                        :key="entry.id"
                        class="p-4 rounded-xl border border-stone-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"
                    >
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="font-bold text-xs text-stone-900">{{ entry.guest_name }}</span>
                                <span
                                    v-if="entry.moderation_status === 'approved'"
                                    class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-emerald-100 text-emerald-800"
                                >
                                    Disetujui (Tampil)
                                </span>
                                <span
                                    v-else-if="entry.moderation_status === 'rejected'"
                                    class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-rose-100 text-rose-800"
                                >
                                    Ditolak (Sembunyi)
                                </span>
                                <span
                                    v-else
                                    class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-amber-100 text-amber-800"
                                >
                                    Menunggu Moderasi
                                </span>
                            </div>
                            <p class="text-xs text-stone-600">{{ entry.message }}</p>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            <button
                                v-if="entry.moderation_status !== 'approved'"
                                @click="moderateMessage(entry.id, 'approved')"
                                class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold"
                            >
                                Setujui
                            </button>
                            <button
                                v-if="entry.moderation_status !== 'rejected'"
                                @click="moderateMessage(entry.id, 'rejected')"
                                class="px-3 py-1 bg-rose-100 hover:bg-rose-200 text-rose-700 rounded-lg text-xs font-semibold"
                            >
                                Sembunyikan
                            </button>
                        </div>
                    </div>

                    <div v-if="guestbooks.length === 0" class="text-center text-xs text-stone-400 py-8">
                        Belum ada pesan ucapan masuk dari tamu.
                    </div>
                </div>
            </div>
        </div>

        <!-- WhatsApp Invitation Generator Modal -->
        <div
            v-if="showWaModal"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs animate-in fade-in duration-150"
        >
            <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-stone-200 space-y-5">
                <div class="flex items-center justify-between border-b border-stone-100 pb-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold text-lg">
                            💬
                        </div>
                        <div>
                            <h3 class="font-bold text-stone-900 text-base">Kirim Undangan WhatsApp</h3>
                            <p class="text-xs text-stone-400">Kepada: <span class="font-semibold text-stone-700">{{ selectedGuest?.name }}</span></p>
                        </div>
                    </div>
                    <button
                        @click="showWaModal = false"
                        class="text-stone-400 hover:text-stone-700 p-1 rounded-lg"
                    >
                        ✕
                    </button>
                </div>

                <!-- Template Selector -->
                <div>
                    <label class="block text-xs font-semibold text-stone-700 mb-1.5">Pilihan Gaya Pesan:</label>
                    <div class="grid grid-cols-2 gap-2">
                        <button
                            type="button"
                            @click="switchTemplate('formal')"
                            :class="[
                                'py-2 px-3 rounded-xl text-xs font-semibold border transition-all text-center',
                                waTemplateType === 'formal' ? 'bg-emerald-50 border-emerald-500 text-emerald-800 shadow-xs' : 'bg-stone-50 border-stone-200 text-stone-600 hover:bg-stone-100'
                            ]"
                        >
                            📜 Formal / Islami (Baku)
                        </button>
                        <button
                            type="button"
                            @click="switchTemplate('casual')"
                            :class="[
                                'py-2 px-3 rounded-xl text-xs font-semibold border transition-all text-center',
                                waTemplateType === 'casual' ? 'bg-emerald-50 border-emerald-500 text-emerald-800 shadow-xs' : 'bg-stone-50 border-stone-200 text-stone-600 hover:bg-stone-100'
                            ]"
                        >
                            ✨ Santai / Modern (Teman)
                        </button>
                    </div>
                </div>

                <!-- Message Preview & Editor -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-semibold text-stone-700">Teks Pesan (Dapat Disesuaikan):</label>
                        <span v-if="copiedWaToast" class="text-[11px] font-bold text-emerald-600 animate-pulse">
                            ✓ Berhasil disalin!
                        </span>
                    </div>
                    <textarea
                        v-model="customWaMessage"
                        rows="7"
                        class="w-full text-xs font-sans p-3 rounded-2xl border border-stone-200 bg-stone-50 focus:bg-white focus:border-emerald-500 focus:ring-emerald-500 leading-relaxed text-stone-800"
                    ></textarea>
                    <p class="text-[10px] text-stone-400 mt-1">
                        Nomor tujuan: <span class="font-mono text-stone-600">{{ selectedGuest?.phone_number || '(Belum ada nomor, akan membuka WA Web)' }}</span>
                    </p>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-stone-100">
                    <button
                        type="button"
                        @click="copyWaMessage"
                        class="px-4 py-2.5 rounded-xl border border-stone-200 bg-stone-50 hover:bg-stone-100 text-stone-700 font-semibold text-xs transition-colors flex items-center gap-1.5"
                    >
                        <span>📋</span>
                        <span>Salin Teks Pesan</span>
                    </button>
                    <button
                        type="button"
                        @click="sendViaWhatsApp"
                        class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-md shadow-emerald-200 transition-colors flex items-center gap-1.5"
                    >
                        <span>🚀</span>
                        <span>Buka WhatsApp Sekarang</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Import Guests Modal (Excel/CSV/Text) -->
        <div
            v-if="showImportModal"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs animate-in fade-in duration-150"
        >
            <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-stone-200 space-y-4">
                <div class="flex items-center justify-between border-b border-stone-100 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="text-xl">📥</span>
                        <h3 class="font-bold text-stone-900 text-base">Import Daftar Tamu Sekaligus</h3>
                    </div>
                    <button
                        @click="showImportModal = false"
                        class="text-stone-400 hover:text-stone-700 p-1 rounded-lg"
                    >
                        ✕
                    </button>
                </div>

                <div class="flex gap-2">
                    <button
                        type="button"
                        @click="importMode = 'file'"
                        :class="[
                            'flex-1 py-2 px-3 rounded-xl text-xs font-semibold border transition-all text-center',
                            importMode === 'file' ? 'bg-rose-50 border-rose-500 text-rose-800' : 'bg-stone-50 border-stone-200 text-stone-600'
                        ]"
                    >
                        📄 Upload File CSV
                    </button>
                    <button
                        type="button"
                        @click="importMode = 'text'"
                        :class="[
                            'flex-1 py-2 px-3 rounded-xl text-xs font-semibold border transition-all text-center',
                            importMode === 'text' ? 'bg-rose-50 border-rose-500 text-rose-800' : 'bg-stone-50 border-stone-200 text-stone-600'
                        ]"
                    >
                        ✍️ Copy-Paste Teks
                    </button>
                </div>

                <div v-if="importMode === 'file'" class="space-y-3">
                    <div class="p-4 border-2 border-dashed border-stone-300 rounded-2xl bg-stone-50 text-center hover:bg-stone-100/50 transition-colors">
                        <input
                            type="file"
                            accept=".csv,.txt"
                            @change="onFileChange"
                            class="block w-full text-xs text-stone-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-stone-900 file:text-white hover:file:bg-stone-800 cursor-pointer"
                        />
                        <p class="text-[11px] text-stone-400 mt-2">
                            Mendukung file .CSV dengan kolom: <strong>Nama Tamu, No HP (opsional), Kuota Pax (opsional)</strong>
                        </p>
                    </div>
                </div>

                <div v-else class="space-y-2">
                    <label class="block text-xs font-semibold text-stone-700">Tempel / Paste Baris Nama Tamu:</label>
                    <textarea
                        v-model="importRawText"
                        rows="6"
                        placeholder="Contoh format per baris:
Budi Santoso, 08123456789, 2
Siti Aminah, 08567890123, 1
Keluarga Besar Bpk Rahmat,, 4"
                        class="w-full text-xs font-mono p-3 rounded-2xl border border-stone-200 bg-stone-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-rose-500"
                    ></textarea>
                    <p class="text-[10px] text-stone-400">
                        Format: <code class="bg-stone-100 px-1 py-0.5 rounded">Nama, Nomor HP (opsional), Kuota (opsional)</code>
                    </p>
                </div>

                <div v-if="importError" class="p-2.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs">
                    {{ importError }}
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-stone-100">
                    <button
                        type="button"
                        @click="showImportModal = false"
                        class="px-4 py-2 rounded-xl border border-stone-200 text-stone-600 hover:bg-stone-50 text-xs font-semibold"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        @click="handleImportSubmit"
                        :disabled="importSubmitting"
                        class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-md shadow-rose-200 transition-colors disabled:opacity-50"
                    >
                        {{ importSubmitting ? 'Mengimpor...' : 'Mulai Import' }}
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

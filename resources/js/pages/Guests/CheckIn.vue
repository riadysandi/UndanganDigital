<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import confetti from 'canvas-confetti';

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
        checked_in_at: string | null;
        rsvp: {
            attendance_status: string;
            attendee_count: number;
            message: string | null;
        } | null;
    }>;
    stats: {
        total_guests: number;
        checked_in: number;
        remaining: number;
    };
}>();

// State
const searchQuery = ref('');
const localGuests = ref([...props.guests]);
const checkedInCount = ref(props.stats.checked_in);
const totalGuestsCount = ref(props.stats.total_guests);

// Modal & Audio Feedback
const checkinStatus = ref<{
    show: boolean;
    success: boolean;
    already: boolean;
    message: string;
    guest: any | null;
}>({
    show: false,
    success: false,
    already: false,
    message: '',
    guest: null,
});

const isProcessing = ref(false);

const filteredGuests = computed(() => {
    if (!searchQuery.value.trim()) return localGuests.value;
    const q = searchQuery.value.toLowerCase().trim();
    return localGuests.value.filter(g =>
        g.name.toLowerCase().includes(q) ||
        g.token.toLowerCase().includes(q) ||
        (g.phone_number && g.phone_number.includes(q))
    );
});

const performCheckIn = async (queryParam: string) => {
    if (!queryParam.trim() || isProcessing.value) return;

    isProcessing.value = true;
    try {
        const res = await fetch(`/api/invitations/${props.invitation.id}/guests/check-in`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as any)?.content || '',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ query: queryParam }),
        });

        const data = await res.json();
        if (res.ok && data.success) {
            checkinStatus.value = {
                show: true,
                success: true,
                already: data.already_checked_in,
                message: data.message,
                guest: data.guest,
            };

            // Update in local list
            const idx = localGuests.value.findIndex(g => g.id === data.guest.id);
            if (idx !== -1) {
                localGuests.value[idx].checked_in_at = data.guest.checked_in_at;
            }

            if (!data.already_checked_in) {
                checkedInCount.value++;
                confetti({
                    particleCount: 80,
                    spread: 70,
                    origin: { y: 0.6 },
                    colors: ['#10B981', '#34D399', '#F59E0B', '#FBBF24'],
                });
            }
        } else {
            checkinStatus.value = {
                show: true,
                success: false,
                already: false,
                message: data.message || 'Tamu tidak ditemukan.',
                guest: null,
            };
        }
    } catch (e) {
        checkinStatus.value = {
            show: true,
            success: false,
            already: false,
            message: 'Terjadi gangguan koneksi.',
            guest: null,
        };
    } finally {
        isProcessing.value = false;
        searchQuery.value = '';
    }
};

const closeStatus = () => {
    checkinStatus.value.show = false;
};
</script>

<template>
    <AppLayout>
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">
            <!-- Header Navigation -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-stone-200">
                <div>
                    <Link :href="`/invitations/${invitation.id}/guests`" class="text-xs font-semibold text-rose-600 hover:text-rose-700 flex items-center gap-1 mb-1">
                        ← Kembali ke Manajemen Tamu
                    </Link>
                    <h1 class="text-2xl font-serif font-bold text-stone-900 flex items-center gap-2">
                        <span>📱 Meja Resepsi & Check-in Tamu</span>
                        <span class="text-xs font-sans px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-bold uppercase tracking-wider">
                            Live Scanner Ready
                        </span>
                    </h1>
                    <p class="text-xs text-stone-500">
                        Scan QR Code di kartu undangan digital tamu atau cari berdasarkan nama tamu untuk absensi kehadiran langsung.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <button
                        @click="searchQuery = ''"
                        class="px-3.5 py-2 rounded-xl bg-stone-100 hover:bg-stone-200 text-stone-700 font-semibold text-xs transition-colors"
                    >
                        🔄 Refresh Data
                    </button>
                    <Link
                        :href="`/invitations/${invitation.id}/guests/export`"
                        class="px-3.5 py-2 rounded-xl bg-white border border-stone-200 hover:bg-stone-50 text-stone-700 font-semibold text-xs shadow-xs transition-colors flex items-center gap-1.5"
                    >
                        <span>📥</span>
                        <span>Export Laporan</span>
                    </Link>
                </div>
            </div>

            <!-- Attendance Counter Stats -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 my-6">
                <div class="p-5 bg-gradient-to-br from-white to-stone-50 rounded-2xl border border-stone-200 shadow-xs">
                    <div class="text-[11px] uppercase tracking-wider font-bold text-stone-500">Total Tamu Terdaftar</div>
                    <div class="font-serif text-3xl font-bold text-stone-900 mt-1">{{ totalGuestsCount }} Orang</div>
                    <div class="text-xs text-stone-400 mt-1">Undangan terkirim</div>
                </div>

                <div class="p-5 bg-gradient-to-br from-emerald-500/10 to-teal-500/5 rounded-2xl border border-emerald-500/30 shadow-xs">
                    <div class="text-[11px] uppercase tracking-wider font-bold text-emerald-800">Sudah Hadir (Checked-in)</div>
                    <div class="font-serif text-3xl font-bold text-emerald-700 mt-1">{{ checkedInCount }} Tamu</div>
                    <div class="text-xs text-emerald-600 mt-1">
                        {{ totalGuestsCount > 0 ? Math.round((checkedInCount / totalGuestsCount) * 100) : 0 }}% Kehadiran
                    </div>
                </div>

                <div class="p-5 bg-gradient-to-br from-amber-500/10 to-orange-500/5 rounded-2xl border border-amber-500/30 shadow-xs">
                    <div class="text-[11px] uppercase tracking-wider font-bold text-amber-800">Belum Check-in</div>
                    <div class="font-serif text-3xl font-bold text-amber-700 mt-1">{{ totalGuestsCount - checkedInCount }} Tamu</div>
                    <div class="text-xs text-amber-600 mt-1">Menunggu kedatangan</div>
                </div>
            </div>

            <!-- Search / Scan Input Box -->
            <div class="bg-white p-5 rounded-3xl border border-stone-200 shadow-sm space-y-4 mb-8">
                <div class="flex items-center gap-2">
                    <span class="text-xl">🔍</span>
                    <h3 class="font-bold text-stone-900 text-sm">Input Token QR atau Cari Nama Tamu</h3>
                </div>

                <form @submit.prevent="performCheckIn(searchQuery)" class="flex gap-2">
                    <div class="relative flex-1">
                        <input
                            v-model="searchQuery"
                            type="text"
                            autofocus
                            placeholder="Ketik nama tamu, scan QR Token (cth: d7a1...), atau nomor HP..."
                            class="w-full text-sm py-3 px-4 rounded-2xl border border-stone-300 focus:outline-none focus:ring-2 focus:ring-emerald-500 bg-stone-50 focus:bg-white transition-all font-mono"
                        />
                        <button
                            v-if="searchQuery"
                            @click="searchQuery = ''"
                            type="button"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-stone-400 hover:text-stone-600 text-xs bg-stone-200 px-1.5 py-0.5 rounded-full"
                        >
                            ✕
                        </button>
                    </div>

                    <button
                        type="submit"
                        :disabled="isProcessing || !searchQuery.trim()"
                        class="px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl text-xs font-bold uppercase tracking-wider shadow-md shadow-emerald-500/20 transition-all disabled:opacity-50 flex items-center gap-2 shrink-0"
                    >
                        <span>✓</span>
                        <span>{{ isProcessing ? 'Mengecek...' : 'Check-in Langsung' }}</span>
                    </button>
                </form>
            </div>

            <!-- Guests Attendance Table -->
            <div class="bg-white rounded-3xl border border-stone-200 shadow-sm overflow-hidden">
                <div class="p-4 bg-stone-50 border-b border-stone-200 flex items-center justify-between">
                    <span class="text-xs font-bold text-stone-700 uppercase tracking-wider">
                        Daftar Tamu Undangan ({{ filteredGuests.length }})
                    </span>
                    <span class="text-xs text-stone-400">
                        Klik tombol "Check-in" untuk mencatat kehadiran tamu secara manual.
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-stone-600">
                        <thead class="bg-stone-50/80 border-b border-stone-200 text-stone-500 font-semibold uppercase text-[10px]">
                            <tr>
                                <th class="p-4">Nama Tamu</th>
                                <th class="p-4">Kode Token</th>
                                <th class="p-4">Konfirmasi RSVP</th>
                                <th class="p-4">Status Check-in</th>
                                <th class="p-4 text-right">Aksi Meja Resepsi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100">
                            <tr
                                v-for="guest in filteredGuests"
                                :key="guest.id"
                                :class="[guest.checked_in_at ? 'bg-emerald-50/40' : 'hover:bg-stone-50/50']"
                            >
                                <td class="p-4">
                                    <div class="font-bold text-stone-900 text-sm">{{ guest.name }}</div>
                                    <div class="text-[11px] text-stone-400">
                                        Kuota: {{ guest.max_attendees }} pax &bull; {{ guest.phone_number || 'Tidak ada HP' }}
                                    </div>
                                </td>
                                <td class="p-4 font-mono text-[11px] text-stone-500">
                                    <span class="px-2 py-1 bg-stone-100 rounded-md border border-stone-200">
                                        {{ guest.token }}
                                    </span>
                                </td>
                                <td class="p-4">
                                    <span
                                        v-if="guest.rsvp && guest.rsvp.attendance_status === 'attending'"
                                        class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800"
                                    >
                                        Hadir ({{ guest.rsvp.attendee_count }} org)
                                    </span>
                                    <span
                                        v-else-if="guest.rsvp && guest.rsvp.attendance_status === 'declined'"
                                        class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800"
                                    >
                                        Berhalangan
                                    </span>
                                    <span
                                        v-else
                                        class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-stone-100 text-stone-500"
                                    >
                                        Belum RSVP
                                    </span>
                                </td>
                                <td class="p-4">
                                    <div v-if="guest.checked_in_at" class="flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                        <span class="font-bold text-emerald-700 text-xs">SUDAH HADIR</span>
                                    </div>
                                    <div v-else class="flex items-center gap-1.5 text-stone-400">
                                        <span class="w-2 h-2 rounded-full bg-stone-300"></span>
                                        <span class="text-xs">Belum Hadir</span>
                                    </div>
                                </td>
                                <td class="p-4 text-right">
                                    <button
                                        v-if="!guest.checked_in_at"
                                        @click="performCheckIn(guest.token)"
                                        :disabled="isProcessing"
                                        class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold shadow-xs transition-colors"
                                    >
                                        Check-in
                                    </button>
                                    <span
                                        v-else
                                        class="text-xs font-mono text-emerald-600 bg-emerald-100/60 px-2.5 py-1 rounded-lg"
                                    >
                                        ✓ Masuk
                                    </span>
                                </td>
                            </tr>

                            <tr v-if="filteredGuests.length === 0">
                                <td colspan="5" class="p-12 text-center text-stone-400">
                                    Tidak ada tamu yang cocok dengan pencarian "{{ searchQuery }}".
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Check-in Status Popup Modal -->
            <div
                v-if="checkinStatus.show"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs animate-in fade-in duration-150"
            >
                <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-stone-200 text-center space-y-4">
                    <div
                        :class="[
                            'w-16 h-16 rounded-3xl mx-auto flex items-center justify-center text-3xl font-bold',
                            checkinStatus.success ? (checkinStatus.already ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700') : 'bg-rose-100 text-rose-700'
                        ]"
                    >
                        <span v-if="checkinStatus.success">{{ checkinStatus.already ? '⚠️' : '🎉' }}</span>
                        <span v-else>✕</span>
                    </div>

                    <div>
                        <h3 class="font-serif text-xl font-bold text-stone-900">
                            {{ checkinStatus.success ? (checkinStatus.already ? 'Tamu Sudah Check-in Sebelumnya' : 'Check-in Berhasil!') : 'Gagal Check-in' }}
                        </h3>
                        <p class="text-xs text-stone-500 mt-1">
                            {{ checkinStatus.message }}
                        </p>
                    </div>

                    <div v-if="checkinStatus.guest" class="p-4 bg-stone-50 rounded-2xl border border-stone-200 text-left space-y-1.5 text-xs">
                        <div class="flex justify-between">
                            <span class="text-stone-400 font-medium">Nama Tamu:</span>
                            <span class="font-bold text-stone-900">{{ checkinStatus.guest.name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-stone-400 font-medium">Kuota Hadir:</span>
                            <span class="font-bold text-stone-900">{{ checkinStatus.guest.max_attendees }} Orang</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-stone-400 font-medium">Waktu Check-in:</span>
                            <span class="font-bold text-emerald-700 font-mono">{{ checkinStatus.guest.checked_in_at }}</span>
                        </div>
                    </div>

                    <button
                        @click="closeStatus"
                        class="w-full py-3 bg-stone-900 hover:bg-stone-800 text-white rounded-2xl text-xs font-bold uppercase tracking-wider transition-colors"
                    >
                        Selesai / Lanjut Tamu Berikutnya
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

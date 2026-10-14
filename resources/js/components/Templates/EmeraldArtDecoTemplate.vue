<script setup lang="ts">
import { ref, computed } from 'vue';
import confetti from 'canvas-confetti';

const props = defineProps<{
    invitationData: any;
    events: Array<{
        id: number;
        name: string;
        starts_at: string;
        ends_at: string | null;
        timezone: string;
        venue_name: string;
        address: string | null;
        maps_url: string | null;
    }>;
    guestbook: Array<{
        id: number;
        guest_name: string;
        message: string;
        created_at: string;
    }>;
    digitalGift: any;
    guestName?: string | null;
    guestToken?: string | null;
    isDemo?: boolean;
    template?: any;
    isOpen: boolean;
    isPlayingMusic: boolean;
    timeLeft: { days: number; hours: number; minutes: number; seconds: number };
    formatDisplayDate?: (d: string) => string;
    formatEventTime?: (s: string, e: string | null, tz: string) => string;
    copyAddress: (addr: string) => void;
    openQris: (acc: any) => void;
    submitRsvp: () => void;
    rsvpForm: any;
    rsvpSubmitting: boolean;
    rsvpSuccess: boolean;
    rsvpErrorMessage: string;
}>();

const emit = defineEmits<{
    (e: 'openInvitation'): void;
    (e: 'toggleMusic'): void;
}>();

const isOpening = ref(false);

const handleOpen = () => {
    isOpening.value = true;
    confetti({
        particleCount: 60,
        spread: 80,
        origin: { y: 0.5 },
        colors: ['#D4AF37', '#10B981', '#F59E0B', '#FDE047']
    });

    setTimeout(() => {
        emit('openInvitation');
    }, 800);
};

// Formatting helpers
const safeFormatEventTime = (startsAt: string, endsAt: string | null, tz: string = 'WIB') => {
    if (typeof props.formatEventTime === 'function') {
        return props.formatEventTime(startsAt, endsAt, tz);
    }
    return '18:00 WIB - Selesai';
};

const safeFormatDisplayDate = (d: string) => {
    if (typeof props.formatDisplayDate === 'function') {
        return props.formatDisplayDate(d);
    }
    return d || 'Sabtu, 26 Desember 2026';
};

const copiedBankIndex = ref<number | null>(null);
const copyBankNumber = (num: string, index: number) => {
    navigator.clipboard.writeText(num);
    copiedBankIndex.value = index;
    setTimeout(() => {
        copiedBankIndex.value = null;
    }, 2500);
};

const bride = computed(() => props.invitationData?.content?.bride || {});
const groom = computed(() => props.invitationData?.content?.groom || {});
const weddingTitle = computed(() => props.invitationData?.content?.weddingTitle || 'Reihan & Natasha');
const weddingDate = computed(() => props.invitationData?.content?.weddingDateText || 'Desember 2026');
const coverImg = computed(() => props.invitationData?.content?.coverImage || 'https://images.unsplash.com/photo-1520854221256-17451cc331bf?auto=format&fit=crop&w=1200&q=80');
</script>

<template>
    <div class="emerald-artdeco-theme min-h-screen bg-[#071D14] text-[#E8F5E9] font-serif selection:bg-[#D4AF37] selection:text-stone-900 overflow-x-hidden relative">

        <!-- Art Deco Subtle Background Lines & Radial Emerald Glow -->
        <div class="pointer-events-none fixed inset-0 bg-[radial-gradient(ellipse_at_center,_var(--tw-gradient-stops))] from-[#0D3827]/70 via-transparent to-transparent -z-10"></div>
        <div class="pointer-events-none fixed inset-0 opacity-10 bg-[radial-gradient(#D4AF37_1px,transparent_1px)] [background-size:32px_32px] -z-10"></div>

        <!-- ======================================================== -->
        <!-- 1. ART DECO GOLD STARBURST COVER (Before Opened)         -->
        <!-- ======================================================== -->
        <div
            v-if="!isOpen"
            class="fixed inset-0 z-50 flex flex-col items-center justify-between p-6 bg-gradient-to-b from-[#051710] via-[#092B1E] to-[#04130D] transition-opacity duration-800"
            :class="{ 'opacity-0 pointer-events-none': isOpening }"
        >
            <!-- Top Art Deco Header -->
            <div class="text-center pt-8">
                <div class="w-12 h-12 rounded-full border-2 border-[#D4AF37] flex items-center justify-center mx-auto mb-2 text-xl text-[#D4AF37] shadow-[0_0_15px_rgba(212,175,55,0.4)]">
                    ✦
                </div>
                <div class="text-[10px] tracking-[0.4em] uppercase text-[#D4AF37] font-sans font-bold">
                    The Grand Matrimony
                </div>
                <h1 class="text-3xl sm:text-5xl font-serif text-white tracking-tight mt-1 font-bold">
                    {{ weddingTitle }}
                </h1>
            </div>

            <!-- Geometric Art Deco Arch Photo Card -->
            <div class="relative w-full max-w-xs sm:max-w-sm aspect-[3/4] my-auto select-none rounded-[36px] p-2 border border-[#D4AF37]/40 bg-[#0A2F21]/80 backdrop-blur-md shadow-2xl shadow-black/80 flex flex-col items-center justify-between">
                
                <!-- Inner Gold Inset Border -->
                <div class="absolute inset-3 border border-[#D4AF37]/25 rounded-[28px] pointer-events-none"></div>

                <div class="w-full h-full rounded-[28px] overflow-hidden relative">
                    <img :src="coverImg" :alt="weddingTitle" class="w-full h-full object-cover" />
                    <div class="absolute inset-0 bg-gradient-to-t from-[#071D14] via-transparent to-transparent flex flex-col justify-end p-5 text-center">
                        <div class="text-[10px] uppercase tracking-widest text-[#D4AF37] font-sans">Royal Celebration</div>
                        <div class="text-xs text-stone-300 font-sans mt-0.5">{{ weddingDate }}</div>
                    </div>
                </div>

            </div>

            <!-- Bottom Guest Info & Diamond Button -->
            <div class="text-center pb-8 space-y-4 max-w-sm w-full">
                <div class="p-3 rounded-2xl bg-[#092B1E]/60 border border-[#D4AF37]/30">
                    <div class="text-[9px] uppercase tracking-widest text-stone-400 font-sans">Undangan Eksklusif:</div>
                    <div class="text-sm font-bold text-amber-200 truncate mt-0.5">{{ guestName || 'Tamu Kehormatan' }}</div>
                </div>

                <button
                    @click="handleOpen"
                    class="w-full py-3.5 rounded-full text-xs font-sans uppercase tracking-[0.3em] font-bold bg-gradient-to-r from-[#C29B38] via-[#F3CE6D] to-[#C29B38] text-stone-950 shadow-xl shadow-[#04130D] hover:scale-105 active:scale-95 transition-all cursor-pointer"
                >
                    ✦ Buka Undangan ✦
                </button>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- 2. LUXURY ART DECO CONTENT (When Opened)                 -->
        <!-- ======================================================== -->
        <div class="max-w-2xl mx-auto px-4 sm:px-6 py-14 space-y-16">

            <!-- Music Player Button -->
            <div class="fixed bottom-6 right-6 z-40">
                <button
                    @click="emit('toggleMusic')"
                    class="w-13 h-13 rounded-full bg-[#0A3324] border-2 border-[#D4AF37] shadow-2xl flex items-center justify-center text-xl text-[#D4AF37] hover:scale-110 transition-transform"
                    :class="{ 'animate-spin': isPlayingMusic }"
                    style="animation-duration: 4s;"
                    aria-label="Toggle Musik"
                >
                    <span v-if="isPlayingMusic">🎵</span>
                    <span v-else>🔇</span>
                </button>
            </div>

            <!-- Header Showcase -->
            <header class="text-center space-y-3 border-b border-[#D4AF37]/30 pb-8">
                <div class="text-2xl text-[#D4AF37]">✦</div>
                <div class="text-[10px] tracking-[0.4em] uppercase text-[#D4AF37] font-sans font-bold">
                    Royal Matrimony & Banquet
                </div>
                <h1 class="text-4xl sm:text-6xl font-serif text-white tracking-wide font-bold leading-tight">
                    {{ weddingTitle }}
                </h1>
                <p class="text-sm sm:text-base text-stone-300 font-sans">
                    {{ weddingDate }}
                </p>

                <!-- Arch Photo -->
                <div class="mt-8 max-w-md mx-auto aspect-[3/4] rounded-t-full rounded-b-2xl overflow-hidden border-2 border-[#D4AF37] shadow-2xl">
                    <img :src="coverImg" :alt="weddingTitle" class="w-full h-full object-cover" />
                </div>
            </header>

            <!-- Holy Quote Card -->
            <section class="p-8 rounded-3xl bg-[#092B1E] border border-[#D4AF37]/30 text-center relative overflow-hidden">
                <div class="text-3xl text-[#D4AF37] mb-2">“</div>
                <p class="text-sm sm:text-base italic text-emerald-100 leading-relaxed max-w-lg mx-auto">
                    {{ invitationData?.content?.quote || 'Semoga cinta dan kasih sayang senantiasa abadi, memancarkan kemuliaan dan keberkahan seumur hidup.' }}
                </p>
                <div class="mt-3 text-[10px] uppercase tracking-widest text-[#D4AF37] font-sans font-bold">
                    {{ invitationData?.content?.quoteSource || 'Royal Blessing' }}
                </div>
            </section>

            <!-- Groom and Bride Profiles -->
            <section class="space-y-8">
                <div class="text-center">
                    <div class="text-[10px] tracking-[0.3em] uppercase text-[#D4AF37] font-sans font-bold">
                        The Couple
                    </div>
                    <h2 class="text-3xl font-serif text-white font-bold mt-1">Dua Mempelai</h2>
                </div>

                <div class="grid sm:grid-cols-2 gap-6">
                    <!-- Groom -->
                    <div class="p-6 rounded-3xl bg-[#092A1E] border border-[#D4AF37]/30 text-center">
                        <div class="w-36 h-36 mx-auto rounded-full overflow-hidden border-2 border-[#D4AF37] shadow-xl mb-4">
                            <img :src="groom.photo || 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=400&q=80'" alt="Groom" class="w-full h-full object-cover" />
                        </div>
                        <h3 class="text-2xl font-serif text-amber-200 font-bold">{{ groom.fullName || groom.nickname || 'Reihan Pratama' }}</h3>
                        <p class="text-xs text-stone-300 font-sans mt-2">{{ groom.parents || 'Putra tercinta dari Bpk. Ir. Suryadi & Ibu Desi' }}</p>
                        <div v-if="groom.instagram" class="mt-3 text-xs text-[#D4AF37] font-sans">
                            {{ groom.instagram }}
                        </div>
                    </div>

                    <!-- Bride -->
                    <div class="p-6 rounded-3xl bg-[#092A1E] border border-[#D4AF37]/30 text-center">
                        <div class="w-36 h-36 mx-auto rounded-full overflow-hidden border-2 border-[#D4AF37] shadow-xl mb-4">
                            <img :src="bride.photo || 'https://images.unsplash.com/photo-1517841905240-472988babdf9?auto=format&fit=crop&w=400&q=80'" alt="Bride" class="w-full h-full object-cover" />
                        </div>
                        <h3 class="text-2xl font-serif text-amber-200 font-bold">{{ bride.fullName || bride.nickname || 'Natasha Aurelia' }}</h3>
                        <p class="text-xs text-stone-300 font-sans mt-2">{{ bride.parents || 'Putri tercinta dari Bpk. Harun & Ibu Melly' }}</p>
                        <div v-if="bride.instagram" class="mt-3 text-xs text-[#D4AF37] font-sans">
                            {{ bride.instagram }}
                        </div>
                    </div>
                </div>
            </section>

            <!-- Events Schedule -->
            <section class="space-y-6">
                <div class="text-center">
                    <div class="text-[10px] tracking-[0.3em] uppercase text-[#D4AF37] font-sans font-bold">
                        Itinerary
                    </div>
                    <h2 class="text-3xl font-serif text-white font-bold mt-1">Waktu & Tempat Acara</h2>
                </div>

                <div class="space-y-6">
                    <div
                        v-for="ev in events"
                        :key="ev.id"
                        class="p-8 rounded-3xl bg-[#08281D] border border-[#D4AF37]/40 text-center shadow-xl relative"
                    >
                        <div class="text-xl text-[#D4AF37] mb-2">✦</div>
                        <h3 class="text-2xl font-serif text-white font-bold">{{ ev.name }}</h3>
                        <div class="my-3 py-2 border-y border-[#D4AF37]/30 inline-block px-6">
                            <p class="text-sm font-bold text-amber-300 font-sans">{{ safeFormatDisplayDate(ev.starts_at) }}</p>
                            <p class="text-xs text-stone-300 font-sans mt-0.5">{{ safeFormatEventTime(ev.starts_at, ev.ends_at, ev.timezone) }}</p>
                        </div>
                        <h4 class="text-base font-semibold text-white">{{ ev.venue_name }}</h4>
                        <p class="text-xs text-stone-300 mt-1 max-w-md mx-auto font-sans leading-relaxed">{{ ev.address }}</p>
                        <div class="mt-5">
                            <a
                                :href="ev.maps_url || 'https://maps.google.com'"
                                target="_blank"
                                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-full text-xs font-sans uppercase tracking-widest font-bold bg-[#D4AF37] hover:bg-[#F2CD5C] text-stone-950 shadow-md transition-all hover:scale-105"
                            >
                                <span>📍 Petunjuk Google Maps</span>
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Digital Gift Accounts -->
            <section v-if="digitalGift?.enabled" class="p-8 rounded-3xl bg-[#092B1E] border border-[#D4AF37]/30 text-center space-y-6">
                <div class="text-[10px] tracking-[0.3em] uppercase text-[#D4AF37] font-sans font-bold">
                    Gift & Tanda Kasih
                </div>
                <h2 class="text-3xl font-serif text-white font-bold">Amplop Digital</h2>

                <div class="grid sm:grid-cols-2 gap-4 max-w-lg mx-auto">
                    <div
                        v-for="(acc, idx) in digitalGift.accounts"
                        :key="idx"
                        class="p-5 rounded-2xl bg-[#051C13] border border-[#D4AF37]/30 text-left flex flex-col justify-between"
                    >
                        <div>
                            <div class="text-[10px] text-stone-400 font-sans uppercase">Transfer Rekening</div>
                            <div class="text-lg font-bold text-amber-200">{{ acc.bank }}</div>
                            <div class="text-sm font-mono text-white mt-1">{{ acc.account_number }}</div>
                            <div class="text-xs text-stone-300">a.n. {{ acc.account_name }}</div>
                        </div>
                        <button
                            @click="copyBankNumber(acc.account_number, idx)"
                            class="mt-4 w-full py-2 rounded-xl text-xs font-sans font-semibold bg-[#D4AF37]/20 hover:bg-[#D4AF37] text-amber-200 hover:text-stone-900 border border-[#D4AF37]/40 transition-colors"
                        >
                            {{ copiedBankIndex === idx ? '✓ Berhasil Disalin!' : 'Salin Nomor Rekening' }}
                        </button>
                    </div>
                </div>
            </section>

            <!-- RSVP Form -->
            <section class="p-8 rounded-3xl bg-[#092B1E] border border-[#D4AF37]/30 space-y-6">
                <div class="text-center">
                    <div class="text-[10px] tracking-[0.3em] uppercase text-[#D4AF37] font-sans font-bold">
                        RSVP
                    </div>
                    <h2 class="text-3xl font-serif text-white font-bold">Konfirmasi Kehadiran</h2>
                </div>

                <form @submit.prevent="submitRsvp" class="space-y-4 max-w-lg mx-auto font-sans">
                    <div v-if="rsvpSuccess" class="p-4 rounded-xl bg-emerald-950/80 border border-emerald-500/40 text-emerald-200 text-xs text-center">
                        ✓ Terima kasih atas konfirmasi kehadiran Anda!
                    </div>

                    <div>
                        <label class="block text-xs text-stone-300 mb-1">Nama Tamu</label>
                        <input
                            v-model="rsvpForm.guest_name"
                            type="text"
                            required
                            placeholder="Nama Lengkap"
                            class="w-full px-4 py-2.5 rounded-xl bg-[#051C13] border border-[#D4AF37]/30 text-white text-xs focus:outline-none focus:border-[#D4AF37]"
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs text-stone-300 mb-1">Kehadiran</label>
                            <select
                                v-model="rsvpForm.status"
                                class="w-full px-3 py-2.5 rounded-xl bg-[#051C13] border border-[#D4AF37]/30 text-white text-xs focus:outline-none focus:border-[#D4AF37]"
                            >
                                <option value="attending">Hadir</option>
                                <option value="declined">Berhalangan</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-stone-300 mb-1">Jumlah</label>
                            <input
                                v-model.number="rsvpForm.attendees_count"
                                type="number"
                                min="1"
                                max="10"
                                class="w-full px-4 py-2.5 rounded-xl bg-[#051C13] border border-[#D4AF37]/30 text-white text-xs focus:outline-none focus:border-[#D4AF37]"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs text-stone-300 mb-1">Ucapan & Doa</label>
                        <textarea
                            v-model="rsvpForm.wishes"
                            rows="3"
                            placeholder="Doa restu Anda..."
                            class="w-full px-4 py-2.5 rounded-xl bg-[#051C13] border border-[#D4AF37]/30 text-white text-xs focus:outline-none focus:border-[#D4AF37]"
                        ></textarea>
                    </div>

                    <button
                        type="submit"
                        :disabled="rsvpSubmitting"
                        class="w-full py-3 rounded-xl bg-[#D4AF37] hover:bg-[#F2CD5C] text-stone-950 font-bold text-xs uppercase tracking-widest transition-all disabled:opacity-50"
                    >
                        {{ rsvpSubmitting ? 'Mengirim...' : 'Kirim Konfirmasi' }}
                    </button>
                </form>

                <div class="mt-6 pt-4 border-t border-[#D4AF37]/20 max-w-lg mx-auto space-y-3 max-h-64 overflow-y-auto">
                    <div
                        v-for="gb in guestbook"
                        :key="gb.id"
                        class="p-4 rounded-xl bg-[#051C13] border border-[#D4AF37]/20 text-left font-sans"
                    >
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-bold text-amber-200">{{ gb.guest_name }}</span>
                            <span class="text-stone-500 text-[10px]">{{ gb.created_at }}</span>
                        </div>
                        <p class="text-xs text-stone-300 mt-1 leading-relaxed">{{ gb.message }}</p>
                    </div>
                </div>
            </section>

            <!-- Footer -->
            <footer class="text-center py-8 border-t border-[#D4AF37]/30 text-xs text-stone-400 font-sans">
                <p>Terima kasih atas doa restu Anda.</p>
                <div class="text-amber-200 font-serif italic text-lg mt-1">{{ weddingTitle }}</div>
            </footer>

        </div>

    </div>
</template>

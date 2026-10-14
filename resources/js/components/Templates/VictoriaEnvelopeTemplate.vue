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

// Envelope 3D Animation States
const isFlapOpen = ref(false);
const isLetterExtracted = ref(false);
const isWaxBroken = ref(false);

const handleOpenEnvelope = () => {
    if (isWaxBroken.value) return;
    
    // 1. Break wax seal with gold spark confetti
    isWaxBroken.value = true;
    confetti({
        particleCount: 50,
        spread: 60,
        origin: { y: 0.5 },
        colors: ['#D4AF37', '#B38728', '#FBF5B7']
    });

    // 2. Open Flap 3D
    setTimeout(() => {
        isFlapOpen.value = true;
    }, 400);

    // 3. Extract letter upwards
    setTimeout(() => {
        isLetterExtracted.value = true;
    }, 900);

    // 4. Trigger global audio and invitation open
    setTimeout(() => {
        emit('openInvitation');
    }, 1800);
};

// Safe formatting helpers
const safeFormatEventTime = (startsAt: string, endsAt: string | null, tz: string = 'WIB') => {
    if (typeof props.formatEventTime === 'function') {
        return props.formatEventTime(startsAt, endsAt, tz);
    }
    return '08:00 WIB - Selesai';
};

const safeFormatDisplayDate = (d: string) => {
    if (typeof props.formatDisplayDate === 'function') {
        return props.formatDisplayDate(d);
    }
    return d || 'Sabtu, 28 Desember 2026';
};

// Copy bank number feedback
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
const weddingTitle = computed(() => props.invitationData?.content?.weddingTitle || 'Royal Wedding');
const weddingDate = computed(() => props.invitationData?.content?.weddingDateText || 'Desember 2026');
const coverImg = computed(() => props.invitationData?.content?.coverImage || 'https://images.unsplash.com/photo-1511285560929-80b456fea0bc?auto=format&fit=crop&w=1200&q=80');
</script>

<template>
    <div class="victoria-envelope-theme min-h-screen bg-[#1F1914] text-[#EFE7DA] font-serif selection:bg-[#B68D40] selection:text-stone-900 overflow-x-hidden relative">

        <!-- Ambient Royal Lighting -->
        <div class="pointer-events-none fixed inset-0 bg-[radial-gradient(ellipse_at_center,_var(--tw-gradient-stops))] from-[#4A3728]/40 via-transparent to-transparent -z-10"></div>
        <div class="pointer-events-none fixed inset-0 opacity-15 bg-[radial-gradient(#D4AF37_1px,transparent_1px)] [background-size:24px_24px] -z-10"></div>

        <!-- ======================================================== -->
        <!-- 1. PHYSICAL 3D ENVELOPE OPENING STAGE (Before Opened)    -->
        <!-- ======================================================== -->
        <div
            v-if="!isOpen"
            class="fixed inset-0 z-50 flex flex-col items-center justify-center p-4 bg-gradient-to-b from-[#18130F] via-[#231A14] to-[#14100D] transition-opacity duration-1000"
            :class="{ 'opacity-0 pointer-events-none': isLetterExtracted }"
        >
            <!-- Top Subtitle / Royal Crest -->
            <div class="text-center mb-8 animate-fade-in">
                <div class="w-12 h-12 rounded-full border border-[#D4AF37]/50 flex items-center justify-center mx-auto mb-3 text-xl text-[#D4AF37] shadow-[0_0_15px_rgba(212,175,55,0.3)]">
                    ⚜️
                </div>
                <div class="text-[11px] tracking-[0.3em] uppercase text-[#D4AF37] font-sans font-semibold">
                    Exclusive Royal Invitation
                </div>
                <h2 class="text-2xl sm:text-3xl font-serif italic text-[#FDF9F0] mt-1">
                    {{ weddingTitle }}
                </h2>
            </div>

            <!-- 3D Realistic Envelope Assembly -->
            <div class="relative w-full max-w-[360px] sm:max-w-[420px] aspect-[1.45/1] perspective-[1200px] select-none">
                
                <!-- Envelope Back Body -->
                <div class="absolute inset-0 rounded-2xl bg-[#2D221A] border border-[#523E2F] shadow-2xl shadow-black/80 overflow-hidden">
                    <!-- Gold Foil Stamp inside lining -->
                    <div class="absolute inset-2 border border-[#D4AF37]/20 rounded-xl flex items-center justify-center opacity-30">
                        <span class="text-7xl text-[#D4AF37] font-serif">V</span>
                    </div>
                </div>

                <!-- Letter Inside sliding out -->
                <div
                    class="absolute inset-x-4 bottom-4 h-[90%] rounded-xl bg-[#FAF6EE] text-stone-900 p-6 shadow-xl transition-all duration-1000 flex flex-col justify-between items-center text-center"
                    :class="isLetterExtracted ? '-translate-y-48 scale-105 z-30' : 'translate-y-0 z-10'"
                >
                    <div class="w-full flex justify-between items-center text-[10px] text-stone-500 font-sans tracking-widest border-b border-stone-200 pb-2">
                        <span>THE WEDDING OF</span>
                        <span>2026</span>
                    </div>
                    <div class="py-2">
                        <h4 class="font-serif italic text-2xl text-stone-900 font-bold leading-tight">
                            {{ weddingTitle }}
                        </h4>
                        <p class="text-xs text-stone-600 mt-1 font-sans">
                            {{ weddingDate }}
                        </p>
                    </div>
                    <div class="w-full border-t border-stone-200 pt-2 text-[10px] text-stone-600">
                        Special Invitation for You
                    </div>
                </div>

                <!-- Envelope Front Pockets (Triangle Left, Right, Bottom Fold) -->
                <div class="absolute inset-0 z-20 pointer-events-none">
                    <!-- Bottom Pocket Triangle -->
                    <div class="absolute inset-x-0 bottom-0 h-full overflow-hidden rounded-b-2xl">
                        <div class="w-full h-full bg-[#3B2D22] border-t border-[#6B533E] [clip-path:polygon(0_100%,_50%_45%,_100%_100%)] shadow-md"></div>
                    </div>
                    <!-- Left Pocket Triangle -->
                    <div class="absolute inset-y-0 left-0 w-full overflow-hidden rounded-l-2xl">
                        <div class="w-full h-full bg-[#33261C] border-r border-[#544030] [clip-path:polygon(0_0,_45%_50%,_0_100%)]"></div>
                    </div>
                    <!-- Right Pocket Triangle -->
                    <div class="absolute inset-y-0 right-0 w-full overflow-hidden rounded-r-2xl">
                        <div class="w-full h-full bg-[#33261C] border-l border-[#544030] [clip-path:polygon(100%_0,_55%_50%,_100%_100%)]"></div>
                    </div>
                </div>

                <!-- Envelope Top Flap (Folds Open in 3D rotateX) -->
                <div
                    class="absolute inset-x-0 top-0 h-full z-20 origin-top transition-transform duration-700 pointer-events-none"
                    :style="{
                        transform: isFlapOpen ? 'rotateX(180deg)' : 'rotateX(0deg)',
                        transformStyle: 'preserve-3d'
                    }"
                >
                    <div class="w-full h-full bg-[#443326] border-b-2 border-[#D4AF37]/40 [clip-path:polygon(0_0,_50%_56%,_100%_0)] shadow-lg"></div>
                </div>

                <!-- Wax Seal Button (Click to Break & Open) -->
                <button
                    @click="handleOpenEnvelope"
                    class="absolute top-[48%] left-1/2 -translate-x-1/2 -translate-y-1/2 z-30 group cursor-pointer focus:outline-none transition-transform active:scale-95"
                    :disabled="isWaxBroken"
                    aria-label="Buka Amplop Undangan"
                >
                    <div class="relative w-16 h-16 sm:w-18 sm:h-18 rounded-full bg-gradient-to-tr from-[#8E1616] via-[#B31312] to-[#D42B2B] border-2 border-[#E9A178]/50 shadow-[0_4px_25px_rgba(179,19,18,0.7)] flex items-center justify-center group-hover:scale-105 transition-transform">
                        <!-- Intricate Wax Stamping Ring -->
                        <div class="w-12 h-12 rounded-full border border-dashed border-[#FAD02C]/60 flex items-center justify-center text-amber-200 text-lg font-serif">
                            👑
                        </div>
                        <div class="absolute -bottom-6 text-[10px] uppercase tracking-widest font-sans font-bold text-[#D4AF37] whitespace-nowrap bg-black/60 px-2 py-0.5 rounded-full border border-[#D4AF37]/30">
                            {{ isWaxBroken ? 'Membuka...' : 'Tekan Segel Lilin' }}
                        </div>
                    </div>
                </button>

            </div>

            <!-- Guest Name Address on Envelope -->
            <div class="mt-12 text-center max-w-sm px-4">
                <p class="text-xs text-stone-400 font-sans uppercase tracking-wider">
                    Kepada Yth. Bpk/Ibu/Saudara/i:
                </p>
                <h3 class="text-lg sm:text-xl font-bold text-amber-100 mt-1 font-serif">
                    {{ guestName || 'Tamu Undangan Terhormat' }}
                </h3>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- 2. FULL ROYAL INVITATION EXPERIENCE (When Opened)        -->
        <!-- ======================================================== -->
        <div class="max-w-2xl mx-auto px-4 sm:px-6 py-12 space-y-16">

            <!-- Floating Music Toggle Record -->
            <div class="fixed bottom-6 right-6 z-40">
                <button
                    @click="emit('toggleMusic')"
                    class="w-13 h-13 rounded-full bg-[#2B2017] border-2 border-[#D4AF37] shadow-xl flex items-center justify-center text-xl text-[#D4AF37] hover:scale-110 transition-transform"
                    :class="{ 'animate-spin': isPlayingMusic }"
                    style="animation-duration: 4s;"
                    aria-label="Toggle Musik"
                >
                    <span v-if="isPlayingMusic">🎵</span>
                    <span v-else>🔇</span>
                </button>
            </div>

            <!-- Royal Cover Hero Section -->
            <header class="text-center pt-8 pb-6 border-b border-[#D4AF37]/20 relative">
                <div class="w-16 h-16 rounded-full border-2 border-[#D4AF37]/60 flex items-center justify-center mx-auto mb-4 text-2xl text-[#D4AF37] shadow-[0_0_20px_rgba(212,175,55,0.2)]">
                    ⚜️
                </div>
                <div class="text-[11px] tracking-[0.35em] uppercase text-[#D4AF37] font-sans font-bold mb-2">
                    The Wedding Celebration
                </div>
                <h1 class="text-4xl sm:text-6xl font-serif italic text-[#FDF9F0] tracking-tight leading-tight">
                    {{ weddingTitle }}
                </h1>
                <p class="mt-3 text-sm sm:text-base text-stone-400 font-sans tracking-wide">
                    {{ weddingDate }}
                </p>

                <!-- Arch Frame Couple Photo -->
                <div class="mt-8 relative max-w-md mx-auto aspect-[3/4] rounded-t-full rounded-b-2xl overflow-hidden border-4 border-[#D4AF37]/50 shadow-2xl shadow-black/80">
                    <img :src="coverImg" :alt="weddingTitle" class="w-full h-full object-cover" />
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent flex items-end justify-center p-6">
                        <span class="text-xs tracking-widest uppercase text-amber-200 font-sans">Special Matrimony 2026</span>
                    </div>
                </div>
            </header>

            <!-- Holy Quote / Royal Blessing -->
            <section class="text-center py-6 px-4 rounded-3xl bg-[#281F17]/60 border border-[#D4AF37]/20 backdrop-blur-md">
                <div class="text-3xl text-[#D4AF37] mb-3">“</div>
                <p class="text-base sm:text-lg italic text-stone-200 leading-relaxed max-w-lg mx-auto">
                    {{ invitationData?.content?.quote || 'Dan di antara tanda-tanda kebesaran-Nya ialah Dia menciptakan pasangan-pasangan untukmu dari jenismu sendiri, agar kamu cenderung dan merasa tenteram kepadanya.' }}
                </p>
                <div class="mt-3 text-xs tracking-widest text-[#D4AF37] font-sans font-bold uppercase">
                    {{ invitationData?.content?.quoteSource || 'Ar-Rum: 21' }}
                </div>
            </section>

            <!-- Bride and Groom Section (Classical Medallion Styling) -->
            <section class="space-y-12">
                <div class="text-center">
                    <div class="text-[11px] tracking-[0.3em] uppercase text-[#D4AF37] font-sans font-semibold mb-1">
                        Groom & Bride
                    </div>
                    <h2 class="text-3xl font-serif italic text-white">Mempelai yang Berbahagia</h2>
                </div>

                <div class="grid sm:grid-cols-2 gap-8">
                    <!-- Groom -->
                    <div class="text-center p-6 rounded-3xl bg-[#251D16] border border-[#523E2F] hover:border-[#D4AF37]/40 transition-colors">
                        <div class="w-36 h-36 mx-auto rounded-full overflow-hidden border-2 border-[#D4AF37] shadow-xl mb-4">
                            <img :src="groom.photo || 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=400&q=80'" alt="Groom" class="w-full h-full object-cover" />
                        </div>
                        <h3 class="text-2xl font-serif italic text-amber-100 font-bold">{{ groom.fullName || groom.nickname || 'Reihan Pratama' }}</h3>
                        <p class="text-xs text-stone-400 font-sans mt-2 leading-relaxed">{{ groom.parents || 'Putra tercinta dari Bpk. Ir. Suryadi & Ibu Desi' }}</p>
                        <div v-if="groom.instagram" class="mt-3 inline-block text-xs text-[#D4AF37] font-sans">
                            {{ groom.instagram }}
                        </div>
                    </div>

                    <!-- Bride -->
                    <div class="text-center p-6 rounded-3xl bg-[#251D16] border border-[#523E2F] hover:border-[#D4AF37]/40 transition-colors">
                        <div class="w-36 h-36 mx-auto rounded-full overflow-hidden border-2 border-[#D4AF37] shadow-xl mb-4">
                            <img :src="bride.photo || 'https://images.unsplash.com/photo-1517841905240-472988babdf9?auto=format&fit=crop&w=400&q=80'" alt="Bride" class="w-full h-full object-cover" />
                        </div>
                        <h3 class="text-2xl font-serif italic text-amber-100 font-bold">{{ bride.fullName || bride.nickname || 'Natasha Aurelia' }}</h3>
                        <p class="text-xs text-stone-400 font-sans mt-2 leading-relaxed">{{ bride.parents || 'Putri tercinta dari Bpk. Harun & Ibu Melly' }}</p>
                        <div v-if="bride.instagram" class="mt-3 inline-block text-xs text-[#D4AF37] font-sans">
                            {{ bride.instagram }}
                        </div>
                    </div>
                </div>
            </section>

            <!-- Event Agenda Cards (Royal Certificate Style) -->
            <section class="space-y-6">
                <div class="text-center">
                    <div class="text-[11px] tracking-[0.3em] uppercase text-[#D4AF37] font-sans font-semibold mb-1">
                        Timeline of Events
                    </div>
                    <h2 class="text-3xl font-serif italic text-white">Rangkaian Acara</h2>
                </div>

                <div class="space-y-6">
                    <div
                        v-for="ev in events"
                        :key="ev.id"
                        class="p-8 rounded-3xl bg-[#281F17] border border-[#D4AF37]/30 text-center relative overflow-hidden shadow-xl"
                    >
                        <div class="w-10 h-10 rounded-full border border-[#D4AF37]/40 flex items-center justify-center mx-auto mb-3 text-[#D4AF37]">
                            📅
                        </div>
                        <h3 class="text-2xl font-serif italic text-amber-100 font-bold">{{ ev.name }}</h3>
                        <div class="my-4 py-2 border-y border-[#D4AF37]/20 inline-block px-6">
                            <p class="text-sm font-bold text-amber-300 font-sans">{{ safeFormatDisplayDate(ev.starts_at) }}</p>
                            <p class="text-xs text-stone-300 font-sans mt-0.5">{{ safeFormatEventTime(ev.starts_at, ev.ends_at, ev.timezone) }}</p>
                        </div>
                        <h4 class="text-base font-semibold text-white">{{ ev.venue_name }}</h4>
                        <p class="text-xs text-stone-400 mt-1 max-w-md mx-auto font-sans leading-relaxed">{{ ev.address }}</p>
                        <div class="mt-5">
                            <a
                                :href="ev.maps_url || 'https://maps.google.com'"
                                target="_blank"
                                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-full text-xs font-sans uppercase tracking-widest font-bold bg-[#D4AF37] hover:bg-[#F2CD5C] text-stone-950 shadow-md transition-all hover:scale-105"
                            >
                                <span>📍 Buka Petunjuk Arah Google Maps</span>
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Digital Envelope & Gift Accounts -->
            <section v-if="digitalGift?.enabled" class="p-8 rounded-3xl bg-[#251D16] border border-[#523E2F] text-center space-y-6">
                <div class="text-[11px] tracking-[0.3em] uppercase text-[#D4AF37] font-sans font-semibold">
                    Wedding Gift
                </div>
                <h2 class="text-3xl font-serif italic text-white">Tanda Kasih Digital</h2>
                <p class="text-xs text-stone-400 font-sans max-w-md mx-auto">
                    Doa restu Anda adalah karunia terindah bagi kami. Bagi Bapak/Ibu/Saudara/i yang ingin memberikan tanda kasih secara digital, dapat melalui:
                </p>

                <div class="grid sm:grid-cols-2 gap-4 max-w-lg mx-auto">
                    <div
                        v-for="(acc, idx) in digitalGift.accounts"
                        :key="idx"
                        class="p-5 rounded-2xl bg-[#1C1510] border border-[#D4AF37]/30 text-left flex flex-col justify-between"
                    >
                        <div>
                            <div class="text-[10px] text-stone-400 font-sans uppercase">Transfer Rekening</div>
                            <div class="text-lg font-bold text-amber-200 mt-0.5">{{ acc.bank }}</div>
                            <div class="text-sm font-mono text-white mt-1">{{ acc.account_number }}</div>
                            <div class="text-xs text-stone-400 mt-0.5">a.n. {{ acc.account_name }}</div>
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

            <!-- RSVP & Wishes Section -->
            <section class="p-8 rounded-3xl bg-[#281F17] border border-[#D4AF37]/30 space-y-6">
                <div class="text-center">
                    <div class="text-[11px] tracking-[0.3em] uppercase text-[#D4AF37] font-sans font-semibold mb-1">
                        RSVP & Guestbook
                    </div>
                    <h2 class="text-3xl font-serif italic text-white">Konfirmasi Kehadiran</h2>
                </div>

                <form @submit.prevent="submitRsvp" class="space-y-4 max-w-lg mx-auto font-sans">
                    <div v-if="rsvpSuccess" class="p-4 rounded-xl bg-emerald-950/80 border border-emerald-500/40 text-emerald-200 text-xs text-center">
                        ✓ Terima kasih! Konfirmasi kehadiran Anda telah tersimpan dengan indah.
                    </div>
                    <div v-if="rsvpErrorMessage" class="p-4 rounded-xl bg-rose-950/80 border border-rose-500/40 text-rose-200 text-xs text-center">
                        {{ rsvpErrorMessage }}
                    </div>

                    <div>
                        <label class="block text-xs text-stone-300 mb-1">Nama Lengkap</label>
                        <input
                            v-model="rsvpForm.guest_name"
                            type="text"
                            required
                            placeholder="Tuliskan nama Anda"
                            class="w-full px-4 py-2.5 rounded-xl bg-[#1C1510] border border-[#523E2F] text-white text-xs focus:outline-none focus:border-[#D4AF37]"
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs text-stone-300 mb-1">Kehadiran</label>
                            <select
                                v-model="rsvpForm.status"
                                class="w-full px-3 py-2.5 rounded-xl bg-[#1C1510] border border-[#523E2F] text-white text-xs focus:outline-none focus:border-[#D4AF37]"
                            >
                                <option value="attending">Hadir</option>
                                <option value="declined">Tidak Hadir</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-stone-300 mb-1">Jumlah Hadir</label>
                            <input
                                v-model.number="rsvpForm.attendees_count"
                                type="number"
                                min="1"
                                max="10"
                                class="w-full px-4 py-2.5 rounded-xl bg-[#1C1510] border border-[#523E2F] text-white text-xs focus:outline-none focus:border-[#D4AF37]"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs text-stone-300 mb-1">Ucapan & Doa Restu</label>
                        <textarea
                            v-model="rsvpForm.wishes"
                            rows="3"
                            placeholder="Tuliskan doa restu untuk kedua mempelai..."
                            class="w-full px-4 py-2.5 rounded-xl bg-[#1C1510] border border-[#523E2F] text-white text-xs focus:outline-none focus:border-[#D4AF37]"
                        ></textarea>
                    </div>

                    <button
                        type="submit"
                        :disabled="rsvpSubmitting"
                        class="w-full py-3 rounded-xl bg-[#D4AF37] hover:bg-[#F2CD5C] text-stone-950 font-bold text-xs uppercase tracking-widest shadow-md transition-all disabled:opacity-50"
                    >
                        {{ rsvpSubmitting ? 'Mengirim...' : 'Kirim RSVP & Doa Restu' }}
                    </button>
                </form>

                <!-- Guestbook Messages List -->
                <div class="mt-8 pt-6 border-t border-[#D4AF37]/20 max-w-lg mx-auto space-y-3 max-h-72 overflow-y-auto pr-2">
                    <div
                        v-for="gb in guestbook"
                        :key="gb.id"
                        class="p-4 rounded-xl bg-[#1C1510] border border-[#523E2F]/60 text-left font-sans"
                    >
                        <div class="flex items-center justify-between text-[11px]">
                            <span class="font-bold text-amber-200">{{ gb.guest_name }}</span>
                            <span class="text-stone-500 text-[10px]">{{ gb.created_at }}</span>
                        </div>
                        <p class="text-xs text-stone-300 mt-1 leading-relaxed">{{ gb.message }}</p>
                    </div>
                </div>
            </section>

            <!-- Royal Footer -->
            <footer class="text-center py-8 border-t border-[#D4AF37]/20 text-xs text-stone-500 font-sans">
                <p>Terima kasih atas doa restu Anda kepada kami berdua.</p>
                <div class="text-[#D4AF37] font-serif italic text-lg mt-2">{{ weddingTitle }}</div>
            </footer>

        </div>

    </div>
</template>

<style scoped>
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in {
    animation: fadeIn 1s ease-out forwards;
}
</style>

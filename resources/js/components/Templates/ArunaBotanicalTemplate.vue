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

const isOpeningCover = ref(false);

const handleOpen = () => {
    isOpeningCover.value = true;
    confetti({
        particleCount: 50,
        spread: 60,
        origin: { y: 0.6 },
        colors: ['#6B8E23', '#8FBC8F', '#D2B48C', '#FAF0E6']
    });

    setTimeout(() => {
        emit('openInvitation');
    }, 700);
};

// Formatting helpers
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
    return d || 'Minggu, 28 Desember 2026';
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
const weddingTitle = computed(() => props.invitationData?.content?.weddingTitle || 'Bumi & Senja');
const weddingDate = computed(() => props.invitationData?.content?.weddingDateText || 'Desember 2026');
const coverImg = computed(() => props.invitationData?.content?.coverImage || 'https://images.unsplash.com/photo-1465495976277-4387d4b0b4c6?auto=format&fit=crop&w=1200&q=80');
</script>

<template>
    <div class="aruna-botanical-theme min-h-screen bg-[#F9F7F2] text-[#3D3A35] font-serif selection:bg-[#7D8F69] selection:text-white overflow-x-hidden relative">

        <!-- Natural Warm Paper & Leaf Texture -->
        <div class="pointer-events-none fixed inset-0 opacity-25 bg-[radial-gradient(#8F9779_1px,transparent_1px)] [background-size:24px_24px] -z-10"></div>
        <div class="pointer-events-none fixed top-0 left-0 w-72 h-72 bg-[#E3E8DC]/60 rounded-full blur-3xl -z-10"></div>
        <div class="pointer-events-none fixed bottom-0 right-0 w-80 h-80 bg-[#EFE9DB]/70 rounded-full blur-3xl -z-10"></div>

        <!-- ======================================================== -->
        <!-- 1. BOTANICAL GLASSHOUSE ARCH COVER (Before Opened)       -->
        <!-- ======================================================== -->
        <div
            v-if="!isOpen"
            class="fixed inset-0 z-50 flex flex-col items-center justify-center p-4 bg-[#F5F2EB] transition-transform duration-700 ease-out"
            :class="{ '-translate-y-full': isOpeningCover }"
        >
            <!-- Elegant Botanical Glasshouse Arch Card -->
            <div class="relative w-full max-w-sm rounded-[42px] bg-white border border-[#D5DCD0] p-6 shadow-2xl shadow-stone-900/10 text-center flex flex-col justify-between items-center overflow-hidden">
                
                <!-- Floating Olive Leaf Corner Ornaments -->
                <div class="absolute -top-6 -right-6 text-4xl opacity-20 pointer-events-none select-none">🌿</div>
                <div class="absolute -bottom-6 -left-6 text-4xl opacity-20 pointer-events-none select-none">🍃</div>

                <!-- Monogram Header -->
                <div class="pt-2">
                    <div class="w-10 h-10 rounded-full bg-[#EBF0E6] text-[#556B2F] border border-[#CCD7C5] flex items-center justify-center mx-auto mb-2 text-sm font-sans font-bold">
                        B&S
                    </div>
                    <div class="text-[9px] uppercase tracking-[0.3em] text-[#6A7B59] font-sans font-bold">
                        Botanical Garden Wedding
                    </div>
                    <h2 class="text-3xl font-serif italic text-stone-900 font-bold mt-1">
                        {{ weddingTitle }}
                    </h2>
                    <p class="text-xs text-stone-500 font-sans mt-0.5">{{ weddingDate }}</p>
                </div>

                <!-- Arch Photo with Pressed Flower Feel -->
                <div class="my-5 w-48 aspect-[3/4] rounded-t-full rounded-b-xl overflow-hidden border-2 border-[#D5DCD0] shadow-md relative">
                    <img :src="coverImg" :alt="weddingTitle" class="w-full h-full object-cover" />
                    <div class="absolute inset-0 bg-gradient-to-t from-stone-900/40 via-transparent to-transparent"></div>
                </div>

                <!-- Guest Greeting & Button -->
                <div class="w-full space-y-3 pb-2">
                    <div class="p-3 rounded-2xl bg-[#F7F5EE] border border-[#E3DFC9] text-left">
                        <div class="text-[9px] text-stone-400 font-sans uppercase tracking-wider">Spesial Mengundang:</div>
                        <div class="text-xs font-bold text-stone-800 font-sans truncate">{{ guestName || 'Sahabat & Tamu Terhormat' }}</div>
                    </div>

                    <button
                        @click="handleOpen"
                        class="w-full py-3 rounded-2xl bg-[#5E714D] hover:bg-[#4E5E40] text-white font-sans text-xs uppercase tracking-widest font-bold shadow-md hover:shadow-lg transition-all cursor-pointer active:scale-95"
                    >
                        Buka Undangan
                    </button>
                </div>

            </div>
        </div>

        <!-- ======================================================== -->
        <!-- 2. BOTANICAL EDITORIAL INVITATION (When Opened)          -->
        <!-- ======================================================== -->
        <div class="max-w-2xl mx-auto px-4 sm:px-6 py-12 space-y-16">

            <!-- Floating Music Toggle -->
            <div class="fixed bottom-6 right-6 z-40">
                <button
                    @click="emit('toggleMusic')"
                    class="w-12 h-12 rounded-full bg-white border border-[#CCD7C5] shadow-lg flex items-center justify-center text-lg text-[#5E714D] hover:scale-110 transition-transform"
                    :class="{ 'animate-spin': isPlayingMusic }"
                    style="animation-duration: 5s;"
                    aria-label="Toggle Musik"
                >
                    <span v-if="isPlayingMusic">🎵</span>
                    <span v-else>🔇</span>
                </button>
            </div>

            <!-- Header Showcase -->
            <header class="text-center pt-6 pb-6 border-b border-[#D5DCD0]">
                <div class="text-2xl text-[#5E714D] mb-1">🌿</div>
                <div class="text-[10px] tracking-[0.3em] uppercase text-[#6A7B59] font-sans font-bold">
                    The Wedding Of
                </div>
                <h1 class="text-4xl sm:text-6xl font-serif italic text-stone-900 font-bold mt-2">
                    {{ weddingTitle }}
                </h1>
                <p class="text-xs sm:text-sm text-stone-500 font-sans mt-2 tracking-wide">
                    {{ weddingDate }}
                </p>

                <!-- Floating Scrapbook Polaroid Photo with Washi Tape Effect -->
                <div class="mt-8 relative max-w-sm mx-auto p-4 pb-12 bg-white rounded-xl shadow-xl border border-stone-200 rotate-1 hover:rotate-0 transition-transform duration-300">
                    <!-- Washi tape at top -->
                    <div class="absolute -top-3 left-1/2 -translate-x-1/2 w-24 h-6 bg-[#E3E8DC]/80 backdrop-blur-xs border border-stone-300/40 rounded-sm -rotate-2"></div>
                    
                    <div class="aspect-[4/5] rounded-lg overflow-hidden bg-stone-100">
                        <img :src="coverImg" :alt="weddingTitle" class="w-full h-full object-cover" />
                    </div>
                    <div class="absolute bottom-4 left-4 right-4 text-center">
                        <span class="font-serif italic text-sm text-stone-700">Forever Begins With Us</span>
                    </div>
                </div>
            </header>

            <!-- Holy Quote Card -->
            <section class="p-6 rounded-3xl bg-white border border-[#D5DCD0] shadow-sm text-center">
                <p class="text-sm sm:text-base italic text-stone-700 leading-relaxed max-w-md mx-auto">
                    {{ invitationData?.content?.quote || 'Di antara tanda-tanda kebesaran-Nya adalah Dia menciptakan pasangan-pasangan untukmu agar kamu merasa tenteram bersamanya.' }}
                </p>
                <div class="mt-2 text-[10px] uppercase tracking-widest text-[#5E714D] font-sans font-bold">
                    {{ invitationData?.content?.quoteSource || 'Holy Matrimony' }}
                </div>
            </section>

            <!-- Couple Section (Polaroid Style) -->
            <section class="space-y-8">
                <div class="text-center">
                    <div class="text-[9px] uppercase tracking-[0.25em] text-[#6A7B59] font-sans font-bold">
                        Bride & Groom
                    </div>
                    <h2 class="text-3xl font-serif italic text-stone-900 font-bold">Dua Jiwa yang Bersatu</h2>
                </div>

                <div class="grid sm:grid-cols-2 gap-6">
                    <!-- Groom -->
                    <div class="p-4 pb-8 bg-white rounded-2xl shadow-md border border-stone-200 -rotate-1 hover:rotate-0 transition-transform text-center">
                        <div class="aspect-square rounded-xl overflow-hidden mb-3 bg-stone-100">
                            <img :src="groom.photo || 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=400&q=80'" alt="Groom" class="w-full h-full object-cover" />
                        </div>
                        <h3 class="text-xl font-serif italic text-stone-900 font-bold">{{ groom.fullName || groom.nickname || 'Bumi Pratama' }}</h3>
                        <p class="text-xs text-stone-500 font-sans mt-1">{{ groom.parents || 'Putra Bpk. Suryadi & Ibu Desi' }}</p>
                    </div>

                    <!-- Bride -->
                    <div class="p-4 pb-8 bg-white rounded-2xl shadow-md border border-stone-200 rotate-1 hover:rotate-0 transition-transform text-center">
                        <div class="aspect-square rounded-xl overflow-hidden mb-3 bg-stone-100">
                            <img :src="bride.photo || 'https://images.unsplash.com/photo-1517841905240-472988babdf9?auto=format&fit=crop&w=400&q=80'" alt="Bride" class="w-full h-full object-cover" />
                        </div>
                        <h3 class="text-xl font-serif italic text-stone-900 font-bold">{{ bride.fullName || bride.nickname || 'Senja Larasati' }}</h3>
                        <p class="text-xs text-stone-500 font-sans mt-1">{{ bride.parents || 'Putri Bpk. Harun & Ibu Melly' }}</p>
                    </div>
                </div>
            </section>

            <!-- Events Schedule (Botanical Minimalist) -->
            <section class="space-y-6">
                <div class="text-center">
                    <div class="text-[9px] uppercase tracking-[0.25em] text-[#6A7B59] font-sans font-bold">
                        Special Events
                    </div>
                    <h2 class="text-3xl font-serif italic text-stone-900 font-bold">Waktu & Tempat</h2>
                </div>

                <div class="space-y-4">
                    <div
                        v-for="ev in events"
                        :key="ev.id"
                        class="p-6 rounded-3xl bg-white border border-[#D5DCD0] shadow-sm text-center"
                    >
                        <h3 class="text-xl font-serif italic text-stone-900 font-bold">{{ ev.name }}</h3>
                        <div class="my-2 py-1 inline-block text-xs font-sans text-[#5E714D] font-bold">
                            {{ safeFormatDisplayDate(ev.starts_at) }} • {{ safeFormatEventTime(ev.starts_at, ev.ends_at, ev.timezone) }}
                        </div>
                        <h4 class="text-sm font-semibold text-stone-800">{{ ev.venue_name }}</h4>
                        <p class="text-xs text-stone-500 mt-1 max-w-sm mx-auto font-sans leading-relaxed">{{ ev.address }}</p>
                        <div class="mt-4">
                            <a
                                :href="ev.maps_url || 'https://maps.google.com'"
                                target="_blank"
                                class="inline-flex items-center gap-1.5 px-5 py-2 rounded-full text-xs font-sans font-bold bg-[#EBF0E6] hover:bg-[#5E714D] text-[#5E714D] hover:text-white transition-colors"
                            >
                                <span>📍 Petunjuk Google Maps</span>
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Digital Gift -->
            <section v-if="digitalGift?.enabled" class="p-6 rounded-3xl bg-white border border-[#D5DCD0] shadow-sm text-center space-y-4">
                <div class="text-[9px] uppercase tracking-[0.25em] text-[#6A7B59] font-sans font-bold">
                    Gift For Couple
                </div>
                <h2 class="text-2xl font-serif italic text-stone-900 font-bold">Tanda Kasih</h2>

                <div class="grid sm:grid-cols-2 gap-3 max-w-md mx-auto">
                    <div
                        v-for="(acc, idx) in digitalGift.accounts"
                        :key="idx"
                        class="p-4 rounded-2xl bg-[#F7F5EE] border border-[#E3DFC9] text-left"
                    >
                        <div class="text-[9px] text-stone-400 font-sans uppercase">Transfer Rekening</div>
                        <div class="text-base font-bold text-stone-800">{{ acc.bank }}</div>
                        <div class="text-xs font-mono text-stone-900 mt-0.5">{{ acc.account_number }}</div>
                        <div class="text-[11px] text-stone-500">a.n. {{ acc.account_name }}</div>
                        <button
                            @click="copyBankNumber(acc.account_number, idx)"
                            class="mt-3 w-full py-1.5 rounded-lg text-[11px] font-sans font-semibold bg-white border border-stone-300 hover:bg-[#5E714D] hover:text-white transition-colors"
                        >
                            {{ copiedBankIndex === idx ? '✓ Disalin' : 'Salin Rekening' }}
                        </button>
                    </div>
                </div>
            </section>

            <!-- RSVP Form -->
            <section class="p-6 rounded-3xl bg-white border border-[#D5DCD0] shadow-sm space-y-4">
                <div class="text-center">
                    <div class="text-[9px] uppercase tracking-[0.25em] text-[#6A7B59] font-sans font-bold">
                        RSVP
                    </div>
                    <h2 class="text-2xl font-serif italic text-stone-900 font-bold">Konfirmasi Kehadiran</h2>
                </div>

                <form @submit.prevent="submitRsvp" class="space-y-3 max-w-md mx-auto font-sans">
                    <div v-if="rsvpSuccess" class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs text-center">
                        ✓ Terima kasih, konfirmasi Anda telah tercatat!
                    </div>

                    <div>
                        <label class="block text-xs text-stone-600 mb-1">Nama</label>
                        <input
                            v-model="rsvpForm.guest_name"
                            type="text"
                            required
                            placeholder="Nama Anda"
                            class="w-full px-3 py-2 rounded-xl bg-[#F7F5EE] border border-stone-200 text-stone-900 text-xs focus:outline-none focus:border-[#5E714D]"
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs text-stone-600 mb-1">Kehadiran</label>
                            <select
                                v-model="rsvpForm.status"
                                class="w-full px-2 py-2 rounded-xl bg-[#F7F5EE] border border-stone-200 text-stone-900 text-xs focus:outline-none focus:border-[#5E714D]"
                            >
                                <option value="attending">Hadir</option>
                                <option value="declined">Tidak Hadir</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-stone-600 mb-1">Jumlah</label>
                            <input
                                v-model.number="rsvpForm.attendees_count"
                                type="number"
                                min="1"
                                max="10"
                                class="w-full px-3 py-2 rounded-xl bg-[#F7F5EE] border border-stone-200 text-stone-900 text-xs focus:outline-none focus:border-[#5E714D]"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs text-stone-600 mb-1">Doa Restu</label>
                        <textarea
                            v-model="rsvpForm.wishes"
                            rows="2"
                            placeholder="Tuliskan ucapan..."
                            class="w-full px-3 py-2 rounded-xl bg-[#F7F5EE] border border-stone-200 text-stone-900 text-xs focus:outline-none focus:border-[#5E714D]"
                        ></textarea>
                    </div>

                    <button
                        type="submit"
                        :disabled="rsvpSubmitting"
                        class="w-full py-2.5 rounded-xl bg-[#5E714D] hover:bg-[#4E5E40] text-white font-bold text-xs uppercase tracking-wider transition-colors disabled:opacity-50"
                    >
                        {{ rsvpSubmitting ? 'Mengirim...' : 'Kirim Konfirmasi' }}
                    </button>
                </form>

                <!-- Guestbook Comments -->
                <div class="mt-6 pt-4 border-t border-stone-100 max-w-md mx-auto space-y-2 max-h-56 overflow-y-auto">
                    <div
                        v-for="gb in guestbook"
                        :key="gb.id"
                        class="p-3 rounded-xl bg-[#F7F5EE] text-left font-sans"
                    >
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-bold text-stone-800">{{ gb.guest_name }}</span>
                            <span class="text-stone-400 text-[10px]">{{ gb.created_at }}</span>
                        </div>
                        <p class="text-xs text-stone-600 mt-0.5">{{ gb.message }}</p>
                    </div>
                </div>
            </section>

            <!-- Footer -->
            <footer class="text-center py-6 border-t border-[#D5DCD0] text-xs text-stone-400 font-sans">
                <p>Terima kasih atas doa & restu Anda.</p>
                <div class="text-stone-700 font-serif italic text-base mt-1">{{ weddingTitle }}</div>
            </footer>

        </div>

    </div>
</template>

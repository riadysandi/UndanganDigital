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
    if (isOpening.value) return;
    isOpening.value = true;
    confetti({
        particleCount: 60,
        spread: 75,
        origin: { y: 0.6 },
        colors: ['#A02830', '#D4AF37', '#EDE6DB', '#8C6239']
    });

    setTimeout(() => {
        emit('openInvitation');
    }, 700);
};

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
    return d || 'Minggu, 6 Desember 2026';
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
const weddingTitle = computed(() => {
    if (groom.value?.nickname && bride.value?.nickname) {
        return `${groom.value.nickname} & ${bride.value.nickname}`;
    }
    return props.invitationData?.content?.weddingTitle || 'Mark & Bianca';
});
const weddingDate = computed(() => props.invitationData?.content?.weddingDateText || 'Minggu, 6 Desember 2026');
const coverImg = computed(() => props.invitationData?.content?.coverImage || 'https://images.unsplash.com/photo-1519741497674-611481863552?auto=format&fit=crop&w=1000&q=80');
</script>

<template>
    <div class="bianca-theme min-h-screen bg-[#FDFBF7] text-[#4A3B32] font-serif selection:bg-[#A02830] selection:text-white relative overflow-x-hidden">

        <!-- ======================================================== -->
        <!-- 1. AUTHENTIC BIANCA LINE ART COVER (Before Opened)       -->
        <!-- ======================================================== -->
        <section
            v-if="!isOpen"
            class="fixed inset-0 z-50 flex flex-col items-center justify-between p-4 sm:p-6 transition-all duration-700 overflow-hidden bg-[#FAF1E6]"
            :class="isOpening ? '-translate-y-full opacity-0 pointer-events-none' : 'translate-y-0 opacity-100'"
        >
            <!-- Authentic Line Art Layer -->
            <img
                src="/images/templates/bianca-lineart.png"
                alt="Bianca Bohemian Art"
                class="absolute inset-0 w-full h-full object-cover object-top pointer-events-none select-none z-0"
            />

            <!-- Cover Top Header -->
            <div class="pt-8 sm:pt-10 text-center relative z-10 animate-fade-in">
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#A02830]/10 text-[#A02830] text-[10px] tracking-widest font-sans font-bold uppercase mb-1 shadow-xs">
                    <span>👑</span> VVIP
                </div>
                <div class="text-xs sm:text-sm tracking-[0.2em] uppercase text-[#735A4B] font-sans font-medium">
                    The Wedding of
                </div>
                <h1 class="text-4xl sm:text-5xl font-serif text-[#A02830] italic font-bold tracking-tight mt-1 drop-shadow-xs">
                    {{ weddingTitle }}
                </h1>
                <p class="text-xs text-[#8C7667] font-sans tracking-wide mt-1">
                    {{ weddingDate }}
                </p>
            </div>

            <!-- Cover Center: Sweetheart Table Space -->
            <div class="my-auto pointer-events-none"></div>

            <!-- Cover Bottom: Greeting & Open Button -->
            <div class="w-full max-w-[320px] text-center pb-6 z-20 space-y-3">
                <div class="px-4 py-2.5 rounded-2xl bg-white/85 backdrop-blur-md border border-[#E8DFD8] shadow-md">
                    <p class="text-[10px] text-[#8C7667] font-sans uppercase tracking-widest">
                        Hai,
                    </p>
                    <p class="text-base font-bold text-[#4A3B32] font-serif truncate">
                        {{ guestName || 'Tamu Undangan Spesial' }}
                    </p>
                </div>

                <button
                    @click="handleOpen"
                    type="button"
                    class="w-full py-3.5 px-6 rounded-full bg-[#A02830] hover:bg-[#B8323B] text-white text-xs sm:text-sm font-sans tracking-[0.15em] uppercase font-bold shadow-xl shadow-[#A02830]/30 hover:scale-105 active:scale-95 transition-all duration-300 cursor-pointer flex items-center justify-center gap-2"
                >
                    <span>💌</span>
                    <span>{{ isOpening ? 'Membuka...' : 'Buka Undangan' }}</span>
                </button>
            </div>
        </section>

        <!-- ======================================================== -->
        <!-- 2. FULL BIANCA OPENED INVITATION CONTENT                 -->
        <!-- ======================================================== -->
        <main v-show="isOpen" class="max-w-2xl mx-auto px-4 sm:px-6 py-12 space-y-16 relative">

            <!-- Floating Music Player Button -->
            <div class="fixed bottom-6 right-6 z-40">
                <button
                    @click="emit('toggleMusic')"
                    type="button"
                    class="w-12 h-12 rounded-full bg-[#A02830] text-white border-2 border-white shadow-xl flex items-center justify-center text-lg hover:scale-110 active:scale-95 transition-all"
                    :class="{ 'animate-spin': isPlayingMusic }"
                    style="animation-duration: 4s;"
                    title="Putar / Hentikan Musik"
                >
                    <span>🎵</span>
                </button>
            </div>

            <!-- Opened Hero Section -->
            <header class="text-center pt-8 space-y-4">
                <div class="w-12 h-12 mx-auto rounded-full bg-[#F5EDE6] border border-[#E3D5C8] flex items-center justify-center text-xl text-[#A02830]">
                    🌿
                </div>
                <div class="text-[11px] tracking-[0.3em] uppercase text-[#8C7667] font-sans font-bold">
                    The Wedding Celebration
                </div>
                <h1 class="text-4xl sm:text-6xl font-serif text-[#A02830] italic font-bold leading-tight">
                    {{ weddingTitle }}
                </h1>
                <p class="text-sm sm:text-base text-[#735A4B] font-sans tracking-wide">
                    {{ weddingDate }}
                </p>

                <!-- Couple Photo in Warm Canvas Frame -->
                <div class="mt-6 relative max-w-sm mx-auto aspect-[3/4] rounded-3xl overflow-hidden border-8 border-white shadow-xl">
                    <img :src="coverImg" :alt="weddingTitle" class="w-full h-full object-cover" />
                    <div class="absolute inset-0 bg-gradient-to-t from-[#4A3B32]/70 via-transparent to-transparent flex items-end justify-center p-6">
                        <span class="text-xs tracking-widest uppercase text-white font-sans font-semibold">Together In Love</span>
                    </div>
                </div>
            </header>

            <!-- Holy Quote Card -->
            <section class="p-8 rounded-3xl bg-white border border-[#E8DFD8] shadow-md text-center space-y-3 relative">
                <div class="text-3xl text-[#A02830]">“</div>
                <p class="text-base sm:text-lg italic text-[#4A3B32] leading-relaxed max-w-md mx-auto">
                    {{ invitationData?.content?.quote || 'Cinta sejati bukanlah menemukan orang yang sempurna, melainkan melihat orang yang tidak sempurna dengan cara yang sempurna.' }}
                </p>
                <div class="text-xs tracking-widest text-[#8C7667] font-sans font-bold uppercase">
                    {{ invitationData?.content?.quoteSource || 'Janji Suci Dua Insan' }}
                </div>
            </section>

            <!-- Mempelai Pengantin -->
            <section class="space-y-8">
                <div class="text-center">
                    <div class="text-[10px] tracking-[0.3em] uppercase text-[#8C7667] font-sans font-bold">The Happy Couple</div>
                    <h2 class="text-3xl font-serif text-[#4A3B32] italic mt-1">Mempelai yang Berbahagia</h2>
                </div>

                <div class="grid sm:grid-cols-2 gap-6">
                    <!-- Groom -->
                    <div class="p-6 rounded-3xl bg-white border border-[#E8DFD8] shadow-sm text-center space-y-3">
                        <div class="w-32 h-32 mx-auto rounded-full overflow-hidden border-4 border-[#F5EDE6] shadow-inner">
                            <img :src="groom.photo || 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=400&q=80'" alt="Groom" class="w-full h-full object-cover" />
                        </div>
                        <h3 class="text-2xl font-serif text-[#4A3B32] font-bold">{{ groom.fullName || groom.nickname || 'Mark Pratama' }}</h3>
                        <p class="text-xs text-[#8C7667] font-sans leading-relaxed">{{ groom.parents || 'Putra sulung dari Bpk. Irwan & Ibu Maria' }}</p>
                        <div v-if="groom.instagram" class="pt-1 text-xs text-[#A02830] font-sans font-semibold">
                            {{ groom.instagram }}
                        </div>
                    </div>

                    <!-- Bride -->
                    <div class="p-6 rounded-3xl bg-white border border-[#E8DFD8] shadow-sm text-center space-y-3">
                        <div class="w-32 h-32 mx-auto rounded-full overflow-hidden border-4 border-[#F5EDE6] shadow-inner">
                            <img :src="bride.photo || 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=400&q=80'" alt="Bride" class="w-full h-full object-cover" />
                        </div>
                        <h3 class="text-2xl font-serif text-[#4A3B32] font-bold">{{ bride.fullName || bride.nickname || 'Bianca Aurelia' }}</h3>
                        <p class="text-xs text-[#8C7667] font-sans leading-relaxed">{{ bride.parents || 'Putri kedua dari Bpk. David & Ibu Cynthia' }}</p>
                        <div v-if="bride.instagram" class="pt-1 text-xs text-[#A02830] font-sans font-semibold">
                            {{ bride.instagram }}
                        </div>
                    </div>
                </div>
            </section>

            <!-- Countdown Timer -->
            <section class="p-8 rounded-3xl bg-[#F5EDE6] border border-[#E3D5C8] text-center shadow-md space-y-4">
                <div class="text-[10px] tracking-[0.3em] uppercase text-[#8C7667] font-sans font-bold">Menghitung Hari Bahagia</div>
                <div class="grid grid-cols-4 gap-2 sm:gap-4 max-w-sm mx-auto">
                    <div class="p-3 rounded-2xl bg-white shadow-xs">
                        <span class="block text-2xl sm:text-3xl font-bold font-serif text-[#A02830]">{{ timeLeft.days }}</span>
                        <span class="text-[9px] uppercase tracking-wider text-[#8C7667]">Hari</span>
                    </div>
                    <div class="p-3 rounded-2xl bg-white shadow-xs">
                        <span class="block text-2xl sm:text-3xl font-bold font-serif text-[#A02830]">{{ timeLeft.hours }}</span>
                        <span class="text-[9px] uppercase tracking-wider text-[#8C7667]">Jam</span>
                    </div>
                    <div class="p-3 rounded-2xl bg-white shadow-xs">
                        <span class="block text-2xl sm:text-3xl font-bold font-serif text-[#A02830]">{{ timeLeft.minutes }}</span>
                        <span class="text-[9px] uppercase tracking-wider text-[#8C7667]">Menit</span>
                    </div>
                    <div class="p-3 rounded-2xl bg-white shadow-xs">
                        <span class="block text-2xl sm:text-3xl font-bold font-serif text-[#A02830]">{{ timeLeft.seconds }}</span>
                        <span class="text-[9px] uppercase tracking-wider text-[#8C7667]">Detik</span>
                    </div>
                </div>
            </section>

            <!-- Rangkaian Acara -->
            <section class="space-y-6">
                <div class="text-center">
                    <div class="text-[10px] tracking-[0.3em] uppercase text-[#8C7667] font-sans font-bold">Wedding Itinerary</div>
                    <h2 class="text-3xl font-serif text-[#4A3B32] italic mt-1">Rangkaian Acara</h2>
                </div>

                <div class="space-y-6">
                    <div
                        v-for="ev in events"
                        :key="ev.id"
                        class="p-8 rounded-3xl bg-white border border-[#E8DFD8] shadow-md text-center space-y-4"
                    >
                        <div class="w-10 h-10 mx-auto rounded-full bg-[#F5EDE6] border border-[#E3D5C8] flex items-center justify-center text-[#A02830]">
                            🌿
                        </div>
                        <h3 class="text-2xl font-serif text-[#4A3B32] font-bold">{{ ev.name }}</h3>
                        <div class="py-2 border-y border-[#F0E6DE] inline-block px-6">
                            <p class="text-sm font-bold text-[#A02830] font-sans">{{ safeFormatDisplayDate(ev.starts_at) }}</p>
                            <p class="text-xs text-[#8C7667] font-sans mt-0.5">{{ safeFormatEventTime(ev.starts_at, ev.ends_at, ev.timezone) }}</p>
                        </div>
                        <div>
                            <h4 class="text-base font-bold text-[#4A3B32]">{{ ev.venue_name }}</h4>
                            <p class="text-xs text-[#8C7667] font-sans mt-1 max-w-sm mx-auto leading-relaxed">{{ ev.address }}</p>
                        </div>
                        <div class="pt-2">
                            <a
                                :href="ev.maps_url || 'https://maps.google.com'"
                                target="_blank"
                                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-full text-xs font-sans uppercase tracking-widest font-bold bg-[#A02830] text-white hover:bg-[#B8323B] shadow-md transition-all hover:scale-105"
                            >
                                <span>📍 Lokasi Acara (Google Maps)</span>
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Digital Gift -->
            <section v-if="digitalGift?.enabled" class="p-8 rounded-3xl bg-white border border-[#E8DFD8] shadow-md text-center space-y-6">
                <div>
                    <div class="text-[10px] tracking-[0.3em] uppercase text-[#8C7667] font-sans font-bold">Wedding Gift</div>
                    <h2 class="text-3xl font-serif text-[#4A3B32] italic mt-1">Tanda Kasih & Hadiah Digital</h2>
                    <p class="text-xs text-[#8C7667] font-sans max-w-md mx-auto mt-2 leading-relaxed">
                        Kehadiran dan doa restu Anda adalah hadiah terindah. Namun jika ingin memberikan tanda kasih secara digital:
                    </p>
                </div>

                <div class="space-y-4 max-w-md mx-auto">
                    <div
                        v-for="(acc, idx) in (digitalGift?.accounts || [])"
                        :key="idx"
                        class="p-5 rounded-2xl bg-[#FDFBF7] border border-[#E8DFD8] text-left flex items-center justify-between"
                    >
                        <div>
                            <span class="text-[10px] uppercase font-bold tracking-wider text-[#8C7667]">{{ acc.bank_name || 'Bank' }}</span>
                            <div class="text-base font-mono font-bold text-[#4A3B32] mt-0.5">{{ acc.account_number }}</div>
                            <div class="text-xs text-[#8C7667]">a.n {{ acc.holder_name }}</div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button
                                v-if="acc.qris_image_url"
                                @click="openQris(acc)"
                                type="button"
                                class="px-3 py-1.5 rounded-lg bg-white border border-[#E8DFD8] text-xs font-sans font-semibold text-[#A02830] hover:bg-[#F5EDE6]"
                            >
                                QRIS
                            </button>
                            <button
                                @click="copyBankNumber(acc.account_number, idx)"
                                type="button"
                                class="px-3 py-1.5 rounded-lg bg-[#A02830] text-white text-xs font-sans font-semibold hover:bg-[#B8323B]"
                            >
                                {{ copiedBankIndex === idx ? '✓ Tersalin' : 'Salin' }}
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <!-- RSVP Form & Wishes -->
            <section class="p-8 rounded-3xl bg-white border border-[#E8DFD8] shadow-md space-y-6">
                <div class="text-center">
                    <div class="text-[10px] tracking-[0.3em] uppercase text-[#8C7667] font-sans font-bold">RSVP & Wishes</div>
                    <h2 class="text-3xl font-serif text-[#4A3B32] italic mt-1">Konfirmasi Kehadiran & Doa Restu</h2>
                </div>

                <form @submit.prevent="submitRsvp" class="space-y-4 max-w-lg mx-auto">
                    <div>
                        <label class="block text-xs font-bold text-[#4A3B32] font-sans mb-1">Nama Lengkap</label>
                        <input
                            v-model="rsvpForm.guest_name"
                            type="text"
                            required
                            placeholder="Tuliskan nama Anda"
                            class="w-full px-4 py-2.5 rounded-xl border border-[#E8DFD8] bg-[#FDFBF7] text-stone-900 text-xs font-sans focus:outline-none focus:ring-2 focus:ring-[#A02830]"
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-[#4A3B32] font-sans mb-1">Kehadiran</label>
                            <select
                                v-model="rsvpForm.status"
                                class="w-full px-4 py-2.5 rounded-xl border border-[#E8DFD8] bg-[#FDFBF7] text-stone-900 text-xs font-sans focus:outline-none focus:ring-2 focus:ring-[#A02830]"
                            >
                                <option value="attending">Hadir</option>
                                <option value="tentative">Masih Ragu</option>
                                <option value="declined">Tidak Hadir</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-[#4A3B32] font-sans mb-1">Jumlah Tamu</label>
                            <input
                                v-model.number="rsvpForm.attendees_count"
                                type="number"
                                min="1"
                                max="10"
                                class="w-full px-4 py-2.5 rounded-xl border border-[#E8DFD8] bg-[#FDFBF7] text-stone-900 text-xs font-sans focus:outline-none focus:ring-2 focus:ring-[#A02830]"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-[#4A3B32] font-sans mb-1">Ucapan & Doa Restu</label>
                        <textarea
                            v-model="rsvpForm.wishes"
                            rows="3"
                            placeholder="Tuliskan doa restu untuk kedua mempelai..."
                            class="w-full px-4 py-2.5 rounded-xl border border-[#E8DFD8] bg-[#FDFBF7] text-stone-900 text-xs font-sans focus:outline-none focus:ring-2 focus:ring-[#A02830]"
                        ></textarea>
                    </div>

                    <button
                        type="submit"
                        :disabled="rsvpSubmitting"
                        class="w-full py-3 rounded-xl bg-[#A02830] text-white text-xs font-sans uppercase tracking-widest font-bold hover:bg-[#B8323B] shadow-md transition-all cursor-pointer"
                    >
                        {{ rsvpSubmitting ? 'Mengirim...' : 'Kirim Konfirmasi Kehadiran' }}
                    </button>
                    <p v-if="rsvpSuccess" class="text-xs text-emerald-700 font-semibold text-center">
                        ✓ Terima kasih, konfirmasi Anda telah tersimpan!
                    </p>
                </form>

                <!-- Guestbook List -->
                <div class="pt-6 border-t border-[#F0E6DE] space-y-3 max-w-lg mx-auto">
                    <h4 class="text-xs font-bold text-[#4A3B32] font-sans uppercase tracking-wider text-center">Buku Tamu & Ucapan</h4>
                    <div class="max-h-60 overflow-y-auto space-y-2 pr-1">
                        <div
                            v-for="entry in guestbook"
                            :key="entry.id"
                            class="p-3.5 rounded-xl bg-[#FDFBF7] border border-[#E8DFD8] text-left"
                        >
                            <div class="flex items-center justify-between text-[11px] font-bold text-[#4A3B32]">
                                <span>{{ entry.guest_name }}</span>
                                <span class="text-[9px] text-[#8C7667] font-normal">{{ entry.created_at }}</span>
                            </div>
                            <p class="text-xs text-[#4A3B32] mt-1 italic leading-relaxed">"{{ entry.message }}"</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Footer -->
            <footer class="text-center pt-8 pb-12 space-y-3">
                <div class="w-10 h-10 mx-auto rounded-full bg-[#F5EDE6] border border-[#E3D5C8] flex items-center justify-center text-lg text-[#A02830]">
                    🌿
                </div>
                <h3 class="text-2xl font-serif text-[#4A3B32] italic font-bold">Terima Kasih</h3>
                <p class="text-xs text-[#8C7667] font-sans max-w-xs mx-auto">
                    Kehadiran dan doa restu Bapak/Ibu/Saudara/i sangat berarti dalam mengawali lembaran hidup baru kami.
                </p>
                <div class="pt-4 text-[10px] text-[#8C7667]/60 font-sans tracking-widest uppercase">
                    Undangan Digital • Bianca Bohemian Serenity Edition
                </div>
            </footer>

        </main>

    </div>
</template>

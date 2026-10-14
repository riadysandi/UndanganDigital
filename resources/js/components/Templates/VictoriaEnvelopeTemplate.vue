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
        particleCount: 65,
        spread: 70,
        origin: { y: 0.6 },
        colors: ['#D4AF37', '#9E2A2B', '#FFF0F5', '#800020']
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
    return d || 'Sabtu, 28 Desember 2026';
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
    return props.invitationData?.content?.weddingTitle || 'Theodore & Victoria';
});
const weddingDate = computed(() => props.invitationData?.content?.weddingDateText || 'Sabtu, 19 Desember 2026');
const coverImg = computed(() => props.invitationData?.content?.coverImage || 'https://images.unsplash.com/photo-1519741497674-611481863552?auto=format&fit=crop&w=1000&q=80');
</script>

<template>
    <div class="victoria-theme min-h-screen bg-[#FBF3F5] text-[#3D2529] font-serif selection:bg-[#9E2A2B] selection:text-white relative overflow-x-hidden">

        <!-- Top Lace Trim Banner -->
        <div class="fixed top-0 inset-x-0 z-40 pointer-events-none opacity-90 h-8 sm:h-12 bg-repeat-x bg-contain" style="background-image: url('/images/templates/victoria/Orn-16.png');"></div>

        <!-- ======================================================== -->
        <!-- 1. AUTHENTIC VICTORIA COVER EXPERIENCE (Before Opened)   -->
        <!-- ======================================================== -->
        <section
            v-if="!isOpen"
            class="fixed inset-0 z-50 flex flex-col items-center justify-between p-4 sm:p-6 transition-all duration-700 overflow-hidden"
            :class="isOpening ? '-translate-y-full opacity-0 pointer-events-none' : 'translate-y-0 opacity-100'"
            style="background: repeating-linear-gradient(90deg, #F8E5EB 0px, #F8E5EB 38px, #FCF1F4 38px, #FCF1F4 76px);"
        >
            <!-- Background Decorative Lace Heart -->
            <div class="absolute -top-12 -right-12 w-64 h-64 opacity-10 pointer-events-none">
                <img src="/images/templates/victoria/Orn-08.png" alt="Lace" class="w-full h-full object-contain" />
            </div>

            <!-- Cover Top: VVIP Crown & Title -->
            <div class="pt-8 sm:pt-10 text-center relative z-10 animate-fade-in">
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#5C1D27]/10 text-[#5C1D27] text-[10px] tracking-widest font-sans font-bold uppercase mb-2">
                    <span>👑</span> VVIP GUEST INVITATION
                </div>
                <h1 class="text-3xl sm:text-4xl font-serif text-[#4A1521] tracking-wider uppercase font-semibold leading-tight">
                    {{ weddingTitle }}
                </h1>
                <p class="text-xs sm:text-sm text-[#7D4F59] font-sans tracking-wide mt-1">
                    {{ weddingDate }}
                </p>
            </div>

            <!-- Cover Center: Chateau Portrait in Rich Burgundy Card -->
            <div class="relative w-full max-w-[320px] sm:max-w-[360px] my-auto z-10">
                <div class="relative rounded-2xl bg-[#4A1521] p-3 shadow-2xl shadow-[#4A1521]/30 text-center border border-[#7D2938]">
                    <!-- Couple Photo -->
                    <div class="aspect-[4/3] rounded-xl overflow-hidden bg-stone-200 shadow-inner relative">
                        <img
                            :src="coverImg"
                            alt="Theodore & Victoria"
                            class="w-full h-full object-cover"
                        />
                        <!-- Subtle lace corner stamp -->
                        <div class="absolute bottom-1 right-1 px-2 py-0.5 rounded bg-black/40 text-[9px] text-pink-100 font-sans tracking-widest uppercase">
                            Chateau Edition
                        </div>
                    </div>
                    <div class="pt-3 pb-1 text-center">
                        <div class="text-[13px] tracking-[0.25em] uppercase text-pink-100 font-serif font-bold">
                            {{ weddingTitle }}
                        </div>
                    </div>
                </div>

                <!-- Floating Hanging Calla Lily on Left -->
                <div class="absolute -bottom-8 -left-8 w-24 sm:w-28 pointer-events-none drop-shadow-xl z-20">
                    <img src="/images/templates/victoria/Orn-04.png" alt="Calla Lily" class="w-full h-auto object-contain" />
                </div>
                <!-- Floating Amaranthus on Right -->
                <div class="absolute -top-10 -right-6 w-20 sm:w-24 pointer-events-none drop-shadow-xl z-20">
                    <img src="/images/templates/victoria/Orn-12.png" alt="Amaranthus" class="w-full h-auto object-contain" />
                </div>
            </div>

            <!-- Cover Bottom: Pennant Card Pocket & Open Button -->
            <div class="w-full max-w-[340px] text-center pb-4 z-20 space-y-3">
                <div class="px-4 py-2.5 rounded-2xl bg-white/80 backdrop-blur-md border border-pink-200/80 shadow-md">
                    <p class="text-[10px] text-[#7D4F59] font-sans uppercase tracking-widest">
                        Kepada Yth. Bpk/Ibu/Saudara/i:
                    </p>
                    <p class="text-sm font-bold text-[#4A1521] font-serif mt-0.5 truncate">
                        {{ guestName || 'Tamu Undangan Spesial' }}
                    </p>
                </div>

                <button
                    @click="handleOpen"
                    type="button"
                    class="w-full py-3.5 px-6 rounded-full bg-gradient-to-r from-[#5C1D27] via-[#7B2433] to-[#5C1D27] text-white text-xs sm:text-sm font-serif tracking-[0.2em] uppercase font-bold shadow-xl shadow-[#5C1D27]/30 hover:scale-105 active:scale-95 transition-all duration-300 cursor-pointer flex items-center justify-center gap-2"
                >
                    <span>💌</span>
                    <span>{{ isOpening ? 'Membuka Undangan...' : 'Buka Undangan' }}</span>
                </button>
            </div>
        </section>

        <!-- ======================================================== -->
        <!-- 2. FULL VICTORIA OPENED INVITATION CONTENT               -->
        <!-- ======================================================== -->
        <main v-show="isOpen" class="max-w-2xl mx-auto px-4 sm:px-6 py-12 space-y-16 relative">

            <!-- Floating Music Player Button -->
            <div class="fixed bottom-6 right-6 z-40">
                <button
                    @click="emit('toggleMusic')"
                    type="button"
                    class="w-12 h-12 rounded-full bg-[#4A1521] text-pink-100 border-2 border-pink-200 shadow-xl flex items-center justify-center text-lg hover:scale-110 active:scale-95 transition-all"
                    :class="{ 'animate-spin': isPlayingMusic }"
                    style="animation-duration: 4s;"
                    title="Putar / Hentikan Musik"
                >
                    <span>🎵</span>
                </button>
            </div>

            <!-- Opened Hero Section -->
            <header class="text-center pt-8 space-y-4">
                <div class="w-20 h-20 mx-auto rounded-full bg-white/70 border border-pink-300 shadow-md p-2 flex items-center justify-center">
                    <img src="/images/templates/victoria/Orn-08.png" alt="Heart Lace" class="w-full h-full object-contain" />
                </div>
                <div class="text-[11px] tracking-[0.3em] uppercase text-[#7D4F59] font-sans font-bold">
                    The Wedding Celebration Of
                </div>
                <h1 class="text-4xl sm:text-6xl font-serif text-[#4A1521] italic leading-tight">
                    {{ weddingTitle }}
                </h1>
                <p class="text-sm sm:text-base text-[#7D4F59] font-sans tracking-wide">
                    {{ weddingDate }}
                </p>

                <!-- Hero Photo with Victorian Frame -->
                <div class="mt-6 relative max-w-sm mx-auto aspect-[3/4] rounded-3xl overflow-hidden border-4 border-white shadow-2xl">
                    <img :src="coverImg" :alt="weddingTitle" class="w-full h-full object-cover" />
                    <div class="absolute inset-0 bg-gradient-to-t from-[#4A1521]/70 via-transparent to-transparent flex items-end justify-center p-6">
                        <span class="text-xs tracking-widest uppercase text-pink-100 font-sans font-bold">Forever & Always</span>
                    </div>
                </div>
            </header>

            <!-- Holy Quote Card -->
            <section class="p-8 rounded-3xl bg-white/90 border border-pink-200 shadow-lg text-center space-y-3 relative overflow-hidden">
                <div class="text-3xl text-[#5C1D27]">“</div>
                <p class="text-base sm:text-lg italic text-[#4A1521] leading-relaxed max-w-md mx-auto">
                    {{ invitationData?.content?.quote || 'Dan di antara tanda-tanda kebesaran-Nya ialah Dia menciptakan pasangan-pasangan untukmu dari jenismu sendiri, agar kamu cenderung dan merasa tenteram kepadanya.' }}
                </p>
                <div class="text-xs tracking-widest text-[#7D4F59] font-sans font-bold uppercase">
                    {{ invitationData?.content?.quoteSource || 'Ar-Rum: 21' }}
                </div>
            </section>

            <!-- Bride & Groom Profiles -->
            <section class="space-y-8">
                <div class="text-center">
                    <div class="text-[10px] tracking-[0.3em] uppercase text-[#7D4F59] font-sans font-bold">The Happy Couple</div>
                    <h2 class="text-3xl font-serif text-[#4A1521] italic mt-1">Mempelai yang Berbahagia</h2>
                </div>

                <div class="grid sm:grid-cols-2 gap-6">
                    <!-- Groom -->
                    <div class="p-6 rounded-3xl bg-white/90 border border-pink-200 shadow-md text-center space-y-3 relative">
                        <div class="w-32 h-32 mx-auto rounded-full overflow-hidden border-4 border-pink-100 shadow-inner">
                            <img :src="groom.photo || 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=400&q=80'" alt="Groom" class="w-full h-full object-cover" />
                        </div>
                        <h3 class="text-2xl font-serif text-[#4A1521] font-bold">{{ groom.fullName || groom.nickname || 'Theodore Alexander' }}</h3>
                        <p class="text-xs text-[#7D4F59] font-sans leading-relaxed">{{ groom.parents || 'Putra pertama dari Bpk. Alexander & Ibu Diana' }}</p>
                        <div v-if="groom.instagram" class="pt-1 text-xs text-[#5C1D27] font-sans font-semibold">
                            {{ groom.instagram }}
                        </div>
                    </div>

                    <!-- Bride -->
                    <div class="p-6 rounded-3xl bg-white/90 border border-pink-200 shadow-md text-center space-y-3 relative">
                        <div class="w-32 h-32 mx-auto rounded-full overflow-hidden border-4 border-pink-100 shadow-inner">
                            <img :src="bride.photo || 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=400&q=80'" alt="Bride" class="w-full h-full object-cover" />
                        </div>
                        <h3 class="text-2xl font-serif text-[#4A1521] font-bold">{{ bride.fullName || bride.nickname || 'Victoria Clarissa' }}</h3>
                        <p class="text-xs text-[#7D4F59] font-sans leading-relaxed">{{ bride.parents || 'Putri bungsu dari Bpk. Christian & Ibu Elizabeth' }}</p>
                        <div v-if="bride.instagram" class="pt-1 text-xs text-[#5C1D27] font-sans font-semibold">
                            {{ bride.instagram }}
                        </div>
                    </div>
                </div>
            </section>

            <!-- Countdown Timer -->
            <section class="p-8 rounded-3xl bg-gradient-to-br from-[#4A1521] to-[#691E2E] text-white text-center shadow-xl space-y-4">
                <div class="text-[10px] tracking-[0.3em] uppercase text-pink-200 font-sans font-bold">Menghitung Hari Bahagia</div>
                <div class="grid grid-cols-4 gap-2 sm:gap-4 max-w-sm mx-auto">
                    <div class="p-3 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20">
                        <span class="block text-2xl sm:text-3xl font-bold font-serif">{{ timeLeft.days }}</span>
                        <span class="text-[9px] uppercase tracking-wider text-pink-200">Hari</span>
                    </div>
                    <div class="p-3 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20">
                        <span class="block text-2xl sm:text-3xl font-bold font-serif">{{ timeLeft.hours }}</span>
                        <span class="text-[9px] uppercase tracking-wider text-pink-200">Jam</span>
                    </div>
                    <div class="p-3 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20">
                        <span class="block text-2xl sm:text-3xl font-bold font-serif">{{ timeLeft.minutes }}</span>
                        <span class="text-[9px] uppercase tracking-wider text-pink-200">Menit</span>
                    </div>
                    <div class="p-3 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20">
                        <span class="block text-2xl sm:text-3xl font-bold font-serif">{{ timeLeft.seconds }}</span>
                        <span class="text-[9px] uppercase tracking-wider text-pink-200">Detik</span>
                    </div>
                </div>
            </section>

            <!-- Event Agenda Cards -->
            <section class="space-y-6">
                <div class="text-center">
                    <div class="text-[10px] tracking-[0.3em] uppercase text-[#7D4F59] font-sans font-bold">Wedding Timeline</div>
                    <h2 class="text-3xl font-serif text-[#4A1521] italic mt-1">Rangkaian Acara</h2>
                </div>

                <div class="space-y-6">
                    <div
                        v-for="ev in events"
                        :key="ev.id"
                        class="p-8 rounded-3xl bg-white/95 border border-pink-200 shadow-md text-center space-y-4"
                    >
                        <div class="w-10 h-10 mx-auto rounded-full bg-pink-50 border border-pink-200 flex items-center justify-center text-[#5C1D27]">
                            📅
                        </div>
                        <h3 class="text-2xl font-serif text-[#4A1521] font-bold">{{ ev.name }}</h3>
                        <div class="py-2 border-y border-pink-100 inline-block px-6">
                            <p class="text-sm font-bold text-[#5C1D27] font-sans">{{ safeFormatDisplayDate(ev.starts_at) }}</p>
                            <p class="text-xs text-[#7D4F59] font-sans mt-0.5">{{ safeFormatEventTime(ev.starts_at, ev.ends_at, ev.timezone) }}</p>
                        </div>
                        <div>
                            <h4 class="text-base font-bold text-[#4A1521]">{{ ev.venue_name }}</h4>
                            <p class="text-xs text-[#7D4F59] font-sans mt-1 max-w-sm mx-auto leading-relaxed">{{ ev.address }}</p>
                        </div>
                        <div class="pt-2">
                            <a
                                :href="ev.maps_url || 'https://maps.google.com'"
                                target="_blank"
                                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-full text-xs font-sans uppercase tracking-widest font-bold bg-[#5C1D27] text-white hover:bg-[#7B2433] shadow-md transition-all hover:scale-105"
                            >
                                <span>📍 Buka di Google Maps</span>
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Digital Gift / Bank Registry -->
            <section v-if="digitalGift?.enabled" class="p-8 rounded-3xl bg-white/95 border border-pink-200 shadow-md text-center space-y-6">
                <div>
                    <div class="text-[10px] tracking-[0.3em] uppercase text-[#7D4F59] font-sans font-bold">Wedding Gift</div>
                    <h2 class="text-3xl font-serif text-[#4A1521] italic mt-1">Tanda Kasih & Doa Restu</h2>
                    <p class="text-xs text-[#7D4F59] font-sans max-w-md mx-auto mt-2 leading-relaxed">
                        Doa restu Anda merupakan karunia terindah bagi kami. Namun jika ingin memberikan tanda kasih secara digital, Anda dapat melalui:
                    </p>
                </div>

                <div class="space-y-4 max-w-md mx-auto">
                    <div
                        v-for="(acc, idx) in (digitalGift?.accounts || [])"
                        :key="idx"
                        class="p-5 rounded-2xl bg-pink-50/60 border border-pink-200 text-left flex items-center justify-between"
                    >
                        <div>
                            <span class="text-[10px] uppercase font-bold tracking-wider text-[#7D4F59]">{{ acc.bank_name || 'Bank' }}</span>
                            <div class="text-base font-mono font-bold text-[#4A1521] mt-0.5">{{ acc.account_number }}</div>
                            <div class="text-xs text-[#7D4F59]">a.n {{ acc.holder_name }}</div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button
                                v-if="acc.qris_image_url"
                                @click="openQris(acc)"
                                type="button"
                                class="px-3 py-1.5 rounded-lg bg-white border border-pink-300 text-xs font-sans font-semibold text-[#5C1D27] hover:bg-pink-100"
                            >
                                QRIS
                            </button>
                            <button
                                @click="copyBankNumber(acc.account_number, idx)"
                                type="button"
                                class="px-3 py-1.5 rounded-lg bg-[#5C1D27] text-white text-xs font-sans font-semibold hover:bg-[#7B2433]"
                            >
                                {{ copiedBankIndex === idx ? '✓ Tersalin' : 'Salin' }}
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <!-- RSVP Form & Wishes -->
            <section class="p-8 rounded-3xl bg-white/95 border border-pink-200 shadow-md space-y-6">
                <div class="text-center">
                    <div class="text-[10px] tracking-[0.3em] uppercase text-[#7D4F59] font-sans font-bold">RSVP & Wishes</div>
                    <h2 class="text-3xl font-serif text-[#4A1521] italic mt-1">Konfirmasi Kehadiran & Ucapan</h2>
                </div>

                <!-- Form -->
                <form @submit.prevent="submitRsvp" class="space-y-4 max-w-lg mx-auto">
                    <div>
                        <label class="block text-xs font-bold text-[#4A1521] font-sans mb-1">Nama Lengkap</label>
                        <input
                            v-model="rsvpForm.guest_name"
                            type="text"
                            required
                            placeholder="Tuliskan nama Anda"
                            class="w-full px-4 py-2.5 rounded-xl border border-pink-200 bg-pink-50/30 text-stone-900 text-xs font-sans focus:outline-none focus:ring-2 focus:ring-[#5C1D27]"
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-[#4A1521] font-sans mb-1">Kehadiran</label>
                            <select
                                v-model="rsvpForm.status"
                                class="w-full px-4 py-2.5 rounded-xl border border-pink-200 bg-pink-50/30 text-stone-900 text-xs font-sans focus:outline-none focus:ring-2 focus:ring-[#5C1D27]"
                            >
                                <option value="attending">Hadir</option>
                                <option value="tentative">Masih Ragu</option>
                                <option value="declined">Tidak Hadir</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-[#4A1521] font-sans mb-1">Jumlah Tamu</label>
                            <input
                                v-model.number="rsvpForm.attendees_count"
                                type="number"
                                min="1"
                                max="10"
                                class="w-full px-4 py-2.5 rounded-xl border border-pink-200 bg-pink-50/30 text-stone-900 text-xs font-sans focus:outline-none focus:ring-2 focus:ring-[#5C1D27]"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-[#4A1521] font-sans mb-1">Ucapan & Doa Restu</label>
                        <textarea
                            v-model="rsvpForm.wishes"
                            rows="3"
                            placeholder="Tuliskan doa restu untuk kedua mempelai..."
                            class="w-full px-4 py-2.5 rounded-xl border border-pink-200 bg-pink-50/30 text-stone-900 text-xs font-sans focus:outline-none focus:ring-2 focus:ring-[#5C1D27]"
                        ></textarea>
                    </div>

                    <button
                        type="submit"
                        :disabled="rsvpSubmitting"
                        class="w-full py-3 rounded-xl bg-[#5C1D27] text-white text-xs font-sans uppercase tracking-widest font-bold hover:bg-[#7B2433] shadow-md transition-all cursor-pointer"
                    >
                        {{ rsvpSubmitting ? 'Mengirim Konfirmasi...' : 'Kirim Konfirmasi Kehadiran' }}
                    </button>
                    <p v-if="rsvpSuccess" class="text-xs text-emerald-700 font-semibold text-center">
                        ✓ Terima kasih, konfirmasi Anda telah tersimpan!
                    </p>
                </form>

                <!-- Guestbook List -->
                <div class="pt-6 border-t border-pink-100 space-y-3 max-w-lg mx-auto">
                    <h4 class="text-xs font-bold text-[#4A1521] font-sans uppercase tracking-wider text-center">Ucapan dari Para Sahabat</h4>
                    <div class="max-h-60 overflow-y-auto space-y-2 pr-1">
                        <div
                            v-for="entry in guestbook"
                            :key="entry.id"
                            class="p-3.5 rounded-xl bg-pink-50/50 border border-pink-100 text-left"
                        >
                            <div class="flex items-center justify-between text-[11px] font-bold text-[#4A1521]">
                                <span>{{ entry.guest_name }}</span>
                                <span class="text-[9px] text-[#7D4F59] font-normal">{{ entry.created_at }}</span>
                            </div>
                            <p class="text-xs text-[#4A1521] mt-1 italic leading-relaxed">"{{ entry.message }}"</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Footer Closing -->
            <footer class="text-center pt-8 pb-12 space-y-3">
                <div class="w-12 h-12 mx-auto rounded-full bg-white/70 border border-pink-300 shadow-sm p-1.5 flex items-center justify-center">
                    <img src="/images/templates/victoria/Orn-08.png" alt="Heart Lace" class="w-full h-full object-contain" />
                </div>
                <h3 class="text-2xl font-serif text-[#4A1521] italic font-bold">Terima Kasih</h3>
                <p class="text-xs text-[#7D4F59] font-sans max-w-xs mx-auto">
                    Merupakan suatu kehormatan dan kebahagiaan bagi kami apabila Bapak/Ibu/Saudara/i berkenan hadir dan memberikan doa restu.
                </p>
                <div class="pt-4 text-[10px] text-pink-400 font-sans tracking-widest uppercase">
                    Undangan Digital • Exclusive Chateau Edition
                </div>
            </footer>

        </main>

    </div>
</template>

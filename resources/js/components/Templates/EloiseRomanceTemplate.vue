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
        spread: 80,
        origin: { y: 0.7 },
        colors: ['#D4AF37', '#3D4A39', '#FFFFFF', '#E6C687']
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
    return d || 'Sabtu, 19 Desember 2026';
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
    return props.invitationData?.content?.weddingTitle || 'Anna & Jeremia';
});
const weddingDate = computed(() => props.invitationData?.content?.weddingDateText || 'Sabtu, 19 Desember 2026');
const coverImg = computed(() => props.invitationData?.content?.coverImage || 'https://images.unsplash.com/photo-1511285560929-80b456fea0bc?auto=format&fit=crop&w=1000&q=80');
</script>

<template>
    <div class="eloise-theme min-h-screen bg-[#1F271E] text-[#EDEFEA] font-serif selection:bg-[#D4AF37] selection:text-stone-900 relative overflow-x-hidden">

        <!-- ======================================================== -->
        <!-- 1. AUTHENTIC ELOISE MISTY PEARL COVER (Before Opened)    -->
        <!-- ======================================================== -->
        <section
            v-if="!isOpen"
            class="fixed inset-0 z-50 flex flex-col items-center justify-between p-4 sm:p-6 transition-all duration-700 overflow-hidden bg-center bg-cover"
            :class="isOpening ? '-translate-y-full opacity-0 pointer-events-none' : 'translate-y-0 opacity-100'"
            style="background-image: url('/images/templates/eloise-original.png');"
        >
            <!-- Atmospheric Dark Vignette -->
            <div class="absolute inset-0 bg-gradient-to-b from-black/40 via-transparent to-black/60 pointer-events-none"></div>

            <!-- Cover Top Header -->
            <div class="pt-8 sm:pt-10 text-center relative z-10 animate-fade-in">
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-black/40 backdrop-blur-md border border-white/20 text-stone-200 text-[10px] tracking-widest font-sans font-bold uppercase mb-2">
                    <span>👑</span> VVIP GUEST
                </div>
                <h1 class="text-4xl sm:text-5xl font-serif text-[#F4F6F2] italic font-normal tracking-wide drop-shadow-[0_2px_12px_rgba(0,0,0,0.8)]">
                    {{ weddingTitle }}
                </h1>
                <p class="text-xs text-stone-300 font-sans tracking-widest mt-1 uppercase">
                    {{ weddingDate }}
                </p>
            </div>

            <!-- Cover Center: Open Olive Envelope & Flowers Space -->
            <div class="my-auto pointer-events-none"></div>

            <!-- Cover Bottom: Guest greeting & Open Trigger -->
            <div class="w-full max-w-[340px] text-center pb-8 z-20 space-y-3">
                <div class="px-5 py-3 rounded-2xl bg-black/55 backdrop-blur-md border border-white/20 shadow-xl">
                    <p class="text-[10px] text-stone-300 font-sans uppercase tracking-widest">
                        Kepada, Yth:
                    </p>
                    <p class="text-base font-bold text-[#F4F6F2] font-serif mt-0.5 truncate">
                        {{ guestName || 'Tamu Undangan Terhormat' }}
                    </p>
                </div>

                <button
                    @click="handleOpen"
                    type="button"
                    class="w-full py-3.5 px-6 rounded-full bg-gradient-to-r from-[#2F3E2C] via-[#41533D] to-[#2F3E2C] border border-[#D4AF37]/80 text-[#F4F6F2] text-xs sm:text-sm font-sans tracking-[0.2em] uppercase font-bold shadow-2xl hover:scale-105 active:scale-95 transition-all duration-300 cursor-pointer flex items-center justify-center gap-2"
                >
                    <span>📜</span>
                    <span>{{ isOpening ? 'Membuka Surat...' : 'Click to Open Invitation' }}</span>
                </button>
            </div>
        </section>

        <!-- ======================================================== -->
        <!-- 2. FULL ELOISE OPENED INVITATION CONTENT                 -->
        <!-- ======================================================== -->
        <main v-show="isOpen" class="max-w-2xl mx-auto px-4 sm:px-6 py-12 space-y-16 relative">

            <!-- Floating Music Player Button -->
            <div class="fixed bottom-6 right-6 z-40">
                <button
                    @click="emit('toggleMusic')"
                    type="button"
                    class="w-12 h-12 rounded-full bg-[#2F3E2C] text-[#D4AF37] border-2 border-[#D4AF37] shadow-2xl flex items-center justify-center text-lg hover:scale-110 active:scale-95 transition-all"
                    :class="{ 'animate-spin': isPlayingMusic }"
                    style="animation-duration: 5s;"
                    title="Putar / Hentikan Musik"
                >
                    <span>🎵</span>
                </button>
            </div>

            <!-- Opened Hero Section -->
            <header class="text-center pt-8 space-y-4">
                <div class="w-16 h-16 mx-auto rounded-full bg-[#2A3728] border-2 border-[#D4AF37] flex items-center justify-center text-2xl shadow-xl text-[#D4AF37]">
                    🌿
                </div>
                <div class="text-[11px] tracking-[0.3em] uppercase text-[#D4AF37] font-sans font-bold">
                    The Wedding Celebration Of
                </div>
                <h1 class="text-4xl sm:text-6xl font-serif text-[#F4F6F2] italic leading-tight">
                    {{ weddingTitle }}
                </h1>
                <p class="text-sm sm:text-base text-stone-300 font-sans tracking-wide">
                    {{ weddingDate }}
                </p>

                <!-- Couple Photo in Misty Forest Frame -->
                <div class="mt-6 relative max-w-sm mx-auto aspect-[3/4] rounded-3xl overflow-hidden border-4 border-[#D4AF37]/60 shadow-[0_0_25px_rgba(0,0,0,0.6)]">
                    <img :src="coverImg" :alt="weddingTitle" class="w-full h-full object-cover" />
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent flex items-end justify-center p-6">
                        <span class="text-xs tracking-widest uppercase text-[#D4AF37] font-sans font-bold">Pure Romantic Symphony</span>
                    </div>
                </div>
            </header>

            <!-- Holy Quote Card -->
            <section class="p-8 rounded-3xl bg-[#283526]/80 border border-white/10 shadow-xl text-center space-y-3 relative">
                <div class="text-3xl text-[#D4AF37]">“</div>
                <p class="text-base sm:text-lg italic text-[#F4F6F2] leading-relaxed max-w-md mx-auto">
                    {{ invitationData?.content?.quote || 'Di antara keindahan alam semesta, cinta adalah keajaiban yang paling agung dan menenteramkan jiwa.' }}
                </p>
                <div class="text-xs tracking-widest text-[#D4AF37] font-sans font-bold uppercase">
                    {{ invitationData?.content?.quoteSource || 'A Beautiful Journey of Love' }}
                </div>
            </section>

            <!-- Bride & Groom Profiles -->
            <section class="space-y-8">
                <div class="text-center">
                    <div class="text-[10px] tracking-[0.3em] uppercase text-[#D4AF37] font-sans font-bold">The Happy Couple</div>
                    <h2 class="text-3xl font-serif text-[#F4F6F2] italic mt-1">Mempelai yang Berbahagia</h2>
                </div>

                <div class="grid sm:grid-cols-2 gap-6">
                    <!-- Groom -->
                    <div class="p-6 rounded-3xl bg-[#283526]/80 border border-white/10 shadow-lg text-center space-y-3">
                        <div class="w-32 h-32 mx-auto rounded-full overflow-hidden border-2 border-[#D4AF37] shadow-xl">
                            <img :src="groom.photo || 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=400&q=80'" alt="Groom" class="w-full h-full object-cover" />
                        </div>
                        <h3 class="text-2xl font-serif text-[#F4F6F2] font-bold">{{ groom.fullName || groom.nickname || 'Jeremia Pratama' }}</h3>
                        <p class="text-xs text-stone-300 font-sans leading-relaxed">{{ groom.parents || 'Putra tercinta dari Bpk. Ir. Andreas & Ibu Maria' }}</p>
                        <div v-if="groom.instagram" class="pt-1 text-xs text-[#D4AF37] font-sans font-semibold">
                            {{ groom.instagram }}
                        </div>
                    </div>

                    <!-- Bride -->
                    <div class="p-6 rounded-3xl bg-[#283526]/80 border border-white/10 shadow-lg text-center space-y-3">
                        <div class="w-32 h-32 mx-auto rounded-full overflow-hidden border-2 border-[#D4AF37] shadow-xl">
                            <img :src="bride.photo || 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=400&q=80'" alt="Bride" class="w-full h-full object-cover" />
                        </div>
                        <h3 class="text-2xl font-serif text-[#F4F6F2] font-bold">{{ bride.fullName || bride.nickname || 'Anna Clarissa' }}</h3>
                        <p class="text-xs text-stone-300 font-sans leading-relaxed">{{ bride.parents || 'Putri tercinta dari Bpk. Handoko & Ibu Ratna' }}</p>
                        <div v-if="bride.instagram" class="pt-1 text-xs text-[#D4AF37] font-sans font-semibold">
                            {{ bride.instagram }}
                        </div>
                    </div>
                </div>
            </section>

            <!-- Countdown Timer -->
            <section class="p-8 rounded-3xl bg-[#253123] border border-[#D4AF37]/30 text-center shadow-xl space-y-4">
                <div class="text-[10px] tracking-[0.3em] uppercase text-[#D4AF37] font-sans font-bold">Menghitung Hari Bahagia</div>
                <div class="grid grid-cols-4 gap-2 sm:gap-4 max-w-sm mx-auto">
                    <div class="p-3 rounded-2xl bg-black/30 border border-white/10">
                        <span class="block text-2xl sm:text-3xl font-bold font-serif text-[#F4F6F2]">{{ timeLeft.days }}</span>
                        <span class="text-[9px] uppercase tracking-wider text-[#D4AF37]">Hari</span>
                    </div>
                    <div class="p-3 rounded-2xl bg-black/30 border border-white/10">
                        <span class="block text-2xl sm:text-3xl font-bold font-serif text-[#F4F6F2]">{{ timeLeft.hours }}</span>
                        <span class="text-[9px] uppercase tracking-wider text-[#D4AF37]">Jam</span>
                    </div>
                    <div class="p-3 rounded-2xl bg-black/30 border border-white/10">
                        <span class="block text-2xl sm:text-3xl font-bold font-serif text-[#F4F6F2]">{{ timeLeft.minutes }}</span>
                        <span class="text-[9px] uppercase tracking-wider text-[#D4AF37]">Menit</span>
                    </div>
                    <div class="p-3 rounded-2xl bg-black/30 border border-white/10">
                        <span class="block text-2xl sm:text-3xl font-bold font-serif text-[#F4F6F2]">{{ timeLeft.seconds }}</span>
                        <span class="text-[9px] uppercase tracking-wider text-[#D4AF37]">Detik</span>
                    </div>
                </div>
            </section>

            <!-- Rangkaian Acara -->
            <section class="space-y-6">
                <div class="text-center">
                    <div class="text-[10px] tracking-[0.3em] uppercase text-[#D4AF37] font-sans font-bold">Wedding Timeline</div>
                    <h2 class="text-3xl font-serif text-[#F4F6F2] italic mt-1">Rangkaian Acara</h2>
                </div>

                <div class="space-y-6">
                    <div
                        v-for="ev in events"
                        :key="ev.id"
                        class="p-8 rounded-3xl bg-[#283526]/85 border border-white/10 shadow-lg text-center space-y-4"
                    >
                        <div class="w-10 h-10 mx-auto rounded-full bg-[#1F271E] border border-[#D4AF37]/50 flex items-center justify-center text-[#D4AF37]">
                            📅
                        </div>
                        <h3 class="text-2xl font-serif text-[#F4F6F2] font-bold">{{ ev.name }}</h3>
                        <div class="py-2 border-y border-white/10 inline-block px-6">
                            <p class="text-sm font-bold text-[#D4AF37] font-sans">{{ safeFormatDisplayDate(ev.starts_at) }}</p>
                            <p class="text-xs text-stone-300 font-sans mt-0.5">{{ safeFormatEventTime(ev.starts_at, ev.ends_at, ev.timezone) }}</p>
                        </div>
                        <div>
                            <h4 class="text-base font-bold text-[#F4F6F2]">{{ ev.venue_name }}</h4>
                            <p class="text-xs text-stone-300 font-sans mt-1 max-w-sm mx-auto leading-relaxed">{{ ev.address }}</p>
                        </div>
                        <div class="pt-2">
                            <a
                                :href="ev.maps_url || 'https://maps.google.com'"
                                target="_blank"
                                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-full text-xs font-sans uppercase tracking-widest font-bold bg-[#D4AF37] text-stone-900 hover:bg-[#F3E5AB] shadow-md transition-all hover:scale-105"
                            >
                                <span>📍 Petunjuk Lokasi (Google Maps)</span>
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Digital Gift -->
            <section v-if="digitalGift?.enabled" class="p-8 rounded-3xl bg-[#283526]/85 border border-white/10 shadow-lg text-center space-y-6">
                <div>
                    <div class="text-[10px] tracking-[0.3em] uppercase text-[#D4AF37] font-sans font-bold">Wedding Gift</div>
                    <h2 class="text-3xl font-serif text-[#F4F6F2] italic mt-1">Tanda Kasih & Hadiah Digital</h2>
                    <p class="text-xs text-stone-300 font-sans max-w-md mx-auto mt-2 leading-relaxed">
                        Doa restu Anda adalah karunia terindah bagi kami. Namun jika ingin memberikan tanda kasih secara digital:
                    </p>
                </div>

                <div class="space-y-4 max-w-md mx-auto">
                    <div
                        v-for="(acc, idx) in (digitalGift?.accounts || [])"
                        :key="idx"
                        class="p-5 rounded-2xl bg-black/40 border border-white/10 text-left flex items-center justify-between"
                    >
                        <div>
                            <span class="text-[10px] uppercase font-bold tracking-wider text-[#D4AF37]">{{ acc.bank_name || 'Bank' }}</span>
                            <div class="text-base font-mono font-bold text-[#F4F6F2] mt-0.5">{{ acc.account_number }}</div>
                            <div class="text-xs text-stone-300">a.n {{ acc.holder_name }}</div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button
                                v-if="acc.qris_image_url"
                                @click="openQris(acc)"
                                type="button"
                                class="px-3 py-1.5 rounded-lg bg-[#1F271E] border border-[#D4AF37]/50 text-xs font-sans font-semibold text-[#D4AF37] hover:bg-[#2A3728]"
                            >
                                QRIS
                            </button>
                            <button
                                @click="copyBankNumber(acc.account_number, idx)"
                                type="button"
                                class="px-3 py-1.5 rounded-lg bg-[#D4AF37] text-stone-900 text-xs font-sans font-bold hover:bg-[#F3E5AB]"
                            >
                                {{ copiedBankIndex === idx ? '✓ Tersalin' : 'Salin' }}
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <!-- RSVP Form & Wishes -->
            <section class="p-8 rounded-3xl bg-[#283526]/85 border border-white/10 shadow-lg space-y-6">
                <div class="text-center">
                    <div class="text-[10px] tracking-[0.3em] uppercase text-[#D4AF37] font-sans font-bold">RSVP & Wishes</div>
                    <h2 class="text-3xl font-serif text-[#F4F6F2] italic mt-1">Konfirmasi Kehadiran & Doa Restu</h2>
                </div>

                <form @submit.prevent="submitRsvp" class="space-y-4 max-w-lg mx-auto">
                    <div>
                        <label class="block text-xs font-bold text-[#D4AF37] font-sans mb-1">Nama Lengkap</label>
                        <input
                            v-model="rsvpForm.guest_name"
                            type="text"
                            required
                            placeholder="Tuliskan nama Anda"
                            class="w-full px-4 py-2.5 rounded-xl border border-white/10 bg-black/40 text-[#F4F6F2] text-xs font-sans focus:outline-none focus:ring-2 focus:ring-[#D4AF37]"
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-[#D4AF37] font-sans mb-1">Kehadiran</label>
                            <select
                                v-model="rsvpForm.status"
                                class="w-full px-4 py-2.5 rounded-xl border border-white/10 bg-[#1F271E] text-[#F4F6F2] text-xs font-sans focus:outline-none focus:ring-2 focus:ring-[#D4AF37]"
                            >
                                <option value="attending">Hadir</option>
                                <option value="tentative">Masih Ragu</option>
                                <option value="declined">Tidak Hadir</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-[#D4AF37] font-sans mb-1">Jumlah Tamu</label>
                            <input
                                v-model.number="rsvpForm.attendees_count"
                                type="number"
                                min="1"
                                max="10"
                                class="w-full px-4 py-2.5 rounded-xl border border-white/10 bg-black/40 text-[#F4F6F2] text-xs font-sans focus:outline-none focus:ring-2 focus:ring-[#D4AF37]"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-[#D4AF37] font-sans mb-1">Ucapan & Doa Restu</label>
                        <textarea
                            v-model="rsvpForm.wishes"
                            rows="3"
                            placeholder="Tuliskan doa restu untuk kedua mempelai..."
                            class="w-full px-4 py-2.5 rounded-xl border border-white/10 bg-black/40 text-[#F4F6F2] text-xs font-sans focus:outline-none focus:ring-2 focus:ring-[#D4AF37]"
                        ></textarea>
                    </div>

                    <button
                        type="submit"
                        :disabled="rsvpSubmitting"
                        class="w-full py-3 rounded-xl bg-[#D4AF37] text-stone-900 text-xs font-sans uppercase tracking-widest font-bold hover:bg-[#F3E5AB] shadow-lg transition-all cursor-pointer"
                    >
                        {{ rsvpSubmitting ? 'Mengirim...' : 'Kirim Konfirmasi Kehadiran' }}
                    </button>
                    <p v-if="rsvpSuccess" class="text-xs text-emerald-400 font-semibold text-center">
                        ✓ Terima kasih, konfirmasi kehadiran Anda telah tersimpan!
                    </p>
                </form>

                <!-- Guestbook List -->
                <div class="pt-6 border-t border-white/10 space-y-3 max-w-lg mx-auto">
                    <h4 class="text-xs font-bold text-[#D4AF37] font-sans uppercase tracking-wider text-center">Untaian Doa & Ucapan</h4>
                    <div class="max-h-60 overflow-y-auto space-y-2 pr-1">
                        <div
                            v-for="entry in guestbook"
                            :key="entry.id"
                            class="p-3.5 rounded-xl bg-black/40 border border-white/10 text-left"
                        >
                            <div class="flex items-center justify-between text-[11px] font-bold text-[#D4AF37]">
                                <span>{{ entry.guest_name }}</span>
                                <span class="text-[9px] text-stone-400 font-normal">{{ entry.created_at }}</span>
                            </div>
                            <p class="text-xs text-[#F4F6F2] mt-1 italic leading-relaxed">"{{ entry.message }}"</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Footer -->
            <footer class="text-center pt-8 pb-12 space-y-3">
                <div class="w-10 h-10 mx-auto rounded-full bg-[#2A3728] border border-[#D4AF37] flex items-center justify-center text-lg text-[#D4AF37]">
                    🌿
                </div>
                <h3 class="text-2xl font-serif text-[#F4F6F2] italic font-bold">Terima Kasih</h3>
                <p class="text-xs text-stone-300 font-sans max-w-xs mx-auto">
                    Merupakan suatu kehormatan dan kebahagiaan bagi kami apabila Bapak/Ibu/Saudara/i berkenan hadir dan memberikan doa restu.
                </p>
                <div class="pt-4 text-[10px] text-[#D4AF37]/70 font-sans tracking-widest uppercase">
                    Undangan Digital • Eloise Misty Romance Edition
                </div>
            </footer>

        </main>

    </div>
</template>

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
        particleCount: 70,
        spread: 80,
        origin: { y: 0.6 },
        colors: ['#F5BD47', '#581825', '#FFDF73', '#FFFFFF']
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
    return d || 'Minggu, 20 Desember 2026';
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
    return props.invitationData?.content?.weddingTitle || 'Sutan Alif & Siti Alsa';
});
const weddingDate = computed(() => props.invitationData?.content?.weddingDateText || 'Minggu, 20 Desember 2026');
const coverImg = computed(() => props.invitationData?.content?.coverImage || 'https://images.unsplash.com/photo-1583939003579-730e3918a45a?auto=format&fit=crop&w=1000&q=80');
</script>

<template>
    <div class="alsa-theme min-h-screen bg-[#1A120B] text-[#FFF6E5] font-serif selection:bg-[#F5BD47] selection:text-stone-900 relative overflow-x-hidden">

        <!-- ======================================================== -->
        <!-- 1. AUTHENTIC ALSA NUSANTARA COVER (Before Opened)        -->
        <!-- ======================================================== -->
        <section
            v-if="!isOpen"
            class="fixed inset-0 z-50 flex flex-col items-center justify-between p-4 sm:p-6 transition-all duration-700 overflow-hidden bg-center bg-cover"
            :class="isOpening ? '-translate-y-full opacity-0 pointer-events-none' : 'translate-y-0 opacity-100'"
            style="background-image: url('/images/templates/alsa-original.png');"
        >
            <!-- Atmospheric Vignette Overlay -->
            <div class="absolute inset-0 bg-gradient-to-b from-black/40 via-transparent to-black/70 pointer-events-none"></div>

            <!-- Cover Top Header -->
            <div class="pt-10 text-center relative z-10 animate-fade-in">
                <div class="inline-flex items-center gap-2 px-4 py-1 rounded-full bg-black/50 backdrop-blur-md border border-[#F5BD47]/40 text-[#F5BD47] text-[10px] sm:text-xs tracking-[0.25em] font-sans font-bold uppercase mb-2 shadow-lg">
                    <span>🏛️</span> SERAT ULEM PAWIWAHAN AGENG
                </div>
                <h1 class="text-4xl sm:text-5xl font-serif text-[#FFF8E7] italic font-bold tracking-tight drop-shadow-[0_2px_10px_rgba(0,0,0,0.9)]">
                    {{ weddingTitle }}
                </h1>
                <p class="text-xs sm:text-sm text-[#F5BD47] font-sans tracking-widest mt-1 uppercase font-semibold drop-shadow-md">
                    {{ weddingDate }}
                </p>
            </div>

            <!-- Cover Center: Joglo Archway Atmosphere Space -->
            <div class="my-auto relative z-10 text-center pointer-events-none">
                <!-- Floating Gunungan Emblem -->
                <div class="w-16 h-16 mx-auto rounded-full bg-black/40 backdrop-blur-md border border-[#F5BD47]/60 flex items-center justify-center text-3xl shadow-xl">
                    ⛰️
                </div>
            </div>

            <!-- Cover Bottom: Guest Card & Open Button -->
            <div class="w-full max-w-[340px] text-center pb-6 z-20 space-y-3">
                <div class="px-5 py-3 rounded-2xl bg-black/65 backdrop-blur-md border border-[#F5BD47]/40 shadow-xl">
                    <p class="text-[10px] text-[#E0C097] font-sans uppercase tracking-widest font-medium">
                        Katur Dhumateng Panjenenganipun:
                    </p>
                    <p class="text-base font-bold text-[#FFF8E7] font-serif mt-0.5 truncate">
                        {{ guestName || 'Tamu Undangan Kinasih' }}
                    </p>
                </div>

                <button
                    @click="handleOpen"
                    type="button"
                    class="w-full py-3.5 px-6 rounded-full bg-gradient-to-r from-[#581825] via-[#7A1F32] to-[#581825] border-2 border-[#F5BD47]/80 text-[#FFF8E7] text-xs sm:text-sm font-serif tracking-[0.2em] uppercase font-bold shadow-2xl hover:scale-105 active:scale-95 transition-all duration-300 cursor-pointer flex items-center justify-center gap-2"
                >
                    <span>📜</span>
                    <span>{{ isOpening ? 'Membuka Serat...' : 'Buka Undangan' }}</span>
                </button>
            </div>
        </section>

        <!-- ======================================================== -->
        <!-- 2. FULL NUSANTARA ROYAL OPENED INVITATION CONTENT        -->
        <!-- ======================================================== -->
        <main v-show="isOpen" class="max-w-2xl mx-auto px-4 sm:px-6 py-12 space-y-16 relative">

            <!-- Floating Music Player Button -->
            <div class="fixed bottom-6 right-6 z-40">
                <button
                    @click="emit('toggleMusic')"
                    type="button"
                    class="w-12 h-12 rounded-full bg-[#581825] text-[#F5BD47] border-2 border-[#F5BD47] shadow-2xl flex items-center justify-center text-lg hover:scale-110 active:scale-95 transition-all"
                    :class="{ 'animate-spin': isPlayingMusic }"
                    style="animation-duration: 5s;"
                    title="Putar / Hentikan Gamelan Musik"
                >
                    <span>🎵</span>
                </button>
            </div>

            <!-- Opened Hero Section with Royal Gunungan -->
            <header class="text-center pt-8 space-y-4">
                <div class="w-16 h-16 mx-auto rounded-full bg-[#2E1D13] border-2 border-[#F5BD47] flex items-center justify-center text-2xl shadow-xl text-[#F5BD47]">
                    ⚜️
                </div>
                <div class="text-[11px] tracking-[0.3em] uppercase text-[#F5BD47] font-sans font-bold">
                    Pernikahan Adat Keraton Nusantara
                </div>
                <h1 class="text-4xl sm:text-6xl font-serif text-[#FFF8E7] italic font-bold leading-tight">
                    {{ weddingTitle }}
                </h1>
                <p class="text-sm sm:text-base text-[#D4AF37] font-sans tracking-wide">
                    {{ weddingDate }}
                </p>

                <!-- Couple Photo in Royal Carved Wood Frame -->
                <div class="mt-6 relative max-w-sm mx-auto aspect-[3/4] rounded-3xl overflow-hidden border-4 border-[#F5BD47]/80 shadow-[0_0_30px_rgba(245,189,71,0.25)]">
                    <img :src="coverImg" :alt="weddingTitle" class="w-full h-full object-cover" />
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent flex items-end justify-center p-6">
                        <span class="text-xs tracking-widest uppercase text-[#F5BD47] font-serif font-bold">Pawiwahan Ageng</span>
                    </div>
                </div>
            </header>

            <!-- Sacred Javanese Blessing Quote -->
            <section class="p-8 rounded-3xl bg-[#261A13]/90 border border-[#F5BD47]/30 shadow-xl text-center space-y-3 relative">
                <div class="text-3xl text-[#F5BD47]">“</div>
                <p class="text-base sm:text-lg italic text-[#FFF8E7] leading-relaxed max-w-md mx-auto">
                    {{ invitationData?.content?.quote || 'Bulek aia dek pambuluah, bulek kato dek mupakaik. Sauh saundang sarangkai budi, basatu janji suci sampai ka akia hayat.' }}
                </p>
                <div class="text-xs tracking-widest text-[#F5BD47] font-sans font-bold uppercase">
                    {{ invitationData?.content?.quoteSource || 'Pepatah Adat Luhur' }}
                </div>
            </section>

            <!-- Mempelai Adat Nusantara -->
            <section class="space-y-8">
                <div class="text-center">
                    <div class="text-[10px] tracking-[0.3em] uppercase text-[#F5BD47] font-sans font-bold">Mempelai Kersaning Pengantin</div>
                    <h2 class="text-3xl font-serif text-[#FFF8E7] italic mt-1">Dua Jiwa Bersatu Janji Suci</h2>
                </div>

                <div class="grid sm:grid-cols-2 gap-6">
                    <!-- Groom -->
                    <div class="p-6 rounded-3xl bg-[#261A13]/80 border border-[#F5BD47]/30 shadow-lg text-center space-y-3">
                        <div class="w-32 h-32 mx-auto rounded-full overflow-hidden border-2 border-[#F5BD47] shadow-xl">
                            <img :src="groom.photo || 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=400&q=80'" alt="Groom" class="w-full h-full object-cover" />
                        </div>
                        <h3 class="text-2xl font-serif text-[#FFF8E7] font-bold">{{ groom.fullName || groom.nickname || 'Sutan Alif Firdaus' }}</h3>
                        <p class="text-xs text-[#D8C4B6] font-sans leading-relaxed">{{ groom.parents || 'Putra sulung dari Bpk. Dt. Bagindo Marajo & Ibu Hj. Rosdiana' }}</p>
                        <div v-if="groom.instagram" class="pt-1 text-xs text-[#F5BD47] font-sans font-semibold">
                            {{ groom.instagram }}
                        </div>
                    </div>

                    <!-- Bride -->
                    <div class="p-6 rounded-3xl bg-[#261A13]/80 border border-[#F5BD47]/30 shadow-lg text-center space-y-3">
                        <div class="w-32 h-32 mx-auto rounded-full overflow-hidden border-2 border-[#F5BD47] shadow-xl">
                            <img :src="bride.photo || 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=400&q=80'" alt="Bride" class="w-full h-full object-cover" />
                        </div>
                        <h3 class="text-2xl font-serif text-[#FFF8E7] font-bold">{{ bride.fullName || bride.nickname || 'Siti Sarah Alsa' }}</h3>
                        <p class="text-xs text-[#D8C4B6] font-sans leading-relaxed">{{ bride.parents || 'Putri kedua dari Bpk. H. Syamsul Bahri & Ibu Hj. Nurmaida' }}</p>
                        <div v-if="bride.instagram" class="pt-1 text-xs text-[#F5BD47] font-sans font-semibold">
                            {{ bride.instagram }}
                        </div>
                    </div>
                </div>
            </section>

            <!-- Countdown Timer -->
            <section class="p-8 rounded-3xl bg-[#2E1D13] border border-[#F5BD47]/40 text-center shadow-2xl space-y-4">
                <div class="text-[10px] tracking-[0.3em] uppercase text-[#F5BD47] font-sans font-bold">Ngentosi Dinten Pawiwahan</div>
                <div class="grid grid-cols-4 gap-2 sm:gap-4 max-w-sm mx-auto">
                    <div class="p-3 rounded-2xl bg-black/40 border border-[#F5BD47]/30">
                        <span class="block text-2xl sm:text-3xl font-bold font-serif text-[#FFF8E7]">{{ timeLeft.days }}</span>
                        <span class="text-[9px] uppercase tracking-wider text-[#F5BD47]">Dinten</span>
                    </div>
                    <div class="p-3 rounded-2xl bg-black/40 border border-[#F5BD47]/30">
                        <span class="block text-2xl sm:text-3xl font-bold font-serif text-[#FFF8E7]">{{ timeLeft.hours }}</span>
                        <span class="text-[9px] uppercase tracking-wider text-[#F5BD47]">Tabuh</span>
                    </div>
                    <div class="p-3 rounded-2xl bg-black/40 border border-[#F5BD47]/30">
                        <span class="block text-2xl sm:text-3xl font-bold font-serif text-[#FFF8E7]">{{ timeLeft.minutes }}</span>
                        <span class="text-[9px] uppercase tracking-wider text-[#F5BD47]">Menit</span>
                    </div>
                    <div class="p-3 rounded-2xl bg-black/40 border border-[#F5BD47]/30">
                        <span class="block text-2xl sm:text-3xl font-bold font-serif text-[#FFF8E7]">{{ timeLeft.seconds }}</span>
                        <span class="text-[9px] uppercase tracking-wider text-[#F5BD47]">Detik</span>
                    </div>
                </div>
            </section>

            <!-- Agenda Acara / Pranatacara -->
            <section class="space-y-6">
                <div class="text-center">
                    <div class="text-[10px] tracking-[0.3em] uppercase text-[#F5BD47] font-sans font-bold">Pranatacara Pahargyan</div>
                    <h2 class="text-3xl font-serif text-[#FFF8E7] italic mt-1">Rangkaian Titimangsa</h2>
                </div>

                <div class="space-y-6">
                    <div
                        v-for="ev in events"
                        :key="ev.id"
                        class="p-8 rounded-3xl bg-[#261A13]/90 border border-[#F5BD47]/30 shadow-xl text-center space-y-4"
                    >
                        <div class="w-10 h-10 mx-auto rounded-full bg-[#1A120B] border border-[#F5BD47]/60 flex items-center justify-center text-[#F5BD47]">
                            🏛️
                        </div>
                        <h3 class="text-2xl font-serif text-[#FFF8E7] font-bold">{{ ev.name }}</h3>
                        <div class="py-2 border-y border-[#F5BD47]/20 inline-block px-6">
                            <p class="text-sm font-bold text-[#F5BD47] font-sans">{{ safeFormatDisplayDate(ev.starts_at) }}</p>
                            <p class="text-xs text-[#D8C4B6] font-sans mt-0.5">{{ safeFormatEventTime(ev.starts_at, ev.ends_at, ev.timezone) }}</p>
                        </div>
                        <div>
                            <h4 class="text-base font-bold text-[#FFF8E7]">{{ ev.venue_name }}</h4>
                            <p class="text-xs text-[#D8C4B6] font-sans mt-1 max-w-sm mx-auto leading-relaxed">{{ ev.address }}</p>
                        </div>
                        <div class="pt-2">
                            <a
                                :href="ev.maps_url || 'https://maps.google.com'"
                                target="_blank"
                                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-full text-xs font-sans uppercase tracking-widest font-bold bg-[#F5BD47] text-stone-950 hover:bg-[#FFDF73] shadow-lg transition-all hover:scale-105"
                            >
                                <span>📍 Pitedah Margi (Google Maps)</span>
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Tanda Asih / Digital Gift -->
            <section v-if="digitalGift?.enabled" class="p-8 rounded-3xl bg-[#261A13]/90 border border-[#F5BD47]/30 shadow-xl text-center space-y-6">
                <div>
                    <div class="text-[10px] tracking-[0.3em] uppercase text-[#F5BD47] font-sans font-bold">Kado Digital</div>
                    <h2 class="text-3xl font-serif text-[#FFF8E7] italic mt-1">Tandha Asih & Doa Restu</h2>
                    <p class="text-xs text-[#D8C4B6] font-sans max-w-md mx-auto mt-2 leading-relaxed">
                        Kanthi raos tulus sanget maturnuwun dhumateng doa restu panjenengan. Menawi kepareng maringi kado kanthi digital:
                    </p>
                </div>

                <div class="space-y-4 max-w-md mx-auto">
                    <div
                        v-for="(acc, idx) in (digitalGift?.accounts || [])"
                        :key="idx"
                        class="p-5 rounded-2xl bg-black/40 border border-[#F5BD47]/30 text-left flex items-center justify-between"
                    >
                        <div>
                            <span class="text-[10px] uppercase font-bold tracking-wider text-[#F5BD47]">{{ acc.bank_name || 'Bank' }}</span>
                            <div class="text-base font-mono font-bold text-[#FFF8E7] mt-0.5">{{ acc.account_number }}</div>
                            <div class="text-xs text-[#D8C4B6]">a.n {{ acc.holder_name }}</div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button
                                v-if="acc.qris_image_url"
                                @click="openQris(acc)"
                                type="button"
                                class="px-3 py-1.5 rounded-lg bg-[#2E1D13] border border-[#F5BD47]/50 text-xs font-sans font-semibold text-[#F5BD47] hover:bg-[#3D271A]"
                            >
                                QRIS
                            </button>
                            <button
                                @click="copyBankNumber(acc.account_number, idx)"
                                type="button"
                                class="px-3 py-1.5 rounded-lg bg-[#F5BD47] text-stone-950 text-xs font-sans font-bold hover:bg-[#FFDF73]"
                            >
                                {{ copiedBankIndex === idx ? '✓ Kasanten' : 'Turun' }}
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <!-- RSVP Form & Buku Tamu -->
            <section class="p-8 rounded-3xl bg-[#261A13]/90 border border-[#F5BD47]/30 shadow-xl space-y-6">
                <div class="text-center">
                    <div class="text-[10px] tracking-[0.3em] uppercase text-[#F5BD47] font-sans font-bold">Pawartos Rawuh</div>
                    <h2 class="text-3xl font-serif text-[#FFF8E7] italic mt-1">Konfirmasi Rawuh & Panyuwun</h2>
                </div>

                <form @submit.prevent="submitRsvp" class="space-y-4 max-w-lg mx-auto">
                    <div>
                        <label class="block text-xs font-bold text-[#F5BD47] font-sans mb-1">Asma Jangkep</label>
                        <input
                            v-model="rsvpForm.guest_name"
                            type="text"
                            required
                            placeholder="Serat asma panjenengan..."
                            class="w-full px-4 py-2.5 rounded-xl border border-[#F5BD47]/30 bg-black/40 text-[#FFF8E7] text-xs font-sans focus:outline-none focus:ring-2 focus:ring-[#F5BD47]"
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-[#F5BD47] font-sans mb-1">Katrangan Rawuh</label>
                            <select
                                v-model="rsvpForm.status"
                                class="w-full px-4 py-2.5 rounded-xl border border-[#F5BD47]/30 bg-[#1A120B] text-[#FFF8E7] text-xs font-sans focus:outline-none focus:ring-2 focus:ring-[#F5BD47]"
                            >
                                <option value="attending">Insya Allah Rawuh</option>
                                <option value="tentative">Dereng Tamtu</option>
                                <option value="declined">Mboten Saged Rawuh</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-[#F5BD47] font-sans mb-1">Gunggunging Tamu</label>
                            <input
                                v-model.number="rsvpForm.attendees_count"
                                type="number"
                                min="1"
                                max="10"
                                class="w-full px-4 py-2.5 rounded-xl border border-[#F5BD47]/30 bg-black/40 text-[#FFF8E7] text-xs font-sans focus:outline-none focus:ring-2 focus:ring-[#F5BD47]"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-[#F5BD47] font-sans mb-1">Panyuwun & Doa Restu</label>
                        <textarea
                            v-model="rsvpForm.wishes"
                            rows="3"
                            placeholder="Kintun doa restu kagem temanten kekalih..."
                            class="w-full px-4 py-2.5 rounded-xl border border-[#F5BD47]/30 bg-black/40 text-[#FFF8E7] text-xs font-sans focus:outline-none focus:ring-2 focus:ring-[#F5BD47]"
                        ></textarea>
                    </div>

                    <button
                        type="submit"
                        :disabled="rsvpSubmitting"
                        class="w-full py-3 rounded-xl bg-gradient-to-r from-[#581825] to-[#7A1F32] border border-[#F5BD47]/80 text-[#FFF8E7] text-xs font-sans uppercase tracking-widest font-bold hover:brightness-110 shadow-lg transition-all cursor-pointer"
                    >
                        {{ rsvpSubmitting ? 'Ngintun Katrangan...' : 'Kirim Konfirmasi Rawuh' }}
                    </button>
                    <p v-if="rsvpSuccess" class="text-xs text-emerald-400 font-semibold text-center">
                        ✓ Maturnuwun sanget, konfirmasi panjenengan sampun katampi!
                    </p>
                </form>

                <!-- Guestbook List -->
                <div class="pt-6 border-t border-[#F5BD47]/20 space-y-3 max-w-lg mx-auto">
                    <h4 class="text-xs font-bold text-[#F5BD47] font-sans uppercase tracking-wider text-center">Doa Restu Para Sederek</h4>
                    <div class="max-h-60 overflow-y-auto space-y-2 pr-1">
                        <div
                            v-for="entry in guestbook"
                            :key="entry.id"
                            class="p-3.5 rounded-xl bg-black/40 border border-[#F5BD47]/20 text-left"
                        >
                            <div class="flex items-center justify-between text-[11px] font-bold text-[#F5BD47]">
                                <span>{{ entry.guest_name }}</span>
                                <span class="text-[9px] text-[#D8C4B6] font-normal">{{ entry.created_at }}</span>
                            </div>
                            <p class="text-xs text-[#FFF8E7] mt-1 italic leading-relaxed">"{{ entry.message }}"</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Footer Closing -->
            <footer class="text-center pt-8 pb-12 space-y-3">
                <div class="w-12 h-12 mx-auto rounded-full bg-[#2E1D13] border border-[#F5BD47] flex items-center justify-center text-xl text-[#F5BD47]">
                    ⛰️
                </div>
                <h3 class="text-2xl font-serif text-[#FFF8E7] italic font-bold">Maturnuwun</h3>
                <p class="text-xs text-[#D8C4B6] font-sans max-w-xs mx-auto">
                    Kaping pungkasan, minangka panyuwunan ageng saking kulawarga kekalih dhumateng rawuh lan doa restu panjenengan.
                </p>
                <div class="pt-4 text-[10px] text-[#F5BD47]/80 font-sans tracking-widest uppercase">
                    Undangan Digital • Royal Nusantara Heritage Edition
                </div>
            </footer>

        </main>

    </div>
</template>

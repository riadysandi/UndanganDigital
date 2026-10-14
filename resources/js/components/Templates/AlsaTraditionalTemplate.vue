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

// 3D Gebyok Doors Opening State
const isDoorOpening = ref(false);
const isDoorFullyOpened = ref(false);

const handleOpenGebyok = () => {
    if (isDoorOpening.value) return;
    isDoorOpening.value = true;

    confetti({
        particleCount: 60,
        spread: 70,
        origin: { y: 0.6 },
        colors: ['#D4AF37', '#996515', '#FFE082']
    });

    setTimeout(() => {
        isDoorFullyOpened.value = true;
        emit('openInvitation');
    }, 1200);
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

// Copy bank number
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
const weddingTitle = computed(() => props.invitationData?.content?.weddingTitle || 'Nusantara Romance');
const weddingDate = computed(() => props.invitationData?.content?.weddingDateText || 'Desember 2026');
const coverImg = computed(() => props.invitationData?.content?.coverImage || 'https://images.unsplash.com/photo-1583939003579-730e3918a45a?auto=format&fit=crop&w=1200&q=80');
</script>

<template>
    <div class="alsa-nusantara-theme min-h-screen bg-[#1A120B] text-[#F3E9DD] font-serif selection:bg-[#C5A059] selection:text-stone-900 overflow-x-hidden relative">

        <!-- Authentic Golden Batik Parang / Mega Mendung Background Motifs -->
        <div class="pointer-events-none fixed inset-0 opacity-10 bg-[radial-gradient(#D4AF37_1px,transparent_1px)] [background-size:28px_28px] -z-10"></div>
        <div class="pointer-events-none fixed inset-0 bg-gradient-to-b from-[#24170E]/80 via-transparent to-[#150D07] -z-10"></div>

        <!-- ======================================================== -->
        <!-- 1. 3D CARVED GEBYOK PALACE GATE (Opening Cover)          -->
        <!-- ======================================================== -->
        <div
            v-if="!isOpen"
            class="fixed inset-0 z-50 flex flex-col items-center justify-between py-10 px-4 bg-[#1C130B] transition-opacity duration-1000"
            :class="{ 'opacity-0 pointer-events-none': isDoorFullyOpened }"
        >
            <!-- Top Gunungan Emblem & Serat Ulem Title -->
            <div class="text-center z-20">
                <div class="text-3xl text-[#D4AF37] mb-1">
                    ⛰️
                </div>
                <div class="text-[10px] tracking-[0.4em] uppercase text-[#D4AF37] font-sans font-bold">
                    Serat Ulem Pawiwahan Ageng
                </div>
                <h1 class="text-3xl sm:text-4xl font-serif italic text-amber-100 mt-1 font-bold">
                    {{ weddingTitle }}
                </h1>
            </div>

            <!-- Double 3D Carved Gebyok Wooden Doors -->
            <div class="relative w-full max-w-sm sm:max-w-md aspect-[3/4] perspective-[1400px] my-auto select-none rounded-2xl shadow-2xl shadow-black border-4 border-[#5E4228]">
                
                <!-- Inside Revealed Palace Stage (Shows behind doors) -->
                <div class="absolute inset-0 bg-gradient-to-b from-[#2B1B10] to-[#120A05] flex flex-col items-center justify-center p-6 text-center">
                    <div class="w-32 h-32 rounded-full overflow-hidden border-2 border-[#D4AF37] shadow-xl mb-3">
                        <img :src="coverImg" :alt="weddingTitle" class="w-full h-full object-cover" />
                    </div>
                    <div class="text-xs uppercase tracking-widest text-[#D4AF37] font-sans">Sugeng Rawuh</div>
                    <h3 class="font-serif italic text-xl text-white font-bold mt-1">{{ weddingTitle }}</h3>
                </div>

                <!-- Left Wooden Door (3D RotateY Open) -->
                <div
                    class="absolute inset-y-0 left-0 w-1/2 bg-[#3A2617] border-r-2 border-[#1E1208] origin-left transition-transform duration-1000 ease-in-out shadow-2xl flex flex-col items-end justify-center pr-3"
                    :style="{
                        transform: isDoorOpening ? 'rotateY(-110deg)' : 'rotateY(0deg)',
                        transformStyle: 'preserve-3d'
                    }"
                >
                    <!-- Intricate Carving Inset -->
                    <div class="w-full h-full p-2 flex flex-col justify-between">
                        <div class="h-1/3 border border-[#8C5D36]/40 rounded-t-lg bg-[#2D1D11]/60 flex items-center justify-center text-[#D4AF37]/50 text-xl">⚜️</div>
                        <div class="h-1/3 border border-[#8C5D36]/40 my-1 bg-[#2D1D11]/60 flex items-center justify-end pr-2">
                            <!-- Brass Door Ring Knocker -->
                            <div class="w-7 h-7 rounded-full border-2 border-[#D4AF37] flex items-center justify-center shadow-md">
                                <div class="w-4 h-4 rounded-full border border-[#D4AF37]/60"></div>
                            </div>
                        </div>
                        <div class="h-1/3 border border-[#8C5D36]/40 rounded-b-lg bg-[#2D1D11]/60 flex items-center justify-center text-[#D4AF37]/50 text-xl">⚜️</div>
                    </div>
                </div>

                <!-- Right Wooden Door (3D RotateY Open) -->
                <div
                    class="absolute inset-y-0 right-0 w-1/2 bg-[#3A2617] border-l-2 border-[#1E1208] origin-right transition-transform duration-1000 ease-in-out shadow-2xl flex flex-col items-start justify-center pl-3"
                    :style="{
                        transform: isDoorOpening ? 'rotateY(110deg)' : 'rotateY(0deg)',
                        transformStyle: 'preserve-3d'
                    }"
                >
                    <!-- Intricate Carving Inset -->
                    <div class="w-full h-full p-2 flex flex-col justify-between">
                        <div class="h-1/3 border border-[#8C5D36]/40 rounded-t-lg bg-[#2D1D11]/60 flex items-center justify-center text-[#D4AF37]/50 text-xl">⚜️</div>
                        <div class="h-1/3 border border-[#8C5D36]/40 my-1 bg-[#2D1D11]/60 flex items-center justify-start pl-2">
                            <!-- Brass Door Ring Knocker -->
                            <div class="w-7 h-7 rounded-full border-2 border-[#D4AF37] flex items-center justify-center shadow-md">
                                <div class="w-4 h-4 rounded-full border border-[#D4AF37]/60"></div>
                            </div>
                        </div>
                        <div class="h-1/3 border border-[#8C5D36]/40 rounded-b-lg bg-[#2D1D11]/60 flex items-center justify-center text-[#D4AF37]/50 text-xl">⚜️</div>
                    </div>
                </div>

            </div>

            <!-- Bottom Guest Info & Trigger Button -->
            <div class="text-center z-20 space-y-4 max-w-sm">
                <div>
                    <p class="text-[11px] text-stone-400 font-sans tracking-wider">
                        Katur Dhumateng Panjenenganipun:
                    </p>
                    <h3 class="text-lg sm:text-xl font-bold text-amber-200 mt-1 font-serif">
                        {{ guestName || 'Tamu Undangan Yang Berbahagia' }}
                    </h3>
                </div>

                <button
                    @click="handleOpenGebyok"
                    class="px-8 py-3 rounded-full text-xs font-sans uppercase tracking-[0.25em] font-bold bg-gradient-to-r from-[#D4AF37] via-[#F4D06F] to-[#D4AF37] text-stone-950 shadow-xl shadow-amber-950/50 hover:scale-105 active:scale-95 transition-all cursor-pointer"
                    :disabled="isDoorOpening"
                >
                    {{ isDoorOpening ? 'Membuka Pintu Keraton...' : 'Buka Undangan' }}
                </button>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- 2. TRADITIONAL ROYAL PALACE CONTENT (When Opened)        -->
        <!-- ======================================================== -->
        <div class="max-w-2xl mx-auto px-4 sm:px-6 py-14 space-y-16">

            <!-- Floating Music Player Gamelan Disc -->
            <div class="fixed bottom-6 right-6 z-40">
                <button
                    @click="emit('toggleMusic')"
                    class="w-13 h-13 rounded-full bg-[#352112] border-2 border-[#D4AF37] shadow-2xl flex items-center justify-center text-xl text-[#D4AF37] hover:scale-110 transition-transform"
                    :class="{ 'animate-spin': isPlayingMusic }"
                    style="animation-duration: 5s;"
                    aria-label="Toggle Musik"
                >
                    <span v-if="isPlayingMusic">🎵</span>
                    <span v-else>🔇</span>
                </button>
            </div>

            <!-- Header Cover -->
            <header class="text-center space-y-4 border-b border-[#D4AF37]/20 pb-8">
                <div class="text-4xl text-[#D4AF37]">⛰️</div>
                <div class="text-xs uppercase tracking-[0.4em] text-[#D4AF37] font-sans font-semibold">
                    Serat Palakrama & Pawiwahan
                </div>
                <h1 class="text-4xl sm:text-6xl font-serif italic text-amber-100 font-bold leading-tight">
                    {{ weddingTitle }}
                </h1>
                <p class="text-sm sm:text-base text-stone-300 font-sans">
                    {{ weddingDate }}
                </p>

                <!-- Frame Photo with Javanese Golden Arch -->
                <div class="mt-6 relative max-w-md mx-auto aspect-[3/4] rounded-3xl overflow-hidden border-4 border-[#8C5D36] shadow-2xl">
                    <img :src="coverImg" :alt="weddingTitle" class="w-full h-full object-cover" />
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent flex items-end justify-center p-6">
                        <span class="text-xs tracking-widest uppercase text-amber-300 font-sans">Pawiwahan Ageng 2026</span>
                    </div>
                </div>
            </header>

            <!-- Salam & Doa Pembuka (Adat Jawa / Nusantara) -->
            <section class="p-8 rounded-3xl bg-[#25180F] border border-[#8C5D36]/40 text-center space-y-3">
                <div class="font-serif text-lg text-amber-200 italic">
                    Assalamu’alaikum Warahmatullahi Wabarakatuh
                </div>
                <p class="text-xs sm:text-sm text-stone-300 font-sans leading-relaxed max-w-lg mx-auto">
                    Kanthi nyuwun berkahing Gusti Ingkang Maha Agung, kula sakulawarga nyuwun lumunturing sih wilasa panjenengan sami, kersaa rawuh paring donga pamuji dhumateng dhauping putra-putri kula:
                </p>
            </section>

            <!-- Pasangan Mempelai (Adat Nusantara) -->
            <section class="space-y-8">
                <div class="text-center">
                    <div class="text-[10px] tracking-[0.3em] uppercase text-[#D4AF37] font-sans font-bold">
                        Panganten Kakung & Putri
                    </div>
                    <h2 class="text-3xl font-serif italic text-amber-100 font-bold mt-1">Dua Insan yang Berbahagia</h2>
                </div>

                <div class="grid sm:grid-cols-2 gap-6">
                    <!-- Pengantin Pria -->
                    <div class="p-6 rounded-3xl bg-[#22160D] border border-[#8C5D36]/50 text-center">
                        <div class="w-32 h-32 mx-auto rounded-full overflow-hidden border-2 border-[#D4AF37] shadow-xl mb-4">
                            <img :src="groom.photo || 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=400&q=80'" alt="Groom" class="w-full h-full object-cover" />
                        </div>
                        <h3 class="text-2xl font-serif italic text-amber-100 font-bold">{{ groom.fullName || groom.nickname || 'Raden Dimas' }}</h3>
                        <p class="text-xs text-stone-400 font-sans mt-2">{{ groom.parents || 'Putra Bpk. Ir. Suryadi & Ibu Desi' }}</p>
                        <div v-if="groom.instagram" class="mt-3 text-xs text-[#D4AF37] font-sans font-semibold">
                            {{ groom.instagram }}
                        </div>
                    </div>

                    <!-- Pengantin Wanita -->
                    <div class="p-6 rounded-3xl bg-[#22160D] border border-[#8C5D36]/50 text-center">
                        <div class="w-32 h-32 mx-auto rounded-full overflow-hidden border-2 border-[#D4AF37] shadow-xl mb-4">
                            <img :src="bride.photo || 'https://images.unsplash.com/photo-1517841905240-472988babdf9?auto=format&fit=crop&w=400&q=80'" alt="Bride" class="w-full h-full object-cover" />
                        </div>
                        <h3 class="text-2xl font-serif italic text-amber-100 font-bold">{{ bride.fullName || bride.nickname || 'Putri Anisa' }}</h3>
                        <p class="text-xs text-stone-400 font-sans mt-2">{{ bride.parents || 'Putri Bpk. Harun & Ibu Melly' }}</p>
                        <div v-if="bride.instagram" class="mt-3 text-xs text-[#D4AF37] font-sans font-semibold">
                            {{ bride.instagram }}
                        </div>
                    </div>
                </div>
            </section>

            <!-- Rangkaian Acara (Wanci Palakrama) -->
            <section class="space-y-6">
                <div class="text-center">
                    <div class="text-[10px] tracking-[0.3em] uppercase text-[#D4AF37] font-sans font-bold">
                        Wanci Palakrama
                    </div>
                    <h2 class="text-3xl font-serif italic text-amber-100 font-bold mt-1">Rangkaian Acara Adat</h2>
                </div>

                <div class="space-y-6">
                    <div
                        v-for="ev in events"
                        :key="ev.id"
                        class="p-8 rounded-3xl bg-[#24170E] border border-[#D4AF37]/40 text-center shadow-xl relative"
                    >
                        <div class="text-2xl text-[#D4AF37] mb-2">📜</div>
                        <h3 class="text-2xl font-serif italic text-amber-100 font-bold">{{ ev.name }}</h3>
                        <div class="my-3 py-2 border-y border-[#D4AF37]/20 inline-block px-6">
                            <p class="text-sm font-bold text-amber-300 font-sans">{{ safeFormatDisplayDate(ev.starts_at) }}</p>
                            <p class="text-xs text-stone-300 font-sans mt-0.5">{{ safeFormatEventTime(ev.starts_at, ev.ends_at, ev.timezone) }}</p>
                        </div>
                        <h4 class="text-base font-semibold text-white">{{ ev.venue_name }}</h4>
                        <p class="text-xs text-stone-400 mt-1 max-w-md mx-auto font-sans leading-relaxed">{{ ev.address }}</p>
                        <div class="mt-5">
                            <a
                                :href="ev.maps_url || 'https://maps.google.com'"
                                target="_blank"
                                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-full text-xs font-sans uppercase tracking-widest font-bold bg-[#D4AF37] hover:bg-[#F2CD5C] text-stone-950 shadow-md transition-transform hover:scale-105"
                            >
                                <span>📍 Petunjuk Lokasi Maps</span>
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Tanda Asih (Amplop Digital) -->
            <section v-if="digitalGift?.enabled" class="p-8 rounded-3xl bg-[#22160D] border border-[#8C5D36]/50 text-center space-y-6">
                <div class="text-[10px] tracking-[0.3em] uppercase text-[#D4AF37] font-sans font-bold">
                    Tanda Katresnan
                </div>
                <h2 class="text-3xl font-serif italic text-amber-100 font-bold">Kado & Hadiah Digital</h2>
                <p class="text-xs text-stone-300 font-sans max-w-md mx-auto">
                    Matur nuwun awit saking sih pasedherekan panjenengan sami. Bilih kepareng paring tanda tresna lumantar transfer:
                </p>

                <div class="grid sm:grid-cols-2 gap-4 max-w-lg mx-auto">
                    <div
                        v-for="(acc, idx) in digitalGift.accounts"
                        :key="idx"
                        class="p-5 rounded-2xl bg-[#170E08] border border-[#D4AF37]/30 text-left flex flex-col justify-between"
                    >
                        <div>
                            <div class="text-[10px] text-stone-400 font-sans uppercase">Bank Tujuan</div>
                            <div class="text-lg font-bold text-amber-200">{{ acc.bank }}</div>
                            <div class="text-sm font-mono text-white mt-1">{{ acc.account_number }}</div>
                            <div class="text-xs text-stone-400">a.n. {{ acc.account_name }}</div>
                        </div>
                        <button
                            @click="copyBankNumber(acc.account_number, idx)"
                            class="mt-4 w-full py-2 rounded-xl text-xs font-sans font-semibold bg-[#D4AF37]/20 hover:bg-[#D4AF37] text-amber-200 hover:text-stone-900 border border-[#D4AF37]/40 transition-colors"
                        >
                            {{ copiedBankIndex === idx ? '✓ Sampun Kaserat!' : 'Salin Nomer Rekening' }}
                        </button>
                    </div>
                </div>
            </section>

            <!-- Konfirmasi Kehadiran & Doa Restu -->
            <section class="p-8 rounded-3xl bg-[#24170E] border border-[#D4AF37]/40 space-y-6">
                <div class="text-center">
                    <div class="text-[10px] tracking-[0.3em] uppercase text-[#D4AF37] font-sans font-bold">
                        Buku Tamu
                    </div>
                    <h2 class="text-3xl font-serif italic text-amber-100 font-bold">Konfirmasi Rawuh & Donga</h2>
                </div>

                <form @submit.prevent="submitRsvp" class="space-y-4 max-w-lg mx-auto font-sans">
                    <div v-if="rsvpSuccess" class="p-4 rounded-xl bg-emerald-950/80 border border-emerald-500/40 text-emerald-200 text-xs text-center">
                        ✓ Matur nuwun sanget, konfirmasi rawuh panjenengan sampun katampi kanthi sae.
                    </div>
                    <div v-if="rsvpErrorMessage" class="p-4 rounded-xl bg-rose-950/80 border border-rose-500/40 text-rose-200 text-xs text-center">
                        {{ rsvpErrorMessage }}
                    </div>

                    <div>
                        <label class="block text-xs text-stone-300 mb-1">Asma Jangkep (Nama Lengkap)</label>
                        <input
                            v-model="rsvpForm.guest_name"
                            type="text"
                            required
                            placeholder="Serat asma panjenengan..."
                            class="w-full px-4 py-2.5 rounded-xl bg-[#170E08] border border-[#8C5D36] text-white text-xs focus:outline-none focus:border-[#D4AF37]"
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs text-stone-300 mb-1">Kepareng Rawuh</label>
                            <select
                                v-model="rsvpForm.status"
                                class="w-full px-3 py-2.5 rounded-xl bg-[#170E08] border border-[#8C5D36] text-white text-xs focus:outline-none focus:border-[#D4AF37]"
                            >
                                <option value="attending">Inggih, Badhe Rawuh</option>
                                <option value="declined">Nyuwun Pangapunten Mboten Saged</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-stone-300 mb-1">Cacahing Rawuh</label>
                            <input
                                v-model.number="rsvpForm.attendees_count"
                                type="number"
                                min="1"
                                max="10"
                                class="w-full px-4 py-2.5 rounded-xl bg-[#170E08] border border-[#8C5D36] text-white text-xs focus:outline-none focus:border-[#D4AF37]"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs text-stone-300 mb-1">Donga Pamuji (Doa & Ucapan)</label>
                        <textarea
                            v-model="rsvpForm.wishes"
                            rows="3"
                            placeholder="Serat donga pamuji kagem temanten kekalih..."
                            class="w-full px-4 py-2.5 rounded-xl bg-[#170E08] border border-[#8C5D36] text-white text-xs focus:outline-none focus:border-[#D4AF37]"
                        ></textarea>
                    </div>

                    <button
                        type="submit"
                        :disabled="rsvpSubmitting"
                        class="w-full py-3 rounded-xl bg-[#D4AF37] hover:bg-[#F2CD5C] text-stone-950 font-bold text-xs uppercase tracking-widest shadow-md transition-all disabled:opacity-50"
                    >
                        {{ rsvpSubmitting ? 'Ngirim...' : 'Kirim RSVP & Donga' }}
                    </button>
                </form>

                <!-- Daftar Ucapan -->
                <div class="mt-8 pt-6 border-t border-[#D4AF37]/20 max-w-lg mx-auto space-y-3 max-h-72 overflow-y-auto pr-2">
                    <div
                        v-for="gb in guestbook"
                        :key="gb.id"
                        class="p-4 rounded-xl bg-[#170E08] border border-[#8C5D36]/60 text-left font-sans"
                    >
                        <div class="flex items-center justify-between text-[11px]">
                            <span class="font-bold text-amber-200">{{ gb.guest_name }}</span>
                            <span class="text-stone-500 text-[10px]">{{ gb.created_at }}</span>
                        </div>
                        <p class="text-xs text-stone-300 mt-1 leading-relaxed">{{ gb.message }}</p>
                    </div>
                </div>
            </section>

            <!-- Penutup -->
            <footer class="text-center py-8 border-t border-[#D4AF37]/20 text-xs text-stone-500 font-sans">
                <p>Matur nuwun awit saking sih kadarman panjenengan sami.</p>
                <div class="text-[#D4AF37] font-serif italic text-lg mt-2">{{ weddingTitle }}</div>
            </footer>

        </div>

    </div>
</template>

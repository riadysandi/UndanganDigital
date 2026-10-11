<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import confetti from 'canvas-confetti';

const props = defineProps<{
    invitation: {
        id: number;
        title: string;
        slug: string;
        published_at: string;
    };
    template?: {
        id: number;
        name: string;
        slug: string;
        category: string;
        is_premium: boolean;
    } | null;
    design: any;
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
    guestName?: string | null;
    guestToken?: string | null;
}>();

// State
const isOpen = ref(false);
const isEnvelopeFlapOpen = ref(false);
const isSealBroken = ref(false);
const isLetterSlidOut = ref(false);
const isPlayingMusic = ref(false);
const audioEl = ref<HTMLAudioElement | null>(null);

// 3D Flip Card state for Agenda Acara
const flippedEvents = ref<Record<number, boolean>>({});
const toggleFlip = (eventId: number) => {
    flippedEvents.value[eventId] = !flippedEvents.value[eventId];
};

// Countdown timer state
const timeLeft = ref({ days: 0, hours: 0, minutes: 0, seconds: 0 });
let timerInterval: any = null;

// RSVP form state
const rsvpForm = ref({
    guest_name: props.guestName || '',
    attendance_status: 'attending',
    attendee_count: 1,
    message: '',
    guest_token: props.guestToken || '',
});
const rsvpSubmitting = ref(false);
const rsvpSuccess = ref(false);
const rsvpErrorMessage = ref('');

// Guestbook entries local list (to append newly submitted prayers)
const localGuestbook = ref([...props.guestbook]);

// Target wedding date for countdown
const weddingDateStr = computed(() => {
    if (props.events && props.events.length > 0) {
        return props.events[0].starts_at;
    }
    return '2026-12-28 08:00:00';
});

// Calculate countdown
const updateCountdown = () => {
    const target = new Date(weddingDateStr.value).getTime();
    const now = new Date().getTime();
    const diff = target - now;

    if (diff > 0) {
        timeLeft.value = {
            days: Math.floor(diff / (1000 * 60 * 60 * 24)),
            hours: Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)),
            minutes: Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60)),
            seconds: Math.floor((diff % (1000 * 60)) / 1000),
        };
    } else {
        timeLeft.value = { days: 0, hours: 0, minutes: 0, seconds: 0 };
    }
};

onMounted(() => {
    updateCountdown();
    timerInterval = setInterval(updateCountdown, 1000);
});

onBeforeUnmount(() => {
    if (timerInterval) clearInterval(timerInterval);
    if (audioEl.value) {
        audioEl.value.pause();
    }
});

// Play background music
const startAudio = () => {
    try {
        if (audioEl.value) {
            audioEl.value.play().then(() => {
                isPlayingMusic.value = true;
            }).catch(() => {
                isPlayingMusic.value = false;
            });
        }
    } catch (e) {
        // audio handling
    }
};

// 3D Envelope Opening Sequence
const handleOpenEnvelope = () => {
    if (isSealBroken.value) return;

    // Step 1: Break Wax Seal with pop
    isSealBroken.value = true;

    // Launch initial golden confetti
    confetti({
        particleCount: 40,
        spread: 60,
        origin: { y: 0.6 },
        colors: ['#D4AF37', '#F3E5AB', '#AA771C']
    });

    // Step 2: Flap rotates open in 3D
    setTimeout(() => {
        isEnvelopeFlapOpen.value = true;
    }, 300);

    // Step 3: Letter slides out with 3D depth
    setTimeout(() => {
        isLetterSlidOut.value = true;
        startAudio();
    }, 700);

    // Step 4: Big celebratory confetti and transition to main invitation
    setTimeout(() => {
        confetti({
            particleCount: 100,
            spread: 90,
            origin: { y: 0.5 },
            colors: ['#D4AF37', '#E11D48', '#FDA4AF', '#FFFFFF']
        });
        isOpen.value = true;
    }, 1800);
};

// Direct skip or instant open
const openDirectly = () => {
    isSealBroken.value = true;
    isEnvelopeFlapOpen.value = true;
    isLetterSlidOut.value = true;
    isOpen.value = true;
    startAudio();
    confetti({
        particleCount: 80,
        spread: 70,
        origin: { y: 0.5 }
    });
};

// Reopen 3D envelope modal
const reopenEnvelope = () => {
    isOpen.value = false;
    isSealBroken.value = false;
    isEnvelopeFlapOpen.value = false;
    isLetterSlidOut.value = false;
};

const toggleMusic = () => {
    if (!audioEl.value) return;
    if (isPlayingMusic.value) {
        audioEl.value.pause();
        isPlayingMusic.value = false;
    } else {
        audioEl.value.play();
        isPlayingMusic.value = true;
    }
};

// Submit RSVP
const submitRsvp = async () => {
    if (!rsvpForm.value.guest_name) {
        rsvpErrorMessage.value = 'Silakan masukkan nama Anda.';
        return;
    }

    rsvpSubmitting.value = true;
    rsvpErrorMessage.value = '';

    try {
        const res = await fetch(`/api/public/${props.invitation.slug}/rsvp`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as any)?.content || '',
                'Accept': 'application/json',
            },
            body: JSON.stringify(rsvpForm.value),
        });

        const data = await res.json();
        if (res.ok && data.success) {
            rsvpSuccess.value = true;
            confetti({
                particleCount: 120,
                spread: 80,
                origin: { y: 0.6 },
            });

            if (rsvpForm.value.message) {
                localGuestbook.value.unshift({
                    id: Date.now(),
                    guest_name: rsvpForm.value.guest_name,
                    message: rsvpForm.value.message,
                    created_at: 'Baru saja',
                });
            }
        } else {
            rsvpErrorMessage.value = data.message || 'Gagal mengirim konfirmasi RSVP.';
        }
    } catch (e) {
        rsvpErrorMessage.value = 'Terjadi kendala saat mengirim. Coba lagi.';
    } finally {
        rsvpSubmitting.value = false;
    }
};

// WhatsApp share
const shareWhatsApp = () => {
    const text = `Halo, kami mengundang Anda ke acara pernikahan kami melalui tautan undangan digital 3D berikut: ${window.location.href}`;
    window.open(`https://api.whatsapp.com/send?text=${encodeURIComponent(text)}`, '_blank');
};
</script>

<template>
    <div class="min-h-screen bg-[#0f1115] text-stone-100 font-sans selection:bg-amber-400 selection:text-stone-900">
        <!-- Optional Audio Element -->
        <audio
            ref="audioEl"
            loop
            src="https://actions.google.com/sounds/v1/ambient/relaxing_music.ogg"
        ></audio>

        <!-- Floating Controls (shown after opened) -->
        <div v-if="isOpen" class="fixed bottom-6 right-6 z-40 flex flex-col gap-3">
            <!-- Reopen 3D Envelope -->
            <button
                @click="reopenEnvelope"
                class="w-12 h-12 rounded-full bg-stone-900/90 backdrop-blur text-amber-300 flex items-center justify-center shadow-xl border border-amber-500/30 hover:scale-110 active:scale-95 transition-all group"
                title="Buka Amplop 3D Kembali"
            >
                <span class="text-lg group-hover:rotate-12 transition-transform">✉️</span>
            </button>

            <!-- Music Player -->
            <button
                @click="toggleMusic"
                class="w-12 h-12 rounded-full bg-stone-900/90 backdrop-blur text-white flex items-center justify-center shadow-xl border border-amber-500/30 hover:scale-110 active:scale-95 transition-all"
                :title="isPlayingMusic ? 'Jeda Musik' : 'Putar Musik'"
            >
                <span v-if="isPlayingMusic" class="animate-spin text-sm">🎵</span>
                <span v-else class="text-sm">🔇</span>
            </button>
        </div>

        <!-- ======================================================== -->
        <!-- 1. FULLSCREEN 3D INTERACTIVE ENVELOPE & WAX SEAL COVER    -->
        <!-- ======================================================== -->
        <transition name="envelope-fade">
            <div
                v-if="!isOpen"
                class="fixed inset-0 z-50 flex flex-col items-center justify-center bg-stone-950/95 backdrop-blur-xl p-4 overflow-y-auto"
            >
                <!-- Subtle Ambient Glow -->
                <div class="absolute inset-0 bg-radial from-amber-600/10 via-transparent to-transparent pointer-events-none"></div>

                <div class="relative z-10 w-full max-w-lg flex flex-col items-center">
                    <!-- Top Badge -->
                    <div class="mb-4 inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/10 border border-amber-500/30 text-amber-300 text-xs font-serif tracking-widest uppercase shadow-sm">
                        <span>✨</span>
                        <span>Royal 3D Wax Seal Edition</span>
                    </div>

                    <!-- Invitation Header Info -->
                    <div class="text-center mb-6">
                        <div class="text-xs uppercase tracking-[0.3em] text-amber-200/70 font-serif mb-1">Walimatul 'Ursy</div>
                        <h1 class="font-serif text-3xl sm:text-4xl text-amber-100 font-bold tracking-tight">Dimas & Anisa</h1>
                        <p class="text-xs text-stone-400 font-serif mt-1">Minggu, 28 Desember 2026</p>
                    </div>

                    <!-- 3D ENVELOPE STAGE -->
                    <div class="envelope-scene w-full flex items-center justify-center py-4 my-2">
                        <div class="envelope-container relative w-[320px] sm:w-[390px] h-[240px] select-none">
                            
                            <!-- Envelope Back Base -->
                            <div class="absolute inset-0 rounded-2xl bg-gradient-to-br from-[#241d17] to-[#18130f] border border-amber-500/30 shadow-[0_25px_50px_-12px_rgba(0,0,0,0.8)]">
                                <!-- Inner golden lining texture -->
                                <div class="absolute inset-2 rounded-xl border border-amber-500/15 bg-[#1f1813]/60"></div>
                            </div>

                            <!-- SLIDING 3D INVITATION LETTER CARD -->
                            <div
                                class="letter-card absolute inset-x-5 top-4 bottom-4 rounded-xl bg-gradient-to-b from-[#fbf8f2] to-[#f4ede2] text-stone-900 p-5 shadow-2xl border border-amber-200/80 flex flex-col justify-between items-center text-center transition-all duration-1000 ease-out"
                                :class="{ 'letter-slid-up': isLetterSlidOut }"
                            >
                                <div class="w-full">
                                    <div class="text-[9px] uppercase tracking-[0.25em] text-amber-800 font-serif font-bold">Undangan Pernikahan</div>
                                    <div class="font-serif text-xl sm:text-2xl font-bold text-stone-900 mt-1">Dimas & Anisa</div>
                                    <div class="w-8 h-0.5 bg-amber-600/40 mx-auto my-1.5"></div>
                                </div>

                                <div class="my-auto py-1">
                                    <div class="text-[10px] text-stone-500 uppercase tracking-wider mb-0.5">Spesial Untuk Yth:</div>
                                    <div class="text-sm sm:text-base font-serif font-bold text-stone-900 px-3 py-1 rounded-lg bg-amber-500/10 border border-amber-500/20">
                                        {{ guestName || 'Tamu Undangan Yang Berbahagia' }}
                                    </div>
                                    <div class="text-[10px] text-stone-500 mt-1">Keluarga Besar / Partner</div>
                                </div>

                                <button
                                    @click="openDirectly"
                                    class="w-full py-2 px-4 rounded-lg bg-stone-900 hover:bg-stone-800 text-amber-100 font-serif text-xs font-semibold shadow-md border border-amber-500/40 hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center justify-center gap-1.5"
                                >
                                    <span>Buka Selengkapnya</span>
                                    <span>→</span>
                                </button>
                            </div>

                            <!-- Envelope Pocket (Front Facade) -->
                            <div class="absolute inset-0 pointer-events-none rounded-2xl overflow-hidden z-20">
                                <!-- Pocket SVG with realistic fold shadows -->
                                <svg viewBox="0 0 390 240" class="w-full h-full preserve-3d">
                                    <!-- Left side flap -->
                                    <polygon points="0,0 0,240 195,145" fill="#201a14" opacity="0.95" />
                                    <!-- Right side flap -->
                                    <polygon points="390,0 390,240 195,145" fill="#1b1510" opacity="0.95" />
                                    <!-- Bottom flap -->
                                    <polygon points="0,240 390,240 195,115" fill="#282019" />
                                    <!-- Delicate gold border lines on bottom pocket -->
                                    <line x1="0" y1="240" x2="195" y2="115" stroke="#D4AF37" stroke-width="1" stroke-opacity="0.3" />
                                    <line x1="390" y1="240" x2="195" y2="115" stroke="#D4AF37" stroke-width="1" stroke-opacity="0.3" />
                                </svg>
                            </div>

                            <!-- Envelope Top Flap (3D Rotating Flap) -->
                            <div
                                class="envelope-flap absolute inset-x-0 top-0 h-[135px] origin-top transition-transform duration-700 ease-in-out z-30"
                                :class="{ 'flap-open': isEnvelopeFlapOpen }"
                            >
                                <svg viewBox="0 0 390 135" class="w-full h-full drop-shadow-xl">
                                    <polygon points="0,0 390,0 195,135" fill="#2e251d" />
                                    <!-- Golden flap rim -->
                                    <line x1="0" y1="0" x2="195" y2="135" stroke="#D4AF37" stroke-width="1.2" stroke-opacity="0.4" />
                                    <line x1="390" y1="0" x2="195" y2="135" stroke="#D4AF37" stroke-width="1.2" stroke-opacity="0.4" />
                                </svg>
                            </div>

                            <!-- 3D CRIMSON & GOLD WAX SEAL -->
                            <div
                                class="wax-seal-anchor absolute left-1/2 -translate-x-1/2 z-40 transition-all duration-500"
                                :class="[
                                    isEnvelopeFlapOpen ? 'top-[-50px] opacity-0 pointer-events-none' : 'top-[95px] opacity-100',
                                    isSealBroken ? 'scale-150 blur-sm opacity-0' : 'hover:scale-110 active:scale-95'
                                ]"
                            >
                                <button
                                    type="button"
                                    @click="handleOpenEnvelope"
                                    class="relative group focus:outline-none"
                                    title="Ketuk untuk membuka segel lilin 3D"
                                >
                                    <!-- Breathing glow pulse -->
                                    <div class="absolute -inset-2 rounded-full bg-amber-500/20 blur-md animate-pulse group-hover:bg-amber-400/40 transition-colors"></div>

                                    <!-- Realistic Wax Seal Stamp -->
                                    <div class="relative w-16 h-16 rounded-full bg-gradient-to-br from-amber-600 via-rose-900 to-red-950 border-2 border-amber-400/80 shadow-[0_10px_25px_rgba(0,0,0,0.8),inset_0_2px_4px_rgba(255,255,255,0.4)] flex items-center justify-center text-amber-200 font-serif">
                                        <!-- Seal rim ring -->
                                        <div class="absolute inset-1.5 rounded-full border border-dashed border-amber-400/50"></div>
                                        
                                        <!-- Monogram -->
                                        <div class="text-center">
                                            <div class="font-serif font-black text-sm tracking-tighter leading-none drop-shadow-md text-amber-200">
                                                D & A
                                            </div>
                                            <div class="text-[7px] tracking-widest text-amber-300/80 uppercase">
                                                2026
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Ribbon tails -->
                                    <div class="absolute -bottom-2 left-1/2 -translate-x-1/2 flex gap-1 pointer-events-none opacity-80">
                                        <div class="w-2.5 h-4 bg-red-950 border-r border-amber-500/30 transform -rotate-12 rounded-b"></div>
                                        <div class="w-2.5 h-4 bg-red-950 border-l border-amber-500/30 transform rotate-12 rounded-b"></div>
                                    </div>
                                </button>
                            </div>

                        </div>
                    </div>

                    <!-- Call To Action Hint -->
                    <div class="text-center mt-4 space-y-3">
                        <button
                            @click="handleOpenEnvelope"
                            class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-stone-950 font-bold text-xs uppercase tracking-wider shadow-lg shadow-amber-500/20 hover:scale-105 active:scale-95 transition-all flex items-center gap-2 mx-auto"
                        >
                            <span>✉️</span>
                            <span>Buka Undangan 3D</span>
                        </button>
                        <p class="text-[11px] text-stone-400 font-serif">
                            Sentuh segel lilin emas di atas untuk membuka amplop secara interaktif.
                        </p>
                    </div>

                </div>
            </div>
        </transition>

        <!-- ======================================================== -->
        <!-- 2. MAIN INVITATION CONTENT (Scrollable, mobile-optimized) -->
        <!-- ======================================================== -->
        <div class="max-w-xl mx-auto min-h-screen bg-[#14171c] shadow-2xl flex flex-col relative border-x border-stone-800/80">
            
            <!-- Hero Cover Header with 3D Depth Card -->
            <header class="relative min-h-[90vh] flex flex-col items-center justify-center text-center p-8 bg-gradient-to-b from-[#181b22] via-[#14171c] to-[#121418] overflow-hidden">
                <!-- Background decorative rings -->
                <div class="absolute w-96 h-96 rounded-full border border-amber-500/10 pointer-events-none animate-pulse"></div>
                <div class="absolute w-[480px] h-[480px] rounded-full border border-amber-500/5 pointer-events-none"></div>

                <div class="text-[11px] font-serif tracking-[0.35em] uppercase text-amber-400 mb-3 font-semibold">
                    The Wedding Celebration
                </div>
                
                <h1 class="font-serif text-5xl sm:text-6xl text-amber-100 my-3 leading-tight tracking-tight drop-shadow-md">
                    Dimas & Anisa
                </h1>

                <div class="flex items-center justify-center gap-3 my-4">
                    <div class="w-12 h-px bg-gradient-to-r from-transparent to-amber-500/60"></div>
                    <span class="text-amber-400 text-xs">✤</span>
                    <div class="w-12 h-px bg-gradient-to-l from-transparent to-amber-500/60"></div>
                </div>

                <p class="font-serif text-xs text-amber-200/80 tracking-widest mb-8 uppercase">
                    Minggu, 28 Desember 2026
                </p>

                <!-- Hero Photo with 3D Border Glow -->
                <div class="relative w-64 h-80 rounded-t-full rounded-b-3xl overflow-hidden border-2 border-amber-500/40 shadow-[0_20px_50px_rgba(0,0,0,0.8)] mx-auto mb-6 transform hover:scale-[1.02] transition-transform duration-500">
                    <img
                        src="https://images.unsplash.com/photo-1519741497674-611481863552?auto=format&fit=crop&w=800&q=80"
                        alt="Dimas & Anisa"
                        class="w-full h-full object-cover"
                    />
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
                </div>

                <div class="text-xs text-stone-400 font-serif italic max-w-xs leading-relaxed">
                    "Dan di antara tanda-tanda kebesaran-Nya ialah Dia menciptakan pasangan-pasangan untukmu dari jenismu sendiri, agar kamu cenderung dan merasa tenteram kepadanya."
                </div>
            </header>

            <!-- Couple Section -->
            <section class="py-16 px-6 text-center bg-[#161a20] border-y border-stone-800/80">
                <div class="inline-block px-3 py-1 rounded-full text-[10px] font-bold tracking-widest uppercase bg-amber-500/10 text-amber-400 border border-amber-500/20 mb-3">
                    Mempelai Pengantin
                </div>
                <h2 class="font-serif text-2xl font-bold text-amber-100 mb-2">Pasangan Bahagia</h2>
                <p class="text-xs text-stone-400 max-w-sm mx-auto mb-10 leading-relaxed">
                    Dengan memohon rahmat dan ridho Allah SWT, kami bermaksud menyelenggarakan pernikahan kami:
                </p>

                <div class="space-y-10">
                    <!-- Groom -->
                    <div class="flex flex-col items-center">
                        <div class="w-32 h-32 rounded-full overflow-hidden border-2 border-amber-500/40 shadow-xl mb-4 p-1 bg-amber-500/10">
                            <img
                                src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=400&q=80"
                                alt="Groom"
                                class="w-full h-full object-cover rounded-full"
                            />
                        </div>
                        <h3 class="font-serif font-bold text-xl text-amber-100">Dimas Prasetyo, S.Kom</h3>
                        <p class="text-xs text-stone-400 mt-1 max-w-xs">
                            Putra pertama dari Bpk. Bambang Prasetyo & Ibu Sri Wahyuni
                        </p>
                    </div>

                    <div class="font-serif text-3xl text-amber-400 font-light">&</div>

                    <!-- Bride -->
                    <div class="flex flex-col items-center">
                        <div class="w-32 h-32 rounded-full overflow-hidden border-2 border-amber-500/40 shadow-xl mb-4 p-1 bg-amber-500/10">
                            <img
                                src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=400&q=80"
                                alt="Bride"
                                class="w-full h-full object-cover rounded-full"
                            />
                        </div>
                        <h3 class="font-serif font-bold text-xl text-amber-100">Anisa Rahmawati, S.E</h3>
                        <p class="text-xs text-stone-400 mt-1 max-w-xs">
                            Putri kedua dari Bpk. H. Rahmat Hidayat & Ibu Siti Aminah
                        </p>
                    </div>
                </div>
            </section>

            <!-- Countdown Timer with 3D Glass Cards -->
            <section class="py-12 px-6 text-center bg-[#101216] border-b border-stone-800">
                <h3 class="text-xs uppercase tracking-widest text-amber-400 font-serif mb-6">Menghitung Hari Bahagia</h3>
                <div class="grid grid-cols-4 gap-3 max-w-xs mx-auto">
                    <div class="p-3 bg-white/5 backdrop-blur rounded-2xl border border-amber-500/20 shadow-lg">
                        <div class="font-serif text-2xl font-bold text-amber-200">{{ timeLeft.days }}</div>
                        <div class="text-[9px] uppercase tracking-wider text-stone-400 mt-1">Hari</div>
                    </div>
                    <div class="p-3 bg-white/5 backdrop-blur rounded-2xl border border-amber-500/20 shadow-lg">
                        <div class="font-serif text-2xl font-bold text-amber-200">{{ timeLeft.hours }}</div>
                        <div class="text-[9px] uppercase tracking-wider text-stone-400 mt-1">Jam</div>
                    </div>
                    <div class="p-3 bg-white/5 backdrop-blur rounded-2xl border border-amber-500/20 shadow-lg">
                        <div class="font-serif text-2xl font-bold text-amber-200">{{ timeLeft.minutes }}</div>
                        <div class="text-[9px] uppercase tracking-wider text-stone-400 mt-1">Menit</div>
                    </div>
                    <div class="p-3 bg-white/5 backdrop-blur rounded-2xl border border-amber-500/20 shadow-lg">
                        <div class="font-serif text-2xl font-bold text-amber-200">{{ timeLeft.seconds }}</div>
                        <div class="text-[9px] uppercase tracking-wider text-stone-400 mt-1">Detik</div>
                    </div>
                </div>
            </section>

            <!-- ======================================================== -->
            <!-- 3. AGENDA ACARA DENGAN 3D INTERACTIVE FLIP CARDS          -->
            <!-- ======================================================== -->
            <section class="py-16 px-6 bg-[#14171c] space-y-6">
                <div class="text-center mb-8">
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold tracking-widest uppercase bg-amber-500/10 text-amber-400 border border-amber-500/20 mb-2">
                        <span>🔄</span>
                        <span>Fitur 3D Flip Card</span>
                    </div>
                    <h2 class="font-serif text-2xl font-bold text-amber-100">Agenda & Lokasi Acara</h2>
                    <p class="text-xs text-stone-400 mt-1">Ketuk kartu acara di bawah untuk membalik kartu (3D Flip) dan melihat rute maps:</p>
                </div>

                <div class="space-y-6">
                    <div
                        v-for="event in events"
                        :key="event.id"
                        class="flip-card-wrapper w-full h-[290px] cursor-pointer select-none"
                        @click="toggleFlip(event.id)"
                    >
                        <div
                            class="flip-card-inner relative w-full h-full transition-transform duration-700 ease-out"
                            :class="{ 'is-flipped': flippedEvents[event.id] }"
                        >
                            <!-- FRONT OF CARD (Event overview) -->
                            <div class="flip-card-front absolute inset-0 w-full h-full p-6 rounded-3xl bg-gradient-to-br from-[#1d222b] to-[#15181f] border border-amber-500/30 shadow-2xl flex flex-col justify-between items-center text-center">
                                <div class="w-full flex items-center justify-between">
                                    <span class="px-3 py-1 rounded-full text-[10px] font-bold tracking-wider uppercase bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                        {{ event.name }}
                                    </span>
                                    <span class="text-xs text-amber-400/80 font-mono">🔄 Balik 3D</span>
                                </div>

                                <div class="space-y-2 my-auto">
                                    <div class="text-2xl">💍</div>
                                    <div class="font-serif text-xl font-bold text-amber-100">
                                        Minggu, 28 Desember 2026
                                    </div>
                                    <div class="text-xs text-amber-300 font-medium">
                                        Pukul 08:00 - Selesai {{ event.timezone }}
                                    </div>
                                    <div class="text-xs text-stone-300 pt-1">
                                        {{ event.venue_name }}
                                    </div>
                                </div>

                                <div class="w-full pt-3 border-t border-stone-800 text-[11px] text-amber-400/90 font-medium flex items-center justify-center gap-1.5">
                                    <span>👉 Ketuk kartu untuk lihat Peta & Rute</span>
                                    <span>🔄</span>
                                </div>
                            </div>

                            <!-- BACK OF CARD (Address & Navigation Maps) -->
                            <div class="flip-card-back absolute inset-0 w-full h-full p-6 rounded-3xl bg-gradient-to-br from-[#161a22] to-[#0f1217] border border-amber-400/50 shadow-2xl flex flex-col justify-between items-center text-center">
                                <div class="w-full flex items-center justify-between">
                                    <span class="text-[10px] uppercase tracking-wider font-bold text-amber-400">Lokasi Acara</span>
                                    <span class="text-xs text-amber-400/80 font-mono">🔄 Balik Depan</span>
                                </div>

                                <div class="space-y-2 my-auto px-2">
                                    <div class="text-xl">📍</div>
                                    <div class="font-serif text-base font-bold text-amber-100">
                                        {{ event.venue_name }}
                                    </div>
                                    <p class="text-xs text-stone-300 leading-relaxed">
                                        {{ event.address || 'Alamat lengkap lokasi acara' }}
                                    </p>
                                </div>

                                <div class="w-full flex items-center gap-2 pt-2" @click.stop>
                                    <a
                                        v-if="event.maps_url"
                                        :href="event.maps_url"
                                        target="_blank"
                                        class="flex-1 py-2 px-3 rounded-xl bg-amber-500 hover:bg-amber-400 text-stone-950 font-bold text-xs shadow-md transition-colors flex items-center justify-center gap-1.5"
                                    >
                                        <span>🗺️</span>
                                        <span>Buka Google Maps</span>
                                    </a>
                                    <button
                                        type="button"
                                        @click="toggleFlip(event.id)"
                                        class="py-2 px-3 rounded-xl bg-stone-800 hover:bg-stone-700 text-stone-300 text-xs font-semibold border border-stone-700"
                                    >
                                        🔄 Balik
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Photo Gallery -->
            <section class="py-16 px-6 bg-[#161a20] border-t border-stone-800 text-center">
                <h2 class="font-serif text-2xl font-bold text-amber-100 mb-2">Galeri Momen</h2>
                <p class="text-xs text-stone-400 mb-8">Potret cinta dan kebahagiaan perjalanan kami.</p>

                <div class="grid grid-cols-2 gap-3">
                    <div class="rounded-2xl overflow-hidden shadow-lg h-48 border border-stone-800 group">
                        <img
                            src="https://images.unsplash.com/photo-1511285560929-80b456fea0bc?auto=format&fit=crop&w=600&q=80"
                            alt="Gallery 1"
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                        />
                    </div>
                    <div class="rounded-2xl overflow-hidden shadow-lg h-48 border border-stone-800 group">
                        <img
                            src="https://images.unsplash.com/photo-1583939003579-730e3918a45a?auto=format&fit=crop&w=600&q=80"
                            alt="Gallery 2"
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                        />
                    </div>
                    <div class="rounded-2xl overflow-hidden shadow-lg h-48 border border-stone-800 group">
                        <img
                            src="https://images.unsplash.com/photo-1520854221256-17451cc331bf?auto=format&fit=crop&w=600&q=80"
                            alt="Gallery 3"
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                        />
                    </div>
                    <div class="rounded-2xl overflow-hidden shadow-lg h-48 border border-stone-800 group">
                        <img
                            src="https://images.unsplash.com/photo-1465495976277-4387d4b0b4c6?auto=format&fit=crop&w=600&q=80"
                            alt="Gallery 4"
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                        />
                    </div>
                </div>
            </section>

            <!-- RSVP Form Section -->
            <section class="py-16 px-6 bg-[#14171c] border-t border-stone-800">
                <div class="text-center mb-8">
                    <h2 class="font-serif text-2xl font-bold text-amber-100">Konfirmasi Kehadiran</h2>
                    <p class="text-xs text-stone-400 mt-1">Mohon konfirmasikan kehadiran Anda pada form di bawah ini:</p>
                </div>

                <div v-if="rsvpSuccess" class="p-6 bg-amber-500/10 rounded-2xl border border-amber-500/30 text-center space-y-2">
                    <div class="text-2xl">🎉</div>
                    <div class="font-serif font-bold text-amber-200 text-lg">Terima Kasih!</div>
                    <p class="text-xs text-stone-300">
                        Konfirmasi kehadiran dan doa restu Anda telah berhasil kami simpan.
                    </p>
                </div>

                <form v-else @submit.prevent="submitRsvp" class="space-y-4 max-w-sm mx-auto">
                    <div>
                        <label class="block text-xs font-semibold text-stone-300 mb-1">Nama Lengkap *</label>
                        <input
                            v-model="rsvpForm.guest_name"
                            type="text"
                            required
                            class="w-full p-3 rounded-xl border border-stone-700 text-xs bg-stone-900 text-white placeholder-stone-500 focus:bg-stone-800 focus:outline-none focus:ring-2 focus:ring-amber-500"
                            placeholder="Nama Tamu"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-stone-300 mb-1">Status Kehadiran *</label>
                        <select
                            v-model="rsvpForm.attendance_status"
                            class="w-full p-3 rounded-xl border border-stone-700 text-xs bg-stone-900 text-white focus:bg-stone-800"
                        >
                            <option value="attending">Ya, Saya Akan Hadir</option>
                            <option value="declined">Maaf, Berhalangan Hadir</option>
                            <option value="tentative">Masih Belum Pasti</option>
                        </select>
                    </div>

                    <div v-if="rsvpForm.attendance_status === 'attending'">
                        <label class="block text-xs font-semibold text-stone-300 mb-1">Jumlah Tamu Hadir</label>
                        <select
                            v-model.number="rsvpForm.attendee_count"
                            class="w-full p-3 rounded-xl border border-stone-700 text-xs bg-stone-900 text-white focus:bg-stone-800"
                        >
                            <option :value="1">1 Orang</option>
                            <option :value="2">2 Orang</option>
                            <option :value="3">3 Orang</option>
                            <option :value="4">4 Orang</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-stone-300 mb-1">Ucapan & Doa Restu</label>
                        <textarea
                            v-model="rsvpForm.message"
                            rows="3"
                            class="w-full p-3 rounded-xl border border-stone-700 text-xs bg-stone-900 text-white placeholder-stone-500 focus:bg-stone-800 focus:outline-none focus:ring-2 focus:ring-amber-500"
                            placeholder="Tulis ucapan selamat..."
                        ></textarea>
                    </div>

                    <div v-if="rsvpErrorMessage" class="text-xs text-rose-400 bg-rose-950/60 border border-rose-800 p-2.5 rounded-xl">
                        {{ rsvpErrorMessage }}
                    </div>

                    <button
                        type="submit"
                        :disabled="rsvpSubmitting"
                        class="w-full py-3.5 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-stone-950 rounded-xl text-xs font-bold uppercase tracking-wider shadow-lg shadow-amber-500/20 transition-all disabled:opacity-50"
                    >
                        {{ rsvpSubmitting ? 'Mengirim...' : 'Kirim Konfirmasi Kehadiran' }}
                    </button>
                </form>
            </section>

            <!-- Guestbook / Wishes Wall -->
            <section class="py-16 px-6 bg-[#161a20] border-t border-stone-800">
                <div class="text-center mb-8">
                    <h2 class="font-serif text-2xl font-bold text-amber-100">Buku Tamu & Ucapan</h2>
                    <p class="text-xs text-stone-400 mt-1">Doa restu hangat dari para sahabat dan keluarga:</p>
                </div>

                <div class="space-y-3 max-w-sm mx-auto">
                    <div
                        v-for="entry in localGuestbook"
                        :key="entry.id"
                        class="p-4 rounded-2xl bg-[#1c212a] border border-stone-800 shadow-md text-left"
                    >
                        <div class="flex items-center justify-between mb-1">
                            <span class="font-bold text-xs text-amber-200">{{ entry.guest_name }}</span>
                            <span class="text-[10px] text-stone-500">{{ entry.created_at }}</span>
                        </div>
                        <p class="text-xs text-stone-300 leading-relaxed">{{ entry.message }}</p>
                    </div>

                    <div v-if="localGuestbook.length === 0" class="text-center text-xs text-stone-500 py-6">
                        Belum ada ucapan. Jadilah yang pertama memberikan doa restu!
                    </div>
                </div>
            </section>

            <!-- WhatsApp Share & Footer -->
            <footer class="py-12 px-6 bg-[#0f1115] text-center text-white space-y-4 border-t border-stone-800">
                <button
                    @click="shareWhatsApp"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold shadow-md transition-colors"
                >
                    <span>💬</span> Bagikan ke WhatsApp
                </button>

                <div class="text-xs text-stone-400 pt-4 border-t border-stone-800/80">
                    <p>Merupakan suatu kehormatan & kebahagiaan bagi kami apabila Anda berkenan hadir.</p>
                    <div class="font-serif text-2xl text-amber-300 mt-2 font-bold">Dimas & Anisa</div>
                </div>

                <div class="text-[10px] text-stone-600 pt-4">
                    Platform Undangan Digital &bull; Efek 3D Wax Seal Interaktif
                </div>
            </footer>
        </div>
    </div>
</template>

<style scoped>
/* 3D Envelope Perspectives */
.envelope-scene {
    perspective: 1200px;
}
.preserve-3d {
    transform-style: preserve-3d;
}

/* 3D Envelope Flap Folding Animation */
.envelope-flap {
    transform-style: preserve-3d;
    transform: rotateX(0deg);
}
.envelope-flap.flap-open {
    transform: rotateX(180deg);
    z-index: 10 !important;
}

/* 3D Sliding Letter Animation */
.letter-card {
    transform-style: preserve-3d;
    transform: translateY(0) translateZ(0);
    z-index: 15;
}
.letter-card.letter-slid-up {
    transform: translateY(-135px) translateZ(50px) scale(1.02);
    z-index: 35 !important;
    box-shadow: 0 30px 60px -12px rgba(0, 0, 0, 0.9);
}

/* 3D Flip Card for Events */
.flip-card-wrapper {
    perspective: 1200px;
}
.flip-card-inner {
    transform-style: preserve-3d;
    position: relative;
}
.flip-card-inner.is-flipped {
    transform: rotateY(180deg);
}
.flip-card-front,
.flip-card-back {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    -webkit-backface-visibility: hidden;
    backface-visibility: hidden;
}
.flip-card-back {
    transform: rotateY(180deg);
}

/* Transitions */
.envelope-fade-enter-active,
.envelope-fade-leave-active {
    transition: opacity 0.8s ease;
}
.envelope-fade-enter-from,
.envelope-fade-leave-to {
    opacity: 0;
}
</style>

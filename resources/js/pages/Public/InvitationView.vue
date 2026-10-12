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

const liveDesignOverride = ref<any>(null);

const onMessage = (event: MessageEvent) => {
    if (event.data && event.data.type === 'LIVE_UPDATE_DESIGN') {
        liveDesignOverride.value = event.data.design;
    }
};

// Formatter to turn any raw ISO date or standard date into elegant Indonesian format
const formatDisplayDate = (val: string) => {
    if (!val) return 'Minggu, 28 Desember 2026';
    if (/[a-zA-Z]{3,}/.test(val) && !val.includes('T') && !val.includes('Z')) {
        return val;
    }
    try {
        const d = new Date(val);
        if (isNaN(d.getTime())) return val;
        return d.toLocaleDateString('id-ID', {
            weekday: 'long',
            day: 'numeric',
            month: 'long',
            year: 'numeric'
        });
    } catch {
        return val;
    }
};

const formatDisplayTime = (val: string) => {
    if (!val) return '08:00';
    try {
        const d = new Date(val);
        if (isNaN(d.getTime())) {
            const match = val.match(/(\d{2}:\d{2})/);
            return match ? match[1] : '08:00';
        }
        const h = d.getHours().toString().padStart(2, '0');
        const m = d.getMinutes().toString().padStart(2, '0');
        return `${h}:${m}`;
    } catch {
        return '08:00';
    }
};

// Dynamic Invitation Data resolution (combining props.design with defaults)
const invitationData = computed(() => {
    const d = liveDesignOverride.value || props.design || {};
    const content = d.content || {};
    const theme = d.theme || {};
    const firstEvent = props.events && props.events.length > 0 ? props.events[0] : null;

    return {
        theme: {
            palette: theme.palette || 'gold',
            primaryColor: theme.primaryColor || '#D4AF37',
            secondaryColor: theme.secondaryColor || '#F3E5AB',
            bgColor: theme.bgColor || '#14171c',
            cardBgColor: theme.cardBgColor || '#1a1f26',
            textColor: theme.textColor || '#f3f4f6',
            fontFamily: theme.fontFamily || 'Playfair Display',
        },
        content: {
            coverBadge: content.coverBadge || 'Royal 3D Wax Seal Edition',
            coverSubtitle: content.coverSubtitle || "Walimatul 'Ursy",
            weddingTitle: content.weddingTitle || props.invitation?.title || 'Dimas & Anisa',
            weddingDateText: formatDisplayDate(content.weddingDateText || (firstEvent ? firstEvent.starts_at : '2026-12-28')),
            coverImage: content.coverImage || 'https://images.unsplash.com/photo-1519741497674-611481863552?auto=format&fit=crop&w=800&q=80',
            quote: content.quote || 'Dan di antara tanda-tanda kebesaran-Nya ialah Dia menciptakan pasangan-pasangan untukmu dari jenismu sendiri, agar kamu cenderung dan merasa tenteram kepadanya, dan dijadikan-Nya di antaramu rasa kasih dan sayang.',
            quoteSource: content.quoteSource || 'QS. Ar-Rum: 21',
            groom: {
                nickname: content.groom?.nickname || 'Dimas',
                fullName: content.groom?.fullName || 'Dimas Prasetyo, S.Kom',
                parents: content.groom?.parents || 'Putra pertama dari Bpk. Bambang Prasetyo & Ibu Sri Wahyuni',
                photo: content.groom?.photo || 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=400&q=80',
                instagram: content.groom?.instagram || '@dimasprasetyo',
            },
            bride: {
                nickname: content.bride?.nickname || 'Anisa',
                fullName: content.bride?.fullName || 'Anisa Rahmawati, S.E',
                parents: content.bride?.parents || 'Putri kedua dari Bpk. H. Rahmat Hidayat & Ibu Siti Aminah',
                photo: content.bride?.photo || 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=400&q=80',
                instagram: content.bride?.instagram || '@anisarahmawati',
            },
            gallery: (content.gallery && Array.isArray(content.gallery) && content.gallery.length === 4)
                ? content.gallery
                : [
                    'https://images.unsplash.com/photo-1511285560929-80b456fea0bc?auto=format&fit=crop&w=600&q=80',
                    'https://images.unsplash.com/photo-1583939003579-730e3918a45a?auto=format&fit=crop&w=600&q=80',
                    'https://images.unsplash.com/photo-1520854221256-17451cc331bf?auto=format&fit=crop&w=600&q=80',
                    'https://images.unsplash.com/photo-1465495976277-4387d4b0b4c6?auto=format&fit=crop&w=600&q=80',
                ],
        }
    };
});

// Dynamic Wax Seal monogram letters (e.g. "D & A")
const waxSealMonogram = computed(() => {
    const g = invitationData.value.content.groom.nickname?.[0] || 'D';
    const b = invitationData.value.content.bride.nickname?.[0] || 'A';
    return `${g.toUpperCase()} & ${b.toUpperCase()}`;
});

// Dynamic Wax Seal Year (e.g. "2026")
const waxSealYear = computed(() => {
    const dt = invitationData.value.content.weddingDateText;
    const match = dt.match(/\b(20\d\d)\b/);
    return match ? match[1] : '2026';
});

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
    if (props.events && props.events.length > 0 && props.events[0].starts_at) {
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
    window.addEventListener('message', onMessage);
    updateCountdown();
    timerInterval = setInterval(updateCountdown, 1000);
});

onBeforeUnmount(() => {
    window.removeEventListener('message', onMessage);
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
        colors: [invitationData.value.theme.primaryColor, invitationData.value.theme.secondaryColor, '#AA771C']
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
            colors: [invitationData.value.theme.primaryColor, '#E11D48', '#FDA4AF', '#FFFFFF']
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
    const text = `Halo, kami mengundang Anda ke acara pernikahan ${invitationData.value.content.weddingTitle} melalui tautan undangan digital 3D berikut: ${window.location.href}`;
    window.open(`https://api.whatsapp.com/send?text=${encodeURIComponent(text)}`, '_blank');
};

// Background Music Source
const musicSource = computed(() => {
    return props.design?.musicUrl || 'https://actions.google.com/sounds/v1/ambient/relaxing_music.ogg';
});

// Guest QR Code for Reception Check-in
const guestQrCodeUrl = computed(() => {
    const token = props.guestToken || props.invitation.slug;
    return `https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=${encodeURIComponent(token)}`;
});

// Google Calendar Integration
const getGoogleCalendarUrl = (event: any) => {
    const title = `${event.name}: ${invitationData.value.content.weddingTitle}`;
    const cleanDate = (dateStr: string) => {
        try {
            const d = new Date(dateStr);
            return d.toISOString().replace(/-|:|\.\d\d\d/g, "");
        } catch {
            return '20261228T080000Z';
        }
    };
    const startDate = cleanDate(event.starts_at);
    let endDate = startDate;
    try {
        if (event.ends_at) {
            endDate = cleanDate(event.ends_at);
        } else {
            const d = new Date(event.starts_at);
            d.setHours(d.getHours() + 3);
            endDate = cleanDate(d.toISOString());
        }
    } catch {
        endDate = startDate;
    }
    const details = `Undangan Pernikahan: ${invitationData.value.content.weddingTitle}\nAcara: ${event.name}\nLokasi: ${event.venue_name}\nAlamat: ${event.address || ''}\nBuka Undangan: ${window.location.href}`;
    const location = `${event.venue_name}, ${event.address || ''}`;

    return `https://calendar.google.com/calendar/render?action=TEMPLATE&text=${encodeURIComponent(title)}&dates=${startDate}/${endDate}&details=${encodeURIComponent(details)}&location=${encodeURIComponent(location)}`;
};

// Amplop Digital & Kado Hadiah Data
const copiedBankIndex = ref<number | null>(null);
const copyAccountNumber = (accNumber: string, index: number) => {
    navigator.clipboard.writeText(accNumber);
    copiedBankIndex.value = index;
    setTimeout(() => {
        copiedBankIndex.value = null;
    }, 2500);
};

const copiedAddressToast = ref(false);
const copyAddress = (address: string) => {
    navigator.clipboard.writeText(address);
    copiedAddressToast.value = true;
    setTimeout(() => {
        copiedAddressToast.value = false;
    }, 2500);
};

const showQrisModal = ref(false);
const activeQris = ref<any>(null);
const openQris = (account: any) => {
    activeQris.value = account;
    showQrisModal.value = true;
};

const digitalGift = computed(() => {
    return props.design?.gift || {
        enabled: true,
        title: 'Amplop Digital & Kado Nikah',
        description: 'Doa restu Anda adalah karunia terindah bagi kami. Namun jika memberi adalah ungkapan kasih, Anda dapat menyalurkannya melalui cashless digital gift di bawah ini:',
        accounts: [
            {
                bank: 'BCA',
                account_number: '8210984721',
                account_name: 'Dimas Prasetyo',
                qr_image: 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=00020101021226590014ID.LINKAJA.WWW01189360091800000000005204581253033605802ID5914DIMAS_PRASETYO6007JAKARTA63041234',
            },
            {
                bank: 'Bank Mandiri',
                account_number: '1370019283741',
                account_name: 'Anisa Rahmawati',
                qr_image: 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=00020101021226590014ID.LINKAJA.WWW01189360091800000000005204581253033605802ID5915ANISA_RAHMAWATI6007JAKARTA63045678',
            },
        ],
        physical_gift: {
            enabled: true,
            receiver_name: 'Dimas & Anisa',
            phone: '0812-3456-7890',
            address: 'Jl. Melati Indah No. 42, RT 03/RW 05, Cilandak Barat, Jakarta Selatan 12430',
        },
    };
});
</script>

<template>
    <div
        class="min-h-screen selection:bg-amber-400 selection:text-stone-900 transition-colors duration-500"
        :style="{
            backgroundColor: invitationData.theme.bgColor,
            fontFamily: invitationData.theme.fontFamily,
            color: invitationData.theme.textColor,
        }"
    >
        <!-- Audio Element -->
        <audio
            ref="audioEl"
            loop
            :src="musicSource"
        ></audio>

        <!-- Floating Controls (shown after opened) -->
        <div v-if="isOpen" class="fixed bottom-6 right-6 z-40 flex flex-col items-center gap-3">
            <!-- Reopen 3D Envelope -->
            <button
                @click="reopenEnvelope"
                class="w-12 h-12 rounded-full bg-stone-900/90 backdrop-blur flex items-center justify-center shadow-xl border hover:scale-110 active:scale-95 transition-all group"
                :style="{
                    borderColor: invitationData.theme.primaryColor + '60',
                    color: invitationData.theme.secondaryColor
                }"
                title="Buka Amplop 3D Kembali"
            >
                <span class="text-lg group-hover:rotate-12 transition-transform">✉️</span>
            </button>

            <!-- Floating Spinning Vinyl Music Player -->
            <button
                @click="toggleMusic"
                class="relative group focus:outline-none"
                :title="isPlayingMusic ? 'Jeda Musik Latar' : 'Putar Musik Latar'"
            >
                <!-- Outer Pulse Glow when playing -->
                <div
                    v-if="isPlayingMusic"
                    class="absolute -inset-1 rounded-full blur-sm animate-pulse"
                    :style="{ backgroundColor: invitationData.theme.primaryColor + '66' }"
                ></div>

                <!-- Realistic Vinyl Record Disk -->
                <div
                    class="w-12 h-12 rounded-full bg-gradient-to-tr from-stone-950 via-[#1a1c22] to-stone-900 border-2 border-stone-700/80 shadow-[0_8px_25px_rgba(0,0,0,0.8)] flex items-center justify-center relative overflow-hidden transition-transform group-hover:scale-105 active:scale-95"
                    :class="{ 'animate-[spin_4s_linear_infinite]': isPlayingMusic }"
                >
                    <!-- Vinyl Grooves -->
                    <div class="absolute inset-1 rounded-full border border-stone-800/80"></div>
                    <div class="absolute inset-2 rounded-full border border-stone-700/40"></div>
                    <div class="absolute inset-3 rounded-full border border-stone-800/60"></div>

                    <!-- Vinyl Center Label -->
                    <div
                        class="w-4 h-4 rounded-full border flex items-center justify-center shadow-inner"
                        :style="{
                            backgroundColor: invitationData.theme.primaryColor,
                            borderColor: invitationData.theme.secondaryColor
                        }"
                    >
                        <div class="w-1 h-1 rounded-full bg-stone-950"></div>
                    </div>
                </div>

                <!-- Floating note badge indicator -->
                <div
                    class="absolute -top-1 -right-1 w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold shadow-md border"
                    :style="{
                        backgroundColor: isPlayingMusic ? invitationData.theme.primaryColor : '#292524',
                        color: isPlayingMusic ? '#111827' : '#9ca3af',
                        borderColor: isPlayingMusic ? invitationData.theme.secondaryColor : '#44403c'
                    }"
                >
                    <span v-if="isPlayingMusic">🎵</span>
                    <span v-else>🔇</span>
                </div>
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
                <!-- Subtle Ambient Glow with theme color -->
                <div
                    class="absolute inset-0 pointer-events-none opacity-20"
                    :style="{ background: `radial-gradient(circle at center, ${invitationData.theme.primaryColor} 0%, transparent 70%)` }"
                ></div>

                <div class="relative z-10 w-full max-w-lg flex flex-col items-center">
                    <!-- Top Badge -->
                    <div
                        class="mb-4 inline-flex items-center gap-2 px-3 py-1 rounded-full border text-xs font-serif tracking-widest uppercase shadow-sm"
                        :style="{
                            backgroundColor: invitationData.theme.primaryColor + '1a',
                            borderColor: invitationData.theme.primaryColor + '4d',
                            color: invitationData.theme.secondaryColor
                        }"
                    >
                        <span>✨</span>
                        <span>{{ invitationData.content.coverBadge }}</span>
                    </div>

                    <!-- Invitation Header Info -->
                    <div class="text-center mb-6">
                        <div
                            class="text-xs uppercase tracking-[0.3em] font-serif mb-1"
                            :style="{ color: invitationData.theme.secondaryColor + 'b3' }"
                        >
                            {{ invitationData.content.coverSubtitle }}
                        </div>
                        <h1
                            class="font-serif text-3xl sm:text-4xl font-bold tracking-tight"
                            :style="{ color: invitationData.theme.secondaryColor }"
                        >
                            {{ invitationData.content.weddingTitle }}
                        </h1>
                        <p class="text-xs text-stone-400 font-serif mt-1">
                            {{ invitationData.content.weddingDateText }}
                        </p>
                    </div>

                    <!-- 3D ENVELOPE STAGE -->
                    <div class="envelope-scene w-full flex items-center justify-center py-4 my-2">
                        <div class="envelope-container relative w-[320px] sm:w-[390px] h-[240px] select-none">
                            
                            <!-- Envelope Back Base -->
                            <div
                                class="absolute inset-0 rounded-2xl border shadow-[0_25px_50px_-12px_rgba(0,0,0,0.8)]"
                                :style="{
                                    backgroundColor: invitationData.theme.cardBgColor,
                                    borderColor: invitationData.theme.primaryColor + '4d'
                                }"
                            >
                                <!-- Inner lining texture -->
                                <div
                                    class="absolute inset-2 rounded-xl border bg-black/30"
                                    :style="{ borderColor: invitationData.theme.primaryColor + '26' }"
                                ></div>
                            </div>

                            <!-- SLIDING 3D INVITATION LETTER CARD -->
                            <div
                                class="letter-card absolute inset-x-5 top-4 bottom-4 rounded-xl bg-gradient-to-b from-[#fbf8f2] to-[#f4ede2] text-stone-900 p-5 shadow-2xl border border-amber-200/80 flex flex-col justify-between items-center text-center transition-all duration-1000 ease-out"
                                :class="{ 'letter-slid-up': isLetterSlidOut }"
                            >
                                <div class="w-full">
                                    <div
                                        class="text-[9px] uppercase tracking-[0.25em] font-serif font-bold"
                                        :style="{ color: invitationData.theme.primaryColor }"
                                    >
                                        {{ invitationData.content.coverSubtitle || 'Undangan Pernikahan' }}
                                    </div>
                                    <div class="font-serif text-xl sm:text-2xl font-bold text-stone-900 mt-1">
                                        {{ invitationData.content.weddingTitle }}
                                    </div>
                                    <div
                                        class="w-8 h-0.5 mx-auto my-1.5"
                                        :style="{ backgroundColor: invitationData.theme.primaryColor + '66' }"
                                    ></div>
                                </div>

                                <div class="my-auto py-1 flex flex-col items-center">
                                    <div class="text-[9px] text-stone-500 uppercase tracking-wider mb-0.5">Spesial Untuk Yth:</div>
                                    <div
                                        class="text-xs sm:text-sm font-serif font-bold text-stone-900 px-3 py-1 rounded-lg border"
                                        :style="{
                                            backgroundColor: invitationData.theme.primaryColor + '15',
                                            borderColor: invitationData.theme.primaryColor + '33'
                                        }"
                                    >
                                        {{ guestName || 'Tamu Undangan Yang Berbahagia' }}
                                    </div>
                                    <div class="flex items-center gap-1.5 mt-1 text-[9px] text-stone-500 font-mono">
                                        <span>🎟️ Kode Tamu:</span>
                                        <span class="font-bold text-stone-900 bg-amber-100/90 px-1.5 py-0.5 rounded">
                                            {{ guestToken ? guestToken.substring(0, 8) : 'VIP' }}
                                        </span>
                                    </div>
                                </div>

                                <button
                                    @click="openDirectly"
                                    class="w-full py-2 px-4 rounded-lg bg-stone-900 hover:bg-stone-800 text-stone-100 font-serif text-xs font-semibold shadow-md border hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center justify-center gap-1.5"
                                    :style="{ borderColor: invitationData.theme.primaryColor + '66' }"
                                >
                                    <span>Buka Selengkapnya</span>
                                    <span>→</span>
                                </button>
                            </div>

                            <!-- Envelope Pocket (Front Facade) -->
                            <div class="absolute inset-0 pointer-events-none rounded-2xl overflow-hidden z-20">
                                <svg viewBox="0 0 390 240" class="w-full h-full preserve-3d">
                                    <polygon points="0,0 0,240 195,145" fill="#1e1814" opacity="0.95" />
                                    <polygon points="390,0 390,240 195,145" fill="#18130f" opacity="0.95" />
                                    <polygon points="0,240 390,240 195,115" fill="#241c16" />
                                    <line x1="0" y1="240" x2="195" y2="115" :stroke="invitationData.theme.primaryColor" stroke-width="1" stroke-opacity="0.35" />
                                    <line x1="390" y1="240" x2="195" y2="115" :stroke="invitationData.theme.primaryColor" stroke-width="1" stroke-opacity="0.35" />
                                </svg>
                            </div>

                            <!-- Envelope Top Flap (3D Rotating Flap) -->
                            <div
                                class="envelope-flap absolute inset-x-0 top-0 h-[135px] origin-top transition-transform duration-700 ease-in-out z-30"
                                :class="{ 'flap-open': isEnvelopeFlapOpen }"
                            >
                                <svg viewBox="0 0 390 135" class="w-full h-full drop-shadow-xl">
                                    <polygon points="0,0 390,0 195,135" fill="#2b201a" />
                                    <line x1="0" y1="0" x2="195" y2="135" :stroke="invitationData.theme.primaryColor" stroke-width="1.2" stroke-opacity="0.45" />
                                    <line x1="390" y1="0" x2="195" y2="135" :stroke="invitationData.theme.primaryColor" stroke-width="1.2" stroke-opacity="0.45" />
                                </svg>
                            </div>

                            <!-- 3D WAX SEAL STAMP -->
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
                                    <div
                                        class="absolute -inset-2 rounded-full blur-md animate-pulse transition-colors"
                                        :style="{ backgroundColor: invitationData.theme.primaryColor + '40' }"
                                    ></div>

                                    <!-- Realistic Wax Seal Stamp -->
                                    <div
                                        class="relative w-16 h-16 rounded-full border-2 shadow-[0_10px_25px_rgba(0,0,0,0.8),inset_0_2px_4px_rgba(255,255,255,0.4)] flex items-center justify-center font-serif"
                                        :style="{
                                            background: `linear-gradient(135deg, ${invitationData.theme.primaryColor}, #881337, #450a0a)`,
                                            borderColor: invitationData.theme.secondaryColor
                                        }"
                                    >
                                        <!-- Seal rim ring -->
                                        <div
                                            class="absolute inset-1.5 rounded-full border border-dashed"
                                            :style="{ borderColor: invitationData.theme.secondaryColor + '99' }"
                                        ></div>
                                        
                                        <!-- Monogram -->
                                        <div class="text-center">
                                            <div
                                                class="font-serif font-black text-sm tracking-tighter leading-none drop-shadow-md"
                                                :style="{ color: invitationData.theme.secondaryColor }"
                                            >
                                                {{ waxSealMonogram }}
                                            </div>
                                            <div
                                                class="text-[7px] tracking-widest uppercase mt-0.5"
                                                :style="{ color: invitationData.theme.secondaryColor + 'cc' }"
                                            >
                                                {{ waxSealYear }}
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
                            class="px-6 py-2.5 rounded-xl font-bold text-xs uppercase tracking-wider shadow-lg hover:scale-105 active:scale-95 transition-all flex items-center gap-2 mx-auto"
                            :style="{
                                backgroundColor: invitationData.theme.primaryColor,
                                color: '#111827',
                                boxShadow: `0 10px 25px ${invitationData.theme.primaryColor}33`
                            }"
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
        <div
            class="max-w-xl mx-auto min-h-screen shadow-2xl flex flex-col relative border-x transition-colors duration-500"
            :style="{
                backgroundColor: invitationData.theme.bgColor,
                borderColor: invitationData.theme.primaryColor + '33',
            }"
        >
            <!-- Hero Cover Header with 3D Depth Card -->
            <header class="relative min-h-[90vh] flex flex-col items-center justify-center text-center p-8 overflow-hidden">
                <!-- Background decorative rings -->
                <div
                    class="absolute w-96 h-96 rounded-full border pointer-events-none animate-pulse"
                    :style="{ borderColor: invitationData.theme.primaryColor + '1a' }"
                ></div>
                <div
                    class="absolute w-[480px] h-[480px] rounded-full border pointer-events-none"
                    :style="{ borderColor: invitationData.theme.primaryColor + '0d' }"
                ></div>

                <div
                    class="text-[11px] font-serif tracking-[0.35em] uppercase mb-3 font-semibold"
                    :style="{ color: invitationData.theme.primaryColor }"
                >
                    {{ invitationData.content.coverSubtitle }}
                </div>
                
                <h1
                    class="font-serif text-5xl sm:text-6xl my-3 leading-tight tracking-tight drop-shadow-md font-bold"
                    :style="{ color: invitationData.theme.secondaryColor }"
                >
                    {{ invitationData.content.weddingTitle }}
                </h1>

                <div class="flex items-center justify-center gap-3 my-4">
                    <div
                        class="w-12 h-px"
                        :style="{ background: `linear-gradient(to right, transparent, ${invitationData.theme.primaryColor}99)` }"
                    ></div>
                    <span :style="{ color: invitationData.theme.primaryColor }" class="text-xs">✤</span>
                    <div
                        class="w-12 h-px"
                        :style="{ background: `linear-gradient(to left, transparent, ${invitationData.theme.primaryColor}99)` }"
                    ></div>
                </div>

                <p
                    class="font-serif text-xs tracking-widest mb-8 uppercase"
                    :style="{ color: invitationData.theme.secondaryColor + 'cc' }"
                >
                    {{ invitationData.content.weddingDateText }}
                </p>

                <!-- Hero Photo with 3D Border Glow -->
                <div
                    class="relative w-64 h-80 rounded-t-full rounded-b-3xl overflow-hidden border-2 shadow-[0_20px_50px_rgba(0,0,0,0.8)] mx-auto mb-6 transform hover:scale-[1.02] transition-transform duration-500"
                    :style="{ borderColor: invitationData.theme.primaryColor + '66' }"
                >
                    <img
                        :src="invitationData.content.coverImage"
                        :alt="invitationData.content.weddingTitle"
                        class="w-full h-full object-cover"
                    />
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
                </div>

                <div class="text-xs text-stone-300 font-serif italic max-w-xs leading-relaxed">
                    "{{ invitationData.content.quote }}"
                </div>
                <div
                    v-if="invitationData.content.quoteSource"
                    class="text-[11px] font-serif font-semibold mt-2"
                    :style="{ color: invitationData.theme.primaryColor }"
                >
                    — {{ invitationData.content.quoteSource }}
                </div>
            </header>

            <!-- Couple Section -->
            <section
                class="py-16 px-6 text-center border-y"
                :style="{
                    backgroundColor: invitationData.theme.cardBgColor,
                    borderColor: invitationData.theme.primaryColor + '33'
                }"
            >
                <div
                    class="inline-block px-3 py-1 rounded-full text-[10px] font-bold tracking-widest uppercase border mb-3"
                    :style="{
                        backgroundColor: invitationData.theme.primaryColor + '1a',
                        borderColor: invitationData.theme.primaryColor + '40',
                        color: invitationData.theme.primaryColor
                    }"
                >
                    Mempelai Pengantin
                </div>
                <h2
                    class="font-serif text-2xl font-bold mb-2"
                    :style="{ color: invitationData.theme.secondaryColor }"
                >
                    Pasangan Bahagia
                </h2>
                <p class="text-xs text-stone-400 max-w-sm mx-auto mb-10 leading-relaxed">
                    Dengan memohon rahmat dan ridho Allah SWT, kami bermaksud menyelenggarakan pernikahan kami:
                </p>

                <div class="space-y-10">
                    <!-- Groom -->
                    <div class="flex flex-col items-center">
                        <div
                            class="w-32 h-32 rounded-full overflow-hidden border-2 shadow-xl mb-4 p-1"
                            :style="{
                                borderColor: invitationData.theme.primaryColor + '66',
                                backgroundColor: invitationData.theme.primaryColor + '1a'
                            }"
                        >
                            <img
                                :src="invitationData.content.groom.photo"
                                :alt="invitationData.content.groom.nickname"
                                class="w-full h-full object-cover rounded-full"
                            />
                        </div>
                        <h3
                            class="font-serif font-bold text-xl"
                            :style="{ color: invitationData.theme.secondaryColor }"
                        >
                            {{ invitationData.content.groom.fullName }}
                        </h3>
                        <p class="text-xs text-stone-400 mt-1 max-w-xs">
                            {{ invitationData.content.groom.parents }}
                        </p>
                        <div v-if="invitationData.content.groom.instagram" class="mt-2">
                            <span class="text-[11px] px-2.5 py-0.5 rounded-full border border-stone-700 text-stone-400">
                                📷 {{ invitationData.content.groom.instagram }}
                            </span>
                        </div>
                    </div>

                    <div
                        class="font-serif text-3xl font-light"
                        :style="{ color: invitationData.theme.primaryColor }"
                    >
                        &
                    </div>

                    <!-- Bride -->
                    <div class="flex flex-col items-center">
                        <div
                            class="w-32 h-32 rounded-full overflow-hidden border-2 shadow-xl mb-4 p-1"
                            :style="{
                                borderColor: invitationData.theme.primaryColor + '66',
                                backgroundColor: invitationData.theme.primaryColor + '1a'
                            }"
                        >
                            <img
                                :src="invitationData.content.bride.photo"
                                :alt="invitationData.content.bride.nickname"
                                class="w-full h-full object-cover rounded-full"
                            />
                        </div>
                        <h3
                            class="font-serif font-bold text-xl"
                            :style="{ color: invitationData.theme.secondaryColor }"
                        >
                            {{ invitationData.content.bride.fullName }}
                        </h3>
                        <p class="text-xs text-stone-400 mt-1 max-w-xs">
                            {{ invitationData.content.bride.parents }}
                        </p>
                        <div v-if="invitationData.content.bride.instagram" class="mt-2">
                            <span class="text-[11px] px-2.5 py-0.5 rounded-full border border-stone-700 text-stone-400">
                                📷 {{ invitationData.content.bride.instagram }}
                            </span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Countdown Timer with 3D Glass Cards -->
            <section
                class="py-12 px-6 text-center border-b"
                :style="{
                    backgroundColor: invitationData.theme.bgColor,
                    borderColor: invitationData.theme.primaryColor + '26'
                }"
            >
                <h3
                    class="text-xs uppercase tracking-widest font-serif mb-6"
                    :style="{ color: invitationData.theme.primaryColor }"
                >
                    Menghitung Hari Bahagia
                </h3>
                <div class="grid grid-cols-4 gap-3 max-w-xs mx-auto">
                    <div
                        class="p-3 rounded-2xl border shadow-lg"
                        :style="{
                            backgroundColor: invitationData.theme.cardBgColor,
                            borderColor: invitationData.theme.primaryColor + '33'
                        }"
                    >
                        <div
                            class="font-serif text-2xl font-bold"
                            :style="{ color: invitationData.theme.secondaryColor }"
                        >
                            {{ timeLeft.days }}
                        </div>
                        <div class="text-[9px] uppercase tracking-wider text-stone-400 mt-1">Hari</div>
                    </div>
                    <div
                        class="p-3 rounded-2xl border shadow-lg"
                        :style="{
                            backgroundColor: invitationData.theme.cardBgColor,
                            borderColor: invitationData.theme.primaryColor + '33'
                        }"
                    >
                        <div
                            class="font-serif text-2xl font-bold"
                            :style="{ color: invitationData.theme.secondaryColor }"
                        >
                            {{ timeLeft.hours }}
                        </div>
                        <div class="text-[9px] uppercase tracking-wider text-stone-400 mt-1">Jam</div>
                    </div>
                    <div
                        class="p-3 rounded-2xl border shadow-lg"
                        :style="{
                            backgroundColor: invitationData.theme.cardBgColor,
                            borderColor: invitationData.theme.primaryColor + '33'
                        }"
                    >
                        <div
                            class="font-serif text-2xl font-bold"
                            :style="{ color: invitationData.theme.secondaryColor }"
                        >
                            {{ timeLeft.minutes }}
                        </div>
                        <div class="text-[9px] uppercase tracking-wider text-stone-400 mt-1">Menit</div>
                    </div>
                    <div
                        class="p-3 rounded-2xl border shadow-lg"
                        :style="{
                            backgroundColor: invitationData.theme.cardBgColor,
                            borderColor: invitationData.theme.primaryColor + '33'
                        }"
                    >
                        <div
                            class="font-serif text-2xl font-bold"
                            :style="{ color: invitationData.theme.secondaryColor }"
                        >
                            {{ timeLeft.seconds }}
                        </div>
                        <div class="text-[9px] uppercase tracking-wider text-stone-400 mt-1">Detik</div>
                    </div>
                </div>
            </section>

            <!-- ======================================================== -->
            <!-- 3. AGENDA ACARA DENGAN 3D INTERACTIVE FLIP CARDS          -->
            <!-- ======================================================== -->
            <section class="py-16 px-6 space-y-6">
                <div class="text-center mb-8">
                    <div
                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold tracking-widest uppercase border mb-2"
                        :style="{
                            backgroundColor: invitationData.theme.primaryColor + '1a',
                            borderColor: invitationData.theme.primaryColor + '40',
                            color: invitationData.theme.primaryColor
                        }"
                    >
                        <span>🔄</span>
                        <span>Fitur 3D Flip Card</span>
                    </div>
                    <h2
                        class="font-serif text-2xl font-bold"
                        :style="{ color: invitationData.theme.secondaryColor }"
                    >
                        Agenda & Lokasi Acara
                    </h2>
                    <p class="text-xs text-stone-400 mt-1">
                        Ketuk kartu acara di bawah untuk membalik kartu (3D Flip) dan melihat rute maps:
                    </p>
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
                            <div
                                class="flip-card-front absolute inset-0 w-full h-full p-6 rounded-3xl border shadow-2xl flex flex-col justify-between items-center text-center"
                                :style="{
                                    backgroundColor: invitationData.theme.cardBgColor,
                                    borderColor: invitationData.theme.primaryColor + '4d'
                                }"
                            >
                                <div class="w-full flex items-center justify-between">
                                    <span
                                        class="px-3 py-1 rounded-full text-[10px] font-bold tracking-wider uppercase border"
                                        :style="{
                                            backgroundColor: invitationData.theme.primaryColor + '26',
                                            borderColor: invitationData.theme.primaryColor + '4d',
                                            color: invitationData.theme.secondaryColor
                                        }"
                                    >
                                        {{ event.name }}
                                    </span>
                                    <span
                                        class="text-xs font-mono font-medium"
                                        :style="{ color: invitationData.theme.primaryColor }"
                                    >
                                        🔄 Balik 3D
                                    </span>
                                </div>

                                <div class="space-y-2 my-auto">
                                    <div class="text-2xl">💍</div>
                                    <div
                                        class="font-serif text-xl font-bold"
                                        :style="{ color: invitationData.theme.secondaryColor }"
                                    >
                                        {{ formatDisplayDate(event.starts_at || invitationData.content.weddingDateText) }}
                                    </div>
                                    <div
                                        class="text-xs font-medium"
                                        :style="{ color: invitationData.theme.primaryColor }"
                                    >
                                        Pukul {{ formatDisplayTime(event.starts_at) }} - Selesai {{ event.timezone || 'WIB' }}
                                    </div>
                                    <div class="text-xs text-stone-300 pt-1 font-semibold">
                                        {{ event.venue_name }}
                                    </div>
                                </div>

                                <div class="w-full pt-3 border-t border-stone-800 flex flex-col gap-2">
                                    <div class="flex items-center justify-center gap-2" @click.stop>
                                        <a
                                            :href="getGoogleCalendarUrl(event)"
                                            target="_blank"
                                            class="px-3.5 py-1.5 rounded-xl border text-[11px] font-semibold flex items-center gap-1.5 transition-colors shadow-xs"
                                            :style="{
                                                backgroundColor: invitationData.theme.primaryColor + '26',
                                                borderColor: invitationData.theme.primaryColor + '66',
                                                color: invitationData.theme.secondaryColor
                                            }"
                                            title="Simpan Acara ke Google Calendar"
                                        >
                                            <span>📅</span>
                                            <span>Simpan ke Google Calendar</span>
                                        </a>
                                    </div>
                                    <div
                                        class="text-[10px] font-medium flex items-center justify-center gap-1"
                                        :style="{ color: invitationData.theme.primaryColor }"
                                    >
                                        <span>👉 Ketuk kartu untuk rute Google Maps</span>
                                        <span>🔄</span>
                                    </div>
                                </div>
                            </div>

                            <!-- BACK OF CARD (Address & Navigation Maps) -->
                            <div
                                class="flip-card-back absolute inset-0 w-full h-full p-6 rounded-3xl border shadow-2xl flex flex-col justify-between items-center text-center"
                                :style="{
                                    backgroundColor: invitationData.theme.bgColor,
                                    borderColor: invitationData.theme.primaryColor + '80'
                                }"
                            >
                                <div class="w-full flex items-center justify-between">
                                    <span
                                        class="text-[10px] uppercase tracking-wider font-bold"
                                        :style="{ color: invitationData.theme.primaryColor }"
                                    >
                                        Lokasi Acara
                                    </span>
                                    <span
                                        class="text-xs font-mono font-medium"
                                        :style="{ color: invitationData.theme.primaryColor }"
                                    >
                                        🔄 Balik Depan
                                    </span>
                                </div>

                                <div class="space-y-2 my-auto px-2">
                                    <div class="text-xl">📍</div>
                                    <div
                                        class="font-serif text-base font-bold"
                                        :style="{ color: invitationData.theme.secondaryColor }"
                                    >
                                        {{ event.venue_name }}
                                    </div>
                                    <p class="text-xs text-stone-300 leading-relaxed">
                                        {{ event.address || 'Alamat lengkap lokasi acara' }}
                                    </p>
                                </div>

                                <div class="w-full flex items-center gap-1.5 pt-2" @click.stop>
                                    <a
                                        v-if="event.maps_url"
                                        :href="event.maps_url"
                                        target="_blank"
                                        class="flex-1 py-2 px-2.5 rounded-xl font-bold text-xs shadow-md transition-colors flex items-center justify-center gap-1"
                                        :style="{
                                            backgroundColor: invitationData.theme.primaryColor,
                                            color: '#111827'
                                        }"
                                    >
                                        <span>🗺️</span>
                                        <span>Maps</span>
                                    </a>
                                    <a
                                        :href="getGoogleCalendarUrl(event)"
                                        target="_blank"
                                        class="py-2 px-2.5 rounded-xl bg-stone-800 hover:bg-stone-700 text-xs font-semibold border flex items-center gap-1"
                                        :style="{
                                            borderColor: invitationData.theme.primaryColor + '40',
                                            color: invitationData.theme.secondaryColor
                                        }"
                                        title="Simpan ke Google Calendar"
                                    >
                                        <span>📅</span>
                                        <span>Kalender</span>
                                    </a>
                                    <button
                                        type="button"
                                        @click="toggleFlip(event.id)"
                                        class="py-2 px-2.5 rounded-xl bg-stone-800 hover:bg-stone-700 text-stone-300 text-xs font-semibold border border-stone-700"
                                        title="Balik Kartu"
                                    >
                                        🔄
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Photo Gallery -->
            <section
                class="py-16 px-6 border-t text-center"
                :style="{
                    backgroundColor: invitationData.theme.cardBgColor,
                    borderColor: invitationData.theme.primaryColor + '33'
                }"
            >
                <h2
                    class="font-serif text-2xl font-bold mb-2"
                    :style="{ color: invitationData.theme.secondaryColor }"
                >
                    Galeri Momen
                </h2>
                <p class="text-xs text-stone-400 mb-8">Potret cinta dan kebahagiaan perjalanan kami.</p>

                <div class="grid grid-cols-2 gap-3">
                    <div
                        v-for="(photoUrl, pIdx) in invitationData.content.gallery"
                        :key="pIdx"
                        class="rounded-2xl overflow-hidden shadow-lg h-48 border border-stone-800 group"
                    >
                        <img
                            :src="photoUrl"
                            :alt="'Galeri ' + (pIdx + 1)"
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                        />
                    </div>
                </div>
            </section>

            <!-- RSVP Form Section -->
            <section
                class="py-16 px-6 border-t"
                :style="{
                    backgroundColor: invitationData.theme.bgColor,
                    borderColor: invitationData.theme.primaryColor + '26'
                }"
            >
                <div class="text-center mb-8">
                    <h2
                        class="font-serif text-2xl font-bold"
                        :style="{ color: invitationData.theme.secondaryColor }"
                    >
                        Konfirmasi Kehadiran
                    </h2>
                    <p class="text-xs text-stone-400 mt-1">Mohon konfirmasikan kehadiran Anda pada form di bawah ini:</p>
                </div>

                <div
                    v-if="rsvpSuccess"
                    class="p-6 rounded-2xl border text-center space-y-2"
                    :style="{
                        backgroundColor: invitationData.theme.primaryColor + '1a',
                        borderColor: invitationData.theme.primaryColor + '4d'
                    }"
                >
                    <div class="text-2xl">🎉</div>
                    <div
                        class="font-serif font-bold text-lg"
                        :style="{ color: invitationData.theme.secondaryColor }"
                    >
                        Terima Kasih!
                    </div>
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
                            class="w-full p-3 rounded-xl border border-stone-700 text-xs bg-stone-900 text-white placeholder-stone-500 focus:bg-stone-800 focus:outline-none focus:ring-2"
                            :style="{ focusRingColor: invitationData.theme.primaryColor }"
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
                            class="w-full p-3 rounded-xl border border-stone-700 text-xs bg-stone-900 text-white placeholder-stone-500 focus:bg-stone-800 focus:outline-none focus:ring-2"
                            placeholder="Tulis ucapan selamat..."
                        ></textarea>
                    </div>

                    <div v-if="rsvpErrorMessage" class="text-xs text-rose-400 bg-rose-950/60 border border-rose-800 p-2.5 rounded-xl">
                        {{ rsvpErrorMessage }}
                    </div>

                    <button
                        type="submit"
                        :disabled="rsvpSubmitting"
                        class="w-full py-3.5 rounded-xl text-xs font-bold uppercase tracking-wider shadow-lg transition-all disabled:opacity-50"
                        :style="{
                            backgroundColor: invitationData.theme.primaryColor,
                            color: '#111827',
                            boxShadow: `0 10px 25px ${invitationData.theme.primaryColor}33`
                        }"
                    >
                        {{ rsvpSubmitting ? 'Mengirim...' : 'Kirim Konfirmasi Kehadiran' }}
                    </button>
                </form>
            </section>

            <!-- VIP QR Check-in Reception Pass -->
            <section
                class="py-12 px-6 border-t text-center"
                :style="{
                    backgroundColor: invitationData.theme.cardBgColor,
                    borderColor: invitationData.theme.primaryColor + '33'
                }"
            >
                <div
                    class="max-w-sm mx-auto p-6 rounded-3xl border shadow-2xl relative overflow-hidden"
                    :style="{
                        backgroundColor: invitationData.theme.bgColor,
                        borderColor: invitationData.theme.primaryColor + '40'
                    }"
                >
                    <div
                        class="absolute -top-12 -right-12 w-28 h-28 rounded-full blur-xl pointer-events-none"
                        :style="{ backgroundColor: invitationData.theme.primaryColor + '20' }"
                    ></div>

                    <div
                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full border text-[10px] font-bold tracking-widest uppercase mb-3"
                        :style="{
                            backgroundColor: invitationData.theme.primaryColor + '1a',
                            borderColor: invitationData.theme.primaryColor + '40',
                            color: invitationData.theme.secondaryColor
                        }"
                    >
                        <span>📱</span>
                        <span>QR Check-in Meja Resepsi</span>
                    </div>

                    <h3
                        class="font-serif text-lg font-bold"
                        :style="{ color: invitationData.theme.secondaryColor }"
                    >
                        Kartu Akses Kehadiran
                    </h3>
                    <p class="text-[11px] text-stone-400 mt-1 mb-5">
                        Tunjukkan QR Code ini kepada penerima tamu di pintu resepsi untuk absensi kehadiran langsung.
                    </p>

                    <!-- QR Code Image -->
                    <div
                        class="w-44 h-44 mx-auto p-3 bg-white rounded-2xl shadow-xl border-2 flex items-center justify-center"
                        :style="{ borderColor: invitationData.theme.primaryColor }"
                    >
                        <img
                            :src="guestQrCodeUrl"
                            alt="QR Check-in"
                            class="w-full h-full object-contain"
                        />
                    </div>

                    <div class="mt-4 pt-3 border-t border-stone-800 space-y-1">
                        <div
                            class="font-serif font-bold text-sm"
                            :style="{ color: invitationData.theme.secondaryColor }"
                        >
                            {{ guestName || 'Tamu Undangan Yang Terhormat' }}
                        </div>
                        <div class="font-mono text-[10px] text-stone-400 tracking-wider">
                            TOKEN: {{ guestToken || 'GENERAL-INVITATION' }}
                        </div>
                    </div>
                </div>
            </section>

            <!-- Amplop Digital & Tanda Kasih Section -->
            <section
                v-if="digitalGift.enabled"
                class="py-16 px-6 border-t text-center"
                :style="{
                    backgroundColor: invitationData.theme.bgColor,
                    borderColor: invitationData.theme.primaryColor + '33'
                }"
            >
                <div class="max-w-sm mx-auto space-y-8">
                    <div>
                        <div
                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full border text-[10px] font-bold tracking-widest uppercase mb-2"
                            :style="{
                                backgroundColor: invitationData.theme.primaryColor + '1a',
                                borderColor: invitationData.theme.primaryColor + '40',
                                color: invitationData.theme.secondaryColor
                            }"
                        >
                            <span>🎁</span>
                            <span>Wedding Gift</span>
                        </div>
                        <h2
                            class="font-serif text-2xl font-bold"
                            :style="{ color: invitationData.theme.secondaryColor }"
                        >
                            {{ digitalGift.title }}
                        </h2>
                        <p class="text-xs text-stone-400 mt-2 leading-relaxed">
                            {{ digitalGift.description }}
                        </p>
                    </div>

                    <!-- Bank Accounts -->
                    <div class="space-y-4">
                        <div
                            v-for="(acc, idx) in digitalGift.accounts"
                            :key="idx"
                            class="p-5 rounded-2xl border shadow-lg text-left relative overflow-hidden"
                            :style="{
                                backgroundColor: invitationData.theme.cardBgColor,
                                borderColor: invitationData.theme.primaryColor + '40'
                            }"
                        >
                            <div class="flex items-center justify-between mb-3">
                                <span
                                    class="px-2.5 py-1 rounded-lg text-xs font-bold border"
                                    :style="{
                                        backgroundColor: invitationData.theme.primaryColor + '26',
                                        borderColor: invitationData.theme.primaryColor + '4d',
                                        color: invitationData.theme.secondaryColor
                                    }"
                                >
                                    {{ acc.bank }}
                                </span>
                                <span class="text-xs text-stone-400 font-serif">Cashless Gift</span>
                            </div>

                            <div class="space-y-1 my-2">
                                <div class="text-[11px] text-stone-400">Nomor Rekening:</div>
                                <div
                                    class="font-mono text-lg font-bold tracking-wider"
                                    :style="{ color: invitationData.theme.secondaryColor }"
                                >
                                    {{ acc.account_number }}
                                </div>
                                <div class="text-xs text-stone-300">
                                    a.n <span class="font-semibold text-white">{{ acc.account_name }}</span>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 pt-3 border-t border-stone-800">
                                <button
                                    @click="copyAccountNumber(acc.account_number, idx)"
                                    class="flex-1 py-2 px-3 rounded-xl font-bold text-xs shadow-md transition-colors flex items-center justify-center gap-1.5"
                                    :style="{
                                        backgroundColor: invitationData.theme.primaryColor,
                                        color: '#111827'
                                    }"
                                >
                                    <span v-if="copiedBankIndex === idx">✓ Tersalin!</span>
                                    <span v-else>📋 Salin No Rekening</span>
                                </button>
                                <button
                                    v-if="acc.qr_image"
                                    @click="openQris(acc)"
                                    class="py-2 px-3 rounded-xl bg-stone-800 hover:bg-stone-700 text-xs font-semibold border flex items-center gap-1"
                                    :style="{
                                        borderColor: invitationData.theme.primaryColor + '40',
                                        color: invitationData.theme.secondaryColor
                                    }"
                                >
                                    <span>📱</span>
                                    <span>QRIS</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Physical Gift Address -->
                    <div
                        v-if="digitalGift.physical_gift && digitalGift.physical_gift.enabled"
                        class="p-5 rounded-2xl border border-stone-700/80 shadow-lg text-left space-y-2.5"
                        :style="{ backgroundColor: invitationData.theme.cardBgColor }"
                    >
                        <div class="flex items-center gap-2">
                            <span class="text-lg">📦</span>
                            <h3
                                class="font-serif font-bold text-sm"
                                :style="{ color: invitationData.theme.secondaryColor }"
                            >
                                Kirim Kado Pernikahan (Fisik)
                            </h3>
                        </div>
                        <p class="text-xs text-stone-300 leading-relaxed">
                            {{ digitalGift.physical_gift.address }}
                        </p>
                        <div class="text-[11px] text-stone-400">
                            Penerima: <strong class="text-white">{{ digitalGift.physical_gift.receiver_name }}</strong> &bull; {{ digitalGift.physical_gift.phone }}
                        </div>

                        <div class="pt-2">
                            <button
                                @click="copyAddress(digitalGift.physical_gift.address)"
                                class="w-full py-2 px-3 rounded-xl bg-stone-800 hover:bg-stone-700 text-xs font-semibold border border-stone-700 transition-colors flex items-center justify-center gap-1.5"
                                :style="{ color: invitationData.theme.secondaryColor }"
                            >
                                <span v-if="copiedAddressToast">✓ Alamat Tersalin!</span>
                                <span v-else>📋 Salin Alamat Pengiriman</span>
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Guestbook / Wishes Wall -->
            <section
                class="py-16 px-6 border-t"
                :style="{
                    backgroundColor: invitationData.theme.cardBgColor,
                    borderColor: invitationData.theme.primaryColor + '26'
                }"
            >
                <div class="text-center mb-8">
                    <h2
                        class="font-serif text-2xl font-bold"
                        :style="{ color: invitationData.theme.secondaryColor }"
                    >
                        Buku Tamu & Ucapan
                    </h2>
                    <p class="text-xs text-stone-400 mt-1">Doa restu hangat dari para sahabat dan keluarga:</p>
                </div>

                <div class="space-y-3 max-w-sm mx-auto">
                    <div
                        v-for="entry in localGuestbook"
                        :key="entry.id"
                        class="p-4 rounded-2xl border shadow-md text-left"
                        :style="{
                            backgroundColor: invitationData.theme.bgColor,
                            borderColor: invitationData.theme.primaryColor + '30'
                        }"
                    >
                        <div class="flex items-center justify-between mb-1">
                            <span
                                class="font-bold text-xs"
                                :style="{ color: invitationData.theme.secondaryColor }"
                            >
                                {{ entry.guest_name }}
                            </span>
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
            <footer
                class="py-12 px-6 text-center space-y-4 border-t"
                :style="{
                    backgroundColor: invitationData.theme.bgColor,
                    borderColor: invitationData.theme.primaryColor + '33'
                }"
            >
                <button
                    @click="shareWhatsApp"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold shadow-md transition-colors"
                >
                    <span>💬</span> Bagikan ke WhatsApp
                </button>

                <div class="text-xs text-stone-400 pt-4 border-t border-stone-800/80">
                    <p>Merupakan suatu kehormatan & kebahagiaan bagi kami apabila Anda berkenan hadir.</p>
                    <div
                        class="font-serif text-2xl mt-2 font-bold"
                        :style="{ color: invitationData.theme.secondaryColor }"
                    >
                        {{ invitationData.content.weddingTitle }}
                    </div>
                </div>

                <div class="text-[10px] text-stone-600 pt-4">
                    Platform Undangan Digital &bull; Efek 3D Wax Seal Interaktif
                </div>
            </footer>
        </div>

        <!-- QRIS Preview Modal Popup -->
        <div
            v-if="showQrisModal && activeQris"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-md animate-in fade-in duration-200"
            @click.self="showQrisModal = false"
        >
            <div
                class="text-white rounded-3xl max-w-sm w-full p-6 shadow-2xl border text-center space-y-4"
                :style="{
                    backgroundColor: invitationData.theme.cardBgColor,
                    borderColor: invitationData.theme.primaryColor + '60'
                }"
            >
                <div class="flex items-center justify-between border-b border-stone-800 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="text-xl">📱</span>
                        <h3
                            class="font-serif font-bold text-sm"
                            :style="{ color: invitationData.theme.secondaryColor }"
                        >
                            QRIS Cashless Gift
                        </h3>
                    </div>
                    <button
                        @click="showQrisModal = false"
                        class="text-stone-400 hover:text-white p-1 rounded-lg text-sm"
                    >
                        ✕
                    </button>
                </div>

                <div class="space-y-1">
                    <div
                        class="text-xs font-bold uppercase tracking-wider"
                        :style="{ color: invitationData.theme.primaryColor }"
                    >
                        {{ activeQris.bank }}
                    </div>
                    <div class="text-sm font-semibold text-white">{{ activeQris.account_name }}</div>
                </div>

                <!-- QRIS Image -->
                <div class="w-60 h-60 mx-auto p-4 bg-white rounded-2xl shadow-xl flex items-center justify-center">
                    <img
                        :src="activeQris.qr_image"
                        alt="QRIS Code"
                        class="w-full h-full object-contain"
                    />
                </div>

                <p class="text-[11px] text-stone-400 leading-relaxed">
                    Scan menggunakan aplikasi m-Banking apa saja (BCA, Mandiri, BRI, BNI) atau e-Wallet (GoPay, OVO, Dana, ShopeePay, LinkAja).
                </p>

                <button
                    @click="showQrisModal = false"
                    class="w-full py-2.5 rounded-xl font-bold text-xs uppercase tracking-wider transition-colors"
                    :style="{
                        backgroundColor: invitationData.theme.primaryColor,
                        color: '#111827'
                    }"
                >
                    Tutup
                </button>
            </div>
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

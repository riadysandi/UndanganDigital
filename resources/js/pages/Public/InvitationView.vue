<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue';
import { Head } from '@inertiajs/vue3';
import confetti from 'canvas-confetti';
import NingrumTemplate from '@/components/Templates/NingrumTemplate.vue';

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
    isDemo?: boolean;
}>();

// State
const isOpen = ref(false);
const isEnvelopeFlapOpen = ref(false);
const isSealBroken = ref(false);
const isLetterSlidOut = ref(false);
const isPlayingMusic = ref(false);
const audioEl = ref<HTMLAudioElement | null>(null);
const envelopeMode = ref(false);

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
    const theme = (typeof d.theme === 'object' && d.theme !== null) ? d.theme : {};
    const firstEvent = props.events && props.events.length > 0 ? props.events[0] : null;

    const isLight = theme.isLight === true || theme.mode === 'light';

    return {
        theme: {
            palette: theme.palette || 'gold',
            isLight: isLight,
            primaryColor: theme.primaryColor || (isLight ? '#2D5A43' : '#D4AF37'),
            secondaryColor: theme.secondaryColor || (isLight ? '#1B2E24' : '#F3E5AB'),
            bgColor: theme.bgColor || (isLight ? '#F4F7F4' : '#14171c'),
            cardBgColor: theme.cardBgColor || (isLight ? '#FFFFFF' : '#1a1f26'),
            envelopeBg: theme.envelopeBg || (isLight ? '#E2ECE5' : '#1e1814'),
            envelopePocketL: theme.envelopePocketL || (isLight ? '#D6E4DB' : '#1e1814'),
            envelopePocketR: theme.envelopePocketR || (isLight ? '#CDE0D4' : '#18130f'),
            envelopePocketB: theme.envelopePocketB || (isLight ? '#DFEAE3' : '#241c16'),
            envelopeFlap: theme.envelopeFlap || (isLight ? '#CFE1D6' : '#2b201a'),
            sealColor: theme.sealColor || theme.primaryColor || '#8A1C3C',
            sealSecondary: theme.sealSecondary || theme.secondaryColor || '#D4AF37',
            textColor: theme.textColor || (isLight ? '#192820' : '#f3f4f6'),
            fontFamily: theme.fontFamily || 'Playfair Display',
            fallingPetals: theme.fallingPetals !== false,
            petalColor1: theme.petalColors?.[0] || theme.petalColor1 || (isLight ? '#D1FAE5' : '#FECDD3'),
            petalColor2: theme.petalColors?.[1] || theme.petalColor2 || (isLight ? '#6EE7B7' : '#FB7185'),
            petalColor3: theme.petalColors?.[2] || theme.petalColor3 || (isLight ? '#059669' : '#E11D48'),
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
        },
        musicUrl: d.musicUrl || '/audio/romantic-wedding.mp3'
    };
});

const isNingrum = computed(() => {
    const slug = props.template?.slug || '';
    return slug.includes('ningrum') || slug.includes('fairytale-3d');
});

// Authentic Katsudoto Assets Resolution
const templateAssets = computed(() => {
    const slug = props.template?.slug || '';
    if (slug.includes('victoria')) {
        return {
            isKatsudoto: true,
            frameFf: '/images/templates/victoria/frame-ff.png',
            frameCover: '/images/templates/victoria/frame-tc.png',
            frameCouple: '/images/templates/victoria/frame-dc.png',
            chandelier: '/images/templates/victoria/Orn-06.png',
            cornerVines: '/images/templates/victoria/Orn-01.png',
            incenseUrn: '/images/templates/victoria/Orn-04.png',
            crest: '/images/templates/victoria/Orn-02.png',
            pediment: '/images/templates/victoria/Orn-03.png',
            texture: '/images/templates/ningrum/texture-sd.png',
        };
    }
    // Default to Ningrum (Fairytale Royal 3D)
    return {
        isKatsudoto: true,
        frameFf: '/images/templates/ningrum/frame-ff.png',
        frameCover: '/images/templates/ningrum/frame-cover.png',
        frameCouple: '/images/templates/ningrum/frame-couple.png',
        chandelier: '/images/templates/ningrum/Orn-07.png',
        cornerVines: '/images/templates/ningrum/Orn-06.png',
        incenseUrn: '/images/templates/ningrum/Orn-17.png',
        crest: '/images/templates/ningrum/Orn-51.png',
        pediment: '/images/templates/ningrum/Orn-50.png',
        sideBranch: '/images/templates/ningrum/Orn-22.png',
        texture: '/images/templates/ningrum/texture-sd.png',
    };
});

const openInvitation = () => {
    isOpen.value = true;
    startAudio();
    confetti({
        particleCount: 60,
        spread: 70,
        origin: { y: 0.6 },
        colors: [invitationData.value.theme.primaryColor, invitationData.value.theme.secondaryColor, '#AA771C']
    });
    setTimeout(() => {
        const el = document.getElementById('invitation-content-start');
        if (el) {
            el.scrollIntoView({ behavior: 'smooth' });
        }
    }, 150);
};

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

// Falling Rose Petals effect parameters
const petalCount = 18;
const petals = Array.from({ length: petalCount }, (_, i) => ({
    id: i,
    left: `${(i * (100 / petalCount) + Math.random() * 4).toFixed(1)}%`,
    animationDelay: `${(Math.random() * 8).toFixed(1)}s`,
    animationDuration: `${(8 + Math.random() * 8).toFixed(1)}s`,
    size: 14 + Math.floor(Math.random() * 16),
    rotation: Math.floor(Math.random() * 360),
    opacity: 0.35 + Math.random() * 0.45,
}));

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

// =========================================================
// INTERACTIVE 3D TILT & PARALLAX MOTION ENGINE
// =========================================================
const mouseX = ref(0);
const mouseY = ref(0);

const handleGlobalMouseMove = (e: MouseEvent) => {
    const nx = (e.clientX / window.innerWidth) * 2 - 1;
    const ny = (e.clientY / window.innerHeight) * 2 - 1;
    mouseX.value = nx;
    mouseY.value = ny;
};

// Dynamic 3D Card Hover Tilt with realistic depth
const handleCardTilt = (e: MouseEvent) => {
    const card = e.currentTarget as HTMLElement;
    if (!card) return;
    const rect = card.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;
    const rotX = ((y / rect.height) - 0.5) * -18;
    const rotY = ((x / rect.width) - 0.5) * 18;
    card.style.transform = `perspective(1000px) rotateX(${rotX.toFixed(1)}deg) rotateY(${rotY.toFixed(1)}deg) scale3d(1.03, 1.03, 1.03)`;
};

const resetCardTilt = (e: MouseEvent) => {
    const card = e.currentTarget as HTMLElement;
    if (card) {
        card.style.transform = 'perspective(1000px) rotateX(0deg) rotateY(0deg) scale3d(1, 1, 1)';
    }
};

// Interactive 3D Lightbox Photo state
const activeLightboxPhoto = ref<string | null>(null);
const openLightbox = (url: string) => {
    activeLightboxPhoto.value = url;
};
const closeLightbox = () => {
    activeLightboxPhoto.value = null;
};

// Cinematic Scroll Reveal Intersection Observer
let scrollObserver: IntersectionObserver | null = null;

const initScrollObserver = () => {
    if (typeof window === 'undefined' || !('IntersectionObserver' in window)) return;
    
    if (scrollObserver) scrollObserver.disconnect();

    scrollObserver = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-revealed');
            }
        });
    }, {
        threshold: 0.08,
        rootMargin: '0px 0px -30px 0px',
    });

    const elements = document.querySelectorAll('.reveal-on-scroll');
    elements.forEach((el) => scrollObserver?.observe(el));
};

watch(isOpen, (newVal) => {
    if (newVal) {
        setTimeout(() => {
            initScrollObserver();
        }, 200);
    }
});

onMounted(() => {
    window.addEventListener('message', onMessage);
    window.addEventListener('mousemove', handleGlobalMouseMove);
    updateCountdown();
    timerInterval = setInterval(updateCountdown, 1000);
    setTimeout(() => {
        initScrollObserver();
    }, 400);
});

onBeforeUnmount(() => {
    window.removeEventListener('message', onMessage);
    window.removeEventListener('mousemove', handleGlobalMouseMove);
    if (timerInterval) clearInterval(timerInterval);
    if (audioEl.value) {
        audioEl.value.pause();
    }
    if (scrollObserver) {
        scrollObserver.disconnect();
    }
});

// Play background music with smooth volume fade-in
const startAudio = () => {
    if (!audioEl.value) return;
    try {
        audioEl.value.volume = 0.2;
        const playPromise = audioEl.value.play();
        if (playPromise !== undefined) {
            playPromise
                .then(() => {
                    isPlayingMusic.value = true;
                    // Smooth volume fade-in up to 0.75
                    let vol = 0.2;
                    const fadeTimer = setInterval(() => {
                        if (!audioEl.value || vol >= 0.75) {
                            clearInterval(fadeTimer);
                            return;
                        }
                        vol += 0.05;
                        audioEl.value.volume = Math.min(vol, 0.75);
                    }, 120);
                })
                .catch((err) => {
                    console.warn('Playback blocked by browser policy:', err);
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

    // Trigger audio immediately within user click gesture
    startAudio();

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

// Reopen Top Cover
const reopenTopCover = () => {
    isOpen.value = false;
    envelopeMode.value = false;
};

// Reopen 3D envelope modal
const reopenEnvelope = () => {
    envelopeMode.value = true;
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
        audioEl.value.volume = 0.75;
        audioEl.value.play().then(() => {
            isPlayingMusic.value = true;
        }).catch((err) => {
            console.error('Play audio error:', err);
        });
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

    if (props.isDemo) {
        setTimeout(() => {
            rsvpSubmitting.value = false;
            rsvpSuccess.value = true;
            localGuestbook.value.unshift({
                id: Date.now(),
                guest_name: rsvpForm.value.guest_name,
                message: rsvpForm.value.message || 'Selamat berbahagia untuk kedua mempelai!',
                created_at: 'Baru saja'
            });
            confetti({
                particleCount: 120,
                spread: 80,
                origin: { y: 0.6 },
            });
        }, 500);
        return;
    }

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
    const d = liveDesignOverride.value || props.design || {};
    const url = d.musicUrl || props.design?.musicUrl;
    if (!url || url.includes('actions.google.com')) {
        return '/audio/romantic-wedding.mp3';
    }
    return url;
});

watch(musicSource, (newUrl) => {
    if (audioEl.value && newUrl) {
        const wasPlaying = isPlayingMusic.value;
        audioEl.value.src = newUrl;
        audioEl.value.load();
        if (wasPlaying) {
            audioEl.value.play().catch(() => {});
        }
    }
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
    <!-- Dynamic Open Graph & Meta Tags for WhatsApp, Social Media, and Browsers -->
    <Head>
        <title>{{ invitationData.content.weddingTitle }} - Undangan Pernikahan Digital</title>
        <meta name="description" :content="`Undangan Pernikahan ${invitationData.content.weddingTitle}. ${invitationData.content.weddingDateText}`" />
        <!-- Open Graph for WhatsApp, Telegram, Facebook, and iMessage -->
        <meta property="og:site_name" content="Undangan Digital" />
        <meta property="og:type" content="website" />
        <meta property="og:title" :content="`The Wedding of ${invitationData.content.weddingTitle}`" />
        <meta property="og:description" :content="`Tanpa mengurangi rasa hormat, kami mengundang Anda untuk hadir di hari bahagia pernikahan ${invitationData.content.weddingTitle} (${invitationData.content.weddingDateText}). Buka tautan untuk melihat undangan digital.`" />
        <meta property="og:image" :content="invitationData.content.coverImage" />
        <!-- Twitter Cards -->
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" :content="`The Wedding of ${invitationData.content.weddingTitle}`" />
        <meta name="twitter:description" :content="`Undangan Pernikahan ${invitationData.content.weddingTitle} - ${invitationData.content.weddingDateText}`" />
        <meta name="twitter:image" :content="invitationData.content.coverImage" />
    </Head>

    <!-- Top Demo Mode Banner -->
    <div v-if="props.isDemo" class="fixed top-0 inset-x-0 z-[100] bg-stone-900/95 backdrop-blur-md text-white py-2.5 px-4 flex items-center justify-between text-xs shadow-xl border-b border-white/10">
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500 text-stone-900">DEMO MODE</span>
            <span class="font-medium text-stone-200 hidden sm:inline">Pratinjau Desain: <strong>{{ template?.name || 'Template' }}</strong></span>
        </div>
        <div class="flex items-center gap-2">
            <a href="/" class="px-2.5 py-1 rounded-lg bg-white/10 hover:bg-white/20 text-stone-300 hover:text-white transition-colors">
                ← Beranda
            </a>
            <a :href="'/register?template=' + (template?.id || '')" class="px-3 py-1 rounded-lg bg-rose-600 hover:bg-rose-500 text-white font-bold shadow transition-colors">
                Gunakan Desain Ini
            </a>
        </div>
    </div>

    <!-- Audio Element (Loop, Preloaded, with Playsinline for iOS/Android) -->
    <audio
        ref="audioEl"
        loop
        preload="auto"
        playsinline
        :src="musicSource"
    ></audio>

    <!-- 1. AUTHENTIC 1:1 KATSUDOTO NINGRUM TEMPLATE -->
    <NingrumTemplate
        v-if="isNingrum"
        :invitation-data="invitationData"
        :events="events"
        :guestbook="localGuestbook"
        :digital-gift="digitalGift"
        :guest-name="guestName"
        :guest-token="guestToken"
        :is-demo="props.isDemo"
        :template="props.template"
        :is-open="isOpen"
        :is-playing-music="isPlayingMusic"
        :time-left="timeLeft"
        :format-display-date="formatDisplayDate"
        :format-event-time="formatEventTime"
        :copy-address="copyAddress"
        :open-qris="openQris"
        :submit-rsvp="submitRsvp"
        :rsvp-form="rsvpForm"
        :rsvp-submitting="rsvpSubmitting"
        :rsvp-success="rsvpSuccess"
        :rsvp-error-message="rsvpErrorMessage"
        @open-invitation="openInvitation"
        @toggle-music="toggleMusic"
    />

    <!-- 2. STANDARD INVITATION TEMPLATE (Other Themes) -->
    <div
        v-else
        class="min-h-screen w-full relative transition-colors duration-500 overflow-x-hidden selection:bg-amber-400 selection:text-stone-900"
        :style="{
            backgroundColor: invitationData.theme.bgColor,
            fontFamily: invitationData.theme.fontFamily + ', serif',
            color: invitationData.theme.textColor,
        }"
    >

        <!-- Falling Romantic Rose Petals Layer (Efek Visual Romantis) -->
        <div 
            v-if="invitationData.theme.fallingPetals" 
            class="pointer-events-none fixed inset-0 z-30 overflow-hidden" 
            aria-hidden="true"
        >
            <div
                v-for="petal in petals"
                :key="petal.id"
                class="absolute -top-12 animate-petal"
                :style="{
                    left: petal.left,
                    animationDelay: petal.animationDelay,
                    animationDuration: petal.animationDuration,
                }"
            >
                <div 
                    class="sway-petal"
                    :style="{
                        width: `${petal.size}px`,
                        height: `${petal.size * 1.3}px`,
                        opacity: petal.opacity,
                        transform: `rotate(${petal.rotation}deg)`
                    }"
                >
                    <svg viewBox="0 0 30 40" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full drop-shadow-sm">
                        <path 
                            d="M15 0 C25 6 30 20 20 34 C16 38 12 38 8 34 C0 20 5 6 15 0 Z" 
                            fill="url(#petalGradient)" 
                        />
                        <defs>
                            <linearGradient id="petalGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" :stop-color="invitationData.theme.petalColor1" />
                                <stop offset="50%" :stop-color="invitationData.theme.petalColor2" />
                                <stop offset="100%" :stop-color="invitationData.theme.petalColor3" />
                            </linearGradient>
                        </defs>
                    </svg>
                </div>
            </div>
        </div>

        <!-- 3D Interactive Floating Elements Layer (Butterflies, Shimmer Dust, & Botanical Sprigs) -->
        <div v-if="isOpen" class="pointer-events-none fixed inset-0 z-30 overflow-hidden" aria-hidden="true">
            <!-- 3D Fluttering Butterfly 1 (Top Left) -->
            <div 
                class="butterfly-3d top-[15%] left-[8%] sm:left-[18%]"
                :style="{ transform: `translate3d(${mouseX * 20}px, ${mouseY * 20}px, 40px)` }"
            >
                <div class="relative w-8 h-8 flex items-center justify-center">
                    <svg viewBox="0 0 32 32" class="w-full h-full drop-shadow-md">
                        <g class="butterfly-wing-left">
                            <path d="M16 16 C12 6, 2 8, 4 18 C6 24, 14 20, 16 16 Z" :fill="invitationData.theme.primaryColor" opacity="0.9" />
                        </g>
                        <g class="butterfly-wing-right">
                            <path d="M16 16 C20 6, 30 8, 28 18 C26 24, 18 20, 16 16 Z" :fill="invitationData.theme.primaryColor" opacity="0.9" />
                        </g>
                        <line x1="16" y1="10" x2="16" y2="22" :stroke="invitationData.theme.secondaryColor" stroke-width="1.5" />
                    </svg>
                </div>
            </div>

            <!-- 3D Fluttering Butterfly 2 (Mid Right) -->
            <div 
                class="butterfly-3d top-[48%] right-[8%] sm:right-[16%]"
                :style="{ transform: `translate3d(${-mouseX * 25}px, ${-mouseY * 25}px, 60px)`, animationDelay: '-4s' }"
            >
                <div class="relative w-6 h-6 flex items-center justify-center">
                    <svg viewBox="0 0 32 32" class="w-full h-full drop-shadow-md">
                        <g class="butterfly-wing-left">
                            <path d="M16 16 C12 6, 2 8, 4 18 C6 24, 14 20, 16 16 Z" :fill="invitationData.theme.secondaryColor" opacity="0.85" />
                        </g>
                        <g class="butterfly-wing-right">
                            <path d="M16 16 C20 6, 30 8, 28 18 C26 24, 18 20, 16 16 Z" :fill="invitationData.theme.secondaryColor" opacity="0.85" />
                        </g>
                    </svg>
                </div>
            </div>

            <!-- 3D Floating Glowing Fairy Dust / Sparkles -->
            <div 
                class="fairy-dust-orb absolute top-[28%] right-[22%] w-3 h-3 rounded-full blur-[1px] pointer-events-none"
                :style="{
                    backgroundColor: invitationData.theme.primaryColor,
                    boxShadow: `0 0 12px ${invitationData.theme.primaryColor}, 0 0 24px ${invitationData.theme.secondaryColor}`,
                    transform: `translate3d(${mouseX * 35}px, ${mouseY * 35}px, 50px)`
                }"
            ></div>
            <div 
                class="fairy-dust-orb absolute top-[65%] left-[15%] w-2.5 h-2.5 rounded-full blur-[1px] pointer-events-none"
                :style="{
                    backgroundColor: invitationData.theme.secondaryColor,
                    boxShadow: `0 0 10px ${invitationData.theme.secondaryColor}`,
                    animationDelay: '-2.5s',
                    transform: `translate3d(${-mouseX * 30}px, ${-mouseY * 30}px, 30px)`
                }"
            ></div>
            <div 
                class="fairy-dust-orb absolute top-[82%] right-[14%] w-2 h-2 rounded-full blur-[0.5px] pointer-events-none"
                :style="{
                    backgroundColor: invitationData.theme.primaryColor,
                    boxShadow: `0 0 8px ${invitationData.theme.primaryColor}`,
                    animationDelay: '-4.2s',
                    transform: `translate3d(${mouseX * 40}px, ${mouseY * 40}px, 70px)`
                }"
            ></div>
        </div>

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
        <!-- FULLSCREEN 3D ENVELOPE MODAL (When not yet opened)       -->
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
                                    <polygon points="0,0 0,240 195,145" :fill="invitationData.theme.envelopePocketL" opacity="0.95" />
                                    <polygon points="390,0 390,240 195,145" :fill="invitationData.theme.envelopePocketR" opacity="0.95" />
                                    <polygon points="0,240 390,240 195,115" :fill="invitationData.theme.envelopePocketB" />
                                    <line x1="0" y1="240" x2="195" y2="115" :stroke="invitationData.theme.primaryColor" stroke-width="1.2" stroke-opacity="0.4" />
                                    <line x1="390" y1="240" x2="195" y2="115" :stroke="invitationData.theme.primaryColor" stroke-width="1.2" stroke-opacity="0.4" />
                                </svg>
                            </div>

                            <!-- Envelope Top Flap (3D Rotating Flap) -->
                            <div
                                class="envelope-flap absolute inset-x-0 top-0 h-[135px] origin-top transition-transform duration-700 ease-in-out z-30"
                                :class="{ 'flap-open': isEnvelopeFlapOpen }"
                            >
                                <svg viewBox="0 0 390 135" class="w-full h-full drop-shadow-xl">
                                    <polygon points="0,0 390,0 195,135" :fill="invitationData.theme.envelopeFlap" />
                                    <line x1="0" y1="0" x2="195" y2="135" :stroke="invitationData.theme.primaryColor" stroke-width="1.5" stroke-opacity="0.5" />
                                    <line x1="390" y1="0" x2="195" y2="135" :stroke="invitationData.theme.primaryColor" stroke-width="1.5" stroke-opacity="0.5" />
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
                                            background: `linear-gradient(135deg, ${invitationData.theme.sealColor}, ${invitationData.theme.sealSecondary})`,
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
        <!-- 3. MAIN INVITATION CONTENT (Scrollable, revealed on open) -->
        <!-- ======================================================== -->
        <div
            v-if="isOpen"
            id="invitation-content-start"
            class="max-w-xl mx-auto min-h-screen shadow-2xl flex flex-col relative border-x transition-colors duration-500"
            :style="{
                backgroundColor: invitationData.theme.bgColor,
                borderColor: invitationData.theme.primaryColor + '33',
            }"
        >
            <!-- Hero Cover Header with 3D Depth Card -->
            <header class="relative min-h-[90vh] flex flex-col items-center justify-center text-center p-8 overflow-hidden">
                <!-- Top Chandelier Swinging (Pendulum) -->
                <div class="absolute -top-3 inset-x-0 flex justify-center pointer-events-none z-20">
                    <div class="w-28 sm:w-36 animate-goyang-top">
                        <img :src="templateAssets.chandelier" alt="Chandelier" class="w-full drop-shadow-xl" />
                    </div>
                </div>

                <!-- Corner Vines Left & Right -->
                <div class="absolute top-0 left-0 w-20 sm:w-28 pointer-events-none z-10">
                    <img :src="templateAssets.cornerVines" alt="Vines" class="w-full opacity-60" />
                </div>
                <div class="absolute top-0 right-0 w-20 sm:w-28 pointer-events-none z-10 scale-x-[-1]">
                    <img :src="templateAssets.cornerVines" alt="Vines" class="w-full opacity-60" />
                </div>
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
                    class="reveal-on-scroll reveal-fade-up text-[11px] font-serif tracking-[0.35em] uppercase mb-3 font-semibold"
                    :style="{ color: invitationData.theme.primaryColor }"
                >
                    {{ invitationData.content.coverSubtitle }}
                </div>
                
                <h1
                    class="reveal-on-scroll reveal-fade-up reveal-delay-1 font-serif text-5xl sm:text-6xl my-3 leading-tight tracking-tight drop-shadow-md font-bold"
                    :style="{ color: invitationData.theme.secondaryColor }"
                >
                    {{ invitationData.content.weddingTitle }}
                </h1>

                <div class="reveal-on-scroll reveal-fade-up reveal-delay-2 flex items-center justify-center gap-3 my-4">
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
                    class="reveal-on-scroll reveal-fade-up reveal-delay-2 font-serif text-xs tracking-widest mb-8 uppercase"
                    :style="{ color: invitationData.theme.secondaryColor + 'cc' }"
                >
                    {{ invitationData.content.weddingDateText }}
                </p>

                <!-- Interactive 3D Hero Arch Showcase with Tilt & Shimmer -->
                <div class="reveal-on-scroll reveal-zoom-in reveal-delay-3 relative my-6 flex items-center justify-center">
                    <div
                        class="card-tilt-3d relative w-72 sm:w-80 h-96 rounded-t-full rounded-b-3xl p-2.5 transition-all duration-300 ease-out cursor-pointer select-none"
                        @mousemove="handleCardTilt"
                        @mouseleave="resetCardTilt"
                        :style="{
                            backgroundColor: invitationData.theme.cardBgColor,
                            boxShadow: `0 25px 60px -15px ${invitationData.theme.primaryColor}50, 0 10px 25px rgba(0,0,0,0.6)`,
                            border: `2px solid ${invitationData.theme.primaryColor}80`
                        }"
                    >
                        <!-- 3D Baroque Corner Flourishes (Top-Left & Top-Right) -->
                        <div class="absolute -top-3.5 -left-3.5 w-11 h-11 pointer-events-none drop-shadow-lg z-20">
                            <svg viewBox="0 0 40 40" fill="none" class="w-full h-full">
                                <path d="M0,0 C18,6 28,16 32,32 C22,22 12,16 0,0 Z" :fill="invitationData.theme.primaryColor" />
                                <circle cx="10" cy="10" r="3.5" :fill="invitationData.theme.secondaryColor" />
                            </svg>
                        </div>
                        <div class="absolute -top-3.5 -right-3.5 w-11 h-11 pointer-events-none drop-shadow-lg z-20 scale-x-[-1]">
                            <svg viewBox="0 0 40 40" fill="none" class="w-full h-full">
                                <path d="M0,0 C18,6 28,16 32,32 C22,22 12,16 0,0 Z" :fill="invitationData.theme.primaryColor" />
                                <circle cx="10" cy="10" r="3.5" :fill="invitationData.theme.secondaryColor" />
                            </svg>
                        </div>

                        <!-- Arch Inner Frame with Shimmer & 3D Badge -->
                        <div class="relative w-full h-full rounded-t-full rounded-b-2xl overflow-hidden border border-amber-200/30 group">
                            <img
                                :src="invitationData.content.coverImage"
                                :alt="invitationData.content.weddingTitle"
                                class="w-full h-full object-cover transition-transform duration-700 ease-out group-hover:scale-108"
                            />
                            <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-transparent to-transparent"></div>

                            <!-- Dynamic Metallic 3D Shimmer Sweep Bar -->
                            <div class="absolute inset-0 pointer-events-none overflow-hidden">
                                <div class="shimmer-sweep-bar"></div>
                            </div>

                            <!-- Floating 3D Badge on Card Base -->
                            <div class="absolute bottom-4 inset-x-4 text-center z-10">
                                <span
                                    class="inline-block px-3 py-1 rounded-full text-[10px] font-serif font-bold tracking-widest uppercase border shadow-lg backdrop-blur-md"
                                    :style="{
                                        backgroundColor: invitationData.theme.cardBgColor + 'e6',
                                        borderColor: invitationData.theme.primaryColor,
                                        color: invitationData.theme.secondaryColor
                                    }"
                                >
                                    ✨ {{ invitationData.content.coverBadge }}
                                </span>
                            </div>
                        </div>

                        <!-- 3D Specular Light Glare Overlay -->
                        <div class="absolute inset-0 rounded-t-full rounded-b-3xl pointer-events-none bg-gradient-to-tr from-white/10 to-transparent opacity-60"></div>
                    </div>
                </div>

                <div 
                    class="reveal-on-scroll reveal-fade-up reveal-delay-4 text-xs font-serif italic max-w-xs leading-relaxed"
                    :style="{ color: invitationData.theme.isLight ? '#374151' : '#d6d3d1' }"
                >
                    "{{ invitationData.content.quote }}"
                </div>
                <div
                    v-if="invitationData.content.quoteSource"
                    class="reveal-on-scroll reveal-fade-up reveal-delay-4 text-[11px] font-serif font-semibold mt-2"
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
                <div class="reveal-on-scroll reveal-fade-up">
                    <!-- Top Pediment / Chandelier Ornament -->
                    <div class="w-24 sm:w-32 mx-auto mb-3 animate-goyang-top pointer-events-none">
                        <img :src="templateAssets.pediment || templateAssets.chandelier" alt="Ornament" class="w-full drop-shadow-md" />
                    </div>

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
                    <p 
                        class="text-xs max-w-sm mx-auto mb-10 leading-relaxed"
                        :style="{ color: invitationData.theme.isLight ? '#4B5563' : '#a8a29e' }"
                    >
                        Dengan memohon rahmat dan ridho Allah SWT, kami bermaksud menyelenggarakan pernikahan kami:
                    </p>
                </div>

                <div class="space-y-10">
                    <!-- Groom (Slide In From Left) -->
                    <div class="reveal-on-scroll reveal-slide-left flex flex-col items-center">
                        <div
                            class="card-tilt-3d relative w-52 sm:w-60 h-64 sm:h-72 mx-auto mb-4 select-none cursor-pointer transition-transform duration-300"
                            @mousemove="handleCardTilt"
                            @mouseleave="resetCardTilt"
                        >
                            <div class="absolute inset-x-3.5 inset-y-3.5 rounded-t-full overflow-hidden">
                                <img
                                    :src="invitationData.content.groom.photo"
                                    :alt="invitationData.content.groom.nickname"
                                    class="w-full h-full object-cover"
                                />
                                <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent"></div>
                            </div>
                            <img
                                :src="templateAssets.frameCouple"
                                alt="Baroque Frame"
                                class="absolute inset-0 w-full h-full pointer-events-none drop-shadow-2xl z-10 object-contain"
                            />
                        </div>
                        <h3
                            class="font-serif font-bold text-xl"
                            :style="{ color: invitationData.theme.secondaryColor }"
                        >
                            {{ invitationData.content.groom.fullName }}
                        </h3>
                        <p 
                            class="text-xs mt-1 max-w-xs"
                            :style="{ color: invitationData.theme.isLight ? '#4B5563' : '#a8a29e' }"
                        >
                            {{ invitationData.content.groom.parents }}
                        </p>
                        <div v-if="invitationData.content.groom.instagram" class="mt-2">
                            <span 
                                class="text-[11px] px-2.5 py-0.5 rounded-full border"
                                :style="{
                                    borderColor: invitationData.theme.isLight ? '#E5E7EB' : '#44403c',
                                    color: invitationData.theme.isLight ? '#4B5563' : '#a8a29e'
                                }"
                            >
                                📷 {{ invitationData.content.groom.instagram }}
                            </span>
                        </div>
                    </div>

                    <div
                        class="reveal-on-scroll reveal-zoom-in reveal-delay-1 font-serif text-3xl font-light"
                        :style="{ color: invitationData.theme.primaryColor }"
                    >
                        &
                    </div>

                    <!-- Bride (Slide In From Right) -->
                    <div class="reveal-on-scroll reveal-slide-right reveal-delay-2 flex flex-col items-center">
                        <div
                            class="card-tilt-3d relative w-52 sm:w-60 h-64 sm:h-72 mx-auto mb-4 select-none cursor-pointer transition-transform duration-300"
                            @mousemove="handleCardTilt"
                            @mouseleave="resetCardTilt"
                        >
                            <div class="absolute inset-x-3.5 inset-y-3.5 rounded-t-full overflow-hidden">
                                <img
                                    :src="invitationData.content.bride.photo"
                                    :alt="invitationData.content.bride.nickname"
                                    class="w-full h-full object-cover"
                                />
                                <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent"></div>
                            </div>
                            <img
                                :src="templateAssets.frameCouple"
                                alt="Baroque Frame"
                                class="absolute inset-0 w-full h-full pointer-events-none drop-shadow-2xl z-10 object-contain"
                            />
                        </div>
                        <h3
                            class="font-serif font-bold text-xl"
                            :style="{ color: invitationData.theme.secondaryColor }"
                        >
                            {{ invitationData.content.bride.fullName }}
                        </h3>
                        <p 
                            class="text-xs mt-1 max-w-xs"
                            :style="{ color: invitationData.theme.isLight ? '#4B5563' : '#a8a29e' }"
                        >
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
                    class="reveal-on-scroll reveal-fade-up text-xs uppercase tracking-widest font-serif mb-6"
                    :style="{ color: invitationData.theme.primaryColor }"
                >
                    Menghitung Hari Bahagia
                </h3>
                <div class="grid grid-cols-4 gap-3 max-w-xs mx-auto">
                    <div
                        class="reveal-on-scroll reveal-zoom-in reveal-delay-1 p-3 rounded-2xl border shadow-lg"
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
                        class="reveal-on-scroll reveal-zoom-in reveal-delay-2 p-3 rounded-2xl border shadow-lg"
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
                        class="reveal-on-scroll reveal-zoom-in reveal-delay-3 p-3 rounded-2xl border shadow-lg"
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
                        class="reveal-on-scroll reveal-zoom-in reveal-delay-4 p-3 rounded-2xl border shadow-lg"
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
                <div class="reveal-on-scroll reveal-fade-up text-center mb-8">
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
                        class="reveal-on-scroll reveal-fade-up flip-card-wrapper w-full h-[290px] cursor-pointer select-none"
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
                <div class="reveal-on-scroll reveal-fade-up">
                    <h2
                        class="font-serif text-2xl font-bold mb-2"
                        :style="{ color: invitationData.theme.secondaryColor }"
                    >
                        Galeri Momen
                    </h2>
                    <p class="text-xs text-stone-400 mb-8">Potret cinta dan kebahagiaan perjalanan kami.</p>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div
                        v-for="(photoUrl, pIdx) in invitationData.content.gallery"
                        :key="pIdx"
                        @mousemove="handleCardTilt"
                        @mouseleave="resetCardTilt"
                        @click="openLightbox(photoUrl)"
                        :class="[
                            'card-tilt-3d reveal-on-scroll reveal-zoom-in rounded-2xl overflow-hidden shadow-lg h-48 border border-stone-800 group cursor-pointer relative',
                            pIdx === 0 ? '' : pIdx === 1 ? 'reveal-delay-1' : pIdx === 2 ? 'reveal-delay-2' : 'reveal-delay-3'
                        ]"
                        title="Klik untuk perbesar 3D Lightbox"
                    >
                        <img
                            :src="photoUrl"
                            :alt="'Galeri ' + (pIdx + 1)"
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700 pointer-events-none"
                        />
                        <div class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                            <span class="px-3 py-1 rounded-full bg-black/70 text-white text-[11px] font-semibold border border-white/20 shadow-lg">
                                🔍 Perbesar
                            </span>
                        </div>
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
                <div class="reveal-on-scroll reveal-fade-up text-center mb-8">
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
                        Konfirmasi kehadiran Anda tersimpan. Ucapan doa akan tampil setelah disetujui oleh pengantin.
                    </p>
                </div>

                <form v-else @submit.prevent="submitRsvp" class="reveal-on-scroll reveal-fade-up space-y-4 max-w-sm mx-auto">
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
                    class="reveal-on-scroll reveal-zoom-in max-w-sm mx-auto p-6 rounded-3xl border shadow-2xl relative overflow-hidden"
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
                    <div class="reveal-on-scroll reveal-fade-up">
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
                            :class="[
                                'reveal-on-scroll reveal-fade-up p-5 rounded-2xl border shadow-lg text-left relative overflow-hidden',
                                idx === 0 ? '' : idx === 1 ? 'reveal-delay-1' : 'reveal-delay-2'
                            ]"
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
                        class="reveal-on-scroll reveal-fade-up p-5 rounded-2xl border border-stone-700/80 shadow-lg text-left space-y-2.5"
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
                <div class="reveal-on-scroll reveal-fade-up text-center mb-8">
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
                        v-for="(entry, eIdx) in localGuestbook"
                        :key="entry.id"
                        :class="[
                            'reveal-on-scroll reveal-fade-up p-4 rounded-2xl border shadow-md text-left',
                            eIdx % 2 === 1 ? 'reveal-delay-1' : ''
                        ]"
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
                class="reveal-on-scroll reveal-fade-up py-12 px-6 text-center space-y-4 border-t"
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

        <!-- 3D Photo Lightbox Modal -->
        <div
            v-if="activeLightboxPhoto"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/90 backdrop-blur-lg animate-in fade-in duration-300"
            @click.self="closeLightbox"
        >
            <div
                class="relative max-w-2xl w-full max-h-[85vh] rounded-3xl overflow-hidden p-2 shadow-[0_25px_70px_rgba(0,0,0,0.9)] border"
                :style="{
                    backgroundColor: invitationData.theme.cardBgColor,
                    borderColor: invitationData.theme.primaryColor + '80'
                }"
            >
                <button
                    @click="closeLightbox"
                    class="absolute top-4 right-4 z-20 w-10 h-10 rounded-full bg-black/60 hover:bg-black/90 text-white flex items-center justify-center border border-white/20 transition-colors shadow-lg"
                    title="Tutup"
                >
                    ✕
                </button>
                <div class="relative w-full h-full max-h-[80vh] flex items-center justify-center overflow-hidden rounded-2xl">
                    <img
                        :src="activeLightboxPhoto"
                        alt="Preview Foto"
                        class="max-w-full max-h-[80vh] object-contain rounded-2xl shadow-2xl transition-transform duration-500 hover:scale-105"
                    />
                </div>
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

/* Falling Rose Petal Keyframe Animations */
@keyframes petalFall {
    0% {
        transform: translateY(-8vh) rotate(0deg) translateX(0);
        opacity: 0;
    }
    10% {
        opacity: 0.85;
    }
    90% {
        opacity: 0.85;
    }
    100% {
        transform: translateY(105vh) rotate(420deg) translateX(60px);
        opacity: 0;
    }
}
@keyframes petalSway {
    0%, 100% {
        transform: translateX(-18px) rotate(-12deg);
    }
    50% {
        transform: translateX(18px) rotate(18deg);
    }
}
.animate-petal {
    animation-name: petalFall;
    animation-timing-function: linear;
    animation-iteration-count: infinite;
}
.sway-petal {
    animation: petalSway 3.2s ease-in-out infinite alternate;
}

/* ========================================================= */
/* CINEMATIC LUXURY SCROLL REVEAL ANIMATIONS                */
/* ========================================================= */
.reveal-on-scroll {
    opacity: 0;
    will-change: transform, opacity;
    transition: opacity 1.1s cubic-bezier(0.16, 1, 0.3, 1), transform 1.1s cubic-bezier(0.16, 1, 0.3, 1);
}

.reveal-fade-up {
    transform: translateY(35px);
}
.reveal-fade-up.is-revealed {
    opacity: 1;
    transform: translateY(0);
}

.reveal-slide-left {
    transform: translateX(-45px);
}
.reveal-slide-left.is-revealed {
    opacity: 1;
    transform: translateX(0);
}

.reveal-slide-right {
    transform: translateX(45px);
}
.reveal-slide-right.is-revealed {
    opacity: 1;
    transform: translateX(0);
}

.reveal-zoom-in {
    transform: scale(0.92);
}
.reveal-zoom-in.is-revealed {
    opacity: 1;
    transform: scale(1);
}

/* Staggered Delay Utilities */
.reveal-delay-1 {
    transition-delay: 150ms;
}
.reveal-delay-2 {
    transition-delay: 300ms;
}
.reveal-delay-3 {
    transition-delay: 450ms;
}
.reveal-delay-4 {
    transition-delay: 600ms;
}

/* ========================================================= */
/* 3D CARD TILT & METALLIC SHIMMER                          */
/* ========================================================= */
.card-tilt-3d {
    transform-style: preserve-3d;
    will-change: transform;
    transition: transform 0.15s ease-out, box-shadow 0.3s ease;
}

.shimmer-sweep-bar {
    position: absolute;
    top: -50%;
    left: -150%;
    width: 60%;
    height: 200%;
    background: linear-gradient(
        105deg,
        rgba(255, 255, 255, 0) 0%,
        rgba(255, 255, 255, 0.08) 35%,
        rgba(255, 255, 255, 0.5) 50%,
        rgba(255, 255, 255, 0.08) 65%,
        rgba(255, 255, 255, 0) 100%
    );
    transform: rotate(25deg);
    animation: sweepShimmer 5.5s infinite cubic-bezier(0.4, 0, 0.2, 1);
    pointer-events: none;
}

@keyframes sweepShimmer {
    0%, 30% {
        left: -150%;
        opacity: 0;
    }
    35% {
        opacity: 0.8;
    }
    60% {
        left: 200%;
        opacity: 0.8;
    }
    61%, 100% {
        left: 200%;
        opacity: 0;
    }
}

/* ========================================================= */
/* 3D FLOATING BUTTERFLY ENGINE                             */
/* ========================================================= */
.butterfly-3d {
    position: absolute;
    width: 32px;
    height: 26px;
    transform-style: preserve-3d;
    pointer-events: none;
    will-change: transform;
    animation: butterflyFloat 16s ease-in-out infinite;
}

.butterfly-wing-left {
    transform-origin: right center;
    animation: wingFlap 0.22s ease-in-out infinite alternate;
}

.butterfly-wing-right {
    transform-origin: left center;
    animation: wingFlap 0.22s ease-in-out infinite alternate-reverse;
}

@keyframes wingFlap {
    0% {
        transform: rotateY(0deg) scaleX(1);
    }
    100% {
        transform: rotateY(70deg) scaleX(0.72);
    }
}

@keyframes butterflyFloat {
    0% {
        transform: translate3d(0, 0, 0) rotate(0deg);
    }
    25% {
        transform: translate3d(35px, -40px, 30px) rotate(12deg);
    }
    50% {
        transform: translate3d(-25px, -85px, 15px) rotate(-8deg);
    }
    75% {
        transform: translate3d(20px, -135px, 45px) rotate(10deg);
    }
    100% {
        transform: translate3d(0, 0, 0) rotate(0deg);
    }
}

/* ========================================================= */
/* 3D FAIRY DUST ORBS                                       */
/* ========================================================= */
.fairy-dust-orb {
    animation: orbFloat3d 8s ease-in-out infinite alternate;
    will-change: transform, opacity;
}

@keyframes orbFloat3d {
    0% {
        transform: translateY(0px) scale(0.9);
        opacity: 0.35;
    }
    50% {
        transform: translateY(-25px) scale(1.2);
        opacity: 0.85;
    }
    100% {
        transform: translateY(-48px) scale(0.95);
        opacity: 0.4;
    }
}

/* ========================================================= */
/* KATSUDOTO SIGNATURE SWAYING PENDULUM ANIMATIONS (GOYANG)   */
/* ========================================================= */
@keyframes goyang {
    0% {
        transform: rotate(-3.5deg);
    }
    100% {
        transform: rotate(3.5deg);
    }
}

.animate-goyang-top {
    animation: goyang 4.2s ease-in-out infinite alternate;
    transform-origin: 50% 0%;
    will-change: transform;
}

.animate-goyang-bottom {
    animation: goyang 4.8s ease-in-out infinite alternate;
    transform-origin: 50% 90%;
    will-change: transform;
}

/* ========================================================= */
/* KATSUDOTO RISING MYSTICAL INCENSE SMOKE PARTICLES         */
/* ========================================================= */
.smoke-wrap {
    position: absolute;
    top: 35%;
    left: 50%;
    width: 0;
    height: 0;
    pointer-events: none;
    z-index: 10;
}

.smoke {
    position: absolute;
    top: 0;
    left: 0;
    border-radius: 50%;
    background: rgba(245, 235, 220, 0.65);
    filter: blur(5px);
    opacity: 0;
    animation: smokeRise 4.5s ease-out infinite;
    will-change: transform, opacity;
}

.smoke.s1 { width: 10px; height: 10px; animation-delay: 0s; animation-duration: 5s; }
.smoke.s2 { width: 14px; height: 14px; animation-delay: 0.35s; animation-duration: 5.5s; }
.smoke.s3 { width: 9px; height: 9px; animation-delay: 0.7s; animation-duration: 4.8s; }
.smoke.s4 { width: 13px; height: 13px; animation-delay: 1.05s; animation-duration: 5.2s; }
.smoke.s5 { width: 11px; height: 11px; animation-delay: 1.4s; animation-duration: 4.6s; }
.smoke.s6 { width: 15px; height: 15px; animation-delay: 1.75s; animation-duration: 5.8s; }
.smoke.s7 { width: 10px; height: 10px; animation-delay: 2.1s; animation-duration: 5s; }
.smoke.s8 { width: 14px; height: 14px; animation-delay: 2.45s; animation-duration: 5.3s; }
.smoke.s9 { width: 12px; height: 12px; animation-delay: 2.8s; animation-duration: 4.7s; }
.smoke.s10 { width: 16px; height: 16px; animation-delay: 3.15s; animation-duration: 5.7s; }
.smoke.s11 { width: 9px; height: 9px; animation-delay: 3.5s; animation-duration: 4.9s; }
.smoke.s12 { width: 13px; height: 13px; animation-delay: 3.85s; animation-duration: 5.4s; }

@keyframes smokeRise {
    0% {
        opacity: 0;
        transform: translate(0, 0) scale(0.4);
    }
    12% {
        opacity: 0.7;
    }
    30% {
        opacity: 0.5;
        transform: translate(-10px, -28px) scale(1.4);
    }
    60% {
        opacity: 0.25;
        transform: translate(6px, -65px) scale(2.2);
    }
    85% {
        opacity: 0.1;
    }
    100% {
        opacity: 0;
        transform: translate(-12px, -110px) scale(3.2);
    }
}

.bg-texture-sd {
    background-image: url('/images/templates/ningrum/texture-sd.png');
    background-repeat: repeat;
    background-size: 320px;
}
</style>

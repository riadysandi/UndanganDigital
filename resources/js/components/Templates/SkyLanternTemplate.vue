<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount, nextTick } from 'vue';
import { Head } from '@inertiajs/vue3';
import confetti from 'canvas-confetti';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

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

// Safe formatters
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
    return d || 'Sabtu, 18 Juli 2026';
};

// Normalized couple and content
const groom = computed(() => {
    const c = props.invitationData?.content || {};
    return {
        fullName: c.groom?.fullName || c.groomName || 'Edward Wijaya, B.Eng',
        nickname: c.groom?.nickname || c.groomNickname || 'Edward',
        parents: c.groom?.parents || c.groomParents || 'Putra tercinta dari Bpk. Hendra Wijaya & Ibu Mei Ling',
        photo: c.groom?.photo || c.groomPhotoUrl || '/images/templates/lantern/groom-real.jpg',
        instagram: c.groom?.instagram || c.groomInstagram || '@edward.wijaya',
    };
});

const bride = computed(() => {
    const c = props.invitationData?.content || {};
    return {
        fullName: c.bride?.fullName || c.brideName || 'Clara Sutanto, B.Com',
        nickname: c.bride?.nickname || c.brideNickname || 'Clara',
        parents: c.bride?.parents || c.brideParents || 'Putri tercinta dari Bpk. Benny Sutanto & Ibu Lindawati',
        photo: c.bride?.photo || c.bridePhotoUrl || '/images/templates/lantern/bride-real.jpg',
        instagram: c.bride?.instagram || c.brideInstagram || '@clara.sutanto',
    };
});

const weddingTitle = computed(() => {
    const c = props.invitationData?.content || {};
    if (groom.value.nickname && bride.value.nickname) {
        return `${groom.value.nickname} & ${bride.value.nickname}`;
    }
    return c.weddingTitle || 'Edward & Clara';
});

const weddingDate = computed(() => {
    const c = props.invitationData?.content || {};
    return c.weddingDateText || c.weddingDate || 'Sabtu, 18 Juli 2026';
});

const coverPhoto = computed(() => {
    const c = props.invitationData?.content || {};
    return c.coverImage || c.coverPhotoUrl || '/images/templates/lantern/couple-lantern.jpg';
});

const galleryPhotos = computed(() => {
    const c = props.invitationData?.content || {};
    if (c.gallery && Array.isArray(c.gallery) && c.gallery.length > 0) {
        return c.gallery;
    }
    return [
        '/images/templates/lantern/sky-lanterns-night.jpg',
        '/images/templates/lantern/couple-lantern.jpg',
        '/images/templates/lantern/groom-real.jpg',
        '/images/templates/lantern/bride-real.jpg',
    ];
});

// Lightbox state
const activeLightboxImg = ref<string | null>(null);
const openLightbox = (url: string) => {
    activeLightboxImg.value = url;
};
const closeLightbox = () => {
    activeLightboxImg.value = null;
};

// Bank copy feedback
const copiedBankIndex = ref<number | null>(null);
const copyBankNumber = (num: string, index: number) => {
    navigator.clipboard.writeText(num);
    copiedBankIndex.value = index;
    setTimeout(() => {
        copiedBankIndex.value = null;
    }, 2500);
};

// Google Calendar URL
const googleCalendarUrl = computed(() => {
    const title = encodeURIComponent((weddingTitle.value || 'Sky Lantern Wedding') + ' Celebration');
    const firstEvent = props.events?.[0];
    const location = encodeURIComponent((firstEvent?.venue_name || '') + ' - ' + (firstEvent?.address || ''));
    return `https://www.google.com/calendar/render?action=TEMPLATE&text=${title}&location=${location}&dates=20260718T010000Z/20260718T140000Z`;
});

// =========================================================================
// 1. UNIQUE COVER EXPERIENCE: 3D KONGMING SKY LANTERN ASCENSION
// (Not sliding doors! A floating 3D lantern in the night sky that takes flight)
// =========================================================================
const isCoverVisible = ref(!props.isOpen);
const isAscending = ref(false);

const handleIgniteAndAscend = () => {
    if (isAscending.value) return;
    isAscending.value = true;

    emit('openInvitation');

    // Shower of golden festival embers & fireflies
    confetti({
        particleCount: 80,
        spread: 100,
        origin: { y: 0.7 },
        colors: ['#F59E0B', '#FBBF24', '#DC2626', '#FEF08A', '#D97706'],
    });

    setTimeout(() => {
        confetti({
            particleCount: 50,
            spread: 120,
            origin: { y: 0.4 },
            colors: ['#FDE68A', '#F59E0B', '#EF4444'],
        });
    }, 450);

    setTimeout(() => {
        isCoverVisible.value = false;
    }, 1800);
};

// =========================================================================
// 2. BACKGROUND 60FPS AMBIENT FLOATING LANTERNS CANVAS
// =========================================================================
const ambientCanvasRef = ref<HTMLCanvasElement | null>(null);
let ambientAnimId: number | null = null;

interface AmbientLantern {
    x: number;
    y: number;
    size: number;
    speedY: number;
    speedX: number;
    swayAngle: number;
    swaySpeed: number;
    opacity: number;
    hueShift: number;
    flamePulse: number;
}

const initAmbientLanterns = () => {
    const canvas = ambientCanvasRef.value;
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    if (!ctx) return;

    let width = (canvas.width = window.innerWidth);
    let height = (canvas.height = window.innerHeight);

    const onResize = () => {
        if (!canvas) return;
        width = canvas.width = window.innerWidth;
        height = canvas.height = window.innerHeight;
    };
    window.addEventListener('resize', onResize);

    const lanterns: AmbientLantern[] = [];
    const count = window.innerWidth < 768 ? 22 : 45;

    for (let i = 0; i < count; i++) {
        lanterns.push({
            x: Math.random() * width,
            y: Math.random() * height,
            size: 10 + Math.random() * 22,
            speedY: -(0.35 + Math.random() * 0.75),
            speedX: (Math.random() - 0.5) * 0.35,
            swayAngle: Math.random() * Math.PI * 2,
            swaySpeed: 0.015 + Math.random() * 0.02,
            opacity: 0.25 + Math.random() * 0.65,
            hueShift: Math.random() * 20 - 10,
            flamePulse: Math.random() * Math.PI * 2,
        });
    }

    const drawLantern = (l: AmbientLantern) => {
        ctx.save();
        ctx.translate(l.x, l.y);
        ctx.rotate(Math.sin(l.swayAngle) * 0.08);

        const w = l.size;
        const h = l.size * 1.35;

        // Outer soft warm heat aura
        const aura = ctx.createRadialGradient(0, 0, 1, 0, 0, w * 1.8);
        aura.addColorStop(0, `rgba(251, 191, 36, ${l.opacity * 0.55})`);
        aura.addColorStop(0.5, `rgba(245, 158, 11, ${l.opacity * 0.2})`);
        aura.addColorStop(1, 'rgba(220, 38, 38, 0)');
        ctx.fillStyle = aura;
        ctx.beginPath();
        ctx.arc(0, 0, w * 1.8, 0, Math.PI * 2);
        ctx.fill();

        // 3D Lantern Rice Paper Body (Hexagonal silhouette)
        const bodyGrad = ctx.createLinearGradient(0, -h / 2, 0, h / 2);
        bodyGrad.addColorStop(0, `rgba(254, 240, 138, ${l.opacity})`);
        bodyGrad.addColorStop(0.35, `rgba(251, 146, 60, ${l.opacity * 0.95})`);
        bodyGrad.addColorStop(0.85, `rgba(225, 29, 72, ${l.opacity * 0.9})`);
        bodyGrad.addColorStop(1, `rgba(159, 18, 57, ${l.opacity * 0.85})`);
        ctx.fillStyle = bodyGrad;

        ctx.beginPath();
        ctx.moveTo(-w / 2, -h / 2 + 3);
        ctx.quadraticCurveTo(0, -h / 2 - 2, w / 2, -h / 2 + 3);
        ctx.quadraticCurveTo(w / 2 + 3, 0, w / 2 - 2, h / 2);
        ctx.lineTo(-w / 2 + 2, h / 2);
        ctx.quadraticCurveTo(-w / 2 - 3, 0, -w / 2, -h / 2 + 3);
        ctx.closePath();
        ctx.fill();

        // Bamboo bottom hoop
        ctx.strokeStyle = `rgba(180, 83, 9, ${l.opacity * 0.8})`;
        ctx.lineWidth = 1;
        ctx.stroke();

        // Center burning flame core
        const flameR = (w * 0.28) * (0.85 + Math.sin(l.flamePulse) * 0.15);
        const flameGrad = ctx.createRadialGradient(0, h / 2 - 3, 0.5, 0, h / 2 - 3, flameR);
        flameGrad.addColorStop(0, '#FFFFFF');
        flameGrad.addColorStop(0.4, '#FEF08A');
        flameGrad.addColorStop(0.8, '#F59E0B');
        flameGrad.addColorStop(1, 'rgba(239, 68, 68, 0)');
        ctx.fillStyle = flameGrad;
        ctx.beginPath();
        ctx.arc(0, h / 2 - 3, flameR, 0, Math.PI * 2);
        ctx.fill();

        ctx.restore();
    };

    const loop = () => {
        ctx.clearRect(0, 0, width, height);

        for (const l of lanterns) {
            l.y += l.speedY;
            l.swayAngle += l.swaySpeed;
            l.flamePulse += 0.06;
            l.x += l.speedX + Math.sin(l.swayAngle) * 0.35;

            if (l.y < -60) {
                l.y = height + 40;
                l.x = Math.random() * width;
            }
            if (l.x > width + 40) l.x = -40;
            if (l.x < -40) l.x = width + 40;

            drawLantern(l);
        }

        ambientAnimId = requestAnimationFrame(loop);
    };

    loop();

    return () => {
        window.removeEventListener('resize', onResize);
        if (ambientAnimId) cancelAnimationFrame(ambientAnimId);
    };
};

// =========================================================================
// 3. REVOLUTIONARY MINI-GAME: "BENGKEL LATERNA PINGXI 3D"
// (True Workshop: Choose Blessing Color, Write Wish, Ignite & Fly with Physics)
// =========================================================================
const gameCanvasRef = ref<HTMLCanvasElement | null>(null);
const gameContainerRef = ref<HTMLElement | null>(null);
const totalLanternsLaunched = ref(388);
const isIgniting = ref(false);
const customWishInput = ref('');

// 4 Auspicious Lantern Themes
interface LanternTheme {
    id: string;
    name: string;
    hanzi: string;
    meaning: string;
    colors: [string, string, string]; // Top, Middle, Base
    flameColor: string;
}

const lanternThemes: LanternTheme[] = [
    {
        id: 'red',
        name: 'Merah Kebahagiaan',
        hanzi: '囍',
        meaning: 'Cinta Abadi & Samawa',
        colors: ['#FEF08A', '#F97316', '#DC2626'],
        flameColor: '#F59E0B',
    },
    {
        id: 'gold',
        name: 'Kuning Keemasan',
        hanzi: '財',
        meaning: 'Kemakmuran & Rezeki',
        colors: ['#FFFBEB', '#FBBF24', '#D97706'],
        flameColor: '#FBBF24',
    },
    {
        id: 'jade',
        name: 'Giok Kedamaian',
        hanzi: '安',
        meaning: 'Kesehatan & Keselamatan',
        colors: ['#ECFDF5', '#34D399', '#059669'],
        flameColor: '#6EE7B7',
    },
    {
        id: 'lotus',
        name: 'Teratai Romansa',
        hanzi: '和',
        meaning: 'Keharmonisan Sejati',
        colors: ['#FFF1F2', '#FB7185', '#BE185D'],
        flameColor: '#FDA4AF',
    },
];

const selectedTheme = ref<LanternTheme>(lanternThemes[0]);

// Active Flying Lanterns inside Game Canvas
interface GameLantern {
    x: number;
    y: number;
    w: number;
    h: number;
    speedY: number;
    speedX: number;
    sway: number;
    swaySpeed: number;
    scale: number;
    opacity: number;
    theme: LanternTheme;
    wishText: string;
    authorName?: string;
    flamePulse: number;
    isCustom: boolean;
}

const gameLanterns: GameLantern[] = [];
let gameAnimId: number | null = null;
let gameCtx: CanvasRenderingContext2D | null = null;

const initInteractiveGame = () => {
    const canvas = gameCanvasRef.value;
    const container = gameContainerRef.value;
    if (!canvas || !container) return;

    gameCtx = canvas.getContext('2d');
    if (!gameCtx) return;

    let width = (canvas.width = container.clientWidth);
    let height = (canvas.height = container.clientHeight);

    const onResize = () => {
        if (!canvas || !container) return;
        width = canvas.width = container.clientWidth;
        height = canvas.height = container.clientHeight;
    };
    window.addEventListener('resize', onResize);

    // Populate initial floating fleet in the festival sky
    const fleetWishes = [
        'Semoga langgeng selamanya',
        'Cinta abadi hingga akhir waktu',
        'Rezeki berlimpah & berkah',
        'Bahagia dunia dan akhirat',
        'Dua jiwa satu tujuan',
        'Keluarga rukun dan damai',
    ];

    for (let i = 0; i < 16; i++) {
        const t = lanternThemes[i % lanternThemes.length];
        gameLanterns.push({
            x: Math.random() * width,
            y: Math.random() * height,
            w: 26 + Math.random() * 16,
            h: 36 + Math.random() * 20,
            speedY: -(0.55 + Math.random() * 0.7),
            speedX: (Math.random() - 0.5) * 0.4,
            sway: Math.random() * Math.PI * 2,
            swaySpeed: 0.02 + Math.random() * 0.015,
            scale: 0.65 + Math.random() * 0.5,
            opacity: 0.85,
            theme: t,
            wishText: fleetWishes[i % fleetWishes.length],
            flamePulse: Math.random() * Math.PI * 2,
            isCustom: false,
        });
    }

    const drawGameLantern = (l: GameLantern) => {
        if (!gameCtx) return;
        const ctx = gameCtx;

        ctx.save();
        ctx.translate(l.x, l.y);
        ctx.rotate(Math.sin(l.sway) * 0.07);
        ctx.scale(l.scale, l.scale);

        // Radiant Atmosphere Aura
        const auraR = l.w * 2.2;
        const auraGrad = ctx.createRadialGradient(0, 0, 2, 0, 0, auraR);
        auraGrad.addColorStop(0, `${l.theme.flameColor}88`);
        auraGrad.addColorStop(0.5, `${l.theme.colors[1]}33`);
        auraGrad.addColorStop(1, 'transparent');
        ctx.fillStyle = auraGrad;
        ctx.beginPath();
        ctx.arc(0, 0, auraR, 0, Math.PI * 2);
        ctx.fill();

        // 3D Paper Chamber
        const bodyGrad = ctx.createLinearGradient(0, -l.h / 2, 0, l.h / 2);
        bodyGrad.addColorStop(0, l.theme.colors[0]);
        bodyGrad.addColorStop(0.4, l.theme.colors[1]);
        bodyGrad.addColorStop(1, l.theme.colors[2]);
        ctx.fillStyle = bodyGrad;

        ctx.beginPath();
        ctx.moveTo(-l.w / 2, -l.h / 2 + 4);
        ctx.quadraticCurveTo(0, -l.h / 2 - 3, l.w / 2, -l.h / 2 + 4);
        ctx.quadraticCurveTo(l.w / 2 + 4, 0, l.w / 2 - 3, l.h / 2);
        ctx.lineTo(-l.w / 2 + 3, l.h / 2);
        ctx.quadraticCurveTo(-l.w / 2 - 4, 0, -l.w / 2, -l.h / 2 + 4);
        ctx.closePath();
        ctx.fill();

        // Fine Bamboo Ribs
        ctx.strokeStyle = 'rgba(120, 53, 15, 0.45)';
        ctx.lineWidth = 1;
        ctx.stroke();

        // Calligraphy Hanzi Symbol in center of lantern
        ctx.fillStyle = 'rgba(120, 53, 15, 0.75)';
        ctx.font = `bold ${Math.round(l.w * 0.45)}px serif`;
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText(l.theme.hanzi, 0, -2);

        // Internal Burner Flame
        const flameR = (l.w * 0.35) * (0.88 + Math.sin(l.flamePulse) * 0.15);
        const flameGrad = ctx.createRadialGradient(0, l.h / 2 - 4, 1, 0, l.h / 2 - 4, flameR);
        flameGrad.addColorStop(0, '#FFFFFF');
        flameGrad.addColorStop(0.3, '#FEF08A');
        flameGrad.addColorStop(0.8, l.theme.flameColor);
        flameGrad.addColorStop(1, 'transparent');
        ctx.fillStyle = flameGrad;
        ctx.beginPath();
        ctx.arc(0, l.h / 2 - 4, flameR, 0, Math.PI * 2);
        ctx.fill();

        // Wish Inscription Silk Ribbon Hanging Below
        ctx.save();
        ctx.translate(0, l.h / 2 + 2);
        ctx.rotate(Math.sin(l.sway * 1.5) * 0.1);

        // Ribbon tag
        ctx.fillStyle = l.theme.colors[2];
        ctx.strokeStyle = '#D4AF37';
        ctx.lineWidth = 0.8;
        const tagW = Math.max(l.w * 1.6, 60);
        ctx.beginPath();
        ctx.roundRect(-tagW / 2, 0, tagW, 14, 3);
        ctx.fill();
        ctx.stroke();

        ctx.fillStyle = '#FEF3C7';
        ctx.font = 'bold 8px sans-serif';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        const displayWish = l.wishText.length > 15 ? l.wishText.slice(0, 14) + '..' : l.wishText;
        ctx.fillText(displayWish, 0, 7);
        ctx.restore();

        ctx.restore();
    };

    const render = () => {
        ctx.clearRect(0, 0, width, height);

        for (let i = gameLanterns.length - 1; i >= 0; i--) {
            const l = gameLanterns[i];
            l.y += l.speedY;
            l.sway += l.swaySpeed;
            l.flamePulse += 0.08;
            l.x += l.speedX + Math.sin(l.sway) * 0.55;

            // Perspective fade as it rises to deep heaven
            l.scale = Math.max(0.3, l.scale - 0.0005);
            l.opacity = Math.max(0.15, l.opacity - 0.0003);

            drawGameLantern(l);

            // Recycle or remove off-screen
            if (l.y < -90 || l.opacity <= 0.15) {
                if (l.isCustom) {
                    gameLanterns.splice(i, 1);
                } else {
                    l.y = height + 50;
                    l.x = Math.random() * width;
                    l.scale = 0.65 + Math.random() * 0.45;
                    l.opacity = 0.85;
                }
            }
        }

        gameAnimId = requestAnimationFrame(render);
    };

    render();

    return () => {
        window.removeEventListener('resize', onResize);
        if (gameAnimId) cancelAnimationFrame(gameAnimId);
    };
};

// Launch Custom Wish Lantern
const launchWorkshopLantern = () => {
    const container = gameContainerRef.value;
    if (!container || isIgniting.value) return;

    isIgniting.value = true;

    const wish = customWishInput.value.trim() || selectedTheme.value.meaning;

    // Launch celebratory micro sparks
    confetti({
        particleCount: 35,
        spread: 60,
        origin: { y: 0.8 },
        colors: [selectedTheme.value.flameColor, '#F59E0B', '#FDE68A', '#DC2626'],
    });

    const newLantern: GameLantern = {
        x: container.clientWidth / 2 + (Math.random() - 0.5) * 60,
        y: container.clientHeight - 30,
        w: 38,
        h: 52,
        speedY: -(1.6 + Math.random() * 0.6),
        speedX: (Math.random() - 0.5) * 0.5,
        sway: 0,
        swaySpeed: 0.03,
        scale: 1.15,
        opacity: 1,
        theme: selectedTheme.value,
        wishText: wish,
        authorName: props.guestName || 'Tamu Undangan',
        flamePulse: 0,
        isCustom: true,
    };

    gameLanterns.push(newLantern);
    totalLanternsLaunched.value++;
    customWishInput.value = '';

    setTimeout(() => {
        isIgniting.value = false;
    }, 800);
};

// Tap on sky to trigger sparklers
const handleSkyTap = (e: MouseEvent | TouchEvent) => {
    const container = gameContainerRef.value;
    if (!container) return;

    const rect = container.getBoundingClientRect();
    let x = container.clientWidth / 2;
    let y = container.clientHeight / 2;

    if ('clientX' in e) {
        x = e.clientX - rect.left;
        y = e.clientY - rect.top;
    }

    confetti({
        particleCount: 15,
        spread: 40,
        origin: {
            x: (rect.left + x) / window.innerWidth,
            y: (rect.top + y) / window.innerHeight,
        },
        colors: ['#F59E0B', '#FBBF24', '#FEF08A'],
    });
};

let cleanupAmbient: (() => void) | undefined;
let cleanupGame: (() => void) | undefined;

onMounted(() => {
    nextTick(() => {
        cleanupAmbient = initAmbientLanterns();
        cleanupGame = initInteractiveGame();

        // GSAP Scroll Triggers for smooth section appearance
        const sections = document.querySelectorAll('.scroll-fade-up');
        sections.forEach((sec) => {
            gsap.fromTo(
                sec,
                { opacity: 0, y: 30 },
                {
                    opacity: 1,
                    y: 0,
                    duration: 0.8,
                    ease: 'power2.out',
                    scrollTrigger: {
                        trigger: sec,
                        start: 'top 88%',
                        toggleActions: 'play none none none',
                    },
                }
            );
        });
    });
});

onBeforeUnmount(() => {
    if (cleanupAmbient) cleanupAmbient();
    if (cleanupGame) cleanupGame();
});
</script>

<template>
    <div
        class="min-h-screen w-full relative bg-[#09080F] text-[#FDF4E3] overflow-x-hidden selection:bg-amber-500 selection:text-stone-900"
        style="font-family: 'Cinzel', serif;"
    >
        <!-- Persistent 60fps Ambient Floating Sky Lanterns Canvas -->
        <canvas
            ref="ambientCanvasRef"
            class="fixed inset-0 pointer-events-none z-10 w-full h-full"
        ></canvas>

        <!-- Starlight Dust Texture Overlay -->
        <div
            class="fixed inset-0 pointer-events-none z-0 opacity-25 mix-blend-screen"
            style="background-image: radial-gradient(#F59E0B 1px, transparent 1px); background-size: 32px 32px;"
        ></div>

        <!-- Hanging Lantern Garlands on Top (Decorative Chinese Festival Atmosphere) -->
        <div class="fixed top-0 inset-x-0 z-30 pointer-events-none flex justify-between px-4 sm:px-12">
            <div class="w-10 sm:w-14 h-16 sm:h-20 rounded-b-2xl bg-gradient-to-b from-[#B91C1C] to-[#881337] border-2 border-[#D4AF37] shadow-[0_5px_25px_rgba(245,158,11,0.6)] flex flex-col items-center justify-between p-1.5 animate-lantern-sway origin-top">
                <span class="text-[9px] text-amber-200">福</span>
                <div class="w-1 h-3 bg-[#D4AF37]"></div>
            </div>
            <div class="w-10 sm:w-14 h-16 sm:h-20 rounded-b-2xl bg-gradient-to-b from-[#B91C1C] to-[#881337] border-2 border-[#D4AF37] shadow-[0_5px_25px_rgba(245,158,11,0.6)] flex flex-col items-center justify-between p-1.5 animate-lantern-sway origin-top" style="animation-delay: -1.4s;">
                <span class="text-[9px] text-amber-200">囍</span>
                <div class="w-1 h-3 bg-[#D4AF37]"></div>
            </div>
        </div>

        <!-- =====================================================================
             1. REVOLUTIONARY COVER: 3D KONGMING SKY LANTERN ASCENSION
             (A Giant Luminous Lantern Suspended in the Mist of Pingxi Night Sky)
             ===================================================================== -->
        <div
            v-if="isCoverVisible"
            class="fixed inset-0 z-50 flex flex-col items-center justify-between p-6 overflow-hidden bg-[#07060B] transition-opacity duration-1000"
            :class="{ 'opacity-0 pointer-events-none': !isCoverVisible }"
        >
            <!-- Background: Deep misty night mountains & thousand glowing lanterns -->
            <div class="absolute inset-0 pointer-events-none">
                <img
                    src="/images/templates/lantern/sky-lanterns-night.jpg"
                    alt="Night Sky of Pingxi"
                    class="w-full h-full object-cover filter brightness-35 contrast-125 scale-105"
                />
                <div class="absolute inset-0 bg-gradient-to-t from-[#07060B] via-black/50 to-[#07060B]/90"></div>
            </div>

            <!-- Top Auspicious Seal -->
            <div class="relative z-10 text-center pt-8 transition-transform duration-700" :class="{ '-translate-y-24 opacity-0': isAscending }">
                <div class="inline-block px-5 py-1.5 rounded-full border border-amber-400/50 bg-black/60 backdrop-blur-md text-[11px] uppercase tracking-[0.35em] text-amber-200 shadow-xl">
                    平溪天燈 • PINGXI SKY LANTERN FESTIVAL
                </div>
                <div class="mt-2 text-xs text-amber-300/80 tracking-widest font-light">
                    The Wedding of
                </div>
            </div>

            <!-- CENTER: 3D MAJESTIC KONGMING SKY LANTERN (Takes flight when opened) -->
            <div
                class="relative z-20 flex flex-col items-center justify-center my-auto transition-all duration-[1600ms] ease-[cubic-bezier(0.2,0.8,0.2,1)]"
                :class="{ '-translate-y-[120vh] scale-125 opacity-0': isAscending }"
            >
                <!-- Glowing Aura Behind Lantern -->
                <div class="absolute w-72 sm:w-96 h-72 sm:h-96 rounded-full bg-gradient-to-tr from-amber-600/40 via-rose-600/30 to-transparent blur-3xl animate-pulse pointer-events-none"></div>

                <!-- 3D Paper Sky Lantern Chamber -->
                <div class="relative w-56 sm:w-72 h-72 sm:h-92 rounded-t-[40px] rounded-b-[20px] p-6 bg-gradient-to-b from-[#FEF08A] via-[#F97316] to-[#991B1B] border-2 border-amber-300 shadow-[0_0_60px_rgba(245,158,11,0.7)] flex flex-col justify-between items-center text-center transform hover:scale-105 transition-transform duration-500">
                    <!-- Bamboo top rim -->
                    <div class="w-full h-1 bg-amber-900/60 rounded-full"></div>

                    <!-- Auspicious Inscription on the Lantern Paper -->
                    <div class="flex flex-col items-center">
                        <div class="text-4xl sm:text-5xl font-bold text-amber-950 font-serif mb-1">
                            囍
                        </div>
                        <div class="text-xl sm:text-2xl font-bold text-stone-900 font-serif tracking-widest">
                            {{ groom.nickname }} &amp; {{ bride.nickname }}
                        </div>
                        <div class="text-[11px] text-amber-950/80 uppercase tracking-widest mt-1">
                            百年好合 • 永結同心
                        </div>
                    </div>

                    <!-- Glowing Flame Burner at Base -->
                    <div class="relative flex flex-col items-center">
                        <div class="w-12 h-12 rounded-full bg-amber-200/90 shadow-[0_0_30px_#FFFFFF] flex items-center justify-center animate-pulse">
                            <div class="w-6 h-6 rounded-full bg-amber-400"></div>
                        </div>
                        <div class="w-36 h-2 bg-amber-950/80 rounded-full mt-2"></div>
                    </div>

                    <!-- Hanging Silk Tassels -->
                    <div class="absolute -bottom-10 inset-x-0 flex justify-center space-x-6">
                        <div class="w-2 h-10 bg-gradient-to-b from-[#D4AF37] to-[#B91C1C] rounded-b-md shadow-lg animate-lantern-sway"></div>
                        <div class="w-2 h-12 bg-gradient-to-b from-[#D4AF37] to-[#B91C1C] rounded-b-md shadow-lg animate-lantern-sway" style="animation-delay: -0.5s;"></div>
                        <div class="w-2 h-10 bg-gradient-to-b from-[#D4AF37] to-[#B91C1C] rounded-b-md shadow-lg animate-lantern-sway" style="animation-delay: -1s;"></div>
                    </div>
                </div>

                <!-- Recipient Card -->
                <div class="mt-14 w-full max-w-xs bg-black/75 backdrop-blur-md border border-amber-400/40 rounded-2xl p-4 text-center shadow-xl">
                    <div class="text-[10px] uppercase tracking-wider text-amber-300/80">Kepada Yth. Bapak/Ibu/Saudara/i:</div>
                    <div class="text-base font-semibold text-amber-100 truncate mt-0.5">
                        {{ props.guestName || 'Tamu Undangan Terhormat' }}
                    </div>
                    <div class="text-[10px] text-amber-200/40 italic mt-0.5">*Mohon maaf bila ada kesalahan gelar</div>
                </div>
            </div>

            <!-- Bottom Action: Ignite & Release -->
            <div class="relative z-10 pb-8 text-center transition-transform duration-700" :class="{ 'translate-y-24 opacity-0': isAscending }">
                <button
                    @click="handleIgniteAndAscend"
                    class="group relative inline-flex items-center justify-center px-8 py-4 rounded-full text-stone-950 bg-gradient-to-r from-[#F59E0B] via-[#FBBF24] to-[#F59E0B] shadow-[0_0_35px_rgba(245,158,11,0.5)] hover:shadow-amber-500/80 hover:scale-105 active:scale-95 transition-all duration-300 font-bold tracking-widest text-xs uppercase overflow-hidden"
                >
                    <span class="absolute inset-0 bg-white/30 transform -translate-x-full group-hover:translate-x-full transition-transform duration-700"></span>
                    <span class="mr-2 text-base">🔥</span>
                    <span>Nyalakan &amp; Terbangkan Lentera</span>
                </button>
                <div class="text-[10px] text-amber-200/60 uppercase tracking-widest mt-3">
                    Sentuh tombol untuk menerbangkan lentera doa
                </div>
            </div>
        </div>

        <!-- =====================================================================
             2. MAIN INVITATION: IMPERIAL HANGING SILK SCROLL (卷轴 - GUAZHOU)
             (Unique, Centered, Culturally Authentic Brocade Experience)
             ===================================================================== -->
        <main class="relative z-20 max-w-3xl mx-auto px-4 py-16 sm:py-24">

            <!-- Top Jade Finial Roller Bar -->
            <div class="relative w-full h-8 bg-gradient-to-r from-[#1E293B] via-[#059669] to-[#1E293B] rounded-full border-2 border-[#D4AF37] shadow-[0_10px_30px_rgba(0,0,0,0.8)] flex items-center justify-between px-4 mb-4">
                <div class="w-4 h-4 rounded-full bg-[#D4AF37] border border-amber-200"></div>
                <div class="text-[11px] font-bold text-amber-200 uppercase tracking-[0.4em]">天賜良緣 • ETERNAL BLESSINGS</div>
                <div class="w-4 h-4 rounded-full bg-[#D4AF37] border border-amber-200"></div>
            </div>

            <!-- Silk Scroll Body -->
            <div class="relative bg-gradient-to-b from-[#181119] via-[#120B13] to-[#181119] border-x-4 border-[#D4AF37] shadow-[0_25px_60px_rgba(0,0,0,0.9)] p-6 sm:p-12 overflow-hidden">
                <!-- Golden Corner Brocade Patterns -->
                <div class="absolute top-2 left-2 text-[#D4AF37] text-2xl select-none">╔</div>
                <div class="absolute top-2 right-2 text-[#D4AF37] text-2xl select-none">╗</div>
                <div class="absolute bottom-2 left-2 text-[#D4AF37] text-2xl select-none">╚</div>
                <div class="absolute bottom-2 right-2 text-[#D4AF37] text-2xl select-none">╝</div>

                <!-- SECTION 1: HERO COUPLE & ROYAL SEAL -->
                <section class="text-center scroll-fade-up pt-4 pb-12">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gradient-to-tr from-[#991B1B] to-[#DC2626] border-2 border-[#D4AF37] shadow-[0_0_25px_rgba(245,158,11,0.5)] flex items-center justify-center text-amber-200 text-2xl font-bold font-serif">
                        囍
                    </div>

                    <div class="text-xs text-amber-300/80 uppercase tracking-[0.35em] mb-2">
                        THE WEDDING CELEBRATION OF
                    </div>

                    <h1 class="text-4xl sm:text-5xl font-bold text-amber-100 font-serif tracking-wide mb-2 drop-shadow-lg">
                        {{ groom.nickname }} &amp; {{ bride.nickname }}
                    </h1>

                    <p class="text-xs text-amber-200/70 uppercase tracking-widest mb-8">
                        {{ safeFormatDisplayDate(weddingDate) }}
                    </p>

                    <!-- Real Couple Portrait in Gold Frame -->
                    <div class="relative w-full max-w-md mx-auto aspect-[4/5] rounded-3xl p-3 bg-gradient-to-tr from-[#D4AF37] via-[#B91C1C] to-[#F59E0B] shadow-2xl border-2 border-amber-300/40 overflow-hidden group">
                        <img
                            :src="coverPhoto"
                            alt="Mempelai Pengantin"
                            class="w-full h-full object-cover rounded-2xl group-hover:scale-105 transition-transform duration-700 filter brightness-95"
                        />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent pointer-events-none"></div>
                        <div class="absolute bottom-6 inset-x-0 text-center">
                            <div class="inline-block px-4 py-1.5 rounded-full bg-black/70 backdrop-blur-md border border-amber-400/60 text-amber-200 text-xs font-serif tracking-widest">
                                緣定三生 • PERIKATAN CINTA ABADI
                            </div>
                        </div>
                    </div>
                </section>

                <!-- SECTION 2: PROFIL KEDUA MEMPELAI -->
                <section class="py-12 border-t border-amber-500/20 scroll-fade-up">
                    <div class="text-center mb-10">
                        <div class="text-xs text-amber-300 uppercase tracking-[0.3em] font-semibold mb-1">MEMPELAI PENGANTIN</div>
                        <h2 class="text-2xl sm:text-3xl font-serif text-amber-100 font-bold">Dua Jiwa Bersatu Dalam Harmoni</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Mempelai Pria -->
                        <div class="p-6 rounded-3xl bg-black/40 border border-amber-500/30 text-center flex flex-col items-center shadow-lg">
                            <div class="w-32 h-32 rounded-full p-1.5 bg-gradient-to-tr from-[#D4AF37] to-[#B91C1C] mb-4 shadow-xl">
                                <img :src="groom.photo" alt="Mempelai Pria" class="w-full h-full object-cover rounded-full" />
                            </div>
                            <h3 class="text-xl font-bold font-serif text-amber-100">{{ groom.fullName }}</h3>
                            <div class="text-xs text-amber-300/80 tracking-widest mb-3 font-semibold">MEMPELAI PRIA</div>
                            <p class="text-xs text-amber-200/70 leading-relaxed mb-4">{{ groom.parents }}</p>
                            <a v-if="groom.instagram" :href="'https://instagram.com/' + groom.instagram.replace('@', '')" target="_blank" class="text-xs text-amber-300 hover:text-amber-100 underline">
                                {{ groom.instagram }}
                            </a>
                        </div>

                        <!-- Mempelai Wanita -->
                        <div class="p-6 rounded-3xl bg-black/40 border border-amber-500/30 text-center flex flex-col items-center shadow-lg">
                            <div class="w-32 h-32 rounded-full p-1.5 bg-gradient-to-tr from-[#D4AF37] to-[#B91C1C] mb-4 shadow-xl">
                                <img :src="bride.photo" alt="Mempelai Wanita" class="w-full h-full object-cover rounded-full" />
                            </div>
                            <h3 class="text-xl font-bold font-serif text-amber-100">{{ bride.fullName }}</h3>
                            <div class="text-xs text-amber-300/80 tracking-widest mb-3 font-semibold">MEMPELAI WANITA</div>
                            <p class="text-xs text-amber-200/70 leading-relaxed mb-4">{{ bride.parents }}</p>
                            <a v-if="bride.instagram" :href="'https://instagram.com/' + bride.instagram.replace('@', '')" target="_blank" class="text-xs text-amber-300 hover:text-amber-100 underline">
                                {{ bride.instagram }}
                            </a>
                        </div>
                    </div>
                </section>

                <!-- SECTION 3: REVOLUTIONARY MINI-GAME "BENGKEL LATERNA HARAPAN" -->
                <section class="py-12 border-t border-amber-500/20 scroll-fade-up">
                    <div class="text-center mb-6">
                        <div class="inline-block px-4 py-1 rounded-full bg-amber-500/10 border border-amber-500/30 text-amber-300 text-[10px] uppercase tracking-[0.25em] mb-2 font-bold">
                            INTERACTIVE FESTIVAL MINI-GAME
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-serif text-amber-100 font-bold">
                            Terbangkan Lentera Harapan Cinta
                        </h2>
                        <p class="text-xs text-amber-200/70 max-w-md mx-auto mt-2">
                            Pilih warna berkah, tuliskan doa restu, lalu sentuh tombol untuk menyalakan dan melepaskan lentera Anda ke langit Pingxi!
                        </p>
                    </div>

                    <!-- Workshop Card & Live Canvas Stage -->
                    <div class="relative rounded-3xl bg-black/60 border-2 border-amber-400/40 p-4 sm:p-6 overflow-hidden shadow-2xl">
                        <!-- Interactive Canvas Viewport (Night Sky of Pingxi) -->
                        <div
                            ref="gameContainerRef"
                            @click="handleSkyTap"
                            class="relative w-full h-72 sm:h-84 rounded-2xl overflow-hidden cursor-pointer border border-amber-500/30 bg-[#0B0914]"
                        >
                            <canvas ref="gameCanvasRef" class="w-full h-full block"></canvas>
                            <!-- Mountain silhouettes at bottom -->
                            <div class="absolute bottom-0 inset-x-0 h-16 bg-gradient-to-t from-black via-black/40 to-transparent pointer-events-none"></div>

                            <!-- Live Counter Badge -->
                            <div class="absolute top-3 left-3 px-3 py-1.5 rounded-full bg-black/70 backdrop-blur-md border border-amber-400/40 text-[11px] text-amber-200 flex items-center space-x-2">
                                <span class="animate-pulse text-amber-400">🔥</span>
                                <span><strong>{{ totalLanternsLaunched }}</strong> Lentera Telah Mengangkasa</span>
                            </div>

                            <div class="absolute bottom-3 right-3 text-[10px] text-amber-200/60 bg-black/50 px-2.5 py-1 rounded-md backdrop-blur-sm">
                                *Ketuk langit untuk meletupkan kembang api
                            </div>
                        </div>

                        <!-- Workshop Controls -->
                        <div class="mt-6 pt-4 border-t border-amber-500/20">
                            <!-- Theme Palette Selection -->
                            <div class="text-[11px] text-amber-300 font-bold uppercase tracking-wider mb-2">
                                1. Pilih Warna Berkah:
                            </div>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mb-4">
                                <button
                                    v-for="thm in lanternThemes"
                                    :key="thm.id"
                                    @click="selectedTheme = thm"
                                    type="button"
                                    class="p-2.5 rounded-xl border text-left transition-all duration-300 flex items-center space-x-2.5"
                                    :class="selectedTheme.id === thm.id ? 'bg-amber-500/20 border-amber-400 shadow-md' : 'bg-black/40 border-stone-800 hover:border-amber-500/40'"
                                >
                                    <span class="text-xl font-serif font-bold text-amber-300">{{ thm.hanzi }}</span>
                                    <div class="overflow-hidden">
                                        <div class="text-xs font-bold text-amber-100 truncate">{{ thm.name }}</div>
                                        <div class="text-[9px] text-amber-200/60 truncate">{{ thm.meaning }}</div>
                                    </div>
                                </button>
                            </div>

                            <!-- Wish Input -->
                            <div class="text-[11px] text-amber-300 font-bold uppercase tracking-wider mb-2">
                                2. Tuliskan Doa / Harapan Anda:
                            </div>
                            <div class="flex flex-col sm:flex-row gap-2">
                                <input
                                    v-model="customWishInput"
                                    type="text"
                                    maxlength="30"
                                    :placeholder="'Contoh: ' + selectedTheme.meaning"
                                    class="flex-1 px-4 py-3 rounded-xl bg-black/60 border border-amber-500/40 text-amber-100 placeholder-amber-200/30 text-xs focus:outline-none focus:border-amber-400"
                                />
                                <button
                                    @click="launchWorkshopLantern"
                                    :disabled="isIgniting"
                                    type="button"
                                    class="px-6 py-3 rounded-xl bg-gradient-to-r from-[#F59E0B] to-[#DC2626] text-stone-950 font-bold text-xs uppercase tracking-widest shadow-lg hover:shadow-amber-500/50 hover:scale-105 active:scale-95 transition-all duration-300 flex items-center justify-center space-x-2"
                                >
                                    <span>🏮</span>
                                    <span>Nyalakan &amp; Terbangkan</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- SECTION 4: AGENDA ACARA (TIMELINE) -->
                <section class="py-12 border-t border-amber-500/20 scroll-fade-up">
                    <div class="text-center mb-8">
                        <div class="text-xs text-amber-300 uppercase tracking-[0.3em] font-semibold mb-1">RANGKAIAN ACARA</div>
                        <h2 class="text-2xl sm:text-3xl font-serif text-amber-100 font-bold">Hari Bahagia &amp; Syukuran</h2>
                    </div>

                    <div class="space-y-6">
                        <div
                            v-for="ev in props.events"
                            :key="ev.id"
                            class="p-6 rounded-3xl bg-black/40 border border-amber-500/30 shadow-lg relative overflow-hidden"
                        >
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                <div>
                                    <div class="text-xs text-amber-400 uppercase tracking-widest font-bold mb-1">{{ ev.name }}</div>
                                    <div class="text-base font-bold text-amber-100 font-serif">{{ safeFormatEventTime(ev.starts_at, ev.ends_at, ev.timezone) }}</div>
                                    <div class="text-xs text-amber-200/80 mt-1 font-semibold">{{ ev.venue_name }}</div>
                                    <p class="text-xs text-amber-200/60 mt-0.5">{{ ev.address }}</p>
                                </div>
                                <div class="flex flex-wrap sm:flex-col gap-2 shrink-0">
                                    <a
                                        v-if="ev.maps_url"
                                        :href="ev.maps_url"
                                        target="_blank"
                                        class="px-4 py-2 rounded-xl bg-amber-500/20 hover:bg-amber-500/30 border border-amber-400/40 text-amber-200 text-xs font-semibold tracking-wider text-center transition-colors"
                                    >
                                        📍 Petunjuk Arah
                                    </a>
                                    <a
                                        :href="googleCalendarUrl"
                                        target="_blank"
                                        class="px-4 py-2 rounded-xl bg-black/60 hover:bg-black/80 border border-amber-500/30 text-amber-300 text-xs font-semibold tracking-wider text-center transition-colors"
                                    >
                                        📅 Simpan Kalender
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- SECTION 5: GALERI FOTO KEBERSAMAAN -->
                <section class="py-12 border-t border-amber-500/20 scroll-fade-up">
                    <div class="text-center mb-8">
                        <div class="text-xs text-amber-300 uppercase tracking-[0.3em] font-semibold mb-1">MOMEN INDAH</div>
                        <h2 class="text-2xl sm:text-3xl font-serif text-amber-100 font-bold">Galeri Foto Mempelai</h2>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div
                            v-for="(img, idx) in galleryPhotos"
                            :key="idx"
                            @click="openLightbox(img)"
                            class="relative aspect-[4/5] rounded-2xl overflow-hidden cursor-pointer border border-amber-500/30 group"
                        >
                            <img :src="img" alt="Gallery Photo" class="w-full h-full object-cover group-hover:scale-108 transition-transform duration-700" />
                            <div class="absolute inset-0 bg-black/20 group-hover:bg-black/0 transition-colors"></div>
                        </div>
                    </div>
                </section>

                <!-- SECTION 6: KONFIRMASI RSVP & UCAPAN -->
                <section class="py-12 border-t border-amber-500/20 scroll-fade-up">
                    <div class="text-center mb-8">
                        <div class="text-xs text-amber-300 uppercase tracking-[0.3em] font-semibold mb-1">KONFIRMASI KEHADIRAN</div>
                        <h2 class="text-2xl sm:text-3xl font-serif text-amber-100 font-bold">Doa &amp; Restu Anda</h2>
                    </div>

                    <div class="p-6 rounded-3xl bg-black/50 border border-amber-500/30 shadow-xl">
                        <div v-if="props.rsvpSuccess" class="p-4 rounded-2xl bg-emerald-950/80 border border-emerald-500/50 text-emerald-200 text-center text-xs font-bold mb-4">
                            ✨ Terima kasih atas konfirmasi kehadiran dan doa restu Anda!
                        </div>

                        <div v-if="props.rsvpErrorMessage" class="p-4 rounded-2xl bg-rose-950/80 border border-rose-500/50 text-rose-200 text-center text-xs font-bold mb-4">
                            ⚠️ {{ props.rsvpErrorMessage }}
                        </div>

                        <form @submit.prevent="props.submitRsvp" class="space-y-4">
                            <div>
                                <label class="block text-xs text-amber-300 font-semibold mb-1">Nama Tamu</label>
                                <input
                                    v-model="props.rsvpForm.guest_name"
                                    type="text"
                                    required
                                    placeholder="Masukkan nama lengkap Anda"
                                    class="w-full px-4 py-3 rounded-xl bg-black/60 border border-amber-500/30 text-amber-100 text-xs focus:outline-none focus:border-amber-400"
                                />
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs text-amber-300 font-semibold mb-1">Status Kehadiran</label>
                                    <select
                                        v-model="props.rsvpForm.attendance_status"
                                        class="w-full px-4 py-3 rounded-xl bg-black/60 border border-amber-500/30 text-amber-100 text-xs focus:outline-none focus:border-amber-400"
                                    >
                                        <option value="attending">Hadir</option>
                                        <option value="tentative">Masih Ragu</option>
                                        <option value="declined">Berhalangan Hadir</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs text-amber-300 font-semibold mb-1">Jumlah Tamu</label>
                                    <input
                                        v-model.number="props.rsvpForm.attendee_count"
                                        type="number"
                                        min="1"
                                        max="5"
                                        class="w-full px-4 py-3 rounded-xl bg-black/60 border border-amber-500/30 text-amber-100 text-xs focus:outline-none focus:border-amber-400"
                                    />
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs text-amber-300 font-semibold mb-1">Pesan / Doa Restu</label>
                                <textarea
                                    v-model="props.rsvpForm.message"
                                    rows="3"
                                    placeholder="Tuliskan ucapan selamat dan doa untuk mempelai..."
                                    class="w-full px-4 py-3 rounded-xl bg-black/60 border border-amber-500/30 text-amber-100 text-xs focus:outline-none focus:border-amber-400"
                                ></textarea>
                            </div>

                            <button
                                type="submit"
                                :disabled="props.rsvpSubmitting"
                                class="w-full py-3.5 rounded-xl bg-gradient-to-r from-[#F59E0B] to-[#DC2626] text-stone-950 font-bold text-xs uppercase tracking-widest shadow-xl hover:scale-[1.02] active:scale-98 transition-all"
                            >
                                {{ props.rsvpSubmitting ? 'Mengirim...' : 'Kirim Konfirmasi Kehadiran' }}
                            </button>
                        </form>
                    </div>

                    <!-- Buku Tamu List -->
                    <div class="mt-8 space-y-3">
                        <div
                            v-for="entry in props.guestbook"
                            :key="entry.id"
                            class="p-4 rounded-2xl bg-black/40 border border-amber-500/20"
                        >
                            <div class="flex items-center justify-between text-xs mb-1">
                                <span class="font-bold text-amber-200">{{ entry.guest_name }}</span>
                                <span class="text-amber-300/50 text-[10px]">{{ entry.created_at }}</span>
                            </div>
                            <p class="text-xs text-amber-100/80">{{ entry.message }}</p>
                        </div>
                    </div>
                </section>

                <!-- SECTION 7: AMPLOP DIGITAL & KADO KASIH -->
                <section v-if="props.digitalGift?.enabled !== false" class="py-12 border-t border-amber-500/20 scroll-fade-up">
                    <div class="text-center mb-8">
                        <div class="text-xs text-amber-300 uppercase tracking-[0.3em] font-semibold mb-1">TANDA KASIH</div>
                        <h2 class="text-2xl sm:text-3xl font-serif text-amber-100 font-bold">Amplop Digital</h2>
                        <p class="text-xs text-amber-200/70 max-w-sm mx-auto mt-2">
                            Doa restu Anda adalah karunia terindah, namun jika ingin memberikan tanda kasih dapat melalui:
                        </p>
                    </div>

                    <div class="space-y-4">
                        <div
                            v-for="(acc, i) in props.digitalGift?.accounts || []"
                            :key="i"
                            class="p-5 rounded-2xl bg-black/40 border border-amber-500/30 flex flex-col sm:flex-row items-center justify-between gap-4"
                        >
                            <div class="text-center sm:text-left">
                                <div class="text-xs font-bold text-amber-400 tracking-wider">{{ acc.bank || acc.bank_name }}</div>
                                <div class="text-base font-serif font-bold text-amber-100 mt-0.5">{{ acc.account_number }}</div>
                                <div class="text-xs text-amber-200/70">a.n {{ acc.account_name || acc.account_holder }}</div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button
                                    @click="copyBankNumber(acc.account_number, i)"
                                    type="button"
                                    class="px-4 py-2 rounded-xl bg-amber-500/20 hover:bg-amber-500/30 border border-amber-400/40 text-amber-200 text-xs font-semibold"
                                >
                                    {{ copiedBankIndex === i ? '✓ Tersalin' : 'Salin Nomor' }}
                                </button>
                                <button
                                    v-if="acc.qr_image || acc.qris_image_url"
                                    @click="props.openQris(acc)"
                                    type="button"
                                    class="px-4 py-2 rounded-xl bg-black/60 hover:bg-black/80 border border-amber-500/30 text-amber-300 text-xs font-semibold"
                                >
                                    Lihat QRIS
                                </button>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- SECTION 8: PENUTUP & UCAPAN TERIMA KASIH -->
                <section class="py-12 text-center border-t border-amber-500/20 scroll-fade-up">
                    <p class="text-xs text-amber-200/70 italic max-w-md mx-auto mb-6">
                        "Dan di antara tanda-tanda kebesaran-Nya ialah Dia menciptakan pasangan-pasangan untukmu dari jenismu sendiri, agar kamu cenderung dan merasa tenteram kepadanya."
                    </p>
                    <div class="text-xs text-amber-300 font-bold uppercase tracking-widest mb-1">KAMI YANG BERBAHAGIA</div>
                    <div class="text-2xl font-serif font-bold text-amber-100">{{ groom.nickname }} &amp; {{ bride.nickname }}</div>
                    <div class="text-[10px] text-amber-300/50 uppercase tracking-widest mt-6">
                        Pingxi Sky Lantern Edition • UndanganDigital
                    </div>
                </section>
            </div>

            <!-- Bottom Jade Finial Roller Bar -->
            <div class="relative w-full h-8 bg-gradient-to-r from-[#1E293B] via-[#059669] to-[#1E293B] rounded-full border-2 border-[#D4AF37] shadow-[0_10px_30px_rgba(0,0,0,0.8)] flex items-center justify-between px-4 mt-4">
                <div class="w-4 h-4 rounded-full bg-[#D4AF37] border border-amber-200"></div>
                <div class="text-[11px] font-bold text-amber-200 uppercase tracking-[0.4em]">百年好合 • FOREVER TOGETHER</div>
                <div class="w-4 h-4 rounded-full bg-[#D4AF37] border border-amber-200"></div>
            </div>
        </main>

        <!-- Floating Music Player Control -->
        <button
            @click="emit('toggleMusic')"
            type="button"
            class="fixed bottom-6 right-6 z-40 w-12 h-12 rounded-full bg-gradient-to-tr from-[#991B1B] to-[#F59E0B] border-2 border-amber-300 shadow-[0_0_20px_rgba(245,158,11,0.5)] flex items-center justify-center text-amber-100 text-lg hover:scale-110 active:scale-95 transition-all"
        >
            <span :class="{ 'animate-spin': props.isPlayingMusic }">🎵</span>
        </button>

        <!-- Lightbox Modal -->
        <div
            v-if="activeLightboxImg"
            @click="closeLightbox"
            class="fixed inset-0 z-50 bg-black/90 backdrop-blur-md flex items-center justify-center p-4 cursor-pointer"
        >
            <img :src="activeLightboxImg" class="max-w-full max-h-[90vh] rounded-2xl shadow-2xl border border-amber-400/40 object-contain" />
        </div>
    </div>
</template>

<style scoped>
@keyframes lantern-sway {
    0%, 100% {
        transform: rotate(-3deg);
    }
    50% {
        transform: rotate(3deg);
    }
}

.animate-lantern-sway {
    animation: lantern-sway 4.5s ease-in-out infinite;
}
</style>

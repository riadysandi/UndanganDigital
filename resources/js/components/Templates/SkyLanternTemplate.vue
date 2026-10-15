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

// Safe helpers
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

// Normalized couple and design accessors
const groom = computed(() => {
    const c = props.invitationData?.content || {};
    return {
        fullName: c.groom?.fullName || c.groomName || 'Edward Wijaya, B.Eng',
        nickname: c.groom?.nickname || c.groomNickname || 'Edward',
        parents: c.groom?.parents || c.groomParents || 'Putra pertama dari Bpk. Hendra Wijaya & Ibu Mei Ling',
        photo: c.groom?.photo || c.groomPhotoUrl || '/images/templates/lantern/groom-real.jpg',
        instagram: c.groom?.instagram || c.groomInstagram || '',
    };
});

const bride = computed(() => {
    const c = props.invitationData?.content || {};
    return {
        fullName: c.bride?.fullName || c.brideName || 'Clara Sutanto, B.Com',
        nickname: c.bride?.nickname || c.brideNickname || 'Clara',
        parents: c.bride?.parents || c.brideParents || 'Putri kedua dari Bpk. Benny Sutanto & Ibu Lindawati',
        photo: c.bride?.photo || c.bridePhotoUrl || '/images/templates/lantern/bride-real.jpg',
        instagram: c.bride?.instagram || c.brideInstagram || '',
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

// Lightbox state for gallery
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

// 3D Parallax Mouse tilt for Left Stage
const targetRotateX = ref(0);
const targetRotateY = ref(0);

const handleMouseMove = (e: MouseEvent) => {
    const { innerWidth, innerHeight } = window;
    const x = (e.clientX / innerWidth - 0.5) * 2;
    const y = (e.clientY / innerHeight - 0.5) * 2;
    targetRotateY.value = x * 12;
    targetRotateX.value = -y * 12;
};

const handleTouchMove = (e: TouchEvent) => {
    if (!e.touches[0]) return;
    const t = e.touches[0];
    const x = (t.clientX / window.innerWidth - 0.5) * 2;
    const y = (t.clientY / window.innerHeight - 0.5) * 2;
    targetRotateY.value = x * 10;
    targetRotateX.value = -y * 10;
};

const handleStageWheel = (e: WheelEvent) => {
    window.scrollBy({ top: e.deltaY, behavior: 'auto' });
};

// 3D Card Hover Tilt for Groom and Bride
const cardGroomTilt = ref({ x: 0, y: 0, glareX: 50, glareY: 50 });
const cardBrideTilt = ref({ x: 0, y: 0, glareX: 50, glareY: 50 });

const handleCardTilt = (e: MouseEvent, target: 'groom' | 'bride') => {
    const card = e.currentTarget as HTMLElement;
    const rect = card.getBoundingClientRect();
    const x = (e.clientX - rect.left) / rect.width;
    const y = (e.clientY - rect.top) / rect.height;
    const rotX = (y - 0.5) * -18;
    const rotY = (x - 0.5) * 18;

    if (target === 'groom') {
        cardGroomTilt.value = { x: rotX, y: rotY, glareX: x * 100, glareY: y * 100 };
    } else {
        cardBrideTilt.value = { x: rotX, y: rotY, glareX: x * 100, glareY: y * 100 };
    }
};

const resetCardTilt = (target: 'groom' | 'bride') => {
    if (target === 'groom') {
        cardGroomTilt.value = { x: 0, y: 0, glareX: 50, glareY: 50 };
    } else {
        cardBrideTilt.value = { x: 0, y: 0, glareX: 50, glareY: 50 };
    }
};

// ========================================================
// 1. 3D PAGODA LATTICE SCREEN TRANSITION
// ========================================================
const isCoverVisible = ref(!props.isOpen);
const isGateOpening = ref(false);

const handleOpenInvitation = () => {
    if (isGateOpening.value) return;
    isGateOpening.value = true;

    emit('openInvitation');

    // Confetti with rich amber, gold & festive red
    confetti({
        particleCount: 75,
        spread: 90,
        origin: { y: 0.6 },
        colors: ['#F59E0B', '#D4AF37', '#E11D48', '#FFEDD5', '#B91C1C']
    });

    setTimeout(() => {
        isCoverVisible.value = false;
    }, 1300);
};

// ========================================================
// 2. 60 FPS INTERACTIVE SKY LANTERN MINI-GAME & SIMULATION
// ========================================================
const lanternCanvasRef = ref<HTMLCanvasElement | null>(null);
const interactiveGameCanvasRef = ref<HTMLCanvasElement | null>(null);
const gameContainerRef = ref<HTMLElement | null>(null);
const lanternsLaunchedCount = ref(128);
let bgCanvasAnimId: number | null = null;
let gameCanvasAnimId: number | null = null;

// Available Blessing Wishes
const selectedWish = ref('Cinta Abadi & Samawa');
const wishOptions = [
    'Cinta Abadi & Samawa',
    'Rezeki & Berkah Melimpah',
    'Keturunan Saleh & Berbakti',
    'Kesehatan & Bahagia Selamanya',
    'Kedamaian Rumah Tangga',
];

interface LanternParticle {
    x: number;
    y: number;
    w: number;
    h: number;
    speedY: number;
    speedX: number;
    swing: number;
    swingSpeed: number;
    opacity: number;
    scale: number;
    flamePulse: number;
    wishText?: string;
    isCustom?: boolean;
}

// Global Ambient Drifting Lanterns Canvas (Background Layer)
const initAmbientLanternsCanvas = () => {
    const canvas = lanternCanvasRef.value;
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

    const ambientLanterns: LanternParticle[] = [];
    const count = window.innerWidth < 768 ? 16 : 28;

    for (let i = 0; i < count; i++) {
        ambientLanterns.push({
            x: Math.random() * width,
            y: Math.random() * height,
            w: 14 + Math.random() * 12,
            h: 18 + Math.random() * 16,
            speedY: -(0.5 + Math.random() * 0.8),
            speedX: 0.15 + (Math.random() - 0.5) * 0.3,
            swing: Math.random() * Math.PI * 2,
            swingSpeed: 0.02 + Math.random() * 0.015,
            opacity: 0.4 + Math.random() * 0.5,
            scale: 0.6 + Math.random() * 0.6,
            flamePulse: Math.random() * Math.PI * 2,
        });
    }

    const drawLantern = (l: LanternParticle) => {
        ctx.save();
        ctx.translate(l.x, l.y);
        ctx.rotate(Math.sin(l.swing) * 0.08);
        ctx.scale(l.scale, l.scale);

        // Warm Glowing Aura
        const auraRadius = l.w * 2.2;
        const auraGrad = ctx.createRadialGradient(0, 0, 2, 0, 0, auraRadius);
        auraGrad.addColorStop(0, 'rgba(251, 191, 36, 0.45)');
        auraGrad.addColorStop(0.5, 'rgba(245, 158, 11, 0.15)');
        auraGrad.addColorStop(1, 'rgba(217, 119, 6, 0)');
        ctx.fillStyle = auraGrad;
        ctx.beginPath();
        ctx.arc(0, 0, auraRadius, 0, Math.PI * 2);
        ctx.fill();

        // Sky Lantern Body (Rice Paper Silhouette with Warm Gradient)
        const bodyGrad = ctx.createLinearGradient(0, -l.h / 2, 0, l.h / 2);
        bodyGrad.addColorStop(0, `rgba(254, 240, 138, ${l.opacity})`);
        bodyGrad.addColorStop(0.35, `rgba(251, 146, 60, ${l.opacity * 0.95})`);
        bodyGrad.addColorStop(0.85, `rgba(225, 29, 72, ${l.opacity * 0.9})`);
        bodyGrad.addColorStop(1, `rgba(159, 18, 57, ${l.opacity * 0.8})`);
        ctx.fillStyle = bodyGrad;

        ctx.beginPath();
        ctx.moveTo(-l.w / 2, -l.h / 2 + 3);
        ctx.quadraticCurveTo(0, -l.h / 2 - 2, l.w / 2, -l.h / 2 + 3);
        ctx.quadraticCurveTo(l.w / 2 + 3, 0, l.w / 2 - 2, l.h / 2);
        ctx.lineTo(-l.w / 2 + 2, l.h / 2);
        ctx.quadraticCurveTo(-l.w / 2 - 3, 0, -l.w / 2, -l.h / 2 + 3);
        ctx.closePath();
        ctx.fill();

        // Bamboo Rim Bottom
        ctx.strokeStyle = 'rgba(120, 53, 15, 0.7)';
        ctx.lineWidth = 1.2;
        ctx.stroke();

        // Inner Burning Candle Flame
        const flameSize = (l.w * 0.35) * (0.85 + Math.sin(l.flamePulse) * 0.15);
        const flameGrad = ctx.createRadialGradient(0, l.h / 2 - 3, 1, 0, l.h / 2 - 3, flameSize);
        flameGrad.addColorStop(0, '#FFFFFF');
        flameGrad.addColorStop(0.3, '#FEF08A');
        flameGrad.addColorStop(0.7, '#F59E0B');
        flameGrad.addColorStop(1, 'rgba(239, 68, 68, 0)');
        ctx.fillStyle = flameGrad;
        ctx.beginPath();
        ctx.arc(0, l.h / 2 - 3, flameSize, 0, Math.PI * 2);
        ctx.fill();

        ctx.restore();
    };

    const render = () => {
        ctx.clearRect(0, 0, width, height);

        for (const l of ambientLanterns) {
            l.y += l.speedY;
            l.swing += l.swingSpeed;
            l.flamePulse += 0.08;
            l.x += l.speedX + Math.sin(l.swing) * 0.4;

            // Loop back to bottom
            if (l.y < -50) {
                l.y = height + 40;
                l.x = Math.random() * width;
            }
            if (l.x > width + 40) l.x = -40;
            if (l.x < -40) l.x = width + 40;

            drawLantern(l);
        }

        bgCanvasAnimId = requestAnimationFrame(render);
    };

    render();

    return () => {
        window.removeEventListener('resize', onResize);
        if (bgCanvasAnimId) cancelAnimationFrame(bgCanvasAnimId);
    };
};

// Interactive Dedicated Sky Lantern Stage Mini-Game (Inside Section 3)
const customLanterns: LanternParticle[] = [];
let gameCtx: CanvasRenderingContext2D | null = null;

const initInteractiveGameCanvas = () => {
    const canvas = interactiveGameCanvasRef.value;
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

    // Initial ambient festival lanterns in the game view
    for (let i = 0; i < 14; i++) {
        customLanterns.push({
            x: Math.random() * width,
            y: Math.random() * height,
            w: 24 + Math.random() * 16,
            h: 32 + Math.random() * 20,
            speedY: -(0.7 + Math.random() * 0.9),
            speedX: (Math.random() - 0.5) * 0.5,
            swing: Math.random() * Math.PI * 2,
            swingSpeed: 0.025 + Math.random() * 0.02,
            opacity: 0.8 + Math.random() * 0.2,
            scale: 0.7 + Math.random() * 0.6,
            flamePulse: Math.random() * Math.PI * 2,
            wishText: wishOptions[i % wishOptions.length],
            isCustom: false,
        });
    }

    const drawCustomLantern = (l: LanternParticle) => {
        if (!gameCtx) return;
        const ctx = gameCtx;

        ctx.save();
        ctx.translate(l.x, l.y);
        ctx.rotate(Math.sin(l.swing) * 0.07);
        ctx.scale(l.scale, l.scale);

        // Radiant Outer Heat Aura
        const auraR = l.w * 2.8;
        const auraGrad = ctx.createRadialGradient(0, 0, 2, 0, 0, auraR);
        auraGrad.addColorStop(0, 'rgba(251, 191, 36, 0.6)');
        auraGrad.addColorStop(0.4, 'rgba(245, 158, 11, 0.25)');
        auraGrad.addColorStop(1, 'rgba(225, 29, 72, 0)');
        ctx.fillStyle = auraGrad;
        ctx.beginPath();
        ctx.arc(0, 0, auraR, 0, Math.PI * 2);
        ctx.fill();

        // 3D Lantern Rice Paper Chamber
        const bodyGrad = ctx.createLinearGradient(0, -l.h / 2, 0, l.h / 2);
        bodyGrad.addColorStop(0, '#FEF08A');
        bodyGrad.addColorStop(0.35, '#FB923C');
        bodyGrad.addColorStop(0.85, '#E11D48');
        bodyGrad.addColorStop(1, '#9F1239');
        ctx.fillStyle = bodyGrad;

        ctx.beginPath();
        ctx.moveTo(-l.w / 2, -l.h / 2 + 4);
        ctx.quadraticCurveTo(0, -l.h / 2 - 3, l.w / 2, -l.h / 2 + 4);
        ctx.quadraticCurveTo(l.w / 2 + 5, 0, l.w / 2 - 3, l.h / 2);
        ctx.lineTo(-l.w / 2 + 3, l.h / 2);
        ctx.quadraticCurveTo(-l.w / 2 - 5, 0, -l.w / 2, -l.h / 2 + 4);
        ctx.closePath();
        ctx.fill();

        // Subtle bamboo ribs
        ctx.strokeStyle = 'rgba(120, 53, 15, 0.4)';
        ctx.lineWidth = 1;
        ctx.beginPath();
        ctx.moveTo(0, -l.h / 2);
        ctx.lineTo(0, l.h / 2);
        ctx.stroke();

        // Core Golden Flame with Flutter
        const flameR = (l.w * 0.4) * (0.9 + Math.sin(l.flamePulse) * 0.15);
        const flameGrad = ctx.createRadialGradient(0, l.h / 2 - 4, 1, 0, l.h / 2 - 4, flameR);
        flameGrad.addColorStop(0, '#FFFFFF');
        flameGrad.addColorStop(0.3, '#FEF08A');
        flameGrad.addColorStop(0.7, '#F59E0B');
        flameGrad.addColorStop(1, 'rgba(239, 68, 68, 0)');
        ctx.fillStyle = flameGrad;
        ctx.beginPath();
        ctx.arc(0, l.h / 2 - 4, flameR, 0, Math.PI * 2);
        ctx.fill();

        // Wish Inscription Banner Tag hanging from the lantern
        if (l.wishText) {
            ctx.save();
            ctx.translate(0, l.h / 2 + 2);
            ctx.rotate(Math.sin(l.swing * 1.5) * 0.12);

            ctx.fillStyle = 'rgba(159, 18, 57, 0.9)';
            ctx.strokeStyle = '#D4AF37';
            ctx.lineWidth = 0.8;
            ctx.beginPath();
            ctx.roundRect(-l.w * 0.8, 0, l.w * 1.6, 12, 3);
            ctx.fill();
            ctx.stroke();

            ctx.fillStyle = '#FEF08A';
            ctx.font = 'bold 7px sans-serif';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillText(l.wishText.length > 14 ? l.wishText.slice(0, 13) + '..' : l.wishText, 0, 6);
            ctx.restore();
        }

        ctx.restore();
    };

    const renderGame = () => {
        ctx.clearRect(0, 0, width, height);

        for (let i = customLanterns.length - 1; i >= 0; i--) {
            const l = customLanterns[i];
            l.y += l.speedY;
            l.swing += l.swingSpeed;
            l.flamePulse += 0.1;
            l.x += l.speedX + Math.sin(l.swing) * 0.6;

            // Slowly shrink as it flies higher into the heavens
            l.scale = Math.max(0.25, l.scale - 0.0006);
            l.opacity = Math.max(0.1, l.opacity - 0.0004);

            drawCustomLantern(l);

            // Remove or recycle if off-screen top
            if (l.y < -80 || l.opacity <= 0.1) {
                if (l.isCustom) {
                    customLanterns.splice(i, 1);
                } else {
                    l.y = height + 50;
                    l.x = Math.random() * width;
                    l.scale = 0.7 + Math.random() * 0.5;
                    l.opacity = 0.85;
                }
            }
        }

        gameCanvasAnimId = requestAnimationFrame(renderGame);
    };

    renderGame();

    return () => {
        window.removeEventListener('resize', onResize);
        if (gameCanvasAnimId) cancelAnimationFrame(gameCanvasAnimId);
    };
};

// Launch a Personalized Lantern
const launchPersonalizedLantern = (e?: MouseEvent | TouchEvent) => {
    const container = gameContainerRef.value;
    if (!container) return;

    let launchX = container.clientWidth / 2 + (Math.random() - 0.5) * 80;
    if (e && 'clientX' in e) {
        const rect = container.getBoundingClientRect();
        launchX = e.clientX - rect.left;
    }

    const newLantern: LanternParticle = {
        x: launchX,
        y: container.clientHeight - 20,
        w: 36,
        h: 48,
        speedY: -(1.4 + Math.random() * 0.8),
        speedX: (Math.random() - 0.5) * 0.6,
        swing: 0,
        swingSpeed: 0.035,
        opacity: 1,
        scale: 1.1,
        flamePulse: 0,
        wishText: selectedWish.value,
        isCustom: true,
    };

    customLanterns.push(newLantern);
    lanternsLaunchedCount.value++;

    // Launch celebratory golden micro sparks
    confetti({
        particleCount: 25,
        spread: 45,
        origin: { y: 0.85 },
        colors: ['#F59E0B', '#D4AF37', '#FEF08A', '#E11D48'],
    });
};

let cleanupAmbient: (() => void) | undefined;
let cleanupGame: (() => void) | undefined;

onMounted(() => {
    window.addEventListener('mousemove', handleMouseMove, { passive: true });
    window.addEventListener('touchmove', handleTouchMove, { passive: true });

    nextTick(() => {
        cleanupAmbient = initAmbientLanternsCanvas();
        cleanupGame = initInteractiveGameCanvas();

        const sections = document.querySelectorAll('.lantern-fade-up');
        sections.forEach((sec) => {
            gsap.fromTo(
                sec,
                { opacity: 0, y: 35 },
                {
                    opacity: 1,
                    y: 0,
                    duration: 0.9,
                    ease: 'power2.out',
                    scrollTrigger: {
                        trigger: sec,
                        start: 'top 85%',
                        toggleActions: 'play none none none',
                    },
                }
            );
        });
    });
});

onBeforeUnmount(() => {
    window.removeEventListener('mousemove', handleMouseMove);
    window.removeEventListener('touchmove', handleTouchMove);
    if (cleanupAmbient) cleanupAmbient();
    if (cleanupGame) cleanupGame();
    ScrollTrigger.getAll().forEach((t) => t.kill());
});
</script>

<template>
    <div
        class="min-h-screen w-full relative bg-[#090A10] text-[#F3F4F6] font-serif selection:bg-amber-500 selection:text-stone-950 overflow-x-hidden"
        :style="{
            backgroundColor: invitationData.theme.bgColor || '#090A10',
            color: invitationData.theme.textColor || '#F3F4F6',
        }"
    >
        <!-- Persistent 60fps Ambient Floating Sky Lanterns Canvas -->
        <canvas
            ref="lanternCanvasRef"
            class="fixed inset-0 pointer-events-none z-20 w-full h-full"
        ></canvas>

        <!-- Starlight Dust & Celestial Sky Glow Overlay -->
        <div
            class="fixed inset-0 pointer-events-none z-10 opacity-35 bg-cover bg-center mix-blend-screen"
            style="background-image: radial-gradient(#F59E0B 1px, transparent 1px); background-size: 28px 28px;"
        ></div>

        <!-- ========================================================
             SWAYING CHINESE SILK LANTERNS (Top Left & Top Right)
             ======================================================== -->
        <!-- Top Left Swaying Silk Lantern -->
        <div class="fixed top-0 left-4 sm:left-8 z-30 pointer-events-none animate-lantern-sway origin-top drop-shadow-2xl">
            <div class="w-0.5 h-10 bg-[#D4AF37] mx-auto"></div>
            <div class="relative w-12 sm:w-16 h-16 sm:h-20 rounded-full bg-gradient-to-b from-[#E11D48] via-[#B91C1C] to-[#881337] border-2 border-[#D4AF37] shadow-[0_0_25px_rgba(245,158,11,0.5)] flex items-center justify-center text-amber-200 text-sm font-bold animate-pulse">
                囍
                <!-- Gold Bottom Fringe & Tassel -->
                <div class="absolute -bottom-6 inset-x-0 flex flex-col items-center">
                    <div class="w-5 h-2 bg-[#D4AF37] rounded-sm"></div>
                    <div class="w-1.5 h-6 bg-[#C2410C]"></div>
                </div>
            </div>
        </div>

        <!-- Top Right Swaying Silk Lantern -->
        <div class="fixed top-0 right-4 sm:right-8 z-30 pointer-events-none animate-lantern-sway origin-top drop-shadow-2xl" style="animation-delay: -1.6s;">
            <div class="w-0.5 h-10 bg-[#D4AF37] mx-auto"></div>
            <div class="relative w-12 sm:w-16 h-16 sm:h-20 rounded-full bg-gradient-to-b from-[#E11D48] via-[#B91C1C] to-[#881337] border-2 border-[#D4AF37] shadow-[0_0_25px_rgba(245,158,11,0.5)] flex items-center justify-center text-amber-200 text-sm font-bold animate-pulse">
                福
                <div class="absolute -bottom-6 inset-x-0 flex flex-col items-center">
                    <div class="w-5 h-2 bg-[#D4AF37] rounded-sm"></div>
                    <div class="w-1.5 h-6 bg-[#C2410C]"></div>
                </div>
            </div>
        </div>

        <!-- ========================================================
             1. 3D IMPERIAL PAGODA LATTICE SCREEN (COVER)
             ======================================================== -->
        <div
            v-if="isCoverVisible"
            class="fixed inset-0 z-50 flex items-center justify-center overflow-hidden bg-[#0A0708] transition-opacity duration-300"
            :class="{ 'pointer-events-none': isGateOpening }"
        >
            <!-- Left Pagoda Screen Panel -->
            <div
                class="absolute inset-y-0 left-0 w-1/2 bg-[#1C0E12] border-r-4 border-[#D4AF37] shadow-2xl transition-transform duration-[1200ms] ease-[cubic-bezier(0.2,0.8,0.2,1)] flex flex-col justify-between p-6 overflow-hidden"
                :class="{ '-translate-x-full': isGateOpening }"
            >
                <div class="absolute inset-0 bg-gradient-to-r from-black/80 to-transparent"></div>
                <!-- Intricate Lattice Woodwork Pattern -->
                <div class="absolute inset-0 grid grid-cols-2 grid-rows-5 gap-3 p-6 opacity-30 pointer-events-none">
                    <div v-for="i in 10" :key="'l-lat-' + i" class="border-2 border-[#D4AF37] bg-black/40 shadow-inner flex items-center justify-center">
                        <div class="w-8 h-8 border border-[#D4AF37]/50 rotate-45"></div>
                    </div>
                </div>
                <!-- Imperial Cloud Seal Motif -->
                <div class="relative z-10 text-xs tracking-[0.3em] uppercase text-amber-300 font-semibold">
                    天賜良緣 • BLESSINGS FROM HEAVEN
                </div>
                <!-- Center Dragon Handle -->
                <div class="absolute right-4 top-1/2 -translate-y-1/2 w-8 h-24 rounded-full border-2 border-[#D4AF37] bg-gradient-to-r from-[#881337] to-[#450A0A] shadow-xl flex items-center justify-center">
                    <div class="w-3 h-12 rounded-full bg-[#D4AF37]/60"></div>
                </div>
                <div class="relative z-10 text-[10px] text-amber-200/50 uppercase tracking-widest">
                    Oriental Royal Heritage
                </div>
            </div>

            <!-- Right Pagoda Screen Panel -->
            <div
                class="absolute inset-y-0 right-0 w-1/2 bg-[#1C0E12] border-l-4 border-[#D4AF37] shadow-2xl transition-transform duration-[1200ms] ease-[cubic-bezier(0.2,0.8,0.2,1)] flex flex-col justify-between items-end p-6 overflow-hidden"
                :class="{ 'translate-x-full': isGateOpening }"
            >
                <div class="absolute inset-0 bg-gradient-to-l from-black/80 to-transparent"></div>
                <!-- Intricate Lattice Woodwork Pattern -->
                <div class="absolute inset-0 grid grid-cols-2 grid-rows-5 gap-3 p-6 opacity-30 pointer-events-none">
                    <div v-for="i in 10" :key="'r-lat-' + i" class="border-2 border-[#D4AF37] bg-black/40 shadow-inner flex items-center justify-center">
                        <div class="w-8 h-8 border border-[#D4AF37]/50 rotate-45"></div>
                    </div>
                </div>
                <div class="relative z-10 text-xs tracking-[0.3em] uppercase text-amber-300 font-semibold">
                    百年好合 • ETERNAL HARMONY
                </div>
                <!-- Center Phoenix Handle -->
                <div class="absolute left-4 top-1/2 -translate-y-1/2 w-8 h-24 rounded-full border-2 border-[#D4AF37] bg-gradient-to-l from-[#881337] to-[#450A0A] shadow-xl flex items-center justify-center">
                    <div class="w-3 h-12 rounded-full bg-[#D4AF37]/60"></div>
                </div>
                <div class="relative z-10 text-[10px] text-amber-200/50 uppercase tracking-widest">
                    Pingxi Sky Lantern Edition
                </div>
            </div>

            <!-- Center Opener Card & Golden Double Happiness Emblem -->
            <div
                class="relative z-20 max-w-sm w-full mx-4 p-8 rounded-3xl bg-[#140C0E]/95 backdrop-blur-xl shadow-2xl border-2 border-[#D4AF37]/70 text-center transition-all duration-700 transform"
                :class="{ 'scale-90 opacity-0': isGateOpening }"
            >
                <!-- Glowing Golden Double Happiness Emblem -->
                <div class="w-16 h-16 mx-auto -mt-14 mb-4 rounded-full bg-gradient-to-tr from-[#B91C1C] via-[#E11D48] to-[#991B1B] border-2 border-[#D4AF37] shadow-[0_0_30px_rgba(245,158,11,0.6)] flex items-center justify-center text-amber-200 text-2xl font-bold font-serif">
                    囍
                </div>

                <div class="text-[11px] tracking-[0.35em] uppercase text-amber-300/80 mb-2 font-medium">
                    The Wedding Invitation
                </div>

                <h1 class="text-3xl sm:text-4xl font-serif text-amber-100 font-bold tracking-wide mb-1 drop-shadow-md">
                    {{ groom.nickname }} &amp; {{ bride.nickname }}
                </h1>

                <div class="flex items-center justify-center space-x-3 my-3">
                    <div class="h-[1px] w-12 bg-gradient-to-r from-transparent to-[#D4AF37]"></div>
                    <span class="text-xs text-amber-300 font-semibold tracking-widest">永結同心</span>
                    <div class="h-[1px] w-12 bg-gradient-to-l from-transparent to-[#D4AF37]"></div>
                </div>

                <p class="text-xs text-amber-200/70 mb-6 font-sans">
                    {{ safeFormatDisplayDate(weddingDate) }}
                </p>

                <!-- Guest Recipient Tag -->
                <div class="bg-black/60 border border-[#D4AF37]/40 rounded-2xl p-4 mb-6 text-center shadow-inner">
                    <div class="text-[10px] uppercase tracking-wider text-amber-300/80 mb-0.5">Kepada Yth. Bapak/Ibu/Saudara/i:</div>
                    <div class="text-base font-semibold text-amber-100 truncate">
                        {{ props.guestName || 'Tamu Undangan Terhormat' }}
                    </div>
                    <div class="text-[10px] text-amber-200/40 italic mt-0.5">*Mohon maaf apabila ada kesalahan penulisan nama/gelar</div>
                </div>

                <!-- Open Gate Button -->
                <button
                    @click="handleOpenInvitation"
                    class="group relative w-full inline-flex items-center justify-center px-6 py-3.5 rounded-full text-stone-950 bg-gradient-to-r from-[#F59E0B] via-[#FBBF24] to-[#F59E0B] shadow-xl shadow-amber-500/25 hover:shadow-amber-500/45 hover:scale-[1.03] active:scale-95 transition-all duration-300 font-bold tracking-widest text-xs uppercase overflow-hidden"
                >
                    <span class="absolute inset-0 bg-white/30 transform -translate-x-full group-hover:translate-x-full transition-transform duration-700"></span>
                    <span class="mr-2">🏮</span>
                    <span>Buka Gerbang Lentera</span>
                </button>
            </div>
        </div>

        <!-- ========================================================
             2. MAIN INVITATION EXPERIENCE (TWO-PANE DESKTOP / MOBILE)
             ======================================================== -->
        <div class="min-h-screen flex flex-col lg:flex-row">

            <!-- LEFT FIXED STAGE: Sky Lanterns Night & Couple (Desktop) -->
            <div
                class="hidden lg:flex lg:w-[45%] xl:w-[42%] fixed inset-y-0 left-0 z-20 flex-col items-center justify-between p-10 select-none overflow-hidden"
                @wheel="handleStageWheel"
            >
                <!-- Real Photographic Backdrop with Night Vignette -->
                <div class="absolute inset-0 pointer-events-none">
                    <img
                        src="/images/templates/lantern/sky-lanterns-night.jpg"
                        alt="Sky Lanterns Festival"
                        class="w-full h-full object-cover filter brightness-45 contrast-110 scale-105"
                    />
                    <div class="absolute inset-0 bg-gradient-to-t from-[#090A10] via-black/40 to-[#140C0E]/80"></div>
                </div>

                <!-- Top Header Badge -->
                <div class="relative z-10 text-center">
                    <div class="inline-block px-4 py-1.5 rounded-full border border-amber-400/40 bg-black/60 backdrop-blur-md text-[11px] uppercase tracking-[0.3em] text-amber-200 shadow-lg">
                        Pingxi Sky Lantern Edition
                    </div>
                    <div class="mt-2 text-xs text-amber-300/80 tracking-widest font-light">
                        星光萬里 • 緣訂三生
                    </div>
                </div>

                <!-- Center 3D Interactive Portrait Frame -->
                <div
                    class="relative z-10 w-full max-w-sm flex flex-col items-center transition-transform duration-300 ease-out"
                    :style="{
                        transform: `perspective(900px) rotateX(${targetRotateX}deg) rotateY(${targetRotateY}deg)`,
                    }"
                >
                    <!-- Authentic Couple Portrait in Floating Gold Bezel -->
                    <div class="relative w-64 h-84 rounded-3xl p-2.5 bg-gradient-to-tr from-[#D4AF37] via-[#E11D48] to-[#F59E0B] shadow-[0_15px_40px_rgba(0,0,0,0.8)] overflow-hidden border-2 border-amber-200/50 group">
                        <img
                            :src="coverPhoto"
                            alt="Couple Cover"
                            class="w-full h-full object-cover rounded-2xl group-hover:scale-105 transition-transform duration-700"
                        />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent pointer-events-none"></div>

                        <!-- Hanging Golden Crest Badge -->
                        <div class="absolute bottom-3 left-1/2 -translate-x-1/2 px-4 py-1 rounded-full bg-black/75 backdrop-blur-md border border-amber-400/60 text-amber-200 text-xs font-serif font-bold tracking-widest uppercase truncate max-w-[90%]">
                            囍 • 百年好合
                        </div>
                    </div>

                    <h2 class="text-3xl font-serif font-bold text-amber-100 tracking-wide mt-5 drop-shadow-md">
                        {{ groom.nickname }} &amp; {{ bride.nickname }}
                    </h2>
                    <div class="text-xs text-amber-300/90 font-light tracking-widest mt-1">
                        {{ safeFormatDisplayDate(weddingDate) }}
                    </div>
                </div>

                <!-- Bottom Venue Card -->
                <div class="relative z-10 text-center w-full max-w-xs">
                    <div class="p-3.5 rounded-2xl bg-black/60 border border-amber-500/25 backdrop-blur-md shadow-xl">
                        <div class="text-[10px] text-amber-300/80 uppercase tracking-widest font-semibold">Tempat Perayaan</div>
                        <div class="text-xs text-amber-100 font-medium truncate mt-0.5">
                            {{ props.events[0]?.venue_name || 'The Grand Imperial Lantern Ballroom' }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT SCROLLABLE INVITATION CONTENT (Desktop & Mobile) -->
            <div class="w-full lg:w-[55%] xl:w-[58%] lg:ml-auto relative z-20 min-h-screen pb-24">

                <!-- MOBILE HERO COVER (Shown only on mobile) -->
                <div class="lg:hidden relative min-h-[92vh] flex flex-col justify-between items-center text-center p-6 text-amber-50 overflow-hidden">
                    <div class="absolute inset-0 pointer-events-none">
                        <img
                            src="/images/templates/lantern/sky-lanterns-night.jpg"
                            alt="Sky Lanterns Festival"
                            class="w-full h-full object-cover filter brightness-40 contrast-105 scale-105"
                        />
                        <div class="absolute inset-0 bg-gradient-to-b from-black/60 via-black/40 to-[#090A10]"></div>
                    </div>

                    <div class="relative z-10 mt-6">
                        <span class="inline-block px-4 py-1.5 rounded-full border border-amber-400/40 bg-black/60 text-[10px] uppercase tracking-[0.25em] text-amber-200">
                            The Wedding Of
                        </span>
                    </div>

                    <div class="relative z-10 flex flex-col items-center my-6">
                        <div class="relative w-56 h-56 rounded-full p-2 bg-gradient-to-tr from-[#D4AF37] via-[#E11D48] to-[#F59E0B] shadow-2xl">
                            <div class="w-full h-full rounded-full overflow-hidden border-2 border-white/60">
                                <img
                                    :src="coverPhoto"
                                    alt="Couple Cover"
                                    class="w-full h-full object-cover"
                                />
                            </div>
                            <div class="absolute -bottom-2 -right-2 w-11 h-11 bg-[#B91C1C] rounded-full border-2 border-amber-200 flex items-center justify-center text-amber-200 text-sm font-bold shadow-lg animate-bounce">
                                囍
                            </div>
                        </div>

                        <h1 class="text-3xl font-serif font-bold text-amber-100 tracking-wider mt-5">
                            {{ groom.nickname }} &amp; {{ bride.nickname }}
                        </h1>
                        <p class="text-xs text-amber-300 font-light tracking-widest mt-1">
                            {{ safeFormatDisplayDate(weddingDate) }}
                        </p>
                    </div>

                    <div class="relative z-10 mb-4 flex flex-col items-center text-amber-200/60 animate-bounce">
                        <span class="text-[10px] tracking-widest uppercase mb-1">Gulir ke bawah</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                        </svg>
                    </div>
                </div>

                <!-- ====================================================
                     SECTION 1: SACRED QUOTE & CELESTIAL BLESSING
                     ==================================================== -->
                <section class="px-6 py-16 max-w-xl mx-auto text-center lantern-fade-up relative">
                    <div class="inline-flex items-center justify-center space-x-2 text-xs tracking-widest text-amber-400 uppercase mb-4 font-semibold">
                        <span class="w-8 h-[1px] bg-[#D4AF37]"></span>
                        <span>CELESTIAL HARMONY</span>
                        <span class="w-8 h-[1px] bg-[#D4AF37]"></span>
                    </div>

                    <!-- Seal Monogram -->
                    <div class="w-12 h-12 mx-auto mb-4 border-2 border-[#D4AF37] rounded-full flex items-center justify-center text-amber-300 font-bold text-lg bg-red-950/80 shadow-lg shadow-amber-500/20">
                        囍
                    </div>

                    <blockquote class="text-base sm:text-lg italic font-serif text-amber-50/90 leading-relaxed mb-4">
                        "{{ invitationData.content.quote || 'Dan di antara tanda-tanda kebesaran-Nya ialah Dia menciptakan pasangan-pasangan untukmu dari jenismu sendiri, agar kamu cenderung dan merasa tenteram kepadanya, dan dijadikan-Nya di antaramu rasa kasih dan sayang.' }}"
                    </blockquote>
                    <p class="text-xs font-semibold text-amber-300 tracking-wider uppercase">
                        — {{ invitationData.content.quoteSource || 'QS. Ar-Rum: 21' }}
                    </p>
                </section>

                <!-- ====================================================
                     SECTION 2: BRIDE & GROOM PROFILE (MEMPELAI)
                     ==================================================== -->
                <section class="px-6 py-12 max-w-xl mx-auto lantern-fade-up">
                    <div class="text-center mb-10">
                        <span class="text-[10px] tracking-[0.3em] uppercase text-amber-400 font-semibold">Pasangan Mempelai</span>
                        <h2 class="text-2xl sm:text-3xl font-serif font-bold text-amber-100 mt-1">Groom &amp; Bride</h2>
                        <div class="w-12 h-0.5 bg-[#F59E0B] mx-auto mt-2"></div>
                    </div>

                    <div class="space-y-8">
                        <!-- GROOM CARD (3D Tilt) -->
                        <div
                            class="relative p-6 sm:p-8 rounded-3xl bg-[#140D10]/85 border border-[#D4AF37]/50 shadow-2xl hover:shadow-amber-500/10 transition-all duration-300"
                            @mousemove="(e) => handleCardTilt(e, 'groom')"
                            @mouseleave="() => resetCardTilt('groom')"
                            :style="{
                                transform: `perspective(800px) rotateX(${cardGroomTilt.x}deg) rotateY(${cardGroomTilt.y}deg)`,
                            }"
                        >
                            <div class="flex flex-col sm:flex-row items-center sm:items-start space-y-4 sm:space-y-0 sm:space-x-6 text-center sm:text-left">
                                <div class="relative w-28 h-28 sm:w-32 sm:h-32 rounded-full p-1.5 bg-gradient-to-tr from-[#D4AF37] via-[#F59E0B] to-[#B91C1C] shadow-lg flex-shrink-0">
                                    <img
                                        :src="groom.photo"
                                        alt="Groom Photo"
                                        class="w-full h-full object-cover rounded-full"
                                    />
                                    <div class="absolute bottom-0 right-0 bg-[#B91C1C] text-amber-200 text-[10px] px-2 py-0.5 rounded-full border border-amber-300">
                                        新郎
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-xl font-serif font-bold text-amber-100">
                                        {{ groom.fullName }}
                                    </h3>
                                    <p class="text-xs text-amber-300/80 font-medium tracking-wide mt-1">
                                        Putra Pertama dari:
                                    </p>
                                    <p class="text-xs text-stone-300 mt-0.5 leading-relaxed">
                                        {{ groom.parents }}
                                    </p>
                                    <div v-if="groom.instagram" class="mt-3">
                                        <a
                                            :href="'https://instagram.com/' + groom.instagram.replace('@', '')"
                                            target="_blank"
                                            class="inline-flex items-center space-x-1.5 text-xs text-amber-400 hover:text-amber-300 font-medium"
                                        >
                                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24">
                                                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                                            </svg>
                                            <span>{{ groom.instagram }}</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- BRIDE CARD (3D Tilt) -->
                        <div
                            class="relative p-6 sm:p-8 rounded-3xl bg-[#140D10]/85 border border-[#D4AF37]/50 shadow-2xl hover:shadow-amber-500/10 transition-all duration-300"
                            @mousemove="(e) => handleCardTilt(e, 'bride')"
                            @mouseleave="() => resetCardTilt('bride')"
                            :style="{
                                transform: `perspective(800px) rotateX(${cardBrideTilt.x}deg) rotateY(${cardBrideTilt.y}deg)`,
                            }"
                        >
                            <div class="flex flex-col sm:flex-row items-center sm:items-start space-y-4 sm:space-y-0 sm:space-x-6 text-center sm:text-left">
                                <div class="relative w-28 h-28 sm:w-32 sm:h-32 rounded-full p-1.5 bg-gradient-to-tr from-[#D4AF37] via-[#F59E0B] to-[#B91C1C] shadow-lg flex-shrink-0">
                                    <img
                                        :src="bride.photo"
                                        alt="Bride Photo"
                                        class="w-full h-full object-cover rounded-full"
                                    />
                                    <div class="absolute bottom-0 right-0 bg-[#B91C1C] text-amber-200 text-[10px] px-2 py-0.5 rounded-full border border-amber-300">
                                        新婦
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-xl font-serif font-bold text-amber-100">
                                        {{ bride.fullName }}
                                    </h3>
                                    <p class="text-xs text-amber-300/80 font-medium tracking-wide mt-1">
                                        Putri Kedua dari:
                                    </p>
                                    <p class="text-xs text-stone-300 mt-0.5 leading-relaxed">
                                        {{ bride.parents }}
                                    </p>
                                    <div v-if="bride.instagram" class="mt-3">
                                        <a
                                            :href="'https://instagram.com/' + bride.instagram.replace('@', '')"
                                            target="_blank"
                                            class="inline-flex items-center space-x-1.5 text-xs text-amber-400 hover:text-amber-300 font-medium"
                                        >
                                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24">
                                                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                                            </svg>
                                            <span>{{ bride.instagram }}</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- ====================================================
                     SECTION 3: 60FPS INTERACTIVE SKY LANTERN MINI-GAME
                     ==================================================== -->
                <section class="px-6 py-12 max-w-xl mx-auto lantern-fade-up">
                    <div class="text-center mb-6">
                        <span class="text-[10px] tracking-[0.3em] uppercase text-amber-400 font-semibold">Wahana Interaktif 3D</span>
                        <h3 class="text-2xl sm:text-3xl font-serif font-bold text-amber-100 mt-1">Lentera Harapan Cinta</h3>
                        <p class="text-xs text-amber-200/70 mt-1">Pilih doa restu Anda, lalu terbangkan lentera ke langit malam</p>
                    </div>

                    <!-- Interactive Sky Stage Box -->
                    <div
                        ref="gameContainerRef"
                        @click="launchPersonalizedLantern"
                        class="relative h-80 sm:h-96 rounded-3xl border-2 border-[#D4AF37]/60 shadow-[0_0_50px_rgba(245,158,11,0.25)] overflow-hidden cursor-pointer select-none bg-[#07050A]"
                    >
                        <!-- Night Sky Background -->
                        <div
                            class="absolute inset-0 pointer-events-none filter brightness-70"
                            style="background: radial-gradient(circle at 50% 100%, rgba(136, 19, 55, 0.45) 0%, rgba(7, 5, 10, 0.95) 75%), url('/images/templates/lantern/sky-lanterns-night.jpg') center/cover;"
                        ></div>

                        <!-- 60 FPS HTML5 Canvas for Physics Drifting Lanterns -->
                        <canvas
                            ref="interactiveGameCanvasRef"
                            class="absolute inset-0 w-full h-full z-10"
                        ></canvas>

                        <!-- Counter and Cue Overlay -->
                        <div class="absolute top-4 inset-x-4 z-20 flex items-center justify-between pointer-events-none">
                            <div class="px-3.5 py-1 rounded-full bg-black/75 backdrop-blur-md border border-amber-400/50 text-amber-300 text-xs font-semibold shadow-lg">
                                🏮 {{ lanternsLaunchedCount }} Lentera Diterbangkan
                            </div>
                            <div class="text-[11px] text-amber-200/80 bg-red-950/70 px-3 py-1 rounded-full border border-red-500/40 animate-pulse">
                                Ketuk Langit Untuk Menerbangkan ✨
                            </div>
                        </div>

                        <!-- Bottom Launching Dock -->
                        <div class="absolute bottom-4 inset-x-4 z-20" @click.stop>
                            <div class="p-3.5 rounded-2xl bg-black/85 backdrop-blur-md border border-amber-400/40 shadow-2xl flex flex-col sm:flex-row items-center gap-2.5">
                                <div class="flex-1 w-full">
                                    <label class="block text-[10px] uppercase tracking-wider text-amber-300 font-semibold mb-1">
                                        Pilih Doa Harapan Anda:
                                    </label>
                                    <select
                                        v-model="selectedWish"
                                        class="w-full px-3 py-1.5 text-xs rounded-xl bg-[#1C0E12] border border-amber-500/50 text-amber-100 focus:outline-none focus:ring-1 focus:ring-amber-400 font-sans"
                                    >
                                        <option v-for="(opt, idx) in wishOptions" :key="idx" :value="opt">
                                            {{ opt }}
                                        </option>
                                    </select>
                                </div>
                                <button
                                    @click="launchPersonalizedLantern"
                                    class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 via-amber-400 to-amber-500 text-stone-950 text-xs font-bold uppercase tracking-wider shadow-lg hover:brightness-110 active:scale-95 transition-all flex items-center justify-center gap-1.5 flex-shrink-0 cursor-pointer"
                                >
                                    <span>🔥</span>
                                    <span>Nyalakan &amp; Terbangkan</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- ====================================================
                     SECTION 4: COUNTDOWN TIMER (WAKTU BAHAGIA)
                     ==================================================== -->
                <section class="px-6 py-12 max-w-xl mx-auto lantern-fade-up">
                    <div class="p-6 sm:p-8 rounded-3xl bg-[#140D10]/85 border border-[#D4AF37]/50 shadow-2xl text-center relative overflow-hidden">
                        <div class="text-[10px] tracking-[0.3em] uppercase text-amber-400 font-semibold mb-1">Menuju Hari Bahagia</div>
                        <h3 class="text-xl font-serif font-bold text-amber-100 mb-6">Hitung Mundur Pernikahan</h3>

                        <div class="grid grid-cols-4 gap-3 sm:gap-4 max-w-md mx-auto mb-6">
                            <div class="p-3 sm:p-4 rounded-2xl bg-black/60 border border-amber-500/30">
                                <div class="text-2xl sm:text-3xl font-bold font-mono text-amber-300">{{ props.timeLeft.days }}</div>
                                <div class="text-[10px] uppercase text-stone-400 mt-1">Hari</div>
                            </div>
                            <div class="p-3 sm:p-4 rounded-2xl bg-black/60 border border-amber-500/30">
                                <div class="text-2xl sm:text-3xl font-bold font-mono text-amber-300">{{ props.timeLeft.hours }}</div>
                                <div class="text-[10px] uppercase text-stone-400 mt-1">Jam</div>
                            </div>
                            <div class="p-3 sm:p-4 rounded-2xl bg-black/60 border border-amber-500/30">
                                <div class="text-2xl sm:text-3xl font-bold font-mono text-amber-300">{{ props.timeLeft.minutes }}</div>
                                <div class="text-[10px] uppercase text-stone-400 mt-1">Menit</div>
                            </div>
                            <div class="p-3 sm:p-4 rounded-2xl bg-black/60 border border-amber-500/30">
                                <div class="text-2xl sm:text-3xl font-bold font-mono text-amber-300">{{ props.timeLeft.seconds }}</div>
                                <div class="text-[10px] uppercase text-stone-400 mt-1">Detik</div>
                            </div>
                        </div>

                        <a
                            :href="googleCalendarUrl"
                            target="_blank"
                            class="inline-flex items-center space-x-2 px-5 py-2.5 rounded-full bg-amber-500/20 text-amber-200 border border-amber-400/50 hover:bg-amber-500/30 text-xs font-semibold transition-all shadow-md"
                        >
                            <span>📅 Simpan ke Google Calendar</span>
                        </a>
                    </div>
                </section>

                <!-- ====================================================
                     SECTION 5: AGENDA ACARA (AKAD & RESEPSI)
                     ==================================================== -->
                <section class="px-6 py-12 max-w-xl mx-auto lantern-fade-up">
                    <div class="text-center mb-10">
                        <span class="text-[10px] tracking-[0.3em] uppercase text-amber-400 font-semibold">Rangkaian Acara</span>
                        <h2 class="text-2xl sm:text-3xl font-serif font-bold text-amber-100 mt-1">Wedding Agenda</h2>
                        <div class="w-12 h-0.5 bg-[#F59E0B] mx-auto mt-2"></div>
                    </div>

                    <div class="space-y-6">
                        <div
                            v-for="(event, eIdx) in props.events"
                            :key="event.id || eIdx"
                            class="p-6 sm:p-8 rounded-3xl bg-[#140D10]/85 border border-[#D4AF37]/50 shadow-2xl relative overflow-hidden"
                        >
                            <div class="flex items-center justify-between mb-4">
                                <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                    {{ eIdx === 0 ? 'Pemberkatan / Akad' : 'Resepsi Pernikahan' }}
                                </span>
                                <span class="text-lg">🏮</span>
                            </div>

                            <h3 class="text-xl font-serif font-bold text-amber-100 mb-2">
                                {{ event.name }}
                            </h3>

                            <div class="space-y-2 text-xs text-stone-300 mb-6 font-sans">
                                <div class="flex items-center space-x-2">
                                    <span class="text-amber-400">📅</span>
                                    <span>{{ safeFormatDisplayDate(event.starts_at) }}</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span class="text-amber-400">⏰</span>
                                    <span>{{ safeFormatEventTime(event.starts_at, event.ends_at, event.timezone) }}</span>
                                </div>
                                <div class="flex items-start space-x-2">
                                    <span class="text-amber-400 mt-0.5">📍</span>
                                    <div>
                                        <div class="font-semibold text-amber-200">{{ event.venue_name }}</div>
                                        <div class="text-stone-400 text-[11px] mt-0.5 leading-relaxed">{{ event.address }}</div>
                                    </div>
                                </div>
                            </div>

                            <a
                                v-if="event.maps_url"
                                :href="event.maps_url"
                                target="_blank"
                                class="w-full inline-flex items-center justify-center space-x-1.5 py-3 rounded-xl bg-gradient-to-r from-[#B91C1C] to-[#881337] border border-amber-400/40 text-amber-100 hover:brightness-110 text-xs font-semibold transition-all shadow-md"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                </svg>
                                <span>Petunjuk Lokasi Google Maps</span>
                            </a>
                        </div>
                    </div>
                </section>

                <!-- ====================================================
                     SECTION 6: PHOTO GALLERY (KENANGAN INDAH)
                     ==================================================== -->
                <section
                    v-if="invitationData.content.gallery && invitationData.content.gallery.length > 0"
                    class="px-6 py-12 max-w-xl mx-auto lantern-fade-up"
                >
                    <div class="text-center mb-10">
                        <span class="text-[10px] tracking-[0.3em] uppercase text-amber-400 font-semibold">Galeri Kenangan</span>
                        <h2 class="text-2xl sm:text-3xl font-serif font-bold text-amber-100 mt-1">Our Moments</h2>
                        <div class="w-12 h-0.5 bg-[#F59E0B] mx-auto mt-2"></div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        <div
                            v-for="(photo, pIdx) in invitationData.content.gallery"
                            :key="pIdx"
                            @click="openLightbox(photo)"
                            class="group relative aspect-square rounded-2xl overflow-hidden cursor-pointer shadow-lg border border-amber-500/30 hover:border-amber-400 transition-all hover:scale-[1.03]"
                        >
                            <img
                                :src="photo"
                                :alt="'Gallery photo ' + (pIdx + 1)"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
                            />
                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                <svg class="w-6 h-6 text-amber-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- ====================================================
                     SECTION 7: WEDDING GIFT (ANGPAO & AMPLOP DIGITAL)
                     ==================================================== -->
                <section class="px-6 py-12 max-w-xl mx-auto lantern-fade-up">
                    <div class="text-center mb-10">
                        <span class="text-[10px] tracking-[0.3em] uppercase text-amber-400 font-semibold">Tanda Kasih &amp; Angpao</span>
                        <h2 class="text-2xl sm:text-3xl font-serif font-bold text-amber-100 mt-1">Wedding Gift</h2>
                        <div class="w-12 h-0.5 bg-[#F59E0B] mx-auto mt-2"></div>
                        <p class="text-xs text-stone-300 mt-2 max-w-md mx-auto">
                            Doa restu Anda merupakan karunia terindah bagi kami. Namun jika ingin memberikan tanda kasih secara cashless, Anda dapat menyalurkannya melalui:
                        </p>
                    </div>

                    <!-- Red Packet Angpao Card Design -->
                    <div class="p-6 sm:p-8 rounded-3xl bg-gradient-to-b from-[#2A0E14] to-[#140D10] border-2 border-[#D4AF37] shadow-2xl relative overflow-hidden">
                        <div class="space-y-4">
                            <div
                                v-for="(bank, bIdx) in digitalGift.accounts"
                                :key="bIdx"
                                class="p-5 rounded-2xl bg-black/60 border border-amber-500/30 shadow-md flex flex-col sm:flex-row sm:items-center justify-between gap-3"
                            >
                                <div>
                                    <div class="text-xs font-bold text-amber-300 uppercase tracking-wider">
                                        {{ bank.bank || bank.bank_name || bank.name || 'Bank Transfer' }}
                                    </div>
                                    <div class="text-lg font-mono font-bold text-amber-100 tracking-wider mt-0.5">
                                        {{ bank.account_number || bank.number }}
                                    </div>
                                    <div class="text-xs text-stone-400">
                                        a.n. {{ bank.account_name || bank.account_holder || bank.holder }}
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <button
                                        @click="copyBankNumber(bank.account_number || bank.number, bIdx)"
                                        class="px-4 py-2 rounded-xl text-xs font-semibold bg-amber-500 text-stone-950 hover:bg-amber-400 transition-colors flex items-center space-x-1 cursor-pointer font-sans"
                                    >
                                        <span>{{ copiedBankIndex === bIdx ? '✓ Tersalin' : 'Salin Rekening' }}</span>
                                    </button>
                                    <button
                                        v-if="bank.qr_image || bank.qris_image_url || digitalGift.qris_url"
                                        @click="props.openQris(bank)"
                                        class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-red-950/80 text-amber-200 border border-amber-400/50 hover:bg-red-900 transition-colors cursor-pointer"
                                    >
                                        QRIS
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- ====================================================
                     SECTION 8: RSVP & WISHES (BUKU TAMU)
                     ==================================================== -->
                <section class="px-6 py-12 max-w-xl mx-auto lantern-fade-up">
                    <div class="text-center mb-10">
                        <span class="text-[10px] tracking-[0.3em] uppercase text-amber-400 font-semibold">Konfirmasi Kehadiran</span>
                        <h2 class="text-2xl sm:text-3xl font-serif font-bold text-amber-100 mt-1">RSVP &amp; Ucapan</h2>
                        <div class="w-12 h-0.5 bg-[#F59E0B] mx-auto mt-2"></div>
                    </div>

                    <!-- RSVP FORM -->
                    <div class="p-6 sm:p-8 rounded-3xl bg-[#140D10]/85 border border-[#D4AF37]/50 shadow-2xl mb-8">
                        <form @submit.prevent="props.submitRsvp" class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-amber-300 uppercase tracking-wider mb-1">
                                    Nama Lengkap
                                </label>
                                <input
                                    v-model="props.rsvpForm.guest_name"
                                    type="text"
                                    required
                                    placeholder="Masukkan nama Anda"
                                    class="w-full px-4 py-2.5 text-sm rounded-xl bg-black/60 border border-amber-500/40 text-amber-100 focus:ring-2 focus:ring-amber-400 outline-none transition-all font-sans"
                                />
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-amber-300 uppercase tracking-wider mb-1">
                                    Konfirmasi Kehadiran
                                </label>
                                <div class="grid grid-cols-2 gap-3 font-sans">
                                    <button
                                        type="button"
                                        @click="props.rsvpForm.attendance_status = 'attending'"
                                        class="py-2.5 px-4 rounded-xl text-xs font-semibold border transition-all text-center cursor-pointer"
                                        :class="props.rsvpForm.attendance_status === 'attending' ? 'bg-amber-500 text-stone-950 border-amber-400 font-bold shadow-md' : 'bg-black/40 text-stone-300 border-stone-700'"
                                    >
                                        ✓ Hadir
                                    </button>
                                    <button
                                        type="button"
                                        @click="props.rsvpForm.attendance_status = 'declined'"
                                        class="py-2.5 px-4 rounded-xl text-xs font-semibold border transition-all text-center cursor-pointer"
                                        :class="props.rsvpForm.attendance_status === 'declined' ? 'bg-stone-700 text-white border-stone-500 shadow-md' : 'bg-black/40 text-stone-300 border-stone-700'"
                                    >
                                        ✕ Berhalangan
                                    </button>
                                </div>
                            </div>

                            <div v-if="props.rsvpForm.attendance_status === 'attending'">
                                <label class="block text-xs font-semibold text-amber-300 uppercase tracking-wider mb-1">
                                    Jumlah Tamu
                                </label>
                                <select
                                    v-model.number="props.rsvpForm.attendee_count"
                                    class="w-full px-4 py-2.5 text-sm rounded-xl bg-black/60 border border-amber-500/40 text-amber-100 focus:ring-2 focus:ring-amber-400 outline-none transition-all font-sans"
                                >
                                    <option :value="1">1 Orang</option>
                                    <option :value="2">2 Orang</option>
                                    <option :value="3">3 Orang</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-amber-300 uppercase tracking-wider mb-1">
                                    Doa &amp; Ucapan Selamat
                                </label>
                                <textarea
                                    v-model="props.rsvpForm.message"
                                    rows="3"
                                    placeholder="Tuliskan doa restu untuk kedua mempelai..."
                                    class="w-full px-4 py-2.5 text-sm rounded-xl bg-black/60 border border-amber-500/40 text-amber-100 focus:ring-2 focus:ring-amber-400 outline-none transition-all font-sans"
                                ></textarea>
                            </div>

                            <div v-if="props.rsvpErrorMessage" class="text-xs text-rose-400 font-sans">
                                {{ props.rsvpErrorMessage }}
                            </div>

                            <div v-if="props.rsvpSuccess" class="text-xs text-emerald-400 font-semibold font-sans">
                                ✓ Terima kasih! Konfirmasi kehadiran Anda telah tersimpan.
                            </div>

                            <button
                                type="submit"
                                :disabled="props.rsvpSubmitting"
                                class="w-full py-3.5 rounded-xl bg-gradient-to-r from-amber-500 via-amber-400 to-amber-500 text-stone-950 text-xs font-bold tracking-widest uppercase hover:brightness-110 active:scale-98 transition-all disabled:opacity-50 cursor-pointer shadow-xl font-sans"
                            >
                                <span v-if="props.rsvpSubmitting">Mengirimkan...</span>
                                <span v-else>Kirim Konfirmasi &amp; Doa</span>
                            </button>
                        </form>
                    </div>

                    <!-- GUESTBOOK WISH FEED -->
                    <div class="space-y-3">
                        <div class="text-xs font-semibold text-amber-300 uppercase tracking-widest text-center mb-3">
                            Doa &amp; Restu Sahabat ({{ props.guestbook.length }})
                        </div>

                        <div class="max-h-96 overflow-y-auto space-y-3 pr-1">
                            <div
                                v-for="(entry, gIdx) in props.guestbook"
                                :key="gIdx"
                                class="p-4 rounded-2xl bg-black/60 border border-amber-500/30 shadow-md relative pl-4 border-l-4 border-l-[#F59E0B]"
                            >
                                <div class="flex items-center justify-between mb-1">
                                    <div class="text-xs font-bold text-amber-200">
                                        {{ entry.guest_name }}
                                    </div>
                                    <div class="text-[10px] text-stone-400 font-sans">
                                        {{ entry.created_at }}
                                    </div>
                                </div>
                                <p class="text-xs text-stone-300 font-sans leading-relaxed">
                                    {{ entry.message }}
                                </p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- FOOTER -->
                <footer class="px-6 pt-8 pb-16 text-center lantern-fade-up">
                    <div class="w-10 h-10 mx-auto mb-4 border border-[#D4AF37] rounded-full flex items-center justify-center text-amber-300 font-serif font-bold bg-black/50">
                        囍
                    </div>
                    <p class="text-xs text-stone-300 max-w-sm mx-auto leading-relaxed mb-6 font-sans">
                        Merupakan kehormatan &amp; kebahagiaan yang tak terhingga bagi kami atas kehadiran dan doa restu Bapak/Ibu/Saudara/i sekalian.
                    </p>

                    <div class="text-sm font-serif font-bold text-amber-300 tracking-wider">
                        Kami yang berbahagia,
                    </div>
                    <div class="text-lg font-serif font-bold text-amber-100 mt-1">
                        {{ groom.nickname }} &amp; {{ bride.nickname }}
                    </div>

                    <div class="mt-8 text-[10px] text-amber-200/40 tracking-widest uppercase">
                        Undangan.id • Pingxi Sky Lantern Edition
                    </div>
                </footer>
            </div>
        </div>

        <!-- LIGHTBOX MODAL -->
        <div
            v-if="activeLightboxImg"
            @click="closeLightbox"
            class="fixed inset-0 z-[100] bg-black/90 backdrop-blur-md flex items-center justify-center p-4 cursor-pointer"
        >
            <div class="relative max-w-3xl max-h-[85vh] p-2 bg-[#140D10] border-2 border-[#D4AF37] rounded-2xl overflow-hidden shadow-2xl">
                <img
                    :src="activeLightboxImg"
                    alt="Lightbox Photo"
                    class="w-full h-full object-contain max-h-[80vh] rounded-xl"
                />
                <button
                    @click="closeLightbox"
                    class="absolute top-4 right-4 w-9 h-9 rounded-full bg-black/80 text-white flex items-center justify-center hover:bg-red-600 transition-colors"
                >
                    ✕
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes lanternSway {
    0%, 100% {
        transform: rotate(-3deg);
    }
    50% {
        transform: rotate(3deg);
    }
}

.animate-lantern-sway {
    animation: lanternSway 4.5s ease-in-out infinite;
}
</style>

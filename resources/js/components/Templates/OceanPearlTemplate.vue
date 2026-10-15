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
    return d || 'Minggu, 20 September 2026';
};

// Normalized couple and design accessors
const groom = computed(() => {
    const c = props.invitationData?.content || {};
    return {
        fullName: c.groom?.fullName || c.groomName || 'Adrian Pradipta, S.Kom',
        nickname: c.groom?.nickname || c.groomNickname || 'Adrian',
        parents: c.groom?.parents || c.groomParents || 'Putra pertama dari Bpk. Ir. Surya Pradipta & Ibu Maya Pradipta',
        photo: c.groom?.photo || c.groomPhotoUrl || '/images/templates/ocean/groom-real.jpg',
        instagram: c.groom?.instagram || c.groomInstagram || '',
    };
});

const bride = computed(() => {
    const c = props.invitationData?.content || {};
    return {
        fullName: c.bride?.fullName || c.brideName || 'Samudra Valerie, B.A',
        nickname: c.bride?.nickname || c.brideNickname || 'Valerie',
        parents: c.bride?.parents || c.brideParents || 'Putri bungsu dari Bpk. Daniel Wijaya & Ibu Ratna Wijaya',
        photo: c.bride?.photo || c.bridePhotoUrl || '/images/templates/ocean/bride-real.jpg',
        instagram: c.bride?.instagram || c.brideInstagram || '',
    };
});

const weddingTitle = computed(() => {
    const c = props.invitationData?.content || {};
    if (groom.value.nickname && bride.value.nickname) {
        return `${groom.value.nickname} & ${bride.value.nickname}`;
    }
    return c.weddingTitle || 'Adrian & Valerie';
});

const weddingDate = computed(() => {
    const c = props.invitationData?.content || {};
    return c.weddingDateText || c.weddingDate || 'Minggu, 20 September 2026';
});

const coverPhoto = computed(() => {
    const c = props.invitationData?.content || {};
    return c.coverImage || c.coverPhotoUrl || '/images/templates/ocean/couple-ocean.jpg';
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
    const title = encodeURIComponent((weddingTitle.value || 'Ocean Horizon Wedding') + ' Celebration');
    const firstEvent = props.events?.[0];
    const location = encodeURIComponent((firstEvent?.venue_name || '') + ' - ' + (firstEvent?.address || ''));
    return `https://www.google.com/calendar/render?action=TEMPLATE&text=${title}&location=${location}&dates=20260920T010000Z/20260920T140000Z`;
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
// 1. 3D COASTAL VILLA GLASS SCREEN TRANSITION
// ========================================================
const isCoverVisible = ref(!props.isOpen);
const isTerraceOpening = ref(false);

const handleOpenInvitation = () => {
    if (isTerraceOpening.value) return;
    isTerraceOpening.value = true;

    emit('openInvitation');

    // Confetti with marine pearl white, champagne gold & ocean turquoise
    confetti({
        particleCount: 75,
        spread: 90,
        origin: { y: 0.6 },
        colors: ['#0284C7', '#38BDF8', '#F59E0B', '#F8FAFC', '#E0F2FE']
    });

    setTimeout(() => {
        isCoverVisible.value = false;
    }, 1300);
};

// ========================================================
// 2. 60 FPS INTERACTIVE SACRED OYSTER & OCEAN CAUSTICS (MINI-GAME)
// ========================================================
const causticsCanvasRef = ref<HTMLCanvasElement | null>(null);
const pearlGameCanvasRef = ref<HTMLCanvasElement | null>(null);
const pearlGameContainerRef = ref<HTMLElement | null>(null);
const pearlsCollectedCount = ref(84);
let bgCausticsAnimId: number | null = null;
let pearlGameAnimId: number | null = null;

// Ambient Ocean Caustics and Bioluminescent Particles
const initAmbientOceanCanvas = () => {
    const canvas = causticsCanvasRef.value;
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

    interface SeaBubble {
        x: number;
        y: number;
        r: number;
        speedY: number;
        speedX: number;
        opacity: number;
        wobble: number;
    }

    const bubbles: SeaBubble[] = [];
    const bubbleCount = window.innerWidth < 768 ? 18 : 32;

    for (let i = 0; i < bubbleCount; i++) {
        bubbles.push({
            x: Math.random() * width,
            y: Math.random() * height,
            r: 2 + Math.random() * 4.5,
            speedY: -(0.6 + Math.random() * 0.9),
            speedX: (Math.random() - 0.5) * 0.4,
            opacity: 0.25 + Math.random() * 0.45,
            wobble: Math.random() * Math.PI * 2,
        });
    }

    let causticsTime = 0;

    const render = () => {
        ctx.clearRect(0, 0, width, height);
        causticsTime += 0.02;

        // Draw Ambient Bubbles
        for (const b of bubbles) {
            b.y += b.speedY;
            b.wobble += 0.04;
            b.x += b.speedX + Math.sin(b.wobble) * 0.5;

            if (b.y < -20) {
                b.y = height + 20;
                b.x = Math.random() * width;
            }

            ctx.save();
            ctx.beginPath();
            ctx.arc(b.x, b.y, b.r, 0, Math.PI * 2);
            ctx.fillStyle = `rgba(186, 230, 253, ${b.opacity * 0.6})`;
            ctx.fill();

            // Bubble highlight
            ctx.beginPath();
            ctx.arc(b.x - b.r * 0.3, b.y - b.r * 0.3, b.r * 0.3, 0, Math.PI * 2);
            ctx.fillStyle = `rgba(255, 255, 255, ${b.opacity * 0.8})`;
            ctx.fill();
            ctx.restore();
        }

        bgCausticsAnimId = requestAnimationFrame(render);
    };

    render();

    return () => {
        window.removeEventListener('resize', onResize);
        if (bgCausticsAnimId) cancelAnimationFrame(bgCausticsAnimId);
    };
};

// Interactive 60fps Pearl Oyster Game Engine
interface FloatingPearl {
    x: number;
    y: number;
    r: number;
    speedY: number;
    speedX: number;
    opacity: number;
    glow: number;
    blessingText: string;
}

interface WaterRing {
    x: number;
    y: number;
    r: number;
    maxR: number;
    alpha: number;
}

const floatingPearls: FloatingPearl[] = [];
const waterRings: WaterRing[] = [];
const isOysterOpen = ref(false);
const latestBlessing = ref('Semoga cinta abadi seluas samudra.');

const oceanBlessings = [
    'Semoga cinta abadi sedalam samudra luas.',
    'Diberikan ketenangan dalam setiap riak kehidupan.',
    'Bahtera rumah tangga kokoh mengarungi setiap ombak.',
    'Penuh limpahan rezeki berkilau bagai mutiara laut.',
    'Saling mengasihi hingga ke ujung cakrawala senja.',
];

const initInteractivePearlGame = () => {
    const canvas = pearlGameCanvasRef.value;
    const container = pearlGameContainerRef.value;
    if (!canvas || !container) return;

    const ctx = canvas.getContext('2d');
    if (!ctx) return;

    let width = (canvas.width = container.clientWidth);
    let height = (canvas.height = container.clientHeight);

    const onResize = () => {
        if (!canvas || !container) return;
        width = canvas.width = container.clientWidth;
        height = canvas.height = container.clientHeight;
    };
    window.addEventListener('resize', onResize);

    const oysterCenter = {
        get x() { return width / 2; },
        get y() { return height * 0.72; }
    };

    let oysterBreath = 0;

    const render = () => {
        ctx.clearRect(0, 0, width, height);
        oysterBreath += 0.03;

        // 1. Draw Water Ripple Rings
        for (let i = waterRings.length - 1; i >= 0; i--) {
            const ring = waterRings[i];
            ring.r += 1.4;
            ring.alpha -= 0.015;

            ctx.save();
            ctx.beginPath();
            ctx.ellipse(ring.x, ring.y, ring.r, ring.r * 0.45, 0, 0, Math.PI * 2);
            ctx.strokeStyle = `rgba(56, 189, 248, ${ring.alpha})`;
            ctx.lineWidth = 1.8;
            ctx.stroke();
            ctx.restore();

            if (ring.alpha <= 0) {
                waterRings.splice(i, 1);
            }
        }

        // 2. Draw 3D Pearl Oyster Shell (Mother of Pearl)
        const ox = oysterCenter.x;
        const oy = oysterCenter.y;
        const oW = Math.min(width * 0.35, 120);
        const oH = oW * 0.65;

        ctx.save();
        ctx.translate(ox, oy);

        // Lower Shell Half
        const lowerGrad = ctx.createRadialGradient(0, 0, 10, 0, 0, oW);
        lowerGrad.addColorStop(0, '#E0F2FE');
        lowerGrad.addColorStop(0.4, '#BAE6FD');
        lowerGrad.addColorStop(0.8, '#0369A1');
        lowerGrad.addColorStop(1, '#0C4A6E');

        ctx.beginPath();
        ctx.ellipse(0, 5, oW, oH, 0, 0, Math.PI);
        ctx.fillStyle = lowerGrad;
        ctx.fill();
        ctx.strokeStyle = '#38BDF8';
        ctx.lineWidth = 2;
        ctx.stroke();

        // Iridescent Mother-of-Pearl Cushion Bed
        const bedGrad = ctx.createRadialGradient(0, 0, 5, 0, 0, oW * 0.8);
        bedGrad.addColorStop(0, '#FFFFFF');
        bedGrad.addColorStop(0.3, '#FDF2F8');
        bedGrad.addColorStop(0.7, '#E0F2FE');
        bedGrad.addColorStop(1, '#7DD3FC');
        ctx.beginPath();
        ctx.ellipse(0, 0, oW * 0.85, oH * 0.5, 0, 0, Math.PI * 2);
        ctx.fillStyle = bedGrad;
        ctx.fill();

        // Shimmering Golden Core Pearl in Nest
        const corePearlR = oW * 0.22;
        const pearlGrad = ctx.createRadialGradient(-corePearlR * 0.3, -corePearlR * 0.3, 1, 0, 0, corePearlR);
        pearlGrad.addColorStop(0, '#FFFFFF');
        pearlGrad.addColorStop(0.25, '#FEF08A');
        pearlGrad.addColorStop(0.65, '#F59E0B');
        pearlGrad.addColorStop(1, '#D97706');

        ctx.beginPath();
        ctx.arc(0, -2, corePearlR, 0, Math.PI * 2);
        ctx.fillStyle = pearlGrad;
        ctx.shadowColor = 'rgba(251, 191, 36, 0.85)';
        ctx.shadowBlur = 18;
        ctx.fill();
        ctx.shadowBlur = 0;

        // Upper Shell Flap (Opens dynamically on click)
        const openProgress = isOysterOpen.value ? 1 : 0.2 + Math.sin(oysterBreath) * 0.08;
        const upperH = oH * (1 - openProgress * 0.9);
        const upperOffsetY = -openProgress * (oH * 0.9);

        const upperGrad = ctx.createRadialGradient(0, upperOffsetY, 10, 0, upperOffsetY, oW);
        upperGrad.addColorStop(0, '#BAE6FD');
        upperGrad.addColorStop(0.4, '#38BDF8');
        upperGrad.addColorStop(0.8, '#0284C7');
        upperGrad.addColorStop(1, '#082F49');

        ctx.beginPath();
        ctx.ellipse(0, upperOffsetY, oW * 0.95, Math.max(8, upperH), 0, Math.PI, 0);
        ctx.fillStyle = upperGrad;
        ctx.fill();
        ctx.strokeStyle = '#BAE6FD';
        ctx.lineWidth = 2;
        ctx.stroke();

        ctx.restore();

        // 3. Draw Rising Floating Pearls
        for (let i = floatingPearls.length - 1; i >= 0; i--) {
            const p = floatingPearls[i];
            p.y += p.speedY;
            p.x += p.speedX;
            p.glow += 0.08;

            ctx.save();
            ctx.translate(p.x, p.y);

            // Radiant Halo
            const haloGrad = ctx.createRadialGradient(0, 0, 2, 0, 0, p.r * 2.5);
            haloGrad.addColorStop(0, `rgba(254, 240, 138, ${p.opacity * 0.8})`);
            haloGrad.addColorStop(0.5, `rgba(56, 189, 248, ${p.opacity * 0.3})`);
            haloGrad.addColorStop(1, 'rgba(2, 132, 199, 0)');
            ctx.fillStyle = haloGrad;
            ctx.beginPath();
            ctx.arc(0, 0, p.r * 2.5, 0, Math.PI * 2);
            ctx.fill();

            // Pearl Sphere
            const pGrad = ctx.createRadialGradient(-p.r * 0.3, -p.r * 0.3, 1, 0, 0, p.r);
            pGrad.addColorStop(0, '#FFFFFF');
            pGrad.addColorStop(0.3, '#FEF08A');
            pGrad.addColorStop(0.75, '#F59E0B');
            pGrad.addColorStop(1, '#B45309');
            ctx.fillStyle = pGrad;
            ctx.beginPath();
            ctx.arc(0, 0, p.r, 0, Math.PI * 2);
            ctx.fill();

            // Text Tag for Pearl
            ctx.fillStyle = `rgba(240, 249, 255, ${p.opacity})`;
            ctx.font = 'bold 10px sans-serif';
            ctx.textAlign = 'center';
            ctx.fillText(p.blessingText, 0, -p.r - 8);

            ctx.restore();

            // Dissolve as it reaches top water surface
            if (p.y < 40) {
                p.opacity -= 0.025;
            }

            if (p.opacity <= 0) {
                floatingPearls.splice(i, 1);
            }
        }

        pearlGameAnimId = requestAnimationFrame(render);
    };

    render();

    return () => {
        window.removeEventListener('resize', onResize);
        if (pearlGameAnimId) cancelAnimationFrame(pearlGameAnimId);
    };
};

// Interactive Tap on Oyster Game
const handleTapOysterPond = (e: MouseEvent | TouchEvent) => {
    const container = pearlGameContainerRef.value;
    if (!container) return;

    const rect = container.getBoundingClientRect();
    let clickX = container.clientWidth / 2;
    let clickY = container.clientHeight * 0.72;

    if ('clientX' in e) {
        clickX = e.clientX - rect.left;
        clickY = e.clientY - rect.top;
    }

    // Ripple
    waterRings.push({
        x: clickX,
        y: clickY,
        r: 10,
        maxR: 90,
        alpha: 0.9,
    });

    // Open oyster shell
    isOysterOpen.value = true;
    setTimeout(() => {
        isOysterOpen.value = false;
    }, 1200);

    // Pick random blessing
    const pickedBlessing = oceanBlessings[Math.floor(Math.random() * oceanBlessings.length)];
    latestBlessing.value = pickedBlessing;

    // Launch luminous floating pearl
    floatingPearls.push({
        x: clickX + (Math.random() - 0.5) * 20,
        y: clickY - 15,
        r: 14 + Math.random() * 4,
        speedY: -(1.5 + Math.random() * 0.8),
        speedX: (Math.random() - 0.5) * 0.6,
        opacity: 1,
        glow: 0,
        blessingText: pickedBlessing,
    });

    pearlsCollectedCount.value++;

    // Sparkling coastal confetti
    confetti({
        particleCount: 20,
        spread: 50,
        origin: { y: 0.75 },
        colors: ['#38BDF8', '#F59E0B', '#F0F9FF', '#0284C7'],
    });
};

let cleanupCaustics: (() => void) | undefined;
let cleanupPearlGame: (() => void) | undefined;

onMounted(() => {
    window.addEventListener('mousemove', handleMouseMove, { passive: true });
    window.addEventListener('touchmove', handleTouchMove, { passive: true });

    nextTick(() => {
        cleanupCaustics = initAmbientOceanCanvas();
        cleanupPearlGame = initInteractivePearlGame();

        const sections = document.querySelectorAll('.ocean-fade-up');
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
    if (cleanupCaustics) cleanupCaustics();
    if (cleanupPearlGame) cleanupPearlGame();
    ScrollTrigger.getAll().forEach((t) => t.kill());
});
</script>

<template>
    <div
        class="min-h-screen w-full relative bg-[#040C18] text-[#F0F9FF] font-serif selection:bg-sky-400 selection:text-slate-950 overflow-x-hidden"
        :style="{
            backgroundColor: invitationData.theme.bgColor || '#040C18',
            color: invitationData.theme.textColor || '#F0F9FF',
        }"
    >
        <!-- Persistent 60fps Ocean Bubbles & Caustic Reflections Canvas -->
        <canvas
            ref="causticsCanvasRef"
            class="fixed inset-0 pointer-events-none z-20 w-full h-full"
        ></canvas>

        <!-- Coastal Water Texture Overlay -->
        <div
            class="fixed inset-0 pointer-events-none z-10 opacity-20 mix-blend-color-dodge bg-cover bg-center"
            style="background-image: url('/images/templates/ocean/clear-water.jpg');"
        ></div>

        <!-- ========================================================
             1. 3D COASTAL VILLA GLASS SCREEN (COVER)
             ======================================================== -->
        <div
            v-if="isCoverVisible"
            class="fixed inset-0 z-50 flex items-center justify-center overflow-hidden bg-[#020814] transition-opacity duration-300"
            :class="{ 'pointer-events-none': isTerraceOpening }"
        >
            <!-- Left Terrace Glass Door -->
            <div
                class="absolute inset-y-0 left-0 w-1/2 bg-[#08182E] border-r-4 border-[#38BDF8] shadow-2xl transition-transform duration-[1200ms] ease-[cubic-bezier(0.2,0.8,0.2,1)] flex flex-col justify-between p-6 overflow-hidden"
                :class="{ '-translate-x-full': isTerraceOpening }"
            >
                <div class="absolute inset-0 bg-gradient-to-r from-black/80 to-transparent"></div>
                <div class="relative z-10 text-xs tracking-[0.35em] uppercase text-sky-300 font-semibold">
                    SAMUDRA CINTA • OCEAN HORIZON
                </div>
                <!-- Shell Motif -->
                <div class="absolute right-4 top-1/2 -translate-y-1/2 w-8 h-24 rounded-full border-2 border-[#38BDF8] bg-gradient-to-r from-[#0369A1] to-[#082F49] shadow-xl flex items-center justify-center">
                    <div class="w-3 h-12 rounded-full bg-[#BAE6FD]/60"></div>
                </div>
                <div class="relative z-10 text-[10px] text-sky-200/50 uppercase tracking-widest">
                    Bali Cliff Sunset Edition
                </div>
            </div>

            <!-- Right Terrace Glass Door -->
            <div
                class="absolute inset-y-0 right-0 w-1/2 bg-[#08182E] border-l-4 border-[#38BDF8] shadow-2xl transition-transform duration-[1200ms] ease-[cubic-bezier(0.2,0.8,0.2,1)] flex flex-col justify-between items-end p-6 overflow-hidden"
                :class="{ 'translate-x-full': isTerraceOpening }"
            >
                <div class="absolute inset-0 bg-gradient-to-l from-black/80 to-transparent"></div>
                <div class="relative z-10 text-xs tracking-[0.35em] uppercase text-sky-300 font-semibold">
                    ETERNAL HORIZON
                </div>
                <div class="absolute left-4 top-1/2 -translate-y-1/2 w-8 h-24 rounded-full border-2 border-[#38BDF8] bg-gradient-to-l from-[#0369A1] to-[#082F49] shadow-xl flex items-center justify-center">
                    <div class="w-3 h-12 rounded-full bg-[#BAE6FD]/60"></div>
                </div>
                <div class="relative z-10 text-[10px] text-sky-200/50 uppercase tracking-widest">
                    Sacred Pearl Ceremony
                </div>
            </div>

            <!-- Center Opener Card & Golden Oyster Pearl Emblem -->
            <div
                class="relative z-20 max-w-sm w-full mx-4 p-8 rounded-3xl bg-[#07172B]/95 backdrop-blur-xl shadow-2xl border-2 border-sky-400/60 text-center transition-all duration-700 transform"
                :class="{ 'scale-90 opacity-0': isTerraceOpening }"
            >
                <!-- Glowing Golden Oyster Shell Badge -->
                <div class="w-16 h-16 mx-auto -mt-14 mb-4 rounded-full bg-gradient-to-tr from-[#0284C7] via-[#38BDF8] to-[#F59E0B] border-2 border-white shadow-[0_0_35px_rgba(56,189,248,0.6)] flex items-center justify-center text-2xl">
                    🐚
                </div>

                <div class="text-[11px] tracking-[0.35em] uppercase text-sky-300 mb-2 font-medium">
                    The Wedding Invitation
                </div>

                <h1 class="text-3xl sm:text-4xl font-serif text-white font-bold tracking-wide mb-1 drop-shadow-md">
                    {{ groom.nickname }} &amp; {{ bride.nickname }}
                </h1>

                <div class="flex items-center justify-center space-x-3 my-3">
                    <div class="h-[1px] w-12 bg-gradient-to-r from-transparent to-sky-400"></div>
                    <span class="text-xs text-sky-300 font-semibold tracking-widest">BAHTERA CINTA</span>
                    <div class="h-[1px] w-12 bg-gradient-to-l from-transparent to-sky-400"></div>
                </div>

                <p class="text-xs text-sky-200/70 mb-6 font-sans">
                    {{ safeFormatDisplayDate(weddingDate) }}
                </p>

                <!-- Guest Recipient Tag -->
                <div class="bg-black/50 border border-sky-400/30 rounded-2xl p-4 mb-6 text-center shadow-inner">
                    <div class="text-[10px] uppercase tracking-wider text-sky-300/80 mb-0.5">Kepada Yth. Bapak/Ibu/Saudara/i:</div>
                    <div class="text-base font-semibold text-white truncate">
                        {{ props.guestName || 'Tamu Undangan Terhormat' }}
                    </div>
                    <div class="text-[10px] text-sky-200/40 italic mt-0.5">*Mohon maaf apabila ada kesalahan penulisan nama/gelar</div>
                </div>

                <!-- Open Door Button -->
                <button
                    @click="handleOpenInvitation"
                    class="group relative w-full inline-flex items-center justify-center px-6 py-3.5 rounded-full text-slate-950 bg-gradient-to-r from-sky-400 via-sky-300 to-sky-400 shadow-xl shadow-sky-500/25 hover:shadow-sky-500/45 hover:scale-[1.03] active:scale-95 transition-all duration-300 font-bold tracking-widest text-xs uppercase overflow-hidden cursor-pointer"
                >
                    <span class="absolute inset-0 bg-white/40 transform -translate-x-full group-hover:translate-x-full transition-transform duration-700"></span>
                    <span class="mr-2">🌊</span>
                    <span>Buka Pintu Samudra</span>
                </button>
            </div>
        </div>

        <!-- ========================================================
             2. MAIN INVITATION EXPERIENCE (TWO-PANE DESKTOP / MOBILE)
             ======================================================== -->
        <div class="min-h-screen flex flex-col lg:flex-row">

            <!-- LEFT FIXED STAGE: Uluwatu Cliff Sunset & Couple (Desktop) -->
            <div
                class="hidden lg:flex lg:w-[45%] xl:w-[42%] fixed inset-y-0 left-0 z-20 flex-col items-center justify-between p-10 select-none overflow-hidden"
                @wheel="handleStageWheel"
            >
                <div class="absolute inset-0 pointer-events-none">
                    <img
                        src="/images/templates/ocean/ocean-sunset-cliff.jpg"
                        alt="Ocean Cliff Sunset"
                        class="w-full h-full object-cover filter brightness-50 contrast-110 scale-105"
                    />
                    <div class="absolute inset-0 bg-gradient-to-t from-[#040C18] via-black/30 to-[#07172B]/75"></div>
                </div>

                <!-- Top Header Badge -->
                <div class="relative z-10 text-center">
                    <div class="inline-block px-4 py-1.5 rounded-full border border-sky-400/40 bg-black/60 backdrop-blur-md text-[11px] uppercase tracking-[0.3em] text-sky-200 shadow-lg">
                        Bali Cliff &amp; Ocean Pearl Edition
                    </div>
                    <div class="mt-2 text-xs text-sky-300/80 tracking-widest font-light">
                        Cinta Abadi Seluas Samudra
                    </div>
                </div>

                <!-- Center 3D Interactive Portrait Frame -->
                <div
                    class="relative z-10 w-full max-w-sm flex flex-col items-center transition-transform duration-300 ease-out"
                    :style="{
                        transform: `perspective(900px) rotateX(${targetRotateX}deg) rotateY(${targetRotateY}deg)`,
                    }"
                >
                    <div class="relative w-64 h-84 rounded-3xl p-2.5 bg-gradient-to-tr from-[#38BDF8] via-[#0284C7] to-[#F59E0B] shadow-[0_15px_40px_rgba(0,0,0,0.8)] overflow-hidden border-2 border-sky-200/50 group">
                        <img
                            :src="coverPhoto"
                            alt="Couple Cover"
                            class="w-full h-full object-cover rounded-2xl group-hover:scale-105 transition-transform duration-700"
                        />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent pointer-events-none"></div>

                        <div class="absolute bottom-3 left-1/2 -translate-x-1/2 px-4 py-1 rounded-full bg-black/75 backdrop-blur-md border border-sky-400/60 text-sky-200 text-xs font-serif font-bold tracking-widest uppercase truncate max-w-[90%]">
                            🌊 Ocean Horizon
                        </div>
                    </div>

                    <h2 class="text-3xl font-serif font-bold text-white tracking-wide mt-5 drop-shadow-md">
                        {{ groom.nickname }} &amp; {{ bride.nickname }}
                    </h2>
                    <div class="text-xs text-sky-300/90 font-light tracking-widest mt-1">
                        {{ safeFormatDisplayDate(weddingDate) }}
                    </div>
                </div>

                <!-- Bottom Venue Card -->
                <div class="relative z-10 text-center w-full max-w-xs">
                    <div class="p-3.5 rounded-2xl bg-black/60 border border-sky-500/25 backdrop-blur-md shadow-xl">
                        <div class="text-[10px] text-sky-300/80 uppercase tracking-widest font-semibold">Tempat Resepsi</div>
                        <div class="text-xs text-sky-100 font-medium truncate mt-0.5">
                            {{ props.events[0]?.venue_name || 'The Cliff Edge Glasshouse, Uluwatu' }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT SCROLLABLE INVITATION CONTENT (Desktop & Mobile) -->
            <div class="w-full lg:w-[55%] xl:w-[58%] lg:ml-auto relative z-20 min-h-screen pb-24">

                <!-- MOBILE HERO COVER (Shown only on mobile) -->
                <div class="lg:hidden relative min-h-[92vh] flex flex-col justify-between items-center text-center p-6 text-sky-50 overflow-hidden">
                    <div class="absolute inset-0 pointer-events-none">
                        <img
                            src="/images/templates/ocean/ocean-sunset-cliff.jpg"
                            alt="Ocean Sunset Cliff"
                            class="w-full h-full object-cover filter brightness-45 contrast-105 scale-105"
                        />
                        <div class="absolute inset-0 bg-gradient-to-b from-black/60 via-black/40 to-[#040C18]"></div>
                    </div>

                    <div class="relative z-10 mt-6">
                        <span class="inline-block px-4 py-1.5 rounded-full border border-sky-400/40 bg-black/60 text-[10px] uppercase tracking-[0.25em] text-sky-200">
                            The Wedding Of
                        </span>
                    </div>

                    <div class="relative z-10 flex flex-col items-center my-6">
                        <div class="relative w-56 h-56 rounded-full p-2 bg-gradient-to-tr from-[#38BDF8] via-[#0284C7] to-[#F59E0B] shadow-2xl">
                            <div class="w-full h-full rounded-full overflow-hidden border-2 border-white/60">
                                <img
                                    :src="coverPhoto"
                                    alt="Couple Cover"
                                    class="w-full h-full object-cover"
                                />
                            </div>
                            <div class="absolute -bottom-2 -right-2 w-11 h-11 bg-[#0284C7] rounded-full border-2 border-sky-200 flex items-center justify-center text-xl shadow-lg animate-bounce">
                                🦪
                            </div>
                        </div>

                        <h1 class="text-3xl font-serif font-bold text-white tracking-wider mt-5">
                            {{ groom.nickname }} &amp; {{ bride.nickname }}
                        </h1>
                        <p class="text-xs text-sky-300 font-light tracking-widest mt-1">
                            {{ safeFormatDisplayDate(weddingDate) }}
                        </p>
                    </div>

                    <div class="relative z-10 mb-4 flex flex-col items-center text-sky-200/60 animate-bounce">
                        <span class="text-[10px] tracking-widest uppercase mb-1">Gulir ke bawah</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                        </svg>
                    </div>
                </div>

                <!-- ====================================================
                     SECTION 1: SACRED QUOTE & OCEAN BLESSING
                     ==================================================== -->
                <section class="px-6 py-16 max-w-xl mx-auto text-center ocean-fade-up relative">
                    <div class="inline-flex items-center justify-center space-x-2 text-xs tracking-widest text-sky-400 uppercase mb-4 font-semibold">
                        <span class="w-8 h-[1px] bg-sky-400"></span>
                        <span>OCEAN SERENITY</span>
                        <span class="w-8 h-[1px] bg-sky-400"></span>
                    </div>

                    <div class="w-12 h-12 mx-auto mb-4 border-2 border-sky-400 rounded-full flex items-center justify-center text-2xl bg-sky-950/80 shadow-lg shadow-sky-500/20">
                        🌊
                    </div>

                    <blockquote class="text-base sm:text-lg italic font-serif text-sky-50/90 leading-relaxed mb-4">
                        "{{ invitationData.content.quote || 'Dan di antara tanda-tanda kebesaran-Nya ialah Dia menciptakan pasangan-pasangan untukmu dari jenismu sendiri, agar kamu cenderung dan merasa tenteram kepadanya, dan dijadikan-Nya di antaramu rasa kasih dan sayang.' }}"
                    </blockquote>
                    <p class="text-xs font-semibold text-sky-300 tracking-wider uppercase">
                        — {{ invitationData.content.quoteSource || 'QS. Ar-Rum: 21' }}
                    </p>
                </section>

                <!-- ====================================================
                     SECTION 2: BRIDE & GROOM PROFILE (MEMPELAI)
                     ==================================================== -->
                <section class="px-6 py-12 max-w-xl mx-auto ocean-fade-up">
                    <div class="text-center mb-10">
                        <span class="text-[10px] tracking-[0.3em] uppercase text-sky-400 font-semibold">Pasangan Mempelai</span>
                        <h2 class="text-2xl sm:text-3xl font-serif font-bold text-white mt-1">Groom &amp; Bride</h2>
                        <div class="w-12 h-0.5 bg-sky-400 mx-auto mt-2"></div>
                    </div>

                    <div class="space-y-8">
                        <!-- GROOM CARD (3D Tilt) -->
                        <div
                            class="relative p-6 sm:p-8 rounded-3xl bg-[#08182E]/85 border border-sky-400/40 shadow-2xl hover:shadow-sky-500/10 transition-all duration-300"
                            @mousemove="(e) => handleCardTilt(e, 'groom')"
                            @mouseleave="() => resetCardTilt('groom')"
                            :style="{
                                transform: `perspective(800px) rotateX(${cardGroomTilt.x}deg) rotateY(${cardGroomTilt.y}deg)`,
                            }"
                        >
                            <div class="flex flex-col sm:flex-row items-center sm:items-start space-y-4 sm:space-y-0 sm:space-x-6 text-center sm:text-left">
                                <div class="relative w-28 h-28 sm:w-32 sm:h-32 rounded-full p-1.5 bg-gradient-to-tr from-sky-400 via-blue-600 to-amber-500 shadow-lg flex-shrink-0">
                                    <img
                                        :src="groom.photo"
                                        alt="Groom Photo"
                                        class="w-full h-full object-cover rounded-full"
                                    />
                                    <div class="absolute bottom-0 right-0 bg-[#0284C7] text-white text-[10px] px-2 py-0.5 rounded-full border border-sky-200 font-bold">
                                        GROOM
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-xl font-serif font-bold text-white">
                                        {{ groom.fullName }}
                                    </h3>
                                    <p class="text-xs text-sky-300/80 font-medium tracking-wide mt-1">
                                        Putra Pertama dari:
                                    </p>
                                    <p class="text-xs text-slate-300 mt-0.5 leading-relaxed">
                                        {{ groom.parents }}
                                    </p>
                                    <div v-if="groom.instagram" class="mt-3">
                                        <a
                                            :href="'https://instagram.com/' + groom.instagram.replace('@', '')"
                                            target="_blank"
                                            class="inline-flex items-center space-x-1.5 text-xs text-sky-400 hover:text-sky-300 font-medium"
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
                            class="relative p-6 sm:p-8 rounded-3xl bg-[#08182E]/85 border border-sky-400/40 shadow-2xl hover:shadow-sky-500/10 transition-all duration-300"
                            @mousemove="(e) => handleCardTilt(e, 'bride')"
                            @mouseleave="() => resetCardTilt('bride')"
                            :style="{
                                transform: `perspective(800px) rotateX(${cardBrideTilt.x}deg) rotateY(${cardBrideTilt.y}deg)`,
                            }"
                        >
                            <div class="flex flex-col sm:flex-row items-center sm:items-start space-y-4 sm:space-y-0 sm:space-x-6 text-center sm:text-left">
                                <div class="relative w-28 h-28 sm:w-32 sm:h-32 rounded-full p-1.5 bg-gradient-to-tr from-sky-400 via-blue-600 to-amber-500 shadow-lg flex-shrink-0">
                                    <img
                                        :src="bride.photo"
                                        alt="Bride Photo"
                                        class="w-full h-full object-cover rounded-full"
                                    />
                                    <div class="absolute bottom-0 right-0 bg-[#0284C7] text-white text-[10px] px-2 py-0.5 rounded-full border border-sky-200 font-bold">
                                        BRIDE
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-xl font-serif font-bold text-white">
                                        {{ bride.fullName }}
                                    </h3>
                                    <p class="text-xs text-sky-300/80 font-medium tracking-wide mt-1">
                                        Putri Kedua dari:
                                    </p>
                                    <p class="text-xs text-slate-300 mt-0.5 leading-relaxed">
                                        {{ bride.parents }}
                                    </p>
                                    <div v-if="bride.instagram" class="mt-3">
                                        <a
                                            :href="'https://instagram.com/' + bride.instagram.replace('@', '')"
                                            target="_blank"
                                            class="inline-flex items-center space-x-1.5 text-xs text-sky-400 hover:text-sky-300 font-medium"
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
                     SECTION 3: 60FPS INTERACTIVE PEARL OYSTER MINI-GAME
                     ==================================================== -->
                <section class="px-6 py-12 max-w-xl mx-auto ocean-fade-up">
                    <div class="text-center mb-6">
                        <span class="text-[10px] tracking-[0.3em] uppercase text-sky-400 font-semibold">Wahana Interaktif Laut</span>
                        <h3 class="text-2xl sm:text-3xl font-serif font-bold text-white mt-1">Mutiara Berkah Samudra</h3>
                        <p class="text-xs text-sky-200/70 mt-1">Sentuh kerang mutiara untuk melepaskan mutiara doa restu</p>
                    </div>

                    <!-- Interactive Pearl Oyster Pool Container -->
                    <div
                        ref="pearlGameContainerRef"
                        @click="handleTapOysterPond"
                        class="relative h-80 sm:h-96 rounded-3xl border-2 border-sky-400/60 shadow-[0_0_50px_rgba(56,189,248,0.25)] overflow-hidden cursor-pointer select-none bg-[#03152B]"
                    >
                        <div
                            class="absolute inset-0 pointer-events-none filter brightness-70"
                            style="background: radial-gradient(circle at 50% 50%, rgba(14, 165, 233, 0.4) 0%, rgba(3, 21, 43, 0.95) 80%), url('/images/templates/ocean/clear-water.jpg') center/cover;"
                        ></div>

                        <!-- 60 FPS Canvas for Oyster Shell & Floating Pearls -->
                        <canvas
                            ref="pearlGameCanvasRef"
                            class="absolute inset-0 w-full h-full z-10"
                        ></canvas>

                        <!-- Counter and Cue Overlay -->
                        <div class="absolute top-4 inset-x-4 z-20 flex items-center justify-between pointer-events-none">
                            <div class="px-3.5 py-1 rounded-full bg-black/75 backdrop-blur-md border border-sky-400/50 text-sky-200 text-xs font-semibold shadow-lg">
                                🦪 {{ pearlsCollectedCount }} Mutiara Doa Terkumpul
                            </div>
                            <div class="text-[11px] text-amber-200 bg-sky-950/80 px-3 py-1 rounded-full border border-sky-400/40 animate-pulse">
                                Ketuk Kerang di Dasar Air ✨
                            </div>
                        </div>

                        <!-- Current Blessing Card -->
                        <div class="absolute bottom-4 inset-x-4 z-20 pointer-events-none">
                            <div class="p-3 rounded-2xl bg-black/80 backdrop-blur-md border border-sky-400/40 text-center shadow-xl">
                                <div class="text-[10px] uppercase text-sky-300 font-semibold tracking-wider">Doa Restu Samudra:</div>
                                <div class="text-xs text-white italic mt-0.5">"{{ latestBlessing }}"</div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- ====================================================
                     SECTION 4: COUNTDOWN TIMER (WAKTU BAHAGIA)
                     ==================================================== -->
                <section class="px-6 py-12 max-w-xl mx-auto ocean-fade-up">
                    <div class="p-6 sm:p-8 rounded-3xl bg-[#08182E]/85 border border-sky-400/40 shadow-2xl text-center relative overflow-hidden">
                        <div class="text-[10px] tracking-[0.3em] uppercase text-sky-400 font-semibold mb-1">Menuju Hari Bahagia</div>
                        <h3 class="text-xl font-serif font-bold text-white mb-6">Hitung Mundur Pernikahan</h3>

                        <div class="grid grid-cols-4 gap-3 sm:gap-4 max-w-md mx-auto mb-6">
                            <div class="p-3 sm:p-4 rounded-2xl bg-black/60 border border-sky-500/30">
                                <div class="text-2xl sm:text-3xl font-bold font-mono text-sky-300">{{ props.timeLeft.days }}</div>
                                <div class="text-[10px] uppercase text-slate-400 mt-1">Hari</div>
                            </div>
                            <div class="p-3 sm:p-4 rounded-2xl bg-black/60 border border-sky-500/30">
                                <div class="text-2xl sm:text-3xl font-bold font-mono text-sky-300">{{ props.timeLeft.hours }}</div>
                                <div class="text-[10px] uppercase text-slate-400 mt-1">Jam</div>
                            </div>
                            <div class="p-3 sm:p-4 rounded-2xl bg-black/60 border border-sky-500/30">
                                <div class="text-2xl sm:text-3xl font-bold font-mono text-sky-300">{{ props.timeLeft.minutes }}</div>
                                <div class="text-[10px] uppercase text-slate-400 mt-1">Menit</div>
                            </div>
                            <div class="p-3 sm:p-4 rounded-2xl bg-black/60 border border-sky-500/30">
                                <div class="text-2xl sm:text-3xl font-bold font-mono text-sky-300">{{ props.timeLeft.seconds }}</div>
                                <div class="text-[10px] uppercase text-slate-400 mt-1">Detik</div>
                            </div>
                        </div>

                        <a
                            :href="googleCalendarUrl"
                            target="_blank"
                            class="inline-flex items-center space-x-2 px-5 py-2.5 rounded-full bg-sky-500/20 text-sky-200 border border-sky-400/50 hover:bg-sky-500/30 text-xs font-semibold transition-all shadow-md"
                        >
                            <span>📅 Simpan ke Google Calendar</span>
                        </a>
                    </div>
                </section>

                <!-- ====================================================
                     SECTION 5: AGENDA ACARA (AKAD & RESEPSI)
                     ==================================================== -->
                <section class="px-6 py-12 max-w-xl mx-auto ocean-fade-up">
                    <div class="text-center mb-10">
                        <span class="text-[10px] tracking-[0.3em] uppercase text-sky-400 font-semibold">Rangkaian Acara</span>
                        <h2 class="text-2xl sm:text-3xl font-serif font-bold text-white mt-1">Wedding Agenda</h2>
                        <div class="w-12 h-0.5 bg-sky-400 mx-auto mt-2"></div>
                    </div>

                    <div class="space-y-6">
                        <div
                            v-for="(event, eIdx) in props.events"
                            :key="event.id || eIdx"
                            class="p-6 sm:p-8 rounded-3xl bg-[#08182E]/85 border border-sky-400/40 shadow-2xl relative overflow-hidden"
                        >
                            <div class="flex items-center justify-between mb-4">
                                <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-sky-500/20 text-sky-300 border border-sky-500/30">
                                    {{ eIdx === 0 ? 'Holy Matrimony / Pemberkatan' : 'Sunset Ocean Reception' }}
                                </span>
                                <span class="text-lg">🌊</span>
                            </div>

                            <h3 class="text-xl font-serif font-bold text-white mb-2">
                                {{ event.name }}
                            </h3>

                            <div class="space-y-2 text-xs text-slate-300 mb-6 font-sans">
                                <div class="flex items-center space-x-2">
                                    <span class="text-sky-400">📅</span>
                                    <span>{{ safeFormatDisplayDate(event.starts_at) }}</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span class="text-sky-400">⏰</span>
                                    <span>{{ safeFormatEventTime(event.starts_at, event.ends_at, event.timezone) }}</span>
                                </div>
                                <div class="flex items-start space-x-2">
                                    <span class="text-sky-400 mt-0.5">📍</span>
                                    <div>
                                        <div class="font-semibold text-sky-200">{{ event.venue_name }}</div>
                                        <div class="text-slate-400 text-[11px] mt-0.5 leading-relaxed">{{ event.address }}</div>
                                    </div>
                                </div>
                            </div>

                            <a
                                v-if="event.maps_url"
                                :href="event.maps_url"
                                target="_blank"
                                class="w-full inline-flex items-center justify-center space-x-1.5 py-3 rounded-xl bg-gradient-to-r from-sky-600 to-blue-700 border border-sky-300/40 text-white hover:brightness-110 text-xs font-semibold transition-all shadow-md"
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
                    class="px-6 py-12 max-w-xl mx-auto ocean-fade-up"
                >
                    <div class="text-center mb-10">
                        <span class="text-[10px] tracking-[0.3em] uppercase text-sky-400 font-semibold">Galeri Kenangan</span>
                        <h2 class="text-2xl sm:text-3xl font-serif font-bold text-white mt-1">Our Moments</h2>
                        <div class="w-12 h-0.5 bg-sky-400 mx-auto mt-2"></div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        <div
                            v-for="(photo, pIdx) in invitationData.content.gallery"
                            :key="pIdx"
                            @click="openLightbox(photo)"
                            class="group relative aspect-square rounded-2xl overflow-hidden cursor-pointer shadow-lg border border-sky-500/30 hover:border-sky-300 transition-all hover:scale-[1.03]"
                        >
                            <img
                                :src="photo"
                                :alt="'Gallery photo ' + (pIdx + 1)"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
                            />
                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                <svg class="w-6 h-6 text-sky-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- ====================================================
                     SECTION 7: WEDDING GIFT (AMPLOP DIGITAL & KADO)
                     ==================================================== -->
                <section class="px-6 py-12 max-w-xl mx-auto ocean-fade-up">
                    <div class="text-center mb-10">
                        <span class="text-[10px] tracking-[0.3em] uppercase text-sky-400 font-semibold">Tanda Kasih &amp; Kado</span>
                        <h2 class="text-2xl sm:text-3xl font-serif font-bold text-white mt-1">Wedding Gift</h2>
                        <div class="w-12 h-0.5 bg-sky-400 mx-auto mt-2"></div>
                        <p class="text-xs text-slate-300 mt-2 max-w-md mx-auto">
                            Doa restu Anda merupakan karunia terindah bagi kami. Namun jika ingin memberikan tanda kasih secara cashless, Anda dapat menyalurkannya melalui:
                        </p>
                    </div>

                    <div class="p-6 sm:p-8 rounded-3xl bg-gradient-to-b from-[#08203E] to-[#040E1B] border-2 border-sky-400/50 shadow-2xl relative overflow-hidden">
                        <div class="space-y-4">
                            <div
                                v-for="(bank, bIdx) in digitalGift.accounts"
                                :key="bIdx"
                                class="p-5 rounded-2xl bg-black/60 border border-sky-500/30 shadow-md flex flex-col sm:flex-row sm:items-center justify-between gap-3"
                            >
                                <div>
                                    <div class="text-xs font-bold text-sky-300 uppercase tracking-wider">
                                        {{ bank.bank || bank.bank_name || bank.name || 'Bank Transfer' }}
                                    </div>
                                    <div class="text-lg font-mono font-bold text-white tracking-wider mt-0.5">
                                        {{ bank.account_number || bank.number }}
                                    </div>
                                    <div class="text-xs text-slate-400">
                                        a.n. {{ bank.account_name || bank.account_holder || bank.holder }}
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <button
                                        @click="copyBankNumber(bank.account_number || bank.number, bIdx)"
                                        class="px-4 py-2 rounded-xl text-xs font-semibold bg-sky-400 text-slate-950 hover:bg-sky-300 transition-colors flex items-center space-x-1 cursor-pointer font-sans font-bold"
                                    >
                                        <span>{{ copiedBankIndex === bIdx ? '✓ Tersalin' : 'Salin Rekening' }}</span>
                                    </button>
                                    <button
                                        v-if="bank.qr_image || bank.qris_image_url || digitalGift.qris_url"
                                        @click="props.openQris(bank)"
                                        class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-sky-950/80 text-sky-200 border border-sky-400/50 hover:bg-sky-900 transition-colors cursor-pointer"
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
                <section class="px-6 py-12 max-w-xl mx-auto ocean-fade-up">
                    <div class="text-center mb-10">
                        <span class="text-[10px] tracking-[0.3em] uppercase text-sky-400 font-semibold">Konfirmasi Kehadiran</span>
                        <h2 class="text-2xl sm:text-3xl font-serif font-bold text-white mt-1">RSVP &amp; Ucapan</h2>
                        <div class="w-12 h-0.5 bg-sky-400 mx-auto mt-2"></div>
                    </div>

                    <div class="p-6 sm:p-8 rounded-3xl bg-[#08182E]/85 border border-sky-400/40 shadow-2xl mb-8">
                        <form @submit.prevent="props.submitRsvp" class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-sky-300 uppercase tracking-wider mb-1">
                                    Nama Lengkap
                                </label>
                                <input
                                    v-model="props.rsvpForm.guest_name"
                                    type="text"
                                    required
                                    placeholder="Masukkan nama Anda"
                                    class="w-full px-4 py-2.5 text-sm rounded-xl bg-black/60 border border-sky-500/40 text-white focus:ring-2 focus:ring-sky-400 outline-none transition-all font-sans"
                                />
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-sky-300 uppercase tracking-wider mb-1">
                                    Konfirmasi Kehadiran
                                </label>
                                <div class="grid grid-cols-2 gap-3 font-sans">
                                    <button
                                        type="button"
                                        @click="props.rsvpForm.attendance_status = 'attending'"
                                        class="py-2.5 px-4 rounded-xl text-xs font-semibold border transition-all text-center cursor-pointer"
                                        :class="props.rsvpForm.attendance_status === 'attending' ? 'bg-sky-400 text-slate-950 border-sky-300 font-bold shadow-md' : 'bg-black/40 text-slate-300 border-slate-700'"
                                    >
                                        ✓ Hadir
                                    </button>
                                    <button
                                        type="button"
                                        @click="props.rsvpForm.attendance_status = 'declined'"
                                        class="py-2.5 px-4 rounded-xl text-xs font-semibold border transition-all text-center cursor-pointer"
                                        :class="props.rsvpForm.attendance_status === 'declined' ? 'bg-slate-700 text-white border-slate-500 shadow-md' : 'bg-black/40 text-slate-300 border-slate-700'"
                                    >
                                        ✕ Berhalangan
                                    </button>
                                </div>
                            </div>

                            <div v-if="props.rsvpForm.attendance_status === 'attending'">
                                <label class="block text-xs font-semibold text-sky-300 uppercase tracking-wider mb-1">
                                    Jumlah Tamu
                                </label>
                                <select
                                    v-model.number="props.rsvpForm.attendee_count"
                                    class="w-full px-4 py-2.5 text-sm rounded-xl bg-black/60 border border-sky-500/40 text-white focus:ring-2 focus:ring-sky-400 outline-none transition-all font-sans"
                                >
                                    <option :value="1">1 Orang</option>
                                    <option :value="2">2 Orang</option>
                                    <option :value="3">3 Orang</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-sky-300 uppercase tracking-wider mb-1">
                                    Doa &amp; Ucapan Selamat
                                </label>
                                <textarea
                                    v-model="props.rsvpForm.message"
                                    rows="3"
                                    placeholder="Tuliskan doa restu untuk kedua mempelai..."
                                    class="w-full px-4 py-2.5 text-sm rounded-xl bg-black/60 border border-sky-500/40 text-white focus:ring-2 focus:ring-sky-400 outline-none transition-all font-sans"
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
                                class="w-full py-3.5 rounded-xl bg-gradient-to-r from-sky-400 via-sky-300 to-sky-400 text-slate-950 text-xs font-bold tracking-widest uppercase hover:brightness-110 active:scale-98 transition-all disabled:opacity-50 cursor-pointer shadow-xl font-sans"
                            >
                                <span v-if="props.rsvpSubmitting">Mengirimkan...</span>
                                <span v-else>Kirim Konfirmasi &amp; Doa</span>
                            </button>
                        </form>
                    </div>

                    <!-- GUESTBOOK WISH FEED -->
                    <div class="space-y-3">
                        <div class="text-xs font-semibold text-sky-300 uppercase tracking-widest text-center mb-3">
                            Doa &amp; Restu Sahabat ({{ props.guestbook.length }})
                        </div>

                        <div class="max-h-96 overflow-y-auto space-y-3 pr-1">
                            <div
                                v-for="(entry, gIdx) in props.guestbook"
                                :key="gIdx"
                                class="p-4 rounded-2xl bg-black/60 border border-sky-500/30 shadow-md relative pl-4 border-l-4 border-l-[#38BDF8]"
                            >
                                <div class="flex items-center justify-between mb-1">
                                    <div class="text-xs font-bold text-sky-200">
                                        {{ entry.guest_name }}
                                    </div>
                                    <div class="text-[10px] text-slate-400 font-sans">
                                        {{ entry.created_at }}
                                    </div>
                                </div>
                                <p class="text-xs text-slate-300 font-sans leading-relaxed">
                                    {{ entry.message }}
                                </p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- FOOTER -->
                <footer class="px-6 pt-8 pb-16 text-center ocean-fade-up">
                    <div class="w-10 h-10 mx-auto mb-4 border border-sky-400 rounded-full flex items-center justify-center text-xl bg-black/50">
                        🌊
                    </div>
                    <p class="text-xs text-slate-300 max-w-sm mx-auto leading-relaxed mb-6 font-sans">
                        Merupakan kehormatan &amp; kebahagiaan yang tak terhingga bagi kami atas kehadiran dan doa restu Bapak/Ibu/Saudara/i sekalian.
                    </p>

                    <div class="text-sm font-serif font-bold text-sky-300 tracking-wider">
                        Kami yang berbahagia,
                    </div>
                    <div class="text-lg font-serif font-bold text-white mt-1">
                        {{ groom.nickname }} &amp; {{ bride.nickname }}
                    </div>

                    <div class="mt-8 text-[10px] text-sky-200/40 tracking-widest uppercase">
                        Undangan.id • Ocean Horizon Pearl Edition
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
            <div class="relative max-w-3xl max-h-[85vh] p-2 bg-[#08182E] border-2 border-sky-400 rounded-2xl overflow-hidden shadow-2xl">
                <img
                    :src="activeLightboxImg"
                    alt="Lightbox Photo"
                    class="w-full h-full object-contain max-h-[80vh] rounded-xl"
                />
                <button
                    @click="closeLightbox"
                    class="absolute top-4 right-4 w-9 h-9 rounded-full bg-black/80 text-white flex items-center justify-center hover:bg-sky-600 transition-colors"
                >
                    ✕
                </button>
            </div>
        </div>
    </div>
</template>

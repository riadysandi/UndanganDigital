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
    return '09:00 WIB - Selesai';
};

const safeFormatDisplayDate = (d: string) => {
    if (typeof props.formatDisplayDate === 'function') {
        return props.formatDisplayDate(d);
    }
    return d || 'Minggu, 12 Desember 2026';
};

// Canvas ref for 60fps falling Sakura petals
const canvasRef = ref<HTMLCanvasElement | null>(null);
let animationFrameId: number | null = null;

// Forward stage wheel to window scroll
const handleStageWheel = (e: WheelEvent) => {
    window.scrollBy({ top: e.deltaY, behavior: 'auto' });
};

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
    const title = encodeURIComponent((props.invitationData?.content?.weddingTitle || 'Japanese Zen Wedding') + ' - Kenji & Ayumi');
    const firstEvent = props.events?.[0];
    const location = encodeURIComponent((firstEvent?.venue_name || '') + ' - ' + (firstEvent?.address || ''));
    return `https://www.google.com/calendar/render?action=TEMPLATE&text=${title}&location=${location}&dates=20261212T010000Z/20261212T140000Z`;
});

// ========================================================
// 1. SHOJI SLIDING DOOR TRANSITION ENGINE
// ========================================================
const isCoverVisible = ref(!props.isOpen);
const isShojiOpening = ref(false);

const handleOpenInvitation = () => {
    if (isShojiOpening.value) return;
    isShojiOpening.value = true;

    // Trigger parent audio playback immediately inside user gesture
    emit('openInvitation');

    // Confetti with sakura blossom tones & gold
    confetti({
        particleCount: 65,
        spread: 90,
        origin: { y: 0.6 },
        colors: ['#E89CAE', '#F4C2C2', '#D4AF37', '#FFF0F5', '#C23B22']
    });

    // Keep doors sliding smoothly for 1300ms before removing the overlay from DOM
    setTimeout(() => {
        isCoverVisible.value = false;
    }, 1300);
};

// ========================================================
// 2. INTERACTIVE KOI POND ENGINE (Ripples & Fish Flutter)
// ========================================================
interface WaterRipple {
    id: number;
    x: number;
    y: number;
}
const pondRipples = ref<WaterRipple[]>([]);
let nextRippleId = 1;

const triggerPondRipple = (e: MouseEvent) => {
    const pond = e.currentTarget as HTMLElement;
    const rect = pond.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;
    const id = nextRippleId++;
    pondRipples.value.push({ id, x, y });
    setTimeout(() => {
        pondRipples.value = pondRipples.value.filter(r => r.id !== id);
    }, 1600);
};

// Periodic gentle natural pond ripples
let pondInterval: number | null = null;
const startPeriodicRipples = () => {
    pondInterval = window.setInterval(() => {
        if (pondRipples.value.length < 5) {
            const rx = Math.random() * 260 + 40;
            const ry = Math.random() * 160 + 30;
            const id = nextRippleId++;
            pondRipples.value.push({ id, x: rx, y: ry });
            setTimeout(() => {
                pondRipples.value = pondRipples.value.filter(r => r.id !== id);
            }, 1600);
        }
    }, 2200);
};

// ========================================================
// 3. SAKURA PETALS ENGINE (Canvas 2D, 60fps, 3D Flutter)
// ========================================================
interface SakuraPetal {
    x: number;
    y: number;
    size: number;
    speedX: number;
    speedY: number;
    rotation: number;
    rotSpeed: number;
    swing: number;
    swingSpeed: number;
    opacity: number;
    color: string;
}

const initSakuraCanvas = () => {
    const canvas = canvasRef.value;
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

    const petalColors = [
        'rgba(244, 194, 194, 0.85)', // soft blush
        'rgba(232, 156, 174, 0.90)', // sakura pink
        'rgba(255, 230, 235, 0.80)', // pale petal white
        'rgba(217, 119, 127, 0.75)', // deeper rose
    ];

    const petalCount = window.innerWidth < 768 ? 25 : 48;
    const petals: SakuraPetal[] = [];

    for (let i = 0; i < petalCount; i++) {
        petals.push({
            x: Math.random() * width,
            y: Math.random() * height,
            size: Math.random() * 8 + 7,
            speedX: Math.random() * 1.6 - 0.2, // slight wind drift to right
            speedY: Math.random() * 1.3 + 0.8, // gentle downward flutter
            rotation: Math.random() * 360,
            rotSpeed: (Math.random() - 0.5) * 1.8,
            swing: Math.random() * Math.PI * 2,
            swingSpeed: Math.random() * 0.025 + 0.01,
            opacity: Math.random() * 0.4 + 0.6,
            color: petalColors[Math.floor(Math.random() * petalColors.length)],
        });
    }

    const drawPetal = (p: SakuraPetal) => {
        ctx.save();
        ctx.translate(p.x, p.y);
        ctx.rotate((p.rotation * Math.PI) / 180);
        ctx.scale(Math.cos(p.swing), 1); // 3D flutter effect

        ctx.fillStyle = p.color;
        ctx.beginPath();
        // Authentic Sakura petal shape with curved tip and gentle notch
        ctx.moveTo(0, 0);
        ctx.bezierCurveTo(-p.size / 2, -p.size * 0.8, -p.size * 0.8, -p.size * 1.6, 0, -p.size * 2);
        ctx.bezierCurveTo(p.size * 0.8, -p.size * 1.6, p.size / 2, -p.size * 0.8, 0, 0);
        ctx.closePath();
        ctx.fill();

        ctx.restore();
    };

    const render = () => {
        ctx.clearRect(0, 0, width, height);

        for (const p of petals) {
            p.swing += p.swingSpeed;
            p.rotation += p.rotSpeed;
            p.x += p.speedX + Math.sin(p.swing) * 0.8;
            p.y += p.speedY;

            // Screen wrap
            if (p.y > height + 25) {
                p.y = -25;
                p.x = Math.random() * width;
            }
            if (p.x > width + 25) {
                p.x = -25;
            }

            drawPetal(p);
        }

        animationFrameId = requestAnimationFrame(render);
    };

    render();

    return () => {
        window.removeEventListener('resize', onResize);
    };
};

let cleanupCanvas: (() => void) | undefined;

onMounted(() => {
    window.addEventListener('mousemove', handleMouseMove, { passive: true });
    window.addEventListener('touchmove', handleTouchMove, { passive: true });

    startPeriodicRipples();

    nextTick(() => {
        cleanupCanvas = initSakuraCanvas();

        // GSAP ScrollTrigger Animations
        const sections = document.querySelectorAll('.zen-fade-up');
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
    if (pondInterval) clearInterval(pondInterval);
    if (cleanupCanvas) cleanupCanvas();
    if (animationFrameId) cancelAnimationFrame(animationFrameId);
    ScrollTrigger.getAll().forEach((t) => t.kill());
});
</script>

<template>
    <div
        class="min-h-screen w-full relative bg-[#FBF8F3] text-[#2D2424] font-serif selection:bg-rose-200 selection:text-rose-900 overflow-x-hidden"
        :style="{
            backgroundColor: invitationData.theme.bgColor || '#FBF8F3',
            color: invitationData.theme.textColor || '#2D2424',
        }"
    >
        <!-- Persistent 60fps Falling Sakura Petals Canvas -->
        <canvas
            ref="canvasRef"
            class="fixed inset-0 pointer-events-none z-30 w-full h-full"
        ></canvas>

        <!-- Subtle Washi Paper Japanese Texture Overlay -->
        <div
            class="fixed inset-0 pointer-events-none z-10 opacity-30 mix-blend-multiply bg-repeat"
            style="background-image: radial-gradient(#d3c5b4 0.75px, transparent 0.75px); background-size: 16px 16px;"
        ></div>

        <!-- ========================================================
             SWAYING CHERRY BLOSSOM TREE BRANCHES (Top Corners)
             ======================================================== -->
        <!-- Top Left Swaying Branch -->
        <div class="fixed top-0 left-0 z-25 pointer-events-none w-48 sm:w-64 origin-top-left animate-sway-left drop-shadow-md">
            <svg viewBox="0 0 200 150" class="w-full fill-none">
                <!-- Branch Stem -->
                <path d="M0,0 Q60,30 110,20 T190,60" stroke="#4A2E1B" stroke-width="4" stroke-linecap="round" />
                <path d="M60,30 Q90,70 140,80" stroke="#4A2E1B" stroke-width="2.5" stroke-linecap="round" />
                <!-- Blooming Sakura Clusters -->
                <circle cx="110" cy="20" r="10" fill="#F4C2C2" stroke="#C23B22" stroke-width="0.75" />
                <circle cx="110" cy="20" r="3" fill="#C23B22" />
                <circle cx="140" cy="80" r="12" fill="#E89CAE" stroke="#C23B22" stroke-width="0.75" />
                <circle cx="140" cy="80" r="3.5" fill="#8C1D0B" />
                <circle cx="180" cy="55" r="9" fill="#FFF0F5" stroke="#E89CAE" stroke-width="0.75" />
                <circle cx="85" cy="45" r="11" fill="#F4C2C2" stroke="#C23B22" stroke-width="0.75" />
                <!-- Leaves -->
                <path d="M115,15 Q125,5 130,12 T115,22 Z" fill="#8BA888" />
                <path d="M145,85 Q155,95 148,102 T138,88 Z" fill="#759772" />
            </svg>
        </div>

        <!-- Top Right Swaying Branch -->
        <div class="fixed top-0 right-0 z-25 pointer-events-none w-48 sm:w-64 origin-top-right animate-sway-right drop-shadow-md">
            <svg viewBox="0 0 200 150" class="w-full fill-none">
                <path d="M200,0 Q140,30 90,20 T10,60" stroke="#4A2E1B" stroke-width="4" stroke-linecap="round" />
                <path d="M140,30 Q110,70 60,80" stroke="#4A2E1B" stroke-width="2.5" stroke-linecap="round" />
                <circle cx="90" cy="20" r="10" fill="#F4C2C2" stroke="#C23B22" stroke-width="0.75" />
                <circle cx="90" cy="20" r="3" fill="#C23B22" />
                <circle cx="60" cy="80" r="12" fill="#E89CAE" stroke="#C23B22" stroke-width="0.75" />
                <circle cx="60" cy="80" r="3.5" fill="#8C1D0B" />
                <circle cx="20" cy="55" r="9" fill="#FFF0F5" stroke="#E89CAE" stroke-width="0.75" />
                <circle cx="115" cy="45" r="11" fill="#F4C2C2" stroke="#C23B22" stroke-width="0.75" />
                <path d="M85,15 Q75,5 70,12 T85,22 Z" fill="#8BA888" />
                <path d="M55,85 Q45,95 52,102 T62,88 Z" fill="#759772" />
            </svg>
        </div>

        <!-- ========================================================
             1. INTERACTIVE SHOJI SLIDING SCREEN (GATE COVER)
             ======================================================== -->
        <div
            v-if="isCoverVisible"
            class="fixed inset-0 z-50 flex items-center justify-center overflow-hidden bg-[#1A1715] transition-opacity duration-300"
            :class="{ 'pointer-events-none': isShojiOpening }"
        >
            <!-- Left Shoji Door Panel -->
            <div
                class="absolute inset-y-0 left-0 w-1/2 bg-[#F7F3E9] border-r-4 border-[#3D2817] shadow-2xl transition-transform duration-[1200ms] ease-[cubic-bezier(0.2,0.8,0.2,1)] flex flex-col justify-between p-6 overflow-hidden"
                :class="{ '-translate-x-full': isShojiOpening }"
            >
                <!-- Shoji Wooden Grid Pattern -->
                <div class="absolute inset-0 grid grid-cols-3 grid-rows-6 gap-2 p-4 pointer-events-none opacity-45">
                    <div v-for="i in 18" :key="'left-grid-' + i" class="border border-[#7A5A43] bg-white/40 shadow-inner"></div>
                </div>
                <!-- Japanese Kanji Crest Watermark -->
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-[14rem] font-serif text-[#C23B22]/5 select-none pointer-events-none font-bold">
                    結
                </div>
                <div class="relative z-10 flex items-center space-x-2 text-xs tracking-widest text-[#7A5A43] uppercase font-semibold">
                    <span>伝統と調和</span>
                </div>
                <!-- Japanese Ornate Door Pull (Hikite) -->
                <div class="absolute right-4 top-1/2 -translate-y-1/2 w-7 h-20 rounded-full border-2 border-[#D4AF37] bg-gradient-to-r from-[#2D2424] to-[#1A1715] shadow-lg flex items-center justify-center">
                    <div class="w-2.5 h-10 rounded-full bg-[#D4AF37]/50"></div>
                </div>
                <!-- Traditional Cloud Motif -->
                <div class="relative z-10 opacity-30">
                    <svg viewBox="0 0 100 20" class="w-24 fill-[#C23B22]">
                        <path d="M10,15 Q25,5 40,15 T70,15 T100,15 A5,5 0 0,1 95,20 L5,20 A5,5 0 0,1 10,15 Z" />
                    </svg>
                </div>
            </div>

            <!-- Right Shoji Door Panel -->
            <div
                class="absolute inset-y-0 right-0 w-1/2 bg-[#F7F3E9] border-l-4 border-[#3D2817] shadow-2xl transition-transform duration-[1200ms] ease-[cubic-bezier(0.2,0.8,0.2,1)] flex flex-col justify-between items-end p-6 overflow-hidden"
                :class="{ 'translate-x-full': isShojiOpening }"
            >
                <!-- Shoji Wooden Grid Pattern -->
                <div class="absolute inset-0 grid grid-cols-3 grid-rows-6 gap-2 p-4 pointer-events-none opacity-45">
                    <div v-for="i in 18" :key="'right-grid-' + i" class="border border-[#7A5A43] bg-white/40 shadow-inner"></div>
                </div>
                <!-- Japanese Kanji Crest Watermark -->
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-[14rem] font-serif text-[#C23B22]/5 select-none pointer-events-none font-bold">
                    寿
                </div>
                <div class="relative z-10 flex items-center space-x-2 text-xs tracking-widest text-[#7A5A43] uppercase font-semibold">
                    <span>ZEN HARMONY</span>
                </div>
                <!-- Japanese Ornate Door Pull (Hikite) -->
                <div class="absolute left-4 top-1/2 -translate-y-1/2 w-7 h-20 rounded-full border-2 border-[#D4AF37] bg-gradient-to-l from-[#2D2424] to-[#1A1715] shadow-lg flex items-center justify-center">
                    <div class="w-2.5 h-10 rounded-full bg-[#D4AF37]/50"></div>
                </div>
                <div class="relative z-10 opacity-30">
                    <svg viewBox="0 0 100 20" class="w-24 fill-[#C23B22]">
                        <path d="M10,15 Q25,5 40,15 T70,15 T100,15 A5,5 0 0,1 95,20 L5,20 A5,5 0 0,1 10,15 Z" />
                    </svg>
                </div>
            </div>

            <!-- Center Calligraphy Emblem & Door Opener Card -->
            <div
                class="relative z-20 max-w-sm w-full mx-4 p-8 rounded-2xl bg-white/95 backdrop-blur-md shadow-2xl border border-[#D4AF37]/50 text-center transition-all duration-700 transform"
                :class="{ 'scale-90 opacity-0': isShojiOpening }"
            >
                <!-- Hanging Chochin Lantern Accent with Pendulum Sway -->
                <div class="flex justify-center -mt-12 mb-3">
                    <div class="relative flex flex-col items-center animate-lantern-sway origin-top">
                        <div class="w-0.5 h-6 bg-[#D4AF37]"></div>
                        <div class="w-12 h-14 bg-gradient-to-b from-[#C23B22] to-[#8C1D0B] rounded-full border-2 border-[#D4AF37] shadow-lg flex items-center justify-center text-white text-xs font-bold shadow-orange-500/30 animate-pulse">
                            寿
                        </div>
                        <div class="w-0.5 h-3 bg-[#D4AF37]"></div>
                    </div>
                </div>

                <div class="text-[11px] tracking-[0.3em] uppercase text-[#8C6D4A] mb-2 font-medium">
                    The Wedding Invitation
                </div>

                <h1 class="text-3xl sm:text-4xl font-serif text-[#2D2424] font-semibold tracking-wide mb-1">
                    {{ invitationData.content.groomNickname || 'Kenji' }} &amp; {{ invitationData.content.brideNickname || 'Ayumi' }}
                </h1>
                
                <div class="flex items-center justify-center space-x-3 my-3">
                    <div class="h-[1px] w-12 bg-gradient-to-r from-transparent to-[#D4AF37]"></div>
                    <span class="text-xs text-[#C23B22] font-semibold tracking-widest">一期一会</span>
                    <div class="h-[1px] w-12 bg-gradient-to-l from-transparent to-[#D4AF37]"></div>
                </div>

                <p class="text-xs text-[#6B5E55] mb-6">
                    {{ safeFormatDisplayDate(invitationData.content.weddingDate) }}
                </p>

                <!-- Guest Recipient Tag -->
                <div class="bg-[#FBF8F3] border border-[#E5DAC8] rounded-xl p-3.5 mb-6 text-center shadow-sm">
                    <div class="text-[10px] uppercase tracking-wider text-[#8C6D4A] mb-0.5">Kepada Yth. Bapak/Ibu/Saudara/i:</div>
                    <div class="text-base font-semibold text-[#2D2424] truncate">
                        {{ props.guestName || 'Tamu Undangan Spesial' }}
                    </div>
                    <div class="text-[10px] text-stone-500 italic mt-0.5">*Mohon maaf apabila ada kesalahan penulisan nama/gelar</div>
                </div>

                <!-- Open Button with Golden Glow -->
                <button
                    @click="handleOpenInvitation"
                    class="group relative w-full inline-flex items-center justify-center px-6 py-3.5 rounded-full text-white bg-gradient-to-r from-[#C23B22] via-[#D9777F] to-[#C23B22] shadow-lg shadow-rose-900/25 hover:shadow-rose-900/45 hover:scale-[1.02] active:scale-95 transition-all duration-300 font-medium tracking-wider text-sm overflow-hidden"
                >
                    <span class="absolute inset-0 bg-white/20 transform -translate-x-full group-hover:translate-x-full transition-transform duration-700"></span>
                    <svg class="w-4 h-4 mr-2 text-amber-200 animate-spin-slow" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2L15 8L21 9L17 14L18 20L12 17L6 20L7 14L3 9L9 8L12 2Z" />
                    </svg>
                    <span>Buka Gerbang Sakura</span>
                </button>
            </div>
        </div>

        <!-- ========================================================
             2. MAIN INVITATION EXPERIENCE (TWO-PANE DESKTOP / FLUID MOBILE)
             ======================================================== -->
        <div class="min-h-screen flex flex-col lg:flex-row">
            
            <!-- LEFT FIXED STAGE: Torii Gate & Japanese Zen Garden (Desktop) -->
            <div
                class="hidden lg:flex lg:w-[45%] xl:w-[42%] fixed inset-y-0 left-0 z-20 flex-col items-center justify-between p-12 bg-gradient-to-b from-[#2A2320] via-[#1F1A17] to-[#151210] text-amber-50 select-none overflow-hidden"
                @wheel="handleStageWheel"
            >
                <!-- Background Mount Fuji Silhouette & Glowing Sunrise -->
                <div class="absolute inset-0 pointer-events-none overflow-hidden opacity-40">
                    <!-- Sunrise Sun Disc -->
                    <div class="absolute top-1/4 left-1/2 -translate-x-1/2 w-80 h-80 rounded-full bg-gradient-to-b from-[#E89CAE]/40 via-[#C23B22]/30 to-transparent blur-2xl"></div>
                    
                    <!-- Drifting Japanese Golden Clouds -->
                    <div class="absolute top-20 -left-20 w-64 opacity-50 animate-cloud-drift-1">
                        <svg viewBox="0 0 200 60" class="fill-[#D4AF37]/30">
                            <path d="M20,40 Q40,15 70,30 Q90,10 130,25 Q160,15 180,40 Q150,55 100,50 Q50,55 20,40 Z" />
                        </svg>
                    </div>
                    <div class="absolute top-44 -right-16 w-56 opacity-40 animate-cloud-drift-2">
                        <svg viewBox="0 0 200 60" class="fill-[#E89CAE]/25">
                            <path d="M20,40 Q40,15 70,30 Q90,10 130,25 Q160,15 180,40 Q150,55 100,50 Q50,55 20,40 Z" />
                        </svg>
                    </div>

                    <!-- Fuji Mountain Polygon -->
                    <svg viewBox="0 0 500 300" class="absolute bottom-0 w-full fill-[#100D0B] opacity-80">
                        <polygon points="0,300 180,140 240,110 260,110 320,140 500,300" />
                        <!-- Snow Cap on Fuji -->
                        <polygon points="210,125 240,110 260,110 290,125 275,135 260,130 250,140 240,130 225,138" fill="#F7F3E9" opacity="0.6" />
                    </svg>
                </div>

                <!-- Top Japanese Calligraphy & Crest -->
                <div class="relative z-10 text-center">
                    <div class="inline-block px-4 py-1 rounded-full border border-amber-300/30 bg-black/40 backdrop-blur-md text-[11px] uppercase tracking-[0.3em] text-amber-200">
                        Sakura Zen Harmony
                    </div>
                    <div class="mt-2 text-xs text-rose-200/80 tracking-widest font-light">
                        日本の美と永遠の愛
                    </div>
                </div>

                <!-- Center 3D Interactive Torii Shrine Gate with Couple Silhouette -->
                <div
                    class="relative z-10 w-full max-w-sm flex flex-col items-center transition-transform duration-300 ease-out"
                    :style="{
                        transform: `perspective(900px) rotateX(${targetRotateX}deg) rotateY(${targetRotateY}deg)`,
                    }"
                >
                    <!-- Hanging Left Chochin Lantern on Torii -->
                    <div class="absolute top-12 left-2 z-20 flex flex-col items-center animate-lantern-sway origin-top">
                        <div class="w-0.5 h-6 bg-[#D4AF37]"></div>
                        <div class="w-9 h-12 rounded-full bg-gradient-to-b from-[#C23B22] to-[#8C1D0B] border border-[#D4AF37] shadow-lg flex items-center justify-center text-white text-[10px] font-bold shadow-orange-500/40">
                            和
                        </div>
                    </div>

                    <!-- Hanging Right Chochin Lantern on Torii -->
                    <div class="absolute top-12 right-2 z-20 flex flex-col items-center animate-lantern-sway origin-top" style="animation-delay: -1.5s;">
                        <div class="w-0.5 h-6 bg-[#D4AF37]"></div>
                        <div class="w-9 h-12 rounded-full bg-gradient-to-b from-[#C23B22] to-[#8C1D0B] border border-[#D4AF37] shadow-lg flex items-center justify-center text-white text-[10px] font-bold shadow-orange-500/40">
                            愛
                        </div>
                    </div>

                    <!-- Torii Gate Vector SVG -->
                    <svg viewBox="0 0 300 240" class="w-full drop-shadow-2xl">
                        <!-- Upper Lintels (Kasagi & Shimaki) -->
                        <path d="M10,35 Q150,15 290,35 L285,45 Q150,30 15,45 Z" fill="#8C1D0B" stroke="#D4AF37" stroke-width="1.5" />
                        <path d="M30,48 L270,48 L265,58 L35,58 Z" fill="#2D2424" />
                        <!-- Central Plaque (Gakuzuka) -->
                        <rect x="140" y="58" width="20" height="28" fill="#1A1715" stroke="#D4AF37" stroke-width="1" />
                        <text x="150" y="78" fill="#D4AF37" font-size="14" text-anchor="middle" font-family="serif">縁</text>
                        <!-- Lower Lintel (Nuki) -->
                        <rect x="25" y="86" width="250" height="12" fill="#8C1D0B" />
                        <!-- Vertical Pillars (Hashira) -->
                        <polygon points="55,58 75,58 70,230 45,230" fill="#A82814" stroke="#D4AF37" stroke-width="1" />
                        <polygon points="225,58 245,58 255,230 230,230" fill="#A82814" stroke="#D4AF37" stroke-width="1" />
                        <!-- Pillar Bases (Nemaki) -->
                        <rect x="42" y="215" width="32" height="15" fill="#1A1715" rx="2" />
                        <rect x="226" y="215" width="32" height="15" fill="#1A1715" rx="2" />
                    </svg>

                    <!-- Couple In Torii Gate Arch -->
                    <div class="absolute inset-0 flex flex-col items-center justify-center pt-8">
                        <div class="w-32 h-32 rounded-full border-2 border-[#D4AF37]/60 overflow-hidden shadow-2xl relative group">
                            <img
                                :src="invitationData.content.coverPhotoUrl || 'https://images.unsplash.com/photo-1583939003579-730e3918a45a?auto=format&fit=crop&w=600&q=80'"
                                alt="Kenji & Ayumi"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
                            />
                            <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
                        </div>
                        <h2 class="text-2xl font-serif font-bold text-amber-100 tracking-wide mt-3">
                            {{ invitationData.content.groomNickname || 'Kenji' }} &amp; {{ invitationData.content.brideNickname || 'Ayumi' }}
                        </h2>
                        <div class="text-xs text-rose-300 font-light tracking-widest mt-0.5">
                            {{ safeFormatDisplayDate(invitationData.content.weddingDate) }}
                        </div>
                    </div>
                </div>

                <!-- Bottom Shrine Audio & Ceremony Info -->
                <div class="relative z-10 text-center w-full max-w-xs">
                    <div class="p-3 rounded-xl bg-white/5 border border-white/10 backdrop-blur-md">
                        <div class="text-[10px] text-amber-300/80 uppercase tracking-widest">Tempat Resepsi</div>
                        <div class="text-xs text-amber-100 font-medium truncate mt-0.5">
                            {{ props.events[0]?.venue_name || 'The Sakura Pavilion & Zen Garden' }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT SCROLLABLE INVITATION CONTENT (Desktop & Mobile) -->
            <div class="w-full lg:w-[55%] xl:w-[58%] lg:ml-auto relative z-20 min-h-screen pb-24">

                <!-- MOBILE HERO COVER (Shown only on small screens) -->
                <div class="lg:hidden relative min-h-[92vh] flex flex-col justify-between items-center text-center p-6 bg-gradient-to-b from-[#2A2320] via-[#1F1A17] to-[#151210] text-amber-50 overflow-hidden">
                    <!-- Sunrise background glow -->
                    <div class="absolute top-1/4 left-1/2 -translate-x-1/2 w-64 h-64 rounded-full bg-gradient-to-b from-[#E89CAE]/30 to-transparent blur-3xl pointer-events-none"></div>

                    <!-- Hanging Chochin Lanterns on Mobile Top -->
                    <div class="absolute top-4 left-6 z-10 animate-lantern-sway origin-top">
                        <div class="w-0.5 h-5 bg-[#D4AF37]"></div>
                        <div class="w-8 h-10 rounded-full bg-gradient-to-b from-[#C23B22] to-[#8C1D0B] border border-[#D4AF37] flex items-center justify-center text-white text-[9px] font-bold shadow-md">
                            寿
                        </div>
                    </div>
                    <div class="absolute top-4 right-6 z-10 animate-lantern-sway origin-top" style="animation-delay: -1.2s;">
                        <div class="w-0.5 h-5 bg-[#D4AF37]"></div>
                        <div class="w-8 h-10 rounded-full bg-gradient-to-b from-[#C23B22] to-[#8C1D0B] border border-[#D4AF37] flex items-center justify-center text-white text-[9px] font-bold shadow-md">
                            縁
                        </div>
                    </div>

                    <!-- Top Japanese Header -->
                    <div class="relative z-10 mt-6">
                        <span class="inline-block px-3.5 py-1 rounded-full border border-amber-300/30 bg-black/40 text-[10px] uppercase tracking-[0.25em] text-amber-200">
                            The Wedding Of
                        </span>
                    </div>

                    <!-- Center Photo Frame in Japanese Enso Window -->
                    <div class="relative z-10 flex flex-col items-center my-6">
                        <div class="relative w-56 h-56 rounded-full p-2 bg-gradient-to-tr from-[#D4AF37] via-[#E89CAE] to-[#C23B22] shadow-2xl">
                            <div class="w-full h-full rounded-full overflow-hidden border-2 border-white/60">
                                <img
                                    :src="invitationData.content.coverPhotoUrl || 'https://images.unsplash.com/photo-1583939003579-730e3918a45a?auto=format&fit=crop&w=600&q=80'"
                                    alt="Couple Cover"
                                    class="w-full h-full object-cover"
                                />
                            </div>
                            <!-- Floating Petal Accent -->
                            <div class="absolute -bottom-2 -right-2 w-10 h-10 bg-[#C23B22] rounded-full border-2 border-amber-200 flex items-center justify-center text-white text-xs font-bold shadow-lg animate-bounce">
                                寿
                            </div>
                        </div>

                        <h1 class="text-3xl font-serif font-bold text-amber-100 tracking-wider mt-5">
                            {{ invitationData.content.groomNickname || 'Kenji' }} &amp; {{ invitationData.content.brideNickname || 'Ayumi' }}
                        </h1>
                        <p class="text-xs text-rose-300 font-light tracking-widest mt-1">
                            {{ safeFormatDisplayDate(invitationData.content.weddingDate) }}
                        </p>
                    </div>

                    <!-- Scroll Down Cue -->
                    <div class="relative z-10 mb-4 flex flex-col items-center text-amber-200/60 animate-bounce">
                        <span class="text-[10px] tracking-widest uppercase mb-1">Gulir ke bawah</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                        </svg>
                    </div>
                </div>

                <!-- ====================================================
                     SECTION 1: JAPANESE BLESSING & BAMBOO FOUNTAIN (QUOTE)
                     ==================================================== -->
                <section class="px-6 py-16 max-w-xl mx-auto text-center zen-fade-up relative">
                    <!-- Traditional Stylized Cloud Motif Background -->
                    <div class="absolute -top-6 right-4 w-32 opacity-25 pointer-events-none animate-cloud-drift-1">
                        <svg viewBox="0 0 200 60" class="fill-[#C23B22]">
                            <path d="M20,40 Q40,15 70,30 Q90,10 130,25 Q160,15 180,40 Q150,55 100,50 Q50,55 20,40 Z" />
                        </svg>
                    </div>

                    <div class="inline-flex items-center justify-center space-x-2 text-xs tracking-widest text-[#8C6D4A] uppercase mb-4">
                        <span class="w-8 h-[1px] bg-[#D4AF37]"></span>
                        <span>一期一会 • ICHIGO ICHIE</span>
                        <span class="w-8 h-[1px] bg-[#D4AF37]"></span>
                    </div>

                    <!-- Red Calligraphy Seal -->
                    <div class="w-10 h-10 mx-auto mb-4 border-2 border-[#C23B22] rounded-md flex items-center justify-center text-[#C23B22] font-bold text-sm bg-rose-50 shadow-sm">
                        愛
                    </div>

                    <blockquote class="text-base sm:text-lg italic font-serif text-[#4A3E39] leading-relaxed mb-4">
                        "Dan di antara tanda-tanda kebesaran-Nya ialah Dia menciptakan pasangan-pasangan untukmu dari jenismu sendiri, agar kamu cenderung dan merasa tenteram kepadanya, dan Dia menjadikan di antaramu rasa kasih dan sayang."
                    </blockquote>
                    <p class="text-xs font-semibold text-[#8C6D4A] tracking-wider uppercase">
                        (QS. Ar-Rum: 21)
                    </p>

                    <!-- ANIMATED JAPANESE BAMBOO FOUNTAIN (SHISHI-ODOSHI) -->
                    <div class="mt-8 flex flex-col items-center justify-center">
                        <div class="relative w-44 h-24">
                            <!-- Water Spout Spilling Water -->
                            <div class="absolute top-1 left-8 w-16 h-3 bg-[#698246] rounded-r-full rotate-12 shadow-sm border border-[#4E6233]"></div>
                            <div class="absolute top-6 left-20 w-0.5 h-8 bg-cyan-300/80 rounded-full animate-water-drip"></div>

                            <!-- Tilting Bamboo Rocker Arm -->
                            <div class="absolute top-8 left-14 w-24 h-4 bg-[#7D9A54] rounded-full border border-[#4E6233] origin-[40%_50%] animate-shishi-rock shadow-md flex items-center justify-end pr-2">
                                <div class="w-2 h-2 rounded-full bg-[#4E6233]"></div>
                            </div>

                            <!-- Supporting Bamboo Stand -->
                            <div class="absolute top-10 left-16 w-3 h-12 bg-[#5A402E] rounded-t-sm shadow"></div>
                            <div class="absolute top-10 left-22 w-3 h-12 bg-[#5A402E] rounded-t-sm shadow"></div>

                            <!-- Stone Basin (Tsukubai) with Ripple -->
                            <div class="absolute bottom-0 left-20 w-16 h-6 bg-[#6E6E6E] rounded-full shadow-inner border border-stone-600 flex items-center justify-center">
                                <div class="w-8 h-2 rounded-full bg-cyan-800/40 animate-ping"></div>
                            </div>
                        </div>
                        <span class="text-[9px] text-stone-500 uppercase tracking-widest mt-1">Shishi-odoshi • Suara Ketenangan Taman Zen</span>
                    </div>
                </section>

                <!-- ====================================================
                     SECTION 2: BRIDE & GROOM PROFILE (MEMPELAI)
                     ==================================================== -->
                <section class="px-6 py-12 max-w-xl mx-auto zen-fade-up">
                    <div class="text-center mb-10">
                        <span class="text-[10px] tracking-[0.3em] uppercase text-[#8C6D4A] font-semibold">Pasangan Mempelai</span>
                        <h2 class="text-2xl sm:text-3xl font-serif font-bold text-[#2D2424] mt-1">Groom &amp; Bride</h2>
                        <div class="w-12 h-0.5 bg-[#C23B22] mx-auto mt-2"></div>
                    </div>

                    <div class="space-y-8">
                        <!-- GROOM CARD (3D Tilt) -->
                        <div
                            class="relative p-6 sm:p-8 rounded-2xl bg-white/90 border border-[#E5DAC8] shadow-xl hover:shadow-2xl transition-all duration-300"
                            @mousemove="(e) => handleCardTilt(e, 'groom')"
                            @mouseleave="() => resetCardTilt('groom')"
                            :style="{
                                transform: `perspective(800px) rotateX(${cardGroomTilt.x}deg) rotateY(${cardGroomTilt.y}deg)`,
                            }"
                        >
                            <div class="flex flex-col sm:flex-row items-center sm:items-start space-y-4 sm:space-y-0 sm:space-x-6 text-center sm:text-left">
                                <!-- Groom Photo inside Enso Window -->
                                <div class="relative w-28 h-28 sm:w-32 sm:h-32 rounded-full p-1.5 bg-gradient-to-tr from-[#3D2817] via-[#D4AF37] to-[#8C1D0B] shadow-md flex-shrink-0">
                                    <img
                                        :src="invitationData.content.groomPhotoUrl || 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?auto=format&fit=crop&w=400&q=80'"
                                        alt="Groom Photo"
                                        class="w-full h-full object-cover rounded-full"
                                    />
                                    <div class="absolute bottom-0 right-0 bg-[#3D2817] text-[#D4AF37] text-[10px] px-2 py-0.5 rounded-full border border-[#D4AF37]">
                                        新郎
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-xl font-serif font-bold text-[#2D2424]">
                                        {{ invitationData.content.groomName || 'Kenji Pratama, S.T.' }}
                                    </h3>
                                    <p class="text-xs text-[#8C6D4A] font-medium tracking-wide mt-1">
                                        Putra Pertama dari:
                                    </p>
                                    <p class="text-xs text-[#5C4D44] mt-0.5 leading-relaxed">
                                        {{ invitationData.content.groomParents || 'Bpk. Ir. Bambang Pratama & Ibu Etsuko Tanaka' }}
                                    </p>
                                    <div v-if="invitationData.content.groomInstagram" class="mt-3">
                                        <a
                                            :href="'https://instagram.com/' + invitationData.content.groomInstagram.replace('@', '')"
                                            target="_blank"
                                            class="inline-flex items-center space-x-1.5 text-xs text-[#C23B22] hover:text-[#8C1D0B] font-medium"
                                        >
                                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24">
                                                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                                            </svg>
                                            <span>{{ invitationData.content.groomInstagram }}</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- BRIDE CARD (3D Tilt) -->
                        <div
                            class="relative p-6 sm:p-8 rounded-2xl bg-white/90 border border-[#E5DAC8] shadow-xl hover:shadow-2xl transition-all duration-300"
                            @mousemove="(e) => handleCardTilt(e, 'bride')"
                            @mouseleave="() => resetCardTilt('bride')"
                            :style="{
                                transform: `perspective(800px) rotateX(${cardBrideTilt.x}deg) rotateY(${cardBrideTilt.y}deg)`,
                            }"
                        >
                            <div class="flex flex-col sm:flex-row items-center sm:items-start space-y-4 sm:space-y-0 sm:space-x-6 text-center sm:text-left">
                                <!-- Bride Photo inside Enso Window -->
                                <div class="relative w-28 h-28 sm:w-32 sm:h-32 rounded-full p-1.5 bg-gradient-to-tr from-[#D4AF37] via-[#E89CAE] to-[#C23B22] shadow-md flex-shrink-0">
                                    <img
                                        :src="invitationData.content.bridePhotoUrl || 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=400&q=80'"
                                        alt="Bride Photo"
                                        class="w-full h-full object-cover rounded-full"
                                    />
                                    <div class="absolute bottom-0 right-0 bg-[#C23B22] text-amber-200 text-[10px] px-2 py-0.5 rounded-full border border-amber-200">
                                        新婦
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-xl font-serif font-bold text-[#2D2424]">
                                        {{ invitationData.content.brideName || 'Ayumi Larasati, B.Des.' }}
                                    </h3>
                                    <p class="text-xs text-[#8C6D4A] font-medium tracking-wide mt-1">
                                        Putri Bungsu dari:
                                    </p>
                                    <p class="text-xs text-[#5C4D44] mt-0.5 leading-relaxed">
                                        {{ invitationData.content.brideParents || 'Bpk. Hendra Gunawan & Ibu Rini Larasati' }}
                                    </p>
                                    <div v-if="invitationData.content.brideInstagram" class="mt-3">
                                        <a
                                            :href="'https://instagram.com/' + invitationData.content.brideInstagram.replace('@', '')"
                                            target="_blank"
                                            class="inline-flex items-center space-x-1.5 text-xs text-[#C23B22] hover:text-[#8C1D0B] font-medium"
                                        >
                                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24">
                                                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                                            </svg>
                                            <span>{{ invitationData.content.brideInstagram }}</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- ====================================================
                     SECTION 3: INTERACTIVE ZEN KOI POND (SEPANGSAN KOI)
                     ==================================================== -->
                <section class="px-6 py-12 max-w-xl mx-auto zen-fade-up">
                    <div class="text-center mb-6">
                        <span class="text-[10px] tracking-[0.3em] uppercase text-[#8C6D4A] font-semibold">Taman Harmoni Zen</span>
                        <h3 class="text-2xl font-serif font-bold text-[#2D2424] mt-1">Sepasang Ikan Koi Abadi</h3>
                        <p class="text-xs text-stone-600 mt-1">Sentuh kolam air untuk menciptakan riak ketenangan</p>
                    </div>

                    <!-- Interactive Koi Pond Card -->
                    <div
                        @click="triggerPondRipple"
                        class="relative h-64 sm:h-72 rounded-3xl bg-gradient-to-b from-[#1C2C28] via-[#142320] to-[#0D1816] border-2 border-[#D4AF37]/50 shadow-2xl overflow-hidden cursor-pointer select-none group"
                    >
                        <!-- Translucent Pond Waterbed & River Pebbles -->
                        <div class="absolute inset-0 bg-radial from-transparent to-black/60 pointer-events-none"></div>

                        <!-- Smooth Water Lilies / Lotus Leaves -->
                        <div class="absolute top-6 left-8 w-14 h-14 rounded-full bg-[#2E4A3B] border border-[#48735C] opacity-75 shadow-md transform rotate-45"></div>
                        <div class="absolute bottom-8 right-10 w-16 h-16 rounded-full bg-[#2E4A3B] border border-[#48735C] opacity-75 shadow-md transform -rotate-12"></div>
                        <div class="absolute top-1/2 right-6 w-10 h-10 rounded-full bg-[#243B2F] border border-[#3A5F4B] opacity-60"></div>

                        <!-- Animated Concentric Water Ripples from Click / Auto -->
                        <div
                            v-for="r in pondRipples"
                            :key="r.id"
                            class="absolute pointer-events-none rounded-full border border-cyan-300/60 animate-pond-ripple"
                            :style="{ left: r.x + 'px', top: r.y + 'px', transform: 'translate(-50%, -50%)' }"
                        ></div>

                        <!-- 1. KOHAKU KOI (Crimson & White Swimming in Circle) -->
                        <div class="absolute inset-0 pointer-events-none flex items-center justify-center animate-koi-swim-orbit-1">
                            <div class="relative w-20 h-36 flex flex-col items-center">
                                <!-- Head & Body SVG -->
                                <svg viewBox="0 0 60 120" class="w-full h-full drop-shadow-xl">
                                    <!-- Pectoral Fins (Left & Right Flapping) -->
                                    <path d="M12,45 Q-10,35 2,25 Q12,35 15,45 Z" fill="#FBF8F3" opacity="0.8" class="animate-koi-fin-left origin-[15px_45px]" />
                                    <path d="M48,45 Q70,35 58,25 Q48,35 45,45 Z" fill="#FBF8F3" opacity="0.8" class="animate-koi-fin-right origin-[45px_45px]" />
                                    
                                    <!-- Main Body -->
                                    <ellipse cx="30" cy="50" rx="14" ry="32" fill="#FBF8F3" />
                                    <!-- Crimson Kohaku Patches -->
                                    <path d="M22,35 Q30,22 38,36 Q32,48 22,35 Z" fill="#C23B22" />
                                    <path d="M24,56 Q36,52 35,68 Q24,70 24,56 Z" fill="#C23B22" />
                                    <!-- Whisker Barbels -->
                                    <path d="M27,18 Q20,12 18,8" stroke="#FBF8F3" stroke-width="1.2" fill="none" />
                                    <path d="M33,18 Q40,12 42,8" stroke="#FBF8F3" stroke-width="1.2" fill="none" />
                                    <!-- Undulating Caudal Tail Fin -->
                                    <g class="animate-koi-tail origin-[30px_82px]">
                                        <path d="M30,82 Q20,105 10,118 Q30,110 50,118 Q40,105 30,82 Z" fill="#F4C2C2" opacity="0.85" />
                                        <path d="M28,85 Q30,102 32,85" stroke="#C23B22" stroke-width="1.5" />
                                    </g>
                                </svg>
                            </div>
                        </div>

                        <!-- 2. YAMABUKI KOI (Golden Yellow Koi Swimming Reverse Path) -->
                        <div class="absolute inset-0 pointer-events-none flex items-center justify-center animate-koi-swim-orbit-2">
                            <div class="relative w-18 h-32 flex flex-col items-center">
                                <svg viewBox="0 0 60 120" class="w-full h-full drop-shadow-xl">
                                    <path d="M12,45 Q-8,35 2,25 Q12,35 15,45 Z" fill="#F3D17C" opacity="0.85" class="animate-koi-fin-left origin-[15px_45px]" />
                                    <path d="M48,45 Q68,35 58,25 Q48,35 45,45 Z" fill="#F3D17C" opacity="0.85" class="animate-koi-fin-right origin-[45px_45px]" />
                                    <ellipse cx="30" cy="50" rx="13" ry="30" fill="#E5A93C" />
                                    <path d="M22,35 Q30,24 38,36 Q32,46 22,35 Z" fill="#FEEAA3" />
                                    <path d="M27,20 Q22,14 20,10" stroke="#FEEAA3" stroke-width="1" fill="none" />
                                    <path d="M33,20 Q38,14 40,10" stroke="#FEEAA3" stroke-width="1" fill="none" />
                                    <g class="animate-koi-tail origin-[30px_80px]">
                                        <path d="M30,80 Q20,102 12,114 Q30,106 48,114 Q40,102 30,80 Z" fill="#F5C765" opacity="0.85" />
                                    </g>
                                </svg>
                            </div>
                        </div>

                        <!-- Center Japanese Symbol Floating in Deep Water -->
                        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                            <span class="text-white/10 text-8xl font-serif select-none font-bold">和</span>
                        </div>
                    </div>
                </section>

                <!-- ====================================================
                     SECTION 4: ZEN COUNTDOWN TIMER (SUNDIAL CLOCK)
                     ==================================================== -->
                <section class="px-6 py-12 max-w-xl mx-auto text-center zen-fade-up">
                    <div class="p-8 rounded-2xl bg-[#2D2424] text-white shadow-2xl relative overflow-hidden border border-[#D4AF37]/40">
                        <!-- Floating Hanging Furin Windchime in Corner -->
                        <div class="absolute top-2 right-4 flex flex-col items-center animate-furin-sway origin-top pointer-events-none">
                            <div class="w-0.5 h-6 bg-[#D4AF37]"></div>
                            <div class="w-6 h-5 rounded-t-full bg-cyan-200/50 border border-white/60 shadow-sm"></div>
                            <div class="w-0.5 h-3 bg-amber-200"></div>
                            <div class="w-3 h-8 bg-rose-200 text-[#8C1D0B] text-[6px] font-bold writing-mode-vertical flex items-center justify-center shadow-sm">
                                祈
                            </div>
                        </div>

                        <div class="text-[10px] tracking-[0.3em] uppercase text-amber-200 mb-1">
                            Hitung Mundur Waktu Bahagia
                        </div>
                        <h3 class="text-xl font-serif text-white font-semibold mb-6">
                            Menuju Ikatan Suci
                        </h3>

                        <!-- Countdown 4-Grid Boxes -->
                        <div class="grid grid-cols-4 gap-2 sm:gap-4 max-w-md mx-auto mb-6">
                            <div class="p-3 sm:p-4 rounded-xl bg-white/10 border border-white/15 backdrop-blur-sm">
                                <div class="text-2xl sm:text-3xl font-bold text-amber-300 font-mono">
                                    {{ String(props.timeLeft.days).padStart(2, '0') }}
                                </div>
                                <div class="text-[10px] uppercase tracking-wider text-rose-200 mt-1">Hari</div>
                            </div>
                            <div class="p-3 sm:p-4 rounded-xl bg-white/10 border border-white/15 backdrop-blur-sm">
                                <div class="text-2xl sm:text-3xl font-bold text-amber-300 font-mono">
                                    {{ String(props.timeLeft.hours).padStart(2, '0') }}
                                </div>
                                <div class="text-[10px] uppercase tracking-wider text-rose-200 mt-1">Jam</div>
                            </div>
                            <div class="p-3 sm:p-4 rounded-xl bg-white/10 border border-white/15 backdrop-blur-sm">
                                <div class="text-2xl sm:text-3xl font-bold text-amber-300 font-mono">
                                    {{ String(props.timeLeft.minutes).padStart(2, '0') }}
                                </div>
                                <div class="text-[10px] uppercase tracking-wider text-rose-200 mt-1">Menit</div>
                            </div>
                            <div class="p-3 sm:p-4 rounded-xl bg-white/10 border border-white/15 backdrop-blur-sm">
                                <div class="text-2xl sm:text-3xl font-bold text-amber-300 font-mono">
                                    {{ String(props.timeLeft.seconds).padStart(2, '0') }}
                                </div>
                                <div class="text-[10px] uppercase tracking-wider text-rose-200 mt-1">Detik</div>
                            </div>
                        </div>

                        <!-- Add to Google Calendar Button -->
                        <a
                            :href="googleCalendarUrl"
                            target="_blank"
                            class="inline-flex items-center space-x-2 px-5 py-2.5 rounded-full bg-gradient-to-r from-[#D4AF37] to-[#AA771C] text-stone-900 font-medium text-xs tracking-wider uppercase hover:shadow-lg hover:scale-105 transition-all"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span>Simpan ke Google Calendar</span>
                        </a>
                    </div>
                </section>

                <!-- ====================================================
                     SECTION 5: CEREMONY & RECEPTION (AGENDA ACARA)
                     ==================================================== -->
                <section class="px-6 py-12 max-w-xl mx-auto zen-fade-up">
                    <div class="text-center mb-10">
                        <span class="text-[10px] tracking-[0.3em] uppercase text-[#8C6D4A] font-semibold">Rangkaian Acara</span>
                        <h2 class="text-2xl sm:text-3xl font-serif font-bold text-[#2D2424] mt-1">Waktu &amp; Lokasi</h2>
                        <div class="w-12 h-0.5 bg-[#C23B22] mx-auto mt-2"></div>
                    </div>

                    <div class="space-y-6">
                        <div
                            v-for="(event, idx) in props.events"
                            :key="event.id"
                            class="relative p-6 sm:p-8 rounded-2xl bg-white/95 border border-[#E5DAC8] shadow-lg hover:shadow-xl transition-shadow"
                        >
                            <!-- Hanging Silk Bell Accent -->
                            <div class="absolute -top-3 left-8 px-3 py-1 rounded-full bg-[#C23B22] text-amber-200 text-[10px] uppercase font-bold tracking-wider shadow-md">
                                {{ event.name || (idx === 0 ? 'Akad Nikah / Pemberkatan' : 'Resepsi Pernikahan') }}
                            </div>

                            <div class="mt-2 space-y-4">
                                <!-- Date & Time -->
                                <div class="flex items-start space-x-3 text-stone-700">
                                    <div class="p-2 rounded-lg bg-rose-50 text-[#C23B22] mt-0.5">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="text-xs font-semibold text-[#8C6D4A] uppercase tracking-wider">Waktu &amp; Tanggal</div>
                                        <div class="text-sm font-serif font-bold text-[#2D2424]">
                                            {{ safeFormatDisplayDate(event.starts_at) }}
                                        </div>
                                        <div class="text-xs text-stone-600 mt-0.5">
                                            {{ safeFormatEventTime(event.starts_at, event.ends_at, event.timezone) }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Venue Location -->
                                <div class="flex items-start space-x-3 text-stone-700">
                                    <div class="p-2 rounded-lg bg-rose-50 text-[#C23B22] mt-0.5">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <div class="text-xs font-semibold text-[#8C6D4A] uppercase tracking-wider">Tempat Acara</div>
                                        <div class="text-sm font-serif font-bold text-[#2D2424]">
                                            {{ event.venue_name }}
                                        </div>
                                        <p class="text-xs text-stone-600 mt-0.5 leading-relaxed">
                                            {{ event.address || 'Jl. Taman Bunga Sakura No. 88, Kebayoran Baru, Jakarta Selatan' }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Action Buttons: Copy Address & Google Maps -->
                                <div class="pt-2 flex flex-wrap gap-2">
                                    <button
                                        v-if="event.address"
                                        @click="props.copyAddress(event.address)"
                                        class="inline-flex items-center space-x-1.5 px-3.5 py-1.5 rounded-lg border border-[#D4AF37]/80 text-[#8C6D4A] hover:bg-[#FBF8F3] text-xs font-medium transition-colors"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                        </svg>
                                        <span>Salin Alamat</span>
                                    </button>

                                    <a
                                        v-if="event.maps_url"
                                        :href="event.maps_url"
                                        target="_blank"
                                        class="inline-flex items-center space-x-1.5 px-3.5 py-1.5 rounded-lg bg-[#C23B22] text-white hover:bg-[#8C1D0B] text-xs font-medium transition-colors shadow-sm"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                        </svg>
                                        <span>Buka Google Maps</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- ====================================================
                     SECTION 6: LOVE STORY TIMELINE (TOBI-ISHI STEPPING STONES)
                     ==================================================== -->
                <section
                    v-if="invitationData.content.story && invitationData.content.story.length > 0"
                    class="px-6 py-12 max-w-xl mx-auto zen-fade-up"
                >
                    <div class="text-center mb-10">
                        <span class="text-[10px] tracking-[0.3em] uppercase text-[#8C6D4A] font-semibold">Kisah Kasih</span>
                        <h2 class="text-2xl sm:text-3xl font-serif font-bold text-[#2D2424] mt-1">Our Journey</h2>
                        <div class="w-12 h-0.5 bg-[#C23B22] mx-auto mt-2"></div>
                    </div>

                    <div class="relative pl-6 border-l-2 border-[#D4AF37]/50 space-y-8 ml-4">
                        <div
                            v-for="(st, sIdx) in invitationData.content.story"
                            :key="sIdx"
                            class="relative group"
                        >
                            <!-- Stepping Stone Dot -->
                            <div class="absolute -left-[31px] top-1 w-4 h-4 rounded-full bg-[#C23B22] border-2 border-white shadow-md group-hover:scale-125 transition-transform"></div>

                            <div class="p-5 rounded-xl bg-white/90 border border-[#E5DAC8] shadow-md group-hover:shadow-lg transition-shadow">
                                <span class="inline-block px-2.5 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-[#C23B22] border border-rose-200 uppercase tracking-wider mb-2">
                                    {{ st.year || st.date || 'Kenangan' }}
                                </span>
                                <h4 class="text-base font-serif font-bold text-[#2D2424] mb-1">
                                    {{ st.title }}
                                </h4>
                                <p class="text-xs text-stone-600 leading-relaxed">
                                    {{ st.description }}
                                </p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- ====================================================
                     SECTION 7: PHOTO GALLERY (KENANGAN INDAH)
                     ==================================================== -->
                <section
                    v-if="invitationData.content.gallery && invitationData.content.gallery.length > 0"
                    class="px-6 py-12 max-w-xl mx-auto zen-fade-up"
                >
                    <div class="text-center mb-10">
                        <span class="text-[10px] tracking-[0.3em] uppercase text-[#8C6D4A] font-semibold">Galeri Foto</span>
                        <h2 class="text-2xl sm:text-3xl font-serif font-bold text-[#2D2424] mt-1">Our Memories</h2>
                        <div class="w-12 h-0.5 bg-[#C23B22] mx-auto mt-2"></div>
                    </div>

                    <!-- Responsive Gallery Grid -->
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        <div
                            v-for="(photo, pIdx) in invitationData.content.gallery"
                            :key="pIdx"
                            @click="openLightbox(photo)"
                            class="group relative aspect-square rounded-xl overflow-hidden cursor-pointer shadow-md border-2 border-white hover:border-[#D4AF37] transition-all hover:scale-[1.03]"
                        >
                            <img
                                :src="photo"
                                :alt="'Gallery photo ' + (pIdx + 1)"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
                            />
                            <div class="absolute inset-0 bg-black/30 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- ====================================================
                     SECTION 8: WEDDING GIFT (SHUGI-BUKURO ENVELOPE)
                     ==================================================== -->
                <section class="px-6 py-12 max-w-xl mx-auto zen-fade-up">
                    <div class="text-center mb-10">
                        <span class="text-[10px] tracking-[0.3em] uppercase text-[#8C6D4A] font-semibold">Tanda Kasih</span>
                        <h2 class="text-2xl sm:text-3xl font-serif font-bold text-[#2D2424] mt-1">Wedding Gift</h2>
                        <div class="w-12 h-0.5 bg-[#C23B22] mx-auto mt-2"></div>
                        <p class="text-xs text-stone-600 mt-2 max-w-md mx-auto">
                            Doa restu Anda merupakan karunia terindah bagi kami. Namun jika ingin memberikan tanda kasih, Anda dapat menyalurkannya melalui:
                        </p>
                    </div>

                    <!-- Japanese Shugi-Bukuro Envelope Styling -->
                    <div class="p-6 sm:p-8 rounded-2xl bg-[#F7F3E9] border-2 border-[#D4AF37] shadow-xl relative overflow-hidden">
                        <!-- Mizuhiki Knot Ornament SVG -->
                        <div class="flex justify-center -mt-2 mb-4">
                            <svg viewBox="0 0 200 40" class="w-48 h-10">
                                <path d="M10,20 Q60,5 100,20 T190,20" fill="none" stroke="#C23B22" stroke-width="2.5" />
                                <path d="M10,24 Q60,9 100,24 T190,24" fill="none" stroke="#D4AF37" stroke-width="2" />
                                <!-- Center Bow Knot -->
                                <circle cx="100" cy="22" r="8" fill="#C23B22" stroke="#D4AF37" stroke-width="1.5" />
                            </svg>
                        </div>

                        <!-- Bank Accounts List -->
                        <div class="space-y-4">
                            <div
                                v-for="(bank, bIdx) in digitalGift.accounts"
                                :key="bIdx"
                                class="p-4 rounded-xl bg-white border border-[#E5DAC8] shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3"
                            >
                                <div>
                                    <div class="text-xs font-bold text-[#C23B22] uppercase tracking-wider">
                                        {{ bank.bank_name || bank.name || 'Bank Transfer' }}
                                    </div>
                                    <div class="text-base font-mono font-bold text-stone-800 tracking-wider mt-0.5">
                                        {{ bank.account_number || bank.number }}
                                    </div>
                                    <div class="text-xs text-stone-600">
                                        a.n. {{ bank.account_holder || bank.holder }}
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <button
                                        @click="copyBankNumber(bank.account_number || bank.number, bIdx)"
                                        class="px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-[#2D2424] text-amber-200 hover:bg-black transition-colors flex items-center space-x-1"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path>
                                        </svg>
                                        <span>{{ copiedBankIndex === bIdx ? 'Tersalin!' : 'Salin No. Rek' }}</span>
                                    </button>
                                    <button
                                        v-if="bank.qris_image_url || digitalGift.qris_url"
                                        @click="props.openQris(bank)"
                                        class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-rose-50 text-[#C23B22] border border-rose-200 hover:bg-rose-100 transition-colors"
                                    >
                                        QRIS
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- ====================================================
                     SECTION 9: RSVP & TANZAKU WISHES (BUKU TAMU)
                     ==================================================== -->
                <section class="px-6 py-12 max-w-xl mx-auto zen-fade-up">
                    <div class="text-center mb-10">
                        <span class="text-[10px] tracking-[0.3em] uppercase text-[#8C6D4A] font-semibold">Konfirmasi Kehadiran</span>
                        <h2 class="text-2xl sm:text-3xl font-serif font-bold text-[#2D2424] mt-1">RSVP &amp; Ucapan</h2>
                        <div class="w-12 h-0.5 bg-[#C23B22] mx-auto mt-2"></div>
                    </div>

                    <!-- RSVP FORM -->
                    <div class="p-6 sm:p-8 rounded-2xl bg-white border border-[#E5DAC8] shadow-xl mb-8">
                        <form @submit.prevent="props.submitRsvp" class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-[#8C6D4A] uppercase tracking-wider mb-1">
                                    Nama Lengkap
                                </label>
                                <input
                                    v-model="props.rsvpForm.guest_name"
                                    type="text"
                                    required
                                    placeholder="Masukkan nama Anda"
                                    class="w-full px-4 py-2.5 text-sm rounded-xl border border-stone-300 focus:ring-2 focus:ring-[#C23B22] focus:border-transparent outline-none transition-all"
                                />
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-[#8C6D4A] uppercase tracking-wider mb-1">
                                    Konfirmasi Kehadiran
                                </label>
                                <select
                                    v-model="props.rsvpForm.attendance_status"
                                    class="w-full px-4 py-2.5 text-sm rounded-xl border border-stone-300 focus:ring-2 focus:ring-[#C23B22] focus:border-transparent outline-none bg-white transition-all"
                                >
                                    <option value="attending">Hadir (出席)</option>
                                    <option value="not_attending">Tidak Hadir (欠席)</option>
                                    <option value="uncertain">Masih Ragu-ragu</option>
                                </select>
                            </div>

                            <div v-if="props.rsvpForm.attendance_status === 'attending'">
                                <label class="block text-xs font-semibold text-[#8C6D4A] uppercase tracking-wider mb-1">
                                    Jumlah Tamu yang Hadir
                                </label>
                                <select
                                    v-model="props.rsvpForm.guest_count"
                                    class="w-full px-4 py-2.5 text-sm rounded-xl border border-stone-300 focus:ring-2 focus:ring-[#C23B22] focus:border-transparent outline-none bg-white transition-all"
                                >
                                    <option :value="1">1 Orang</option>
                                    <option :value="2">2 Orang</option>
                                    <option :value="3">3 Orang</option>
                                    <option :value="4">4 Orang</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-[#8C6D4A] uppercase tracking-wider mb-1">
                                    Doa &amp; Ucapan Selamat
                                </label>
                                <textarea
                                    v-model="props.rsvpForm.message"
                                    rows="3"
                                    required
                                    placeholder="Tuliskan ucapan dan doa restu terbaik untuk kedua mempelai..."
                                    class="w-full px-4 py-2.5 text-sm rounded-xl border border-stone-300 focus:ring-2 focus:ring-[#C23B22] focus:border-transparent outline-none transition-all"
                                ></textarea>
                            </div>

                            <!-- Success / Error messages -->
                            <div v-if="props.rsvpSuccess" class="p-3 rounded-lg bg-emerald-50 text-emerald-700 text-xs font-medium">
                                Terima kasih atas konfirmasi kehadiran dan doa restu Anda!
                            </div>
                            <div v-if="props.rsvpErrorMessage" class="p-3 rounded-lg bg-rose-50 text-rose-700 text-xs font-medium">
                                {{ props.rsvpErrorMessage }}
                            </div>

                            <button
                                type="submit"
                                :disabled="props.rsvpSubmitting"
                                class="w-full py-3 rounded-xl bg-gradient-to-r from-[#C23B22] to-[#8C1D0B] text-white text-sm font-semibold tracking-wider uppercase hover:shadow-lg hover:brightness-110 active:scale-98 transition-all disabled:opacity-50"
                            >
                                <span v-if="props.rsvpSubmitting">Mengirimkan...</span>
                                <span v-else>Kirim Konfirmasi &amp; Doa</span>
                            </button>
                        </form>
                    </div>

                    <!-- GUESTBOOK WISH FEED (Tanzaku Strips) -->
                    <div class="space-y-3">
                        <div class="text-xs font-semibold text-[#8C6D4A] uppercase tracking-widest text-center mb-3">
                            Doa &amp; Restu Sahabat ({{ props.guestbook.length }})
                        </div>

                        <div class="max-h-96 overflow-y-auto space-y-3 pr-1">
                            <div
                                v-for="(entry, gIdx) in props.guestbook"
                                :key="gIdx"
                                class="p-4 rounded-xl bg-white/80 border border-[#E5DAC8] shadow-sm relative pl-4 border-l-4 border-l-[#C23B22]"
                            >
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-xs font-serif font-bold text-[#2D2424]">{{ entry.guest_name }}</span>
                                    <span class="text-[10px] text-stone-400">{{ entry.created_at }}</span>
                                </div>
                                <p class="text-xs text-stone-600 leading-relaxed">{{ entry.message }}</p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- ====================================================
                     SECTION 10: FOOTER & CLOSING BLESSINGS
                     ==================================================== -->
                <footer class="px-6 pt-12 pb-24 text-center max-w-xl mx-auto zen-fade-up">
                    <div class="w-12 h-12 mx-auto mb-4 border-2 border-[#D4AF37] rounded-full flex items-center justify-center text-[#D4AF37] font-serif font-bold text-lg bg-[#2D2424]">
                        結
                    </div>

                    <h3 class="text-2xl font-serif font-bold text-[#2D2424] mb-2">
                        Arigato Gozaimasu
                    </h3>
                    <p class="text-xs text-stone-600 max-w-sm mx-auto leading-relaxed mb-6">
                        Merupakan kehormatan &amp; kebahagiaan yang tak terhingga bagi kami atas kehadiran dan doa restu Bapak/Ibu/Saudara/i sekalian.
                    </p>

                    <div class="text-sm font-serif font-bold text-[#C23B22] tracking-wider">
                        Kami yang berbahagia,
                    </div>
                    <div class="text-lg font-serif font-bold text-[#2D2424] mt-1">
                        {{ invitationData.content.groomNickname || 'Kenji' }} &amp; {{ invitationData.content.brideNickname || 'Ayumi' }}
                    </div>

                    <div class="mt-8 text-[10px] text-stone-400 tracking-widest uppercase">
                        Undangan.id • Crafted with Zen &amp; Love
                    </div>
                </footer>

            </div>
        </div>

        <!-- ========================================================
             LIGHTBOX MODAL FOR GALLERY PHOTOS
             ======================================================== -->
        <div
            v-if="activeLightboxImg"
            class="fixed inset-0 z-50 bg-black/90 flex items-center justify-center p-4 backdrop-blur-sm"
            @click="closeLightbox"
        >
            <div class="relative max-w-3xl max-h-[90vh]" @click.stop>
                <img :src="activeLightboxImg" alt="Enlarged photo" class="max-w-full max-h-[85vh] rounded-lg shadow-2xl object-contain border-2 border-white/20" />
                <button
                    @click="closeLightbox"
                    class="absolute -top-10 right-0 text-white/80 hover:text-white text-sm font-semibold tracking-wider flex items-center space-x-1"
                >
                    <span>Tutup</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- ========================================================
             FLOATING AUDIO TOGGLE BUTTON
             ======================================================== -->
        <button
            v-if="props.isOpen || isShojiOpening"
            @click="emit('toggleMusic')"
            class="fixed bottom-6 right-6 z-40 w-12 h-12 rounded-full bg-[#2D2424] text-amber-200 border-2 border-[#D4AF37] shadow-xl flex items-center justify-center hover:scale-110 active:scale-95 transition-all group"
            :title="props.isPlayingMusic ? 'Hentikan Musik' : 'Putar Musik'"
        >
            <span v-if="props.isPlayingMusic" class="flex items-end space-x-0.5 h-4">
                <span class="w-1 bg-amber-300 rounded animate-bounce" style="animation-duration: 0.6s"></span>
                <span class="w-1 bg-amber-300 rounded animate-bounce" style="animation-duration: 0.9s"></span>
                <span class="w-1 bg-amber-300 rounded animate-bounce" style="animation-duration: 0.7s"></span>
            </span>
            <svg v-else class="w-5 h-5 text-stone-400 group-hover:text-amber-200" fill="currentColor" viewBox="0 0 24 24">
                <path d="M16.5 12c0-1.77-1.02-3.29-2.5-4.03v2.21l2.45 2.45c.03-.2.05-.41.05-.63zm2.5 0c0 .94-.2 1.82-.54 2.64l1.51 1.51C20.63 14.91 21 13.5 21 12c0-4.28-2.99-7.86-7-8.77v2.06c2.89.86 5 3.54 5 6.71zM4.27 3L3 4.27l4.73 4.73H4v6h4l5 5v-6.73l4.25 4.25c-.67.52-1.42.93-2.25 1.18v2.06c1.38-.31 2.63-.95 3.69-1.81L19.73 21 21 19.73l-9-9L4.27 3zM12 4L9.91 6.09 12 8.18V4z"/>
            </svg>
        </button>

    </div>
</template>

<style scoped>
/* Slow Spin for Crest */
@keyframes spinSlow {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}
.animate-spin-slow {
    animation: spinSlow 12s linear infinite;
}

/* Swaying Sakura Branches in Top Corners */
@keyframes swayBranchLeft {
    0% { transform: rotate(0deg); }
    50% { transform: rotate(3deg); }
    100% { transform: rotate(-2deg); }
}
.animate-sway-left {
    animation: swayBranchLeft 6.5s ease-in-out infinite alternate;
}

@keyframes swayBranchRight {
    0% { transform: rotate(0deg); }
    50% { transform: rotate(-3deg); }
    100% { transform: rotate(2deg); }
}
.animate-sway-right {
    animation: swayBranchRight 7s ease-in-out infinite alternate;
}

/* Pendulum Lantern Swaying */
@keyframes lanternSway {
    0% { transform: rotate(-4deg); }
    50% { transform: rotate(4deg); }
    100% { transform: rotate(-4deg); }
}
.animate-lantern-sway {
    animation: lanternSway 3.6s ease-in-out infinite;
}

/* Japanese Windchime (Furin) Sway */
@keyframes furinSway {
    0% { transform: rotate(-5deg); }
    50% { transform: rotate(5deg); }
    100% { transform: rotate(-5deg); }
}
.animate-furin-sway {
    animation: furinSway 2.8s ease-in-out infinite;
}

/* Shishi-odoshi Bamboo Rocking Sequence */
@keyframes shishiRock {
    0%, 20% {
        transform: rotate(0deg);
    }
    55% {
        transform: rotate(22deg); /* Slowly filling with water */
    }
    60% {
        transform: rotate(26deg); /* Full weight water spill */
    }
    68% {
        transform: rotate(-6deg); /* Snapping back against stone with clack */
    }
    75%, 100% {
        transform: rotate(0deg); /* Resting position */
    }
}
.animate-shishi-rock {
    animation: shishiRock 4.2s cubic-bezier(0.4, 0, 0.2, 1) infinite;
}

/* Water Droplet Falling */
@keyframes waterDrip {
    0% {
        transform: scaleY(0.2) translateY(0);
        opacity: 0.3;
    }
    50% {
        transform: scaleY(1.4) translateY(6px);
        opacity: 0.9;
    }
    100% {
        transform: scaleY(0.4) translateY(14px);
        opacity: 0;
    }
}
.animate-water-drip {
    animation: waterDrip 1.2s ease-in infinite;
}

/* Drifting Stylized Golden Clouds */
@keyframes cloudDrift1 {
    0% { transform: translateX(-30px); }
    50% { transform: translateX(35px); }
    100% { transform: translateX(-30px); }
}
.animate-cloud-drift-1 {
    animation: cloudDrift1 14s ease-in-out infinite;
}

@keyframes cloudDrift2 {
    0% { transform: translateX(25px); }
    50% { transform: translateX(-30px); }
    100% { transform: translateX(25px); }
}
.animate-cloud-drift-2 {
    animation: cloudDrift2 16s ease-in-out infinite;
}

/* Animated Swimming Koi Fish Orbits */
@keyframes koiOrbit1 {
    0% {
        transform: rotate(0deg) translate(80px) rotate(90deg);
    }
    100% {
        transform: rotate(360deg) translate(80px) rotate(90deg);
    }
}
.animate-koi-swim-orbit-1 {
    animation: koiOrbit1 16s linear infinite;
}

@keyframes koiOrbit2 {
    0% {
        transform: rotate(180deg) translate(65px) rotate(-90deg);
    }
    100% {
        transform: rotate(-180deg) translate(65px) rotate(-90deg);
    }
}
.animate-koi-swim-orbit-2 {
    animation: koiOrbit2 19s linear infinite;
}

/* Koi Fin & Tail Flapping */
@keyframes koiTail {
    0%, 100% { transform: rotate(-14deg); }
    50% { transform: rotate(14deg); }
}
.animate-koi-tail {
    animation: koiTail 0.8s ease-in-out infinite;
}

@keyframes koiFinLeft {
    0%, 100% { transform: rotate(-10deg); }
    50% { transform: rotate(10deg); }
}
.animate-koi-fin-left {
    animation: koiFinLeft 1.1s ease-in-out infinite;
}

@keyframes koiFinRight {
    0%, 100% { transform: rotate(10deg); }
    50% { transform: rotate(-10deg); }
}
.animate-koi-fin-right {
    animation: koiFinRight 1.1s ease-in-out infinite;
}

/* Concentric Pond Water Ripples */
@keyframes pondRipple {
    0% {
        width: 10px;
        height: 10px;
        opacity: 0.9;
    }
    100% {
        width: 140px;
        height: 140px;
        opacity: 0;
    }
}
.animate-pond-ripple {
    animation: pondRipple 1.6s cubic-bezier(0, 0.2, 0.8, 1) forwards;
}
</style>

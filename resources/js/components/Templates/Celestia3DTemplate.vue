<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount, nextTick } from 'vue';
import { Head } from '@inertiajs/vue3';
import confetti from 'canvas-confetti';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import Lenis from 'lenis';

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
    return d || 'Sabtu, 28 November 2026';
};

// Canvas ref for 3D stardust & fireflies
const canvasRef = ref<HTMLCanvasElement | null>(null);
let animationFrameId: number | null = null;

// Smooth scroll instance
let lenisInstance: Lenis | null = null;

// 3D Parallax Mouse tilt state for Left Pane
const mouseX = ref(0);
const mouseY = ref(0);
const targetRotateX = ref(0);
const targetRotateY = ref(0);

const handleMouseMove = (e: MouseEvent) => {
    const { innerWidth, innerHeight } = window;
    const x = (e.clientX / innerWidth - 0.5) * 2;
    const y = (e.clientY / innerHeight - 0.5) * 2;
    targetRotateY.value = x * 15;
    targetRotateX.value = -y * 15;
};

// 3D Tilt for Interactive Hologram Cards
const cardGroomTilt = ref({ x: 0, y: 0, glareX: 50, glareY: 50 });
const cardBrideTilt = ref({ x: 0, y: 0, glareX: 50, glareY: 50 });

const handleCardTilt = (e: MouseEvent, target: 'groom' | 'bride') => {
    const card = e.currentTarget as HTMLElement;
    const rect = card.getBoundingClientRect();
    const x = (e.clientX - rect.left) / rect.width;
    const y = (e.clientY - rect.top) / rect.height;
    const rotX = (y - 0.5) * -22;
    const rotY = (x - 0.5) * 22;

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

// Copy bank account feedback
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
    const title = encodeURIComponent((props.invitationData?.content?.weddingTitle || 'Royal') + ' Wedding');
    const firstEvent = props.events?.[0];
    const location = encodeURIComponent((firstEvent?.venue_name || '') + ' - ' + (firstEvent?.address || ''));
    return `https://www.google.com/calendar/render?action=TEMPLATE&text=${title}&location=${location}`;
});

// Particle system setup
const initStardustCanvas = () => {
    const canvas = canvasRef.value;
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    if (!ctx) return;

    let width = (canvas.width = window.innerWidth);
    let height = (canvas.height = window.innerHeight);

    window.addEventListener('resize', () => {
        if (!canvasRef.value) return;
        width = canvasRef.value.width = window.innerWidth;
        height = canvasRef.value.height = window.innerHeight;
    });

    const particles: Array<{
        x: number;
        y: number;
        z: number;
        radius: number;
        color: string;
        vy: number;
        vx: number;
        alpha: number;
        alphaSpeed: number;
    }> = [];

    const colors = ['#E6CA65', '#F9E79F', '#6EE7B7', '#A7F3D0', '#FFFFFF'];

    for (let i = 0; i < 65; i++) {
        particles.push({
            x: Math.random() * width,
            y: Math.random() * height,
            z: Math.random() * 2 + 0.5,
            radius: Math.random() * 2.2 + 0.6,
            color: colors[Math.floor(Math.random() * colors.length)],
            vy: -(Math.random() * 0.4 + 0.15),
            vx: (Math.random() - 0.5) * 0.3,
            alpha: Math.random() * 0.7 + 0.2,
            alphaSpeed: (Math.random() - 0.5) * 0.015,
        });
    }

    const render = () => {
        ctx.clearRect(0, 0, width, height);

        for (let i = 0; i < particles.length; i++) {
            const p = particles[i];
            p.y += p.vy * p.z;
            p.x += p.vx * p.z;
            p.alpha += p.alphaSpeed;

            if (p.alpha <= 0.15 || p.alpha >= 0.85) {
                p.alphaSpeed = -p.alphaSpeed;
            }

            if (p.y < -10) p.y = height + 10;
            if (p.x < -10) p.x = width + 10;
            if (p.x > width + 10) p.x = -10;

            ctx.save();
            ctx.globalAlpha = Math.max(0, Math.min(1, p.alpha));
            ctx.fillStyle = p.color;
            ctx.shadowBlur = 10 * p.z;
            ctx.shadowColor = p.color;
            ctx.beginPath();
            ctx.arc(p.x, p.y, p.radius * p.z, 0, Math.PI * 2);
            ctx.fill();
            ctx.restore();
        }

        animationFrameId = requestAnimationFrame(render);
    };

    render();
};

// Initialize GSAP ScrollTrigger Animations
const initScrollAnimations = () => {
    // 1. Hero Couple 3D Prism Tilt Scrub
    const heroPrism = document.querySelector('.celestia-hero-prism');
    if (heroPrism) {
        gsap.fromTo(
            heroPrism,
            {
                rotateX: 18,
                rotateY: -8,
                scale: 0.9,
                y: 40,
                transformPerspective: 1200,
            },
            {
                rotateX: 0,
                rotateY: 0,
                scale: 1,
                y: 0,
                ease: 'power2.out',
                scrollTrigger: {
                    trigger: '.celestia-hero-section',
                    start: 'top 85%',
                    end: 'center 45%',
                    scrub: 1.2,
                },
            }
        );
    }

    // 2. 3D Unfolding Holy Verse Scroll
    const verseCard = document.querySelector('.celestia-verse-card');
    if (verseCard) {
        gsap.fromTo(
            verseCard,
            {
                rotateX: 32,
                scale: 0.9,
                opacity: 0.7,
                y: 60,
                transformPerspective: 1000,
            },
            {
                rotateX: 0,
                scale: 1,
                opacity: 1,
                y: 0,
                ease: 'power2.out',
                scrollTrigger: {
                    trigger: '.celestia-verse-section',
                    start: 'top 80%',
                    end: 'center 50%',
                    scrub: 1.2,
                },
            }
        );
    }

    // 3. Groom & Bride 3D Holographic Fly-In
    const groomCard = document.querySelector('.celestia-card-groom');
    const brideCard = document.querySelector('.celestia-card-bride');

    if (groomCard) {
        gsap.fromTo(
            groomCard,
            {
                x: -70,
                rotateY: -25,
                opacity: 0,
                transformPerspective: 1000,
            },
            {
                x: 0,
                rotateY: 0,
                opacity: 1,
                ease: 'power3.out',
                scrollTrigger: {
                    trigger: '.celestia-couple-section',
                    start: 'top 75%',
                    end: 'center 50%',
                    scrub: 1.3,
                },
            }
        );
    }

    if (brideCard) {
        gsap.fromTo(
            brideCard,
            {
                x: 70,
                rotateY: 25,
                opacity: 0,
                transformPerspective: 1000,
            },
            {
                x: 0,
                rotateY: 0,
                opacity: 1,
                ease: 'power3.out',
                scrollTrigger: {
                    trigger: '.celestia-couple-section',
                    start: 'top 75%',
                    end: 'center 50%',
                    scrub: 1.3,
                },
            }
        );
    }

    // 4. Chronometer 3D Orbit Boxes
    const chronoBoxes = document.querySelectorAll('.celestia-chrono-box');
    if (chronoBoxes.length > 0) {
        gsap.from(chronoBoxes, {
            y: 40,
            scale: 0.85,
            opacity: 0,
            stagger: 0.1,
            duration: 0.8,
            ease: 'back.out(1.7)',
            scrollTrigger: {
                trigger: '.celestia-countdown-section',
                start: 'top 80%',
            },
        });
    }

    // 5. Event Portals (Akad & Resepsi) 3D Elevation
    const eventCards = document.querySelectorAll('.celestia-event-card');
    eventCards.forEach((card, idx) => {
        gsap.fromTo(
            card,
            {
                rotateX: 20,
                y: 50,
                scale: 0.92,
                opacity: 0.4,
                transformPerspective: 1200,
            },
            {
                rotateX: 0,
                y: 0,
                scale: 1,
                opacity: 1,
                ease: 'power2.out',
                scrollTrigger: {
                    trigger: card,
                    start: 'top 85%',
                    end: 'center 55%',
                    scrub: 1,
                },
            }
        );
    });

    // 6. Gallery 3D Perspective Tilt Scrub
    const galleryItems = document.querySelectorAll('.celestia-gallery-item');
    galleryItems.forEach((item, idx) => {
        const isOdd = idx % 2 === 1;
        gsap.fromTo(
            item,
            {
                rotateY: isOdd ? 14 : -14,
                y: 40,
                scale: 0.94,
                opacity: 0.6,
                transformPerspective: 1000,
            },
            {
                rotateY: 0,
                y: 0,
                scale: 1,
                opacity: 1,
                ease: 'power2.out',
                scrollTrigger: {
                    trigger: item,
                    start: 'top 85%',
                    end: 'center 60%',
                    scrub: 1.2,
                },
            }
        );
    });

    // 7. Love Story Milestones
    const milestones = document.querySelectorAll('.celestia-milestone');
    milestones.forEach((m, idx) => {
        const isLeft = idx % 2 === 0;
        gsap.fromTo(
            m,
            {
                x: isLeft ? -50 : 50,
                opacity: 0,
                rotateY: isLeft ? -15 : 15,
                transformPerspective: 1000,
            },
            {
                x: 0,
                opacity: 1,
                rotateY: 0,
                ease: 'power3.out',
                scrollTrigger: {
                    trigger: m,
                    start: 'top 82%',
                    end: 'center 60%',
                    scrub: 1,
                },
            }
        );
    });

    // 8. 3D Bank Credit Cards
    const bankCards = document.querySelectorAll('.celestia-bank-card');
    bankCards.forEach((b) => {
        gsap.fromTo(
            b,
            {
                rotateX: 25,
                y: 50,
                scale: 0.9,
                opacity: 0.5,
                transformPerspective: 1000,
            },
            {
                rotateX: 0,
                y: 0,
                scale: 1,
                opacity: 1,
                ease: 'power2.out',
                scrollTrigger: {
                    trigger: b,
                    start: 'top 85%',
                    end: 'center 60%',
                    scrub: 1.2,
                },
            }
        );
    });
};

onMounted(() => {
    // 1. Initialize Stardust
    initStardustCanvas();

    // 2. Listen to mousemove for 3D parallax on Left Pane
    window.addEventListener('mousemove', handleMouseMove);

    // 3. Setup Lenis Smooth Scroll
    try {
        lenisInstance = new Lenis({
            duration: 1.2,
            easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
            touchMultiplier: 1.5,
        });

        lenisInstance.on('scroll', ScrollTrigger.update);

        gsap.ticker.add((time) => {
            lenisInstance?.raf(time * 1000);
        });

        gsap.ticker.lagSmoothing(0);
    } catch (e) {
        console.warn('Lenis smooth scroll initialized with fallback:', e);
    }

    // 4. Initialize GSAP ScrollTrigger after DOM is ready
    nextTick(() => {
        setTimeout(() => {
            initScrollAnimations();
        }, 300);
    });
});

onBeforeUnmount(() => {
    window.removeEventListener('mousemove', handleMouseMove);
    if (animationFrameId) cancelAnimationFrame(animationFrameId);
    if (lenisInstance) {
        lenisInstance.destroy();
        lenisInstance = null;
    }
    ScrollTrigger.getAll().forEach((t) => t.kill());
});

const handleOpenInvitation = () => {
    emit('openInvitation');
    confetti({
        particleCount: 80,
        spread: 90,
        origin: { y: 0.6 },
        colors: ['#E6CA65', '#D4AF37', '#6EE7B7', '#FFFFFF', '#0B1E17'],
    });

    setTimeout(() => {
        const el = document.getElementById('celestia-content-start');
        if (el) {
            el.scrollIntoView({ behavior: 'smooth' });
        }
    }, 200);
};

const scrollToSection = (id: string) => {
    const el = document.getElementById(id);
    if (el) {
        el.scrollIntoView({ behavior: 'smooth' });
    }
};
</script>

<template>
    <Head>
        <!-- Luxury Typography Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700;800;900&family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;0,700;1,400&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap">
    </Head>

    <!-- Canvas for Floating Stardust & Celestial Fireflies -->
    <canvas ref="canvasRef" class="celestia-stardust-canvas"></canvas>

    <div class="celestia-page-root selection:bg-amber-400 selection:text-stone-900">

        <!-- ======================================================== -->
        <!-- 1. DESKTOP PRIMARY PANE (Fixed Left 58% on Large Screens)-->
        <!-- ======================================================== -->
        <aside class="celestia-desktop-stage hidden lg:flex">
            <!-- 3D Gyroscope / Astrolabe System -->
            <div
                class="celestia-astrolabe-viewport"
                :style="{
                    transform: `perspective(1200px) rotateX(${targetRotateX * 0.4}deg) rotateY(${targetRotateY * 0.4}deg)`
                }"
            >
                <!-- Outer Astrological Orbit Ring -->
                <div class="astrolabe-ring ring-outer"></div>
                <!-- Middle Gyroscope Ring (Rotates on X axis) -->
                <div class="astrolabe-ring ring-middle"></div>
                <!-- Inner Gyroscope Ring (Rotates on Y axis) -->
                <div class="astrolabe-ring ring-inner"></div>

                <!-- Center 3D Floating Prism with Couple Portrait -->
                <div class="astrolabe-center-core">
                    <div class="prism-frame">
                        <img
                            :src="invitationData.content.coverImage || '/images/templates/ningrum/portrait-couple.webp'"
                            alt="The Couple"
                            class="prism-photo"
                        />
                        <div class="prism-glass-overlay"></div>
                        <div class="prism-gold-filigree"></div>
                    </div>
                </div>

                <!-- Floating 3D Star Constellations -->
                <div class="floating-constellation star-1">✦</div>
                <div class="floating-constellation star-2">✧</div>
                <div class="floating-constellation star-3">✦</div>
                <div class="floating-constellation star-4">✧</div>
            </div>

            <!-- Stage Header & VIP Badge -->
            <div class="desktop-stage-caption">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/10 border border-amber-400/30 text-amber-300 text-xs tracking-widest uppercase font-semibold mb-3">
                    <span class="w-2 h-2 rounded-full bg-amber-400 animate-ping"></span>
                    Royal 3D Celestial Edition
                </div>
                <h1 class="text-4xl xl:text-5xl font-serif text-white tracking-wide font-normal">
                    {{ invitationData.content.weddingTitle }}
                </h1>
                <p class="text-emerald-300/80 font-sans text-sm tracking-wider mt-2">
                    {{ safeFormatDisplayDate(invitationData.content.weddingDateText) }}
                </p>
                <div class="mt-6 pt-4 border-t border-white/10 flex items-center justify-center gap-4 text-xs text-stone-300 font-light">
                    <span>Kepada Yth.</span>
                    <strong class="text-amber-300 font-semibold px-2 py-0.5 rounded bg-emerald-950/80 border border-amber-500/20">
                        {{ guestName || 'Tamu Kehormatan' }}
                    </strong>
                </div>
            </div>
        </aside>

        <!-- ======================================================== -->
        <!-- 2. SCROLLABLE INVITATION CONTAINER (Right Side / Full Mobile) -->
        <!-- ======================================================== -->
        <main class="celestia-main-scrollable">

            <!-- ==================================================== -->
            <!-- TOP OPENING COVER (3D Interactive Astrolabe Portal)  -->
            <!-- ==================================================== -->
            <section
                class="celestia-top-cover"
                :class="{ 'is-opened': isOpen }"
                data-section-order="opening_cover"
            >
                <div class="celestia-cover-inner">
                    <!-- Subtle Ambient Aura Glow -->
                    <div class="celestia-cover-glow"></div>

                    <!-- 3D Interactive Astrolabe Lock -->
                    <div class="celestia-cover-lock-wrap">
                        <div class="lock-ring lock-ring-1"></div>
                        <div class="lock-ring lock-ring-2"></div>
                        <div class="lock-ring lock-ring-3"></div>

                        <!-- Core Medallion -->
                        <div class="lock-medallion">
                            <span class="monogram-symbol">
                                {{ (invitationData.content.groom?.nickname?.[0] || 'A') }} &amp; {{ (invitationData.content.bride?.nickname?.[0] || 'N') }}
                            </span>
                            <span class="medallion-year">2026</span>
                        </div>
                    </div>

                    <!-- Invitation Header -->
                    <div class="mt-6 text-center z-10 px-4">
                        <p class="text-amber-300/90 text-xs tracking-[0.25em] uppercase font-medium">
                            THE WEDDING CELEBRATION OF
                        </p>
                        <h2 class="text-3xl sm:text-4xl md:text-5xl font-serif text-white tracking-tight mt-2 font-normal">
                            {{ invitationData.content.weddingTitle }}
                        </h2>
                        <div class="w-16 h-0.5 bg-gradient-to-r from-transparent via-amber-400 to-transparent mx-auto my-4"></div>
                        <p class="text-emerald-200/80 text-sm font-sans">
                            {{ safeFormatDisplayDate(invitationData.content.weddingDateText) }}
                        </p>
                    </div>

                    <!-- Guest VIP Card -->
                    <div class="celestia-guest-card mt-6">
                        <p class="text-[11px] text-stone-400 uppercase tracking-widest">Kepada Yth. Bapak/Ibu/Saudara/i</p>
                        <h3 class="text-lg font-serif text-amber-200 font-semibold mt-1">
                            {{ guestName || 'Tamu Kehormatan' }}
                        </h3>
                        <p class="text-[10px] text-stone-400 mt-1 italic">
                            *Mohon maaf apabila ada kesalahan penulisan nama/gelar
                        </p>
                    </div>

                    <!-- 3D Open Button -->
                    <button
                        @click="handleOpenInvitation"
                        class="celestia-btn-open group mt-7"
                    >
                        <span class="btn-glow"></span>
                        <span class="relative z-10 flex items-center gap-3 text-stone-900 font-serif font-bold text-sm tracking-wider uppercase px-6 py-3">
                            <svg class="w-4 h-4 text-emerald-900 fill-current animate-spin" style="animation-duration: 6s;" viewBox="0 0 24 24">
                                <path d="M12 2L14.39 8.26L21 9.27L16.5 14.14L17.77 21L12 17.77L6.23 21L7.5 14.14L3 9.27L9.61 8.26L12 2Z"/>
                            </svg>
                            Buka Gerbang Undangan
                            <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </span>
                    </button>
                </div>
            </section>

            <!-- ==================================================== -->
            <!-- MAIN CONTENT SECTIONS (Scrollable When Opened)       -->
            <!-- ==================================================== -->
            <div id="celestia-content-start" class="celestia-content-flow">

                <!-- SECTION 1: HERO COUPLE & 3D PRISM -->
                <section class="celestia-hero-section py-16 px-4 md:px-8 text-center relative overflow-hidden">
                    <div class="max-w-md mx-auto relative z-10">
                        <!-- Heraldic Monogram -->
                        <div class="inline-flex items-center justify-center w-14 h-14 rounded-full border border-amber-400/40 bg-emerald-950/70 text-amber-300 text-lg font-serif mb-4 shadow-[0_0_20px_rgba(212,175,55,0.25)]">
                            ✦
                        </div>

                        <p class="text-xs uppercase tracking-[0.3em] text-amber-300/80 mb-2">
                            WALIMATUL 'URSY
                        </p>
                        <h2 class="text-4xl sm:text-5xl font-serif text-white font-normal leading-tight">
                            {{ invitationData.content.weddingTitle }}
                        </h2>

                        <!-- 3D Tilting Hero Couple Portrait -->
                        <div class="celestia-hero-prism mt-8 relative mx-auto w-72 sm:w-80 h-96 sm:h-[420px] rounded-2xl p-2.5 bg-gradient-to-b from-amber-400/30 via-emerald-800/20 to-amber-500/10 shadow-[0_20px_50px_rgba(0,0,0,0.8)] border border-amber-400/30">
                            <div class="relative w-full h-full rounded-xl overflow-hidden">
                                <img
                                    :src="invitationData.content.coverImage || '/images/templates/ningrum/portrait-couple.webp'"
                                    alt="Couple Portrait"
                                    class="w-full h-full object-cover transform hover:scale-105 transition-transform duration-700"
                                />
                                <div class="absolute inset-0 bg-gradient-to-t from-emerald-950/90 via-transparent to-transparent"></div>
                                <div class="absolute bottom-4 inset-x-4 text-center">
                                    <p class="text-xs text-amber-200 tracking-widest uppercase font-serif">Menuju Bahtera Abadi</p>
                                    <p class="text-sm text-stone-200 font-light mt-0.5">{{ safeFormatDisplayDate(invitationData.content.weddingDateText) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- SECTION 2: 3D UNFOLDING HOLY VERSE -->
                <section class="celestia-verse-section py-16 px-4 md:px-8 relative">
                    <div class="max-w-md mx-auto">
                        <div class="celestia-verse-card relative p-8 rounded-2xl bg-emerald-950/40 backdrop-blur-xl border border-amber-400/30 shadow-[0_20px_40px_rgba(0,0,0,0.6)] text-center">
                            <!-- Golden Ornamental Arch Header -->
                            <div class="flex items-center justify-center gap-3 mb-6">
                                <div class="h-px w-10 bg-gradient-to-r from-transparent to-amber-400/60"></div>
                                <span class="text-amber-400 text-lg">✧</span>
                                <div class="h-px w-10 bg-gradient-to-l from-transparent to-amber-400/60"></div>
                            </div>

                            <p class="text-stone-200 font-serif text-base sm:text-lg leading-relaxed italic">
                                "{{ invitationData.content.quote || 'Dan di antara tanda-tanda kebesaran-Nya ialah Dia menciptakan pasangan-pasangan untukmu dari jenismu sendiri, agar kamu cenderung dan merasa tenteram kepadanya, dan dijadikan-Nya di antaramu rasa kasih dan sayang.' }}"
                            </p>

                            <div class="mt-6 flex items-center justify-center gap-2">
                                <span class="inline-block w-6 h-px bg-amber-400/50"></span>
                                <p class="text-xs text-amber-300 font-sans tracking-widest font-semibold uppercase">
                                    {{ invitationData.content.quoteSource || 'QS. Ar-Rum: 21' }}
                                </p>
                                <span class="inline-block w-6 h-px bg-amber-400/50"></span>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- SECTION 3: THE COUPLE (GROOM & BRIDE 3D HOLOGRAPHIC CARDS) -->
                <section class="celestia-couple-section py-16 px-4 md:px-8 relative overflow-hidden">
                    <div class="max-w-md mx-auto text-center mb-10">
                        <span class="text-xs uppercase tracking-[0.3em] text-amber-300/80 font-medium">Maha Suci Allah</span>
                        <h2 class="text-3xl sm:text-4xl font-serif text-white mt-1">Kedua Mempelai</h2>
                        <div class="w-12 h-0.5 bg-gradient-to-r from-transparent via-amber-400 to-transparent mx-auto mt-3"></div>
                    </div>

                    <div class="max-w-md mx-auto space-y-8">
                        <!-- GROOM 3D CARD -->
                        <div
                            class="celestia-card-groom relative rounded-2xl p-6 bg-gradient-to-br from-emerald-950/80 to-stone-900/90 backdrop-blur-xl border border-amber-400/30 shadow-[0_20px_45px_rgba(0,0,0,0.7)] cursor-pointer transition-transform duration-200"
                            :style="{
                                transform: `perspective(900px) rotateX(${cardGroomTilt.x}deg) rotateY(${cardGroomTilt.y}deg)`,
                            }"
                            @mousemove="(e) => handleCardTilt(e, 'groom')"
                            @mouseleave="() => resetCardTilt('groom')"
                        >
                            <!-- Specular Light Sheen Overlay -->
                            <div
                                class="absolute inset-0 rounded-2xl pointer-events-none opacity-40 mix-blend-overlay transition-opacity duration-300"
                                :style="{
                                    background: `radial-gradient(circle at ${cardGroomTilt.glareX}% ${cardGroomTilt.glareY}%, rgba(255,255,255,0.8), transparent 70%)`
                                }"
                            ></div>

                            <div class="flex flex-col items-center text-center">
                                <div class="relative w-36 h-36 rounded-full p-1 bg-gradient-to-tr from-amber-400 via-emerald-500 to-amber-200 shadow-[0_0_25px_rgba(212,175,55,0.4)] mb-4">
                                    <img
                                        :src="invitationData.content.groom?.photo || 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=400&q=80'"
                                        alt="Groom Photo"
                                        class="w-full h-full object-cover rounded-full"
                                    />
                                    <span class="absolute bottom-0 right-2 w-7 h-7 rounded-full bg-amber-400 text-stone-900 flex items-center justify-center font-bold text-xs shadow-md">
                                        ♂
                                    </span>
                                </div>
                                <h3 class="text-2xl font-serif text-white font-semibold">
                                    {{ invitationData.content.groom?.fullName || 'Dimas Prasetyo, S.Kom' }}
                                </h3>
                                <p class="text-xs text-amber-300/90 font-serif tracking-widest uppercase mt-0.5">
                                    - The Groom -
                                </p>
                                <p class="text-xs text-stone-300 mt-3 font-light max-w-xs leading-relaxed">
                                    {{ invitationData.content.groom?.parents || 'Putra pertama dari Bpk. Bambang Prasetyo & Ibu Sri Wahyuni' }}
                                </p>
                                <a
                                    v-if="invitationData.content.groom?.instagram"
                                    :href="'https://instagram.com/' + invitationData.content.groom.instagram.replace('@', '')"
                                    target="_blank"
                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/5 hover:bg-white/10 border border-white/10 text-amber-300 text-xs mt-4 transition-colors"
                                >
                                    <span>📸</span>
                                    <span>{{ invitationData.content.groom.instagram }}</span>
                                </a>
                            </div>
                        </div>

                        <!-- Interlocking Rings / Heart Emblem -->
                        <div class="flex items-center justify-center">
                            <div class="w-12 h-12 rounded-full border border-amber-400/40 bg-emerald-950/80 flex items-center justify-center text-amber-300 shadow-[0_0_20px_rgba(212,175,55,0.3)]">
                                &amp;
                            </div>
                        </div>

                        <!-- BRIDE 3D CARD -->
                        <div
                            class="celestia-card-bride relative rounded-2xl p-6 bg-gradient-to-br from-emerald-950/80 to-stone-900/90 backdrop-blur-xl border border-amber-400/30 shadow-[0_20px_45px_rgba(0,0,0,0.7)] cursor-pointer transition-transform duration-200"
                            :style="{
                                transform: `perspective(900px) rotateX(${cardBrideTilt.x}deg) rotateY(${cardBrideTilt.y}deg)`,
                            }"
                            @mousemove="(e) => handleCardTilt(e, 'bride')"
                            @mouseleave="() => resetCardTilt('bride')"
                        >
                            <!-- Specular Light Sheen Overlay -->
                            <div
                                class="absolute inset-0 rounded-2xl pointer-events-none opacity-40 mix-blend-overlay transition-opacity duration-300"
                                :style="{
                                    background: `radial-gradient(circle at ${cardBrideTilt.glareX}% ${cardBrideTilt.glareY}%, rgba(255,255,255,0.8), transparent 70%)`
                                }"
                            ></div>

                            <div class="flex flex-col items-center text-center">
                                <div class="relative w-36 h-36 rounded-full p-1 bg-gradient-to-tr from-amber-400 via-rose-400 to-amber-200 shadow-[0_0_25px_rgba(212,175,55,0.4)] mb-4">
                                    <img
                                        :src="invitationData.content.bride?.photo || 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=400&q=80'"
                                        alt="Bride Photo"
                                        class="w-full h-full object-cover rounded-full"
                                    />
                                    <span class="absolute bottom-0 right-2 w-7 h-7 rounded-full bg-rose-400 text-stone-900 flex items-center justify-center font-bold text-xs shadow-md">
                                        ♀
                                    </span>
                                </div>
                                <h3 class="text-2xl font-serif text-white font-semibold">
                                    {{ invitationData.content.bride?.fullName || 'Anisa Rahmawati, S.E' }}
                                </h3>
                                <p class="text-xs text-amber-300/90 font-serif tracking-widest uppercase mt-0.5">
                                    - The Bride -
                                </p>
                                <p class="text-xs text-stone-300 mt-3 font-light max-w-xs leading-relaxed">
                                    {{ invitationData.content.bride?.parents || 'Putri kedua dari Bpk. H. Rahmat Hidayat & Ibu Siti Aminah' }}
                                </p>
                                <a
                                    v-if="invitationData.content.bride?.instagram"
                                    :href="'https://instagram.com/' + invitationData.content.bride.instagram.replace('@', '')"
                                    target="_blank"
                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/5 hover:bg-white/10 border border-white/10 text-amber-300 text-xs mt-4 transition-colors"
                                >
                                    <span>📸</span>
                                    <span>{{ invitationData.content.bride.instagram }}</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- SECTION 4: 3D CHRONOMETER COUNTDOWN -->
                <section class="celestia-countdown-section py-16 px-4 md:px-8 relative">
                    <div class="max-w-md mx-auto text-center">
                        <span class="text-xs uppercase tracking-[0.3em] text-amber-300/80 font-medium">Menghitung Hari</span>
                        <h2 class="text-3xl font-serif text-white mt-1">Hari Berbahagia</h2>
                        <p class="text-xs text-emerald-200/80 mt-1">{{ safeFormatDisplayDate(invitationData.content.weddingDateText) }}</p>

                        <!-- 4 3D Chronometer Boxes -->
                        <div class="grid grid-cols-4 gap-2.5 sm:gap-3 mt-8">
                            <div class="celestia-chrono-box p-3 rounded-xl bg-emerald-950/60 backdrop-blur-md border border-amber-400/30 text-center shadow-lg">
                                <span class="text-2xl sm:text-3xl font-serif font-bold text-amber-300 block">
                                    {{ timeLeft.days }}
                                </span>
                                <span class="text-[10px] tracking-widest uppercase text-stone-400 font-sans mt-0.5 block">Hari</span>
                            </div>
                            <div class="celestia-chrono-box p-3 rounded-xl bg-emerald-950/60 backdrop-blur-md border border-amber-400/30 text-center shadow-lg">
                                <span class="text-2xl sm:text-3xl font-serif font-bold text-amber-300 block">
                                    {{ timeLeft.hours }}
                                </span>
                                <span class="text-[10px] tracking-widest uppercase text-stone-400 font-sans mt-0.5 block">Jam</span>
                            </div>
                            <div class="celestia-chrono-box p-3 rounded-xl bg-emerald-950/60 backdrop-blur-md border border-amber-400/30 text-center shadow-lg">
                                <span class="text-2xl sm:text-3xl font-serif font-bold text-amber-300 block">
                                    {{ timeLeft.minutes }}
                                </span>
                                <span class="text-[10px] tracking-widest uppercase text-stone-400 font-sans mt-0.5 block">Menit</span>
                            </div>
                            <div class="celestia-chrono-box p-3 rounded-xl bg-emerald-950/60 backdrop-blur-md border border-amber-400/30 text-center shadow-lg">
                                <span class="text-2xl sm:text-3xl font-serif font-bold text-amber-300 block animate-pulse">
                                    {{ timeLeft.seconds }}
                                </span>
                                <span class="text-[10px] tracking-widest uppercase text-stone-400 font-sans mt-0.5 block">Detik</span>
                            </div>
                        </div>

                        <!-- Add to Google Calendar Button -->
                        <div class="mt-8">
                            <a
                                :href="googleCalendarUrl"
                                target="_blank"
                                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-gradient-to-r from-amber-500 to-amber-600 text-stone-900 font-serif font-bold text-xs tracking-wider uppercase shadow-[0_10px_25px_rgba(212,175,55,0.35)] hover:scale-105 active:scale-95 transition-transform"
                            >
                                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                                    <path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zm0-12H5V6h14v2z"/>
                                </svg>
                                Simpan ke Google Calendar
                            </a>
                        </div>
                    </div>
                </section>

                <!-- SECTION 5: THE SACRED EVENTS (AKAD & RESEPSI 3D PORTALS) -->
                <section class="celestia-events-section py-16 px-4 md:px-8 relative">
                    <div class="max-w-md mx-auto text-center mb-10">
                        <span class="text-xs uppercase tracking-[0.3em] text-amber-300/80 font-medium">Rangkaian Acara</span>
                        <h2 class="text-3xl sm:text-4xl font-serif text-white mt-1">Agenda Sakral</h2>
                        <div class="w-12 h-0.5 bg-gradient-to-r from-transparent via-amber-400 to-transparent mx-auto mt-3"></div>
                    </div>

                    <div class="max-w-md mx-auto space-y-6">
                        <div
                            v-for="(ev, idx) in events"
                            :key="ev.id || idx"
                            class="celestia-event-card relative rounded-2xl p-6 sm:p-7 bg-emerald-950/60 backdrop-blur-xl border border-amber-400/35 shadow-[0_20px_45px_rgba(0,0,0,0.6)]"
                        >
                            <!-- Card Crown Icon -->
                            <div class="flex items-center justify-between pb-4 border-b border-white/10 mb-5">
                                <span class="px-3 py-1 rounded-full bg-amber-400/10 border border-amber-400/30 text-amber-300 text-xs font-serif font-bold uppercase tracking-wider">
                                    {{ ev.name || (idx === 0 ? 'Akad Nikah' : 'Resepsi Pernikahan') }}
                                </span>
                                <span class="text-amber-400 text-sm">✦</span>
                            </div>

                            <!-- Date & Time -->
                            <div class="space-y-2 text-stone-200">
                                <div class="flex items-start gap-3">
                                    <span class="text-amber-400 text-base">📅</span>
                                    <div>
                                        <p class="text-xs text-stone-400 uppercase tracking-wider">Hari & Tanggal</p>
                                        <p class="font-serif text-base text-white font-medium">
                                            {{ safeFormatDisplayDate(ev.starts_at) }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-start gap-3 pt-2">
                                    <span class="text-amber-400 text-base">⏰</span>
                                    <div>
                                        <p class="text-xs text-stone-400 uppercase tracking-wider">Waktu Acara</p>
                                        <p class="font-serif text-base text-white font-medium">
                                            {{ safeFormatEventTime(ev.starts_at, ev.ends_at, ev.timezone || 'WIB') }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-start gap-3 pt-2">
                                    <span class="text-amber-400 text-base">📍</span>
                                    <div>
                                        <p class="text-xs text-stone-400 uppercase tracking-wider">Lokasi / Tempat</p>
                                        <p class="font-serif text-base text-amber-200 font-semibold">
                                            {{ ev.venue_name || 'Grand Ballroom Hotel Arya' }}
                                        </p>
                                        <p class="text-xs text-stone-300 mt-0.5 leading-relaxed font-light">
                                            {{ ev.address || 'Jl. Jenderal Sudirman Kav. 52-53, Jakarta Selatan' }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="mt-6 flex flex-wrap gap-2.5 pt-4 border-t border-white/10">
                                <a
                                    v-if="ev.maps_url"
                                    :href="ev.maps_url"
                                    target="_blank"
                                    class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-amber-400 hover:bg-amber-300 text-stone-900 font-serif font-bold text-xs uppercase tracking-wider transition-colors shadow"
                                >
                                    <span>🗺️ Buka Google Maps</span>
                                </a>
                                <button
                                    v-if="ev.address"
                                    @click="copyAddress(ev.address)"
                                    class="px-3.5 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-stone-200 text-xs transition-colors"
                                    title="Salin Alamat"
                                >
                                    📋 Salin
                                </button>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- SECTION 6: GALLERY OF MOMENTS (3D PERSPECTIVE COVERFLOW) -->
                <section class="celestia-gallery-section py-16 px-4 md:px-8 relative">
                    <div class="max-w-md mx-auto text-center mb-10">
                        <span class="text-xs uppercase tracking-[0.3em] text-amber-300/80 font-medium">Dokumentasi</span>
                        <h2 class="text-3xl sm:text-4xl font-serif text-white mt-1">Galeri Bahagia</h2>
                        <div class="w-12 h-0.5 bg-gradient-to-r from-transparent via-amber-400 to-transparent mx-auto mt-3"></div>
                    </div>

                    <div class="max-w-md mx-auto grid grid-cols-2 gap-3.5">
                        <div
                            v-for="(img, idx) in invitationData.content.gallery || []"
                            :key="idx"
                            @click="openLightbox(img)"
                            class="celestia-gallery-item group relative aspect-[3/4] rounded-2xl overflow-hidden cursor-pointer border border-amber-400/30 shadow-[0_15px_35px_rgba(0,0,0,0.6)]"
                        >
                            <img
                                :src="img"
                                alt="Gallery Moment"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
                            />
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end justify-center p-3">
                                <span class="text-[11px] text-amber-300 font-serif tracking-widest uppercase">Perbesar 🔍</span>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- SECTION 7: LOVE STORY 3D PARALLAX TIMELINE -->
                <section class="celestia-story-section py-16 px-4 md:px-8 relative">
                    <div class="max-w-md mx-auto text-center mb-12">
                        <span class="text-xs uppercase tracking-[0.3em] text-amber-300/80 font-medium">Jejak Langkah</span>
                        <h2 class="text-3xl sm:text-4xl font-serif text-white mt-1">Kisah Cinta Kami</h2>
                        <div class="w-12 h-0.5 bg-gradient-to-r from-transparent via-amber-400 to-transparent mx-auto mt-3"></div>
                    </div>

                    <div class="max-w-md mx-auto relative border-l-2 border-amber-400/30 ml-4 sm:ml-6 pl-6 space-y-8">
                        <div class="celestia-milestone relative">
                            <!-- Bullet node -->
                            <div class="absolute -left-[31px] top-1.5 w-4 h-4 rounded-full bg-amber-400 shadow-[0_0_12px_rgba(212,175,55,0.8)] border-2 border-stone-900"></div>
                            <span class="text-xs text-amber-400 font-serif font-bold uppercase tracking-wider">Awal Bertemu • 2021</span>
                            <h4 class="text-lg font-serif text-white font-semibold mt-0.5">Pertemuan yang Tertulis di Bintang</h4>
                            <p class="text-xs text-stone-300 leading-relaxed font-light mt-1">
                                Bertemu pertama kali dalam sebuah konferensi kerja di Yogyakarta. Percakapan singkat yang sederhana berkembang menjadi ikatan persahabatan yang tulus.
                            </p>
                        </div>

                        <div class="celestia-milestone relative">
                            <div class="absolute -left-[31px] top-1.5 w-4 h-4 rounded-full bg-amber-400 shadow-[0_0_12px_rgba(212,175,55,0.8)] border-2 border-stone-900"></div>
                            <span class="text-xs text-amber-400 font-serif font-bold uppercase tracking-wider">Mengikat Janji • 2025</span>
                            <h4 class="text-lg font-serif text-white font-semibold mt-0.5">Lamaran di Bawah Sinar Rembulan</h4>
                            <p class="text-xs text-stone-300 leading-relaxed font-light mt-1">
                                Dengan restu kedua orang tua dan keluarga besar, kami membulatkan niat suci untuk melangkah ke jenjang pernikahan yang sakral.
                            </p>
                        </div>

                        <div class="celestia-milestone relative">
                            <div class="absolute -left-[31px] top-1.5 w-4 h-4 rounded-full bg-amber-400 shadow-[0_0_12px_rgba(212,175,55,0.8)] border-2 border-stone-900"></div>
                            <span class="text-xs text-amber-400 font-serif font-bold uppercase tracking-wider">Hari Sakral • 2026</span>
                            <h4 class="text-lg font-serif text-white font-semibold mt-0.5">Menuju Bahtera Selamanya</h4>
                            <p class="text-xs text-stone-300 leading-relaxed font-light mt-1">
                                Mengucap janji suci di hadapan Allah SWT, disaksikan oleh keluarga dan sahabat terkasih untuk mengarungi bahtera kehidupan bersama.
                            </p>
                        </div>
                    </div>
                </section>

                <!-- SECTION 8: THE ROYAL VAULT (AMPLOP DIGITAL & 3D METALLIC CARDS) -->
                <section class="celestia-gift-section py-16 px-4 md:px-8 relative">
                    <div class="max-w-md mx-auto text-center mb-10">
                        <span class="text-xs uppercase tracking-[0.3em] text-amber-300/80 font-medium">Tanda Kasih</span>
                        <h2 class="text-3xl sm:text-4xl font-serif text-white mt-1">Amplop Digital</h2>
                        <div class="w-12 h-0.5 bg-gradient-to-r from-transparent via-amber-400 to-transparent mx-auto mt-3"></div>
                        <p class="text-xs text-stone-300 mt-3 font-light leading-relaxed max-w-sm mx-auto">
                            Doa restu Anda adalah anugerah terindah bagi kami. Namun jika memberi adalah ungkapan kasih, Anda dapat menyalurkannya melalui rekening berikut:
                        </p>
                    </div>

                    <div class="max-w-md mx-auto space-y-6">
                        <div
                            v-for="(acc, idx) in digitalGift?.accounts || []"
                            :key="idx"
                            class="celestia-bank-card relative rounded-2xl p-6 bg-gradient-to-br from-emerald-950 via-stone-900 to-emerald-900/90 border border-amber-400/40 shadow-[0_20px_45px_rgba(0,0,0,0.7)] overflow-hidden"
                        >
                            <!-- Metallic Foil Sheen -->
                            <div class="absolute -right-8 -top-8 w-32 h-32 bg-amber-400/10 rounded-full blur-2xl"></div>

                            <div class="flex items-center justify-between">
                                <span class="font-serif font-black text-xl text-amber-300 tracking-wider">
                                    {{ acc.bank || 'BANK TRANSFER' }}
                                </span>
                                <!-- Realistic 3D Microchip -->
                                <div class="w-10 h-7 rounded bg-gradient-to-r from-amber-300 via-amber-200 to-amber-400 border border-amber-500/50 shadow-inner flex items-center justify-center">
                                    <div class="w-8 h-5 border border-amber-600/40 rounded-sm"></div>
                                </div>
                            </div>

                            <div class="my-6">
                                <p class="text-[10px] text-stone-400 tracking-widest uppercase">Nomor Rekening</p>
                                <p class="text-xl sm:text-2xl font-mono text-white font-bold tracking-widest mt-0.5">
                                    {{ acc.account_number }}
                                </p>
                                <p class="text-xs text-amber-200 font-serif mt-1 font-medium">
                                    a.n {{ acc.account_name }}
                                </p>
                            </div>

                            <div class="flex gap-2 pt-3 border-t border-white/10">
                                <button
                                    @click="copyBankNumber(acc.account_number, idx)"
                                    class="flex-1 inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl bg-amber-400 hover:bg-amber-300 text-stone-900 font-serif font-bold text-xs uppercase tracking-wider transition-colors shadow"
                                >
                                    <span>{{ copiedBankIndex === idx ? '✓ Berhasil Disalin!' : '📋 Salin No. Rekening' }}</span>
                                </button>
                                <button
                                    v-if="acc.qr_image"
                                    @click="openQris(acc)"
                                    class="px-3.5 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-stone-200 text-xs font-serif transition-colors"
                                    title="Tampilkan QRIS"
                                >
                                    QRIS 📱
                                </button>
                            </div>
                        </div>

                        <!-- Physical Gift Address Box -->
                        <div
                            v-if="digitalGift?.physical_gift?.enabled"
                            class="celestia-physical-gift p-6 rounded-2xl bg-emerald-950/40 border border-amber-400/25 text-left"
                        >
                            <div class="flex items-center gap-2 mb-2">
                                <span class="text-base">🎁</span>
                                <h4 class="font-serif text-white font-semibold text-sm">Kirim Kado Fisik</h4>
                            </div>
                            <p class="text-xs text-stone-300 leading-relaxed font-light">
                                Penerima: <strong>{{ digitalGift.physical_gift.receiver_name }}</strong><br />
                                Telepon: {{ digitalGift.physical_gift.phone }}<br />
                                Alamat: {{ digitalGift.physical_gift.address }}
                            </p>
                            <button
                                @click="copyAddress(digitalGift.physical_gift.address)"
                                class="mt-4 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-amber-300 text-xs transition-colors"
                            >
                                📋 Salin Alamat Kirim
                            </button>
                        </div>
                    </div>
                </section>

                <!-- SECTION 9: RSVP & GUESTBOOK (WISHES WALL) -->
                <section class="celestia-rsvp-section py-16 px-4 md:px-8 relative">
                    <div class="max-w-md mx-auto text-center mb-10">
                        <span class="text-xs uppercase tracking-[0.3em] text-amber-300/80 font-medium">Doa & Kehadiran</span>
                        <h2 class="text-3xl sm:text-4xl font-serif text-white mt-1">Konfirmasi &amp; Doa Restu</h2>
                        <div class="w-12 h-0.5 bg-gradient-to-r from-transparent via-amber-400 to-transparent mx-auto mt-3"></div>
                    </div>

                    <div class="max-w-md mx-auto">
                        <!-- RSVP Form -->
                        <form
                            @submit.prevent="submitRsvp"
                            class="p-6 rounded-2xl bg-emerald-950/60 backdrop-blur-xl border border-amber-400/30 shadow-[0_20px_45px_rgba(0,0,0,0.6)] space-y-4 text-left"
                        >
                            <div>
                                <label class="block text-xs uppercase tracking-wider text-stone-300 mb-1 font-medium">Nama Anda</label>
                                <input
                                    v-model="rsvpForm.guest_name"
                                    type="text"
                                    placeholder="Masukkan nama Anda"
                                    required
                                    class="w-full px-4 py-2.5 rounded-xl bg-stone-900/80 border border-white/15 text-white placeholder-stone-500 focus:outline-none focus:border-amber-400 text-sm"
                                />
                            </div>

                            <div>
                                <label class="block text-xs uppercase tracking-wider text-stone-300 mb-1 font-medium">Konfirmasi Kehadiran</label>
                                <select
                                    v-model="rsvpForm.attendance_status"
                                    class="w-full px-4 py-2.5 rounded-xl bg-stone-900/80 border border-white/15 text-white focus:outline-none focus:border-amber-400 text-sm"
                                >
                                    <option value="attending">✓ Ya, Saya Akan Hadir</option>
                                    <option value="not_attending">✗ Maaf, Belum Bisa Hadir</option>
                                    <option value="uncertain">? Masih Ragu</option>
                                </select>
                            </div>

                            <div v-if="rsvpForm.attendance_status === 'attending'">
                                <label class="block text-xs uppercase tracking-wider text-stone-300 mb-1 font-medium">Jumlah Hadir</label>
                                <select
                                    v-model="rsvpForm.attendee_count"
                                    class="w-full px-4 py-2.5 rounded-xl bg-stone-900/80 border border-white/15 text-white focus:outline-none focus:border-amber-400 text-sm"
                                >
                                    <option :value="1">1 Orang</option>
                                    <option :value="2">2 Orang</option>
                                    <option :value="3">3 Orang</option>
                                    <option :value="4">4 Orang</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs uppercase tracking-wider text-stone-300 mb-1 font-medium">Ucapan &amp; Doa Restu</label>
                                <textarea
                                    v-model="rsvpForm.message"
                                    rows="3"
                                    placeholder="Tuliskan ucapan dan doa Anda untuk kedua mempelai..."
                                    class="w-full px-4 py-2.5 rounded-xl bg-stone-900/80 border border-white/15 text-white placeholder-stone-500 focus:outline-none focus:border-amber-400 text-sm resize-none"
                                ></textarea>
                            </div>

                            <p v-if="rsvpErrorMessage" class="text-xs text-rose-400 font-medium">
                                {{ rsvpErrorMessage }}
                            </p>

                            <p v-if="rsvpSuccess" class="text-xs text-emerald-400 font-medium">
                                ✓ Terima kasih, konfirmasi dan doa Anda telah tersimpan!
                            </p>

                            <button
                                type="submit"
                                :disabled="rsvpSubmitting"
                                class="w-full py-3 rounded-xl bg-gradient-to-r from-amber-400 to-amber-500 hover:from-amber-300 hover:to-amber-400 text-stone-900 font-serif font-bold text-xs uppercase tracking-wider transition-all shadow-lg disabled:opacity-50"
                            >
                                <span v-if="rsvpSubmitting">Mengirim...</span>
                                <span v-else>Kirim Konfirmasi &amp; Doa</span>
                            </button>
                        </form>

                        <!-- Guestbook Messages List -->
                        <div class="mt-8 space-y-3.5">
                            <h4 class="text-sm font-serif text-amber-300 uppercase tracking-wider text-center">
                                Doa Restu Sahabat &amp; Keluarga ({{ guestbook.length }})
                            </h4>

                            <div
                                v-for="g in guestbook"
                                :key="g.id"
                                class="p-4 rounded-xl bg-emerald-950/40 border border-white/10 text-left"
                            >
                                <div class="flex items-center justify-between mb-1.5">
                                    <h5 class="text-sm font-serif text-white font-semibold">{{ g.guest_name }}</h5>
                                    <span class="text-[10px] text-stone-400">{{ g.created_at }}</span>
                                </div>
                                <p class="text-xs text-stone-300 font-light leading-relaxed">
                                    {{ g.message }}
                                </p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- SECTION 10: CLOSING FOOTER -->
                <footer class="py-16 px-4 md:px-8 text-center border-t border-white/10 relative">
                    <div class="max-w-md mx-auto">
                        <span class="text-amber-400 text-2xl block mb-2">✦</span>
                        <p class="text-xs text-stone-400 uppercase tracking-widest font-serif">Merupakan suatu kehormatan &amp; kebahagiaan bagi kami</p>
                        <h3 class="text-3xl font-serif text-white mt-2">{{ invitationData.content.weddingTitle }}</h3>
                        <p class="text-xs text-emerald-300/80 mt-1">{{ safeFormatDisplayDate(invitationData.content.weddingDateText) }}</p>
                        <p class="text-[11px] text-stone-500 mt-8">
                            Dibuat dengan segenap cinta &amp; teknologi 3D oleh UndanganDigital
                        </p>
                    </div>
                </footer>

            </div>
        </main>

        <!-- ======================================================== -->
        <!-- FLOATING 3D GLASS DOCK (Music & Quick Navigation)        -->
        <!-- ======================================================== -->
        <div v-if="isOpen" class="celestia-floating-dock">
            <button
                @click="emit('toggleMusic')"
                class="dock-btn music-btn"
                :class="{ 'is-spinning': isPlayingMusic }"
                title="Putar / Jeda Musik"
            >
                <div class="vinyl-disc">
                    <div class="vinyl-center"></div>
                </div>
            </button>
            <div class="dock-divider"></div>
            <button
                @click="scrollToSection('celestia-content-start')"
                class="dock-btn"
                title="Kembali ke Atas"
            >
                ▲
            </button>
        </div>

        <!-- ======================================================== -->
        <!-- LIGHTBOX MODAL FOR GALLERY PHOTO                         -->
        <!-- ======================================================== -->
        <div
            v-if="activeLightboxImg"
            @click="closeLightbox"
            class="fixed inset-0 z-[200] bg-black/90 backdrop-blur-md flex items-center justify-center p-4 cursor-pointer"
        >
            <div class="relative max-w-2xl max-h-[85vh] rounded-2xl overflow-hidden border border-amber-400/40 shadow-2xl">
                <img :src="activeLightboxImg" alt="Enlarged Photo" class="w-full h-full object-contain" />
                <button
                    @click.stop="closeLightbox"
                    class="absolute top-3 right-3 w-8 h-8 rounded-full bg-stone-900/80 text-white flex items-center justify-center font-bold hover:bg-amber-400 hover:text-stone-900 transition-colors"
                >
                    ✕
                </button>
            </div>
        </div>

    </div>
</template>

<style scoped>
/* ==========================================================================
   CELESTIA 3D CORE ENGINE STYLES
   ========================================================================== */

.celestia-stardust-canvas {
    position: fixed;
    inset: 0;
    width: 100vw;
    height: 100vh;
    pointer-events: none;
    z-index: 1;
}

.celestia-page-root {
    position: relative;
    min-height: 100vh;
    background-color: #040D0A;
    background-image: 
        radial-gradient(circle at 50% 0%, #0F2A21 0%, transparent 60%),
        radial-gradient(circle at 100% 100%, #071712 0%, transparent 70%),
        radial-gradient(circle at 0% 80%, #081C15 0%, transparent 60%);
    color: #F8FAFC;
    overflow-x: hidden;
    font-family: 'Plus Jakarta Sans', sans-serif;
}

/* Split Pane Layout */
@media (min-width: 1024px) {
    .celestia-page-root {
        display: flex;
        width: 100vw;
        height: 100vh;
        overflow: hidden;
    }

    .celestia-desktop-stage {
        flex: 0 0 58%;
        position: relative;
        height: 100vh;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        border-right: 1px solid rgba(212, 175, 55, 0.2);
        background: radial-gradient(circle at 50% 45%, #0B221A 0%, #040D0A 75%);
    }

    .celestia-main-scrollable {
        flex: 0 0 42%;
        height: 100vh;
        overflow-y: auto;
        position: relative;
        scroll-behavior: smooth;
    }
}

@media (max-width: 1023px) {
    .celestia-main-scrollable {
        width: 100%;
        min-height: 100vh;
        position: relative;
    }
}

/* ==========================================================================
   DESKTOP 3D ASTROLABE / ORRERY
   ========================================================================== */

.celestia-astrolabe-viewport {
    position: relative;
    width: 380px;
    height: 380px;
    transform-style: preserve-3d;
    transition: transform 0.25s ease-out;
    display: flex;
    align-items: center;
    justify-content: center;
}

.astrolabe-ring {
    position: absolute;
    border-radius: 50%;
    border: 1.5px solid rgba(212, 175, 55, 0.4);
    box-shadow: 0 0 20px rgba(212, 175, 55, 0.2);
    transform-style: preserve-3d;
}

.ring-outer {
    width: 360px;
    height: 360px;
    border-style: dashed;
    border-color: rgba(230, 202, 101, 0.35);
    animation: orbitSpin 50s linear infinite;
}

.ring-middle {
    width: 310px;
    height: 310px;
    border-color: rgba(212, 175, 55, 0.5);
    animation: orbitX 30s linear infinite;
}

.ring-inner {
    width: 260px;
    height: 260px;
    border-color: rgba(110, 231, 183, 0.4);
    animation: orbitY 24s linear infinite reverse;
}

@keyframes orbitSpin {
    0% { transform: rotateZ(0deg); }
    100% { transform: rotateZ(360deg); }
}

@keyframes orbitX {
    0% { transform: rotateX(60deg) rotateY(0deg) rotateZ(0deg); }
    100% { transform: rotateX(60deg) rotateY(360deg) rotateZ(360deg); }
}

@keyframes orbitY {
    0% { transform: rotateY(60deg) rotateX(0deg) rotateZ(0deg); }
    100% { transform: rotateY(60deg) rotateX(360deg) rotateZ(360deg); }
}

.astrolabe-center-core {
    position: relative;
    width: 200px;
    height: 250px;
    transform-style: preserve-3d;
    z-index: 10;
}

.prism-frame {
    position: relative;
    width: 100%;
    height: 100%;
    border-radius: 24px;
    overflow: hidden;
    padding: 6px;
    background: linear-gradient(135deg, rgba(212,175,55,0.6), rgba(11,34,26,0.8), rgba(212,175,55,0.3));
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.7), 0 0 30px rgba(212, 175, 55, 0.25);
}

.prism-photo {
    width: 100%;
    height: 100%;
    object-cover: cover;
    border-radius: 18px;
}

.floating-constellation {
    position: absolute;
    color: #E6CA65;
    font-size: 18px;
    filter: drop-shadow(0 0 8px #E6CA65);
    animation: twinkle 3s ease-in-out infinite alternate;
}

.star-1 { top: 10px; left: 20px; animation-delay: 0s; }
.star-2 { bottom: 20px; right: 15px; animation-delay: 1.2s; }
.star-3 { top: 40px; right: 30px; animation-delay: 2.1s; }
.star-4 { bottom: 30px; left: 40px; animation-delay: 0.7s; }

@keyframes twinkle {
    0% { opacity: 0.3; transform: scale(0.8); }
    100% { opacity: 1; transform: scale(1.3); }
}

.desktop-stage-caption {
    margin-top: 2rem;
    text-align: center;
    z-index: 10;
}

/* ==========================================================================
   TOP COVER (3D OPENING SCREEN)
   ========================================================================== */

.celestia-top-cover {
    position: fixed;
    inset: 0;
    z-index: 80;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #040D0A;
    background-image: 
        radial-gradient(circle at 50% 40%, #0F2A21 0%, #040D0A 75%),
        url('/images/templates/ningrum/texture-sd.png');
    background-size: cover, 180px;
    background-repeat: no-repeat, repeat;
    transition: transform 1.1s cubic-bezier(0.85, 0, 0.15, 1), opacity 0.9s ease;
}

@media (min-width: 1024px) {
    .celestia-top-cover {
        left: 58%;
        width: 42%;
    }
}

.celestia-top-cover.is-opened {
    transform: translateY(-105%) scale(0.95);
    opacity: 0;
    pointer-events: none;
}

.celestia-cover-inner {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 2rem 1.5rem;
    position: relative;
    max-width: 420px;
    width: 100%;
}

.celestia-cover-glow {
    position: absolute;
    width: 280px;
    height: 280px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(212, 175, 55, 0.25) 0%, transparent 70%);
    filter: blur(40px);
    pointer-events: none;
}

/* 3D Cover Lock */
.celestia-cover-lock-wrap {
    position: relative;
    width: 160px;
    height: 160px;
    display: flex;
    align-items: center;
    justify-content: center;
    transform-style: preserve-3d;
}

.lock-ring {
    position: absolute;
    border-radius: 50%;
    border: 1.5px solid rgba(212, 175, 55, 0.4);
}

.lock-ring-1 {
    width: 150px;
    height: 150px;
    border-style: dashed;
    border-color: rgba(230, 202, 101, 0.5);
    animation: orbitSpin 35s linear infinite;
}

.lock-ring-2 {
    width: 125px;
    height: 125px;
    border-color: rgba(110, 231, 183, 0.45);
    animation: orbitSpin 20s linear infinite reverse;
}

.lock-ring-3 {
    width: 100px;
    height: 100px;
    border-color: rgba(212, 175, 55, 0.6);
}

.lock-medallion {
    position: relative;
    width: 78px;
    height: 78px;
    border-radius: 50%;
    background: linear-gradient(135deg, #E6CA65 0%, #AA771C 60%, #5E4007 100%);
    box-shadow: 0 0 25px rgba(212, 175, 55, 0.5), inset 0 2px 5px rgba(255,255,255,0.6);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #1A1203;
    font-family: 'Cinzel', serif;
    font-weight: 700;
    text-align: center;
}

.monogram-symbol {
    font-size: 14px;
    letter-spacing: 1px;
}

.medallion-year {
    font-size: 9px;
    letter-spacing: 2px;
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-weight: 600;
    margin-top: 1px;
}

.celestia-guest-card {
    background: rgba(11, 34, 26, 0.7);
    border: 1px solid rgba(212, 175, 55, 0.3);
    backdrop-filter: blur(10px);
    border-radius: 16px;
    padding: 1rem 1.5rem;
    text-align: center;
    width: 100%;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
}

.celestia-btn-open {
    position: relative;
    border-radius: 9999px;
    background: linear-gradient(135deg, #FDE68A 0%, #D4AF37 50%, #B45309 100%);
    border: 1px solid rgba(255, 255, 255, 0.4);
    box-shadow: 0 10px 30px rgba(212, 175, 55, 0.45);
    overflow: hidden;
    cursor: pointer;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.celestia-btn-open:hover {
    transform: scale(1.05);
    box-shadow: 0 15px 40px rgba(212, 175, 55, 0.65);
}

.celestia-btn-open:active {
    transform: scale(0.97);
}

.btn-glow {
    position: absolute;
    inset: 0;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.6), transparent);
    transform: translateX(-100%);
    animation: btnShimmer 3s infinite;
}

@keyframes btnShimmer {
    100% { transform: translateX(100%); }
}

/* ==========================================================================
   FLOATING 3D GLASS DOCK
   ========================================================================== */

.celestia-floating-dock {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 90;
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    background: rgba(11, 34, 26, 0.85);
    border: 1px solid rgba(212, 175, 55, 0.35);
    backdrop-filter: blur(14px);
    border-radius: 9999px;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.6);
}

.dock-btn {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #FDE68A;
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.15);
    cursor: pointer;
    transition: background 0.2s, transform 0.2s;
    font-size: 12px;
}

.dock-btn:hover {
    background: rgba(212, 175, 55, 0.3);
    transform: scale(1.1);
}

.dock-divider {
    width: 1px;
    height: 20px;
    background: rgba(255, 255, 255, 0.2);
}

.vinyl-disc {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: radial-gradient(circle, #292524 30%, #0c0a09 100%);
    border: 1px solid rgba(212, 175, 55, 0.5);
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
}

.vinyl-center {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #D4AF37;
}

.music-btn.is-spinning .vinyl-disc {
    animation: orbitSpin 4s linear infinite;
}
</style>

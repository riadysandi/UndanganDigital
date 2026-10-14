<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { Head } from '@inertiajs/vue3';
import confetti from 'canvas-confetti';
import EloiseFairyDustCanvas from './EloiseFairyDustCanvas.vue';

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

onMounted(() => {
    document.body.classList.add('eloise', 'original', 'preset-original');
});

onBeforeUnmount(() => {
    document.body.classList.remove('eloise', 'original', 'preset-original');
});

const isOpening = ref(false);

const handleOpen = () => {
    if (isOpening.value) return;
    isOpening.value = true;
    confetti({
        particleCount: 80,
        spread: 90,
        origin: { y: 0.7 },
        colors: ['#D4AF37', '#353A20', '#FFFFFF', '#EDEFEA']
    });

    setTimeout(() => {
        emit('openInvitation');
    }, 400);
};

const handleDesktopScrollToDetails = () => {
    const el = document.getElementById('detailsTarget');
    if (el) {
        el.scrollIntoView({ behavior: 'smooth' });
    } else {
        window.scrollBy({ top: 600, behavior: 'smooth' });
    }
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
    return d || 'Sabtu, 19 Desember 2026';
};

// Copy bank account number feedback
const copiedBankIndex = ref<number | null>(null);
const copyBankNumber = (num: string, index: number) => {
    navigator.clipboard.writeText(num);
    copiedBankIndex.value = index;
    setTimeout(() => {
        copiedBankIndex.value = null;
    }, 2500);
};

// Gallery lightbox
const activeLightboxImg = ref<string | null>(null);
const openLightbox = (url: string) => {
    activeLightboxImg.value = url;
};
const closeLightbox = () => {
    activeLightboxImg.value = null;
};

// QRIS modal
const handleOpenQris = (acc: any) => {
    if (typeof props.openQris === 'function') {
        props.openQris(acc);
    }
};

// Google Calendar URL
const googleCalendarUrl = computed(() => {
    const title = encodeURIComponent((weddingTitle.value || 'Wedding') + ' Celebration');
    const firstEvent = props.events?.[0];
    const location = encodeURIComponent((firstEvent?.venue_name || '') + ' - ' + (firstEvent?.address || ''));
    return `https://www.google.com/calendar/render?action=TEMPLATE&text=${title}&location=${location}`;
});

// Computed content fields
const bride = computed(() => props.invitationData?.content?.bride || {});
const groom = computed(() => props.invitationData?.content?.groom || {});
const weddingTitle = computed(() => {
    if (groom.value?.nickname && bride.value?.nickname) {
        return `${groom.value.nickname} & ${bride.value.nickname}`;
    }
    return props.invitationData?.content?.weddingTitle || 'Anna & Jeremiah';
});

const monogramInitials = computed(() => {
    const g = (groom.value?.nickname || groom.value?.fullName || 'J').charAt(0).toUpperCase();
    const b = (bride.value?.nickname || bride.value?.fullName || 'A').charAt(0).toUpperCase();
    return `${b}${g}`;
});

const weddingHashtag = computed(() => {
    const g = groom.value?.nickname || 'Jeremiah';
    const b = bride.value?.nickname || 'Anna';
    return `${b}To${g}`;
});

const weddingDate = computed(() => props.invitationData?.content?.weddingDateText || 'Sabtu, 19 Desember 2026');
const coverImg = computed(() => props.invitationData?.content?.coverImage || 'https://images.unsplash.com/photo-1511285560929-80b456fea0bc?auto=format&fit=crop&w=1000&q=80');

const galleryImages = computed(() => {
    const imgs = props.invitationData?.content?.gallery;
    if (Array.isArray(imgs) && imgs.length > 0) {
        return imgs;
    }
    return [
        coverImg.value,
        'https://images.unsplash.com/photo-1519741497674-611481863552?auto=format&fit=crop&w=800&q=80',
        'https://images.unsplash.com/photo-1520854221256-17451cc331bf?auto=format&fit=crop&w=800&q=80',
        'https://images.unsplash.com/photo-1465495976277-4387d4b0b4c6?auto=format&fit=crop&w=800&q=80'
    ];
});

const defaultQuote = "In all the world, there is no heart for me like yours. In all the world, there is no love for you like mine.";
const defaultQuoteSource = "Maya Angelou";

const loveStoryList = computed(() => {
    if (Array.isArray(props.invitationData?.content?.story) && props.invitationData.content.story.length > 0) {
        return props.invitationData.content.story;
    }
    return [
        {
            year: '2021',
            title: 'First Encounter',
            description: 'A chance meeting at an art exhibition where a simple conversation sparked a journey neither of us expected.',
            image: 'https://images.unsplash.com/photo-1522673607200-164d1b6ce486?auto=format&fit=crop&w=800&q=80'
        },
        {
            year: '2024',
            title: 'The Unforgettable Proposal',
            description: 'Underneath a canopy of fairy lights and starlit skies, a question was asked and an eternal "Yes" was whispered.',
            image: 'https://images.unsplash.com/photo-1515934751635-c81c6bc9a2d8?auto=format&fit=crop&w=800&q=80'
        }
    ];
});
</script>

<template>
    <Head>
        <link rel="stylesheet" href="/css/templates/katsudoto-global.css">
        <link rel="stylesheet" href="/css/templates/eloise.css">
    </Head>

    <div class="kat-page__side-to-side eloise-romance-page min-h-screen">

        <!-- ========================================== -->
        <!-- PRIMARY PANE (Fixed Left Side on Desktop 61%) -->
        <!-- ========================================== -->
        <section class="primary-pane">
            <div class="bg-pp"></div>
            <div class="inner relative">

                <!-- Authentic Sparkle Fairy Dust Canvas Over Desktop Primary Pane -->
                <EloiseFairyDustCanvas :count="48" className="z-10" />

                <div class="primary-pane-content relative z-20">

                    <!-- Head & VIP Badge -->
                    <div class="tc-head">
                        <div class="invitation-category vip">
                            <div class="category-icon">
                                <svg class="icon ic-filled ic-star" xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="#D4AF37" viewBox="0 0 256 256">
                                    <path d="M234.29,114.85l-45,38.83L203,211.75a16.4,16.4,0,0,1-24.5,17.82L128,198.49,77.47,229.57A16.4,16.4,0,0,1,53,211.75l13.76-58.07-45-38.83A16.46,16.46,0,0,1,31.08,86l59-4.76,22.76-55.08a16.36,16.36,0,0,1,30.27,0l22.75,55.08,59,4.76a16.46,16.46,0,0,1,9.37,28.86Z"></path>
                                </svg>
                            </div>
                            <p class="category-label">VIP</p>
                        </div>
                        <h1 class="top-cover-title">{{ weddingTitle }}</h1>
                    </div>

                    <!-- Arched Frame with Couple Photo & Ornaments -->
                    <div class="tc-frame">
                        <div class="orn-tc-frame">
                            <div class="image-wrap">
                                <img src="/images/templates/eloise/Orn-33.png" alt="Ornaments">
                            </div>
                        </div>

                        <div class="orn-tc-2">
                            <div class="image-wrap">
                                <img src="/images/templates/eloise/Orn-19.png" alt="Ornaments">
                            </div>
                        </div>

                        <div class="orn-tc-1">
                            <div class="image-wrap">
                                <img src="/images/templates/eloise/Orn-13.png" alt="Ornaments">
                            </div>
                            <div class="orn-tc-1-1">
                                <div class="image-wrap">
                                    <img src="/images/templates/eloise/Orn-31.png" alt="Ornaments">
                                </div>
                            </div>
                        </div>

                        <!-- Polaroid Couple Frame with Clickable Trigger -->
                        <div class="orn-tc-photo cursor-pointer" @click="handleDesktopScrollToDetails">
                            <div class="image-wrap">
                                <img src="/images/templates/eloise/frame-tc.png" alt="Frame">
                            </div>
                            <div class="top-cover-highlight">
                                <div class="preview-container cover-show" id="cover-pane">
                                    <div class="picture desktop h-full w-full">
                                        <img :src="coverImg" alt="Couple" class="w-full h-full object-cover">
                                    </div>
                                </div>
                            </div>
                            <div class="orn-tc-click">
                                <div class="image-wrap">
                                    <img src="/images/templates/eloise/Orn-click.png" alt="Click to see details">
                                </div>
                            </div>
                        </div>

                        <!-- Greeting Text in Envelope Card -->
                        <div class="tc-frame-content">
                            <div class="content">
                                <p class="bottom-text">{{ guestName ? 'Hai ' + guestName : 'Hai Tamu Spesial' }}</p>
                            </div>
                        </div>

                        <!-- Green Envelope Flap with Flowers & Pearls -->
                        <div class="image-wrap pointer-events-none">
                            <img src="/images/templates/eloise/Orn-01.png" alt="Envelope">
                        </div>

                        <div class="orn-tc-4">
                            <div class="image-wrap">
                                <img src="/images/templates/eloise/Orn-17.png" alt="Ornaments">
                            </div>
                        </div>
                        <div class="orn-tc-3">
                            <div class="image-wrap">
                                <img src="/images/templates/eloise/Orn-34.png" alt="Ornaments">
                            </div>
                        </div>
                        <!-- Wax Seal with Monogram -->
                        <div class="orn-tc-5">
                            <div class="image-wrap">
                                <img src="/images/templates/eloise/Orn-08.png" alt="Seal">
                            </div>
                            <div class="orn-ww-logo flex items-center justify-center">
                                <span class="text-sm font-serif font-bold text-[#F5F0E8] tracking-widest">{{ monogramInitials }}</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- ========================================== -->
        <!-- MOBILE FLOATING FAIRY DUST PARTICLES -->
        <!-- Visible on mobile devices so effects shine! -->
        <!-- ========================================== -->
        <div class="block lg:hidden fixed inset-0 pointer-events-none z-30">
            <EloiseFairyDustCanvas :count="35" />
        </div>

        <!-- ========================================== -->
        <!-- MOBILE TOP COVER (Opening Cover Modal for Mobile) -->
        <!-- ========================================== -->
        <section
            class="top-cover lg:!hidden"
            :class="{ 'hide': isOpen }"
            data-section-order="opening_cover"
        >
            <div class="bg-cover"></div>
            <div class="top-cover-inner">
                <div class="top-cover-details">
                    <div class="tc-head text-center">
                        <div class="invitation-category vip mx-auto mb-2">
                            <div class="category-icon">
                                <svg class="icon ic-filled ic-star" xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="#D4AF37" viewBox="0 0 256 256">
                                    <path d="M234.29,114.85l-45,38.83L203,211.75a16.4,16.4,0,0,1-24.5,17.82L128,198.49,77.47,229.57A16.4,16.4,0,0,1,53,211.75l13.76-58.07-45-38.83A16.46,16.46,0,0,1,31.08,86l59-4.76,22.76-55.08a16.36,16.36,0,0,1,30.27,0l22.75,55.08,59,4.76a16.46,16.46,0,0,1,9.37,28.86Z"></path>
                                </svg>
                            </div>
                            <p class="category-label text-xs">VIP</p>
                        </div>
                        <h1 class="top-cover-title text-4xl font-serif text-[#353A20]">{{ weddingTitle }}</h1>
                    </div>

                    <!-- Mobile Arched Envelope Frame with Lilies & Pearls -->
                    <div class="tc-frame my-4">
                        <div class="orn-tc-frame">
                            <div class="image-wrap">
                                <img src="/images/templates/eloise/Orn-33.png" alt="Ornaments">
                            </div>
                        </div>

                        <div class="orn-tc-2">
                            <div class="image-wrap">
                                <img src="/images/templates/eloise/Orn-19.png" alt="Ornaments">
                            </div>
                        </div>

                        <div class="orn-tc-1">
                            <div class="image-wrap">
                                <img src="/images/templates/eloise/Orn-13.png" alt="Ornaments">
                            </div>
                            <div class="orn-tc-1-1">
                                <div class="image-wrap">
                                    <img src="/images/templates/eloise/Orn-31.png" alt="Ornaments">
                                </div>
                            </div>
                        </div>

                        <div class="orn-tc-photo cursor-pointer" @click="handleOpen">
                            <div class="image-wrap">
                                <img src="/images/templates/eloise/frame-tc.png" alt="Frame">
                            </div>
                            <div class="top-cover-highlight">
                                <div class="preview-container cover-show">
                                    <img :src="coverImg" alt="Cover" class="w-full h-full object-cover">
                                </div>
                            </div>
                            <div class="orn-tc-click">
                                <div class="image-wrap">
                                    <img src="/images/templates/eloise/Orn-click.png" alt="Click to see details">
                                </div>
                            </div>
                        </div>

                        <div class="tc-frame-content">
                            <div class="content">
                                <p class="bottom-text">{{ guestName ? 'Hai ' + guestName : 'Hai Tamu Spesial' }}</p>
                            </div>
                        </div>

                        <div class="image-wrap pointer-events-none">
                            <img src="/images/templates/eloise/Orn-01.png" alt="Envelope">
                        </div>

                        <div class="orn-tc-4">
                            <div class="image-wrap">
                                <img src="/images/templates/eloise/Orn-17.png" alt="Ornaments">
                            </div>
                        </div>
                        <div class="orn-tc-3">
                            <div class="image-wrap">
                                <img src="/images/templates/eloise/Orn-34.png" alt="Ornaments">
                            </div>
                        </div>

                        <!-- Wax Seal -->
                        <div class="orn-tc-5">
                            <div class="image-wrap">
                                <img src="/images/templates/eloise/Orn-08.png" alt="Seal">
                            </div>
                            <div class="orn-ww-logo flex items-center justify-center">
                                <span class="text-sm font-serif font-bold text-[#F5F0E8] tracking-widest">{{ monogramInitials }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Open Button -->
                    <div class="text-center px-4 mt-2">
                        <button
                            @click="handleOpen"
                            class="inline-flex items-center gap-3 px-8 py-3 rounded-full bg-[#353A20] text-[#F5F0E8] font-serif text-sm tracking-wider shadow-xl hover:bg-[#474c2e] hover:scale-105 transition-all duration-300 active:scale-95"
                        >
                            <span>Click to open invitation</span>
                        </button>
                    </div>

                </div>
            </div>
        </section>

        <!-- ========================================== -->
        <!-- SECONDARY PANE (Scrollable Right Side / Mobile Stack) -->
        <!-- ========================================== -->
        <section class="secondary-pane min-h-screen">

            <!-- 1. MAIN COVER (Hero Cover with Oval Lace Frame) -->
            <section class="cover" data-section-order="cover" id="detailsTarget">
                <div class="ornaments-wrapper"></div>
                <div class="bg-cover"></div>
                <div class="inner">
                    <div class="head">
                        <div class="logo-wrap mb-2">
                            <span class="font-serif text-3xl font-bold italic tracking-widest text-[#353A20]">{{ monogramInitials }}</span>
                        </div>
                        <p class="font-sans text-xs tracking-widest uppercase text-[#56584B]">Wedding Invitation</p>
                    </div>

                    <!-- Oval Lace Carved Border with Masked Photo -->
                    <div class="body highlight my-6">
                        <div class="orn-cover-frame mx-auto max-w-[320px] relative">
                            <div class="cover-frame" id="coverFrame">
                                <div class="cover-picture cover-show" id="cover-main">
                                    <div class="picture desktop h-full w-full">
                                        <img :src="coverImg" alt="Cover" class="w-full h-full object-cover">
                                    </div>
                                    <div class="picture mobile h-full w-full">
                                        <img :src="coverImg" alt="Cover" class="w-full h-full object-cover">
                                    </div>
                                </div>
                            </div>
                            <div class="image-wrap pointer-events-none relative z-10">
                                <img src="/images/templates/eloise/frame-cover.png" alt="Cover Frame" class="w-full h-auto">
                            </div>
                        </div>
                    </div>

                    <div class="head bot text-center mt-6">
                        <h1 class="text-4xl font-serif text-[#353A20]">{{ weddingTitle }}</h1>
                        <p class="text-xs font-sans tracking-widest uppercase text-[#8A7B6B] mt-1 font-semibold">#{{ weddingHashtag }}</p>
                    </div>

                    <div class="ornaments-wrapper"></div>
                </div>
            </section>

            <!-- 2. QUOTE SECTION -->
            <section class="quote-wrap py-12 px-6 bg-[#FAF7F2]" data-section-order="quote">
                <div class="ornaments-wrapper">
                    <div class="text-center mb-3">
                        <img src="/images/templates/eloise/Orn-02.png" alt="Flourish" class="mx-auto w-12 opacity-70">
                    </div>
                </div>
                <div class="p-relative quote-frame-wrap text-center px-6">
                    <div class="quote max-w-md mx-auto">
                        <p class="quote-caption font-serif text-lg leading-relaxed text-[#353A20] italic">
                            "{{ props.invitationData?.content?.quote || defaultQuote }}"
                        </p>
                        <p class="quote-source font-sans text-xs tracking-widest uppercase text-[#56584B] mt-4 font-semibold">
                            — {{ props.invitationData?.content?.quoteSource || defaultQuoteSource }}
                        </p>
                    </div>
                </div>
                <div class="text-center mt-3">
                    <img src="/images/templates/eloise/Orn-03.png" alt="Flourish" class="mx-auto w-12 opacity-70">
                </div>
            </section>

            <!-- 3. GROOM & BRIDE (Couple Profiles) -->
            <section class="couple-wrap py-12 px-4 bg-[#FFFFFF]" data-section-order="couple">
                <div class="couple">
                    <div class="couple-head text-center px-4 mb-8">
                        <h1 class="couple-title text-4xl font-serif text-[#353A20]">Groom and Bride</h1>
                        <p class="couple-description text-sm text-[#56584B] max-w-sm mx-auto mt-2 italic">
                            Every love story is unique, and ours has led us to this unforgettable day. With joyful hearts, we invite you to celebrate our union.
                        </p>
                    </div>

                    <!-- Groom & Bride Photo Portraits with botanical frames -->
                    <div class="couple-picture-outer flex justify-center items-center gap-4 my-6">
                        <!-- Groom Portrait -->
                        <div class="couple-preview groom">
                            <div class="couple-frame relative">
                                <div class="image-wrap">
                                    <img src="/images/templates/eloise/frame-couple.png" class="img-couple-frame" alt="Frame">
                                </div>
                                <div class="couple-picture-wrap">
                                    <div class="couple-picture">
                                        <img :src="groom.photo || coverImg" class="img w-full h-full object-cover rounded-full" alt="Groom">
                                    </div>
                                </div>
                            </div>
                            <div class="orn-cp-2">
                                <div class="image-wrap">
                                    <img src="/images/templates/eloise/Orn-12.png" alt="Ornaments">
                                </div>
                            </div>
                        </div>

                        <!-- Center Flourish -->
                        <div class="orn-cp-1">
                            <div class="image-wrap">
                                <img src="/images/templates/eloise/Orn-10.png" alt="Ornaments">
                            </div>
                        </div>

                        <!-- Bride Portrait -->
                        <div class="couple-preview bride">
                            <div class="orn-cp-3">
                                <div class="image-wrap">
                                    <img src="/images/templates/eloise/Orn-13.png" alt="Ornaments">
                                </div>
                            </div>
                            <div class="couple-frame relative">
                                <div class="image-wrap">
                                    <img src="/images/templates/eloise/frame-couple.png" class="img-couple-frame" alt="Frame">
                                </div>
                                <div class="couple-picture-wrap">
                                    <div class="couple-picture">
                                        <img :src="bride.photo || coverImg" class="img w-full h-full object-cover rounded-full" alt="Bride">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Couple Info Text & Parent Details -->
                    <div class="couple-body show-picture">
                        <!-- Groom Info -->
                        <div class="couple-info groom">
                            <div class="couple-details text-center">
                                <p class="couple-role">The Groom</p>
                                <h2 class="couple-name text-[#353A20]">{{ groom.fullName || groom.nickname || 'Jeremiah Sanjaya' }}</h2>
                                <p class="couple-parents text-[#56584B]">{{ groom.parents || 'The Son of Mr. Dudley Sanjaya & Mrs. Henrietta Smith' }}</p>
                                <div v-if="groom.instagram" class="couple-link-wrap mt-3">
                                    <a :href="'https://instagram.com/' + groom.instagram.replace('@', '')" target="_blank" class="couple-link text-[#353A20]">
                                        {{ groom.instagram }}
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Ampersand -->
                        <div class="separator-wrap">
                            <div class="separator">
                                <h2 class="couple-separator text-[#353A20]">&amp;</h2>
                            </div>
                        </div>

                        <!-- Bride Info -->
                        <div class="couple-info bride">
                            <div class="couple-details text-center">
                                <p class="couple-role">The Bride</p>
                                <h2 class="couple-name text-[#353A20]">{{ bride.fullName || bride.nickname || 'Anna Celastine' }}</h2>
                                <p class="couple-parents text-[#56584B]">{{ bride.parents || 'The Daughter of Mr. Jeremy Georgio & Mrs. Kelly Frenny' }}</p>
                                <div v-if="bride.instagram" class="couple-link-wrap mt-3">
                                    <a :href="'https://instagram.com/' + bride.instagram.replace('@', '')" target="_blank" class="couple-link text-[#353A20]">
                                        {{ bride.instagram }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </section>

            <!-- 4. LOVE STORY (Timeline) -->
            <section class="love-story-outer py-12 px-4 bg-[#FAF7F2]" data-section-order="love_story">
                <div class="text-center mb-8">
                    <img src="/images/templates/eloise/Orn-07.png" alt="Ornaments" class="mx-auto w-10 mb-2">
                    <h2 class="text-3xl font-serif text-[#353A20]">Our Love Story</h2>
                    <p class="text-xs text-[#56584B] uppercase tracking-widest mt-1">A Journey Towards Forever</p>
                </div>

                <div class="space-y-8 max-w-md mx-auto">
                    <div
                        v-for="(chapter, idx) in loveStoryList"
                        :key="idx"
                        class="bg-[#FFFFFF] border border-[#E5DECF] rounded-2xl p-5 shadow-sm relative overflow-hidden"
                    >
                        <div class="flex items-center justify-between mb-3 border-b border-[#F0EAE1] pb-2">
                            <span class="text-xs font-sans uppercase tracking-widest text-[#8A7B6B] font-semibold">{{ chapter.year || 'Chapter ' + (idx + 1) }}</span>
                            <span class="text-sm font-serif font-bold text-[#353A20]">{{ chapter.title }}</span>
                        </div>
                        <div v-if="chapter.image" class="mb-4 rounded-xl overflow-hidden aspect-[16/10] bg-[#353A20]/10">
                            <img :src="chapter.image" :alt="chapter.title" class="w-full h-full object-cover">
                        </div>
                        <p class="text-sm font-serif text-[#56584B] leading-relaxed italic">
                            {{ chapter.description }}
                        </p>
                    </div>
                </div>
            </section>

            <!-- 5. SAVE THE DATE & COUNTDOWN -->
            <section class="save-date-wrap bg-[#FFFFFF]" data-section-order="save_the_date">
                <div class="save-date-frame">
                    <div class="ornaments-wrapper">
                        <div class="orn-sd-3">
                            <div class="image-wrap">
                                <img src="/images/templates/eloise/Orn-14.png" alt="Ornaments">
                            </div>
                        </div>
                    </div>

                    <div class="save-date-inner text-center px-4">
                        <div class="head mb-6">
                            <img src="/images/templates/eloise/Orn-03.png" alt="Ornaments" class="mx-auto w-12 mb-3">
                            <h2 class="save-date-title text-3xl font-serif text-[#353A20]">Save The Date</h2>
                            <p class="save-date-caption text-sm text-[#56584B] italic mt-1">{{ weddingDate }}</p>
                        </div>

                        <!-- Live Countdown Timer -->
                        <div class="grid grid-cols-4 gap-2 max-w-xs mx-auto my-8">
                            <div class="bg-[#353A20] text-[#F5F0E8] p-3 rounded-xl shadow-md text-center">
                                <span class="block text-2xl font-serif font-bold">{{ timeLeft.days }}</span>
                                <span class="text-[10px] uppercase tracking-wider opacity-80">Hari</span>
                            </div>
                            <div class="bg-[#353A20] text-[#F5F0E8] p-3 rounded-xl shadow-md text-center">
                                <span class="block text-2xl font-serif font-bold">{{ timeLeft.hours }}</span>
                                <span class="text-[10px] uppercase tracking-wider opacity-80">Jam</span>
                            </div>
                            <div class="bg-[#353A20] text-[#F5F0E8] p-3 rounded-xl shadow-md text-center">
                                <span class="block text-2xl font-serif font-bold">{{ timeLeft.minutes }}</span>
                                <span class="text-[10px] uppercase tracking-wider opacity-80">Menit</span>
                            </div>
                            <div class="bg-[#353A20] text-[#F5F0E8] p-3 rounded-xl shadow-md text-center">
                                <span class="block text-2xl font-serif font-bold">{{ timeLeft.seconds }}</span>
                                <span class="text-[10px] uppercase tracking-wider opacity-80">Detik</span>
                            </div>
                        </div>

                        <!-- Google Calendar Button -->
                        <div class="mt-6">
                            <a
                                :href="googleCalendarUrl"
                                target="_blank"
                                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-full border border-[#353A20] text-[#353A20] text-xs font-sans font-semibold tracking-wider uppercase hover:bg-[#353A20] hover:text-[#F5F0E8] transition-all"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span>Simpan ke Kalender</span>
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 6. AGENDA ACARA (Our Special Day) -->
            <section class="agenda-wrap py-12 bg-[#FAF7F2]" data-section-order="event">
                <div class="agenda-inner px-4">
                    <div class="agenda-head text-center mb-8">
                        <img src="/images/templates/eloise/Orn-03.png" alt="Ornaments" class="mx-auto w-10 mb-2">
                        <h2 class="agenda-title text-3xl font-serif text-[#353A20]">Our Special Day</h2>
                        <p class="agenda-description text-sm text-[#56584B] italic max-w-sm mx-auto mt-1">
                            With grateful hearts, we invite you to witness and celebrate the beginning of our forever.
                        </p>
                    </div>

                    <!-- Event Cards Loop -->
                    <div class="agenda-body space-y-10 max-w-md mx-auto">
                        <div
                            v-for="(event, eIdx) in props.events"
                            :key="event.id"
                            class="event-item relative bg-[#FFFFFF] border border-[#E5DECF] rounded-3xl p-6 shadow-md overflow-hidden text-center"
                        >
                            <!-- Botanical Corner Ornament -->
                            <div class="absolute -top-3 -right-3 w-16 opacity-40 pointer-events-none">
                                <img src="/images/templates/eloise/Orn-17.png" alt="Floral">
                            </div>

                            <div class="event-head mb-4">
                                <div class="orn-ww-head mb-2">
                                    <img src="/images/templates/eloise/Orn-03.png" alt="Orn" class="mx-auto w-8">
                                </div>
                                <h3 class="text-xl font-serif font-bold text-[#353A20]">{{ event.name }}</h3>
                                <p class="text-sm font-sans font-medium text-[#8A7B6B] mt-1">
                                    {{ safeFormatDisplayDate(event.starts_at) }}
                                </p>
                                <p class="text-xs font-sans text-[#353A20] font-semibold mt-1">
                                    {{ safeFormatEventTime(event.starts_at, event.ends_at, event.timezone) }}
                                </p>
                            </div>

                            <div class="activity-details border-t border-[#F0EAE1] pt-4">
                                <p class="text-base font-serif font-bold text-[#353A20]">{{ event.venue_name }}</p>
                                <p v-if="event.address" class="text-xs text-[#56584B] mt-1 leading-relaxed">{{ event.address }}</p>

                                <div v-if="event.maps_url" class="mt-5">
                                    <a
                                        :href="event.maps_url"
                                        target="_blank"
                                        class="inline-flex items-center gap-2 px-5 py-2 rounded-full bg-[#353A20] text-[#F5F0E8] text-xs font-sans font-medium tracking-wider uppercase shadow-md hover:bg-[#474C2E] transition-all"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        <span>Petunjuk Arah (Maps)</span>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Dresscode Section -->
                        <div class="dress-wrapper bg-[#353A20] text-[#F5F0E8] rounded-3xl p-6 shadow-md text-center">
                            <h3 class="text-xl font-serif mb-1">Dresscode</h3>
                            <p class="text-xs text-[#EDEFEA]/80 italic mb-5">We kindly ask that guests please attend wearing our wedding colors.</p>
                            <div class="flex justify-center items-center gap-6">
                                <div class="text-center">
                                    <img src="/images/templates/eloise/ic-dress-man-garden.png" alt="Men" class="w-9 h-9 mx-auto mb-1 opacity-90 invert">
                                    <span class="text-xs font-sans tracking-wide">Pria: Formal / Batik</span>
                                </div>
                                <div class="h-8 w-px bg-white/20"></div>
                                <div class="text-center">
                                    <img src="/images/templates/eloise/ic-dress-woman-garden-party.png" alt="Women" class="w-9 h-9 mx-auto mb-1 opacity-90 invert">
                                    <span class="text-xs font-sans tracking-wide">Wanita: Garden Dress</span>
                                </div>
                            </div>
                            <div class="flex justify-center items-center gap-3 mt-5">
                                <div class="w-6 h-6 rounded-full bg-[#353A20] border-2 border-white shadow-sm" title="Forest Green"></div>
                                <div class="w-6 h-6 rounded-full bg-[#8A7B6B] border-2 border-white shadow-sm" title="Warm Taupe"></div>
                                <div class="w-6 h-6 rounded-full bg-[#D4AF37] border-2 border-white shadow-sm" title="Champagne Gold"></div>
                                <div class="w-6 h-6 rounded-full bg-[#F5F0E8] border-2 border-white shadow-sm" title="Ivory Cream"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 7. GALLERY (Portrait of Us) -->
            <section class="photo-wrap py-12 px-4 bg-[#FFFFFF]" data-section-order="gallery_photo">
                <div class="photo-inner max-w-md mx-auto">
                    <div class="photo-head text-center mb-8">
                        <img src="/images/templates/eloise/Orn-03.png" alt="Orn" class="mx-auto w-10 mb-2">
                        <h1 class="photo-title text-3xl font-serif text-[#353A20]">Portrait of Us</h1>
                        <p class="text-xs text-[#56584B] uppercase tracking-widest mt-1">Moments Frozen in Time</p>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div
                            v-for="(imgUrl, gIdx) in galleryImages"
                            :key="gIdx"
                            @click="openLightbox(imgUrl)"
                            class="group relative rounded-2xl overflow-hidden aspect-[4/5] bg-stone-200 cursor-pointer shadow-sm hover:shadow-lg transition-all duration-300"
                        >
                            <img
                                :src="imgUrl"
                                :alt="'Gallery ' + (gIdx + 1)"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                            >
                            <div class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 8. RSVP FORM -->
            <section class="rsvp-wrap py-12 px-4 bg-[#FAF7F2]" id="toRsvp" data-section-order="rsvp">
                <div class="max-w-md mx-auto bg-[#FFFFFF] border border-[#E5DECF] rounded-3xl p-6 shadow-md">
                    <div class="text-center mb-6">
                        <img src="/images/templates/eloise/Orn-07.png" alt="Orn" class="mx-auto w-10 mb-2">
                        <h2 class="text-3xl font-serif text-[#353A20]">Konfirmasi Kehadiran</h2>
                        <p class="text-xs text-[#56584B] italic mt-1">Mohon konfirmasikan kehadiran Anda demi kenyamanan acara.</p>
                    </div>

                    <form @submit.prevent="submitRsvp" class="space-y-4">
                        <div>
                            <label class="block text-xs font-sans font-semibold uppercase tracking-wider text-[#353A20] mb-1">Nama Lengkap</label>
                            <input
                                v-model="rsvpForm.guest_name"
                                type="text"
                                required
                                class="w-full px-4 py-2.5 rounded-xl border border-[#D5CDC0] bg-white text-sm text-[#353A20] focus:outline-none focus:border-[#353A20]"
                                placeholder="Masukkan nama Anda"
                            >
                        </div>

                        <div>
                            <label class="block text-xs font-sans font-semibold uppercase tracking-wider text-[#353A20] mb-1">Status Kehadiran</label>
                            <div class="grid grid-cols-2 gap-3">
                                <label
                                    class="flex items-center justify-center gap-2 p-3 rounded-xl border cursor-pointer text-sm font-medium transition-all"
                                    :class="rsvpForm.status === 'attending' ? 'bg-[#353A20] text-[#F5F0E8] border-[#353A20]' : 'border-[#D5CDC0] text-[#56584B]'"
                                >
                                    <input type="radio" v-model="rsvpForm.status" value="attending" class="hidden">
                                    <span>Hadir</span>
                                </label>
                                <label
                                    class="flex items-center justify-center gap-2 p-3 rounded-xl border cursor-pointer text-sm font-medium transition-all"
                                    :class="rsvpForm.status === 'not_attending' ? 'bg-[#8A7B6B] text-[#F5F0E8] border-[#8A7B6B]' : 'border-[#D5CDC0] text-[#56584B]'"
                                >
                                    <input type="radio" v-model="rsvpForm.status" value="not_attending" class="hidden">
                                    <span>Berhalangan</span>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-sans font-semibold uppercase tracking-wider text-[#353A20] mb-1">Jumlah Tamu</label>
                            <select
                                v-model="rsvpForm.guest_count"
                                class="w-full px-4 py-2.5 rounded-xl border border-[#D5CDC0] bg-white text-sm text-[#353A20] focus:outline-none focus:border-[#353A20]"
                            >
                                <option :value="1">1 Orang</option>
                                <option :value="2">2 Orang</option>
                                <option :value="3">3 Orang</option>
                                <option :value="4">4 Orang</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-sans font-semibold uppercase tracking-wider text-[#353A20] mb-1">Ucapan & Doa Restu</label>
                            <textarea
                                v-model="rsvpForm.notes"
                                rows="3"
                                class="w-full px-4 py-2.5 rounded-xl border border-[#D5CDC0] bg-white text-sm text-[#353A20] focus:outline-none focus:border-[#353A20]"
                                placeholder="Tuliskan ucapan dan doa terbaik Anda..."
                            ></textarea>
                        </div>

                        <div v-if="rsvpSuccess" class="p-3 bg-emerald-50 text-emerald-800 rounded-xl text-xs text-center border border-emerald-200">
                            Terima kasih, konfirmasi kehadiran Anda telah tersimpan!
                        </div>

                        <div v-if="rsvpErrorMessage" class="p-3 bg-rose-50 text-rose-800 rounded-xl text-xs text-center border border-rose-200">
                            {{ rsvpErrorMessage }}
                        </div>

                        <button
                            type="submit"
                            :disabled="rsvpSubmitting"
                            class="w-full py-3 rounded-xl bg-[#353A20] text-[#F5F0E8] font-sans text-xs font-semibold uppercase tracking-wider shadow-md hover:bg-[#474C2E] transition-all disabled:opacity-50"
                        >
                            <span v-if="rsvpSubmitting">Mengirimkan Konfirmasi...</span>
                            <span v-else>Kirim Konfirmasi</span>
                        </button>
                    </form>
                </div>
            </section>

            <!-- 9. WEDDING GIFT (Amplop Digital & Kado) -->
            <section class="wedding-gift-wrap py-12 px-4 bg-[#FFFFFF]" data-section-order="wedding_gift">
                <div class="wedding-gift-inner max-w-md mx-auto">
                    <div class="wedding-gift-head text-center mb-8">
                        <img src="/images/templates/eloise/Orn-03.png" alt="Orn" class="mx-auto w-10 mb-2">
                        <h1 class="wedding-gift-title text-3xl font-serif text-[#353A20]">Wedding Gift</h1>
                        <p class="wedding-gift-description text-sm text-[#56584B] italic mt-1 max-w-sm mx-auto">
                            Doa restu Anda adalah karunia terindah bagi kami. Namun jika Anda ingin memberikan hadiah, kami menyediakan amplop digital berikut.
                        </p>
                    </div>

                    <!-- Digital Bank Accounts -->
                    <div class="space-y-4">
                        <div
                            v-for="(acc, bIdx) in (props.digitalGift?.accounts || [])"
                            :key="bIdx"
                            class="bg-[#FDFCFA] border border-[#E5DECF] rounded-2xl p-5 shadow-sm relative overflow-hidden"
                        >
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-sans font-bold uppercase tracking-widest text-[#8A7B6B]">{{ acc.bank || 'BANK' }}</span>
                                <span v-if="acc.qr_image" @click="handleOpenQris(acc)" class="cursor-pointer text-xs font-sans text-[#353A20] underline font-semibold flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                                    </svg>
                                    <span>Tampilkan QRIS</span>
                                </span>
                            </div>

                            <p class="text-xs text-[#56584B]">Atas Nama:</p>
                            <p class="text-sm font-serif font-bold text-[#353A20] mb-3">{{ acc.account_name || weddingTitle }}</p>

                            <div class="flex items-center justify-between bg-[#F5F0E8] px-4 py-2.5 rounded-xl border border-[#E5DECF]">
                                <span class="font-mono text-sm font-bold text-[#353A20]">{{ acc.account_number }}</span>
                                <button
                                    @click="copyBankNumber(acc.account_number, bIdx)"
                                    class="text-xs font-sans font-semibold uppercase tracking-wider text-[#353A20] hover:text-black transition-colors"
                                >
                                    {{ copiedBankIndex === bIdx ? 'Tersalin!' : 'Salin No. Rek' }}
                                </button>
                            </div>
                        </div>

                        <!-- Physical Gift Delivery Address -->
                        <div v-if="props.digitalGift?.physical_gift?.enabled" class="bg-[#FDFCFA] border border-[#E5DECF] rounded-2xl p-5 shadow-sm">
                            <h4 class="text-xs font-sans font-bold uppercase tracking-widest text-[#8A7B6B] mb-2">Kirim Kado Fisik</h4>
                            <p class="text-sm font-serif font-bold text-[#353A20]">{{ props.digitalGift.physical_gift.receiver_name }}</p>
                            <p class="text-xs text-[#56584B] mt-1">{{ props.digitalGift.physical_gift.address }}</p>
                            <p v-if="props.digitalGift.physical_gift.phone" class="text-xs text-[#56584B] mt-0.5">Telp: {{ props.digitalGift.physical_gift.phone }}</p>

                            <button
                                @click="props.copyAddress(props.digitalGift.physical_gift.address)"
                                class="mt-3 text-xs font-sans font-semibold uppercase tracking-wider text-[#353A20] underline"
                            >
                                Salin Alamat Pengiriman
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 10. GUESTBOOK WISHES -->
            <section class="wedding-wish-wrap py-12 px-4 bg-[#FAF7F2]" data-section-order="wedding_wish">
                <div class="wedding-wish-inner max-w-md mx-auto">
                    <div class="wedding-wish-head text-center mb-8">
                        <img src="/images/templates/eloise/Orn-07.png" alt="Orn" class="mx-auto w-10 mb-2">
                        <h1 class="wedding-wish-title text-3xl font-serif text-[#353A20]">Ucapan & Doa Restu</h1>
                        <p class="text-xs text-[#56584B] uppercase tracking-widest mt-1">Warm Wishes from Loved Ones</p>
                    </div>

                    <div class="space-y-4 max-h-[420px] overflow-y-auto pr-1">
                        <div
                            v-for="wish in props.guestbook"
                            :key="wish.id"
                            class="bg-[#FFFFFF] border border-[#E5DECF] rounded-2xl p-4 shadow-sm"
                        >
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="text-sm font-serif font-bold text-[#353A20]">{{ wish.guest_name }}</span>
                                <span class="text-[10px] font-sans text-[#8A7B6B]">{{ wish.created_at }}</span>
                            </div>
                            <p class="text-xs font-serif text-[#56584B] leading-relaxed italic">
                                "{{ wish.message }}"
                            </p>
                        </div>

                        <div v-if="!props.guestbook || props.guestbook.length === 0" class="text-center py-8 text-xs text-[#8A7B6B] italic">
                            Belum ada ucapan doa restu. Jadilah yang pertama memberikan ucapan!
                        </div>
                    </div>
                </div>
            </section>

            <!-- 11. THANK YOU & FOOTNOTE -->
            <section class="ty-wrap py-16 px-4 bg-[#FFFFFF]" data-section-order="greet_thanks">
                <div class="ty text-center max-w-md mx-auto">
                    <div class="tys-frame relative py-12 px-6 bg-[#353A20] text-[#F5F0E8] rounded-3xl shadow-xl overflow-hidden">
                        <!-- Floral Ornaments Inside Thank You Card -->
                        <div class="absolute -top-6 -left-6 w-24 opacity-30 pointer-events-none">
                            <img src="/images/templates/eloise/Orn-20.png" alt="Floral">
                        </div>
                        <div class="absolute -bottom-6 -right-6 w-24 opacity-30 pointer-events-none">
                            <img src="/images/templates/eloise/Orn-22.png" alt="Floral">
                        </div>

                        <img src="/images/templates/eloise/Orn-08.png" alt="Ornaments" class="mx-auto w-10 mb-4 opacity-80">

                        <h2 class="text-3xl font-serif mb-4">Terima Kasih</h2>
                        <p class="text-xs text-[#EDEFEA]/85 italic leading-relaxed max-w-xs mx-auto mb-6">
                            Merupakan suatu kehormatan dan kebahagiaan bagi kami apabila Bapak/Ibu/Saudara/i berkenan hadir dan memberikan doa restu bagi kami.
                        </p>

                        <div class="mt-6 border-t border-white/20 pt-4">
                            <p class="text-xs uppercase tracking-widest text-[#EDEFEA]/70 mb-1">Kami yang berbahagia,</p>
                            <h3 class="text-2xl font-serif font-bold text-[#F5F0E8]">{{ weddingTitle }}</h3>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="footer-inner mt-12 text-center text-xs text-[#8A7B6B]">
                        <p>© {{ new Date().getFullYear() }} {{ weddingTitle }} Wedding. Powered by UndanganDigital.</p>
                    </div>
                </div>
            </section>

        </section>

        <!-- ========================================== -->
        <!-- FLOATING MUSIC BUTTON -->
        <!-- ========================================== -->
        <div class="music-outer">
            <div
                @click="emit('toggleMusic')"
                class="music-box flex items-center justify-center shadow-2xl transition-transform active:scale-90"
                :class="{ 'playing': props.isPlayingMusic }"
                title="Putar / Hentikan Musik"
            >
                <svg v-if="props.isPlayingMusic" class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM7 8a1 1 0 012 0v4a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v4a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                <svg v-else class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
                </svg>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- LIGHTBOX MODAL FOR GALLERY -->
        <!-- ========================================== -->
        <Teleport to="body">
            <div
                v-if="activeLightboxImg"
                @click="closeLightbox"
                class="fixed inset-0 z-[9999] bg-black/90 backdrop-blur-sm flex items-center justify-center p-4 cursor-pointer"
            >
                <div class="relative max-w-4xl max-h-[90vh] overflow-hidden rounded-xl">
                    <img :src="activeLightboxImg" alt="Enlarged" class="w-full h-full object-contain">
                    <button
                        @click="closeLightbox"
                        class="absolute top-4 right-4 bg-white/20 text-white rounded-full p-2 hover:bg-white/40 transition-colors"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </Teleport>

    </div>
</template>

<style scoped>
.eloise-romance-page {
    position: relative;
    width: 100%;
}
</style>

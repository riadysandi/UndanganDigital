<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
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
    formatDisplayDate: (d: string) => string;
    formatEventTime: (s: string, e: string | null, tz: string) => string;
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

// Lightbox state for gallery
const activeLightboxImg = ref<string | null>(null);
const openLightbox = (url: string) => {
    activeLightboxImg.value = url;
};
const closeLightbox = () => {
    activeLightboxImg.value = null;
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

// Living 3D photo URL (uses authentic moving cinemagraph)
const livingPhoto = computed(() => {
    return '/images/templates/ningrum/couple-3d.gif';
});

// Google Calendar URL
const googleCalendarUrl = computed(() => {
    const title = encodeURIComponent(props.invitationData?.content?.weddingTitle + ' Wedding');
    const firstEvent = props.events?.[0];
    const location = encodeURIComponent((firstEvent?.venue_name || '') + ' - ' + (firstEvent?.address || ''));
    return `https://www.google.com/calendar/render?action=TEMPLATE&text=${title}&location=${location}`;
});

const handleOpenInvitation = () => {
    emit('openInvitation');
    confetti({
        particleCount: 70,
        spread: 80,
        origin: { y: 0.6 },
        colors: ['#D4AF37', '#FBEFD3', '#AA771C', '#48110C']
    });
};
</script>

<template>
    <Head>
        <!-- Authentic Google Fonts for Katsudoto Ningrum -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cormorant+Upright:wght@300;400;500;600;700&family=Ovo&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap">
        <!-- Katsudoto Global & Ningrum Authentic Stylesheets -->
        <link rel="stylesheet" href="/css/templates/katsudoto-global.css">
        <link rel="stylesheet" href="/css/templates/ningrum.css">
    </Head>

    <div class="kat-page__side-to-side ningrum original preset-original selection:bg-amber-400 selection:text-stone-900">

        <!-- ======================================================== -->
        <!-- 1. DESKTOP PRIMARY PANE (Fixed Left 61% on Desktop)      -->
        <!-- ======================================================== -->
        <section class="primary-pane">
            <div class="inner">
                <!-- Ornaments Behind Frame -->
                <div class="ornaments-wrapper">
                    <div class="orn-pp-7">
                        <div class="image-wrap">
                            <img src="/images/templates/ningrum/Orn-22.png" alt="Ornaments">
                        </div>
                    </div>
                    <div class="orn-pp-6">
                        <div class="image-wrap">
                            <img src="/images/templates/ningrum/Orn-50.png" alt="Ornaments">
                        </div>
                    </div>
                    <div class="orn-pp-3">
                        <div class="image-wrap">
                            <img src="/images/templates/ningrum/Orn-51.png" alt="Ornaments">
                        </div>
                    </div>
                </div>

                <div class="inner-wrapper">
                    <!-- Head: VIP Badge & Title -->
                    <div class="pp-head">
                        <div class="invitation-category vip">
                            <div class="category-icon">
                                <svg class="icon ic-filled ic-star" xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="#000000" viewBox="0 0 256 256">
                                    <path d="M234.29,114.85l-45,38.83L203,211.75a16.4,16.4,0,0,1-24.5,17.82L128,198.49,77.47,229.57A16.4,16.4,0,0,1,53,211.75l13.76-58.07-45-38.83A16.46,16.46,0,0,1,31.08,86l59-4.76,22.76-55.08a16.36,16.36,0,0,1,30.27,0l22.75,55.08,59,4.76a16.46,16.46,0,0,1,9.37,28.86Z"></path>
                                </svg>
                            </div>
                            <p class="category-label">VIP</p>
                        </div>
                        <h1 class="top-cover-title">{{ invitationData.content.weddingTitle }}</h1>
                    </div>

                    <!-- Arch Frame with 3D Cinemagraph -->
                    <div class="pp-frame-wrapper">
                        <div class="body highlight">
                            <div class="pp-frame" id="ppFrame">
                                <div class="ornaments-wrapper">
                                    <div class="orn-tc-5">
                                        <div class="image-wrap">
                                            <img src="/images/templates/ningrum/Orn-37.png" alt="Ornaments">
                                        </div>
                                    </div>
                                </div>

                                <div class="preview-container cover-show" id="cover-primary">
                                    <div class="picture desktop">
                                        <img :src="livingPhoto" alt="Couple 3D">
                                    </div>
                                </div>

                                <div class="image-wrap">
                                    <img src="/images/templates/ningrum/frame-ff.png" alt="Cover Frame">
                                </div>

                                <div class="ornaments-wrapper">
                                    <div class="orn-tc-4">
                                        <div class="image-wrap">
                                            <img src="/images/templates/ningrum/Orn-25.png" alt="Ornaments">
                                        </div>
                                    </div>
                                    <div class="orn-pp-2">
                                        <div class="image-wrap">
                                            <img src="/images/templates/ningrum/Orn-12.png" alt="Ornaments">
                                        </div>
                                    </div>
                                    <div class="orn-pp-1">
                                        <div class="image-wrap">
                                            <img src="/images/templates/ningrum/Orn-35.png" alt="Ornaments">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ======================================================== -->
        <!-- 2. SECONDARY PANE (Right 39% on Desktop, 100% Mobile)    -->
        <!-- ======================================================== -->
        <main class="secondary-pane">

            <!-- ==================================================== -->
            <!-- TOP COVER (Opening Screen on Mobile)                 -->
            <!-- ==================================================== -->
            <section class="top-cover" :class="{ 'hide': isOpen }" data-section-order="opening_cover">
                <!-- Rising Smoke Urn Background Effect -->
                <div class="tc-animation-wrap">
                    <div class="bg-tca"></div>
                    <div class="ornaments-wrapper">
                        <div class="orn-tc-7">
                            <div class="smoke-wrap">
                                <span v-for="s in 12" :key="s" :class="'smoke s' + s"></span>
                            </div>
                            <div class="image-wrap">
                                <img src="/images/templates/ningrum/Orn-17.png" alt="Smoke Incense Urn">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="ornaments-wrapper">
                    <div class="orn-tc-2">
                        <div class="image-wrap">
                            <img src="/images/templates/ningrum/Orn-22.png" alt="Ornaments">
                        </div>
                    </div>
                    <div class="orn-tc-6">
                        <div class="image-wrap">
                            <img src="/images/templates/ningrum/Orn-50.png" alt="Ornaments">
                        </div>
                    </div>
                    <div class="orn-tc-1">
                        <div class="image-wrap">
                            <img src="/images/templates/ningrum/Orn-48.png" alt="Ornaments">
                        </div>
                    </div>
                </div>

                <div class="inner">
                    <div class="tc-head">
                        <div class="invitation-category vip">
                            <div class="category-icon">
                                <svg class="icon ic-filled ic-star" xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="#000000" viewBox="0 0 256 256">
                                    <path d="M234.29,114.85l-45,38.83L203,211.75a16.4,16.4,0,0,1-24.5,17.82L128,198.49,77.47,229.57A16.4,16.4,0,0,1,53,211.75l13.76-58.07-45-38.83A16.46,16.46,0,0,1,31.08,86l59-4.76,22.76-55.08a16.36,16.36,0,0,1,30.27,0l22.75,55.08,59,4.76a16.46,16.46,0,0,1,9.37,28.86Z"></path>
                                </svg>
                            </div>
                            <p class="category-label">VIP</p>
                        </div>
                        <h1 class="top-cover-title">{{ invitationData.content.weddingTitle }}</h1>
                    </div>

                    <div class="tc-frame-wrapper">
                        <div class="body highlight">
                            <div class="tc-frame" id="tcFrame">
                                <div class="ornaments-wrapper">
                                    <div class="orn-tc-5">
                                        <div class="image-wrap">
                                            <img src="/images/templates/ningrum/Orn-37.png" alt="Ornaments">
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-container cover-show" id="cover-opening">
                                    <div class="picture mobile">
                                        <img :src="livingPhoto" alt="Couple 3D">
                                    </div>
                                </div>
                                <div class="image-wrap">
                                    <img src="/images/templates/ningrum/frame-ff.png" alt="Cover Frame">
                                </div>
                                <div class="orn-tc-4">
                                    <div class="image-wrap">
                                        <img src="/images/templates/ningrum/Orn-25.png" alt="Ornaments">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="ornaments-wrapper">
                        <div class="orn-tc-3">
                            <div class="orn-tc-3-1">
                                <div class="image-wrap">
                                    <img src="/images/templates/ningrum/Orn-12.png" alt="Ornaments">
                                </div>
                            </div>
                            <div class="image-wrap">
                                <img src="/images/templates/ningrum/Orn-49.png" alt="Ornaments">
                            </div>
                        </div>
                    </div>

                    <div class="details">
                        <p>
                            Kepada Yth. Bpk/Ibu/Saudara/i:<br>
                            <strong>{{ guestName || 'Tamu Undangan Yang Berbahagia' }}</strong>
                        </p>
                        <div class="link-wrap">
                            <a href="javascript:;" @click="handleOpenInvitation" class="link" id="startToExplore">
                                Open Invitation
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ==================================================== -->
            <!-- COVER SECTION (Pendulum Chandelier & 3D Living Photo) -->
            <!-- ==================================================== -->
            <section class="cover" data-section-order="cover" id="coverSection">
                <div class="ornaments-wrapper">
                    <!-- Swinging Pendulum Chandelier -->
                    <div class="orn-cover-2 center">
                        <div class="image-wrap">
                            <img src="/images/templates/ningrum/Orn-07.png" alt="Swinging Chandelier">
                        </div>
                    </div>
                    <!-- Hanging Vines Left & Right -->
                    <div class="orn-cover-1 right">
                        <div class="image-wrap">
                            <img src="/images/templates/ningrum/Orn-06.png" alt="Vines">
                        </div>
                    </div>
                    <div class="orn-cover-1 left">
                        <div class="image-wrap">
                            <img src="/images/templates/ningrum/Orn-06.png" alt="Vines">
                        </div>
                    </div>
                </div>

                <div class="inner">
                    <div class="head">
                        <div class="logo-wrap">
                            <img src="/images/templates/ningrum/Orn-51.png" alt="Monogram" class="logo">
                        </div>
                    </div>

                    <div class="body highlight">
                        <div class="orn-cover-frame">
                            <div class="orn-cover-5">
                                <div class="image-wrap">
                                    <img src="/images/templates/ningrum/Orn-12.png" alt="Orn">
                                </div>
                            </div>

                            <!-- Baroque Arch Frame with Moving 3D Cinemagraph -->
                            <div class="cover-frame" id="coverFrame">
                                <div class="cover-picture cover-show" id="cover-main">
                                    <div class="picture desktop">
                                        <img :src="livingPhoto" alt="Nicholas & Arabella 3D">
                                    </div>
                                    <div class="picture mobile">
                                        <img :src="livingPhoto" alt="Nicholas & Arabella 3D">
                                    </div>
                                </div>
                            </div>
                            <div class="image-wrap">
                                <img src="/images/templates/ningrum/frame-cover.png" alt="Cover Frame">
                            </div>

                            <div class="orn-cover-3">
                                <div class="image-wrap">
                                    <img src="/images/templates/ningrum/Orn-08.png" alt="Orn">
                                </div>
                            </div>

                            <div class="orn-cover-4">
                                <div class="orn-cover-4-2">
                                    <div class="image-wrap">
                                        <img src="/images/templates/ningrum/Orn-11.png" alt="Orn">
                                    </div>
                                </div>
                                <div class="orn-cover-4-1">
                                    <div class="image-wrap">
                                        <img src="/images/templates/ningrum/Orn-10.png" alt="Orn">
                                    </div>
                                </div>
                                <div class="image-wrap">
                                    <img src="/images/templates/ningrum/Orn-09.png" alt="Orn">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="head bot">
                        <p>{{ invitationData.content.coverSubtitle || 'An Evening to Remember' }}</p>
                        <h1>{{ invitationData.content.weddingTitle }}</h1>
                        <p>{{ invitationData.content.weddingDateText }}</p>
                    </div>
                </div>
            </section>

            <!-- ==================================================== -->
            <!-- QUOTE SECTION                                        -->
            <!-- ==================================================== -->
            <section class="quote-wrap" data-section-order="quote">
                <div class="ornaments-wrapper">
                    <div class="orn-quote-1">
                        <div class="image-wrap">
                            <img src="/images/templates/ningrum/Orn-13.png" alt="Ornaments">
                        </div>
                    </div>
                </div>

                <div class="p-relative quote-frame-wrap">
                    <div class="quote">
                        <p class="quote-caption">
                            “{{ invitationData.content.quote || 'Dan di antara tanda-tanda kebesaran-Nya ialah Dia menciptakan untukmu pasangan-pasangan dari jenismu sendiri, supaya kamu merasa tenang dan tenteram kepadanya, dan dijadikan-Nya diantaramu rasa kasih dan sayang.' }}”
                            <br><br>
                            — {{ invitationData.content.quoteSource || 'QS. Ar-Rum: 21' }}
                        </p>
                        <div class="ornaments-wrapper">
                            <div class="orn-quote-2">
                                <div class="image-wrap">
                                    <img src="/images/templates/ningrum/Orn-01.png" alt="Ornaments">
                                </div>
                                <div class="orn-quote-2-1">
                                    <div class="image-wrap">
                                        <img src="/images/templates/ningrum/Orn-14.png" alt="Ornaments">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ==================================================== -->
            <!-- GROOM & BRIDE COUPLE SECTION                         -->
            <!-- ==================================================== -->
            <section class="couple-wrap" data-section-order="couple">
                <div class="couple">
                    <div class="couple-head">
                        <h1 class="couple-title">Groom and Bride</h1>
                        <p class="couple-description">
                            Dengan penuh rasa syukur dan sukacita, kami mengundang Anda untuk merayakan awal perjalanan suci ikatan pernikahan kami:
                        </p>
                    </div>

                    <div class="couple-body show-picture">
                        <!-- GROOM -->
                        <div class="couple-info groom">
                            <div class="couple-preview">
                                <div class="orn-cp-5">
                                    <div class="image-wrap">
                                        <img src="/images/templates/ningrum/Orn-20.png" alt="Orn">
                                    </div>
                                </div>
                                <div class="couple-frame">
                                    <div class="orn-cp-1">
                                        <div class="image-wrap">
                                            <img src="/images/templates/ningrum/Orn-15.png" alt="Orn">
                                        </div>
                                    </div>
                                    <div class="orn-cp-3">
                                        <div class="image-wrap">
                                            <img src="/images/templates/ningrum/Orn-18.png" alt="Orn">
                                        </div>
                                    </div>

                                    <div class="couple-picture-wrap">
                                        <div class="couple-picture">
                                            <img class="img" :src="invitationData.content.groom.photo" :alt="invitationData.content.groom.fullName">
                                        </div>
                                    </div>

                                    <div class="image-wrap">
                                        <img src="/images/templates/ningrum/frame-couple.png" class="img-couple-frame" alt="Frame">
                                    </div>

                                    <div class="orn-cp-2">
                                        <div class="image-wrap">
                                            <img src="/images/templates/ningrum/Orn-16.png" alt="Orn">
                                        </div>
                                    </div>

                                    <div class="orn-cp-4">
                                        <div class="orn-cp-4-2">
                                            <div class="image-wrap">
                                                <img src="/images/templates/ningrum/Orn-09.png" alt="Orn">
                                            </div>
                                        </div>
                                        <div class="orn-cp-4-1">
                                            <div class="image-wrap">
                                                <img src="/images/templates/ningrum/Orn-19.png" alt="Orn">
                                            </div>
                                        </div>
                                        <div class="image-wrap">
                                            <img src="/images/templates/ningrum/Orn-17.png" alt="Orn">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="couple-details">
                                <h2 class="couple-name">{{ invitationData.content.groom.fullName }}</h2>
                                <p class="couple-parents">{{ invitationData.content.groom.parents }}</p>
                                <div v-if="invitationData.content.groom.instagram" class="couple-link-wrap">
                                    <a :href="'https://instagram.com/' + invitationData.content.groom.instagram.replace('@','')" target="_blank" class="couple-link">
                                        📷 {{ invitationData.content.groom.instagram }}
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- SEPARATOR -->
                        <div class="separator-wrap">
                            <div class="separator">
                                <h2 class="couple-separator">&amp;</h2>
                            </div>
                        </div>

                        <!-- BRIDE -->
                        <div class="couple-info bride">
                            <div class="couple-preview">
                                <div class="orn-cp-5">
                                    <div class="image-wrap">
                                        <img src="/images/templates/ningrum/Orn-20.png" alt="Orn">
                                    </div>
                                </div>
                                <div class="couple-frame">
                                    <div class="orn-cp-1">
                                        <div class="image-wrap">
                                            <img src="/images/templates/ningrum/Orn-15.png" alt="Orn">
                                        </div>
                                    </div>
                                    <div class="orn-cp-3">
                                        <div class="image-wrap">
                                            <img src="/images/templates/ningrum/Orn-18.png" alt="Orn">
                                        </div>
                                    </div>

                                    <div class="couple-picture-wrap">
                                        <div class="couple-picture">
                                            <img class="img" :src="invitationData.content.bride.photo" :alt="invitationData.content.bride.fullName">
                                        </div>
                                    </div>

                                    <div class="image-wrap">
                                        <img src="/images/templates/ningrum/frame-couple.png" class="img-couple-frame" alt="Frame">
                                    </div>

                                    <div class="orn-cp-2">
                                        <div class="image-wrap">
                                            <img src="/images/templates/ningrum/Orn-16.png" alt="Orn">
                                        </div>
                                    </div>

                                    <div class="orn-cp-4">
                                        <div class="orn-cp-4-2">
                                            <div class="image-wrap">
                                                <img src="/images/templates/ningrum/Orn-09.png" alt="Orn">
                                            </div>
                                        </div>
                                        <div class="orn-cp-4-1">
                                            <div class="image-wrap">
                                                <img src="/images/templates/ningrum/Orn-19.png" alt="Orn">
                                            </div>
                                        </div>
                                        <div class="image-wrap">
                                            <img src="/images/templates/ningrum/Orn-17.png" alt="Orn">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="couple-details">
                                <h2 class="couple-name">{{ invitationData.content.bride.fullName }}</h2>
                                <p class="couple-parents">{{ invitationData.content.bride.parents }}</p>
                                <div v-if="invitationData.content.bride.instagram" class="couple-link-wrap">
                                    <a :href="'https://instagram.com/' + invitationData.content.bride.instagram.replace('@','')" target="_blank" class="couple-link">
                                        📷 {{ invitationData.content.bride.instagram }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ==================================================== -->
            <!-- SAVE THE DATE & COUNTDOWN                            -->
            <!-- ==================================================== -->
            <section class="save-date-wrap" data-section-order="save_the_date">
                <div class="save-date">
                    <div class="save-date-body">
                        <h1 class="save-date-title">Save the date</h1>
                        <div class="save-date-box">
                            <div class="countdown">
                                <div class="count-item">
                                    <h2 class="count-num count-day">{{ timeLeft.days }}</h2>
                                    <small class="count-text">Days</small>
                                </div>
                                <div class="count-item">
                                    <h2 class="count-num count-hour">{{ timeLeft.hours }}</h2>
                                    <small class="count-text">Hours</small>
                                </div>
                                <div class="count-item">
                                    <h2 class="count-num count-minute">{{ timeLeft.minutes }}</h2>
                                    <small class="count-text">Minutes</small>
                                </div>
                                <div class="count-item">
                                    <h2 class="count-num count-second">{{ timeLeft.seconds }}</h2>
                                    <small class="count-text">Seconds</small>
                                </div>
                            </div>

                            <div class="add-to-calendar-wrap">
                                <a class="add-to-calendar" :href="googleCalendarUrl" target="_blank" rel="nofollow">
                                    📅 Add to Google Calendar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="ornaments-wrapper">
                    <div class="orn-sd-2 right">
                        <div class="image-wrap">
                            <img src="/images/templates/ningrum/Orn-22.png" alt="Ornaments">
                        </div>
                    </div>
                    <div class="orn-sd-2 left">
                        <div class="image-wrap">
                            <img src="/images/templates/ningrum/Orn-22.png" alt="Ornaments">
                        </div>
                    </div>
                    <div class="orn-sd-3">
                        <div class="image-wrap">
                            <img src="/images/templates/ningrum/Orn-50.png" alt="Ornaments">
                        </div>
                    </div>
                </div>
            </section>

            <!-- ==================================================== -->
            <!-- AGENDA ACARA (Wedding Events)                        -->
            <!-- ==================================================== -->
            <section class="agenda-wrap" data-section-order="event">
                <div class="agenda-inner">
                    <div class="agenda-head">
                        <h2 class="agenda-title">The Wedding Day</h2>
                    </div>

                    <div class="agenda-body">
                        <div v-for="ev in events" :key="ev.id" class="event-item mb-10">
                            <div class="ornaments-wrapper">
                                <div class="orn-ev-9">
                                    <div class="image-wrap">
                                        <img src="/images/templates/ningrum/Orn-13.png" alt="Ornament">
                                    </div>
                                </div>
                                <div class="orn-ev-10">
                                    <div class="image-wrap">
                                        <img src="/images/templates/ningrum/Orn-28.png" alt="Ornament">
                                    </div>
                                </div>
                            </div>

                            <div class="event-head">
                                <div class="event-hw-outer">
                                    <div class="event-head-wrapper">
                                        <p class="event-day">{{ ev.name }}</p>
                                        <div class="event-date">
                                            <p class="event-date-text">{{ formatDisplayDate(ev.starts_at) }}</p>
                                        </div>
                                        <p class="event-time">{{ formatEventTime(ev.starts_at, ev.ends_at, ev.timezone) }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="event-body text-center mt-3 px-6">
                                <h3 class="font-serif font-bold text-lg text-amber-200">{{ ev.venue_name }}</h3>
                                <p class="text-xs text-stone-300 mt-1 max-w-sm mx-auto leading-relaxed">{{ ev.address }}</p>
                                <div class="mt-4">
                                    <a
                                        :href="ev.maps_url || 'https://maps.google.com'"
                                        target="_blank"
                                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-full text-xs font-serif uppercase tracking-widest font-bold transition-transform hover:scale-105"
                                        style="background-color: var(--button-background-primary); color: var(--button-text-primary);"
                                    >
                                        <span>📍</span>
                                        <span>Petunjuk Lokasi (Google Maps)</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ==================================================== -->
            <!-- GALLERY PHOTO (Vintage Baroque Grid)                 -->
            <!-- ==================================================== -->
            <section class="photo-wrap" data-section-order="gallery_photo">
                <div class="photo-inner">
                    <div class="photo-head">
                        <h1 class="photo-title">Our Gallery</h1>
                    </div>

                    <div class="photo-body">
                        <div class="photo-box-outer no-scrollbar">
                            <div class="photo-box ningrum-lightgallery">
                                <div class="photo-group pattern-0">
                                    <div
                                        v-for="(img, idx) in invitationData.content.gallery"
                                        :key="idx"
                                        class="photo-item"
                                        :class="idx % 2 === 0 ? 'photo-big' : 'photo-small'"
                                        :style="{ '--gal-frame': `url('/images/templates/ningrum/${idx % 2 === 0 ? 'gal-big' : 'gal-small'}.png')` }"
                                        @click="openLightbox(img)"
                                        style="cursor: pointer;"
                                    >
                                        <span class="photo-item-inner">
                                            <img :src="img" :alt="`Gallery Photo ${idx + 1}`" class="w-full h-full object-cover">
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Lightbox Modal -->
            <div
                v-if="activeLightboxImg"
                class="fixed inset-0 z-50 bg-black/90 backdrop-blur-md flex items-center justify-center p-4 cursor-pointer"
                @click="closeLightbox"
            >
                <div class="relative max-w-3xl max-h-[85vh] p-2 border border-amber-500/40 rounded-2xl bg-stone-950">
                    <img :src="activeLightboxImg" alt="Enlarged" class="max-w-full max-h-[80vh] object-contain rounded-xl">
                    <button
                        @click="closeLightbox"
                        class="absolute -top-3 -right-3 w-8 h-8 rounded-full bg-amber-600 text-stone-900 font-bold flex items-center justify-center shadow-lg"
                    >
                        ✕
                    </button>
                </div>
            </div>

            <!-- ==================================================== -->
            <!-- RSVP SECTION                                         -->
            <!-- ==================================================== -->
            <section class="rsvp-wrap py-12 px-6">
                <div class="max-w-md mx-auto rounded-3xl p-6 sm:p-8 border shadow-2xl text-center relative overflow-hidden" style="background-color: var(--background-secondary-rgb, 23 19 14 / 0.85); border-color: var(--text-primary); backdrop-filter: blur(12px);">
                    <h2 class="font-serif text-2xl sm:text-3xl font-bold tracking-tight mb-2" style="color: var(--text-primary);">
                        Konfirmasi Kehadiran
                    </h2>
                    <p class="text-xs text-stone-300 mb-6">
                        Mohon konfirmasikan kehadiran Anda untuk membantu kami mempersiapkan jamuan terbaik.
                    </p>

                    <div v-if="rsvpSuccess" class="p-4 rounded-xl border border-emerald-500/50 bg-emerald-950/60 text-emerald-200 text-xs">
                        ✨ Terima kasih! Konfirmasi kehadiran Anda telah berhasil tersimpan.
                    </div>

                    <form v-else @submit.prevent="submitRsvp" class="space-y-4 text-left">
                        <div v-if="rsvpErrorMessage" class="p-3 rounded-lg border border-rose-500 bg-rose-950/60 text-rose-200 text-xs">
                            {{ rsvpErrorMessage }}
                        </div>

                        <div>
                            <label class="block text-[11px] font-serif uppercase tracking-wider text-amber-200 mb-1">Nama Tamu</label>
                            <input
                                v-model="rsvpForm.guest_name"
                                type="text"
                                required
                                class="w-full px-4 py-2.5 rounded-xl border bg-stone-900/80 text-white text-xs focus:outline-none focus:border-amber-400 border-amber-900/40"
                                placeholder="Masukkan nama Anda"
                            />
                        </div>

                        <div>
                            <label class="block text-[11px] font-serif uppercase tracking-wider text-amber-200 mb-1">Status Kehadiran</label>
                            <div class="grid grid-cols-2 gap-2">
                                <button
                                    type="button"
                                    @click="rsvpForm.attendance_status = 'attending'"
                                    class="py-2.5 px-3 rounded-xl border text-xs font-serif font-bold transition-all text-center"
                                    :class="rsvpForm.attendance_status === 'attending' ? 'bg-amber-500 text-stone-950 border-amber-400 shadow-md' : 'bg-stone-900/80 text-stone-300 border-stone-700'"
                                >
                                    ✓ Hadir
                                </button>
                                <button
                                    type="button"
                                    @click="rsvpForm.attendance_status = 'declined'"
                                    class="py-2.5 px-3 rounded-xl border text-xs font-serif font-bold transition-all text-center"
                                    :class="rsvpForm.attendance_status === 'declined' ? 'bg-stone-700 text-white border-stone-500 shadow-md' : 'bg-stone-900/80 text-stone-300 border-stone-700'"
                                >
                                    ✕ Berhalangan
                                </button>
                            </div>
                        </div>

                        <div v-if="rsvpForm.attendance_status === 'attending'">
                            <label class="block text-[11px] font-serif uppercase tracking-wider text-amber-200 mb-1">Jumlah Tamu</label>
                            <select
                                v-model.number="rsvpForm.attendee_count"
                                class="w-full px-4 py-2.5 rounded-xl border bg-stone-900/80 text-white text-xs focus:outline-none focus:border-amber-400 border-amber-900/40"
                            >
                                <option :value="1">1 Orang</option>
                                <option :value="2">2 Orang</option>
                                <option :value="3">3 Orang</option>
                                <option :value="4">4 Orang</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[11px] font-serif uppercase tracking-wider text-amber-200 mb-1">Ucapan & Doa Restu</label>
                            <textarea
                                v-model="rsvpForm.message"
                                rows="3"
                                class="w-full px-4 py-2 rounded-xl border bg-stone-900/80 text-white text-xs focus:outline-none focus:border-amber-400 border-amber-900/40"
                                placeholder="Tuliskan ucapan selamat dan doa untuk mempelai..."
                            ></textarea>
                        </div>

                        <button
                            type="submit"
                            :disabled="rsvpSubmitting"
                            class="w-full py-3 rounded-full font-serif font-bold text-xs uppercase tracking-widest shadow-xl transition-all duration-300 hover:scale-105 active:scale-95 text-stone-950"
                            style="background-color: var(--button-background-primary);"
                        >
                            <span v-if="rsvpSubmitting">Mengirimkan...</span>
                            <span v-else>Kirim Konfirmasi Kehadiran</span>
                        </button>
                    </form>
                </div>
            </section>

            <!-- ==================================================== -->
            <!-- WEDDING GIFT & DIGITAL ENVELOPE                      -->
            <!-- ==================================================== -->
            <section class="wedding-gift-wrap py-12 px-6" data-section-order="wedding_gift">
                <div class="wedding-gift-inner max-w-md mx-auto text-center">
                    <div class="wedding-gift-head mb-8">
                        <h1 class="wedding-gift-title font-serif text-3xl font-bold" style="color: var(--text-primary);">
                            Wedding Gift
                        </h1>
                        <p class="wedding-gift-description text-xs text-stone-300 mt-2 leading-relaxed">
                            Doa restu Anda adalah karunia terindah bagi kami. Namun jika memberi adalah ungkapan kasih, kami menyediakan Amplop Digital di bawah ini:
                        </p>
                    </div>

                    <div class="space-y-4">
                        <div
                            v-for="(acc, i) in digitalGift?.accounts || []"
                            :key="i"
                            class="p-5 rounded-2xl border backdrop-blur-md shadow-xl text-left relative overflow-hidden"
                            style="background-color: var(--background-secondary-rgb, 35 25 18 / 0.7); border-color: var(--text-primary);"
                        >
                            <div class="flex items-center justify-between mb-3">
                                <span class="font-serif font-bold text-sm tracking-wider uppercase text-amber-200">{{ acc.bank }}</span>
                                <span class="text-xs px-2.5 py-0.5 rounded-full bg-amber-500/20 text-amber-300 border border-amber-500/30">Transfer Bank</span>
                            </div>
                            <div class="font-mono text-base font-bold text-white tracking-widest my-1">
                                {{ acc.account_number }}
                            </div>
                            <div class="text-xs text-stone-300 mb-3">
                                a.n. {{ acc.account_name }}
                            </div>

                            <div class="flex items-center gap-2">
                                <button
                                    @click="copyBankNumber(acc.account_number, i)"
                                    class="px-4 py-2 rounded-xl text-xs font-serif font-bold transition-all flex items-center gap-1.5"
                                    :class="copiedBankIndex === i ? 'bg-emerald-600 text-white' : 'bg-amber-500 text-stone-950 hover:bg-amber-400'"
                                >
                                    <span v-if="copiedBankIndex === i">✓ Berhasil Disalin</span>
                                    <span v-else>📋 Salin Nomor Rekening</span>
                                </button>
                                <button
                                    v-if="acc.qr_image"
                                    @click="openQris(acc)"
                                    class="px-3.5 py-2 rounded-xl text-xs font-serif border border-amber-400/40 text-amber-200 hover:bg-amber-400/10 transition-colors"
                                >
                                    QRIS
                                </button>
                            </div>
                        </div>

                        <!-- Physical Gift -->
                        <div
                            v-if="digitalGift?.physical_gift?.enabled"
                            class="p-5 rounded-2xl border text-left"
                            style="background-color: var(--background-secondary-rgb, 35 25 18 / 0.7); border-color: var(--text-primary);"
                        >
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-serif font-bold text-sm text-amber-200">Kirim Kado Fisik</span>
                                <span class="text-base">📦</span>
                            </div>
                            <p class="text-xs text-stone-300 leading-relaxed mb-3">
                                <strong>Penerima:</strong> {{ digitalGift.physical_gift.receiver_name }}<br>
                                <strong>Alamat:</strong> {{ digitalGift.physical_gift.address }}
                            </p>
                            <button
                                @click="copyAddress(digitalGift.physical_gift.address)"
                                class="px-4 py-1.5 rounded-lg text-xs font-serif bg-amber-500/20 text-amber-200 border border-amber-500/40 hover:bg-amber-500/30"
                            >
                                📍 Salin Alamat Kirim
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ==================================================== -->
            <!-- GUESTBOOK WISHES WALL                                -->
            <!-- ==================================================== -->
            <section class="wedding-wish-wrap py-12 px-6" data-section-order="wedding_wishes">
                <div class="max-w-md mx-auto text-center">
                    <h2 class="font-serif text-2xl sm:text-3xl font-bold mb-6" style="color: var(--text-primary);">
                        Doa & Ucapan Hangat
                    </h2>

                    <div class="space-y-3 max-h-96 overflow-y-auto pr-2 no-scrollbar text-left">
                        <div
                            v-for="entry in guestbook"
                            :key="entry.id"
                            class="p-4 rounded-2xl border backdrop-blur-md"
                            style="background-color: var(--background-secondary-rgb, 35 25 18 / 0.6); border-color: var(--text-primary);"
                        >
                            <div class="flex items-center justify-between mb-1">
                                <strong class="font-serif text-xs font-bold text-amber-200">{{ entry.guest_name }}</strong>
                                <span class="text-[10px] text-stone-400">{{ entry.created_at ? new Date(entry.created_at).toLocaleDateString('id-ID') : 'Baru saja' }}</span>
                            </div>
                            <p class="text-xs text-stone-300 leading-relaxed font-serif italic">
                                “{{ entry.message }}”
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ==================================================== -->
            <!-- LUXURY FOOTER                                        -->
            <!-- ==================================================== -->
            <footer class="footer py-12 px-6 text-center border-t border-amber-900/30" style="background-color: var(--background-primary);">
                <div class="logo-wrap max-w-[80px] mx-auto mb-3">
                    <img src="/images/templates/ningrum/Orn-51.png" alt="Monogram" class="w-full opacity-80">
                </div>
                <h2 class="font-serif text-2xl font-bold" style="color: var(--text-primary);">
                    {{ invitationData.content.weddingTitle }}
                </h2>
                <p class="text-xs text-stone-400 font-serif mt-1">
                    {{ invitationData.content.weddingDateText }}
                </p>
                <p class="text-[10px] text-stone-500 font-serif uppercase tracking-widest mt-6">
                    Made with Love • Undangan Digital Premium 3D
                </p>
            </footer>

        </main>

        <!-- ======================================================== -->
        <!-- FLOATING MUSIC PLAYER (Vinyl Disk)                       -->
        <!-- ======================================================== -->
        <div class="fixed bottom-6 right-6 z-50">
            <button
                @click="emit('toggleMusic')"
                class="relative group focus:outline-none cursor-pointer"
                :title="isPlayingMusic ? 'Jeda Musik Latar' : 'Putar Musik Latar'"
            >
                <!-- Outer Pulse Glow when playing -->
                <div
                    v-if="isPlayingMusic"
                    class="absolute -inset-1 rounded-full blur-sm animate-pulse bg-amber-500/50"
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
                    <div class="w-4 h-4 rounded-full border flex items-center justify-center shadow-inner bg-amber-600 border-amber-300">
                        <div class="w-1 h-1 rounded-full bg-stone-950"></div>
                    </div>
                </div>

                <!-- Floating note badge indicator -->
                <div
                    class="absolute -top-1 -right-1 w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold shadow-md border"
                    :class="isPlayingMusic ? 'bg-amber-400 text-stone-900 border-amber-200' : 'bg-stone-800 text-stone-400 border-stone-700'"
                >
                    <span v-if="isPlayingMusic">🎵</span>
                    <span v-else>🔇</span>
                </div>
            </button>
        </div>

    </div>
</template>

<style>
/* CSS Variables for Ningrum Authentic Layout */
:root, .kat-page__side-to-side {
    --heading-size: 48px !important;
    --body-text-size: 16px !important;
    --heading-family: 'Cormorant Upright', serif !important;
    --body-text-family: 'Plus Jakarta Sans', sans-serif !important;
    --background-primary: #17130E !important;
    --background-primary-rgb: 23, 19, 14 !important;
    --background-secondary: #E7E4DB !important;
    --background-secondary-rgb: 231, 228, 219 !important;
    --background-tertiary: #48110C !important;
    --background-tertiary-rgb: 72, 17, 12 !important;
    --text-primary: #A29166 !important;
    --text-primary-rgb: 162, 145, 102 !important;
    --text-secondary: #E7E4DB !important;
    --text-secondary-rgb: 231, 228, 219 !important;
    --text-tertiary: #48110C !important;
    --text-tertiary-rgb: 72, 17, 12 !important;
    --button-text-primary: #E7E4DB !important;
    --button-text-primary-rgb: 231, 228, 219 !important;
    --button-background-primary: #A29166 !important;
    --button-background-primary-rgb: 162, 145, 102 !important;
    --button-text-secondary: #E7E4DB !important;
    --button-text-secondary-rgb: 231, 228, 219 !important;
    --button-background-secondary: #56483B !important;
    --button-background-secondary-rgb: 86, 72, 59 !important;
    --texture-sd: url("/images/templates/ningrum/texture-sd.png");
    --frame-cover-mask: url("/images/templates/ningrum/mask-cover.png");
    --frame-ff-mask: url("/images/templates/ningrum/mask-ff.png");
    --mask-couple: url("/images/templates/ningrum/mask-couple.png");
}

/* Ensure smooth transitions and full-height scroll */
.kat-page__side-to-side {
    position: relative;
    min-height: 100vh;
    background-color: var(--background-primary);
    color: var(--text-secondary);
    font-family: var(--body-text-family);
}
</style>

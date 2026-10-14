<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import Lenis from 'lenis';

gsap.registerPlugin(ScrollTrigger);

const props = defineProps<{
    templates: Array<{
        id: number;
        name: string;
        slug: string;
        category: string;
        thumbnail_path: string;
        is_premium: boolean;
    }>;
}>();

// Lenis smooth scroll instance
let lenisInstance: Lenis | null = null;
const heroPhoneRef = ref<HTMLElement | null>(null);
const heroSectionRef = ref<HTMLElement | null>(null);
const featuresSectionRef = ref<HTMLElement | null>(null);
const cardsContainerRef = ref<HTMLElement | null>(null);

// Interactive 3D Card Hover Tilt logic
const handleMouseMove = (e: MouseEvent, cardEl: HTMLElement) => {
    const rect = cardEl.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;
    const centerX = rect.width / 2;
    const centerY = rect.height / 2;
    const rotateX = ((y - centerY) / centerY) * -7;
    const rotateY = ((x - centerX) / centerX) * 7;

    cardEl.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-4px)`;
};

const handleMouseLeave = (cardEl: HTMLElement) => {
    cardEl.style.transform = 'perspective(1000px) rotateX(0deg) rotateY(0deg) translateY(0px)';
};

onMounted(() => {
    // 1. Initialize Lenis Smooth Scroll
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
    } catch (err) {
        console.warn('Lenis smooth scroll initialization skipped:', err);
    }

    // 2. GSAP ScrollTrigger for 3D Phone Hero Mockup
    if (heroPhoneRef.value && heroSectionRef.value) {
        gsap.fromTo(
            heroPhoneRef.value,
            {
                rotateX: 14,
                rotateY: -8,
                rotateZ: -2,
                scale: 0.92,
                y: 30,
            },
            {
                rotateX: 0,
                rotateY: 0,
                rotateZ: 0,
                scale: 1.05,
                y: -10,
                ease: 'power2.out',
                scrollTrigger: {
                    trigger: heroSectionRef.value,
                    start: 'top top',
                    end: 'bottom 40%',
                    scrub: 1.2,
                },
            }
        );
    }

    // 3. Staggered reveal for feature items
    const featureCards = document.querySelectorAll('.scrub-feature-card');
    if (featureCards.length > 0) {
        gsap.from(featureCards, {
            y: 45,
            opacity: 0,
            stagger: 0.15,
            duration: 0.8,
            ease: 'power2.out',
            scrollTrigger: {
                trigger: featuresSectionRef.value,
                start: 'top 75%',
            },
        });
    }

    // 4. Parallax floating sage botanicals
    const leaves = document.querySelectorAll('.floating-botanical');
    leaves.forEach((leaf, idx) => {
        const speed = (idx + 1) * 35;
        gsap.to(leaf, {
            y: -speed,
            ease: 'none',
            scrollTrigger: {
                trigger: heroSectionRef.value,
                start: 'top top',
                end: 'bottom top',
                scrub: true,
            },
        });
    });
});

onUnmounted(() => {
    if (lenisInstance) {
        lenisInstance.destroy();
        lenisInstance = null;
    }
    ScrollTrigger.getAll().forEach((st) => st.kill());
});
</script>

<template>
    <AppLayout>
        <div class="relative overflow-hidden bg-[#F7F9F7] text-stone-800 font-sans selection:bg-emerald-700 selection:text-white">

            <!-- Ethereal Background Ambient Glows (Sage, Olive & Warm Ivory) -->
            <div class="pointer-events-none absolute -top-40 -left-40 w-[600px] h-[600px] rounded-full bg-emerald-100/60 blur-[130px] -z-10"></div>
            <div class="pointer-events-none absolute top-1/3 -right-40 w-[550px] h-[550px] rounded-full bg-stone-200/50 blur-[140px] -z-10"></div>
            <div class="pointer-events-none absolute top-2/3 left-1/4 w-[500px] h-[500px] rounded-full bg-emerald-50/70 blur-[120px] -z-10"></div>

            <!-- ======================================================== -->
            <!-- 1. HERO SECTION: 3D INTERACTIVE SHOWCASE                 -->
            <!-- ======================================================== -->
            <section ref="heroSectionRef" class="relative pt-12 pb-24 sm:pt-20 sm:pb-32 lg:pt-28 overflow-hidden">
                
                <!-- Floating Decorative Botanicals / Gold Sparkles -->
                <div class="floating-botanical pointer-events-none absolute top-16 left-8 opacity-25 hidden lg:block select-none">
                    <span class="text-4xl">🌿</span>
                </div>
                <div class="floating-botanical pointer-events-none absolute top-48 right-16 opacity-30 hidden lg:block select-none">
                    <span class="text-3xl">🍃</span>
                </div>
                <div class="floating-botanical pointer-events-none absolute bottom-24 left-1/4 opacity-20 hidden lg:block select-none">
                    <span class="text-2xl">✨</span>
                </div>

                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="grid lg:grid-cols-12 gap-12 lg:gap-8 items-center">
                        
                        <!-- Left Content Column -->
                        <div class="lg:col-span-7 text-center lg:text-left z-10">
                            <!-- Premium Locket Badge -->
                            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-emerald-900/5 border border-emerald-800/15 text-emerald-800 text-xs font-semibold tracking-wide mb-6 backdrop-blur-md">
                                <span class="w-2 h-2 rounded-full bg-emerald-600 animate-pulse"></span>
                                <span>Platform Undangan Digital Eksklusif Berteknologi 3D</span>
                            </div>

                            <!-- Main Grand Title -->
                            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-serif font-bold text-stone-900 tracking-tight leading-[1.15]">
                                Abadikan Momen Spesial Anda dalam 
                                <span class="relative inline-block text-emerald-800 italic">
                                    Undangan Bernyawa
                                    <svg class="absolute -bottom-1.5 left-0 w-full text-emerald-600/30" viewBox="0 0 250 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M3 9C60 3 190 2 247 9" stroke="currentColor" stroke-width="4" stroke-linecap="round"/>
                                    </svg>
                                </span>
                            </h1>

                            <p class="mt-6 text-base sm:text-lg text-stone-600 max-w-xl leading-relaxed mx-auto lg:mx-0 font-normal">
                                Ciptakan undangan pernikahan digital kelas dunia dengan foto 3D hidup, alunan musik sakral, serta buku tamu & RSVP otomatis yang dapat disebarkan dalam hitungan detik.
                            </p>

                            <!-- CTA Buttons -->
                            <div class="mt-8 flex flex-col sm:flex-row gap-3.5 justify-center lg:justify-start items-center">
                                <Link
                                    href="/register"
                                    class="w-full sm:w-auto inline-flex justify-center items-center gap-2 px-7 py-3.5 text-sm sm:text-base font-semibold rounded-2xl text-white bg-emerald-800 hover:bg-emerald-900 shadow-lg shadow-emerald-900/20 hover:shadow-xl hover:shadow-emerald-900/30 hover:-translate-y-0.5 transition-all duration-200"
                                >
                                    <span>Buat Undangan Gratis</span>
                                    <span>→</span>
                                </Link>
                                <Link
                                    href="/bantu-buat"
                                    class="w-full sm:w-auto inline-flex justify-center items-center gap-2 px-6 py-3.5 text-sm sm:text-base font-semibold rounded-2xl text-stone-800 bg-white/90 hover:bg-emerald-50/70 border border-emerald-200/80 shadow-xs hover:shadow-md transition-all duration-200"
                                >
                                    <span>✨ Jasa Terima Beres</span>
                                </Link>
                                <a
                                    href="/demo/ningrum-fairytale-3d"
                                    target="_blank"
                                    class="w-full sm:w-auto inline-flex justify-center items-center gap-2 px-5 py-3.5 text-sm sm:text-base font-medium text-emerald-800 hover:text-emerald-950 transition-colors"
                                >
                                    <span>Lihat Demo 3D</span>
                                    <span>↗</span>
                                </a>
                            </div>

                            <!-- Trust Indicators -->
                            <div class="mt-10 pt-6 border-t border-emerald-900/10 flex flex-wrap items-center justify-center lg:justify-start gap-6 text-xs text-stone-600 font-medium">
                                <div class="flex items-center gap-2">
                                    <div class="w-4 h-4 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-[10px] font-bold">✓</div>
                                    <span>Foto 3D Bergerak</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="w-4 h-4 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-[10px] font-bold">✓</div>
                                    <span>Kirim Nama Tamu WhatsApp</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="w-4 h-4 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-[10px] font-bold">✓</div>
                                    <span>QR Code & Hadiah Digital</span>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Interactive 3D Smartphone Device Mockup -->
                        <div class="lg:col-span-5 flex justify-center items-center relative perspective-[1200px]">
                            
                            <!-- Ambient Glow behind Phone -->
                            <div class="absolute w-72 h-96 bg-gradient-to-tr from-emerald-300/40 via-amber-200/30 to-emerald-200/40 blur-3xl rounded-full -z-10 animate-pulse"></div>

                            <div
                                ref="heroPhoneRef"
                                class="relative w-[300px] sm:w-[330px] rounded-[48px] bg-stone-900 p-3.5 shadow-2xl ring-1 ring-white/20 shadow-emerald-950/30 transition-transform duration-150 ease-out select-none"
                                style="transform-style: preserve-3d;"
                            >
                                <!-- Phone Notch & Speaker -->
                                <div class="absolute top-6 left-1/2 -translate-x-1/2 w-28 h-5 bg-black rounded-full z-30 flex items-center justify-center gap-2">
                                    <div class="w-2 h-2 rounded-full bg-stone-800"></div>
                                    <div class="w-9 h-1 rounded-full bg-stone-800"></div>
                                </div>

                                <!-- Screen Container -->
                                <div class="relative w-full aspect-[9/18.5] rounded-[38px] overflow-hidden bg-[#17130E] border border-stone-800/80 text-white flex flex-col justify-between p-5 text-center">
                                    
                                    <!-- Screen Top: Monogram & Date -->
                                    <div class="pt-6">
                                        <div class="inline-block px-3 py-0.5 rounded-full bg-amber-400/10 border border-amber-300/20 text-amber-200 text-[10px] font-serif tracking-widest uppercase mb-1">
                                            The Royal Wedding
                                        </div>
                                        <h3 class="font-serif italic text-2xl text-amber-100 font-bold">
                                            Raden & Ningrum
                                        </h3>
                                        <p class="text-[10px] text-stone-400 font-sans tracking-wide">
                                            Sabtu, 14 November 2026
                                        </p>
                                    </div>

                                    <!-- Screen Center: 3D Living Photo in Arch Frame -->
                                    <div class="relative my-auto flex justify-center">
                                        <div class="relative w-44 aspect-[3/4] rounded-2xl overflow-hidden shadow-2xl border-2 border-amber-400/40">
                                            <img
                                                src="/images/templates/ningrum/couple-3d.gif"
                                                alt="Couple 3D Cinemagraph"
                                                class="w-full h-full object-cover"
                                            />
                                            <div class="absolute bottom-2 left-2 right-2 px-2 py-1 rounded-lg bg-black/60 backdrop-blur-xs text-[9px] text-amber-200 flex items-center justify-between">
                                                <span>✨ Living 3D Photo</span>
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping"></span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Screen Bottom: Personal Guest Card & Open Button -->
                                    <div class="pb-2 space-y-2">
                                        <div class="px-3 py-1.5 rounded-xl bg-white/10 backdrop-blur-md border border-white/10 text-left">
                                            <div class="text-[8px] text-stone-400 uppercase tracking-wider">Kepada Yth:</div>
                                            <div class="text-[11px] font-bold text-amber-100 truncate">Tamu Undangan Terhormat</div>
                                        </div>
                                        <a
                                            href="/demo/ningrum-fairytale-3d"
                                            target="_blank"
                                            class="block w-full py-2.5 rounded-xl bg-gradient-to-r from-amber-600 to-amber-700 text-amber-50 text-[11px] font-serif tracking-widest uppercase font-bold shadow-md hover:brightness-110 transition-all"
                                        >
                                            Buka Undangan
                                        </a>
                                    </div>

                                </div>

                                <!-- Floating Interactive Tag on Side of Phone -->
                                <div class="absolute -right-6 top-24 bg-white/95 backdrop-blur-md border border-emerald-100 px-3.5 py-2 rounded-2xl shadow-xl shadow-stone-900/10 flex items-center gap-2.5 text-xs text-stone-800 font-semibold animate-bounce duration-1000">
                                    <span class="text-base">🎵</span>
                                    <div>
                                        <div class="text-[9px] text-stone-400 uppercase">Musik Otomatis</div>
                                        <div class="text-[11px] text-emerald-800">Lagu Sakral Pilihan</div>
                                    </div>
                                </div>

                                <div class="absolute -left-6 bottom-20 bg-white/95 backdrop-blur-md border border-emerald-100 px-3.5 py-2 rounded-2xl shadow-xl shadow-stone-900/10 flex items-center gap-2.5 text-xs text-stone-800 font-semibold">
                                    <span class="text-base">💌</span>
                                    <div>
                                        <div class="text-[9px] text-stone-400 uppercase">RSVP Realtime</div>
                                        <div class="text-[11px] text-emerald-800">Buku Tamu Instan</div>
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>
                </div>
            </section>

            <!-- ======================================================== -->
            <!-- 2. TEMPLATE CATALOG SHOWCASE (3D TILT CARDS)             -->
            <!-- ======================================================== -->
            <section class="py-20 sm:py-28 relative border-t border-emerald-900/10 bg-gradient-to-b from-[#F7F9F7] via-white to-[#F2F6F3]">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    
                    <div class="text-center max-w-2xl mx-auto mb-16">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100/60 border border-emerald-200 text-emerald-800 text-xs font-semibold mb-3">
                            <span>🎨 Koleksi Masterpiece</span>
                        </div>
                        <h2 class="text-3xl sm:text-4xl font-serif font-bold text-stone-900 tracking-tight">
                            Pilihan Desain Tema Eksklusif & 3D
                        </h2>
                        <p class="mt-3 text-stone-600 text-sm sm:text-base leading-relaxed">
                            Mulai dari nuansa adat royal Jawa klasik hingga modern minimalis Eropa dengan ornamen bergerak yang memukau.
                        </p>
                    </div>

                    <!-- Cards Grid -->
                    <div ref="cardsContainerRef" class="grid sm:grid-cols-2 lg:grid-cols-4 gap-8">
                        <div
                            v-for="tpl in templates"
                            :key="tpl.id"
                            @mousemove="handleMouseMove($event, $event.currentTarget as HTMLElement)"
                            @mouseleave="handleMouseLeave($event.currentTarget as HTMLElement)"
                            class="group relative rounded-3xl bg-white border border-emerald-950/10 p-3 shadow-md hover:shadow-2xl hover:shadow-emerald-900/15 transition-all duration-300 flex flex-col justify-between"
                            style="transform-style: preserve-3d; transition: transform 0.2s cubic-bezier(0.25, 1, 0.5, 1);"
                        >
                            <!-- Thumbnail Area -->
                            <div class="relative aspect-[3/4] rounded-2xl overflow-hidden bg-stone-100">
                                <img
                                    :src="tpl.thumbnail_path"
                                    :alt="tpl.name"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out"
                                />

                                <!-- Glass Gradient Overlay on Hover -->
                                <div class="absolute inset-0 bg-gradient-to-t from-stone-950/70 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-4">
                                    <a
                                        :href="'/demo/' + tpl.slug"
                                        target="_blank"
                                        class="w-full py-2.5 rounded-xl bg-white/95 text-stone-900 text-xs font-bold text-center shadow-lg hover:bg-emerald-50 transition-colors"
                                    >
                                        Buka Pratinjau Interaktif ↗
                                    </a>
                                </div>

                                <!-- Badge Tags -->
                                <div class="absolute top-3 left-3 flex flex-col gap-1.5">
                                    <span
                                        v-if="tpl.slug.includes('3d')"
                                        class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-500 text-white shadow-md flex items-center gap-1"
                                    >
                                        <span>✨</span> 3D LIVE
                                    </span>
                                </div>

                                <div class="absolute top-3 right-3">
                                    <span
                                        v-if="tpl.is_premium"
                                        class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-800 text-white shadow-md"
                                    >
                                        PREMIUM
                                    </span>
                                    <span
                                        v-else
                                        class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-stone-800 text-white shadow-md"
                                    >
                                        GRATIS
                                    </span>
                                </div>
                            </div>

                            <!-- Card Info Details -->
                            <div class="p-3 pt-4 flex flex-col flex-1 justify-between">
                                <div>
                                    <div class="text-[11px] font-medium text-emerald-800 uppercase tracking-wider">{{ tpl.category }}</div>
                                    <h3 class="font-serif font-bold text-stone-900 text-lg mt-0.5 leading-snug group-hover:text-emerald-800 transition-colors">
                                        {{ tpl.name }}
                                    </h3>
                                </div>

                                <div class="mt-4 pt-3 border-t border-stone-100 flex items-center justify-between">
                                    <a
                                        :href="'/demo/' + tpl.slug"
                                        target="_blank"
                                        class="inline-flex items-center gap-1 text-xs font-semibold text-stone-600 hover:text-emerald-800 transition-colors"
                                    >
                                        <span>👁️</span> Demo
                                    </a>
                                    <Link
                                        :href="'/register?template=' + tpl.id"
                                        class="text-xs font-bold text-emerald-800 hover:text-emerald-950 inline-flex items-center gap-1"
                                    >
                                        <span>Gunakan</span>
                                        <span>→</span>
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Catalog CTA -->
                    <div class="mt-14 text-center">
                        <Link
                            href="/register"
                            class="inline-flex items-center gap-2 px-8 py-4 rounded-2xl bg-white border border-emerald-900/15 text-emerald-900 font-semibold text-sm hover:bg-emerald-50/50 shadow-sm hover:shadow-md transition-all"
                        >
                            <span>Jelajahi Semua Pilihan Tema di Dashboard</span>
                            <span>→</span>
                        </Link>
                    </div>

                </div>
            </section>

            <!-- ======================================================== -->
            <!-- 3. SCROLL-DRIVEN FEATURE SHOWCASE (SHOPIFY STYLE)        -->
            <!-- ======================================================== -->
            <section ref="featuresSectionRef" class="py-24 sm:py-32 relative bg-white border-t border-emerald-900/10">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    
                    <div class="text-center max-w-2xl mx-auto mb-20">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100/60 border border-emerald-200 text-emerald-800 text-xs font-semibold mb-3">
                            <span>⚡ Solusi Terlengkap</span>
                        </div>
                        <h2 class="text-3xl sm:text-4xl font-serif font-bold text-stone-900 tracking-tight">
                            Segala Kemudahan Pernikahan dalam Satu Genggaman
                        </h2>
                        <p class="mt-3 text-stone-600 text-sm sm:text-base leading-relaxed">
                            Dirancang khusus untuk menghemat waktu Anda, tanpa ribet instalasi aplikasi atau pengetahuan koding.
                        </p>
                    </div>

                    <div class="grid md:grid-cols-3 gap-8">
                        
                        <!-- Feature 1 -->
                        <div class="scrub-feature-card p-8 rounded-3xl bg-[#F7F9F7] border border-emerald-900/10 hover:border-emerald-700/30 transition-all duration-300 hover:shadow-xl hover:shadow-emerald-900/5 group flex flex-col justify-between">
                            <div>
                                <div class="w-14 h-14 rounded-2xl bg-emerald-800 text-white flex items-center justify-center text-2xl shadow-md shadow-emerald-900/20 mb-6 group-hover:scale-110 transition-transform">
                                    🎨
                                </div>
                                <h3 class="font-serif font-bold text-xl text-stone-900 mb-3">Visual Realtime Editor</h3>
                                <p class="text-sm text-stone-600 leading-relaxed">
                                    Ubah teks, tanggal acara, lokasi Google Maps, dan foto mempelai secara langsung. Dilengkapi pratinjau instan di layar komputer maupun ponsel.
                                </p>
                            </div>
                            <div class="mt-8 pt-4 border-t border-emerald-900/10 flex items-center gap-2 text-xs font-semibold text-emerald-800">
                                <span>Autosave Otomatis 0ms</span>
                                <span>✓</span>
                            </div>
                        </div>

                        <!-- Feature 2 -->
                        <div class="scrub-feature-card p-8 rounded-3xl bg-[#F7F9F7] border border-emerald-900/10 hover:border-emerald-700/30 transition-all duration-300 hover:shadow-xl hover:shadow-emerald-900/5 group flex flex-col justify-between">
                            <div>
                                <div class="w-14 h-14 rounded-2xl bg-emerald-800 text-white flex items-center justify-center text-2xl shadow-md shadow-emerald-900/20 mb-6 group-hover:scale-110 transition-transform">
                                    💌
                                </div>
                                <h3 class="font-serif font-bold text-xl text-stone-900 mb-3">Link Personal & Generator WhatsApp</h3>
                                <p class="text-sm text-stone-600 leading-relaxed">
                                    Tulis nama tamu di dashboard, dan sistem akan langsung menghasilkan tautan eksklusif ber-nama tamu beserta template pesan WhatsApp siap kirim.
                                </p>
                            </div>
                            <div class="mt-8 pt-4 border-t border-emerald-900/10 flex items-center gap-2 text-xs font-semibold text-emerald-800">
                                <span>Kirim Cepat 1-Klik</span>
                                <span>✓</span>
                            </div>
                        </div>

                        <!-- Feature 3 -->
                        <div class="scrub-feature-card p-8 rounded-3xl bg-[#F7F9F7] border border-emerald-900/10 hover:border-emerald-700/30 transition-all duration-300 hover:shadow-xl hover:shadow-emerald-900/5 group flex flex-col justify-between">
                            <div>
                                <div class="w-14 h-14 rounded-2xl bg-emerald-800 text-white flex items-center justify-center text-2xl shadow-md shadow-emerald-900/20 mb-6 group-hover:scale-110 transition-transform">
                                    🛡️
                                </div>
                                <h3 class="font-serif font-bold text-xl text-stone-900 mb-3">RSVP & Amplop Digital Terintegrasi</h3>
                                <p class="text-sm text-stone-600 leading-relaxed">
                                    Ketahui konfirmasi kehadiran tamu lebih awal, lengkapi dengan rekening bank, QRIS instan, serta buku ucapan selamat dengan sistem filter kata kotor otomatis.
                                </p>
                            </div>
                            <div class="mt-8 pt-4 border-t border-emerald-900/10 flex items-center gap-2 text-xs font-semibold text-emerald-800">
                                <span>Statistik Akurat Tamu</span>
                                <span>✓</span>
                            </div>
                        </div>

                    </div>
                </div>
            </section>

            <!-- ======================================================== -->
            <!-- 4. GRAND FINALE CTA BANNER (LUXURY EMERALD & GOLD)       -->
            <!-- ======================================================== -->
            <section class="py-20 sm:py-28 relative overflow-hidden bg-gradient-to-br from-emerald-950 via-stone-900 to-emerald-950 text-white">
                
                <!-- Background Gold Particle Glow -->
                <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-amber-500/15 via-transparent to-transparent pointer-events-none"></div>

                <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
                    <div class="w-16 h-16 rounded-3xl bg-amber-400/20 border border-amber-300/30 text-amber-200 flex items-center justify-center text-3xl mx-auto mb-6 shadow-xl">
                        👑
                    </div>
                    <h2 class="text-3xl sm:text-5xl font-serif font-bold tracking-tight text-amber-100 max-w-3xl mx-auto leading-tight">
                        Wujudkan Undangan Impian Anda Hari Ini
                    </h2>
                    <p class="mt-5 text-sm sm:text-base text-stone-300 max-w-xl mx-auto leading-relaxed">
                        Bergabunglah dengan ribuan calon pengantin yang merayakan kebahagiaan mereka dengan undangan digital paling elegan di Indonesia.
                    </p>
                    <div class="mt-9 flex flex-col sm:flex-row gap-4 justify-center items-center">
                        <Link
                            href="/register"
                            class="w-full sm:w-auto px-8 py-4 rounded-2xl bg-gradient-to-r from-amber-500 to-amber-600 text-stone-950 font-bold text-sm sm:text-base shadow-xl hover:shadow-amber-400/20 hover:scale-105 transition-all duration-200"
                        >
                            Daftar & Rancang Sekarang (Gratis)
                        </Link>
                        <Link
                            href="/bantu-buat"
                            class="w-full sm:w-auto px-8 py-4 rounded-2xl bg-white/10 hover:bg-white/15 text-white border border-white/20 font-semibold text-sm sm:text-base backdrop-blur-md transition-all"
                        >
                            Konsultasi Jasa Bantu Buat
                        </Link>
                    </div>
                </div>
            </section>

        </div>
    </AppLayout>
</template>

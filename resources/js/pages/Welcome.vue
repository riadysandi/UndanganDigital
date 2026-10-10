<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

defineProps<{
    templates: Array<{
        id: number;
        name: string;
        slug: string;
        category: string;
        thumbnail_path: string;
        is_premium: boolean;
    }>;
}>();
</script>

<template>
    <AppLayout>
        <!-- Hero Section -->
        <section class="relative overflow-hidden bg-gradient-to-b from-rose-50/50 via-white to-stone-50 py-16 sm:py-24 border-b border-stone-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid lg:grid-cols-12 gap-12 items-center">
                    <div class="lg:col-span-7 text-center lg:text-left">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-100/80 border border-rose-200 text-rose-700 text-xs font-semibold mb-6">
                            <span>✨ Platform Undangan Pernikahan Digital Masa Kini</span>
                        </div>
                        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-serif font-bold text-stone-900 leading-tight">
                            Rayakan Momen Spesial Anda dengan <span class="text-rose-600 italic">Undangan Digital Elegan</span>
                        </h1>
                        <p class="mt-6 text-base sm:text-lg text-stone-600 max-w-2xl leading-relaxed">
                            Buat, rancang dengan editor visual drag-and-drop, dan bagikan undangan pernikahan interaktif dalam hitungan menit. Dilengkapi RSVP otomatis, buku tamu, pemutar musik, dan link personal tamu.
                        </p>
                        <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                            <Link
                                href="/register"
                                class="inline-flex justify-center items-center px-6 py-3.5 text-base font-medium rounded-xl text-white bg-rose-600 hover:bg-rose-700 shadow-md shadow-rose-200 hover:shadow-lg transition-all"
                            >
                                Buat Undangan Sekarang
                            </Link>
                            <Link
                                href="/i/dimas-anisa"
                                target="_blank"
                                class="inline-flex justify-center items-center px-6 py-3.5 text-base font-medium rounded-xl text-stone-700 bg-white hover:bg-stone-50 border border-stone-300 shadow-sm transition-all"
                            >
                                Lihat Contoh Undangan ↗
                            </Link>
                        </div>
                        <div class="mt-8 flex items-center justify-center lg:justify-start gap-6 text-xs text-stone-500">
                            <div class="flex items-center gap-1.5">
                                <span class="text-emerald-500 font-bold">✓</span> Tanpa Keahlian Koding
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="text-emerald-500 font-bold">✓</span> Desain Bebas Kustom
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="text-emerald-500 font-bold">✓</span> Link WhatsApp Otomatis
                            </div>
                        </div>
                    </div>

                    <!-- Hero Visual Card Preview -->
                    <div class="lg:col-span-5 flex justify-center">
                        <div class="relative w-full max-w-sm rounded-3xl bg-white p-3 shadow-2xl border border-stone-200/80">
                            <div class="rounded-2xl overflow-hidden border border-stone-100 bg-[#FDFBF7] p-6 text-center">
                                <div class="text-[10px] tracking-widest uppercase font-serif text-stone-500 mb-2">The Wedding Of</div>
                                <h3 class="font-script text-4xl text-stone-800 my-2">Dimas & Anisa</h3>
                                <p class="text-xs font-serif text-stone-600 mb-4">Minggu, 28 Desember 2026</p>
                                <img
                                    src="https://images.unsplash.com/photo-1519741497674-611481863552?auto=format&fit=crop&w=600&q=80"
                                    alt="Wedding Mockup"
                                    class="w-full h-48 object-cover rounded-xl shadow-sm mb-4"
                                />
                                <div class="p-3 bg-white/80 rounded-xl border border-stone-200/60 text-left text-xs mb-3">
                                    <div class="text-[10px] text-stone-400">Kepada Yth:</div>
                                    <div class="font-bold text-stone-800">Budi Santoso & Partner</div>
                                </div>
                                <Link
                                    href="/i/dimas-anisa"
                                    target="_blank"
                                    class="block w-full py-2.5 rounded-lg bg-stone-900 text-white text-xs font-medium hover:bg-stone-800 transition-colors"
                                >
                                    Buka Undangan Demo
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Template Showcase -->
        <section class="py-16 sm:py-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-12">
                <h2 class="text-3xl font-serif font-bold text-stone-900">Pilihan Tema Template Eksklusif</h2>
                <p class="mt-3 text-sm sm:text-base text-stone-600">
                    Beragam tema artistik yang siap digunakan dan dapat Anda sesuaikan warna, teks, serta tata letaknya.
                </p>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div
                    v-for="tpl in templates"
                    :key="tpl.id"
                    class="group bg-white rounded-2xl border border-stone-200 overflow-hidden shadow-sm hover:shadow-md transition-all flex flex-col"
                >
                    <div class="relative aspect-[3/4] bg-stone-100 overflow-hidden">
                        <img
                            :src="tpl.thumbnail_path"
                            :alt="tpl.name"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                        />
                        <div class="absolute top-3 right-3">
                            <span
                                v-if="tpl.is_premium"
                                class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500 text-white shadow-sm"
                            >
                                PREMIUM
                            </span>
                            <span
                                v-else
                                class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-600 text-white shadow-sm"
                            >
                                GRATIS
                            </span>
                        </div>
                    </div>
                    <div class="p-4 flex flex-col flex-1 justify-between">
                        <div>
                            <div class="text-[11px] text-stone-400 capitalize">{{ tpl.category }}</div>
                            <h3 class="font-serif font-bold text-stone-800 text-base mt-0.5">{{ tpl.name }}</h3>
                        </div>
                        <div class="mt-4 pt-3 border-t border-stone-100 flex items-center justify-between">
                            <Link
                                href="/register"
                                class="text-xs font-semibold text-rose-600 hover:text-rose-700"
                            >
                                Pakai Desain Ini →
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Feature Grid -->
        <section class="bg-white py-16 sm:py-20 border-t border-stone-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-2xl mx-auto mb-12">
                    <h2 class="text-3xl font-serif font-bold text-stone-900">Fitur Lengkap Sesuai Kebutuhan Anda</h2>
                    <p class="mt-3 text-sm text-stone-600">Semua yang Anda butuhkan untuk mengelola kehadiran dan membagikan kebahagiaan.</p>
                </div>

                <div class="grid md:grid-cols-3 gap-8">
                    <div class="p-6 rounded-2xl bg-stone-50 border border-stone-200">
                        <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center font-bold text-lg mb-4">🎨</div>
                        <h3 class="font-bold text-stone-900 mb-2">Visual Drag-and-Drop Editor</h3>
                        <p class="text-xs text-stone-600 leading-relaxed">
                            Sesuaikan font, warna, ukuran, foto pasangan, dan urutan section dengan kanvas interaktif yang responsif dan autosave.
                        </p>
                    </div>

                    <div class="p-6 rounded-2xl bg-stone-50 border border-stone-200">
                        <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center font-bold text-lg mb-4">💌</div>
                        <h3 class="font-bold text-stone-900 mb-2">Daftar Tamu & Link Khusus</h3>
                        <p class="text-xs text-stone-600 leading-relaxed">
                            Buat link undangan personal dengan nama tamu tertentu dan bagikan langsung ke WhatsApp dengan satu klik.
                        </p>
                    </div>

                    <div class="p-6 rounded-2xl bg-stone-50 border border-stone-200">
                        <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold text-lg mb-4">📋</div>
                        <h3 class="font-bold text-stone-900 mb-2">RSVP & Moderasi Ucapan</h3>
                        <p class="text-xs text-stone-600 leading-relaxed">
                            Pantau jumlah tamu hadir secara real-time dan moderasi ucapan selamat di buku tamu agar tetap tertib dan sopan.
                        </p>
                    </div>
                </div>
            </div>
        </section>
    </AppLayout>
</template>

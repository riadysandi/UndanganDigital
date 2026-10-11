<script setup lang="ts">
import { Link, usePage, router } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => (page.props as any).auth?.user);
const flash = computed(() => (page.props as any).flash || {});

const logout = () => {
    router.post('/logout');
};
</script>

<template>
    <div class="min-h-screen bg-stone-50 flex flex-col font-sans">
        <!-- Navigation Bar -->
        <header class="bg-white border-b border-stone-200 sticky top-0 z-40">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <!-- Brand Logo -->
                <div class="flex items-center gap-8">
                    <Link href="/" class="flex items-center gap-2 group">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-rose-500 to-amber-500 flex items-center justify-center text-white shadow-sm shadow-rose-200 group-hover:scale-105 transition-transform">
                            <span class="font-serif font-bold text-lg">U</span>
                        </div>
                        <span class="font-serif font-bold text-xl tracking-tight text-stone-800">
                            Undangan<span class="text-rose-600">.id</span>
                        </span>
                    </Link>

                    <nav class="hidden md:flex items-center gap-6 text-sm font-medium text-stone-600">
                        <Link href="/pricing" class="hover:text-stone-900 transition-colors">Paket & Harga</Link>
                        <Link href="/bantu-buat" class="text-rose-600 hover:text-rose-700 font-semibold transition-colors flex items-center gap-1.5">
                            <span class="text-xs">✨</span>
                            Dibantu Tim Kami
                        </Link>
                        <template v-if="user">
                            <Link href="/dashboard" class="hover:text-stone-900 transition-colors">Dashboard Saya</Link>
                            <Link v-if="user.role === 'admin'" href="/admin/dashboard" class="text-rose-600 hover:text-rose-700 font-semibold transition-colors flex items-center gap-1">
                                <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                                Admin Panel
                            </Link>
                        </template>
                    </nav>
                </div>

                <!-- User Controls -->
                <div class="flex items-center gap-3">
                    <template v-if="user">
                        <div class="flex items-center gap-3">
                            <div class="text-right hidden sm:block">
                                <div class="text-xs font-semibold text-stone-900">{{ user.name }}</div>
                                <div class="text-[11px] text-stone-400 capitalize">{{ user.role }}</div>
                            </div>
                            <div class="w-8 h-8 rounded-full bg-rose-100 text-rose-700 flex items-center justify-center font-bold text-xs border border-rose-200">
                                {{ user.name.charAt(0).toUpperCase() }}
                            </div>
                            <button
                                @click="logout"
                                class="text-xs text-stone-500 hover:text-rose-600 font-medium px-2 py-1 rounded transition-colors"
                            >
                                Keluar
                            </button>
                        </div>
                    </template>
                    <template v-else>
                        <Link
                            href="/login"
                            class="text-sm font-medium text-stone-600 hover:text-stone-900 px-3 py-1.5 transition-colors"
                        >
                            Masuk
                        </Link>
                        <Link
                            href="/register"
                            class="text-sm font-medium text-white bg-rose-600 hover:bg-rose-700 px-4 py-2 rounded-lg shadow-sm shadow-rose-200 transition-all hover:shadow"
                        >
                            Buat Undangan
                        </Link>
                    </template>
                </div>
            </div>
        </header>

        <!-- Flash Messages -->
        <div v-if="flash.success" class="bg-emerald-50 border-b border-emerald-200 text-emerald-800 text-xs sm:text-sm py-2 px-4 text-center">
            {{ flash.success }}
        </div>
        <div v-if="flash.error" class="bg-rose-50 border-b border-rose-200 text-rose-800 text-xs sm:text-sm py-2 px-4 text-center">
            {{ flash.error }}
        </div>

        <!-- Main Content Slot -->
        <main class="flex-1">
            <slot />
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-stone-200 py-8 text-center text-xs text-stone-500">
            <div class="max-w-7xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-4">
                <p>&copy; 2026 Platform Undangan Digital SaaS. Hak Cipta Dilindungi.</p>
                <div class="flex gap-4">
                    <Link href="/pricing" class="hover:text-stone-800">Paket Harga</Link>
                    <span class="text-stone-300">•</span>
                    <a href="#" class="hover:text-stone-800">Syarat & Ketentuan</a>
                    <span class="text-stone-300">•</span>
                    <a href="#" class="hover:text-stone-800">Kebijakan Privasi</a>
                </div>
            </div>
        </footer>
    </div>
</template>

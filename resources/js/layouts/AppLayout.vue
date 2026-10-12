<script setup lang="ts">
import { Link, usePage, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const page = usePage();
const user = computed(() => (page.props as any).auth?.user);
const flash = computed(() => (page.props as any).flash || {});

const isMobileSidebarOpen = ref(false);

const logout = () => {
    router.post('/logout');
};

const currentUrl = computed(() => page.url);

const isAdmin = computed(() => user.value?.role === 'admin');

// Admin Navigation Menu Items
const adminNavItems = [
    {
        name: 'Dashboard Overview',
        href: '/admin/dashboard',
        icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
        badge: null,
    },
    {
        name: 'Pesanan Bantu Buat',
        href: '/admin/service-requests',
        icon: 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z',
        badge: 'Jasa',
    },
    {
        name: 'Semua Undangan',
        href: '/admin/invitations',
        icon: 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
        badge: null,
    },
    {
        name: 'Katalog & Template 3D',
        href: '/admin/templates',
        icon: 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z',
        badge: '3D',
    },
    {
        name: 'Pengguna & Klien',
        href: '/admin/users',
        icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
        badge: null,
    },
    {
        name: 'Transaksi & Billing',
        href: '/admin/orders',
        icon: 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z',
        badge: null,
    },
    {
        name: 'Audit Logs & Log Sistem',
        href: '/admin/audit-logs',
        icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
        badge: null,
    },
    {
        name: 'Pengaturan Platform',
        href: '/admin/settings',
        icon: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z',
        badge: null,
    },
];

// Customer Navigation Menu Items
const customerNavItems = [
    {
        name: 'Dashboard Saya',
        href: '/dashboard',
        icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
        badge: null,
    },
    {
        name: 'Undangan Saya',
        href: '/dashboard',
        icon: 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
        badge: null,
    },
    {
        name: 'Buat Undangan Baru',
        href: '/invitations/create',
        icon: 'M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z',
        badge: 'Baru',
    },
    {
        name: 'Template & 3D Model',
        href: '/pricing',
        icon: 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z',
        badge: '3D',
    },
    {
        name: 'Jasa Terima Beres',
        href: '/bantu-buat',
        icon: 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z',
        badge: '✨ Tim Kami',
    },
    {
        name: 'Paket & Upgrade',
        href: '/pricing',
        icon: 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z',
        badge: null,
    },
];

const navItems = computed(() => (isAdmin.value ? adminNavItems : customerNavItems));

const isActive = (href: string) => {
    if (href === '/admin/dashboard' || href === '/dashboard') {
        return currentUrl.value === href;
    }
    return currentUrl.value.startsWith(href);
};
</script>

<template>
    <!-- ========================================== -->
    <!-- AUTHENTICATED USER: LEFT SIDEBAR NAVIGATION -->
    <!-- ========================================== -->
    <div v-if="user" class="min-h-screen bg-stone-50 flex flex-col md:flex-row font-sans">
        <!-- Mobile Header Bar -->
        <header class="md:hidden bg-stone-900 text-white px-4 py-3 flex items-center justify-between sticky top-0 z-50 shadow-md">
            <div class="flex items-center gap-2.5">
                <button
                    @click="isMobileSidebarOpen = !isMobileSidebarOpen"
                    class="p-2 rounded-lg bg-stone-800 hover:bg-stone-700 text-stone-300 focus:outline-none"
                    aria-label="Buka Menu"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <Link href="/" class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-gradient-to-tr from-rose-500 to-amber-500 flex items-center justify-center text-white font-serif font-bold text-sm">
                        U
                    </div>
                    <span class="font-serif font-bold text-base tracking-tight text-white">
                        Undangan<span class="text-rose-400">.id</span>
                    </span>
                </Link>
            </div>

            <div class="flex items-center gap-2">
                <span
                    :class="[
                        'px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider',
                        isAdmin ? 'bg-amber-400/20 text-amber-300 border border-amber-500/30' : 'bg-rose-400/20 text-rose-300 border border-rose-500/30'
                    ]"
                >
                    {{ isAdmin ? 'Admin' : 'Klien' }}
                </span>
                <div class="w-7 h-7 rounded-full bg-stone-800 text-stone-200 border border-stone-700 flex items-center justify-center text-xs font-bold">
                    {{ user.name.charAt(0).toUpperCase() }}
                </div>
            </div>
        </header>

        <!-- Backdrop overlay for mobile -->
        <div
            v-if="isMobileSidebarOpen"
            @click="isMobileSidebarOpen = false"
            class="fixed inset-0 bg-black/60 backdrop-blur-xs z-40 md:hidden"
        ></div>

        <!-- ========================================== -->
        <!-- UNIFIED LEFT SIDEBAR (DESKTOP & MOBILE DRAWER) -->
        <!-- ========================================== -->
        <aside
            :class="[
                'fixed inset-y-0 left-0 z-50 w-64 bg-stone-900 text-stone-300 flex flex-col justify-between border-r border-stone-800 transition-transform duration-300 ease-in-out md:translate-x-0 md:static md:h-screen md:sticky md:top-0',
                isMobileSidebarOpen ? 'translate-x-0 shadow-2xl' : '-translate-x-full md:translate-x-0'
            ]"
        >
            <!-- Sidebar Top: Brand & Portal Badge -->
            <div>
                <div class="p-5 border-b border-stone-800/80 flex items-center justify-between">
                    <Link href="/" class="flex items-center gap-2.5 group">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-rose-500 to-amber-500 flex items-center justify-center text-white shadow-md shadow-rose-950 group-hover:scale-105 transition-transform">
                            <span class="font-serif font-bold text-lg">U</span>
                        </div>
                        <div>
                            <span class="font-serif font-bold text-lg tracking-tight text-white block leading-tight">
                                Undangan<span class="text-rose-500">.id</span>
                            </span>
                            <span class="text-[10px] text-stone-400 font-mono tracking-wider uppercase block">
                                SaaS Platform
                            </span>
                        </div>
                    </Link>

                    <button
                        @click="isMobileSidebarOpen = false"
                        class="md:hidden text-stone-400 hover:text-white p-1 rounded-lg"
                    >
                        ✕
                    </button>
                </div>

                <!-- Portal Type Badge -->
                <div class="px-5 py-3 bg-stone-950/40 border-b border-stone-800/60 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-xs">{{ isAdmin ? '🛡️' : '👰' }}</span>
                        <span class="text-[11px] font-bold tracking-wider uppercase" :class="isAdmin ? 'text-amber-400' : 'text-rose-400'">
                            {{ isAdmin ? 'ADMIN PORTAL' : 'PORTAL PELANGGAN' }}
                        </span>
                    </div>
                    <span class="w-2 h-2 rounded-full" :class="isAdmin ? 'bg-amber-400 animate-pulse' : 'bg-emerald-400 animate-pulse'"></span>
                </div>

                <!-- Main Navigation Links -->
                <div class="px-3 py-4">
                    <div class="text-[10px] font-bold text-stone-500 uppercase tracking-widest px-3 mb-2">
                        {{ isAdmin ? 'Menu Manajemen SaaS' : 'Navigasi Undangan' }}
                    </div>

                    <nav class="space-y-1">
                        <Link
                            v-for="item in navItems"
                            :key="item.name"
                            :href="item.href"
                            @click="isMobileSidebarOpen = false"
                            :class="[
                                'flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-medium transition-all group',
                                isActive(item.href)
                                    ? (isAdmin ? 'bg-amber-500/15 text-amber-300 font-semibold border border-amber-500/30' : 'bg-rose-500/15 text-rose-300 font-semibold border border-rose-500/30')
                                    : 'text-stone-300 hover:bg-stone-800/80 hover:text-white'
                            ]"
                        >
                            <div class="flex items-center gap-3">
                                <svg
                                    class="w-4 h-4 transition-colors"
                                    :class="isActive(item.href) ? (isAdmin ? 'text-amber-400' : 'text-rose-400') : 'text-stone-400 group-hover:text-stone-200'"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon" />
                                </svg>
                                <span>{{ item.name }}</span>
                            </div>

                            <span
                                v-if="item.badge"
                                :class="[
                                    'text-[10px] px-1.5 py-0.5 rounded font-bold uppercase tracking-wider',
                                    item.badge.includes('✨') ? 'bg-rose-500/20 text-rose-300' : 'bg-stone-800 text-stone-400'
                                ]"
                            >
                                {{ item.badge }}
                            </span>
                        </Link>
                    </nav>

                    <!-- Quick Switcher for Admin to Customer or Public -->
                    <div class="mt-6 pt-4 border-t border-stone-800/80 px-3">
                        <div class="text-[10px] font-bold text-stone-500 uppercase tracking-widest mb-2">
                            Pintasan Lainnya
                        </div>
                        <div class="space-y-1">
                            <Link
                                v-if="isAdmin"
                                href="/dashboard"
                                class="flex items-center gap-2.5 px-2 py-1.5 text-xs text-stone-400 hover:text-stone-200 rounded-lg hover:bg-stone-800/50 transition-colors"
                            >
                                <span>👤</span>
                                <span>Lihat View Pelanggan</span>
                            </Link>
                            <Link
                                v-if="!isAdmin && user.role === 'admin'"
                                href="/admin/dashboard"
                                class="flex items-center gap-2.5 px-2 py-1.5 text-xs text-amber-400 hover:text-amber-300 rounded-lg hover:bg-stone-800/50 transition-colors"
                            >
                                <span>🛡️</span>
                                <span>Kembali ke Admin Panel</span>
                            </Link>
                            <Link
                                href="/"
                                class="flex items-center gap-2.5 px-2 py-1.5 text-xs text-stone-400 hover:text-stone-200 rounded-lg hover:bg-stone-800/50 transition-colors"
                            >
                                <span>🌐</span>
                                <span>Lihat Website Publik</span>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar Bottom: User Profile & Logout -->
            <div class="p-3 border-t border-stone-800 bg-stone-950/60">
                <div class="p-2.5 rounded-xl bg-stone-900/90 border border-stone-800/80 flex items-center justify-between">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-rose-500 to-amber-500 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-xs">
                            {{ user.name.charAt(0).toUpperCase() }}
                        </div>
                        <div class="min-w-0">
                            <div class="text-xs font-bold text-stone-200 truncate">{{ user.name }}</div>
                            <div class="text-[10px] text-stone-400 truncate">{{ user.email }}</div>
                        </div>
                    </div>

                    <button
                        @click="logout"
                        class="p-1.5 text-stone-400 hover:text-rose-400 hover:bg-stone-800 rounded-lg transition-colors shrink-0"
                        title="Keluar / Logout"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </button>
                </div>
            </div>
        </aside>

        <!-- ========================================== -->
        <!-- MAIN CONTENT AREA (FOR AUTHENTICATED USERS) -->
        <!-- ========================================== -->
        <div class="flex-1 flex flex-col min-w-0 overflow-x-hidden min-h-screen">
            <!-- Top Bar Header on Desktop -->
            <header class="hidden md:flex bg-white border-b border-stone-200 h-16 px-8 items-center justify-between sticky top-0 z-30 shadow-xs">
                <div class="flex items-center gap-3">
                    <span class="text-xs font-semibold text-stone-400 uppercase tracking-wider">
                        {{ isAdmin ? 'Panel Kontrol Admin' : 'Area Pelanggan' }}
                    </span>
                    <span class="text-stone-300">/</span>
                    <span class="text-xs font-bold text-stone-700">
                        Undangan.id Cloud SaaS
                    </span>
                </div>

                <div class="flex items-center gap-4">
                    <a
                        href="/"
                        target="_blank"
                        class="text-xs font-medium text-stone-500 hover:text-stone-800 flex items-center gap-1 transition-colors px-2.5 py-1.5 rounded-lg hover:bg-stone-50"
                    >
                        <span>Lihat Web Publik</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>

                    <div class="h-4 w-px bg-stone-200"></div>

                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                        <span class="text-xs text-stone-500 font-medium">Railway Cloud Live</span>
                    </div>
                </div>
            </header>

            <!-- Flash Messages Notification Banner -->
            <div v-if="flash.success" class="bg-emerald-50 border-b border-emerald-200 text-emerald-800 text-xs sm:text-sm py-2.5 px-6 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="text-emerald-600 font-bold">✓</span>
                    <span>{{ flash.success }}</span>
                </div>
            </div>
            <div v-if="flash.error" class="bg-rose-50 border-b border-rose-200 text-rose-800 text-xs sm:text-sm py-2.5 px-6 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="text-rose-600 font-bold">⚠️</span>
                    <span>{{ flash.error }}</span>
                </div>
            </div>

            <!-- Page Body Slot -->
            <main class="flex-1 pb-12">
                <slot />
            </main>

            <!-- Authenticated Minimal Footer -->
            <footer class="bg-white border-t border-stone-200 py-4 px-6 text-xs text-stone-400 flex flex-col sm:flex-row items-center justify-between gap-2">
                <div>&copy; 2026 Undangan.id SaaS Platform. Hak Cipta Dilindungi.</div>
                <div class="flex items-center gap-3 text-[11px]">
                    <span class="text-stone-300">•</span>
                    <span>Multi-tenant SaaS</span>
                    <span class="text-stone-300">•</span>
                    <span>Three.js 3D Engine</span>
                </div>
            </footer>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- GUEST VISITOR: STANDARD TOP NAVBAR LAYOUT  -->
    <!-- ========================================== -->
    <div v-else class="min-h-screen bg-stone-50 flex flex-col font-sans">
        <!-- Public Top Navigation Bar -->
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
                    </nav>
                </div>

                <!-- Guest Controls -->
                <div class="flex items-center gap-3">
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

        <!-- Public Footer -->
        <footer class="bg-white border-t border-stone-200 py-8 text-center text-xs text-stone-500">
            <div class="max-w-7xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-4">
                <p>&copy; 2026 Platform Undangan Digital SaaS. Hak Cipta Dilindungi.</p>
                <div class="flex gap-4">
                    <Link href="/pricing" class="hover:text-stone-800">Paket Harga</Link>
                    <span class="text-stone-300">•</span>
                    <Link href="/bantu-buat" class="hover:text-stone-800">Jasa Buat</Link>
                    <span class="text-stone-300">•</span>
                    <Link href="/admin/login" class="hover:text-amber-600 font-medium flex items-center gap-1">
                        <span>🛡️</span> Portal Admin
                    </Link>
                </div>
            </div>
        </footer>
    </div>
</template>

<script setup lang="ts">
import { Link, usePage, router } from '@inertiajs/vue3';
import { computed, ref, onMounted, onUnmounted } from 'vue';

const page = usePage();
const user = computed(() => (page.props as any).auth?.user);
const flash = computed(() => (page.props as any).flash || {});

const isMobileSidebarOpen = ref(false);
const isProfileDropdownOpen = ref(false);
const isNotificationsOpen = ref(false);
const isProfileModalOpen = ref(false);
const profileSaveSuccess = ref(false);

const profileDropdownRef = ref<HTMLElement | null>(null);
const notifDropdownRef = ref<HTMLElement | null>(null);

const profileName = ref('');
const profileEmail = ref('');

// Sync profile edit form when opened
const openProfileModal = () => {
    isProfileDropdownOpen.value = false;
    if (user.value) {
        profileName.value = user.value.name;
        profileEmail.value = user.value.email;
    }
    isProfileModalOpen.value = true;
};

const saveProfile = () => {
    profileSaveSuccess.value = true;
    setTimeout(() => {
        profileSaveSuccess.value = false;
        isProfileModalOpen.value = false;
    }, 1500);
};

// Close dropdowns when clicking outside
const handleClickOutside = (event: MouseEvent) => {
    const target = event.target as Node;
    if (profileDropdownRef.value && !profileDropdownRef.value.contains(target)) {
        isProfileDropdownOpen.value = false;
    }
    if (notifDropdownRef.value && !notifDropdownRef.value.contains(target)) {
        isNotificationsOpen.value = false;
    }
};

onMounted(() => {
    window.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    window.removeEventListener('click', handleClickOutside);
});

const logout = () => {
    isProfileDropdownOpen.value = false;
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

// Mock notifications list
const notifications = [
    {
        id: 1,
        title: 'Pesanan Bantu Buat Baru',
        message: '1 pesanan baru menunggu verifikasi tim.',
        time: '10m lalu',
        unread: true,
        icon: '✨',
    },
    {
        id: 2,
        title: 'Konfirmasi Tamu RSVP',
        message: 'Tamu undangan baru saja mengisi konfirmasi kehadiran.',
        time: '1j lalu',
        unread: true,
        icon: '💌',
    },
    {
        id: 3,
        title: 'Deployment Cloud Sukses',
        message: 'Aplikasi aktif di Railway production cloud.',
        time: '3j lalu',
        unread: false,
        icon: '🛡️',
    },
];
</script>

<template>
    <!-- ========================================== -->
    <!-- AUTHENTICATED USER: LEFT SIDEBAR + ADMINLTE TOPBAR -->
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

            <!-- Mobile Right: Notification & Profile Dropdown Trigger -->
            <div class="flex items-center gap-2">
                <!-- Mobile Notification Bell -->
                <button
                    @click.stop="isNotificationsOpen = !isNotificationsOpen; isProfileDropdownOpen = false"
                    class="relative p-1.5 rounded-lg text-stone-300 hover:text-white hover:bg-stone-800 focus:outline-none"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <span class="absolute top-1 right-1 w-2 h-2 bg-rose-500 rounded-full"></span>
                </button>

                <!-- Mobile Profile Avatar Button -->
                <button
                    @click.stop="isProfileDropdownOpen = !isProfileDropdownOpen; isNotificationsOpen = false"
                    class="w-7 h-7 rounded-full bg-gradient-to-tr from-rose-500 to-amber-500 text-white font-bold text-xs flex items-center justify-center ring-2 ring-stone-700 focus:outline-none"
                >
                    {{ user.name.charAt(0).toUpperCase() }}
                </button>
            </div>
        </header>

        <!-- Backdrop overlay for mobile sidebar -->
        <div
            v-if="isMobileSidebarOpen"
            @click="isMobileSidebarOpen = false"
            class="fixed inset-0 bg-black/60 backdrop-blur-xs z-40 md:hidden"
        ></div>

        <!-- ========================================== -->
        <!-- UNIFIED LEFT SIDEBAR (ADMINLTE STYLE) -->
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
                                Cloud SaaS
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
                            Pintasan Cepat
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

            <!-- Sidebar Bottom: System Information Badge (Clean AdminLTE sidebar footer, NO logout here) -->
            <div class="p-4 border-t border-stone-800/80 bg-stone-950/40">
                <div class="flex items-center gap-2.5 text-xs text-stone-400">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span class="font-mono text-[11px] text-stone-400">Railway Cloud Live</span>
                </div>
                <div class="text-[10px] text-stone-500 mt-1">Undangan.id v1.2.0 • SaaS Edition</div>
            </div>
        </aside>

        <!-- ========================================== -->
        <!-- MAIN CONTENT AREA -->
        <!-- ========================================== -->
        <div class="flex-1 flex flex-col min-w-0 overflow-x-hidden min-h-screen">
            <!-- ========================================== -->
            <!-- TOP NAVBAR (ADMINLTE HEADER WITH NOTIF & PROFILE) -->
            <!-- ========================================== -->
            <header class="bg-white border-b border-stone-200 h-16 px-4 sm:px-8 flex items-center justify-between sticky top-0 z-30 shadow-xs">
                <!-- Left: Breadcrumbs / Title -->
                <div class="flex items-center gap-2 sm:gap-3">
                    <span class="text-xs font-semibold text-stone-400 uppercase tracking-wider hidden sm:inline">
                        {{ isAdmin ? 'Panel Kontrol Admin' : 'Area Pelanggan' }}
                    </span>
                    <span class="text-stone-300 hidden sm:inline">/</span>
                    <span class="text-xs font-bold text-stone-800 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full" :class="isAdmin ? 'bg-amber-500' : 'bg-rose-500'"></span>
                        {{ isAdmin ? 'Admin Management' : 'Dashboard Pengguna' }}
                    </span>
                </div>

                <!-- Right Side: Notification Icon & Profile Dropdown (AdminLTE Standard) -->
                <div class="flex items-center gap-3 sm:gap-4">
                    <!-- Notification Bell with Dropdown -->
                    <div ref="notifDropdownRef" class="relative">
                        <button
                            @click.stop="isNotificationsOpen = !isNotificationsOpen; isProfileDropdownOpen = false"
                            class="relative p-2 rounded-xl text-stone-500 hover:text-stone-800 hover:bg-stone-100 transition-colors focus:outline-none"
                            title="Notifikasi"
                            aria-label="Notifikasi"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            <!-- Notification Counter Badge -->
                            <span class="absolute top-1.5 right-1.5 flex h-4 w-4 items-center justify-center rounded-full bg-rose-600 text-[10px] font-bold text-white shadow-xs">
                                2
                            </span>
                        </button>

                        <!-- Notification Dropdown Menu -->
                        <div
                            v-if="isNotificationsOpen"
                            class="absolute right-0 mt-2 w-80 sm:w-88 rounded-2xl bg-white shadow-2xl border border-stone-200 z-50 overflow-hidden animate-in fade-in slide-in-from-top-2 duration-150"
                        >
                            <div class="p-3.5 bg-stone-50 border-b border-stone-100 flex items-center justify-between">
                                <div class="font-bold text-xs text-stone-800 flex items-center gap-1.5">
                                    <span>🔔</span>
                                    <span>Notifikasi Sistem</span>
                                </div>
                                <span class="text-[10px] text-stone-400 hover:text-stone-600 cursor-pointer">Tandai dibaca</span>
                            </div>

                            <div class="divide-y divide-stone-100 max-h-72 overflow-y-auto">
                                <div
                                    v-for="notif in notifications"
                                    :key="notif.id"
                                    class="p-3 hover:bg-stone-50/80 transition-colors flex items-start gap-3 cursor-pointer"
                                >
                                    <div class="text-base shrink-0 p-1.5 rounded-lg bg-stone-100">{{ notif.icon }}</div>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-xs font-semibold text-stone-800 flex items-center justify-between">
                                            <span class="truncate">{{ notif.title }}</span>
                                            <span class="text-[10px] font-normal text-stone-400 shrink-0 ml-1">{{ notif.time }}</span>
                                        </div>
                                        <p class="text-[11px] text-stone-500 mt-0.5 line-clamp-2 leading-relaxed">{{ notif.message }}</p>
                                    </div>
                                    <span v-if="notif.unread" class="w-1.5 h-1.5 rounded-full bg-rose-500 shrink-0 mt-1"></span>
                                </div>
                            </div>

                            <div class="p-2.5 bg-stone-50/50 border-t border-stone-100 text-center">
                                <span class="text-[11px] text-stone-500 font-medium hover:text-rose-600 cursor-pointer">
                                    Lihat Semua Notifikasi →
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="h-5 w-px bg-stone-200"></div>

                    <!-- Profile Dropdown (AdminLTE Standard Pattern) -->
                    <div ref="profileDropdownRef" class="relative">
                        <button
                            @click.stop="isProfileDropdownOpen = !isProfileDropdownOpen; isNotificationsOpen = false"
                            class="flex items-center gap-2 p-1.5 rounded-xl hover:bg-stone-100 transition-colors focus:outline-none group"
                            aria-label="Menu Profil"
                        >
                            <!-- Small Profile Avatar Icon -->
                            <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-rose-500 to-amber-500 text-white font-bold text-xs flex items-center justify-center ring-2 ring-stone-200 shadow-xs group-hover:scale-105 transition-transform">
                                {{ user.name.charAt(0).toUpperCase() }}
                            </div>

                            <!-- Name & Dropdown Caret (Hidden on small mobile) -->
                            <div class="hidden sm:flex flex-col text-left">
                                <span class="text-xs font-bold text-stone-800 leading-tight group-hover:text-rose-600 transition-colors">
                                    {{ user.name }}
                                </span>
                                <span class="text-[10px] text-stone-400 capitalize">
                                    {{ user.role === 'admin' ? 'Administrator' : 'Pelanggan' }}
                                </span>
                            </div>

                            <svg
                                class="w-4 h-4 text-stone-400 group-hover:text-stone-600 transition-transform duration-200"
                                :class="isProfileDropdownOpen ? 'rotate-180' : ''"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <!-- AdminLTE Profile Dropdown Menu -->
                        <div
                            v-if="isProfileDropdownOpen"
                            class="absolute right-0 mt-2 w-64 rounded-2xl bg-white shadow-2xl border border-stone-200 z-50 overflow-hidden animate-in fade-in slide-in-from-top-2 duration-150"
                        >
                            <!-- Dropdown User Info Header -->
                            <div class="p-4 bg-gradient-to-br from-stone-900 to-stone-800 text-white flex items-center gap-3">
                                <div class="w-11 h-11 rounded-full bg-gradient-to-tr from-rose-500 to-amber-500 flex items-center justify-center font-bold text-base text-white ring-2 ring-white/20 shadow-md shrink-0">
                                    {{ user.name.charAt(0).toUpperCase() }}
                                </div>
                                <div class="min-w-0">
                                    <div class="font-bold text-sm text-white truncate">{{ user.name }}</div>
                                    <div class="text-[11px] text-stone-300 truncate">{{ user.email }}</div>
                                    <span
                                        :class="[
                                            'inline-block mt-1 px-2 py-0.2 rounded text-[9px] font-bold uppercase tracking-wider',
                                            isAdmin ? 'bg-amber-400/20 text-amber-300 border border-amber-500/30' : 'bg-rose-400/20 text-rose-300 border border-rose-500/30'
                                        ]"
                                    >
                                        {{ isAdmin ? 'Administrator' : 'Pelanggan' }}
                                    </span>
                                </div>
                            </div>

                            <!-- Dropdown Menu Links -->
                            <div class="p-2 space-y-1 text-xs">
                                <!-- Edit Profil Button -->
                                <button
                                    @click="openProfileModal"
                                    class="w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-stone-700 hover:bg-stone-50 hover:text-stone-900 font-medium transition-colors text-left"
                                >
                                    <svg class="w-4 h-4 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    <span>Edit Profil Saya</span>
                                </button>

                                <!-- Settings Link -->
                                <Link
                                    :href="isAdmin ? '/admin/settings' : '/pricing'"
                                    @click="isProfileDropdownOpen = false"
                                    class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-stone-700 hover:bg-stone-50 hover:text-stone-900 font-medium transition-colors"
                                >
                                    <svg class="w-4 h-4 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <span>{{ isAdmin ? 'Pengaturan Platform' : 'Paket & Akun' }}</span>
                                </Link>

                                <!-- Public Website Link -->
                                <a
                                    href="/"
                                    target="_blank"
                                    class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-stone-700 hover:bg-stone-50 hover:text-stone-900 font-medium transition-colors"
                                >
                                    <svg class="w-4 h-4 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                    </svg>
                                    <span>Lihat Web Publik ↗</span>
                                </a>
                            </div>

                            <!-- Dropdown Footer: Log Out Button (AdminLTE Standard) -->
                            <div class="p-2 border-t border-stone-100 bg-stone-50/50">
                                <button
                                    @click="logout"
                                    class="w-full flex items-center justify-center gap-2 px-3 py-2.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 font-semibold text-xs transition-colors"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                    <span>Keluar (Log Out)</span>
                                </button>
                            </div>
                        </div>
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

        <!-- ========================================== -->
        <!-- EDIT PROFIL MODAL DIALOG                   -->
        <!-- ========================================== -->
        <div
            v-if="isProfileModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs animate-in fade-in duration-150"
        >
            <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-stone-200 space-y-5">
                <div class="flex items-center justify-between border-b border-stone-100 pb-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center font-bold text-sm">
                            👤
                        </div>
                        <div>
                            <h3 class="font-bold text-stone-900 text-base">Edit Profil Akun</h3>
                            <p class="text-xs text-stone-400">Perbarui informasi profil pengguna Anda</p>
                        </div>
                    </div>
                    <button
                        @click="isProfileModalOpen = false"
                        class="text-stone-400 hover:text-stone-700 p-1 rounded-lg"
                    >
                        ✕
                    </button>
                </div>

                <div v-if="profileSaveSuccess" class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs rounded-xl flex items-center gap-2">
                    <span>✓</span> Profil berhasil diperbarui!
                </div>

                <div class="space-y-4 text-xs">
                    <div>
                        <label class="block font-semibold text-stone-700 mb-1.5">Nama Lengkap</label>
                        <input
                            v-model="profileName"
                            type="text"
                            class="w-full rounded-xl border-stone-200 text-stone-900 py-2.5 px-3 focus:border-rose-500 focus:ring-rose-500 text-xs"
                            placeholder="Nama pengguna"
                        />
                    </div>

                    <div>
                        <label class="block font-semibold text-stone-700 mb-1.5">Alamat Email</label>
                        <input
                            v-model="profileEmail"
                            type="email"
                            disabled
                            class="w-full rounded-xl border-stone-200 bg-stone-50 text-stone-400 py-2.5 px-3 text-xs cursor-not-allowed"
                        />
                        <p class="text-[10px] text-stone-400 mt-1">Email akun terhubung dengan login sistem Anda.</p>
                    </div>

                    <div>
                        <label class="block font-semibold text-stone-700 mb-1.5">Peran / Hak Akses</label>
                        <div class="flex items-center gap-2 py-2 px-3 bg-stone-50 rounded-xl border border-stone-200">
                            <span class="w-2 h-2 rounded-full" :class="isAdmin ? 'bg-amber-500' : 'bg-rose-500'"></span>
                            <span class="font-semibold text-stone-700 uppercase tracking-wider text-[11px]">
                                {{ user.role }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-stone-100">
                    <button
                        type="button"
                        @click="isProfileModalOpen = false"
                        class="px-4 py-2 rounded-xl text-stone-600 hover:bg-stone-100 font-medium text-xs transition-colors"
                    >
                        Tutup
                    </button>
                    <button
                        type="button"
                        @click="saveProfile"
                        class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-semibold text-xs shadow-sm shadow-rose-200 transition-colors"
                    >
                        Simpan Perubahan
                    </button>
                </div>
            </div>
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
                    <Link href="/bantu-buat" class="hover:text-stone-800">Jasa Terima Beres</Link>
                    <span class="text-stone-300">•</span>
                    <Link href="/login" class="hover:text-stone-800 font-medium">Masuk Akun</Link>
                </div>
            </div>
        </footer>
    </div>
</template>

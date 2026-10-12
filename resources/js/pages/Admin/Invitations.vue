<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

defineProps<{
    invitations: {
        data: Array<{
            id: number;
            title: string;
            slug: string;
            status: string;
            event_date: string | null;
            created_at: string;
            guests_count: number;
            rsvps_count: number;
            user: {
                name: string;
                email: string;
            };
            template: {
                name: string;
                category: string;
            } | null;
            published_version: {
                id: number;
                published_at: string;
            } | null;
        }>;
    };
}>();
</script>

<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">
            <!-- Header Section -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 border-b border-stone-200 gap-4">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="px-2 py-0.5 rounded text-[11px] font-bold uppercase tracking-wider bg-rose-100 text-rose-800">
                            Undangan Digital
                        </span>
                        <span class="text-xs text-stone-400">• Total Semua Pengguna</span>
                    </div>
                    <h1 class="text-2xl font-serif font-bold text-stone-900">Kelola Semua Undangan</h1>
                    <p class="text-xs text-stone-500 mt-1">Daftar semua undangan digital yang dibuat oleh seluruh klien di platform.</p>
                </div>
            </div>

            <!-- Table Section -->
            <div class="bg-white rounded-2xl border border-stone-200 overflow-hidden shadow-sm mt-8">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-stone-600">
                        <thead class="bg-stone-50 border-b border-stone-200 text-stone-500 font-semibold uppercase text-[10px]">
                            <tr>
                                <th class="p-4">Undangan</th>
                                <th class="p-4">Pemilik (User)</th>
                                <th class="p-4">Template & Tema</th>
                                <th class="p-4">Tamu & RSVP</th>
                                <th class="p-4">Status</th>
                                <th class="p-4">Tanggal Acara</th>
                                <th class="p-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100">
                            <tr v-for="inv in invitations.data" :key="inv.id" class="hover:bg-stone-50/50 transition-colors">
                                <td class="p-4">
                                    <div class="font-bold text-stone-900 text-sm">{{ inv.title }}</div>
                                    <div class="text-[11px] font-mono text-stone-400">/i/{{ inv.slug }}</div>
                                </td>
                                <td class="p-4">
                                    <div class="font-medium text-stone-900">{{ inv.user?.name || '-' }}</div>
                                    <div class="text-[11px] text-stone-400">{{ inv.user?.email || '-' }}</div>
                                </td>
                                <td class="p-4">
                                    <span v-if="inv.template" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-stone-100 text-stone-800 font-medium text-[11px]">
                                        <span>🎨</span>
                                        {{ inv.template.name }}
                                    </span>
                                    <span v-else class="text-stone-400 text-xs italic">Custom / Default</span>
                                </td>
                                <td class="p-4">
                                    <div class="flex items-center gap-3">
                                        <div class="text-center">
                                            <span class="block font-bold text-stone-800">{{ inv.guests_count }}</span>
                                            <span class="text-[10px] text-stone-400">Tamu</span>
                                        </div>
                                        <div class="h-6 w-px bg-stone-200"></div>
                                        <div class="text-center">
                                            <span class="block font-bold text-emerald-600">{{ inv.rsvps_count }}</span>
                                            <span class="text-[10px] text-stone-400">RSVP</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <span :class="[
                                        'px-2.5 py-1 rounded-full text-[10px] font-bold capitalize inline-flex items-center gap-1',
                                        inv.status === 'published' ? 'bg-emerald-100 text-emerald-800' :
                                        inv.status === 'draft' ? 'bg-amber-100 text-amber-800' : 'bg-stone-100 text-stone-600'
                                    ]">
                                        <span class="w-1.5 h-1.5 rounded-full" :class="inv.status === 'published' ? 'bg-emerald-500' : 'bg-amber-500'"></span>
                                        {{ inv.status }}
                                    </span>
                                </td>
                                <td class="p-4 text-stone-500">
                                    {{ inv.event_date ? new Date(inv.event_date).toLocaleDateString('id-ID', { dateStyle: 'medium' }) : '-' }}
                                </td>
                                <td class="p-4 text-right">
                                    <a
                                        :href="'/i/' + inv.slug"
                                        target="_blank"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-stone-100 hover:bg-stone-200 text-stone-700 font-medium rounded-lg text-xs transition-colors"
                                    >
                                        <span>Lihat Web</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    </a>
                                </td>
                            </tr>
                            <tr v-if="invitations.data.length === 0">
                                <td colspan="7" class="p-12 text-center text-stone-400">
                                    <div class="text-3xl mb-2">💌</div>
                                    <p class="font-medium text-stone-600">Belum ada undangan yang dibuat pengguna.</p>
                                    <p class="text-xs text-stone-400 mt-1">Undangan digital yang dibuat pengguna akan tampil di sini.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

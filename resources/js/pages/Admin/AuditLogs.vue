<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

defineProps<{
    logs: {
        data: Array<{
            id: number;
            action: string;
            subject_type: string | null;
            subject_id: number | null;
            metadata: Record<string, any> | null;
            ip_address: string | null;
            user_agent: string | null;
            created_at: string;
            actor: {
                name: string;
                email: string;
                role: string;
            } | null;
        }>;
        links:Array<{url:string|null;label:string;active:boolean}>;
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
                        <span class="px-2 py-0.5 rounded text-[11px] font-bold uppercase tracking-wider bg-indigo-100 text-indigo-800">
                            Keamanan & Rekam Jejak
                        </span>
                        <span class="text-xs text-stone-400">• Aktivitas Sistem</span>
                    </div>
                    <h1 class="text-2xl font-serif font-bold text-stone-900">Audit Logs & Rekam Aktivitas</h1>
                    <p class="text-xs text-stone-500 mt-1">Catatan aktivitas penting, audit trail, perubahan status, dan interaksi pengguna.</p>
                </div>
            </div>

            <!-- Table Section -->
            <div class="bg-white rounded-2xl border border-stone-200 overflow-hidden shadow-sm mt-8">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-stone-600">
                        <thead class="bg-stone-50 border-b border-stone-200 text-stone-500 font-semibold uppercase text-[10px]">
                            <tr>
                                <th class="p-4">Waktu</th>
                                <th class="p-4">Pelaku (Actor)</th>
                                <th class="p-4">Aksi / Aktivitas</th>
                                <th class="p-4">Subjek</th>
                                <th class="p-4">Metadata</th>
                                <th class="p-4">IP Address</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100">
                            <tr v-for="log in logs.data" :key="log.id" class="hover:bg-stone-50/50 transition-colors">
                                <td class="p-4 text-stone-500 font-mono text-[11px] whitespace-nowrap">
                                    {{ new Date(log.created_at).toLocaleString('id-ID') }}
                                </td>
                                <td class="p-4">
                                    <div v-if="log.actor">
                                        <div class="font-medium text-stone-900">{{ log.actor.name }}</div>
                                        <div class="text-[11px] text-stone-400 flex items-center gap-1.5">
                                            <span>{{ log.actor.email }}</span>
                                            <span class="px-1.5 py-0.2 rounded text-[9px] font-bold uppercase" :class="log.actor.role === 'admin' ? 'bg-amber-100 text-amber-800' : 'bg-stone-100 text-stone-600'">
                                                {{ log.actor.role }}
                                            </span>
                                        </div>
                                    </div>
                                    <div v-else class="text-stone-400 italic">
                                        Sistem / Anonim
                                    </div>
                                </td>
                                <td class="p-4">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md font-mono text-[11px] font-bold bg-stone-100 text-stone-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                                        {{ log.action }}
                                    </span>
                                </td>
                                <td class="p-4">
                                    <div v-if="log.subject_type" class="font-mono text-[11px] text-stone-600">
                                        {{ log.subject_type.split('\\').pop() }} #{{ log.subject_id }}
                                    </div>
                                    <div v-else class="text-stone-400">-</div>
                                </td>
                                <td class="p-4 max-w-xs">
                                    <pre v-if="log.metadata && Object.keys(log.metadata).length > 0" class="text-[10px] bg-stone-50 p-1.5 rounded border border-stone-200 overflow-x-auto text-stone-700 font-mono">{{ JSON.stringify(log.metadata, null, 2) }}</pre>
                                    <span v-else class="text-stone-400">-</span>
                                </td>
                                <td class="p-4 text-stone-500 font-mono text-[11px]">
                                    {{ log.ip_address || '127.0.0.1' }}
                                </td>
                            </tr>
                            <tr v-if="logs.data.length === 0">
                                <td colspan="6" class="p-12 text-center text-stone-400">
                                    <div class="text-3xl mb-2">🛡️</div>
                                    <p class="font-medium text-stone-600">Belum ada audit logs yang terekam.</p>
                                    <p class="text-xs text-stone-400 mt-1">Aktivitas penting sistem akan otomatis tercatat di sini.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <nav class="flex gap-2 mt-4"><Link v-for="link in logs.links" :key="link.label" :href="link.url || '/admin/audit-logs'" preserve-scroll class="px-3 py-1 rounded border text-xs" :class="link.active?'bg-stone-900 text-white':'bg-white'" v-html="link.label"/></nav>
        </div>
    </AppLayout>
</template>

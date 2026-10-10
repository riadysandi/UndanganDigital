<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

defineProps<{
    users: {
        data: Array<{
            id: number;
            name: string;
            email: string;
            role: string;
            invitations_count: number;
            created_at: string;
        }>;
    };
}>();
</script>

<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">
            <div class="flex items-center justify-between pb-6 border-b border-stone-200">
                <div>
                    <Link href="/admin/dashboard" class="text-xs font-semibold text-rose-600 hover:text-rose-700 flex items-center gap-1 mb-1">
                        ← Kembali ke Dashboard Admin
                    </Link>
                    <h1 class="text-2xl font-serif font-bold text-stone-900">Daftar Pengguna Platform</h1>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-stone-200 overflow-hidden shadow-sm mt-8">
                <table class="w-full text-left text-xs text-stone-600">
                    <thead class="bg-stone-50 border-b border-stone-200 text-stone-500 font-semibold uppercase text-[10px]">
                        <tr>
                            <th class="p-4">Nama</th>
                            <th class="p-4">Email</th>
                            <th class="p-4">Role</th>
                            <th class="p-4">Undangan</th>
                            <th class="p-4">Terdaftar</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        <tr v-for="user in users.data" :key="user.id" class="hover:bg-stone-50/50">
                            <td class="p-4 font-bold text-stone-900">{{ user.name }}</td>
                            <td class="p-4">{{ user.email }}</td>
                            <td class="p-4">
                                <span :class="['px-2 py-0.5 rounded text-[10px] font-bold capitalize', user.role === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-stone-100 text-stone-700']">
                                    {{ user.role }}
                                </span>
                            </td>
                            <td class="p-4">{{ user.invitations_count }} undangan</td>
                            <td class="p-4 text-stone-400">{{ new Date(user.created_at).toLocaleDateString() }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>

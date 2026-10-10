<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

defineProps<{
    stats: {
        total_users: number;
        total_invitations: number;
        published_invitations: number;
        total_revenue: number;
        total_orders: number;
    };
    recentOrders: Array<any>;
    recentUsers: Array<any>;
    recentLogs: Array<any>;
}>();

const formatRupiah = (val: string | number) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(Number(val));
};
</script>

<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-stone-200">
                <div>
                    <h1 class="text-2xl font-serif font-bold text-stone-900">Panel Administrasi SaaS</h1>
                    <p class="text-xs text-stone-500">Ringkasan analitik sistem, order, dan manajemen platform.</p>
                </div>
                <div class="flex gap-2">
                    <Link href="/admin/templates" class="px-3.5 py-2 bg-white border border-stone-200 rounded-xl text-xs font-semibold text-stone-700 hover:bg-stone-50">
                        Kelola Template
                    </Link>
                    <Link href="/admin/orders" class="px-3.5 py-2 bg-white border border-stone-200 rounded-xl text-xs font-semibold text-stone-700 hover:bg-stone-50">
                        Daftar Order
                    </Link>
                    <Link href="/admin/users" class="px-3.5 py-2 bg-white border border-stone-200 rounded-xl text-xs font-semibold text-stone-700 hover:bg-stone-50">
                        Daftar User
                    </Link>
                </div>
            </div>

            <!-- Metrics Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 my-8">
                <div class="p-5 bg-white rounded-2xl border border-stone-200">
                    <div class="text-[10px] uppercase font-bold text-stone-400">Total Pengguna</div>
                    <div class="font-serif text-3xl font-bold text-stone-900 mt-2">{{ stats.total_users }}</div>
                </div>
                <div class="p-5 bg-white rounded-2xl border border-stone-200">
                    <div class="text-[10px] uppercase font-bold text-stone-400">Undangan Dibuat</div>
                    <div class="font-serif text-3xl font-bold text-stone-900 mt-2">{{ stats.total_invitations }}</div>
                    <div class="text-[11px] text-emerald-600 mt-1">{{ stats.published_invitations }} published</div>
                </div>
                <div class="p-5 bg-white rounded-2xl border border-stone-200">
                    <div class="text-[10px] uppercase font-bold text-stone-400">Total Transaksi</div>
                    <div class="font-serif text-3xl font-bold text-stone-900 mt-2">{{ stats.total_orders }}</div>
                </div>
                <div class="p-5 bg-white rounded-2xl border border-stone-200">
                    <div class="text-[10px] uppercase font-bold text-emerald-600">Total Pendapatan</div>
                    <div class="font-serif text-2xl font-bold text-emerald-700 mt-2">{{ formatRupiah(stats.total_revenue) }}</div>
                </div>
            </div>

            <div class="grid lg:grid-cols-2 gap-8">
                <!-- Recent Orders -->
                <div class="bg-white p-6 rounded-2xl border border-stone-200 space-y-4">
                    <h3 class="font-serif font-bold text-base text-stone-900">Transaksi Terbaru</h3>
                    <div class="space-y-3">
                        <div
                            v-for="order in recentOrders"
                            :key="order.id"
                            class="p-3 bg-stone-50 rounded-xl flex items-center justify-between text-xs"
                        >
                            <div>
                                <div class="font-bold text-stone-800">{{ order.order_number }}</div>
                                <div class="text-stone-400 text-[11px]">{{ order.user?.email }} • {{ order.plan?.name }}</div>
                            </div>
                            <div class="text-right">
                                <div class="font-bold text-stone-900">{{ formatRupiah(order.amount) }}</div>
                                <span
                                    :class="[
                                        'px-2 py-0.5 rounded text-[10px] font-bold capitalize',
                                        order.status === 'paid' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'
                                    ]"
                                >
                                    {{ order.status }}
                                </span>
                            </div>
                        </div>
                        <div v-if="recentOrders.length === 0" class="text-center text-xs text-stone-400 py-4">
                            Belum ada order.
                        </div>
                    </div>
                </div>

                <!-- Recent Audit Logs -->
                <div class="bg-white p-6 rounded-2xl border border-stone-200 space-y-4">
                    <h3 class="font-serif font-bold text-base text-stone-900">Audit Logs Terbaru</h3>
                    <div class="space-y-2">
                        <div
                            v-for="log in recentLogs"
                            :key="log.id"
                            class="p-2.5 bg-stone-50 rounded-xl text-xs flex items-center justify-between"
                        >
                            <div>
                                <span class="font-mono font-bold text-rose-700">{{ log.action }}</span>
                                <span class="text-stone-400 text-[11px] ml-2">oleh {{ log.actor?.name || 'Sistem' }}</span>
                            </div>
                            <div class="text-[10px] text-stone-400">
                                {{ new Date(log.created_at).toLocaleTimeString() }}
                            </div>
                        </div>
                        <div v-if="recentLogs.length === 0" class="text-center text-xs text-stone-400 py-4">
                            Belum ada log.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

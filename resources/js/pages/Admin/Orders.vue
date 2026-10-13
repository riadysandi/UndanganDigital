<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps<{
    orders: {
        data: Array<{
            id: number;
            order_number: string;
            amount: string;
            status: string;
            created_at: string;
            user: {
                name: string;
                email: string;
            };
            plan: {
                name: string;
            };
        }>;
        links:Array<{url:string|null;label:string;active:boolean}>;
    };
    filters:{q?:string;status?:string};
}>();
const q=ref(props.filters?.q||''); const status=ref(props.filters?.status||'');
const search=()=>router.get('/admin/orders',{q:q.value||undefined,status:status.value||undefined},{preserveState:true,replace:true});

const formatRupiah = (val: string | number) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(Number(val));
};
</script>

<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">
            <div class="flex items-center justify-between pb-6 border-b border-stone-200">
                <div>
                    <Link href="/admin/dashboard" class="text-xs font-semibold text-rose-600 hover:text-rose-700 flex items-center gap-1 mb-1">
                        ← Kembali ke Dashboard Admin
                    </Link>
                    <h1 class="text-2xl font-serif font-bold text-stone-900">Riwayat Transaksi & Order</h1>
                </div>
            </div>

            <form @submit.prevent="search" class="flex flex-wrap gap-2 mt-6"><input v-model="q" class="rounded-xl border-stone-200 text-sm" placeholder="Cari order, nama, email"><select v-model="status" class="rounded-xl border-stone-200 text-sm"><option value="">Semua status</option><option v-for="s in ['pending','paid','failed','cancelled','expired']" :key="s" :value="s">{{s}}</option></select><button class="px-4 py-2 bg-stone-900 text-white rounded-xl text-sm">Filter</button><span class="self-center text-xs text-stone-500">Informasi saja; pembayaran tidak dapat diubah dari panel ini.</span></form>

            <div class="bg-white rounded-2xl border border-stone-200 overflow-hidden shadow-sm mt-8">
                <table class="w-full text-left text-xs text-stone-600">
                    <thead class="bg-stone-50 border-b border-stone-200 text-stone-500 font-semibold uppercase text-[10px]">
                        <tr>
                            <th class="p-4">Nomor Order</th>
                            <th class="p-4">Pengguna</th>
                            <th class="p-4">Paket</th>
                            <th class="p-4">Nominal</th>
                            <th class="p-4">Status</th>
                            <th class="p-4">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        <tr v-for="order in orders.data" :key="order.id" class="hover:bg-stone-50/50">
                            <td class="p-4 font-mono font-bold text-stone-900">{{ order.order_number }}</td>
                            <td class="p-4">
                                <div class="font-medium text-stone-900">{{ order.user?.name }}</div>
                                <div class="text-[11px] text-stone-400">{{ order.user?.email }}</div>
                            </td>
                            <td class="p-4">{{ order.plan?.name }}</td>
                            <td class="p-4 font-bold text-stone-900">{{ formatRupiah(order.amount) }}</td>
                            <td class="p-4">
                                <span :class="['px-2 py-0.5 rounded text-[10px] font-bold capitalize', order.status === 'paid' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800']">
                                    {{ order.status }}
                                </span>
                            </td>
                            <td class="p-4 text-stone-400">{{ new Date(order.created_at).toLocaleString() }}</td>
                        </tr>
                        <tr v-if="orders.data.length === 0">
                            <td colspan="6" class="p-8 text-center text-stone-400">
                                Belum ada transaksi yang tercatat.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <nav class="flex gap-2 mt-4"><Link v-for="link in orders.links" :key="link.label" :href="link.url || '/admin/orders'" preserve-scroll class="px-3 py-1 rounded border text-xs" :class="link.active?'bg-stone-900 text-white':'bg-white'" v-html="link.label"/></nav>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { useToast } from '@/composables/useToast';

const props = defineProps<{
    order: {
        id: number;
        order_number: string;
        amount: string;
        currency: string;
        status: string;
        plan: {
            name: string;
        };
        product_snapshot_json: any;
    };
    payment: {
        id: number;
        provider_transaction_id: string;
        status: string;
        metadata_json: any;
    } | null;
}>();

const toast = useToast();
const isPaying = ref(false);
const orderStatus = ref(props.order.status);

const formatRupiah = (val: string | number) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(Number(val));
};

const simulateSuccess = async () => {
    isPaying.value = true;
    try {
        const res = await fetch(`/api/sandbox/simulate-payment/${props.order.id}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as any)?.content || '',
                'Accept': 'application/json',
            },
        });

        const data = await res.json();
        if (data.success) {
            orderStatus.value = 'paid';
            toast.success('Pembayaran simulasi berhasil diverifikasi!');
        } else {
            toast.error('Simulasi gagal: ' + (data.message || 'Terjadi kesalahan'));
        }
    } catch (e) {
        toast.error('Terjadi kesalahan saat memproses pembayaran.');
    } finally {
        isPaying.value = false;
    }
};
</script>

<template>
    <AppLayout>
        <div class="max-w-xl mx-auto px-4 py-12 sm:py-16">
            <div class="bg-white rounded-3xl border border-stone-200 p-8 shadow-sm text-center">
                <!-- Paid Success State -->
                <div v-if="orderStatus === 'paid'" class="space-y-4 py-6">
                    <div class="w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-3xl mx-auto font-bold">
                        ✓
                    </div>
                    <h2 class="text-2xl font-serif font-bold text-stone-900">Pembayaran Berhasil!</h2>
                    <p class="text-xs text-stone-500">
                        Selamat! Paket <strong>{{ order.plan?.name }}</strong> telah aktif pada akun Anda selama 12 bulan.
                    </p>
                    <div class="pt-4">
                        <Link
                            href="/dashboard"
                            class="inline-block px-6 py-3 bg-stone-900 hover:bg-stone-800 text-white rounded-xl text-xs font-bold shadow transition-colors"
                        >
                            Kembali ke Dashboard
                        </Link>
                    </div>
                </div>

                <!-- Pending Payment State -->
                <div v-else class="space-y-6">
                    <div class="text-xs uppercase tracking-widest text-stone-400 font-semibold">Instruksi Pembayaran Sandbox</div>
                    <h2 class="text-2xl font-serif font-bold text-stone-900">
                        Total Tagihan: {{ formatRupiah(order.amount) }}
                    </h2>
                    <p class="text-xs text-stone-500">
                        Order ID: <span class="font-mono text-stone-700 font-bold">{{ order.order_number }}</span>
                    </p>

                    <!-- Mock QRIS Box -->
                    <div class="p-6 bg-stone-50 rounded-2xl border border-stone-200 max-w-xs mx-auto space-y-4">
                        <div class="font-bold text-xs text-stone-700">QRIS / Pembayaran Instan</div>
                        <div class="w-48 h-48 bg-white border-2 border-dashed border-stone-300 rounded-xl mx-auto flex flex-col items-center justify-center p-4">
                            <!-- Visual QR representation -->
                            <div class="grid grid-cols-5 gap-1.5 w-32 h-32 p-2 bg-stone-900 rounded-lg">
                                <div v-for="i in 25" :key="i" :class="[i % 2 === 0 ? 'bg-white' : 'bg-transparent', 'rounded-sm']"></div>
                            </div>
                            <span class="text-[9px] text-stone-400 mt-2">Pindai dengan aplikasi e-wallet / banking</span>
                        </div>
                        <div class="text-xs text-stone-500">
                            Virtual Account: <span class="font-mono font-bold text-stone-800">{{ payment?.metadata_json?.va_number || '880812345678' }}</span>
                        </div>
                    </div>

                    <!-- Sandbox Simulator Button -->
                    <div class="pt-2">
                        <button
                            @click="simulateSuccess"
                            :disabled="isPaying"
                            class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-md shadow-emerald-200 transition-all disabled:opacity-50"
                        >
                            {{ isPaying ? 'Memproses Simulasi...' : '⚡ Simulasikan Bayar Berhasil (Sandbox Mode)' }}
                        </button>
                        <p class="text-[10px] text-stone-400 mt-2">
                            Tekan tombol di atas untuk menyimulasikan notifikasi sukses pembayaran dari payment gateway secara otomatis.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

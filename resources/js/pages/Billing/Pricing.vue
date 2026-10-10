<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps<{
    plans: Array<{
        id: number;
        name: string;
        code: string;
        description: string;
        features_json: any;
        prices: Array<{
            id: number;
            amount: string;
            currency: string;
            billing_interval: string;
        }>;
    }>;
    currentSubscription: boolean;
}>();

const form = useForm({
    plan_id: 2, // default Gold
});

const formatRupiah = (val: string | number) => {
    const num = Number(val);
    if (num === 0) return 'Gratis';
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(num);
};

const selectPlan = (planId: number) => {
    form.plan_id = planId;
    form.post('/checkout');
};
</script>

<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <div class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-700 mb-3">
                    Paket Fleksibel & Terjangkau
                </div>
                <h1 class="text-3xl sm:text-4xl font-serif font-bold text-stone-900">
                    Pilih Paket Terbaik untuk Hari Bahagia Anda
                </h1>
                <p class="mt-3 text-sm text-stone-600">
                    Semua paket dirancang untuk memberikan kemudahan tanpa biaya tersembunyi.
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-8 items-stretch max-w-5xl mx-auto">
                <div
                    v-for="plan in plans"
                    :key="plan.id"
                    :class="[
                        'rounded-3xl p-8 border flex flex-col justify-between transition-all relative',
                        plan.code === 'gold' ? 'border-amber-400 bg-white shadow-xl ring-2 ring-amber-400/30' : 'border-stone-200 bg-white/70 shadow-sm'
                    ]"
                >
                    <div v-if="plan.code === 'gold'" class="absolute -top-3.5 left-1/2 -translate-x-1/2 bg-amber-500 text-white text-[10px] font-bold py-1 px-3 rounded-full uppercase tracking-wider shadow">
                        Paling Populer
                    </div>

                    <div>
                        <h3 class="font-serif font-bold text-xl text-stone-900">{{ plan.name }}</h3>
                        <p class="text-xs text-stone-500 mt-2 min-h-[32px]">{{ plan.description }}</p>

                        <div class="my-6">
                            <span class="font-serif font-bold text-3xl sm:text-4xl text-stone-900">
                                {{ formatRupiah(plan.prices[0]?.amount || 0) }}
                            </span>
                            <span v-if="Number(plan.prices[0]?.amount) > 0" class="text-xs text-stone-400 ml-1">/ 12 Bulan</span>
                        </div>

                        <div class="space-y-3 pt-4 border-t border-stone-100 text-xs text-stone-600">
                            <div class="flex items-center gap-2">
                                <span class="text-emerald-500 font-bold">✓</span>
                                <span>Maksimal Tamu: <strong>{{ plan.features_json?.max_guests || '50' }}</strong></span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span :class="plan.features_json?.premium_templates ? 'text-emerald-500 font-bold' : 'text-stone-300'">
                                    {{ plan.features_json?.premium_templates ? '✓' : '✕' }}
                                </span>
                                <span>Akses Template Premium</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span :class="plan.features_json?.remove_watermark ? 'text-emerald-500 font-bold' : 'text-stone-300'">
                                    {{ plan.features_json?.remove_watermark ? '✓' : '✕' }}
                                </span>
                                <span>Tanpa Watermark</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span :class="plan.features_json?.background_music ? 'text-emerald-500 font-bold' : 'text-stone-300'">
                                    {{ plan.features_json?.background_music ? '✓' : '✕' }}
                                </span>
                                <span>Musik Latar Otomatis</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-emerald-500 font-bold">✓</span>
                                <span>Galeri Foto: <strong>{{ plan.features_json?.photo_gallery_limit || '3' }} foto</strong></span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 pt-4">
                        <button
                            v-if="plan.code === 'free'"
                            disabled
                            class="w-full py-3 rounded-xl border border-stone-300 text-stone-400 text-xs font-semibold cursor-default"
                        >
                            Paket Dasar
                        </button>
                        <button
                            v-else
                            @click="selectPlan(plan.id)"
                            class="w-full py-3 rounded-xl text-xs font-bold text-white bg-stone-900 hover:bg-stone-800 shadow-md transition-all hover:scale-[1.02]"
                        >
                            Pilih {{ plan.name }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

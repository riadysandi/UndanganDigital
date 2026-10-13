<script setup lang="ts">
import { ref } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps<{
    templates: Array<{
        id: number;
        name: string;
        slug: string;
        category: string;
        thumbnail_path: string;
        is_premium: boolean;
    }>;
    isPremium: boolean;
}>();

const selectedTemplateId = ref<number>(props.templates[0]?.id || 1);

const form = useForm({
    title: '',
    slug: '',
    template_id: props.templates[0]?.id || 1,
});

const selectTemplate = (tpl: any) => {
    selectedTemplateId.value = tpl.id;
    form.template_id = tpl.id;
};

const submit = () => {
    form.post('/invitations');
};
</script>

<template>
    <AppLayout>
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">
            <div class="mb-8">
                <Link href="/dashboard" class="text-xs font-semibold text-rose-600 hover:text-rose-700 flex items-center gap-1 mb-2">
                    ← Kembali ke Dashboard
                </Link>
                <h1 class="text-2xl sm:text-3xl font-serif font-bold text-stone-900">Buat Undangan Baru</h1>
                <p class="text-sm text-stone-500 mt-1">Pilih template desain awal dan tentukan judul undangan Anda.</p>
            </div>

            <form @submit.prevent="submit" class="space-y-8">
                <!-- Section 1: Template Selection -->
                <div class="bg-white p-6 rounded-2xl border border-stone-200">
                    <h2 class="text-sm font-bold text-stone-800 uppercase tracking-wider mb-4">1. Pilih Template Desain</h2>
                    
                    <div class="grid sm:grid-cols-2 md:grid-cols-4 gap-4">
                        <div
                            v-for="tpl in templates"
                            :key="tpl.id"
                            @click="selectTemplate(tpl)"
                            :class="[
                                'relative rounded-xl border-2 overflow-hidden cursor-pointer transition-all flex flex-col',
                                selectedTemplateId === tpl.id ? 'border-rose-500 ring-2 ring-rose-200 shadow-sm' : 'border-stone-200 hover:border-stone-300'
                            ]"
                        >
                            <div class="aspect-[3/4] bg-stone-100 relative">
                                <img :src="tpl.thumbnail_path" :alt="tpl.name" class="w-full h-full object-cover" />
                                <div class="absolute top-2 right-2">
                                    <span v-if="tpl.is_premium" class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-amber-500 text-white">
                                        PREMIUM
                                    </span>
                                    <span v-else class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-emerald-600 text-white">
                                        GRATIS
                                    </span>
                                </div>
                                <div v-if="selectedTemplateId === tpl.id" class="absolute inset-0 bg-rose-500/10 flex items-center justify-center">
                                    <div class="w-8 h-8 rounded-full bg-rose-600 text-white flex items-center justify-center text-sm font-bold shadow">
                                        ✓
                                    </div>
                                </div>
                            </div>
                            <div class="p-3 bg-white">
                                <div class="flex items-center justify-between">
                                    <div class="text-[10px] text-stone-400 capitalize">{{ tpl.category }}</div>
                                    <a
                                        :href="'/demo/' + tpl.slug"
                                        target="_blank"
                                        @click.stop
                                        class="text-[10px] font-semibold text-rose-600 hover:text-rose-700 hover:underline flex items-center gap-0.5"
                                    >
                                        Demo ↗
                                    </a>
                                </div>
                                <div class="font-bold text-stone-800 text-xs truncate mt-0.5">{{ tpl.name }}</div>
                            </div>
                        </div>
                    </div>

                    <div v-if="form.errors.template_id" class="text-xs text-rose-600 mt-3 p-3 bg-rose-50 rounded-lg">
                        {{ form.errors.template_id }}
                    </div>
                </div>

                <!-- Section 2: Invitation Information -->
                <div class="bg-white p-6 rounded-2xl border border-stone-200 space-y-4">
                    <h2 class="text-sm font-bold text-stone-800 uppercase tracking-wider mb-2">2. Informasi Undangan</h2>

                    <div>
                        <label class="block text-xs font-semibold text-stone-700">Judul Undangan / Nama Pasangan *</label>
                        <input
                            v-model="form.title"
                            type="text"
                            required
                            class="mt-1 block w-full px-3.5 py-2.5 bg-stone-50 border border-stone-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-rose-500 focus:bg-white"
                            placeholder="Contoh: Pernikahan Dimas & Anisa"
                        />
                        <div v-if="form.errors.title" class="text-xs text-rose-600 mt-1">
                            {{ form.errors.title }}
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-stone-700">Kustom Slug URL (Opsional)</label>
                        <div class="mt-1 flex rounded-xl shadow-sm">
                            <span class="inline-flex items-center px-3 rounded-l-xl border border-r-0 border-stone-300 bg-stone-100 text-stone-500 text-xs">
                                /i/
                            </span>
                            <input
                                v-model="form.slug"
                                type="text"
                                class="flex-1 min-w-0 block w-full px-3.5 py-2.5 bg-stone-50 border border-stone-300 rounded-r-xl text-sm focus:outline-none focus:ring-2 focus:ring-rose-500 focus:bg-white"
                                placeholder="dimas-anisa"
                            />
                        </div>
                        <p class="text-[11px] text-stone-400 mt-1">Jika dikosongkan, slug unik akan dibuatkan secara otomatis.</p>
                        <div v-if="form.errors.slug" class="text-xs text-rose-600 mt-1">
                            {{ form.errors.slug }}
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end gap-3">
                    <Link
                        href="/dashboard"
                        class="px-5 py-2.5 rounded-xl border border-stone-300 text-sm font-medium text-stone-700 hover:bg-stone-50"
                    >
                        Batal
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-6 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-sm font-medium shadow-sm transition-all disabled:opacity-50"
                    >
                        {{ form.processing ? 'Membuat Undangan...' : 'Lanjut ke Editor Visual →' }}
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { router, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps<{
    templates: {
    data: Array<{
        id: number;
        name: string;
        slug: string;
        category: string;
        thumbnail_path: string;
        is_premium: boolean;
        is_active: boolean;
        invitations_count: number;
    }>;
    links:Array<{url:string|null;label:string;active:boolean}>;
    };
    filters:{q?:string;status?:string};
}>();
const q=ref(props.filters?.q||''); const status=ref(props.filters?.status||'');
const search=()=>router.get('/admin/templates',{q:q.value||undefined,status:status.value||undefined},{preserveState:true,replace:true});

const toggle = (templateId: number, field: 'is_premium' | 'is_active') => {
    router.post(`/admin/templates/${templateId}/toggle`, { field });
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
                    <h1 class="text-2xl font-serif font-bold text-stone-900">Kelola Template Katalog</h1>
                </div>
            </div>

            <form @submit.prevent="search" class="flex flex-wrap gap-2 mt-6"><input v-model="q" class="rounded-xl border-stone-200 text-sm" placeholder="Cari nama atau slug template"><select v-model="status" class="rounded-xl border-stone-200 text-sm"><option value="">Semua status</option><option value="active">Aktif</option><option value="inactive">Nonaktif</option></select><button class="px-4 py-2 bg-stone-900 text-white rounded-xl text-sm">Filter</button></form>

            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6 mt-8">
                <div
                    v-for="tpl in templates.data"
                    :key="tpl.id"
                    class="bg-white rounded-2xl border border-stone-200 overflow-hidden shadow-sm flex flex-col justify-between"
                >
                    <div class="aspect-[3/4] bg-stone-100 relative">
                        <img :src="tpl.thumbnail_path" :alt="tpl.name" class="w-full h-full object-cover" />
                        <div class="absolute top-2 right-2">
                            <span v-if="tpl.is_premium" class="px-2 py-0.5 rounded text-[9px] font-bold bg-amber-500 text-white">
                                PREMIUM
                            </span>
                            <span v-else class="px-2 py-0.5 rounded text-[9px] font-bold bg-emerald-600 text-white">
                                GRATIS
                            </span>
                        </div>
                    </div>

                    <div class="p-4 space-y-3">
                        <div>
                            <div class="text-[10px] text-stone-400 capitalize">{{ tpl.category }}</div>
                            <h3 class="font-bold text-sm text-stone-800">{{ tpl.name }}</h3>
                            <div class="text-xs text-stone-500 mt-1">Digunakan: {{ tpl.invitations_count }} undangan</div>
                        </div>

                        <div class="pt-3 border-t border-stone-100 flex items-center justify-between text-xs">
                            <button
                                @click="toggle(tpl.id, 'is_premium')"
                                class="px-2.5 py-1 rounded border text-[11px] font-medium transition-colors hover:bg-stone-50"
                            >
                                {{ tpl.is_premium ? 'Ubah ke Gratis' : 'Ubah ke Premium' }}
                            </button>
                            <button
                                @click="toggle(tpl.id, 'is_active')"
                                :class="['px-2.5 py-1 rounded text-[11px] font-medium transition-colors', tpl.is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700']"
                            >
                                {{ tpl.is_active ? 'Aktif' : 'Nonaktif' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <nav class="flex gap-2 mt-4"><Link v-for="link in templates.links" :key="link.label" :href="link.url || '/admin/templates'" preserve-scroll class="px-3 py-1 rounded border text-xs" :class="link.active?'bg-stone-900 text-white':'bg-white'" v-html="link.label"/></nav>
        </div>
    </AppLayout>
</template>

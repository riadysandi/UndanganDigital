<script setup lang="ts">
import { router, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { useConfirm } from '@/composables/useConfirm';

type TemplateRow = { id:number; name:string; slug:string; category:string; thumbnail_path:string|null; default_design_json:Record<string, unknown>; schema_version:number; is_premium:boolean; is_active:boolean; invitations_count:number };
const props = defineProps<{ templates:{data:TemplateRow[];links:Array<{url:string|null;label:string;active:boolean}>};filters:{q?:string;status?:string} }>();
const q = ref(props.filters?.q || ''); const status = ref(props.filters?.status || '');
const selected = ref<TemplateRow | null>(null); const createOpen = ref(false); const { confirmAction } = useConfirm();
const emptyDesign = { schemaVersion:1, canvas:{width:1080,height:1920,background:'#FDFBF7',primaryColor:'#5D6B50',fontFamily:'Playfair Display'}, theme:'modern', musicUrl:'', sections:[] };
const form = useForm({ name:'', slug:'', category:'modern', thumbnail_path:'', default_design_json:JSON.stringify(emptyDesign,null,2), schema_version:1, is_premium:false, is_active:true });
const search = () => router.get('/admin/templates',{q:q.value||undefined,status:status.value||undefined},{preserveState:true,replace:true});
const openCreate = () => { selected.value=null;form.reset();form.default_design_json=JSON.stringify(emptyDesign,null,2);form.clearErrors();createOpen.value=true; };
const openEdit = (tpl:TemplateRow) => { selected.value=tpl;createOpen.value=false;form.clearErrors();form.name=tpl.name;form.slug=tpl.slug;form.category=tpl.category;form.thumbnail_path=tpl.thumbnail_path||'';form.default_design_json=JSON.stringify(tpl.default_design_json,null,2);form.schema_version=tpl.schema_version;form.is_premium=tpl.is_premium;form.is_active=tpl.is_active; };
const closeForm = () => { selected.value=null;createOpen.value=false;form.reset();form.clearErrors(); };
const save = () => {
    let design: unknown;
    try { design=JSON.parse(form.default_design_json); } catch { form.setError('default_design_json','JSON tidak valid. Periksa tanda kurung dan koma.');return; }
    form.default_design_json=JSON.stringify(design);
    const options={preserveScroll:true,onSuccess:closeForm};
    if(selected.value) form.put(`/admin/templates/${selected.value.id}`,options); else form.post('/admin/templates',options);
};
const toggle = (id:number,field:'is_premium'|'is_active') => router.post(`/admin/templates/${id}/toggle`,{field},{preserveScroll:true});
const remove = async (tpl:TemplateRow) => { if(await confirmAction({title:'Hapus template?',message:`Template “${tpl.name}” akan dihapus. Template yang sudah dipakai harus dinonaktifkan.`,confirmText:'Hapus template',type:'danger'})) router.delete(`/admin/templates/${tpl.id}`,{preserveScroll:true}); };
</script>

<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">
            <div class="flex flex-wrap items-end justify-between gap-4 pb-6 border-b border-stone-200"><div><Link href="/admin/dashboard" class="text-xs font-semibold text-rose-600">← Kembali ke Dashboard Admin</Link><h1 class="mt-1 text-2xl font-serif font-bold text-stone-900">Kelola Template Katalog</h1></div><button @click="openCreate" class="px-4 py-2 rounded-xl bg-stone-900 text-white text-sm">+ Tambah template</button></div>
            <form @submit.prevent="search" class="flex flex-wrap gap-2 mt-6"><input v-model="q" class="rounded-xl border-stone-200 text-sm" placeholder="Cari nama atau slug template"><select v-model="status" class="rounded-xl border-stone-200 text-sm"><option value="">Semua status</option><option value="active">Aktif</option><option value="inactive">Nonaktif</option></select><button class="px-4 py-2 bg-stone-900 text-white rounded-xl text-sm">Filter</button></form>
            <p class="mt-3 text-xs text-stone-500">Template yang sudah dipakai undangan tidak dapat dihapus; nonaktifkan untuk menyembunyikannya dari katalog.</p>
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6 mt-8">
                <div v-for="tpl in templates.data" :key="tpl.id" class="bg-white rounded-2xl border border-stone-200 overflow-hidden shadow-sm flex flex-col">
                    <div class="aspect-[3/4] bg-stone-100 relative"><img v-if="tpl.thumbnail_path" :src="tpl.thumbnail_path" :alt="tpl.name" class="w-full h-full object-cover"><div v-else class="grid h-full place-items-center text-stone-400">Tidak ada thumbnail</div><span class="absolute top-2 right-2 px-2 py-0.5 rounded text-[9px] font-bold bg-amber-500 text-white">{{tpl.is_premium?'PREMIUM':'GRATIS'}}</span></div>
                    <div class="p-4 space-y-3 flex-1"><div><div class="text-[10px] text-stone-400 capitalize">{{tpl.category}} · {{tpl.is_active?'Aktif':'Nonaktif'}}</div><h3 class="font-bold text-sm text-stone-800">{{tpl.name}}</h3><div class="text-xs text-stone-500 mt-1">Digunakan: {{tpl.invitations_count}} undangan</div></div><div class="pt-3 border-t flex flex-wrap gap-2 text-xs"><button @click="openEdit(tpl)" class="px-2.5 py-1 rounded border">Edit</button><button @click="toggle(tpl.id,'is_premium')" class="px-2.5 py-1 rounded border">{{tpl.is_premium?'Ubah ke Gratis':'Ubah ke Premium'}}</button><button @click="toggle(tpl.id,'is_active')" class="px-2.5 py-1 rounded" :class="tpl.is_active?'bg-emerald-50 text-emerald-700':'bg-rose-50 text-rose-700'">{{tpl.is_active?'Nonaktifkan':'Aktifkan'}}</button><button v-if="tpl.invitations_count===0" @click="remove(tpl)" class="px-2.5 py-1 text-red-700">Hapus</button></div></div>
                </div>
                <p v-if="!templates.data.length" class="col-span-full p-12 text-center text-stone-400">Belum ada template.</p>
            </div>
            <nav class="flex gap-2 mt-4"><Link v-for="link in templates.links" :key="link.label" :href="link.url || '/admin/templates'" preserve-scroll class="px-3 py-1 rounded border text-xs" :class="link.active?'bg-stone-900 text-white':'bg-white'" v-html="link.label"/></nav>
        </div>
        <div v-if="selected || createOpen" class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" @click.self="closeForm"><form @submit.prevent="save" class="w-full max-w-3xl max-h-[90vh] overflow-y-auto rounded-2xl bg-white p-6 shadow-xl space-y-4">
            <div class="flex justify-between"><h2 class="text-lg font-serif font-bold">{{selected?'Edit template':'Tambah template'}}</h2><button type="button" @click="closeForm">✕</button></div>
            <div class="grid sm:grid-cols-2 gap-3"><label class="text-sm">Nama<input v-model="form.name" required class="mt-1 w-full rounded-xl border-stone-200"><span v-if="form.errors.name" class="text-xs text-red-600">{{form.errors.name}}</span></label><label class="text-sm">Slug<input v-model="form.slug" required pattern="[A-Za-z0-9_-]+" class="mt-1 w-full rounded-xl border-stone-200"><span v-if="form.errors.slug" class="text-xs text-red-600">{{form.errors.slug}}</span></label><label class="text-sm">Kategori<input v-model="form.category" required class="mt-1 w-full rounded-xl border-stone-200"></label><label class="text-sm">Thumbnail URL<input v-model="form.thumbnail_path" type="url" class="mt-1 w-full rounded-xl border-stone-200"></label><label class="text-sm">Schema version<input v-model.number="form.schema_version" type="number" min="1" required class="mt-1 w-full rounded-xl border-stone-200"></label><div class="flex items-end gap-5 pb-2"><label class="text-sm flex items-center gap-2"><input v-model="form.is_premium" type="checkbox"> Premium</label><label class="text-sm flex items-center gap-2"><input v-model="form.is_active" type="checkbox"> Aktif</label></div></div>
            <label class="block text-sm">Default design JSON<textarea v-model="form.default_design_json" rows="14" required spellcheck="false" class="mt-1 w-full rounded-xl border-stone-200 font-mono text-xs"></textarea><span v-if="form.errors.default_design_json" class="text-xs text-red-600">{{form.errors.default_design_json}}</span></label>
            <div class="flex justify-end gap-2"><button type="button" @click="closeForm" class="px-4 py-2 rounded-xl border">Batal</button><button :disabled="form.processing" class="px-4 py-2 rounded-xl bg-stone-900 text-white">Simpan</button></div>
        </form></div>
    </AppLayout>
</template>

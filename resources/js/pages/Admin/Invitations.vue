<script setup lang="ts">
import { Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { useConfirm } from '@/composables/useConfirm';

const props = defineProps<{
    invitations: {
        data: Array<{
            id: number;
            user_id: number;
            template_id: number | null;
            title: string;
            slug: string;
            status: string;
            event_date: string | null;
            created_at: string;
            guests_count: number;
            rsvps_count: number;
            guestbook_entries_count: number;
            media_count: number;
            versions_count: number;
            events_count: number;
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
        links:Array<{url:string|null;label:string;active:boolean}>;
    };
    filters: {q?:string;status?:string};
    owners:Array<{id:number;name:string;email:string}>;
    templates:Array<{id:number;name:string;is_active:boolean}>;
}>();
const q=ref(props.filters?.q||''); const status=ref(props.filters?.status||'');
const selected=ref<typeof props.invitations.data[number]|null>(null); const createOpen=ref(false);
const {confirmAction}=useConfirm();
const search=()=>router.get('/admin/invitations',{q:q.value||undefined,status:status.value||undefined},{preserveState:true,replace:true});
const unpublish=async (invitation:{id:number;title:string})=>{if(await confirmAction({title:'Unpublish undangan?',message:`Undangan “${invitation.title}” akan menjadi draft dan tidak tampil ke publik.`,confirmText:'Unpublish',type:'warning'})) router.post(`/admin/invitations/${invitation.id}/unpublish`,{}, {preserveScroll:true});};
const form=useForm({user_id:props.owners[0]?.id||0,template_id:null as number|null,title:'',slug:''});
const openCreate=()=>{selected.value=null;form.reset();form.user_id=props.owners[0]?.id||0;form.template_id=null;form.clearErrors();createOpen.value=true;};
const openEdit=(inv:typeof props.invitations.data[number])=>{selected.value=inv;createOpen.value=false;form.clearErrors();form.user_id=inv.user_id;form.template_id=inv.template_id;form.title=inv.title;form.slug=inv.slug;};
const closeForm=()=>{selected.value=null;createOpen.value=false;form.reset();form.clearErrors();};
const save=()=>{const options={preserveScroll:true,onSuccess:closeForm};if(selected.value)form.put(`/admin/invitations/${selected.value.id}`,options);else form.post('/admin/invitations',options);};
const remove=async(inv:typeof props.invitations.data[number])=>{if(await confirmAction({title:'Hapus undangan draft?',message:`Undangan “${inv.title}” dapat dihapus hanya jika belum memiliki riwayat, acara, atau data tamu.`,confirmText:'Hapus draft',type:'danger'}))router.delete(`/admin/invitations/${inv.id}`,{preserveScroll:true});};
const canDelete=(inv:typeof props.invitations.data[number])=>inv.status!=='published'&&inv.versions_count===0&&inv.guests_count===0&&inv.rsvps_count===0&&inv.guestbook_entries_count===0&&inv.media_count===0&&inv.events_count===0;
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
                </div><button @click="openCreate" class="px-4 py-2 rounded-xl bg-stone-900 text-white text-sm" :disabled="!owners.length">+ Buat undangan draft</button>
            </div>

            <form @submit.prevent="search" class="flex flex-wrap gap-2 mt-6"><input v-model="q" class="rounded-xl border-stone-200 text-sm" placeholder="Cari judul, slug, pemilik"><select v-model="status" class="rounded-xl border-stone-200 text-sm"><option value="">Semua status</option><option value="published">Published</option><option value="draft">Draft</option></select><button class="px-4 py-2 bg-stone-900 text-white rounded-xl text-sm">Filter</button></form>

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
                                    <button @click="openEdit(inv)" class="ml-2 inline-flex px-3 py-1.5 bg-stone-100 text-stone-700 rounded-lg text-xs">Edit</button>
                                    <button v-if="inv.status === 'published'" @click="unpublish(inv)" class="ml-2 inline-flex px-3 py-1.5 bg-amber-50 text-amber-800 rounded-lg text-xs">Unpublish</button>
                                    <button v-if="canDelete(inv)" @click="remove(inv)" class="ml-2 inline-flex px-3 py-1.5 bg-red-50 text-red-700 rounded-lg text-xs">Hapus</button><span v-else-if="inv.status==='draft'" class="ml-2 text-[10px] text-stone-400">Riwayat dilindungi</span>
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
            <nav class="flex gap-2 mt-4"><Link v-for="link in invitations.links" :key="link.label" :href="link.url || '/admin/invitations'" preserve-scroll class="px-3 py-1 rounded border text-xs" :class="link.active?'bg-stone-900 text-white':'bg-white'" v-html="link.label"/></nav>
        </div>
        <div v-if="selected||createOpen" class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" @click.self="closeForm"><form @submit.prevent="save" class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl space-y-4">
            <div class="flex justify-between"><h2 class="text-lg font-serif font-bold">{{selected?'Edit data undangan':'Buat draft undangan'}}</h2><button type="button" @click="closeForm">✕</button></div>
            <label class="block text-sm">Pemilik<select v-model="form.user_id" required class="mt-1 w-full rounded-xl border-stone-200"><option v-for="owner in owners" :key="owner.id" :value="owner.id">{{owner.name}} ({{owner.email}})</option></select><span v-if="form.errors.user_id" class="text-xs text-red-600">{{form.errors.user_id}}</span></label>
            <label class="block text-sm">Template<select v-model="form.template_id" class="mt-1 w-full rounded-xl border-stone-200"><option :value="null">Tanpa template</option><option v-for="template in templates" :key="template.id" :value="template.id">{{template.name}}{{template.is_active?'':' (nonaktif)'}}</option></select></label>
            <label class="block text-sm">Judul<input v-model="form.title" required class="mt-1 w-full rounded-xl border-stone-200"><span v-if="form.errors.title" class="text-xs text-red-600">{{form.errors.title}}</span></label>
            <label class="block text-sm">Slug publik<input v-model="form.slug" required pattern="[A-Za-z0-9_-]+" class="mt-1 w-full rounded-xl border-stone-200"><span v-if="form.errors.slug" class="text-xs text-red-600">{{form.errors.slug}}</span></label>
            <p class="text-xs text-stone-500">Undangan baru dibuat sebagai draft. Konten selanjutnya diedit melalui editor undangan.</p>
            <div class="flex justify-end gap-2"><button type="button" @click="closeForm" class="px-4 py-2 rounded-xl border">Batal</button><button :disabled="form.processing" class="px-4 py-2 rounded-xl bg-stone-900 text-white">Simpan</button></div>
        </form></div>
    </AppLayout>
</template>

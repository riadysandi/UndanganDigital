<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { useConfirm } from '@/composables/useConfirm';

const props = defineProps<{ users: { data: Array<{id:number;name:string;email:string;role:string;is_active:boolean;invitations_count:number;created_at:string}>; links:Array<{url:string|null;label:string;active:boolean}> }; filters:{q?:string;status?:string} }>();
const q = ref(props.filters?.q || ''); const status = ref(props.filters?.status || '');
const { confirmAction } = useConfirm();
const search = () => router.get('/admin/users', {q:q.value||undefined,status:status.value||undefined}, {preserveState:true,replace:true});
const toggle = async (user: typeof props.users.data[number]) => {
    const ok = await confirmAction({title:user.is_active?'Nonaktifkan pengguna?':'Aktifkan pengguna?',message:`${user.name} ${user.is_active?'tidak dapat masuk':'dapat kembali masuk'} setelah perubahan ini.`,confirmText:user.is_active?'Nonaktifkan':'Aktifkan',type:user.is_active?'warning':'info'});
    if(ok) router.patch(`/admin/users/${user.id}/toggle`, {}, {preserveScroll:true});
};
</script>
<template><AppLayout><div class="max-w-7xl mx-auto px-4 py-8">
<div class="pb-6 border-b"><Link href="/admin/dashboard" class="text-xs text-rose-600">← Dashboard Admin</Link><h1 class="text-2xl font-serif font-bold mt-1">Daftar Pengguna Platform</h1></div>
<form @submit.prevent="search" class="flex flex-wrap gap-2 mt-6"><input v-model="q" class="rounded-xl border-stone-200 text-sm" placeholder="Cari nama atau email"><select v-model="status" class="rounded-xl border-stone-200 text-sm"><option value="">Semua status</option><option value="active">Aktif</option><option value="inactive">Nonaktif</option></select><button class="px-4 py-2 bg-stone-900 text-white rounded-xl text-sm">Filter</button></form>
<div class="bg-white rounded-2xl border overflow-x-auto mt-5"><table class="w-full text-left text-xs"><thead class="bg-stone-50 uppercase text-[10px]"><tr><th class="p-4">Nama</th><th class="p-4">Email</th><th class="p-4">Role / Status</th><th class="p-4">Undangan</th><th class="p-4">Terdaftar</th><th class="p-4">Aksi</th></tr></thead><tbody class="divide-y"><tr v-for="user in users.data" :key="user.id"><td class="p-4 font-bold">{{user.name}}</td><td class="p-4">{{user.email}}</td><td class="p-4 capitalize">{{user.role}} · {{user.is_active?'Aktif':'Nonaktif'}}</td><td class="p-4">{{user.invitations_count}}</td><td class="p-4">{{new Date(user.created_at).toLocaleDateString('id-ID')}}</td><td class="p-4"><button v-if="user.role!=='admin'" @click="toggle(user)" class="font-semibold text-rose-700">{{user.is_active?'Nonaktifkan':'Aktifkan'}}</button></td></tr><tr v-if="!users.data.length"><td colspan="6" class="p-8 text-center">Tidak ada pengguna.</td></tr></tbody></table></div>
<nav class="flex gap-2 mt-4"><Link v-for="link in users.links" :key="link.label" :href="link.url || '/admin/users'" preserve-scroll class="px-3 py-1 rounded border text-xs" :class="link.active?'bg-stone-900 text-white':'bg-white'" v-html="link.label"/></nav>
</div></AppLayout></template>

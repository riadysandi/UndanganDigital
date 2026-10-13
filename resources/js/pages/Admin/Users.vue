<script setup lang="ts">
import { Link, router, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { useConfirm } from '@/composables/useConfirm';

type UserRow = { id: number; name: string; email: string; phone_number: string | null; role: string; is_active: boolean; invitations_count: number; created_at: string };
const props = defineProps<{ users: { data: UserRow[]; links: Array<{url:string|null;label:string;active:boolean}> }; filters: {q?:string;status?:string} }>();
const q = ref(props.filters?.q || '');
const status = ref(props.filters?.status || '');
const currentUserId = (usePage().props as any).auth?.user?.id;
const selected = ref<UserRow | null>(null);
const createOpen = ref(false);
const form = useForm({ name: '', email: '', phone_number: '', password: '', role: 'customer' });
const { confirmAction } = useConfirm();

const search = () => router.get('/admin/users', { q:q.value || undefined, status:status.value || undefined }, { preserveState:true, replace:true });
const openCreate = () => { selected.value = null; form.reset(); form.clearErrors(); createOpen.value = true; };
const openEdit = (user: UserRow) => {
    selected.value = user; createOpen.value = false; form.clearErrors();
    form.name = user.name; form.email = user.email; form.phone_number = user.phone_number || ''; form.password = ''; form.role = user.role;
};
const closeForm = () => { selected.value = null; createOpen.value = false; form.reset(); form.clearErrors(); };
const save = () => {
    if (form.role === 'admin' && selected.value?.role !== 'admin') {
        confirmAction({ title:'Berikan akses superadmin?', message:`${form.email || 'Akun ini'} dapat membuka semua menu pengelolaan platform.`, confirmText:'Berikan akses', type:'warning' }).then(ok => { if (ok) submit(); });
        return;
    }
    submit();
};
const submit = () => {
    const options = { preserveScroll:true, onSuccess:closeForm };
    if (selected.value) form.put(`/admin/users/${selected.value.id}`, options);
    else form.post('/admin/users', options);
};
const toggle = async (user: UserRow) => {
    const ok = await confirmAction({ title:user.is_active?'Nonaktifkan pengguna?':'Aktifkan pengguna?', message:`${user.name} ${user.is_active?'tidak dapat masuk':'dapat kembali masuk'} setelah perubahan ini.`, confirmText:user.is_active?'Nonaktifkan':'Aktifkan', type:'warning' });
    if (ok) router.patch(`/admin/users/${user.id}/toggle`, {}, { preserveScroll:true });
};
const remove = async (user: UserRow) => {
    const ok = await confirmAction({ title:'Hapus akun pengguna?', message:`Akun ${user.name} hanya dapat dihapus jika belum memiliki undangan atau transaksi.`, confirmText:'Hapus akun', type:'danger' });
    if (ok) router.delete(`/admin/users/${user.id}`, { preserveScroll:true });
};
</script>

<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto px-4 py-8">
            <div class="flex flex-wrap items-end justify-between gap-4 pb-6 border-b">
                <div><Link href="/admin/dashboard" class="text-xs text-rose-600">← Dashboard Admin</Link><h1 class="text-2xl font-serif font-bold mt-1">Daftar Pengguna Platform</h1><p class="mt-1 text-xs text-stone-500">Kelola akun customer. Akun administrator dilindungi.</p></div>
                <button @click="openCreate" class="px-4 py-2 rounded-xl bg-stone-900 text-white text-sm">+ Tambah pengguna</button>
            </div>
            <form @submit.prevent="search" class="flex flex-wrap gap-2 mt-6"><input v-model="q" class="rounded-xl border-stone-200 text-sm" placeholder="Cari nama atau email"><select v-model="status" class="rounded-xl border-stone-200 text-sm"><option value="">Semua status</option><option value="active">Aktif</option><option value="inactive">Nonaktif</option></select><button class="px-4 py-2 bg-stone-900 text-white rounded-xl text-sm">Filter</button></form>
            <div class="bg-white rounded-2xl border overflow-x-auto mt-5"><table class="w-full text-left text-xs"><thead class="bg-stone-50 uppercase text-[10px]"><tr><th class="p-4">Nama</th><th class="p-4">Email</th><th class="p-4">Role / Status</th><th class="p-4">Undangan</th><th class="p-4">Terdaftar</th><th class="p-4">Aksi</th></tr></thead><tbody class="divide-y"><tr v-for="user in users.data" :key="user.id"><td class="p-4 font-bold">{{user.name}}</td><td class="p-4">{{user.email}}</td><td class="p-4 capitalize">{{user.role}} · {{user.is_active?'Aktif':'Nonaktif'}}</td><td class="p-4">{{user.invitations_count}}</td><td class="p-4">{{new Date(user.created_at).toLocaleDateString('id-ID')}}</td><td class="p-4"><template v-if="user.id!==currentUserId"><button @click="openEdit(user)" class="font-semibold text-stone-700 mr-3">Edit</button><button @click="toggle(user)" class="font-semibold text-rose-700 mr-3">{{user.is_active?'Nonaktifkan':'Aktifkan'}}</button><button @click="remove(user)" class="font-semibold text-red-700">Hapus</button></template><span v-else class="text-stone-400">Akun yang sedang digunakan</span></td></tr><tr v-if="!users.data.length"><td colspan="6" class="p-8 text-center">Tidak ada pengguna.</td></tr></tbody></table></div>
            <nav class="flex gap-2 mt-4"><Link v-for="link in users.links" :key="link.label" :href="link.url || '/admin/users'" preserve-scroll class="px-3 py-1 rounded border text-xs" :class="link.active?'bg-stone-900 text-white':'bg-white'" v-html="link.label"/></nav>
        </div>
        <div v-if="selected !== null || createOpen" class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" @click.self="closeForm">
            <form @submit.prevent="save" class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl space-y-4">
                <div class="flex justify-between"><h2 class="text-lg font-serif font-bold">{{ selected ? 'Edit pengguna' : 'Tambah pengguna' }}</h2><button type="button" @click="closeForm">✕</button></div>
                <label class="block text-sm">Nama<input v-model="form.name" class="mt-1 w-full rounded-xl border-stone-200" required><span v-if="form.errors.name" class="text-xs text-red-600">{{form.errors.name}}</span></label>
                <label class="block text-sm">Email<input v-model="form.email" type="email" class="mt-1 w-full rounded-xl border-stone-200" required><span v-if="form.errors.email" class="text-xs text-red-600">{{form.errors.email}}</span></label>
                <label class="block text-sm">Nomor telepon<input v-model="form.phone_number" class="mt-1 w-full rounded-xl border-stone-200"></label>
                <label class="block text-sm">Peran<select v-model="form.role" class="mt-1 w-full rounded-xl border-stone-200"><option value="customer">Customer</option><option value="admin">Superadmin</option></select><span v-if="form.errors.role" class="text-xs text-red-600">{{form.errors.role}}</span></label>
                <label class="block text-sm">{{selected ? 'Kata sandi baru (kosongkan jika tidak diubah)' : 'Kata sandi (minimal 12 karakter)'}}<input v-model="form.password" type="password" :required="!selected" minlength="12" class="mt-1 w-full rounded-xl border-stone-200"><span v-if="form.errors.password" class="text-xs text-red-600">{{form.errors.password}}</span></label>
                <p v-if="form.errors.delete" class="text-sm text-red-600">{{form.errors.delete}}</p>
                <div class="flex justify-end gap-2"><button type="button" @click="closeForm" class="px-4 py-2 rounded-xl border">Batal</button><button :disabled="form.processing" class="px-4 py-2 rounded-xl bg-stone-900 text-white">Simpan</button></div>
            </form>
        </div>
    </AppLayout>
</template>

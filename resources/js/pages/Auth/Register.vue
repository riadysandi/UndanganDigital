<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post('/register', {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <AppLayout>
        <div class="min-h-[80vh] flex flex-col justify-center py-12 sm:px-6 lg:px-8">
            <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-rose-500 to-amber-500 flex items-center justify-center text-white mx-auto shadow-md shadow-rose-200 mb-4">
                    <span class="font-serif font-bold text-2xl">U</span>
                </div>
                <h2 class="text-3xl font-serif font-bold text-stone-900">Buat Akun Baru</h2>
                <p class="mt-2 text-sm text-stone-600">
                    Sudah punya akun?
                    <Link href="/login" class="font-medium text-rose-600 hover:text-rose-500">
                        Masuk di sini
                    </Link>
                </p>
            </div>

            <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
                <div class="bg-white py-8 px-6 shadow-sm border border-stone-200 sm:rounded-2xl sm:px-10">
                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-stone-700">Nama Lengkap / Pasangan</label>
                            <input
                                v-model="form.name"
                                type="text"
                                required
                                class="mt-1 block w-full px-3.5 py-2.5 bg-stone-50 border border-stone-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-rose-500 focus:bg-white"
                                placeholder="Contoh: Dimas & Anisa"
                            />
                            <div v-if="form.errors.name" class="text-xs text-rose-600 mt-1">
                                {{ form.errors.name }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-stone-700">Alamat Email</label>
                            <input
                                v-model="form.email"
                                type="email"
                                required
                                class="mt-1 block w-full px-3.5 py-2.5 bg-stone-50 border border-stone-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-rose-500 focus:bg-white"
                                placeholder="nama@email.com"
                            />
                            <div v-if="form.errors.email" class="text-xs text-rose-600 mt-1">
                                {{ form.errors.email }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-stone-700">Password</label>
                            <input
                                v-model="form.password"
                                type="password"
                                required
                                class="mt-1 block w-full px-3.5 py-2.5 bg-stone-50 border border-stone-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-rose-500 focus:bg-white"
                                placeholder="Minimal 8 karakter"
                            />
                            <div v-if="form.errors.password" class="text-xs text-rose-600 mt-1">
                                {{ form.errors.password }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-stone-700">Konfirmasi Password</label>
                            <input
                                v-model="form.password_confirmation"
                                type="password"
                                required
                                class="mt-1 block w-full px-3.5 py-2.5 bg-stone-50 border border-stone-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-rose-500 focus:bg-white"
                                placeholder="Ulangi password"
                            />
                        </div>

                        <div class="pt-2">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white bg-rose-600 hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-rose-500 transition-all disabled:opacity-50"
                            >
                                {{ form.processing ? 'Mendaftarkan...' : 'Daftar Sekarang' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

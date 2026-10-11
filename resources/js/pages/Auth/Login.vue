<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
};

const fillDemo = (role: 'admin' | 'customer') => {
    if (role === 'admin') {
        form.email = 'admin@undangan.id';
        form.password = 'password123';
    } else {
        form.email = 'pengantin@undangan.id';
        form.password = 'password123';
    }
};
</script>

<template>
    <AppLayout>
        <div class="min-h-[80vh] flex flex-col justify-center py-12 sm:px-6 lg:px-8">
            <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-rose-500 to-amber-500 flex items-center justify-center text-white mx-auto shadow-md shadow-rose-200 mb-4">
                    <span class="font-serif font-bold text-2xl">U</span>
                </div>
                <h2 class="text-3xl font-serif font-bold text-stone-900">Masuk ke Akun Anda</h2>
                <p class="mt-2 text-sm text-stone-600">
                    Atau
                    <Link href="/register" class="font-medium text-rose-600 hover:text-rose-500">
                        daftar akun baru sekarang
                    </Link>
                </p>
            </div>

            <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
                <div class="bg-white py-8 px-6 shadow-sm border border-stone-200 sm:rounded-2xl sm:px-10">
                    <!-- Quick Demo Credentials Box -->
                    <div class="mb-6 p-4 rounded-xl bg-amber-50/80 border border-amber-200">
                        <div class="text-xs font-semibold text-amber-900 mb-2">⚡ Akun Demo Bawaan:</div>
                        <div class="flex gap-2">
                            <button
                                type="button"
                                @click="fillDemo('customer')"
                                class="flex-1 py-1.5 px-2 bg-amber-100/80 hover:bg-amber-200 text-amber-800 rounded-lg text-xs font-medium transition-colors border border-amber-300"
                            >
                                Pengantin (Customer)
                            </button>
                            <Link
                                href="/admin/login"
                                class="flex-1 py-1.5 px-2 bg-stone-900 hover:bg-stone-800 text-amber-300 rounded-lg text-xs font-medium transition-colors border border-stone-800 text-center flex items-center justify-center gap-1"
                            >
                                <span>🛡️</span>
                                <span>Portal Admin</span>
                            </Link>
                        </div>
                    </div>

                    <form @submit.prevent="submit" class="space-y-5">
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
                                placeholder="••••••••"
                            />
                            <div v-if="form.errors.password" class="text-xs text-rose-600 mt-1">
                                {{ form.errors.password }}
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <input
                                    v-model="form.remember"
                                    id="remember"
                                    type="checkbox"
                                    class="h-4 w-4 text-rose-600 focus:ring-rose-500 border-stone-300 rounded"
                                />
                                <label for="remember" class="ml-2 block text-xs text-stone-600">Ingat saya</label>
                            </div>
                        </div>

                        <div>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white bg-rose-600 hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-rose-500 transition-all disabled:opacity-50"
                            >
                                {{ form.processing ? 'Memproses...' : 'Masuk Sekarang' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

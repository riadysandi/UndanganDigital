<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = useForm({
    email: 'admin@undangan.id',
    password: 'password123',
    remember: true,
});

const isOneClickLoading = ref(false);

const submit = () => {
    form.post('/admin/login', {
        onFinish: () => form.reset('password'),
    });
};

const oneClickAdminLogin = () => {
    isOneClickLoading.value = true;
    form.email = 'admin@undangan.id';
    form.password = 'password123';
    form.remember = true;
    form.post('/admin/login', {
        onFinish: () => {
            isOneClickLoading.value = false;
        },
    });
};
</script>

<template>
    <div class="min-h-screen bg-[#0b0f17] text-stone-100 flex flex-col justify-center py-12 px-4 sm:px-6 lg:px-8 selection:bg-amber-500 selection:text-stone-950 relative overflow-hidden">
        <!-- Background Ambient Glow -->
        <div class="absolute -top-40 -left-40 w-96 h-96 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-blue-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="sm:mx-auto sm:w-full sm:max-w-md text-center relative z-10">
            <!-- Brand Badge -->
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/10 border border-amber-500/30 text-amber-400 text-xs font-mono uppercase tracking-widest mb-4">
                <span>🛡️</span>
                <span>Portal Administrator & Tim</span>
            </div>

            <h1 class="text-3xl font-serif font-bold text-white tracking-tight">
                Panel Kontrol SaaS
            </h1>
            <p class="mt-2 text-xs text-stone-400">
                Akses manajemen terpusat: template 3D, pesanan jasa, analitik pengguna & billing.
            </p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md relative z-10">
            <div class="bg-[#121824] py-8 px-6 shadow-2xl rounded-3xl border border-stone-800 sm:px-10 space-y-6">
                
                <!-- Quick 1-Click Login Button for Admin Demo/Ease -->
                <div class="p-4 rounded-2xl bg-gradient-to-br from-amber-500/10 to-amber-600/5 border border-amber-500/30 text-center space-y-2">
                    <div class="text-xs font-semibold text-amber-300">
                        ⚡ Akses Instan Administrator:
                    </div>
                    <p class="text-[11px] text-stone-400">
                        Klik tombol di bawah untuk langsung login tanpa perlu mengetik kredensial.
                    </p>
                    <button
                        type="button"
                        @click="oneClickAdminLogin"
                        :disabled="isOneClickLoading || form.processing"
                        class="w-full py-2.5 px-4 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-stone-950 font-bold text-xs uppercase tracking-wider shadow-lg shadow-amber-500/20 hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center justify-center gap-2"
                    >
                        <span v-if="isOneClickLoading" class="animate-spin">⏳</span>
                        <span v-else>🚀</span>
                        <span>{{ isOneClickLoading ? 'Mengautentikasi...' : 'Masuk Otomatis Sebagai Admin (1-Click)' }}</span>
                    </button>
                </div>

                <div class="relative flex py-1 items-center">
                    <div class="flex-grow border-t border-stone-800"></div>
                    <span class="flex-shrink mx-4 text-[10px] uppercase font-mono tracking-widest text-stone-500">atau login manual</span>
                    <div class="flex-grow border-t border-stone-800"></div>
                </div>

                <!-- Manual Login Form -->
                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-stone-300 mb-1">Email Administrator</label>
                        <input
                            v-model="form.email"
                            type="email"
                            required
                            class="w-full px-3.5 py-2.5 bg-[#0b0f17] border border-stone-700 rounded-xl text-sm text-white placeholder-stone-600 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-colors"
                            placeholder="admin@undangan.id"
                        />
                        <div v-if="form.errors.email" class="text-xs text-rose-400 mt-1">
                            {{ form.errors.email }}
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-medium text-stone-300">Password</label>
                            <span class="text-[10px] text-stone-500">Default: password / password123</span>
                        </div>
                        <input
                            v-model="form.password"
                            type="password"
                            required
                            class="w-full px-3.5 py-2.5 bg-[#0b0f17] border border-stone-700 rounded-xl text-sm text-white placeholder-stone-600 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-colors"
                            placeholder="••••••••"
                        />
                        <div v-if="form.errors.password" class="text-xs text-rose-400 mt-1">
                            {{ form.errors.password }}
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-1">
                        <label class="flex items-center cursor-pointer">
                            <input
                                v-model="form.remember"
                                type="checkbox"
                                class="h-4 w-4 rounded bg-[#0b0f17] border-stone-700 text-amber-500 focus:ring-amber-400"
                            />
                            <span class="ml-2 text-xs text-stone-400">Ingat sesi admin di browser ini</span>
                        </label>
                    </div>

                    <div class="pt-2">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full py-3 px-4 rounded-xl bg-stone-800 hover:bg-stone-700 border border-stone-700 text-amber-200 text-xs font-bold uppercase tracking-wider shadow-md hover:scale-[1.01] active:scale-[0.99] transition-all disabled:opacity-50 flex items-center justify-center gap-2"
                        >
                            <span>{{ form.processing ? 'Memverifikasi...' : 'Verifikasi & Buka Panel Admin' }}</span>
                            <span>→</span>
                        </button>
                    </div>
                </form>

                <!-- Security & Notice Footer -->
                <div class="pt-4 border-t border-stone-800/80 text-center space-y-3">
                    <div class="flex items-center justify-center gap-1.5 text-[10px] text-stone-500">
                        <span>🔒</span>
                        <span>Sesi terenkripsi 256-bit SSL & diawasi sistem Audit Log.</span>
                    </div>

                    <div class="flex items-center justify-center gap-4 text-xs text-stone-400 pt-1">
                        <Link href="/" class="hover:text-amber-400 transition-colors">
                            ← Website Utama
                        </Link>
                        <span>&bull;</span>
                        <Link href="/login" class="hover:text-amber-400 transition-colors">
                            Login Pengantin (Customer)
                        </Link>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>

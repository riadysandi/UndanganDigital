<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const showPassword = ref(false);

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post('/admin/login', {
        onFinish: () => form.reset('password'),
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
                Otentikasi terenkripsi untuk manajemen platform, pesanan jasa, dan analitik.
            </p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md relative z-10">
            <div class="bg-[#121824] py-8 px-6 shadow-2xl rounded-3xl border border-stone-800 sm:px-10 space-y-6">
                <!-- Admin Login Form -->
                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-stone-300 mb-1">Email Administrator</label>
                        <input
                            v-model="form.email"
                            type="email"
                            required
                            autocomplete="username"
                            class="w-full px-3.5 py-2.5 bg-[#0b0f17] border border-stone-700 rounded-xl text-sm text-white placeholder-stone-600 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-colors"
                            placeholder="admin@undangan.id"
                        />
                        <div v-if="form.errors.email" class="text-xs text-rose-400 mt-1">
                            {{ form.errors.email }}
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-medium text-stone-300">Kata Sandi</label>
                        </div>
                        <div class="relative">
                            <input
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                required
                                autocomplete="current-password"
                                class="w-full px-3.5 py-2.5 bg-[#0b0f17] border border-stone-700 rounded-xl text-sm text-white placeholder-stone-600 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 pr-10 transition-colors"
                                placeholder="Masukkan kata sandi administrator"
                            />
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-stone-500 hover:text-stone-300"
                            >
                                <svg v-if="showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" /></svg>
                                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                            </button>
                        </div>
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
                            class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-stone-950 font-bold text-xs uppercase tracking-wider shadow-lg shadow-amber-500/20 hover:scale-[1.01] active:scale-[0.99] transition-all disabled:opacity-50 flex items-center justify-center gap-2"
                        >
                            <span>{{ form.processing ? 'Memverifikasi...' : 'Otentikasi & Buka Portal' }}</span>
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

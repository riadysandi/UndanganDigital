<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps<{
    title: string;
}>();
</script>

<template>
    <div class="min-h-screen bg-stone-50 flex items-center justify-center p-4 font-sans text-center">
        <div class="max-w-md w-full bg-white p-8 rounded-3xl border border-stone-200 shadow-sm space-y-4">
            <div class="w-16 h-16 rounded-full bg-stone-100 text-stone-400 flex items-center justify-center text-2xl mx-auto">
                ⏳
            </div>
            <h1 class="text-xl font-serif font-bold text-stone-900">Masa Aktif Undangan Berakhir</h1>
            <p class="text-xs text-stone-500 leading-relaxed">
                Masa tayang undangan "<strong>{{ title }}</strong>" telah melewati batas periode aktif. Terima kasih atas perhatian Anda.
            </p>
            <div class="pt-4">
                <Link
                    href="/"
                    class="inline-block px-5 py-2.5 bg-stone-900 text-white rounded-xl text-xs font-semibold hover:bg-stone-800 transition-colors"
                >
                    Kembali ke Beranda
                </Link>
            </div>
        </div>
    </div>
</template>

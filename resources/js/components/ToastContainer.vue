<script setup lang="ts">
import { useToast } from '@/composables/useToast';
import { usePage } from '@inertiajs/vue3';
import { watch } from 'vue';

const { toasts, remove } = useToast();
const page = usePage();

// Automatically watch for Inertia flash messages and trigger toasts
watch(
    () => (page.props as any).flash,
    (flash) => {
        if (!flash) return;
        if (flash.success) {
            useToast().success(flash.success);
        }
        if (flash.error) {
            useToast().error(flash.error);
        }
        if (flash.message) {
            useToast().info(flash.message);
        }
    },
    { deep: true }
);
</script>

<template>
    <div
        class="fixed top-4 right-4 sm:top-5 sm:right-5 z-[9999] flex flex-col gap-2.5 max-w-sm w-full pointer-events-none px-4 sm:px-0"
    >
        <transition-group
            enter-active-class="transform ease-out duration-300 transition"
            enter-from-class="translate-y-[-10px] opacity-0 sm:translate-y-0 sm:translate-x-4"
            enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0 translate-y-[-10px] sm:translate-y-0 sm:translate-x-4"
        >
            <div
                v-for="toast in toasts"
                :key="toast.id"
                class="pointer-events-auto rounded-2xl shadow-xl border p-3.5 flex items-start gap-3 backdrop-blur-md bg-white/95 transition-all text-xs"
                :class="{
                    'border-emerald-200 shadow-emerald-500/10': toast.type === 'success',
                    'border-rose-200 shadow-rose-500/10': toast.type === 'error',
                    'border-amber-200 shadow-amber-500/10': toast.type === 'warning',
                    'border-sky-200 shadow-sky-500/10': toast.type === 'info',
                }"
            >
                <!-- Icon Indicator -->
                <div
                    class="w-7 h-7 rounded-xl flex items-center justify-center shrink-0 text-sm font-bold shadow-xs"
                    :class="{
                        'bg-emerald-100 text-emerald-700': toast.type === 'success',
                        'bg-rose-100 text-rose-700': toast.type === 'error',
                        'bg-amber-100 text-amber-800': toast.type === 'warning',
                        'bg-sky-100 text-sky-700': toast.type === 'info',
                    }"
                >
                    <span v-if="toast.type === 'success'">✓</span>
                    <span v-else-if="toast.type === 'error'">✕</span>
                    <span v-else-if="toast.type === 'warning'">⚠️</span>
                    <span v-else>ℹ</span>
                </div>

                <!-- Toast Content -->
                <div class="flex-1 min-w-0 pt-0.5">
                    <div
                        v-if="toast.title"
                        class="font-bold text-stone-900 mb-0.5 text-xs"
                    >
                        {{ toast.title }}
                    </div>
                    <div class="text-stone-700 leading-relaxed font-medium">
                        {{ toast.message }}
                    </div>
                </div>

                <!-- Dismiss Button -->
                <button
                    type="button"
                    @click="remove(toast.id)"
                    class="text-stone-400 hover:text-stone-700 p-1 rounded-lg text-xs leading-none shrink-0 transition-colors"
                    title="Tutup Notifikasi"
                >
                    ✕
                </button>
            </div>
        </transition-group>
    </div>
</template>

<script setup lang="ts">
import { useConfirm } from '@/composables/useConfirm';
import { onMounted, onUnmounted } from 'vue';

const { isOpen, options, handleConfirm, handleCancel } = useConfirm();

const handleKeydown = (e: KeyboardEvent) => {
    if (!isOpen.value) return;
    if (e.key === 'Escape') {
        handleCancel();
    }
};

onMounted(() => {
    window.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown);
});
</script>

<template>
    <div
        v-if="isOpen"
        class="fixed inset-0 z-[10000] flex items-center justify-center p-4 bg-stone-950/60 backdrop-blur-sm animate-in fade-in duration-200"
        @click.self="handleCancel"
    >
        <div
            class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl border border-stone-200 space-y-4 transform transition-all scale-100"
        >
            <!-- Header Icon & Title -->
            <div class="flex items-center gap-3.5">
                <div
                    class="w-12 h-12 rounded-2xl flex items-center justify-center shrink-0 text-xl font-bold"
                    :class="{
                        'bg-rose-100 text-rose-600': options.type === 'danger',
                        'bg-amber-100 text-amber-700': options.type === 'warning',
                        'bg-sky-100 text-sky-600': options.type === 'info',
                    }"
                >
                    <span v-if="options.type === 'danger'">🗑️</span>
                    <span v-else-if="options.type === 'warning'">⚠️</span>
                    <span v-else>ℹ️</span>
                </div>
                <div>
                    <h3 class="font-serif font-bold text-base text-stone-900 leading-snug">
                        {{ options.title }}
                    </h3>
                    <p class="text-[11px] text-stone-500 font-medium">
                        Konfirmasi diperlukan sebelum melanjutkan
                    </p>
                </div>
            </div>

            <!-- Message Body -->
            <p class="text-xs text-stone-600 leading-relaxed bg-stone-50 p-3.5 rounded-2xl border border-stone-100">
                {{ options.message }}
            </p>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-2.5 pt-1">
                <button
                    type="button"
                    @click="handleCancel"
                    class="px-4 py-2 rounded-xl text-xs font-semibold text-stone-600 hover:text-stone-900 bg-stone-100 hover:bg-stone-200 transition-colors"
                >
                    {{ options.cancelText || 'Batal' }}
                </button>
                <button
                    type="button"
                    @click="handleConfirm"
                    class="px-4 py-2 rounded-xl text-xs font-bold text-white shadow-sm transition-all"
                    :class="{
                        'bg-rose-600 hover:bg-rose-700 shadow-rose-200': options.type === 'danger',
                        'bg-amber-600 hover:bg-amber-700 shadow-amber-200': options.type === 'warning',
                        'bg-stone-900 hover:bg-stone-800': options.type === 'info',
                    }"
                >
                    {{ options.confirmText || 'Ya, Lanjutkan' }}
                </button>
            </div>
        </div>
    </div>
</template>

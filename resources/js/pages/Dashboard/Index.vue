<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { useConfirm } from '@/composables/useConfirm';
import { useToast } from '@/composables/useToast';

const { confirmAction } = useConfirm();
const { success } = useToast();

const props = defineProps<{
    invitations: Array<{
        id: number;
        title: string;
        slug: string;
        status: string;
        published_version_id: number | null;
        published_at: string | null;
        created_at: string;
        guests_count: number;
        rsvps_count: number;
        template: {
            name: string;
            thumbnail_path: string;
            is_premium: boolean;
        } | null;
    }>;
    templates: Array<{
        id: number;
        name: string;
        slug: string;
        category: string;
        thumbnail_path: string;
        is_premium: boolean;
    }>;
    isPremium: boolean;
}>();

const deleteInvitation = async (id: number, title: string) => {
    const ok = await confirmAction({
        title: 'Hapus Undangan?',
        message: `Yakin ingin menghapus undangan "${title}"? Seluruh data tamu dan pengaturan akan ikut terhapus.`,
        confirmText: 'Ya, Hapus Sekarang',
        cancelText: 'Batal',
        type: 'danger',
    });

    if (ok) {
        router.delete(`/invitations/${id}`, {
            onSuccess: () => {
                success(`Undangan "${title}" berhasil dihapus.`);
            },
        });
    }
};
</script>

<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">
            <!-- Header Section -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-8 border-b border-stone-200">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-serif font-bold text-stone-900">Dashboard Undangan</h1>
                    <p class="text-sm text-stone-500 mt-1">Kelola desain, tamu undangan, dan publikasi acara Anda.</p>
                </div>
                <div class="flex items-center gap-3">
                    <span v-if="isPremium" class="px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-300">
                        ⭐ Akun Premium Aktif
                    </span>
                    <Link
                        v-else
                        href="/pricing"
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold text-rose-700 bg-rose-50 border border-rose-200 hover:bg-rose-100 transition-colors"
                    >
                        ⚡ Upgrade Premium
                    </Link>
                    <Link
                        href="/invitations/create"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium text-white bg-rose-600 hover:bg-rose-700 shadow-sm transition-all"
                    >
                        <span>+</span> Buat Undangan Baru
                    </Link>
                </div>
            </div>

            <!-- Invitations List -->
            <div class="mt-8">
                <div v-if="invitations.length === 0" class="text-center py-16 bg-white rounded-2xl border border-dashed border-stone-300 p-8">
                    <div class="w-16 h-16 rounded-full bg-rose-50 text-rose-500 flex items-center justify-center text-2xl mx-auto mb-4">
                        💌
                    </div>
                    <h3 class="text-lg font-serif font-bold text-stone-800">Belum Ada Undangan</h3>
                    <p class="text-xs text-stone-500 max-w-sm mx-auto mt-1 mb-6">
                        Mulai langkah awal persiapan pernikahan Anda dengan memilih template desain yang indah.
                    </p>
                    <Link
                        href="/invitations/create"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-medium text-white bg-rose-600 hover:bg-rose-700 shadow-sm"
                    >
                        Pilih Template & Buat
                    </Link>
                </div>

                <div v-else class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div
                        v-for="inv in invitations"
                        :key="inv.id"
                        class="bg-white rounded-2xl border border-stone-200 overflow-hidden shadow-sm hover:shadow transition-shadow flex flex-col"
                    >
                        <!-- Top Banner / Image -->
                        <div class="relative h-44 bg-stone-100 overflow-hidden">
                            <img
                                :src="inv.template?.thumbnail_path || 'https://images.unsplash.com/photo-1519741497674-611481863552?auto=format&fit=crop&w=600&q=80'"
                                :alt="inv.title"
                                class="w-full h-full object-cover"
                            />
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
                            
                            <!-- Badges -->
                            <div class="absolute top-3 left-3">
                                <span
                                    v-if="inv.status === 'published'"
                                    class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-500 text-white shadow-sm flex items-center gap-1"
                                >
                                    <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
                                    Published
                                </span>
                                <span
                                    v-else
                                    class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-500 text-white shadow-sm"
                                >
                                    Draft
                                </span>
                            </div>

                            <div class="absolute bottom-3 left-3 right-3 text-white">
                                <div class="text-[11px] text-stone-200">Template: {{ inv.template?.name || 'Kustom' }}</div>
                                <h3 class="font-serif font-bold text-lg leading-tight truncate">{{ inv.title }}</h3>
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="p-5 flex-1 flex flex-col justify-between">
                            <div class="space-y-3">
                                <div class="flex items-center justify-between text-xs text-stone-600 bg-stone-50 p-2.5 rounded-xl border border-stone-100">
                                    <div>
                                        <span class="text-stone-400">Total Tamu:</span>
                                        <span class="font-bold text-stone-800 ml-1">{{ inv.guests_count }}</span>
                                    </div>
                                    <div>
                                        <span class="text-stone-400">RSVP Masuk:</span>
                                        <span class="font-bold text-emerald-600 ml-1">{{ inv.rsvps_count }}</span>
                                    </div>
                                </div>

                                <div class="text-xs text-stone-500 truncate">
                                    <span class="font-medium text-stone-700">Link Publik:</span>
                                    <a
                                        :href="`/i/${inv.slug}`"
                                        target="_blank"
                                        class="ml-1 text-rose-600 hover:underline"
                                    >
                                        /i/{{ inv.slug }}
                                    </a>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="mt-5 pt-4 border-t border-stone-100 flex flex-col gap-2">
                                <div class="grid grid-cols-2 gap-2">
                                    <Link
                                        :href="`/invitations/${inv.id}/editor`"
                                        class="flex items-center justify-center py-2 px-3 rounded-lg text-xs font-semibold text-white bg-stone-900 hover:bg-stone-800 transition-colors"
                                    >
                                        🎨 Edit Desain
                                    </Link>
                                    <Link
                                        :href="`/invitations/${inv.id}/guests`"
                                        class="flex items-center justify-center py-2 px-3 rounded-lg text-xs font-semibold text-stone-700 bg-stone-100 hover:bg-stone-200 transition-colors"
                                    >
                                        👥 Kelola Tamu
                                    </Link>
                                </div>
                                <div class="flex items-center justify-between pt-1">
                                    <a
                                        :href="`/i/${inv.slug}`"
                                        target="_blank"
                                        class="text-xs font-medium text-stone-600 hover:text-stone-900 flex items-center gap-1"
                                    >
                                        Buka Publik ↗
                                    </a>
                                    <button
                                        @click="deleteInvitation(inv.id, inv.title)"
                                        class="text-xs text-stone-400 hover:text-rose-600 transition-colors"
                                    >
                                        Hapus
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

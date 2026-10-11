<script setup lang="ts">
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps<{
    requests: {
        data: Array<{
            id: number;
            customer_name: string;
            whatsapp: string;
            email: string | null;
            package_selected: string;
            template_preference: string;
            groom_name: string;
            bride_name: string;
            akad_date: string | null;
            akad_venue: string | null;
            resepsi_date: string | null;
            resepsi_venue: string | null;
            photo_drive_link: string | null;
            notes: string | null;
            status: string;
            created_invitation_url: string | null;
            created_at: string;
        }>;
    };
}>();

const selectedReq = ref<any>(null);
const updateForm = ref({
    status: 'in_progress',
    created_invitation_url: '',
});

const openModal = (req: any) => {
    selectedReq.value = req;
    updateForm.value = {
        status: req.status,
        created_invitation_url: req.created_invitation_url || '',
    };
};

const saveStatus = () => {
    if (!selectedReq.value) return;
    router.patch(`/admin/service-requests/${selectedReq.value.id}`, updateForm.value, {
        onSuccess: () => {
            selectedReq.value = null;
        },
    });
};

const openWhatsApp = (phone: string, name: string) => {
    const cleanPhone = phone.replace(/^0/, '62').replace(/\D/g, '');
    const text = `Halo Kak ${name}, kami dari Tim Desainer Undangan.id terkait pemesanan jasa pembuatan undangan digital Anda. Apakah ada data tambahan atau referensi yang ingin disertakan?`;
    window.open(`https://api.whatsapp.com/send?phone=${cleanPhone}&text=${encodeURIComponent(text)}`, '_blank');
};
</script>

<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">
            <div class="flex items-center justify-between pb-6 border-b border-stone-200">
                <div>
                    <Link href="/admin/dashboard" class="text-xs font-semibold text-rose-600 hover:text-rose-700 flex items-center gap-1 mb-1">
                        ← Kembali ke Dashboard Admin
                    </Link>
                    <h1 class="text-2xl font-serif font-bold text-stone-900">Permintaan Jasa (Bantu Buat)</h1>
                    <p class="text-xs text-stone-500">Daftar calon pengantin yang memesan layanan pembuatan undangan Terima Beres.</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-stone-200 overflow-hidden shadow-sm mt-8">
                <table class="w-full text-left text-xs text-stone-600">
                    <thead class="bg-stone-50 border-b border-stone-200 text-stone-500 font-semibold uppercase text-[10px]">
                        <tr>
                            <th class="p-4">Pemesan & WA</th>
                            <th class="p-4">Paket & Tema</th>
                            <th class="p-4">Pasangan Pengantin</th>
                            <th class="p-4">Status Pengerjaan</th>
                            <th class="p-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        <tr v-for="req in requests.data" :key="req.id" class="hover:bg-stone-50/50">
                            <td class="p-4">
                                <div class="font-bold text-stone-900">{{ req.customer_name }}</div>
                                <div class="text-[11px] text-emerald-600 flex items-center gap-1 font-mono">
                                    <span>WA: {{ req.whatsapp }}</span>
                                </div>
                            </td>
                            <td class="p-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-700">
                                    {{ req.package_selected }}
                                </span>
                                <div class="text-stone-400 text-[11px] mt-1">{{ req.template_preference }}</div>
                            </td>
                            <td class="p-4">
                                <div class="font-bold text-stone-800">{{ req.groom_name }} & {{ req.bride_name }}</div>
                                <div class="text-stone-400 text-[10px]">{{ req.akad_date || 'Belum ada tanggal' }}</div>
                            </td>
                            <td class="p-4">
                                <span
                                    :class="[
                                        'px-2 py-0.5 rounded text-[10px] font-bold capitalize',
                                        req.status === 'completed' ? 'bg-emerald-100 text-emerald-800' :
                                        req.status === 'in_progress' ? 'bg-blue-100 text-blue-800' :
                                        req.status === 'contacted' ? 'bg-amber-100 text-amber-800' : 'bg-stone-100 text-stone-700'
                                    ]"
                                >
                                    {{ req.status }}
                                </span>
                                <div v-if="req.created_invitation_url" class="mt-1">
                                    <a :href="req.created_invitation_url" target="_blank" class="text-[10px] text-rose-600 underline">
                                        Lihat Undangan ↗
                                    </a>
                                </div>
                            </td>
                            <td class="p-4 text-right space-x-2">
                                <button
                                    @click="openWhatsApp(req.whatsapp, req.customer_name)"
                                    class="px-2.5 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-lg text-xs font-semibold"
                                >
                                    💬 Chat WA
                                </button>
                                <button
                                    @click="openModal(req)"
                                    class="px-2.5 py-1 bg-stone-900 hover:bg-stone-800 text-white rounded-lg text-xs font-semibold"
                                >
                                    Update
                                </button>
                            </td>
                        </tr>
                        <tr v-if="requests.data.length === 0">
                            <td colspan="5" class="p-8 text-center text-stone-400">
                                Belum ada permintaan jasa pembuatan saat ini.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Update Status Modal -->
        <div v-if="selectedReq" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-stone-200 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-stone-100">
                    <h3 class="font-serif font-bold text-base text-stone-900">Update Status Pengerjaan</h3>
                    <button @click="selectedReq = null" class="text-stone-400 hover:text-stone-600">✕</button>
                </div>

                <div class="space-y-3 text-xs">
                    <div>
                        <label class="block font-semibold text-stone-700 mb-1">Status</label>
                        <select v-model="updateForm.status" class="w-full p-2 border rounded-xl bg-stone-50">
                            <option value="pending">Pending (Menunggu)</option>
                            <option value="contacted">Contacted (Sudah Dihubungi)</option>
                            <option value="in_progress">In Progress (Sedang Dibuatkan)</option>
                            <option value="completed">Completed (Selesai)</option>
                            <option value="cancelled">Cancelled (Batal)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-stone-700 mb-1">URL Undangan yang Selesai Dibuat</label>
                        <input
                            v-model="updateForm.created_invitation_url"
                            type="url"
                            class="w-full p-2 border rounded-xl bg-stone-50"
                            placeholder="http://127.0.0.1:8000/i/slug-pengantin"
                        />
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button @click="selectedReq = null" class="px-4 py-2 border rounded-xl text-xs font-semibold text-stone-600">
                        Batal
                    </button>
                    <button @click="saveStatus" class="px-5 py-2 bg-stone-900 hover:bg-stone-800 text-white rounded-xl text-xs font-bold">
                        Simpan Perubahan
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

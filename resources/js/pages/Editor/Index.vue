<script setup lang="ts">
import { ref, onMounted, reactive, watch } from 'vue';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';
import { useToast } from '@/composables/useToast';
import ToastContainer from '@/components/ToastContainer.vue';

const props = defineProps<{
    invitation: {
        id: number;
        title: string;
        slug: string;
        status: string;
        published_version_id: number | null;
        design: {
            design_json: any;
            schema_version: number;
        } | null;
        events: Array<any>;
    };
    isPremium: boolean;
}>();

// Editor state
const toast = useToast();
const saveStatus = ref<'idle' | 'saving' | 'saved' | 'error'>('saved');
const lastSavedTime = ref<string>('Baru saja');
const previewKey = ref<number>(0);
const previewIframeRef = ref<HTMLIFrameElement | null>(null);
const activeTab = ref<'couple' | 'theme' | 'media' | 'events' | 'features'>('couple');

// Indonesian date formatting helper
const formatIndonesianDate = (dateStr: string) => {
    if (!dateStr) return '';
    try {
        const d = new Date(dateStr);
        if (isNaN(d.getTime())) return dateStr;
        return d.toLocaleDateString('id-ID', {
            weekday: 'long',
            day: 'numeric',
            month: 'long',
            year: 'numeric'
        });
    } catch {
        return dateStr;
    }
};

// Convert string to HTML5 datetime-local format (YYYY-MM-DDTHH:mm)
const toDatetimeLocal = (val: string) => {
    if (!val) return '';
    if (val.includes('T') && val.length === 16) return val;
    try {
        const d = new Date(val);
        if (isNaN(d.getTime())) return '';
        const year = d.getFullYear();
        const month = (d.getMonth() + 1).toString().padStart(2, '0');
        const day = d.getDate().toString().padStart(2, '0');
        const hours = d.getHours().toString().padStart(2, '0');
        const minutes = d.getMinutes().toString().padStart(2, '0');
        return `${year}-${month}-${day}T${hours}:${minutes}`;
    } catch {
        return '';
    }
};

// Refresh iframe preview manually
const refreshPreview = () => {
    previewKey.value++;
};

// Send live real-time design state to iframe for 0ms delay sync
const syncToPreview = () => {
    if (previewIframeRef.value && previewIframeRef.value.contentWindow) {
        try {
            previewIframeRef.value.contentWindow.postMessage({
                type: 'LIVE_UPDATE_DESIGN',
                design: JSON.parse(JSON.stringify(designData))
            }, '*');
        } catch (e) {
            // iframe cross-origin or loading
        }
    }
};

// Color Palette Presets
const colorPalettes = [
    {
        id: 'gold',
        name: 'Royal Gold',
        badge: 'bg-amber-500',
        primary: '#D4AF37',
        secondary: '#F3E5AB',
        bg: '#14171c',
        card: '#1a1f26',
    },
    {
        id: 'rose',
        name: 'Rose Gold',
        badge: 'bg-rose-400',
        primary: '#E0A9A5',
        secondary: '#FDE8E7',
        bg: '#1c1517',
        card: '#271d21',
    },
    {
        id: 'emerald',
        name: 'Emerald Jade',
        badge: 'bg-emerald-500',
        primary: '#10B981',
        secondary: '#A7F3D0',
        bg: '#0c1914',
        card: '#13261e',
    },
    {
        id: 'terracotta',
        name: 'Terracotta Warm',
        badge: 'bg-orange-600',
        primary: '#C25E43',
        secondary: '#F8D5C8',
        bg: '#1c1412',
        card: '#271c19',
    },
    {
        id: 'sapphire',
        name: 'Sapphire Navy',
        badge: 'bg-sky-500',
        primary: '#38BDF8',
        secondary: '#BAE6FD',
        bg: '#0b1329',
        card: '#121e3d',
    },
    {
        id: 'burgundy',
        name: 'Burgundy Wine',
        badge: 'bg-rose-700',
        primary: '#F43F5E',
        secondary: '#FECDD3',
        bg: '#1a0e14',
        card: '#29141f',
    },
];

const fontOptions = [
    { name: 'Playfair Display (Serif Mewah)', value: 'Playfair Display' },
    { name: 'Cormorant Garamond (Klasik Elegan)', value: 'Cormorant Garamond' },
    { name: 'Cinzel (Royal Kerajaan)', value: 'Cinzel' },
    { name: 'Great Vibes (Kaligrafi Script)', value: 'Great Vibes' },
    { name: 'Plus Jakarta Sans (Modern Bersih)', value: 'Plus Jakarta Sans' },
];

const musicPresets = [
    { name: 'Ambient Romantic Classical (Default)', url: 'https://actions.google.com/sounds/v1/ambient/relaxing_music.ogg' },
    { name: 'Gentle Wedding Strings', url: 'https://actions.google.com/sounds/v1/weather/gentle_rain_on_leaves.ogg' },
];

// Reactive Design State
const designData = reactive({
    schemaVersion: 1,
    theme: {
        palette: 'gold',
        primaryColor: '#D4AF37',
        secondaryColor: '#F3E5AB',
        bgColor: '#14171c',
        cardBgColor: '#1a1f26',
        textColor: '#f3f4f6',
        fontFamily: 'Playfair Display',
    },
    content: {
        coverBadge: 'Royal 3D Wax Seal Edition',
        coverSubtitle: "Walimatul 'Ursy",
        weddingTitle: props.invitation?.title || 'Dimas & Anisa',
        weddingDate: '2026-12-28',
        weddingDateText: 'Minggu, 28 Desember 2026',
        coverImage: 'https://images.unsplash.com/photo-1519741497674-611481863552?auto=format&fit=crop&w=800&q=80',
        quote: 'Dan di antara tanda-tanda kebesaran-Nya ialah Dia menciptakan pasangan-pasangan untukmu dari jenismu sendiri, agar kamu cenderung dan merasa tenteram kepadanya, dan dijadikan-Nya di antaramu rasa kasih dan sayang.',
        quoteSource: 'QS. Ar-Rum: 21',
        groom: {
            nickname: 'Dimas',
            fullName: 'Dimas Prasetyo, S.Kom',
            parents: 'Putra pertama dari Bpk. Bambang Prasetyo & Ibu Sri Wahyuni',
            photo: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=400&q=80',
            instagram: '@dimasprasetyo',
        },
        bride: {
            nickname: 'Anisa',
            fullName: 'Anisa Rahmawati, S.E',
            parents: 'Putri kedua dari Bpk. H. Rahmat Hidayat & Ibu Siti Aminah',
            photo: 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=400&q=80',
            instagram: '@anisarahmawati',
        },
        gallery: [
            'https://images.unsplash.com/photo-1511285560929-80b456fea0bc?auto=format&fit=crop&w=600&q=80',
            'https://images.unsplash.com/photo-1583939003579-730e3918a45a?auto=format&fit=crop&w=600&q=80',
            'https://images.unsplash.com/photo-1520854221256-17451cc331bf?auto=format&fit=crop&w=600&q=80',
            'https://images.unsplash.com/photo-1465495976277-4387d4b0b4c6?auto=format&fit=crop&w=600&q=80',
        ],
    },
    canvas: {
        width: 380,
        height: 620,
        background: '#14171c',
        primaryColor: '#D4AF37',
        fontFamily: 'Playfair Display',
    },
    musicUrl: 'https://actions.google.com/sounds/v1/ambient/relaxing_music.ogg',
    gift: {
        enabled: true,
        title: 'Amplop Digital & Kado Nikah',
        description: 'Doa restu Anda adalah karunia terindah bagi kami. Namun jika memberi adalah ungkapan kasih, Anda dapat menyalurkannya melalui cashless digital gift di bawah ini:',
        accounts: [
            {
                bank: 'BCA',
                account_number: '8210984721',
                account_name: 'Dimas Prasetyo',
                qr_image: 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=00020101021226590014ID.LINKAJA.WWW01189360091800000000005204581253033605802ID5914DIMAS_PRASETYO6007JAKARTA63041234',
            },
            {
                bank: 'Bank Mandiri',
                account_number: '1370019283741',
                account_name: 'Anisa Rahmawati',
                qr_image: 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=00020101021226590014ID.LINKAJA.WWW01189360091800000000005204581253033605802ID5915ANISA_RAHMAWATI6007JAKARTA63045678',
            },
        ],
        physical_gift: {
            enabled: true,
            receiver_name: 'Dimas & Anisa',
            phone: '0812-3456-7890',
            address: 'Jl. Melati Indah No. 42, RT 03/RW 05, Cilandak Barat, Jakarta Selatan 12430',
        },
    },
    sections: [
        { id: 'cover', type: 'cover', title: 'Cover Utama', visible: true },
        { id: 'couple', type: 'couple', title: 'Mempelai Pengantin', visible: true },
        { id: 'events', type: 'events', title: 'Agenda Acara (Akad & Resepsi)', visible: true },
        { id: 'gallery', type: 'gallery', title: 'Galeri Foto', visible: true },
        { id: 'rsvp', type: 'rsvp', title: 'Konfirmasi RSVP & Doa Restu', visible: true },
    ],
});

// Dedicated date input picker state
const weddingDateOnly = ref<string>('2026-12-28');
const onWeddingDateOnlyChange = () => {
    if (!weddingDateOnly.value) return;
    designData.content.weddingDate = weddingDateOnly.value;
    designData.content.weddingDateText = formatIndonesianDate(weddingDateOnly.value);
    syncToPreview();
    triggerAutosave();
};

// Watch designData deeply for instant real-time iframe sync
watch(
    () => designData,
    () => {
        syncToPreview();
    },
    { deep: true }
);

// Apply Color Palette Preset
const applyPalette = (palette: typeof colorPalettes[0]) => {
    designData.theme.palette = palette.id;
    designData.theme.primaryColor = palette.primary;
    designData.theme.secondaryColor = palette.secondary;
    designData.theme.bgColor = palette.bg;
    designData.theme.cardBgColor = palette.card;
    designData.canvas.background = palette.bg;
    designData.canvas.primaryColor = palette.primary;
    syncToPreview();
    triggerAutosave();
};

// Events in invitation
const eventsList = ref<any[]>(props.invitation.events || []);

const addEvent = () => {
    eventsList.value.push({
        id: Date.now(),
        name: 'Resepsi Pernikahan',
        starts_at: '2026-12-28T11:00',
        ends_at: '2026-12-28T14:00',
        timezone: 'WIB',
        venue_name: 'Grand Ballroom Hotel Arya',
        address: 'Jl. Sudirman No. 123, Jakarta Pusat',
        maps_url: 'https://maps.google.com',
    });
    syncToPreview();
    triggerAutosave();
};

const removeEvent = (index: number) => {
    eventsList.value.splice(index, 1);
    syncToPreview();
    triggerAutosave();
};

// Bank Accounts for Digital Gift
const addBankAccount = () => {
    if (!designData.gift.accounts) {
        designData.gift.accounts = [];
    }
    designData.gift.accounts.push({
        bank: 'BCA',
        account_number: '',
        account_name: '',
        qr_image: '',
    });
    syncToPreview();
    triggerAutosave();
};

const removeBankAccount = (idx: number) => {
    designData.gift.accounts.splice(idx, 1);
    syncToPreview();
    triggerAutosave();
};

// Upload Handler (Photos, QRIS, and Music Audio)
const isUploading = ref(false);
const uploadError = ref('');
const targetUploadField = ref<string>(''); // 'cover' | 'groom' | 'bride' | 'gallery_0' .. 'gallery_3' | 'qris_0' .. 'qris_n' | 'music'
const fileInputRef = ref<HTMLInputElement | null>(null);

const triggerUploadFor = (field: string) => {
    targetUploadField.value = field;
    if (fileInputRef.value) {
        fileInputRef.value.click();
    }
};

const handleFileUpload = async (event: any) => {
    const file = event.target?.files?.[0];
    if (!file) return;

    isUploading.value = true;
    uploadError.value = '';

    const formData = new FormData();
    formData.append('file', file);

    let uploadType = 'gallery';
    if (targetUploadField.value === 'music') {
        uploadType = 'music';
    } else if (targetUploadField.value === 'cover') {
        uploadType = 'cover';
    } else if (targetUploadField.value === 'groom') {
        uploadType = 'groom';
    } else if (targetUploadField.value === 'bride') {
        uploadType = 'bride';
    }
    formData.append('type', uploadType);

    try {
        const res = await axios.post(`/api/invitations/${props.invitation.id}/upload`, formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        });

        if (res.data?.success && res.data?.url) {
            const uploadedUrl = res.data.url;
            if (targetUploadField.value === 'music') {
                designData.musicUrl = uploadedUrl;
            } else if (targetUploadField.value === 'cover') {
                designData.content.coverImage = uploadedUrl;
            } else if (targetUploadField.value === 'groom') {
                designData.content.groom.photo = uploadedUrl;
            } else if (targetUploadField.value === 'bride') {
                designData.content.bride.photo = uploadedUrl;
            } else if (targetUploadField.value.startsWith('gallery_')) {
                const idx = parseInt(targetUploadField.value.split('_')[1]);
                if (!isNaN(idx) && designData.content.gallery[idx] !== undefined) {
                    designData.content.gallery[idx] = uploadedUrl;
                }
            } else if (targetUploadField.value.startsWith('qris_')) {
                const idx = parseInt(targetUploadField.value.split('_')[1]);
                if (!isNaN(idx) && designData.gift.accounts[idx] !== undefined) {
                    designData.gift.accounts[idx].qr_image = uploadedUrl;
                }
            }
            syncToPreview();
            triggerAutosave();
        } else {
            uploadError.value = res.data?.message || 'Gagal mengunggah file.';
        }
    } catch (e: any) {
        uploadError.value = e.response?.data?.message || 'Terjadi kesalahan saat mengunggah file.';
    } finally {
        isUploading.value = false;
        if (event.target) event.target.value = '';
    }
};

// Publish Modal State
const showPublishModal = ref(false);
const isPublishing = ref(false);
const publishMessage = ref('');
const publishedUrl = ref('');

// Initialize from backend
onMounted(() => {
    if (props.invitation.design?.design_json) {
        const loaded = props.invitation.design.design_json;
        if (loaded.theme) Object.assign(designData.theme, loaded.theme);
        if (loaded.content) {
            if (loaded.content.groom) Object.assign(designData.content.groom, loaded.content.groom);
            if (loaded.content.bride) Object.assign(designData.content.bride, loaded.content.bride);
            if (loaded.content.gallery && Array.isArray(loaded.content.gallery)) {
                designData.content.gallery = [...loaded.content.gallery];
            }
            Object.assign(designData.content, loaded.content);

            // Initialize date picker value
            if (loaded.content.weddingDate) {
                weddingDateOnly.value = loaded.content.weddingDate;
            } else if (loaded.content.weddingDateText) {
                const match = loaded.content.weddingDateText.match(/\b(20\d\d-\d\d-\d\d)\b/);
                if (match) {
                    weddingDateOnly.value = match[1];
                }
            }
        }
        if (loaded.canvas) Object.assign(designData.canvas, loaded.canvas);
        if (loaded.musicUrl) designData.musicUrl = loaded.musicUrl;
        if (loaded.gift) Object.assign(designData.gift, loaded.gift);
        if (loaded.sections) designData.sections = loaded.sections;
    }

    // Normalize event start dates to datetime-local format
    if (eventsList.value && eventsList.value.length > 0) {
        eventsList.value.forEach((ev) => {
            if (ev.starts_at) {
                ev.starts_at = toDatetimeLocal(ev.starts_at);
            }
            if (ev.ends_at) {
                ev.ends_at = toDatetimeLocal(ev.ends_at);
            }
        });
    }

    // Set initial sync after iframe loads
    setTimeout(() => {
        syncToPreview();
    }, 1500);
});

// Autosave with Debounce
let autosaveTimeout: any = null;
const triggerAutosave = () => {
    syncToPreview();
    saveStatus.value = 'saving';
    clearTimeout(autosaveTimeout);
    autosaveTimeout = setTimeout(async () => {
        await executeSave();
    }, 800);
};

const executeSave = async () => {
    try {
        saveStatus.value = 'saving';

        const payload = {
            design: {
                schemaVersion: 1,
                theme: designData.theme,
                content: designData.content,
                canvas: designData.canvas,
                musicUrl: designData.musicUrl,
                gift: designData.gift,
                sections: designData.sections,
            },
            events: eventsList.value,
        };

        const res = await axios.put(`/api/invitations/${props.invitation.id}/design`, payload);

        if (res.status === 200 && res.data?.success) {
            saveStatus.value = 'saved';
            const now = new Date();
            lastSavedTime.value = `${now.getHours().toString().padStart(2, '0')}:${now.getMinutes().toString().padStart(2, '0')}:${now.getSeconds().toString().padStart(2, '0')}`;
            syncToPreview();
        } else {
            saveStatus.value = 'error';
        }
    } catch (err) {
        saveStatus.value = 'error';
    }
};

// Publish Action
const openPublishModal = () => {
    showPublishModal.value = true;
    publishMessage.value = '';
};

const confirmPublish = async () => {
    isPublishing.value = true;
    publishMessage.value = '';

    await executeSave();

    try {
        const res = await axios.post(`/api/invitations/${props.invitation.id}/publish`);
        if (res.data?.success) {
            publishedUrl.value = res.data.public_url;
            publishMessage.value = 'Undangan berhasil diterbitkan ke publik!';
            toast.success('Undangan berhasil diterbitkan ke publik!');
        } else {
            publishMessage.value = res.data?.message || 'Gagal menerbitkan undangan.';
            toast.error(res.data?.message || 'Gagal menerbitkan undangan.');
        }
    } catch (e: any) {
        publishMessage.value = e.response?.data?.message || 'Terjadi kesalahan saat mempublikasikan.';
        toast.error(e.response?.data?.message || 'Terjadi kesalahan saat mempublikasikan.');
    } finally {
        isPublishing.value = false;
    }
};

const copyUrl = () => {
    navigator.clipboard.writeText(publishedUrl.value);
    toast.success('URL Undangan berhasil disalin ke clipboard!');
};
</script>

<template>
    <div class="h-screen flex flex-col bg-stone-100 overflow-hidden font-sans">
        <!-- Hidden file input for direct uploads (Photos, QRIS, & MP3 Audio) -->
        <input
            ref="fileInputRef"
            type="file"
            accept="image/png,image/jpeg,image/webp,image/jpg,audio/mp3,audio/mpeg,audio/ogg,audio/wav,.mp3,.ogg,.wav"
            class="hidden"
            @change="handleFileUpload"
        />

        <!-- Top Header Navigation -->
        <header class="h-14 bg-white border-b border-stone-200 px-4 flex items-center justify-between z-30 shrink-0 shadow-xs">
            <div class="flex items-center gap-3">
                <Link
                    href="/dashboard"
                    class="p-2 text-stone-500 hover:text-stone-800 hover:bg-stone-100 rounded-lg text-xs font-semibold flex items-center gap-1.5 transition-colors"
                >
                    <span>←</span>
                    <span class="hidden sm:inline">Dashboard</span>
                </Link>
                <div class="h-4 w-px bg-stone-200"></div>
                <div class="flex items-center gap-2">
                    <span class="font-serif font-bold text-stone-800 text-sm truncate max-w-[220px]">
                        {{ designData.content.weddingTitle }}
                    </span>
                    <span
                        v-if="saveStatus === 'saving'"
                        class="text-[10px] text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full font-medium flex items-center gap-1 animate-pulse"
                    >
                        <span>●</span> Menyimpan...
                    </span>
                    <span
                        v-else-if="saveStatus === 'saved'"
                        class="text-[10px] text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full font-medium flex items-center gap-1"
                    >
                        <span>✓</span> Tersimpan {{ lastSavedTime }}
                    </span>
                    <span
                        v-else-if="saveStatus === 'error'"
                        class="text-[10px] text-rose-600 bg-rose-50 px-2 py-0.5 rounded-full font-medium flex items-center gap-1"
                    >
                        <span>✕</span> Gagal Menyimpan
                    </span>
                </div>
            </div>

            <!-- Right Controls: Refresh & Publish -->
            <div class="flex items-center gap-2.5">
                <button
                    type="button"
                    @click="refreshPreview"
                    class="px-2.5 py-1.5 text-xs font-semibold text-stone-700 bg-stone-100 hover:bg-stone-200 rounded-lg transition-colors flex items-center gap-1"
                    title="Segarkan tampilan preview"
                >
                    <span>🔄 Refresh</span>
                </button>
                <a
                    :href="`/i/${invitation.slug}?preview=1`"
                    target="_blank"
                    class="px-3 py-1.5 text-xs font-semibold text-stone-700 bg-stone-100 hover:bg-stone-200 rounded-lg transition-colors flex items-center gap-1"
                >
                    Tab Baru ↗
                </a>
                <button
                    @click="openPublishModal"
                    class="px-4 py-1.5 text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-lg shadow-sm shadow-rose-200 transition-all flex items-center gap-1"
                >
                    🚀 Publikasikan
                </button>
            </div>
        </header>

        <!-- Main Workspace -->
        <div class="flex-1 flex overflow-hidden">
            <!-- Left Sidebar: Dedicated Editing Tabs -->
            <aside class="w-88 sm:w-96 bg-white border-r border-stone-200 flex flex-col shrink-0 shadow-sm z-10">
                <!-- Navigation Tabs -->
                <div class="grid grid-cols-5 border-b border-stone-200 text-xs font-semibold text-center bg-stone-50/60">
                    <button
                        @click="activeTab = 'couple'"
                        :class="['py-3 border-b-2 transition-colors flex flex-col items-center gap-1', activeTab === 'couple' ? 'border-rose-500 text-rose-600 font-bold bg-white' : 'border-transparent text-stone-500 hover:text-stone-800']"
                    >
                        <span>✍️</span>
                        <span class="text-[10px]">Mempelai</span>
                    </button>
                    <button
                        @click="activeTab = 'theme'"
                        :class="['py-3 border-b-2 transition-colors flex flex-col items-center gap-1', activeTab === 'theme' ? 'border-rose-500 text-rose-600 font-bold bg-white' : 'border-transparent text-stone-500 hover:text-stone-800']"
                    >
                        <span>🎨</span>
                        <span class="text-[10px]">Warna</span>
                    </button>
                    <button
                        @click="activeTab = 'media'"
                        :class="['py-3 border-b-2 transition-colors flex flex-col items-center gap-1', activeTab === 'media' ? 'border-rose-500 text-rose-600 font-bold bg-white' : 'border-transparent text-stone-500 hover:text-stone-800']"
                    >
                        <span>📷</span>
                        <span class="text-[10px]">Foto</span>
                    </button>
                    <button
                        @click="activeTab = 'events'"
                        :class="['py-3 border-b-2 transition-colors flex flex-col items-center gap-1', activeTab === 'events' ? 'border-rose-500 text-rose-600 font-bold bg-white' : 'border-transparent text-stone-500 hover:text-stone-800']"
                    >
                        <span>📅</span>
                        <span class="text-[10px]">Acara</span>
                    </button>
                    <button
                        @click="activeTab = 'features'"
                        :class="['py-3 border-b-2 transition-colors flex flex-col items-center gap-1', activeTab === 'features' ? 'border-rose-500 text-rose-600 font-bold bg-white' : 'border-transparent text-stone-500 hover:text-stone-800']"
                    >
                        <span>🎁</span>
                        <span class="text-[10px]">Fitur</span>
                    </button>
                </div>

                <!-- Tab Content Body (Scrollable) -->
                <div class="p-4 flex-1 overflow-y-auto space-y-4 text-xs">
                    <!-- ============================================== -->
                    <!-- TAB 1: TEKS & PROFIL MEMPELAI                  -->
                    <!-- ============================================== -->
                    <div v-if="activeTab === 'couple'" class="space-y-4">
                        <div class="p-3 bg-rose-50/60 rounded-xl border border-rose-100 text-stone-700 text-[11px] leading-relaxed">
                            💡 <strong>Pratinjau Langsung (Real-time):</strong> Setiap ketikan teks mempelai langsung tampil seketika di layar smartphone di samping tanpa jeda.
                        </div>

                        <!-- Info Cover & Judul -->
                        <div class="space-y-2.5 border-b pb-4 border-stone-200">
                            <div class="text-[10px] font-bold text-stone-400 uppercase tracking-wider">Judul & Cover</div>
                            <div>
                                <label class="block text-[11px] font-semibold text-stone-700 mb-1">Judul Utama Undangan</label>
                                <input
                                    v-model="designData.content.weddingTitle"
                                    @input="triggerAutosave"
                                    type="text"
                                    placeholder="Contoh: Sandi & Dinnda"
                                    class="w-full p-2.5 border rounded-xl bg-stone-50 font-serif font-bold text-stone-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-rose-400"
                                />
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-stone-700 mb-1">Subtitle / Jenis Acara</label>
                                <input
                                    v-model="designData.content.coverSubtitle"
                                    @input="triggerAutosave"
                                    type="text"
                                    placeholder="Contoh: Walimatul 'Ursy / The Wedding Celebration"
                                    class="w-full p-2.5 border rounded-xl bg-stone-50 text-stone-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-rose-400"
                                />
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-stone-700 mb-1">Pilih Tanggal Pernikahan (Kalender)</label>
                                <input
                                    type="date"
                                    v-model="weddingDateOnly"
                                    @change="onWeddingDateOnlyChange"
                                    class="w-full p-2.5 border rounded-xl bg-white text-stone-900 font-semibold focus:outline-none focus:ring-2 focus:ring-rose-400 text-xs cursor-pointer shadow-xs"
                                />
                                <div class="text-[10px] text-stone-500 mt-1.5 flex items-center gap-1">
                                    <span>📅 Format tampil:</span>
                                    <strong class="text-stone-800">{{ designData.content.weddingDateText }}</strong>
                                </div>
                            </div>
                        </div>

                        <!-- Profil Mempelai Pria -->
                        <div class="space-y-2 border-b pb-4 border-stone-200">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-bold text-stone-400 uppercase tracking-wider">🤵 Mempelai Pria</span>
                                <span class="text-[10px] text-stone-500 font-mono">Groom</span>
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-stone-700 mb-1">Nama Panggilan (Inisial Segel Wax Seal)</label>
                                <input
                                    v-model="designData.content.groom.nickname"
                                    @input="triggerAutosave"
                                    type="text"
                                    placeholder="Contoh: Sandi"
                                    class="w-full p-2 border rounded-lg bg-stone-50 text-stone-800 focus:bg-white"
                                />
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-stone-700 mb-1">Nama Lengkap & Gelar</label>
                                <input
                                    v-model="designData.content.groom.fullName"
                                    @input="triggerAutosave"
                                    type="text"
                                    placeholder="Contoh: Sandi Riady, S.Kom"
                                    class="w-full p-2 border rounded-lg bg-stone-50 font-bold text-stone-900 focus:bg-white"
                                />
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-stone-700 mb-1">Nama Orang Tua (Ayah & Ibu)</label>
                                <textarea
                                    v-model="designData.content.groom.parents"
                                    @input="triggerAutosave"
                                    rows="2"
                                    placeholder="Putra pertama dari Bpk. ... & Ibu ..."
                                    class="w-full p-2 border rounded-lg bg-stone-50 text-stone-800 focus:bg-white"
                                ></textarea>
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-stone-700 mb-1">Username Instagram (Opsional)</label>
                                <input
                                    v-model="designData.content.groom.instagram"
                                    @input="triggerAutosave"
                                    type="text"
                                    placeholder="@sandiriady"
                                    class="w-full p-2 border rounded-lg bg-stone-50 text-stone-800 focus:bg-white"
                                />
                            </div>
                        </div>

                        <!-- Profil Mempelai Wanita -->
                        <div class="space-y-2 border-b pb-4 border-stone-200">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-bold text-stone-400 uppercase tracking-wider">👰 Mempelai Wanita</span>
                                <span class="text-[10px] text-stone-500 font-mono">Bride</span>
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-stone-700 mb-1">Nama Panggilan (Inisial Segel Wax Seal)</label>
                                <input
                                    v-model="designData.content.bride.nickname"
                                    @input="triggerAutosave"
                                    type="text"
                                    placeholder="Contoh: Dinnda"
                                    class="w-full p-2 border rounded-lg bg-stone-50 text-stone-800 focus:bg-white"
                                />
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-stone-700 mb-1">Nama Lengkap & Gelar</label>
                                <input
                                    v-model="designData.content.bride.fullName"
                                    @input="triggerAutosave"
                                    type="text"
                                    placeholder="Contoh: Dinnda Ochtaffia, S.E"
                                    class="w-full p-2 border rounded-lg bg-stone-50 font-bold text-stone-900 focus:bg-white"
                                />
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-stone-700 mb-1">Nama Orang Tua (Ayah & Ibu)</label>
                                <textarea
                                    v-model="designData.content.bride.parents"
                                    @input="triggerAutosave"
                                    rows="2"
                                    placeholder="Putri kedua dari Bpk. ... & Ibu ..."
                                    class="w-full p-2 border rounded-lg bg-stone-50 text-stone-800 focus:bg-white"
                                ></textarea>
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-stone-700 mb-1">Username Instagram (Opsional)</label>
                                <input
                                    v-model="designData.content.bride.instagram"
                                    @input="triggerAutosave"
                                    type="text"
                                    placeholder="@dinndaochtaffia"
                                    class="w-full p-2 border rounded-lg bg-stone-50 text-stone-800 focus:bg-white"
                                />
                            </div>
                        </div>

                        <!-- Kutipan Ayat & Doa -->
                        <div class="space-y-2">
                            <div class="text-[10px] font-bold text-stone-400 uppercase tracking-wider">Kutipan Ayat / Doa Restu</div>
                            <div>
                                <label class="block text-[11px] font-semibold text-stone-700 mb-1">Teks Ayat / Kata Mutiara</label>
                                <textarea
                                    v-model="designData.content.quote"
                                    @input="triggerAutosave"
                                    rows="3"
                                    class="w-full p-2 border rounded-lg bg-stone-50 text-stone-800 focus:bg-white italic"
                                ></textarea>
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-stone-700 mb-1">Sumber Ayat (Misal: QS. Ar-Rum: 21)</label>
                                <input
                                    v-model="designData.content.quoteSource"
                                    @input="triggerAutosave"
                                    type="text"
                                    class="w-full p-2 border rounded-lg bg-stone-50 text-stone-800 focus:bg-white"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- ============================================== -->
                    <!-- TAB 2: WARNA, PALET & TEMA                      -->
                    <!-- ============================================== -->
                    <div v-if="activeTab === 'theme'" class="space-y-4">
                        <div class="p-3 bg-stone-50 rounded-xl border border-stone-200 text-stone-600 text-[11px] leading-relaxed">
                            🎨 Klik salah satu preset palet warna di bawah untuk mengubah nuansa tema undangan secara menyeluruh.
                        </div>

                        <!-- Preset Palettes -->
                        <div>
                            <div class="text-[10px] font-bold text-stone-400 uppercase tracking-wider mb-2">Preset Palet Warna Mewah</div>
                            <div class="grid grid-cols-2 gap-2">
                                <button
                                    v-for="p in colorPalettes"
                                    :key="p.id"
                                    type="button"
                                    @click="applyPalette(p)"
                                    :class="['p-2.5 rounded-xl border text-left flex items-center gap-2.5 transition-all', designData.theme.palette === p.id ? 'border-rose-500 bg-rose-50/50 shadow-xs ring-1 ring-rose-400' : 'border-stone-200 hover:border-stone-300 bg-white']"
                                >
                                    <div class="w-5 h-5 rounded-full shrink-0 shadow-inner flex items-center justify-center border border-white" :style="{ backgroundColor: p.primary }">
                                        <div class="w-1.5 h-1.5 rounded-full" :style="{ backgroundColor: p.secondary }"></div>
                                    </div>
                                    <span class="font-semibold text-stone-800 text-[11px] truncate">{{ p.name }}</span>
                                </button>
                            </div>
                        </div>

                        <!-- Custom Color Pickers -->
                        <div class="space-y-3 pt-2 border-t border-stone-200">
                            <div class="text-[10px] font-bold text-stone-400 uppercase tracking-wider">Kustom Warna Hex</div>

                            <div>
                                <label class="block text-[11px] font-semibold text-stone-700 mb-1">Warna Aksen Utama (Emas / Rose / Emerald)</label>
                                <div class="flex items-center gap-2">
                                    <input
                                        type="color"
                                        v-model="designData.theme.primaryColor"
                                        @input="triggerAutosave"
                                        class="w-9 h-9 rounded-lg border p-0.5 cursor-pointer bg-white"
                                    />
                                    <input
                                        v-model="designData.theme.primaryColor"
                                        @input="triggerAutosave"
                                        type="text"
                                        class="flex-1 p-2 border rounded-lg bg-stone-50 font-mono text-xs uppercase"
                                    />
                                </div>
                            </div>

                            <div>
                                <label class="block text-[11px] font-semibold text-stone-700 mb-1">Warna Highlight & Teks Judul</label>
                                <div class="flex items-center gap-2">
                                    <input
                                        type="color"
                                        v-model="designData.theme.secondaryColor"
                                        @input="triggerAutosave"
                                        class="w-9 h-9 rounded-lg border p-0.5 cursor-pointer bg-white"
                                    />
                                    <input
                                        v-model="designData.theme.secondaryColor"
                                        @input="triggerAutosave"
                                        type="text"
                                        class="flex-1 p-2 border rounded-lg bg-stone-50 font-mono text-xs uppercase"
                                    />
                                </div>
                            </div>

                            <div>
                                <label class="block text-[11px] font-semibold text-stone-700 mb-1">Warna Background Undangan</label>
                                <div class="flex items-center gap-2">
                                    <input
                                        type="color"
                                        v-model="designData.theme.bgColor"
                                        @input="triggerAutosave"
                                        class="w-9 h-9 rounded-lg border p-0.5 cursor-pointer bg-white"
                                    />
                                    <input
                                        v-model="designData.theme.bgColor"
                                        @input="triggerAutosave"
                                        type="text"
                                        class="flex-1 p-2 border rounded-lg bg-stone-50 font-mono text-xs uppercase"
                                    />
                                </div>
                            </div>

                            <div>
                                <label class="block text-[11px] font-semibold text-stone-700 mb-1">Warna Background Kartu & Section</label>
                                <div class="flex items-center gap-2">
                                    <input
                                        type="color"
                                        v-model="designData.theme.cardBgColor"
                                        @input="triggerAutosave"
                                        class="w-9 h-9 rounded-lg border p-0.5 cursor-pointer bg-white"
                                    />
                                    <input
                                        v-model="designData.theme.cardBgColor"
                                        @input="triggerAutosave"
                                        type="text"
                                        class="flex-1 p-2 border rounded-lg bg-stone-50 font-mono text-xs uppercase"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Font Family Selection -->
                        <div class="space-y-2 pt-2 border-t border-stone-200">
                            <div class="text-[10px] font-bold text-stone-400 uppercase tracking-wider">Pilihan Tipografi & Font</div>
                            <div>
                                <select
                                    v-model="designData.theme.fontFamily"
                                    @change="triggerAutosave"
                                    class="w-full p-2.5 border rounded-xl bg-stone-50 text-xs font-medium"
                                >
                                    <option v-for="f in fontOptions" :key="f.value" :value="f.value">
                                        {{ f.name }}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- ============================================== -->
                    <!-- TAB 3: FOTO & GALERI                           -->
                    <!-- ============================================== -->
                    <div v-if="activeTab === 'media'" class="space-y-4">
                        <div v-if="uploadError" class="p-2.5 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-xl">
                            {{ uploadError }}
                        </div>
                        <div v-if="isUploading" class="p-2.5 bg-amber-50 border border-amber-200 text-amber-800 text-xs rounded-xl animate-pulse">
                            ⏳ Sedang mengunggah file...
                        </div>

                        <!-- Foto Cover Hero -->
                        <div class="p-3 bg-stone-50 border border-stone-200 rounded-xl space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-stone-800 text-[11px]">Foto Cover Utama (Hero)</span>
                                <button
                                    type="button"
                                    @click="triggerUploadFor('cover')"
                                    class="text-[10px] text-rose-600 hover:underline font-bold"
                                >
                                    📁 Upload Foto
                                </button>
                            </div>
                            <div class="flex gap-3 items-center">
                                <div class="w-14 h-16 rounded-lg overflow-hidden border bg-stone-200 shrink-0">
                                    <img :src="designData.content.coverImage" class="w-full h-full object-cover" />
                                </div>
                                <input
                                    v-model="designData.content.coverImage"
                                    @input="triggerAutosave"
                                    type="text"
                                    placeholder="https://..."
                                    class="flex-1 p-2 border rounded-lg bg-white text-xs"
                                />
                            </div>
                        </div>

                        <!-- Foto Mempelai Pria & Wanita -->
                        <div class="p-3 bg-stone-50 border border-stone-200 rounded-xl space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-stone-800 text-[11px]">Foto Mempelai Pria</span>
                                <button
                                    type="button"
                                    @click="triggerUploadFor('groom')"
                                    class="text-[10px] text-rose-600 hover:underline font-bold"
                                >
                                    📁 Upload Foto
                                </button>
                            </div>
                            <div class="flex gap-3 items-center">
                                <div class="w-12 h-12 rounded-full overflow-hidden border bg-stone-200 shrink-0">
                                    <img :src="designData.content.groom.photo" class="w-full h-full object-cover" />
                                </div>
                                <input
                                    v-model="designData.content.groom.photo"
                                    @input="triggerAutosave"
                                    type="text"
                                    placeholder="https://..."
                                    class="flex-1 p-2 border rounded-lg bg-white text-xs"
                                />
                            </div>
                        </div>

                        <div class="p-3 bg-stone-50 border border-stone-200 rounded-xl space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-stone-800 text-[11px]">Foto Mempelai Wanita</span>
                                <button
                                    type="button"
                                    @click="triggerUploadFor('bride')"
                                    class="text-[10px] text-rose-600 hover:underline font-bold"
                                >
                                    📁 Upload Foto
                                </button>
                            </div>
                            <div class="flex gap-3 items-center">
                                <div class="w-12 h-12 rounded-full overflow-hidden border bg-stone-200 shrink-0">
                                    <img :src="designData.content.bride.photo" class="w-full h-full object-cover" />
                                </div>
                                <input
                                    v-model="designData.content.bride.photo"
                                    @input="triggerAutosave"
                                    type="text"
                                    placeholder="https://..."
                                    class="flex-1 p-2 border rounded-lg bg-white text-xs"
                                />
                            </div>
                        </div>

                        <!-- 4 Foto Galeri Kenangan -->
                        <div class="space-y-2 border-t pt-3 border-stone-200">
                            <div class="text-[10px] font-bold text-stone-400 uppercase tracking-wider">4 Foto Galeri Kenangan</div>
                            <div class="space-y-2">
                                <div
                                    v-for="(photoUrl, idx) in designData.content.gallery"
                                    :key="idx"
                                    class="p-2.5 bg-stone-50 border border-stone-200 rounded-xl space-y-1.5"
                                >
                                    <div class="flex items-center justify-between text-[10px]">
                                        <span class="font-bold text-stone-700">Foto Galeri #{{ idx + 1 }}</span>
                                        <button
                                            type="button"
                                            @click="triggerUploadFor('gallery_' + idx)"
                                            class="text-rose-600 font-bold hover:underline"
                                        >
                                            📁 Ganti File
                                        </button>
                                    </div>
                                    <div class="flex gap-2 items-center">
                                        <div class="w-10 h-10 rounded-lg overflow-hidden border bg-stone-200 shrink-0">
                                            <img :src="photoUrl" class="w-full h-full object-cover" />
                                        </div>
                                        <input
                                            v-model="designData.content.gallery[idx]"
                                            @input="triggerAutosave"
                                            type="text"
                                            class="flex-1 p-1.5 border rounded-lg bg-white text-xs"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ============================================== -->
                    <!-- TAB 4: JADWAL & AGENDA ACARA                   -->
                    <!-- ============================================== -->
                    <div v-if="activeTab === 'events'" class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold text-stone-400 uppercase tracking-wider">Rangkaian Acara</span>
                            <button
                                type="button"
                                @click="addEvent"
                                class="px-2.5 py-1 bg-rose-50 hover:bg-rose-100 text-rose-600 text-xs font-bold rounded-lg border border-rose-200 transition-colors"
                            >
                                + Tambah Acara
                            </button>
                        </div>

                        <div v-for="(ev, idx) in eventsList" :key="ev.id" class="p-3 bg-stone-50 border border-stone-200 rounded-xl space-y-2.5 relative">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-stone-800 text-[11px]">Acara #{{ idx + 1 }}</span>
                                <button
                                    v-if="eventsList.length > 1"
                                    type="button"
                                    @click="removeEvent(idx)"
                                    class="text-stone-400 hover:text-rose-600 text-xs font-bold"
                                    title="Hapus Acara"
                                >
                                    ✕ Hapus
                                </button>
                            </div>

                            <div>
                                <label class="block text-[10px] font-semibold text-stone-600 mb-0.5">Nama Acara</label>
                                <input
                                    v-model="ev.name"
                                    @input="triggerAutosave"
                                    type="text"
                                    placeholder="Akad Nikah / Resepsi"
                                    class="w-full text-xs p-1.5 border rounded-lg bg-white font-medium"
                                />
                            </div>

                            <div>
                                <label class="block text-[10px] font-semibold text-stone-600 mb-0.5">Waktu Mulai Acara (Pilih Kalender & Jam)</label>
                                <input
                                    type="datetime-local"
                                    v-model="ev.starts_at"
                                    @change="triggerAutosave"
                                    class="w-full text-xs p-2 border rounded-lg bg-white font-medium cursor-pointer shadow-xs"
                                />
                            </div>

                            <div>
                                <label class="block text-[10px] font-semibold text-stone-600 mb-0.5">Waktu Selesai Acara (Opsional)</label>
                                <input
                                    type="datetime-local"
                                    v-model="ev.ends_at"
                                    @change="triggerAutosave"
                                    class="w-full text-xs p-2 border rounded-lg bg-white font-medium cursor-pointer shadow-xs"
                                />
                            </div>

                            <div>
                                <label class="block text-[10px] font-semibold text-stone-600 mb-0.5">Nama Venue / Tempat</label>
                                <input
                                    v-model="ev.venue_name"
                                    @input="triggerAutosave"
                                    type="text"
                                    placeholder="Gedung / Masjid / Hotel"
                                    class="w-full text-xs p-1.5 border rounded-lg bg-white"
                                />
                            </div>

                            <div>
                                <label class="block text-[10px] font-semibold text-stone-600 mb-0.5">Alamat Lengkap</label>
                                <textarea
                                    v-model="ev.address"
                                    @input="triggerAutosave"
                                    rows="2"
                                    placeholder="Jl. Melati No. 12..."
                                    class="w-full text-xs p-1.5 border rounded-lg bg-white"
                                ></textarea>
                            </div>

                            <div>
                                <label class="block text-[10px] font-semibold text-stone-600 mb-0.5">Link Google Maps (URL)</label>
                                <input
                                    v-model="ev.maps_url"
                                    @input="triggerAutosave"
                                    type="text"
                                    placeholder="https://maps.google.com/..."
                                    class="w-full text-xs p-1.5 border rounded-lg bg-white"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- ============================================== -->
                    <!-- TAB 5: FITUR MUSIK & AMPLOP DIGITAL             -->
                    <!-- ============================================== -->
                    <div v-if="activeTab === 'features'" class="space-y-4">
                        <!-- Musik Latar -->
                        <div class="space-y-2 border-b pb-4 border-stone-200">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-bold text-stone-400 uppercase tracking-wider">Musik Latar (Vinyl Player)</span>
                                <button
                                    type="button"
                                    @click="triggerUploadFor('music')"
                                    class="text-[10px] text-rose-600 font-bold hover:underline flex items-center gap-1"
                                >
                                    <span>🎵</span>
                                    <span>Upload Lagu Sendiri</span>
                                </button>
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-stone-700 mb-1">URL File Audio (MP3 / OGG)</label>
                                <input
                                    v-model="designData.musicUrl"
                                    @input="triggerAutosave"
                                    type="text"
                                    placeholder="https://.../music.mp3"
                                    class="w-full p-2 border rounded-lg bg-stone-50 text-xs font-mono"
                                />
                            </div>

                            <!-- Pilihan Preset Musik Siap Pakai -->
                            <div class="space-y-1 pt-1">
                                <div class="text-[10px] font-medium text-stone-500">Atau pilih lagu pengiring siap pakai:</div>
                                <div class="grid grid-cols-1 gap-1.5">
                                    <button
                                        v-for="m in musicPresets"
                                        :key="m.url"
                                        type="button"
                                        @click="designData.musicUrl = m.url; syncToPreview(); triggerAutosave();"
                                        :class="['p-2 rounded-lg border text-left flex items-center justify-between transition-colors text-xs', designData.musicUrl === m.url ? 'border-rose-500 bg-rose-50 text-rose-700 font-bold' : 'border-stone-200 hover:bg-stone-50 text-stone-700']"
                                    >
                                        <span>🎼 {{ m.name }}</span>
                                        <span v-if="designData.musicUrl === m.url" class="text-xs">✓</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Amplop Digital & QRIS -->
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-bold text-stone-400 uppercase tracking-wider">🎁 Amplop Digital & QRIS</span>
                                <input
                                    v-model="designData.gift.enabled"
                                    @change="triggerAutosave"
                                    type="checkbox"
                                    class="rounded text-rose-600"
                                />
                            </div>

                            <div v-if="designData.gift.enabled" class="space-y-3">
                                <div>
                                    <label class="block text-[10px] font-semibold text-stone-600 mb-1">Judul Bagian</label>
                                    <input
                                        v-model="designData.gift.title"
                                        @input="triggerAutosave"
                                        type="text"
                                        class="w-full text-xs p-1.5 border rounded-lg bg-white"
                                    />
                                </div>

                                <div class="space-y-3">
                                    <div
                                        v-for="(acc, idx) in designData.gift.accounts"
                                        :key="idx"
                                        class="p-3 bg-stone-50 border border-stone-200 rounded-xl space-y-2 relative"
                                    >
                                        <button
                                            type="button"
                                            @click="removeBankAccount(idx)"
                                            class="absolute top-2 right-2 text-stone-400 hover:text-rose-600 text-xs font-bold"
                                        >
                                            ✕
                                        </button>
                                        <div>
                                            <label class="text-[9px] font-semibold text-stone-500">Nama Bank / E-Wallet</label>
                                            <input
                                                v-model="acc.bank"
                                                @input="triggerAutosave"
                                                type="text"
                                                placeholder="BCA / Mandiri / GoPay"
                                                class="w-full text-xs p-1.5 border rounded bg-white"
                                            />
                                        </div>
                                        <div>
                                            <label class="text-[9px] font-semibold text-stone-500">Nomor Rekening</label>
                                            <input
                                                v-model="acc.account_number"
                                                @input="triggerAutosave"
                                                type="text"
                                                placeholder="1234567890"
                                                class="w-full text-xs p-1.5 border rounded bg-white font-mono"
                                            />
                                        </div>
                                        <div>
                                            <label class="text-[9px] font-semibold text-stone-500">Atas Nama (a.n)</label>
                                            <input
                                                v-model="acc.account_name"
                                                @input="triggerAutosave"
                                                type="text"
                                                class="w-full text-xs p-1.5 border rounded bg-white"
                                            />
                                        </div>

                                        <!-- QRIS Barcode Image Upload & Preview -->
                                        <div class="pt-1 border-t border-stone-200">
                                            <div class="flex items-center justify-between mb-1">
                                                <label class="text-[9px] font-semibold text-stone-600">Gambar Barcode QRIS</label>
                                                <button
                                                    type="button"
                                                    @click="triggerUploadFor('qris_' + idx)"
                                                    class="text-[10px] text-rose-600 font-bold hover:underline flex items-center gap-1"
                                                >
                                                    <span>📁</span>
                                                    <span>Upload QRIS</span>
                                                </button>
                                            </div>
                                            <div class="flex gap-2 items-center">
                                                <div v-if="acc.qr_image" class="w-10 h-10 rounded-lg border bg-white p-0.5 shrink-0 flex items-center justify-center shadow-xs">
                                                    <img :src="acc.qr_image" class="w-full h-full object-contain" />
                                                </div>
                                                <input
                                                    v-model="acc.qr_image"
                                                    @input="triggerAutosave"
                                                    type="text"
                                                    placeholder="URL barcode QRIS..."
                                                    class="flex-1 text-xs p-1.5 border rounded bg-white"
                                                />
                                            </div>
                                        </div>
                                    </div>

                                    <button
                                        type="button"
                                        @click="addBankAccount"
                                        class="w-full py-2 bg-stone-100 hover:bg-stone-200 text-stone-800 text-xs font-semibold rounded-xl border border-stone-200 transition-colors"
                                    >
                                        + Tambah Rekening Lain
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- Center: Interactive Smartphone Live Preview (Spacious, Perfectly Sized & Never Cut Off) -->
            <main class="flex-1 overflow-y-auto flex items-center justify-center p-2 sm:p-4 bg-stone-200/70 relative">
                <div class="flex flex-col items-center gap-2 max-h-[calc(100vh-80px)]">
                    <!-- Real Smartphone Bezel -->
                    <div class="relative w-[340px] sm:w-[370px] h-[640px] sm:h-[680px] max-h-[calc(100vh-120px)] bg-stone-900 rounded-[44px] p-3 shadow-2xl border-4 border-stone-800 flex flex-col overflow-hidden ring-1 ring-white/10 shrink-0">
                        <!-- Dynamic Island Notch -->
                        <div class="absolute top-3.5 left-1/2 -translate-x-1/2 w-24 h-3.5 bg-stone-950 rounded-full z-30 flex items-center justify-center shadow-inner">
                            <div class="w-2 h-2 rounded-full bg-stone-900 mr-2 border border-stone-800"></div>
                            <div class="w-8 h-1 bg-stone-800 rounded-full"></div>
                        </div>

                        <!-- Live Iframe View of Invitation -->
                        <div class="w-full h-full rounded-[34px] overflow-hidden bg-stone-950 relative">
                            <iframe
                                ref="previewIframeRef"
                                :key="previewKey"
                                :src="`/i/${invitation.slug}?preview=1`"
                                class="w-full h-full border-0 select-none"
                                @load="syncToPreview"
                            ></iframe>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 text-[11px] text-stone-600 font-medium">
                        <span>📱 Pratinjau Langsung HP</span>
                        <span>&bull;</span>
                        <span class="text-stone-500">Otomatis sinkron saat Anda mengetik</span>
                    </div>
                </div>
            </main>
        </div>

        <!-- Publish Modal -->
        <div v-if="showPublishModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-stone-200 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-stone-100">
                    <h3 class="font-serif font-bold text-lg text-stone-900">Publikasikan Undangan</h3>
                    <button @click="showPublishModal = false" class="text-stone-400 hover:text-stone-600">✕</button>
                </div>

                <div v-if="!publishedUrl">
                    <p class="text-xs text-stone-600 leading-relaxed mb-4">
                        Publikasi akan membuat versi baru yang dapat dilihat oleh seluruh tamu tanpa perlu login. Perubahan teks dan warna akan disinkronkan langsung ke link publik.
                    </p>
                    <div class="p-3 bg-stone-50 rounded-xl text-xs text-stone-500 mb-4">
                        <div><strong class="text-stone-700">Target URL:</strong> /i/{{ invitation.slug }}</div>
                    </div>
                    <div v-if="publishMessage" class="text-xs text-rose-600 mb-3 p-2 bg-rose-50 rounded">
                        {{ publishMessage }}
                    </div>
                    <div class="flex justify-end gap-2">
                        <button
                            @click="showPublishModal = false"
                            class="px-4 py-2 border rounded-xl text-xs font-semibold text-stone-600"
                        >
                            Batal
                        </button>
                        <button
                            @click="confirmPublish"
                            :disabled="isPublishing"
                            class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold transition-all disabled:opacity-50"
                        >
                            {{ isPublishing ? 'Menerbitkan...' : 'Ya, Terbitkan Sekarang' }}
                        </button>
                    </div>
                </div>

                <!-- After Published Success -->
                <div v-else class="text-center py-4 space-y-4">
                    <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-xl mx-auto font-bold">
                        ✓
                    </div>
                    <h4 class="font-bold text-stone-900 text-base">Selamat! Undangan Anda Sudah Terbit</h4>
                    <p class="text-xs text-stone-500">Link undangan digital Anda kini sudah aktif dan dapat dibagikan ke para tamu.</p>
                    
                    <div class="p-3 bg-stone-50 rounded-xl border border-stone-200 flex items-center justify-between text-xs">
                        <span class="font-mono text-stone-700 truncate mr-2">{{ publishedUrl }}</span>
                        <button
                            @click="copyUrl"
                            class="shrink-0 px-3 py-1 bg-stone-900 text-white rounded-lg text-xs font-semibold hover:bg-stone-800"
                        >
                            Salin Link
                        </button>
                    </div>

                    <div class="pt-2 flex justify-center gap-3">
                        <a
                            :href="publishedUrl"
                            target="_blank"
                            class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold"
                        >
                            Buka Undangan ↗
                        </a>
                        <button
                            @click="showPublishModal = false"
                            class="px-4 py-2 border rounded-xl text-xs font-semibold text-stone-600"
                        >
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Global Toasts -->
        <ToastContainer />
    </div>
</template>

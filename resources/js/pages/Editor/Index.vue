<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount, reactive } from 'vue';
import { Link } from '@inertiajs/vue3';
import * as fabric from 'fabric';

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
const canvasEl = ref<HTMLCanvasElement | null>(null);
let canvas: fabric.Canvas | null = null;

const saveStatus = ref<'idle' | 'saving' | 'saved' | 'error'>('saved');
const lastSavedTime = ref<string>('Baru saja');
const previewMode = ref<'mobile' | 'canvas'>('mobile');
const previewKey = ref<number>(0);
const zoomLevel = ref<number>(1);
const activeTab = ref<'couple' | 'theme' | 'media' | 'events' | 'features' | 'canvas'>('couple');

const refreshPreview = () => {
    previewKey.value++;
};

// Undo / Redo History stack
const historyStack = ref<string[]>([]);
const historyIndex = ref<number>(-1);
let isHistoryUpdating = false;

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

// Apply Color Palette Preset
const applyPalette = (palette: typeof colorPalettes[0]) => {
    designData.theme.palette = palette.id;
    designData.theme.primaryColor = palette.primary;
    designData.theme.secondaryColor = palette.secondary;
    designData.theme.bgColor = palette.bg;
    designData.theme.cardBgColor = palette.card;
    designData.canvas.background = palette.bg;
    designData.canvas.primaryColor = palette.primary;
    triggerAutosave();
};

// Events in invitation
const eventsList = ref<any[]>(props.invitation.events || []);

const addEvent = () => {
    eventsList.value.push({
        id: Date.now(),
        name: 'Resepsi Pernikahan',
        starts_at: '2026-12-28 11:00:00',
        ends_at: '2026-12-28 14:00:00',
        timezone: 'WIB',
        venue_name: 'Grand Ballroom Hotel Arya',
        address: 'Jl. Sudirman No. 123, Jakarta Pusat',
        maps_url: 'https://maps.google.com',
    });
    triggerAutosave();
};

const removeEvent = (index: number) => {
    eventsList.value.splice(index, 1);
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
    triggerAutosave();
};

const removeBankAccount = (idx: number) => {
    designData.gift.accounts.splice(idx, 1);
    triggerAutosave();
};

// Upload Photo Handler
const isUploading = ref(false);
const uploadError = ref('');
const targetUploadField = ref<string>(''); // 'cover' | 'groom' | 'bride' | 'gallery_0' .. 'gallery_3'
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
    formData.append('type', targetUploadField.value.startsWith('gallery') ? 'gallery' : targetUploadField.value);

    try {
        const res = await fetch(`/api/invitations/${props.invitation.id}/upload`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as any)?.content || '',
                'Accept': 'application/json',
            },
            body: formData,
        });

        const data = await res.json();
        if (res.ok && data.success && data.url) {
            const uploadedUrl = data.url;
            if (targetUploadField.value === 'cover') {
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
            }
            triggerAutosave();
        } else {
            uploadError.value = data.message || 'Gagal mengunggah foto.';
        }
    } catch (e: any) {
        uploadError.value = 'Terjadi kesalahan saat mengunggah foto.';
    } finally {
        isUploading.value = false;
        if (event.target) event.target.value = '';
    }
};

// Selected Element Properties in Canvas
const selectedObj = ref<any>(null);
const objProps = reactive({
    text: '',
    fontSize: 24,
    fill: '#D4AF37',
    fontFamily: 'Playfair Display',
    textAlign: 'center',
    opacity: 1,
});

// Publish Modal State
const showPublishModal = ref(false);
const isPublishing = ref(false);
const publishMessage = ref('');
const publishedUrl = ref('');

// Initialize Fabric.js Canvas
onMounted(() => {
    // Load initial design JSON if exists
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
        }
        if (loaded.canvas) Object.assign(designData.canvas, loaded.canvas);
        if (loaded.musicUrl) designData.musicUrl = loaded.musicUrl;
        if (loaded.gift) Object.assign(designData.gift, loaded.gift);
        if (loaded.sections) designData.sections = loaded.sections;
    }

    if (canvasEl.value) {
        canvas = new fabric.Canvas(canvasEl.value, {
            width: 380,
            height: 620,
            backgroundColor: designData.theme.bgColor || '#14171c',
            selection: true,
            preserveObjectStacking: true,
        });

        loadCanvasContent();

        canvas.on('selection:created', onObjectSelected);
        canvas.on('selection:updated', onObjectSelected);
        canvas.on('selection:cleared', () => {
            selectedObj.value = null;
        });

        canvas.on('object:modified', () => {
            saveHistoryState();
            triggerAutosave();
        });

        canvas.on('object:added', () => {
            if (!isHistoryUpdating) {
                saveHistoryState();
            }
        });

        saveHistoryState();
    }
});

onBeforeUnmount(() => {
    if (canvas) {
        canvas.dispose();
    }
});

const loadCanvasContent = () => {
    if (!canvas) return;

    const primaryColor = designData.theme.primaryColor || '#D4AF37';
    const textColor = designData.theme.secondaryColor || '#F3E5AB';

    const subTitle = new fabric.IText(designData.content.coverSubtitle || "WALIMATUL 'URSY", {
        left: 190,
        top: 100,
        fontSize: 12,
        fontFamily: designData.theme.fontFamily || 'Playfair Display',
        fill: primaryColor,
        originX: 'center',
    });

    const coupleNames = new fabric.IText(designData.content.weddingTitle || 'Dimas & Anisa', {
        left: 190,
        top: 135,
        fontSize: 32,
        fontFamily: designData.theme.fontFamily || 'Playfair Display',
        fontWeight: 'bold',
        fill: textColor,
        originX: 'center',
    });

    const dateText = new fabric.IText(designData.content.weddingDateText || 'Minggu, 28 Desember 2026', {
        left: 190,
        top: 185,
        fontSize: 12,
        fontFamily: designData.theme.fontFamily || 'Playfair Display',
        fill: primaryColor,
        originX: 'center',
    });

    canvas.add(subTitle);
    canvas.add(coupleNames);
    canvas.add(dateText);
    canvas.renderAll();
};

const onObjectSelected = (e: any) => {
    const obj = e.selected?.[0] || canvas?.getActiveObject();
    if (!obj) return;
    selectedObj.value = obj;

    if (obj.type === 'i-text' || obj.type === 'text') {
        objProps.text = obj.text || '';
        objProps.fontSize = obj.fontSize || 24;
        objProps.fill = (obj.fill as string) || '#D4AF37';
        objProps.fontFamily = obj.fontFamily || 'Playfair Display';
        objProps.textAlign = obj.textAlign || 'center';
        objProps.opacity = obj.opacity || 1;
    }
};

const updateTextProp = (prop: string, val: any) => {
    if (!selectedObj.value || !canvas) return;
    selectedObj.value.set(prop, val);
    canvas.renderAll();
    saveHistoryState();
    triggerAutosave();
};

const addText = (type: 'title' | 'names' | 'subtext') => {
    if (!canvas) return;
    let textObj: fabric.IText;

    if (type === 'title') {
        textObj = new fabric.IText('The Wedding Celebration', {
            left: 190,
            top: 250,
            fontSize: 14,
            fontFamily: designData.theme.fontFamily,
            fill: designData.theme.primaryColor,
            originX: 'center',
        });
    } else if (type === 'names') {
        textObj = new fabric.IText(designData.content.weddingTitle, {
            left: 190,
            top: 290,
            fontSize: 34,
            fontFamily: designData.theme.fontFamily,
            fontWeight: 'bold',
            fill: designData.theme.secondaryColor,
            originX: 'center',
        });
    } else {
        textObj = new fabric.IText('Doa restu Anda adalah kehormatan bagi kami.', {
            left: 190,
            top: 360,
            fontSize: 12,
            fontFamily: designData.theme.fontFamily,
            fill: '#d1d5db',
            originX: 'center',
        });
    }

    canvas.add(textObj);
    canvas.setActiveObject(textObj);
    canvas.renderAll();
    saveHistoryState();
    triggerAutosave();
};

const addOrnament = () => {
    if (!canvas) return;
    const textObj = new fabric.IText('♥', {
        left: 190,
        top: 220,
        fontSize: 26,
        fill: designData.theme.primaryColor,
        originX: 'center',
    });
    canvas.add(textObj);
    canvas.setActiveObject(textObj);
    canvas.renderAll();
    saveHistoryState();
    triggerAutosave();
};

const deleteSelected = () => {
    if (!canvas || !selectedObj.value) return;
    canvas.remove(selectedObj.value);
    selectedObj.value = null;
    canvas.renderAll();
    triggerAutosave();
};

const duplicateSelected = async () => {
    if (!canvas || !selectedObj.value) return;
    const cloned = await selectedObj.value.clone();
    cloned.set({
        left: (cloned.left || 0) + 20,
        top: (cloned.top || 0) + 20,
    });
    canvas.add(cloned);
    canvas.setActiveObject(cloned);
    canvas.renderAll();
    triggerAutosave();
};

// Undo / Redo
const saveHistoryState = () => {
    if (!canvas || isHistoryUpdating) return;
    const json = JSON.stringify(canvas.toJSON());
    if (historyIndex.value < historyStack.value.length - 1) {
        historyStack.value = historyStack.value.slice(0, historyIndex.value + 1);
    }
    historyStack.value.push(json);
    historyIndex.value = historyStack.value.length - 1;
};

const undo = async () => {
    if (historyIndex.value > 0 && canvas) {
        isHistoryUpdating = true;
        historyIndex.value--;
        const state = historyStack.value[historyIndex.value];
        await canvas.loadFromJSON(JSON.parse(state));
        canvas.renderAll();
        isHistoryUpdating = false;
        triggerAutosave();
    }
};

const redo = async () => {
    if (historyIndex.value < historyStack.value.length - 1 && canvas) {
        isHistoryUpdating = true;
        historyIndex.value++;
        const state = historyStack.value[historyIndex.value];
        await canvas.loadFromJSON(JSON.parse(state));
        canvas.renderAll();
        isHistoryUpdating = false;
        triggerAutosave();
    }
};

// Autosave with Debounce
let autosaveTimeout: any = null;
const triggerAutosave = () => {
    saveStatus.value = 'saving';
    clearTimeout(autosaveTimeout);
    autosaveTimeout = setTimeout(async () => {
        await executeSave();
    }, 1000);
};

const executeSave = async () => {
    try {
        saveStatus.value = 'saving';
        const canvasObjects = canvas ? canvas.toJSON() : null;

        const payload = {
            design: {
                schemaVersion: 1,
                theme: designData.theme,
                content: designData.content,
                canvas: designData.canvas,
                musicUrl: designData.musicUrl,
                gift: designData.gift,
                sections: designData.sections,
                canvasObjects: canvasObjects,
            },
            events: eventsList.value,
        };

        const res = await fetch(`/api/invitations/${props.invitation.id}/design`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as any)?.content || '',
                'Accept': 'application/json',
            },
            body: JSON.stringify(payload),
        });

        if (res.ok) {
            saveStatus.value = 'saved';
            const now = new Date();
            lastSavedTime.value = `${now.getHours().toString().padStart(2, '0')}:${now.getMinutes().toString().padStart(2, '0')}:${now.getSeconds().toString().padStart(2, '0')}`;
            // Refresh preview iframe to show new edits
            refreshPreview();
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
        const res = await fetch(`/api/invitations/${props.invitation.id}/publish`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as any)?.content || '',
                'Accept': 'application/json',
            },
        });

        const data = await res.json();
        if (res.ok && data.success) {
            publishedUrl.value = data.public_url;
            publishMessage.value = 'Undangan berhasil diterbitkan ke publik!';
        } else {
            publishMessage.value = data.message || 'Gagal menerbitkan undangan.';
        }
    } catch (e: any) {
        publishMessage.value = 'Terjadi kesalahan saat mempublikasikan.';
    } finally {
        isPublishing.value = false;
    }
};

const copyUrl = () => {
    navigator.clipboard.writeText(publishedUrl.value);
    alert('URL Undangan berhasil disalin!');
};
</script>

<template>
    <div class="h-screen flex flex-col bg-stone-100 overflow-hidden font-sans">
        <!-- Hidden file input for direct uploads -->
        <input
            ref="fileInputRef"
            type="file"
            accept="image/png,image/jpeg,image/webp,image/jpg"
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
                    <span class="font-serif font-bold text-stone-800 text-sm truncate max-w-[200px]">
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
                        class="text-[10px] text-rose-600 bg-rose-50 px-2 py-0.5 rounded-full font-medium"
                    >
                        Gagal Menyimpan
                    </span>
                </div>
            </div>

            <!-- Center Mode Switcher -->
            <div class="flex items-center gap-2">
                <div class="flex items-center bg-stone-100 rounded-xl p-0.5 border border-stone-200 text-xs">
                    <button
                        type="button"
                        @click="previewMode = 'mobile'"
                        :class="['px-3 py-1 rounded-lg font-semibold flex items-center gap-1.5 transition-all', previewMode === 'mobile' ? 'bg-white shadow-xs text-rose-600 font-bold' : 'text-stone-600 hover:text-stone-900']"
                    >
                        <span>📱</span>
                        <span>Preview Mobile Live</span>
                    </button>
                    <button
                        type="button"
                        @click="previewMode = 'canvas'"
                        :class="['px-3 py-1 rounded-lg font-semibold flex items-center gap-1.5 transition-all', previewMode === 'canvas' ? 'bg-white shadow-xs text-rose-600 font-bold' : 'text-stone-600 hover:text-stone-900']"
                    >
                        <span>🎨</span>
                        <span>Kanvas Cover</span>
                    </button>
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
            <aside class="w-80 bg-white border-r border-stone-200 flex flex-col shrink-0">
                <!-- Navigation Tabs -->
                <div class="grid grid-cols-6 border-b border-stone-200 text-[11px] font-semibold text-center bg-stone-50/50">
                    <button
                        @click="activeTab = 'couple'"
                        :class="['py-3 border-b-2 transition-colors flex flex-col items-center gap-1', activeTab === 'couple' ? 'border-rose-500 text-rose-600 font-bold bg-white' : 'border-transparent text-stone-500 hover:text-stone-800']"
                        title="Teks & Mempelai"
                    >
                        <span>✍️</span>
                        <span class="text-[9px]">Mempelai</span>
                    </button>
                    <button
                        @click="activeTab = 'theme'"
                        :class="['py-3 border-b-2 transition-colors flex flex-col items-center gap-1', activeTab === 'theme' ? 'border-rose-500 text-rose-600 font-bold bg-white' : 'border-transparent text-stone-500 hover:text-stone-800']"
                        title="Warna & Gaya"
                    >
                        <span>🎨</span>
                        <span class="text-[9px]">Warna</span>
                    </button>
                    <button
                        @click="activeTab = 'media'"
                        :class="['py-3 border-b-2 transition-colors flex flex-col items-center gap-1', activeTab === 'media' ? 'border-rose-500 text-rose-600 font-bold bg-white' : 'border-transparent text-stone-500 hover:text-stone-800']"
                        title="Foto & Galeri"
                    >
                        <span>📷</span>
                        <span class="text-[9px]">Foto</span>
                    </button>
                    <button
                        @click="activeTab = 'events'"
                        :class="['py-3 border-b-2 transition-colors flex flex-col items-center gap-1', activeTab === 'events' ? 'border-rose-500 text-rose-600 font-bold bg-white' : 'border-transparent text-stone-500 hover:text-stone-800']"
                        title="Jadwal Acara"
                    >
                        <span>📅</span>
                        <span class="text-[9px]">Acara</span>
                    </button>
                    <button
                        @click="activeTab = 'features'"
                        :class="['py-3 border-b-2 transition-colors flex flex-col items-center gap-1', activeTab === 'features' ? 'border-rose-500 text-rose-600 font-bold bg-white' : 'border-transparent text-stone-500 hover:text-stone-800']"
                        title="Musik & Kado"
                    >
                        <span>🎁</span>
                        <span class="text-[9px]">Fitur</span>
                    </button>
                    <button
                        @click="activeTab = 'canvas'"
                        :class="['py-3 border-b-2 transition-colors flex flex-col items-center gap-1', activeTab === 'canvas' ? 'border-rose-500 text-rose-600 font-bold bg-white' : 'border-transparent text-stone-500 hover:text-stone-800']"
                        title="Kanvas Cover"
                    >
                        <span>🖌️</span>
                        <span class="text-[9px]">Kanvas</span>
                    </button>
                </div>

                <!-- Tab Content Body (Scrollable) -->
                <div class="p-4 flex-1 overflow-y-auto space-y-4 text-xs">
                    <!-- ============================================== -->
                    <!-- TAB 1: TEKS & PROFIL MEMPELAI                  -->
                    <!-- ============================================== -->
                    <div v-if="activeTab === 'couple'" class="space-y-4">
                        <div class="p-3 bg-rose-50/50 rounded-xl border border-rose-100 text-stone-600 text-[11px] leading-relaxed">
                            💡 Ubah nama mempelai, orang tua, dan kutipan ayat. Perubahan otomatis tersimpan dan tampil langsung di pratinjau mobile.
                        </div>

                        <!-- Info Cover & Judul -->
                        <div class="space-y-2 border-b pb-4 border-stone-200">
                            <div class="text-[10px] font-bold text-stone-400 uppercase tracking-wider">Judul & Cover</div>
                            <div>
                                <label class="block text-[11px] font-semibold text-stone-700 mb-1">Judul Utama Undangan</label>
                                <input
                                    v-model="designData.content.weddingTitle"
                                    @input="triggerAutosave"
                                    type="text"
                                    placeholder="Contoh: Dimas & Anisa"
                                    class="w-full p-2 border rounded-lg bg-stone-50 font-serif font-bold text-stone-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-rose-400"
                                />
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-stone-700 mb-1">Subtitle / Jenis Acara</label>
                                <input
                                    v-model="designData.content.coverSubtitle"
                                    @input="triggerAutosave"
                                    type="text"
                                    placeholder="Contoh: Walimatul 'Ursy / The Wedding Celebration"
                                    class="w-full p-2 border rounded-lg bg-stone-50 text-stone-800 focus:bg-white"
                                />
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-stone-700 mb-1">Tanggal Pernikahan Teks</label>
                                <input
                                    v-model="designData.content.weddingDateText"
                                    @input="triggerAutosave"
                                    type="text"
                                    placeholder="Contoh: Minggu, 28 Desember 2026"
                                    class="w-full p-2 border rounded-lg bg-stone-50 text-stone-800 focus:bg-white"
                                />
                            </div>
                        </div>

                        <!-- Profil Mempelai Pria -->
                        <div class="space-y-2 border-b pb-4 border-stone-200">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-bold text-stone-400 uppercase tracking-wider">🤵 Mempelai Pria</span>
                                <span class="text-[10px] text-stone-500 font-mono">Groom</span>
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-stone-700 mb-1">Nama Panggilan (Monogram)</label>
                                <input
                                    v-model="designData.content.groom.nickname"
                                    @input="triggerAutosave"
                                    type="text"
                                    placeholder="Contoh: Dimas"
                                    class="w-full p-2 border rounded-lg bg-stone-50 text-stone-800 focus:bg-white"
                                />
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-stone-700 mb-1">Nama Lengkap & Gelar</label>
                                <input
                                    v-model="designData.content.groom.fullName"
                                    @input="triggerAutosave"
                                    type="text"
                                    placeholder="Contoh: Dimas Prasetyo, S.Kom"
                                    class="w-full p-2 border rounded-lg bg-stone-50 font-bold text-stone-900 focus:bg-white"
                                />
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-stone-700 mb-1">Nama Orang Tua (Ayah & Ibu)</label>
                                <textarea
                                    v-model="designData.content.groom.parents"
                                    @input="triggerAutosave"
                                    rows="2"
                                    placeholder="Putra pertama dari Bpk. Bambang & Ibu Sri"
                                    class="w-full p-2 border rounded-lg bg-stone-50 text-stone-800 focus:bg-white"
                                ></textarea>
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-stone-700 mb-1">Username Instagram (Opsional)</label>
                                <input
                                    v-model="designData.content.groom.instagram"
                                    @input="triggerAutosave"
                                    type="text"
                                    placeholder="@dimasprasetyo"
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
                                <label class="block text-[11px] font-semibold text-stone-700 mb-1">Nama Panggilan (Monogram)</label>
                                <input
                                    v-model="designData.content.bride.nickname"
                                    @input="triggerAutosave"
                                    type="text"
                                    placeholder="Contoh: Anisa"
                                    class="w-full p-2 border rounded-lg bg-stone-50 text-stone-800 focus:bg-white"
                                />
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-stone-700 mb-1">Nama Lengkap & Gelar</label>
                                <input
                                    v-model="designData.content.bride.fullName"
                                    @input="triggerAutosave"
                                    type="text"
                                    placeholder="Contoh: Anisa Rahmawati, S.E"
                                    class="w-full p-2 border rounded-lg bg-stone-50 font-bold text-stone-900 focus:bg-white"
                                />
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-stone-700 mb-1">Nama Orang Tua (Ayah & Ibu)</label>
                                <textarea
                                    v-model="designData.content.bride.parents"
                                    @input="triggerAutosave"
                                    rows="2"
                                    placeholder="Putri kedua dari Bpk. H. Rahmat & Ibu Siti"
                                    class="w-full p-2 border rounded-lg bg-stone-50 text-stone-800 focus:bg-white"
                                ></textarea>
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-stone-700 mb-1">Username Instagram (Opsional)</label>
                                <input
                                    v-model="designData.content.bride.instagram"
                                    @input="triggerAutosave"
                                    type="text"
                                    placeholder="@anisarahmawati"
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
                            🎨 Pilih salah satu preset palet warna di bawah, atau tentukan kode warna heksadesimal kustom Anda sendiri.
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
                            ⏳ Sedang mengunggah dan mengompres foto...
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
                                    class="w-full text-xs p-1.5 border rounded-lg bg-white"
                                />
                            </div>

                            <div>
                                <label class="block text-[10px] font-semibold text-stone-600 mb-0.5">Waktu Mulai</label>
                                <input
                                    v-model="ev.starts_at"
                                    @input="triggerAutosave"
                                    type="text"
                                    placeholder="2026-12-28 08:00:00"
                                    class="w-full text-xs p-1.5 border rounded-lg bg-white font-mono"
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
                            <div class="text-[10px] font-bold text-stone-400 uppercase tracking-wider">Musik Latar (Vinyl Player)</div>
                            <div>
                                <label class="block text-[11px] font-semibold text-stone-700 mb-1">URL Audio (MP3 / OGG)</label>
                                <input
                                    v-model="designData.musicUrl"
                                    @input="triggerAutosave"
                                    type="text"
                                    placeholder="https://.../music.mp3"
                                    class="w-full p-2 border rounded-lg bg-stone-50 text-xs"
                                />
                            </div>
                        </div>

                        <!-- Amplop Digital -->
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

                                <div class="space-y-2">
                                    <div
                                        v-for="(acc, idx) in designData.gift.accounts"
                                        :key="idx"
                                        class="p-2.5 bg-stone-50 border border-stone-200 rounded-xl space-y-2 relative"
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
                                                class="w-full text-xs p-1 border rounded bg-white"
                                            />
                                        </div>
                                        <div>
                                            <label class="text-[9px] font-semibold text-stone-500">Nomor Rekening</label>
                                            <input
                                                v-model="acc.account_number"
                                                @input="triggerAutosave"
                                                type="text"
                                                placeholder="1234567890"
                                                class="w-full text-xs p-1 border rounded bg-white font-mono"
                                            />
                                        </div>
                                        <div>
                                            <label class="text-[9px] font-semibold text-stone-500">Atas Nama (a.n)</label>
                                            <input
                                                v-model="acc.account_name"
                                                @input="triggerAutosave"
                                                type="text"
                                                class="w-full text-xs p-1 border rounded bg-white"
                                            />
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

                    <!-- ============================================== -->
                    <!-- TAB 6: KANVAS COVER FABRIC.JS                   -->
                    <!-- ============================================== -->
                    <div v-if="activeTab === 'canvas'" class="space-y-4">
                        <div class="text-[10px] font-bold text-stone-400 uppercase tracking-wider">Tambah Teks ke Kanvas</div>
                        <div class="grid grid-cols-1 gap-2">
                            <button
                                @click="addText('title')"
                                class="w-full text-left p-2.5 rounded-xl border border-stone-200 hover:border-rose-300 hover:bg-rose-50/50 text-xs font-bold text-stone-800 transition-colors"
                            >
                                + Judul Acara
                            </button>
                            <button
                                @click="addText('names')"
                                class="w-full text-left p-2.5 rounded-xl border border-stone-200 hover:border-rose-300 hover:bg-rose-50/50 text-xs font-bold text-stone-800 transition-colors"
                            >
                                + Nama Pasangan
                            </button>
                            <button
                                @click="addText('subtext')"
                                class="w-full text-left p-2.5 rounded-xl border border-stone-200 hover:border-rose-300 hover:bg-rose-50/50 text-xs text-stone-600 transition-colors"
                            >
                                + Paragraf / Doa
                            </button>
                            <button
                                @click="addOrnament"
                                class="w-full text-left p-2.5 rounded-xl border border-stone-200 hover:border-rose-300 hover:bg-rose-50/50 text-xs text-stone-700 transition-colors flex items-center gap-2"
                            >
                                <span class="text-rose-500">♥</span> Simbol Cinta (Heart)
                            </button>
                        </div>

                        <div class="flex items-center gap-2 pt-2 border-t border-stone-200">
                            <button
                                @click="undo"
                                class="flex-1 py-1.5 border rounded-lg text-xs font-semibold text-stone-600 hover:bg-stone-50"
                            >
                                ↺ Undo
                            </button>
                            <button
                                @click="redo"
                                class="flex-1 py-1.5 border rounded-lg text-xs font-semibold text-stone-600 hover:bg-stone-50"
                            >
                                ↻ Redo
                            </button>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- Center: Interactive Workspace (Live Mobile Preview & Canvas Editor) -->
            <main class="flex-1 overflow-auto flex items-center justify-center p-6 bg-stone-200/60 relative">
                <!-- MODE 1: Interactive Live Mobile Phone Mockup -->
                <div v-show="previewMode === 'mobile'" class="flex flex-col items-center gap-3">
                    <div class="relative w-[380px] h-[720px] bg-stone-900 rounded-[50px] p-3 shadow-2xl border-4 border-stone-800 flex flex-col overflow-hidden ring-1 ring-white/10">
                        <!-- Dynamic Island Notch -->
                        <div class="absolute top-4 left-1/2 -translate-x-1/2 w-28 h-4 bg-stone-950 rounded-full z-30 flex items-center justify-center shadow-inner">
                            <div class="w-2.5 h-2.5 rounded-full bg-stone-900 mr-2 border border-stone-800"></div>
                            <div class="w-10 h-1 bg-stone-800 rounded-full"></div>
                        </div>

                        <!-- Live Iframe View of Invitation -->
                        <div class="w-full h-full rounded-[40px] overflow-hidden bg-stone-950 relative">
                            <iframe
                                :key="previewKey"
                                :src="`/i/${invitation.slug}?preview=1`"
                                class="w-full h-full border-0 select-none"
                            ></iframe>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 text-xs text-stone-500 font-medium">
                        <span>📱 Pratinjau Tampilan Undangan Asli</span>
                        <span>&bull;</span>
                        <button
                            type="button"
                            @click="refreshPreview"
                            class="text-rose-600 hover:text-rose-700 font-bold underline flex items-center gap-1"
                        >
                            <span>🔄 Segarkan Tampilan</span>
                        </button>
                    </div>
                </div>

                <!-- MODE 2: Fabric Canvas Cover Designer -->
                <div v-show="previewMode === 'canvas'" class="flex flex-col items-center gap-3">
                    <div class="relative bg-white shadow-2xl rounded-2xl overflow-hidden border border-stone-300">
                        <canvas ref="canvasEl"></canvas>
                    </div>
                    <div class="text-xs text-stone-500 font-medium">
                        🎨 Kanvas Desain Cover &bull; Klik teks di atas untuk mengedit di panel kanan
                    </div>
                </div>
            </main>

            <!-- Right Sidebar: Properties Inspector (Canvas Object) -->
            <aside class="w-64 bg-white border-l border-stone-200 p-4 flex flex-col shrink-0 overflow-y-auto">
                <div class="text-xs font-bold text-stone-800 uppercase tracking-wider mb-4 pb-2 border-b border-stone-100">
                    Inspektor Properti
                </div>

                <div v-if="selectedObj" class="space-y-4 text-xs">
                    <!-- Typography settings -->
                    <div v-if="selectedObj.type === 'i-text' || selectedObj.type === 'text'" class="space-y-3">
                        <div>
                            <label class="block text-[11px] font-semibold text-stone-600 mb-1">Font Family</label>
                            <select
                                v-model="objProps.fontFamily"
                                @change="updateTextProp('fontFamily', objProps.fontFamily)"
                                class="w-full p-2 border rounded-xl bg-stone-50 text-xs"
                            >
                                <option v-for="f in fontOptions" :key="f.value" :value="f.value">
                                    {{ f.name }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-stone-600 mb-1">Ukuran Font ({{ objProps.fontSize }}px)</label>
                            <input
                                type="range"
                                min="10"
                                max="100"
                                v-model.number="objProps.fontSize"
                                @input="updateTextProp('fontSize', objProps.fontSize)"
                                class="w-full"
                            />
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-stone-600 mb-1">Warna Teks</label>
                            <div class="flex gap-2 items-center">
                                <input
                                    type="color"
                                    v-model="objProps.fill"
                                    @input="updateTextProp('fill', objProps.fill)"
                                    class="w-8 h-8 rounded border p-0 cursor-pointer"
                                />
                                <span class="font-mono text-stone-600 text-xs">{{ objProps.fill }}</span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-stone-600 mb-1">Perataan Teks</label>
                            <div class="grid grid-cols-3 gap-1">
                                <button
                                    @click="updateTextProp('textAlign', 'left')"
                                    :class="['py-1 border rounded text-xs', objProps.textAlign === 'left' ? 'bg-stone-900 text-white' : 'bg-stone-50']"
                                >
                                    Kiri
                                </button>
                                <button
                                    @click="updateTextProp('textAlign', 'center')"
                                    :class="['py-1 border rounded text-xs', objProps.textAlign === 'center' ? 'bg-stone-900 text-white' : 'bg-stone-50']"
                                >
                                    Tengah
                                </button>
                                <button
                                    @click="updateTextProp('textAlign', 'right')"
                                    :class="['py-1 border rounded text-xs', objProps.textAlign === 'right' ? 'bg-stone-900 text-white' : 'bg-stone-50']"
                                >
                                    Kanan
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Layer & Object Actions -->
                    <div class="pt-4 border-t border-stone-100 space-y-2">
                        <div class="text-[11px] font-bold text-stone-400 uppercase tracking-wider mb-2">Aksi Objek</div>
                        <div class="grid grid-cols-2 gap-2">
                            <button
                                @click="duplicateSelected"
                                class="p-2 border border-stone-200 rounded-lg hover:bg-stone-50 text-[11px] font-medium"
                            >
                                Duplikasi ⧉
                            </button>
                            <button
                                @click="deleteSelected"
                                class="p-2 border border-rose-200 text-rose-600 rounded-lg hover:bg-rose-50 text-[11px] font-medium"
                            >
                                Hapus ✕
                            </button>
                        </div>
                    </div>
                </div>

                <div v-else class="text-center py-12 text-stone-400 text-xs leading-relaxed">
                    Pilih tab di sebelah kiri untuk mengedit mempelai, warna tema, foto, acara, atau fitur.
                </div>
            </aside>
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
    </div>
</template>

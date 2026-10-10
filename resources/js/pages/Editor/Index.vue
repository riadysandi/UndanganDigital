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
const previewMode = ref<'canvas' | 'mobile'>('canvas');
const zoomLevel = ref<number>(1);
const activeTab = ref<'elements' | 'sections' | 'events'>('elements');

// Undo / Redo History stack
const historyStack = ref<string[]>([]);
const historyIndex = ref<number>(-1);
let isHistoryUpdating = false;

// Sections state
const designData = reactive({
    schemaVersion: 1,
    canvas: {
        width: 480,
        height: 720,
        background: '#FDFBF7',
        primaryColor: '#5D6B50',
        fontFamily: 'Playfair Display',
    },
    sections: [
        { id: 'cover', type: 'cover', title: 'Cover Utama', visible: true },
        { id: 'couple', type: 'couple', title: 'Mempelai Pengantin', visible: true },
        { id: 'events', type: 'events', title: 'Agenda Acara (Akad & Resepsi)', visible: true },
        { id: 'gallery', type: 'gallery', title: 'Galeri Foto', visible: true },
        { id: 'rsvp', type: 'rsvp', title: 'Konfirmasi RSVP & Doa Restu', visible: true },
    ],
});

// Selected Element Properties in Canvas
const selectedObj = ref<any>(null);
const objProps = reactive({
    text: '',
    fontSize: 24,
    fill: '#333333',
    fontFamily: 'Playfair Display',
    textAlign: 'center',
    opacity: 1,
});

// Events in invitation
const eventsList = ref<any[]>(props.invitation.events || []);

// Publish Modal State
const showPublishModal = ref(false);
const isPublishing = ref(false);
const publishMessage = ref('');
const publishedUrl = ref('');

// Initialize Fabric.js Canvas
onMounted(() => {
    if (!canvasEl.value) return;

    // Load initial design JSON if exists
    if (props.invitation.design?.design_json) {
        Object.assign(designData, props.invitation.design.design_json);
    }

    canvas = new fabric.Canvas(canvasEl.value, {
        width: designData.canvas.width || 480,
        height: designData.canvas.height || 720,
        backgroundColor: designData.canvas.background || '#FDFBF7',
        selection: true,
        preserveObjectStacking: true,
    });

    // Load initial objects into canvas
    loadCanvasContent();

    // Event listeners
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

    // Save initial history
    saveHistoryState();
});

onBeforeUnmount(() => {
    if (canvas) {
        canvas.dispose();
    }
});

const loadCanvasContent = () => {
    if (!canvas) return;

    // Default decorative elements if empty
    const titleText = new fabric.IText('The Wedding Of', {
        left: 240,
        top: 120,
        originX: 'center',
        originY: 'center',
        fontFamily: 'Playfair Display',
        fontSize: 24,
        fill: '#7D8C71',
    });

    const coupleText = new fabric.IText('Dimas & Anisa', {
        left: 240,
        top: 220,
        originX: 'center',
        originY: 'center',
        fontFamily: 'Great Vibes',
        fontSize: 54,
        fill: '#2C3E50',
    });

    const dateText = new fabric.IText('Minggu, 28 Desember 2026', {
        left: 240,
        top: 310,
        originX: 'center',
        originY: 'center',
        fontFamily: 'Playfair Display',
        fontSize: 16,
        fill: '#666666',
    });

    // Decorative shape (line)
    const line = new fabric.Line([140, 260, 340, 260], {
        stroke: '#D4AF37',
        strokeWidth: 2,
    });

    canvas.add(titleText);
    canvas.add(coupleText);
    canvas.add(line);
    canvas.add(dateText);
    canvas.renderAll();
};

const onObjectSelected = (e: any) => {
    const active = canvas?.getActiveObject();
    if (active) {
        selectedObj.value = active;
        if (active.type === 'i-text' || active.type === 'text') {
            objProps.text = (active as any).text || '';
            objProps.fontSize = (active as any).fontSize || 20;
            objProps.fill = (active as any).fill || '#333333';
            objProps.fontFamily = (active as any).fontFamily || 'Playfair Display';
            objProps.textAlign = (active as any).textAlign || 'center';
            objProps.opacity = active.opacity ?? 1;
        }
    }
};

const updateTextProp = (prop: string, val: any) => {
    if (!selectedObj.value || !canvas) return;
    selectedObj.value.set(prop, val);
    canvas.renderAll();
    triggerAutosave();
};

// Add Element to Canvas
const addText = (preset: 'title' | 'names' | 'subtext') => {
    if (!canvas) return;

    let textObj: fabric.IText;
    if (preset === 'title') {
        textObj = new fabric.IText('Judul Acara', {
            left: 240,
            top: 150,
            originX: 'center',
            fontSize: 28,
            fontFamily: 'Playfair Display',
            fill: '#5D6B50',
        });
    } else if (preset === 'names') {
        textObj = new fabric.IText('Nama Mempelai', {
            left: 240,
            top: 250,
            originX: 'center',
            fontSize: 48,
            fontFamily: 'Great Vibes',
            fill: '#1A1A1A',
        });
    } else {
        textObj = new fabric.IText('Kami mengundang Anda untuk hadir...', {
            left: 240,
            top: 360,
            originX: 'center',
            fontSize: 14,
            fontFamily: 'Plus Jakarta Sans',
            fill: '#555555',
        });
    }

    canvas.add(textObj);
    canvas.setActiveObject(textObj);
    canvas.renderAll();
    triggerAutosave();
};

const addOrnament = () => {
    if (!canvas) return;

    const heart = new fabric.Path('M 10,30 A 20,20 0,0,1 50,30 A 20,20 0,0,1 90,30 Q 90,60 50,90 Q 10,60 10,30 z', {
        left: 240,
        top: 400,
        originX: 'center',
        originY: 'center',
        fill: '#E0CEC7',
        scaleX: 0.5,
        scaleY: 0.5,
    });

    canvas.add(heart);
    canvas.setActiveObject(heart);
    canvas.renderAll();
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

const bringForward = () => {
    if (!canvas || !selectedObj.value) return;
    canvas.bringObjectForward(selectedObj.value);
    canvas.renderAll();
    triggerAutosave();
};

const sendBackward = () => {
    if (!canvas || !selectedObj.value) return;
    canvas.sendObjectBackwards(selectedObj.value);
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

// Zoom controls
const setZoom = (delta: number) => {
    if (!canvas) return;
    let newZoom = zoomLevel.value + delta;
    if (newZoom < 0.5) newZoom = 0.5;
    if (newZoom > 2.0) newZoom = 2.0;
    zoomLevel.value = newZoom;
    canvas.setZoom(newZoom);
    canvas.setWidth(designData.canvas.width * newZoom);
    canvas.setHeight(designData.canvas.height * newZoom);
    canvas.renderAll();
};

// Autosave with Debounce
let autosaveTimeout: any = null;
const triggerAutosave = () => {
    saveStatus.value = 'saving';
    clearTimeout(autosaveTimeout);
    autosaveTimeout = setTimeout(async () => {
        await executeSave();
    }, 1200);
};

const executeSave = async () => {
    if (!canvas) return;
    try {
        saveStatus.value = 'saving';
        const canvasObjects = canvas.toJSON();

        const payload = {
            design: {
                schemaVersion: 1,
                canvas: designData.canvas,
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

    // First ensure current canvas is saved
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
        <!-- Top Application Toolbar -->
        <header class="h-14 bg-white border-b border-stone-200 px-4 flex items-center justify-between z-30 shrink-0">
            <div class="flex items-center gap-4">
                <Link href="/dashboard" class="flex items-center gap-1.5 text-xs font-semibold text-stone-600 hover:text-stone-900 bg-stone-100 hover:bg-stone-200 px-3 py-1.5 rounded-lg transition-colors">
                    ← Dashboard
                </Link>
                <div class="h-4 w-px bg-stone-200"></div>
                <div>
                    <h1 class="text-xs font-bold text-stone-900 truncate max-w-xs">{{ invitation.title }}</h1>
                    <div class="flex items-center gap-2 text-[10px] text-stone-400">
                        <span v-if="saveStatus === 'saving'" class="text-amber-600 flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span> Menyimpan...
                        </span>
                        <span v-else-if="saveStatus === 'saved'" class="text-emerald-600 flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Tersimpan ({{ lastSavedTime }})
                        </span>
                        <span v-else class="text-rose-600">Gagal menyimpan</span>
                    </div>
                </div>
            </div>

            <!-- Middle Controls: Undo, Redo, Zoom, View Mode -->
            <div class="flex items-center gap-2">
                <div class="flex items-center bg-stone-100 rounded-lg p-0.5 border border-stone-200">
                    <button
                        @click="undo"
                        :disabled="historyIndex <= 0"
                        title="Undo (Ctrl+Z)"
                        class="p-1.5 text-xs text-stone-700 hover:bg-white rounded transition-colors disabled:opacity-30"
                    >
                        ↶
                    </button>
                    <button
                        @click="redo"
                        :disabled="historyIndex >= historyStack.length - 1"
                        title="Redo (Ctrl+Y)"
                        class="p-1.5 text-xs text-stone-700 hover:bg-white rounded transition-colors disabled:opacity-30"
                    >
                        ↷
                    </button>
                </div>

                <div class="flex items-center bg-stone-100 rounded-lg p-0.5 border border-stone-200 text-xs">
                    <button @click="setZoom(-0.1)" class="px-2 py-1 hover:bg-white rounded">-</button>
                    <span class="px-1 text-[11px] font-mono text-stone-600">{{ Math.round(zoomLevel * 100) }}%</span>
                    <button @click="setZoom(0.1)" class="px-2 py-1 hover:bg-white rounded">+</button>
                </div>
            </div>

            <!-- Right Controls: Preview & Publish -->
            <div class="flex items-center gap-3">
                <a
                    :href="`/i/${invitation.slug}`"
                    target="_blank"
                    class="px-3 py-1.5 text-xs font-semibold text-stone-700 bg-stone-100 hover:bg-stone-200 rounded-lg transition-colors flex items-center gap-1"
                >
                    Lihat Publik ↗
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
            <!-- Left Sidebar: Tools & Sections -->
            <aside class="w-64 bg-white border-r border-stone-200 flex flex-col shrink-0">
                <div class="flex border-b border-stone-200 text-xs font-semibold">
                    <button
                        @click="activeTab = 'elements'"
                        :class="['flex-1 py-3 text-center border-b-2', activeTab === 'elements' ? 'border-rose-500 text-rose-600' : 'border-transparent text-stone-500 hover:text-stone-800']"
                    >
                        Elemen
                    </button>
                    <button
                        @click="activeTab = 'sections'"
                        :class="['flex-1 py-3 text-center border-b-2', activeTab === 'sections' ? 'border-rose-500 text-rose-600' : 'border-transparent text-stone-500 hover:text-stone-800']"
                    >
                        Section
                    </button>
                    <button
                        @click="activeTab = 'events'"
                        :class="['flex-1 py-3 text-center border-b-2', activeTab === 'events' ? 'border-rose-500 text-rose-600' : 'border-transparent text-stone-500 hover:text-stone-800']"
                    >
                        Acara
                    </button>
                </div>

                <div class="p-4 flex-1 overflow-y-auto space-y-4">
                    <!-- Tab: Elements -->
                    <div v-if="activeTab === 'elements'" class="space-y-4">
                        <div class="text-[11px] font-bold text-stone-400 uppercase tracking-wider">Tambah Teks</div>
                        <div class="grid grid-cols-1 gap-2">
                            <button
                                @click="addText('title')"
                                class="w-full text-left p-2.5 rounded-xl border border-stone-200 hover:border-rose-300 hover:bg-rose-50/50 text-xs font-serif font-bold text-stone-800 transition-colors"
                            >
                                + Judul Acara (Playfair)
                            </button>
                            <button
                                @click="addText('names')"
                                class="w-full text-left p-2.5 rounded-xl border border-stone-200 hover:border-rose-300 hover:bg-rose-50/50 text-sm font-script text-stone-800 transition-colors"
                            >
                                + Nama Pasangan (Script)
                            </button>
                            <button
                                @click="addText('subtext')"
                                class="w-full text-left p-2.5 rounded-xl border border-stone-200 hover:border-rose-300 hover:bg-rose-50/50 text-xs text-stone-600 transition-colors"
                            >
                                + Paragraf / Deskripsi
                            </button>
                        </div>

                        <div class="text-[11px] font-bold text-stone-400 uppercase tracking-wider pt-2">Dekorasi & Ornamen</div>
                        <button
                            @click="addOrnament"
                            class="w-full text-left p-2.5 rounded-xl border border-stone-200 hover:border-rose-300 hover:bg-rose-50/50 text-xs text-stone-700 transition-colors flex items-center gap-2"
                        >
                            <span class="text-rose-400">♥</span> Simbol Cinta (Heart)
                        </button>
                    </div>

                    <!-- Tab: Sections -->
                    <div v-if="activeTab === 'sections'" class="space-y-2">
                        <div class="text-[11px] font-bold text-stone-400 uppercase tracking-wider mb-2">Urutan Bagian Undangan</div>
                        <div
                            v-for="sec in designData.sections"
                            :key="sec.id"
                            class="flex items-center justify-between p-2.5 bg-stone-50 border border-stone-200 rounded-xl text-xs"
                        >
                            <span class="font-medium text-stone-700">{{ sec.title }}</span>
                            <input
                                type="checkbox"
                                v-model="sec.visible"
                                @change="triggerAutosave"
                                class="rounded text-rose-600 focus:ring-rose-500"
                            />
                        </div>
                    </div>

                    <!-- Tab: Events Schedule -->
                    <div v-if="activeTab === 'events'" class="space-y-4">
                        <div class="text-[11px] font-bold text-stone-400 uppercase tracking-wider">Jadwal Acara</div>
                        <div v-for="ev in eventsList" :key="ev.id" class="p-3 bg-stone-50 border border-stone-200 rounded-xl space-y-2">
                            <div>
                                <label class="text-[10px] font-semibold text-stone-600">Nama Acara</label>
                                <input v-model="ev.name" @input="triggerAutosave" type="text" class="w-full text-xs p-1.5 border rounded bg-white" />
                            </div>
                            <div>
                                <label class="text-[10px] font-semibold text-stone-600">Venue / Lokasi</label>
                                <input v-model="ev.venue_name" @input="triggerAutosave" type="text" class="w-full text-xs p-1.5 border rounded bg-white" />
                            </div>
                            <div>
                                <label class="text-[10px] font-semibold text-stone-600">Alamat</label>
                                <textarea v-model="ev.address" @input="triggerAutosave" rows="2" class="w-full text-xs p-1.5 border rounded bg-white"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- Center: Interactive Canvas Editor -->
            <main class="flex-1 overflow-auto flex items-center justify-center p-8 bg-stone-200/70">
                <div class="relative bg-white shadow-2xl rounded-2xl overflow-hidden border border-stone-300">
                    <canvas ref="canvasEl"></canvas>
                </div>
            </main>

            <!-- Right Sidebar: Properties Inspector -->
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
                                <option value="Playfair Display">Playfair Display (Serif)</option>
                                <option value="Great Vibes">Great Vibes (Script)</option>
                                <option value="Alex Brush">Alex Brush (Brush)</option>
                                <option value="Cinzel">Cinzel (Classic)</option>
                                <option value="Cormorant Garamond">Cormorant Garamond</option>
                                <option value="Plus Jakarta Sans">Plus Jakarta Sans</option>
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
                        <div class="text-[11px] font-bold text-stone-400 uppercase tracking-wider mb-2">Layer & Aksi</div>
                        <div class="grid grid-cols-2 gap-2">
                            <button
                                @click="bringForward"
                                class="p-2 border border-stone-200 rounded-lg hover:bg-stone-50 text-[11px] font-medium"
                            >
                                Ke Depan ↑
                            </button>
                            <button
                                @click="sendBackward"
                                class="p-2 border border-stone-200 rounded-lg hover:bg-stone-50 text-[11px] font-medium"
                            >
                                Ke Belakang ↓
                            </button>
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

                <div v-else class="text-center py-12 text-stone-400 text-xs">
                    Pilih objek di kanvas untuk mengubah properti teks, font, warna, atau urutan layer.
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
                        Publikasi akan membuat versi baru yang dapat dilihat oleh seluruh tamu tanpa perlu login. Perubahan draft selanjutnya tidak akan mengubah halaman publik sebelum Anda mempublikasikannya kembali.
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
                    <p class="text-xs text-stone-500">Link undangan digital Anda kini sudah aktif dan dapat dibagikan.</p>
                    
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

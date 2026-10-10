<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import confetti from 'canvas-confetti';

const props = defineProps<{
    invitation: {
        id: number;
        title: string;
        slug: string;
        published_at: string;
    };
    design: any;
    events: Array<{
        id: number;
        name: string;
        starts_at: string;
        ends_at: string | null;
        timezone: string;
        venue_name: string;
        address: string | null;
        maps_url: string | null;
    }>;
    guestbook: Array<{
        id: number;
        guest_name: string;
        message: string;
        created_at: string;
    }>;
    guestName?: string | null;
    guestToken?: string | null;
}>();

// State
const isOpen = ref(false);
const isPlayingMusic = ref(false);
const audioEl = ref<HTMLAudioElement | null>(null);

// Countdown timer state
const timeLeft = ref({ days: 0, hours: 0, minutes: 0, seconds: 0 });
let timerInterval: any = null;

// RSVP form state
const rsvpForm = ref({
    guest_name: props.guestName || '',
    attendance_status: 'attending',
    attendee_count: 1,
    message: '',
    guest_token: props.guestToken || '',
});
const rsvpSubmitting = ref(false);
const rsvpSuccess = ref(false);
const rsvpErrorMessage = ref('');

// Guestbook entries local list (to append newly submitted prayers)
const localGuestbook = ref([...props.guestbook]);

// Target wedding date for countdown
const weddingDateStr = computed(() => {
    if (props.events && props.events.length > 0) {
        return props.events[0].starts_at;
    }
    return '2026-12-28 08:00:00';
});

// Calculate countdown
const updateCountdown = () => {
    const target = new Date(weddingDateStr.value).getTime();
    const now = new Date().getTime();
    const diff = target - now;

    if (diff > 0) {
        timeLeft.value = {
            days: Math.floor(diff / (1000 * 60 * 60 * 24)),
            hours: Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)),
            minutes: Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60)),
            seconds: Math.floor((diff % (1000 * 60)) / 1000),
        };
    } else {
        timeLeft.value = { days: 0, hours: 0, minutes: 0, seconds: 0 };
    }
};

onMounted(() => {
    updateCountdown();
    timerInterval = setInterval(updateCountdown, 1000);
});

onBeforeUnmount(() => {
    if (timerInterval) clearInterval(timerInterval);
    if (audioEl.value) {
        audioEl.value.pause();
    }
});

// Open Invitation & play music
const openInvitation = () => {
    isOpen.value = true;
    try {
        if (audioEl.value) {
            audioEl.value.play().then(() => {
                isPlayingMusic.value = true;
            }).catch(() => {
                // browser blocked autoplay without direct sound permission
                isPlayingMusic.value = false;
            });
        }
    } catch (e) {
        // audio handling
    }
};

const toggleMusic = () => {
    if (!audioEl.value) return;
    if (isPlayingMusic.value) {
        audioEl.value.pause();
        isPlayingMusic.value = false;
    } else {
        audioEl.value.play();
        isPlayingMusic.value = true;
    }
};

// Submit RSVP
const submitRsvp = async () => {
    if (!rsvpForm.value.guest_name) {
        rsvpErrorMessage.value = 'Silakan masukkan nama Anda.';
        return;
    }

    rsvpSubmitting.value = true;
    rsvpErrorMessage.value = '';

    try {
        const res = await fetch(`/api/public/${props.invitation.slug}/rsvp`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as any)?.content || '',
                'Accept': 'application/json',
            },
            body: JSON.stringify(rsvpForm.value),
        });

        const data = await res.json();
        if (res.ok && data.success) {
            rsvpSuccess.value = true;
            // Confetti celebration effect!
            confetti({
                particleCount: 100,
                spread: 70,
                origin: { y: 0.6 },
            });

            if (rsvpForm.value.message) {
                localGuestbook.value.unshift({
                    id: Date.now(),
                    guest_name: rsvpForm.value.guest_name,
                    message: rsvpForm.value.message,
                    created_at: 'Baru saja',
                });
            }
        } else {
            rsvpErrorMessage.value = data.message || 'Gagal mengirim konfirmasi RSVP.';
        }
    } catch (e) {
        rsvpErrorMessage.value = 'Terjadi kendala saat mengirim. Coba lagi.';
    } finally {
        rsvpSubmitting.value = false;
    }
};

// WhatsApp share
const shareWhatsApp = () => {
    const text = `Halo, kami mengundang Anda ke acara pernikahan kami melalui tautan undangan digital berikut: ${window.location.href}`;
    window.open(`https://api.whatsapp.com/send?text=${encodeURIComponent(text)}`, '_blank');
};
</script>

<template>
    <div class="min-h-screen bg-[#FDFBF7] text-stone-800 font-sans selection:bg-rose-200">
        <!-- Optional Audio Element -->
        <audio
            ref="audioEl"
            loop
            src="https://actions.google.com/sounds/v1/ambient/relaxing_music.ogg"
        ></audio>

        <!-- Floating Music Controller (shown after opened) -->
        <div v-if="isOpen" class="fixed bottom-6 right-6 z-40">
            <button
                @click="toggleMusic"
                class="w-12 h-12 rounded-full bg-stone-900/80 backdrop-blur text-white flex items-center justify-center shadow-lg border border-white/20 hover:scale-105 transition-transform"
                :title="isPlayingMusic ? 'Jeda Musik' : 'Putar Musik'"
            >
                <span v-if="isPlayingMusic" class="animate-spin text-sm">🎵</span>
                <span v-else class="text-sm">🔇</span>
            </button>
        </div>

        <!-- 1. FULLSCREEN OPENING COVER (Envelope style) -->
        <transition name="fade">
            <div
                v-if="!isOpen"
                class="fixed inset-0 z-50 flex items-center justify-center bg-stone-900/90 backdrop-blur-md p-4"
            >
                <div class="relative w-full max-w-md rounded-3xl bg-[#FDFBF7] p-8 text-center shadow-2xl border border-stone-200 overflow-hidden">
                    <!-- Ornamental corner frame -->
                    <div class="absolute top-3 left-3 text-stone-300 text-lg">✤</div>
                    <div class="absolute top-3 right-3 text-stone-300 text-lg">✤</div>
                    <div class="absolute bottom-3 left-3 text-stone-300 text-lg">✤</div>
                    <div class="absolute bottom-3 right-3 text-stone-300 text-lg">✤</div>

                    <div class="text-[11px] font-serif uppercase tracking-widest text-stone-500 mb-2">The Wedding Of</div>
                    <h1 class="font-script text-5xl text-stone-900 my-4">Dimas & Anisa</h1>
                    <p class="font-serif text-xs text-stone-600 mb-6">Minggu, 28 Desember 2026</p>

                    <div class="my-6 p-4 rounded-2xl bg-white border border-stone-200/80 shadow-sm text-center">
                        <div class="text-[11px] text-stone-400 mb-1">Kepada Yth. Bapak/Ibu/Saudara/i:</div>
                        <div class="text-base font-bold text-stone-900 font-serif">
                            {{ guestName || 'Tamu Undangan Yang Berbahagia' }}
                        </div>
                    </div>

                    <button
                        @click="openInvitation"
                        class="w-full py-3.5 px-6 rounded-2xl bg-stone-900 hover:bg-stone-800 text-white font-medium text-sm shadow-lg shadow-stone-900/20 hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center justify-center gap-2"
                    >
                        <span>✉️</span> Buka Undangan
                    </button>
                </div>
            </div>
        </transition>

        <!-- 2. MAIN INVITATION CONTENT (Scrollable, mobile-optimized) -->
        <div class="max-w-xl mx-auto min-h-screen bg-white shadow-xl flex flex-col relative border-x border-stone-100">
            <!-- Hero Cover Header -->
            <header class="relative min-h-[85vh] flex flex-col items-center justify-center text-center p-8 bg-gradient-to-b from-[#F9F5EF] to-white overflow-hidden">
                <div class="text-[12px] font-serif tracking-[0.3em] uppercase text-stone-500 mb-4">
                    The Wedding Celebration
                </div>
                <h1 class="font-script text-6xl sm:text-7xl text-stone-900 my-4 leading-tight">
                    Dimas & Anisa
                </h1>
                <div class="w-16 h-0.5 bg-rose-400 my-4 mx-auto"></div>
                <p class="font-serif text-sm text-stone-600 mb-8">
                    28 . 12 . 2026
                </p>

                <!-- Hero Photo -->
                <div class="relative w-64 h-80 rounded-t-full rounded-b-2xl overflow-hidden border-4 border-white shadow-xl mx-auto mb-6">
                    <img
                        src="https://images.unsplash.com/photo-1519741497674-611481863552?auto=format&fit=crop&w=800&q=80"
                        alt="Dimas & Anisa"
                        class="w-full h-full object-cover"
                    />
                </div>

                <div class="text-xs text-stone-400 font-serif italic">
                    "Dan Kami jadikan kamu berpasang-pasangan"
                </div>
            </header>

            <!-- Couple Section -->
            <section class="py-16 px-6 text-center bg-[#FAF8F5] border-y border-stone-100">
                <h2 class="font-serif text-2xl font-bold text-stone-900 mb-2">Mempelai Pengantin</h2>
                <p class="text-xs text-stone-500 max-w-sm mx-auto mb-10 leading-relaxed">
                    Dengan memohon rahmat dan ridho Allah SWT, kami bermaksud menyelenggarakan pernikahan kami:
                </p>

                <div class="space-y-10">
                    <!-- Groom -->
                    <div class="flex flex-col items-center">
                        <div class="w-28 h-28 rounded-full overflow-hidden border-2 border-stone-300 shadow-md mb-3">
                            <img
                                src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=400&q=80"
                                alt="Groom"
                                class="w-full h-full object-cover"
                            />
                        </div>
                        <h3 class="font-serif font-bold text-xl text-stone-900">Dimas Prasetyo, S.Kom</h3>
                        <p class="text-xs text-stone-500 mt-1 max-w-xs">
                            Putra pertama dari Bpk. Bambang Prasetyo & Ibu Sri Wahyuni
                        </p>
                    </div>

                    <div class="font-script text-4xl text-rose-500">&</div>

                    <!-- Bride -->
                    <div class="flex flex-col items-center">
                        <div class="w-28 h-28 rounded-full overflow-hidden border-2 border-stone-300 shadow-md mb-3">
                            <img
                                src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=400&q=80"
                                alt="Bride"
                                class="w-full h-full object-cover"
                            />
                        </div>
                        <h3 class="font-serif font-bold text-xl text-stone-900">Anisa Rahmawati, S.E</h3>
                        <p class="text-xs text-stone-500 mt-1 max-w-xs">
                            Putri kedua dari Bpk. H. Rahmat Hidayat & Ibu Siti Aminah
                        </p>
                    </div>
                </div>
            </section>

            <!-- Countdown Timer -->
            <section class="py-12 px-6 text-center bg-stone-900 text-white">
                <h3 class="text-xs uppercase tracking-widest text-stone-400 font-serif mb-6">Menghitung Hari Bahagia</h3>
                <div class="grid grid-cols-4 gap-3 max-w-xs mx-auto">
                    <div class="p-3 bg-white/10 backdrop-blur rounded-2xl border border-white/10">
                        <div class="font-serif text-2xl font-bold">{{ timeLeft.days }}</div>
                        <div class="text-[9px] uppercase tracking-wider text-stone-400 mt-1">Hari</div>
                    </div>
                    <div class="p-3 bg-white/10 backdrop-blur rounded-2xl border border-white/10">
                        <div class="font-serif text-2xl font-bold">{{ timeLeft.hours }}</div>
                        <div class="text-[9px] uppercase tracking-wider text-stone-400 mt-1">Jam</div>
                    </div>
                    <div class="p-3 bg-white/10 backdrop-blur rounded-2xl border border-white/10">
                        <div class="font-serif text-2xl font-bold">{{ timeLeft.minutes }}</div>
                        <div class="text-[9px] uppercase tracking-wider text-stone-400 mt-1">Menit</div>
                    </div>
                    <div class="p-3 bg-white/10 backdrop-blur rounded-2xl border border-white/10">
                        <div class="font-serif text-2xl font-bold">{{ timeLeft.seconds }}</div>
                        <div class="text-[9px] uppercase tracking-wider text-stone-400 mt-1">Detik</div>
                    </div>
                </div>
            </section>

            <!-- Event Details (Akad & Resepsi) -->
            <section class="py-16 px-6 bg-white space-y-8">
                <div class="text-center mb-8">
                    <h2 class="font-serif text-2xl font-bold text-stone-900">Agenda Acara</h2>
                    <p class="text-xs text-stone-500 mt-1">Rangkaian acara yang akan diselenggarakan:</p>
                </div>

                <div v-for="event in events" :key="event.id" class="p-6 rounded-3xl bg-[#FAF8F5] border border-stone-200 text-center space-y-3">
                    <div class="inline-block px-3 py-1 rounded-full text-[10px] font-bold tracking-wider uppercase bg-rose-100 text-rose-700">
                        {{ event.name }}
                    </div>
                    <div class="font-serif text-lg font-bold text-stone-900">
                        Minggu, 28 Desember 2026
                    </div>
                    <div class="text-xs text-stone-600 font-medium">
                        Pukul 08:00 - 10:00 {{ event.timezone }}
                    </div>
                    <div class="pt-2 text-xs text-stone-700">
                        <div class="font-bold text-stone-900">{{ event.venue_name }}</div>
                        <div class="text-stone-500 text-[11px] mt-0.5">{{ event.address }}</div>
                    </div>
                    <div v-if="event.maps_url" class="pt-2">
                        <a
                            :href="event.maps_url"
                            target="_blank"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold text-stone-800 bg-white border border-stone-300 hover:bg-stone-50 shadow-sm"
                        >
                            📍 Petunjuk Google Maps
                        </a>
                    </div>
                </div>
            </section>

            <!-- Photo Gallery -->
            <section class="py-16 px-6 bg-[#FAF8F5] border-t border-stone-100 text-center">
                <h2 class="font-serif text-2xl font-bold text-stone-900 mb-2">Galeri Momen</h2>
                <p class="text-xs text-stone-500 mb-8">Potret cinta dan kebahagiaan perjalanan kami.</p>

                <div class="grid grid-cols-2 gap-3">
                    <div class="rounded-2xl overflow-hidden shadow-sm h-48">
                        <img
                            src="https://images.unsplash.com/photo-1511285560929-80b456fea0bc?auto=format&fit=crop&w=600&q=80"
                            alt="Gallery 1"
                            class="w-full h-full object-cover hover:scale-105 transition-transform duration-300"
                        />
                    </div>
                    <div class="rounded-2xl overflow-hidden shadow-sm h-48">
                        <img
                            src="https://images.unsplash.com/photo-1583939003579-730e3918a45a?auto=format&fit=crop&w=600&q=80"
                            alt="Gallery 2"
                            class="w-full h-full object-cover hover:scale-105 transition-transform duration-300"
                        />
                    </div>
                    <div class="rounded-2xl overflow-hidden shadow-sm h-48">
                        <img
                            src="https://images.unsplash.com/photo-1520854221256-17451cc331bf?auto=format&fit=crop&w=600&q=80"
                            alt="Gallery 3"
                            class="w-full h-full object-cover hover:scale-105 transition-transform duration-300"
                        />
                    </div>
                    <div class="rounded-2xl overflow-hidden shadow-sm h-48">
                        <img
                            src="https://images.unsplash.com/photo-1465495976277-4387d4b0b4c6?auto=format&fit=crop&w=600&q=80"
                            alt="Gallery 4"
                            class="w-full h-full object-cover hover:scale-105 transition-transform duration-300"
                        />
                    </div>
                </div>
            </section>

            <!-- RSVP Form Section -->
            <section class="py-16 px-6 bg-white border-t border-stone-100">
                <div class="text-center mb-8">
                    <h2 class="font-serif text-2xl font-bold text-stone-900">Konfirmasi Kehadiran</h2>
                    <p class="text-xs text-stone-500 mt-1">Mohon konfirmasikan kehadiran Anda pada form di bawah ini:</p>
                </div>

                <div v-if="rsvpSuccess" class="p-6 bg-emerald-50 rounded-2xl border border-emerald-200 text-center space-y-2">
                    <div class="text-2xl">🎉</div>
                    <div class="font-serif font-bold text-emerald-900 text-lg">Terima Kasih!</div>
                    <p class="text-xs text-emerald-700">
                        Konfirmasi kehadiran dan doa restu Anda telah berhasil kami simpan.
                    </p>
                </div>

                <form v-else @submit.prevent="submitRsvp" class="space-y-4 max-w-sm mx-auto">
                    <div>
                        <label class="block text-xs font-semibold text-stone-700 mb-1">Nama Lengkap *</label>
                        <input
                            v-model="rsvpForm.guest_name"
                            type="text"
                            required
                            class="w-full p-2.5 rounded-xl border border-stone-300 text-xs bg-stone-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-rose-500"
                            placeholder="Nama Tamu"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-stone-700 mb-1">Status Kehadiran *</label>
                        <select
                            v-model="rsvpForm.attendance_status"
                            class="w-full p-2.5 rounded-xl border border-stone-300 text-xs bg-stone-50 focus:bg-white"
                        >
                            <option value="attending">Ya, Saya Akan Hadir</option>
                            <option value="declined">Maaf, Berhalangan Hadir</option>
                            <option value="tentative">Masih Belum Pasti</option>
                        </select>
                    </div>

                    <div v-if="rsvpForm.attendance_status === 'attending'">
                        <label class="block text-xs font-semibold text-stone-700 mb-1">Jumlah Tamu Hadir</label>
                        <select
                            v-model.number="rsvpForm.attendee_count"
                            class="w-full p-2.5 rounded-xl border border-stone-300 text-xs bg-stone-50 focus:bg-white"
                        >
                            <option :value="1">1 Orang</option>
                            <option :value="2">2 Orang</option>
                            <option :value="3">3 Orang</option>
                            <option :value="4">4 Orang</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-stone-700 mb-1">Ucapan & Doa Restu</label>
                        <textarea
                            v-model="rsvpForm.message"
                            rows="3"
                            class="w-full p-2.5 rounded-xl border border-stone-300 text-xs bg-stone-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-rose-500"
                            placeholder="Tulis ucapan selamat..."
                        ></textarea>
                    </div>

                    <div v-if="rsvpErrorMessage" class="text-xs text-rose-600 bg-rose-50 p-2 rounded">
                        {{ rsvpErrorMessage }}
                    </div>

                    <button
                        type="submit"
                        :disabled="rsvpSubmitting"
                        class="w-full py-3 bg-stone-900 hover:bg-stone-800 text-white rounded-xl text-xs font-bold shadow-md transition-all disabled:opacity-50"
                    >
                        {{ rsvpSubmitting ? 'Mengirim...' : 'Kirim Konfirmasi Kehadiran' }}
                    </button>
                </form>
            </section>

            <!-- Guestbook / Wishes Wall -->
            <section class="py-16 px-6 bg-[#FAF8F5] border-t border-stone-100">
                <div class="text-center mb-8">
                    <h2 class="font-serif text-2xl font-bold text-stone-900">Buku Tamu & Ucapan</h2>
                    <p class="text-xs text-stone-500 mt-1">Doa restu hangat dari para sahabat dan keluarga:</p>
                </div>

                <div class="space-y-3 max-w-sm mx-auto">
                    <div
                        v-for="entry in localGuestbook"
                        :key="entry.id"
                        class="p-4 rounded-2xl bg-white border border-stone-200 shadow-sm text-left"
                    >
                        <div class="flex items-center justify-between mb-1">
                            <span class="font-bold text-xs text-stone-900">{{ entry.guest_name }}</span>
                            <span class="text-[10px] text-stone-400">{{ entry.created_at }}</span>
                        </div>
                        <p class="text-xs text-stone-600 leading-relaxed">{{ entry.message }}</p>
                    </div>

                    <div v-if="localGuestbook.length === 0" class="text-center text-xs text-stone-400 py-6">
                        Belum ada ucapan. Jadilah yang pertama memberikan doa restu!
                    </div>
                </div>
            </section>

            <!-- WhatsApp Share & Footer -->
            <footer class="py-12 px-6 bg-stone-900 text-center text-white space-y-4">
                <button
                    @click="shareWhatsApp"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-md transition-colors"
                >
                    <span>💬</span> Bagikan ke WhatsApp
                </button>

                <div class="text-xs text-stone-400 pt-4 border-t border-stone-800">
                    <p>Merupakan suatu kehormatan & kebahagiaan bagi kami apabila Anda berkenan hadir.</p>
                    <div class="font-script text-2xl text-stone-300 mt-2">Dimas & Anisa</div>
                </div>

                <div class="text-[10px] text-stone-600 pt-4">
                    Dibuat dengan cinta via <a href="/" class="underline text-stone-500">Undangan.id</a>
                </div>
            </footer>
        </div>
    </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.5s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>

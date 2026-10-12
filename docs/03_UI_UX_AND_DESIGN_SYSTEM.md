# 03 - Standar UI/UX & Design System

Dokumen ini mengatur standar antarmuka pengguna (UI), pengalaman pengguna (UX), serta komponen interaktif yang wajib dipatuhi di seluruh halaman aplikasi.

---

## 1. Aturan Baku Notifikasi & Konfirmasi (WAJIB)

> ⛔ **LARANGAN KERAS PENGGUNAAN DIALOG NATIVE**:
> **DILARANG MENGGUNAKAN** `alert(...)` atau `confirm(...)` bawaan browser di seluruh kode aplikasi. Setiap interaksi notifikasi dan konfirmasi wajib menggunakan composable modern yang telah disediakan.

### A. Sistem Notifikasi Mengambang (`useToast`)

Gunakan composable `@/composables/useToast` untuk memberikan feedback visual atas aksi pengguna (create, update, delete, salin teks, error, dsb).

```typescript
import { useToast } from '@/composables/useToast';

const toast = useToast();

// Sukses (Warna Emerald)
toast.success('Undangan berhasil diterbitkan ke publik!');

// Gagal / Error (Warna Rose)
toast.error('Gagal mengunggah foto. Ukuran file maksimal 5MB.');

// Informasi (Warna Sapphire)
toast.info('Menyinkronkan data dengan server...');

// Peringatan (Warna Amber)
toast.warning('Masa aktif paket Anda tersisa 3 hari.');
```

**Penempatan Komponen**:
Komponen `<ToastContainer />` sudah dimount secara global di:
- `resources/js/layouts/AppLayout.vue` (Mencakup seluruh halaman dashboard, tamu, admin, billing).
- `resources/js/pages/Editor/Index.vue` (Mencakup halaman editor visual fullscreen).

### B. Dialog Konfirmasi Kustom (`useConfirm`)

Gunakan composable `@/composables/useConfirm` untuk meminta konfirmasi sebelum mengeksekusi aksi destruktif (misal menghapus undangan, menghapus tamu, reset data).

```typescript
import { useConfirm } from '@/composables/useConfirm';

const { confirmAction } = useConfirm();

const handleDelete = async (id: number) => {
    const confirmed = await confirmAction({
        title: 'Hapus Undangan?',
        message: 'Tindakan ini permanen. Semua data tamu, RSVP, dan ucapan pada undangan ini akan dihapus.',
        confirmText: 'Ya, Hapus Permanen',
        cancelText: 'Batalkan',
        isDanger: true // Menampilkan warna tombol merah dan ikon warning
    });

    if (!confirmed) return;

    // Lanjutkan proses penghapusan
    router.delete(`/invitations/${id}`, {
        onSuccess: () => toast.success('Undangan berhasil dihapus.')
    });
};
```

---

## 2. Standar Form Input (Data Picker & Upload)

Untuk mencegah kesalahan format input manusia (*human error*), gunakan kontrol visual bawaan HTML5 & komponen kustom:

### A. Tanggal & Waktu (Date/Time Picker)
- **Hanya Tanggal** (misal tanggal pernikahan):
  ```html
  <input 
      type="date" 
      v-model="form.weddingDate" 
      class="w-full px-3 py-2 bg-stone-50 border border-stone-200 rounded-xl text-xs font-semibold focus:ring-rose-500"
  />
  ```
- **Tanggal Beserta Jam** (misal waktu mulai & selesai acara akad/resepsi):
  ```html
  <input 
      type="datetime-local" 
      v-model="event.starts_at" 
      class="w-full px-3 py-2 bg-stone-50 border border-stone-200 rounded-xl text-xs font-semibold focus:ring-rose-500"
  />
  ```

### B. Pemilih Warna (Color Picker)
Gunakan input warna visual dengan preview lingkaran atau swatch:
```html
<input 
    type="color" 
    v-model="designForm.design_json.theme.primaryColor" 
    class="w-8 h-8 rounded-lg cursor-pointer border border-stone-200 bg-transparent p-0"
/>
```

### C. Unggah Media (Foto, QRIS, MP3)
- Unggah file diproses langsung via endpoint `/api/upload` (controller memvalidasi tipe mime `image/*` atau `audio/*`).
- Berikan indikator visual `isUploading` saat file sedang dikirim ke server.
- Setelah berhasil, URL file hasil upload langsung disimpan ke model reactive.

---

## 3. Filosofi Desain Visual

Platform ini mengusung estetika **Modern Luxury Romantic Wedding**:
- **Palet Utama**: Dominasi warna hangat batu alam (`stone-900`, `stone-800`), aksen emas lembut (`#D4AF37`), dan sentuhan rose elegan.
- **Glassmorphism**: Gunakan `backdrop-blur-md bg-stone-900/80` atau `bg-white/80` dengan border tipis `border-white/10` atau `border-stone-200` untuk panel mengambang, modal, dan navbar.
- **Micro-Interactions**: Transisi halus `transition-all duration-300`, hover states pada tombol, dan animiasi confetti pada saat tamu mengonfirmasi kehadiran.
- **Framing Pratinjau Mobile**: Rasio layar HP dibatasi maksimal `max-h-[82vh]` dengan border bezel siluet smartphone dan bayangan lembut agar konten cover atas tidak terpotong pada berbagai resolusi monitor laptop.

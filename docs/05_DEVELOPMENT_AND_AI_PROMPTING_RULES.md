# 05 - Panduan Pengembangan & Aturan Instruksi AI (AI Prompting Rules)

Dokumen ini berisi pedoman eksplisit untuk **Prompting Asisten AI** (seperti Claude, ChatGPT, Cursor, Windsurf, Copilot) dan pengembang baru agar tidak membuat "jalur aneh" yang menyimpang dari arsitektur yang sudah stabil.

---

## 1. Golden Rules untuk AI & Pengembang

Ketika Anda memberikan tugas kepada AI lain di masa mendatang, **copy-paste paragraf berikut ke dalam prompt Anda**:

> *"Anda sedang memodifikasi proyek UndanganDigital (Laravel 11 + Inertia Vue 3 + Tailwind CSS). Wajib membaca file di folder `docs/` terlebih dahulu sebelum mengusulkan atau menulis kode baru. Dilarang menggunakan alert() atau confirm() bawaan browser, dilarang merusak kontrak `design_json`, dilarang memisahkan login admin/user (login satu pintu di /login), dan pastikan `npm run build` sukses tanpa error."*

---

## 2. Daftar "Jalur Aneh" yang DILARANG KERAS (Anti-Patterns)

Berikut adalah daftar kesalahan umum yang sering dibuat AI jika tidak dipandu:

| No | ⛔ Jalur Aneh / Anti-Pattern (DILARANG) | ✅ Solusi Benar (WAJIB DIIKUTI) |
|---|---|---|
| 1 | Membuat form login terpisah `/admin/login` dengan tabel user baru | Gunakan **satu pintu** di `/login` dengan kolom `role` pada tabel `users`. Controller akan otomatis me-redirect ke dashboard yang tepat. |
| 2 | Menggunakan `window.alert()` atau `window.confirm()` | Gunakan composable modern `@/composables/useToast` dan `@/composables/useConfirm`. |
| 3 | Mengganti nama atau menghapus key di `design_json` | Skema bersifat **aditif**. Jika ada fitur baru, tambahkan key baru dengan nilai default tanpa menghapus key lama. |
| 4 | Me-refresh seluruh iframe preview saat pengguna mengetik di editor | Gunakan komunikasi instan `postMessage({ type: 'UPDATE_DESIGN', ... })` untuk update 0ms tanpa refresh. |
| 5 | Menggunakan textfield biasa untuk input tanggal/waktu | Gunakan visual picker bawaan browser: `<input type="date">` dan `<input type="datetime-local">`. |
| 6 | Memasang dependensi CSS raksasa baru (Bootstrap/Element UI) | Tetap gunakan utilitas **Tailwind CSS v3** yang sudah terkonfigurasi. |
| 7 | Mengabaikan kompilasi aset saat deploy | Selalu jalankan `npm run build` untuk memverifikasi bahwa TypeScript & Vue template valid sebelum commit. |

---

## 3. Checklist Verifikasi Sebelum Commit & Deploy

Setiap kali menyelesaikan sebuah fitur atau perbaikan tema:

- [ ] **Verifikasi Notifikasi**: Pastikan tidak ada satupun panggilan `alert(` atau `confirm(` yang tertinggal (`grep_search` pada `resources/`).
- [ ] **Verifikasi Skema Desain**: Pastikan data `design_json` tetap dapat dibaca oleh editor maupun tampilan publik.
- [ ] **Verifikasi Iframe Preview**: Buka editor, pastikan perubahan teks/warna langsung terpantul di layar HP sebelah kanan secara instan.
- [ ] **Kompilasi Aset**: Jalankan perintah `npm run build` di terminal untuk memastikan tidak ada error TypeScript atau syntax template.
- [ ] **Cek Status Deploy Railway**: Setelah push ke GitHub (`origin master`), pastikan container Railway berstatus `● Online`.

---

## 4. Contoh Format Prompt untuk AI di Masa Depan

### Contoh 1: Menambahkan Variasi Tema / Desain Baru
```text
Saya ingin menambahkan variasi tema baru bertema "Rustic Vintage" untuk undangan digital ini.
Tolong baca panduan di `docs/02_TEMPLATE_AND_THEME_SYSTEM.md`.
Ketentuan:
1. Jangan merombak struktur `design_json` yang sudah ada, gunakan properti yang ada atau tambahkan variasi pada `theme`.
2. Pastikan warna dapat dipilih melalui palet preset di `Editor/Index.vue`.
3. Tampilan publik harus tetap merespons pesan `postMessage` secara instan.
4. Gunakan `useToast` jika ada interaksi tombol copy/simpan.
```

### Contoh 2: Menambahkan Fitur Interaktif Baru (misal: Buku Cerita / Love Story)
```text
Saya ingin melengkapi tab cerita cinta (Love Story) di editor dan tampilan undangan publik.
Tolong baca panduan di `docs/02_TEMPLATE_AND_THEME_SYSTEM.md` dan `docs/03_UI_UX_AND_DESIGN_SYSTEM.md`.
Ketentuan:
1. Tambahkan array `stories: [{ year: '', title: '', story: '' }]` ke dalam objek `design_json` secara aditif dengan fallback aman jika data belum ada.
2. Gunakan komponen form yang rapi dengan Tailwind CSS.
3. Jangan gunakan alert/confirm browser, gunakan `useToast` dan `useConfirm`.
4. Jalankan `npm run build` setelah selesai untuk verifikasi.
```

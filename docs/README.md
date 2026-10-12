# 📖 Dokumentasi Teknis & Panduan Pengembangan Undangan Digital

Dokumentasi ini dibuat khusus sebagai **Single Source of Truth (SSOT)** arsitektur, standar kode, dan kontrak data untuk seluruh pengembang manusia maupun asisten AI (seperti Claude, ChatGPT, Cursor, GitHub Copilot, dsb).

> ⚠️ **ATURAN UTAMA BAGI AI / PENGEMBANG**:
> Sebelum menambahkan fitur baru, memodifikasi tema, atau mengubah logika sistem, **BACA DAN IKUTI** panduan di folder `docs/` ini. **DILARANG KERAS** membuat struktur jalur baru yang menyimpang dari kontrak data (`design_json`), sistem notifikasi kustom, dan arsitektur autentikasi yang sudah ada.

---

## 🗂️ Daftar Dokumen Panduan

1. **[01_ARCHITECTURE_AND_TECH_STACK.md](./01_ARCHITECTURE_AND_TECH_STACK.md)**
   - Stack teknologi (Laravel 11, Inertia.js, Vue 3 TypeScript, Tailwind CSS, PostgreSQL).
   - Arsitektur autentikasi satu pintu (Unified Login & Role-based redirection).
   - Struktur folder dan alur deployment Docker & Railway.

2. **[02_TEMPLATE_AND_THEME_SYSTEM.md](./02_TEMPLATE_AND_THEME_SYSTEM.md)**
   - **PANDUAN UTAMA TEMA**: Kontrak skema `design_json` standar platform.
   - Sistem palet warna, tipografi font, dan styling dinamis.
   - Komunikasi Real-time `postMessage` (0ms lag) antara Editor dan Iframe Pratinjau.
   - Standar pembuatan template/tema baru agar selalu kompatibel dengan panel Editor.

3. **[03_UI_UX_AND_DESIGN_SYSTEM.md](./03_UI_UX_AND_DESIGN_SYSTEM.md)**
   - Standar komponen UI (Glassmorphism, Dark/Warm luxury wedding theme).
   - **Larangan `alert()` dan `confirm()` bawaan browser**: Panduan penggunaan `useToast()` dan `useConfirm()`.
   - Standar input form (DatePicker visual, DateTimePicker, ColorPicker, File Upload).

4. **[04_DATABASE_AND_API_CONTRACTS.md](./04_DATABASE_AND_API_CONTRACTS.md)**
   - Skema lengkap tabel database & relasi Eloquent.
   - Kontrak API REST & rute Inertia controller.
   - Mekanisme publikasi, versioning, dan slug publik undangan.

5. **[05_DEVELOPMENT_AND_AI_PROMPTING_RULES.md](./05_DEVELOPMENT_AND_AI_PROMPTING_RULES.md)**
   - Aturan baku instruksi (prompting) untuk AI eksternal agar tidak merusak kode yang ada.
   - Checklist verifikasi & build sebelum melakukan commit atau deployment.

---

## 🚀 Ringkasan Akses Cepat Platform

- **URL Produksi**: `https://undangan-digital-production-e6fd.up.railway.app`
- **Port Lokal Default**: `http://localhost:8000` (atau `http://localhost:8080` via Docker)
- **Akun Default**:
  - **Admin**: `admin@undangan.id` / `password`
  - **User**: `user@undangan.id` / `password`

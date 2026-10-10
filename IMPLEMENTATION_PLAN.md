# Roadmap Implementasi Teknis — Platform Undangan Digital SaaS

Roadmap pengerjaan proyek sesuai dengan Milestone M0 hingga M7 dari PRD Spesifikasi Bangun Teknis.

---

## Ringkasan Milestone & Kriteria Selesai (DoD)

### Milestone 0: Discovery & Arsitektur
- [x] Analisis PRD dokumen spesifikasi lengkap (`PRD_Platform_Undangan_Digital_Final.docx`).
- [x] Pembuatan dokumen fondasi: `ARCHITECTURE.md`, `ERD.md`, `API_SPEC.md`, `README.md`, `IMPLEMENTATION_PLAN.md`.
- [x] Verifikasi runtime lokal: PHP 8.3.33, Composer 2.8.9, Node.js 24.17.0, NPM 11.13.0.

### Milestone 1: Fondasi Proyek (Laravel 11 + Vue 3 Inertia)
- [x] Bootstrap modular monolith Laravel 11.
- [x] Setup Vue 3 + TypeScript + Tailwind CSS + Lucide Icons + Inertia.js.
- [x] Konfigurasi Autentikasi (Customer & Admin roles), CSRF, Session, dan Rate Limiting.
- [x] Setup testing framework (PHPUnit) dan validasi baseline.

### Milestone 2: Model Data, Relasi & Otorisasi
- [x] Pembuatan migrasi 17 tabel inti sesuai `ERD.md`.
- [x] Model Eloquent & casts (JSONB, DateTime, Enum equivalents).
- [x] Policies untuk isolasi tenant & pencegahan IDOR (`InvitationPolicy`, `GuestPolicy`, dll).
- [x] Seeders: Admin default (`admin@undangan.id`), Customer demo (`pengantin@undangan.id`), Paket langganan (Free, Gold, Platinum), Template pernikahan (Minimalist, Rustic, Floral, Classic).

### Milestone 3: Dashboard Pelanggan & Public Invitation Renderer
- [x] Dashboard pelanggan (list undangan, status, statistik tamu, quick actions).
- [x] Modul Events (Akad Nikah, Resepsi, detail venue, Google Maps).
- [x] Public Invitation Engine (`/i/{slug}`):
  - Dynamic responsive template loader.
  - Cover pembuka surat & nama tamu personal (`?to=...`).
  - Detail mempelai, countdown timer, rincian acara, foto galeri, pemutar musik.
  - Snapshot viewer (hanya menampilkan versi publik yang immutable).

### Milestone 4: Visual Canvas Hybrid Editor
- [x] Integrasi Fabric.js canvas editor.
- [x] Hybrid Section Organizer (reorder sections, toggle visibility).
- [x] Canvas element interactions: tambah teks, ornamen grafis, gambar, pengatur ukuran, rotasi, warna, typography.
- [x] History manager: Undo/Redo stack.
- [x] Autosave engine dengan debounce & indikator status (Saving/Saved/Error).
- [x] Serializer & Deserializer JSON schema version 1.
- [x] Flow Publikasi: snapshot immutable ke `invitation_versions`.

### Milestone 5: Manajemen Tamu, Buku Tamu & RSVP
- [x] Manajemen daftar tamu pengantin (tambah, edit, hapus, kuota orang, nomor WA).
- [x] Fitur buat link personal dan share ke WhatsApp.
- [x] Formulir RSVP publik dengan rate limiting & proteksi spam.
- [x] Formulir Buku Tamu publik dan panel moderasi bagi pemilik undangan (Approve / Reject).

### Milestone 6: Billing, Transaksi & Monetisasi
- [x] Halaman katalog paket upgrade (`/pricing`).
- [x] Checkout order flow dengan snapshot harga dan produk.
- [x] Payment gateway adapter interface (`PaymentGatewayInterface`).
- [x] Sandbox Payment Simulator (QRIS / VA) untuk kemudahan pengujian lokal.
- [x] Webhook receiver dengan signature verification dan deduplikasi idempotency.
- [x] Server-side Entitlement Enforcer: mengunci template dan fitur premium untuk akun gratis.

### Milestone 7: Panel Admin, Pengerasan Keamanan & Automated Testing
- [x] Admin panel (kelola user, template, transaksi, moderasi buku tamu, audit log).
- [x] Pengujian menyeluruh (15 Feature tests, 52 assertions - 100% Passed):
  - Test Auth & Tenant isolation.
  - Test Entitlement check.
  - Test Publishing immutable snapshot.
  - Test RSVP submission.
  - Test Webhook idempotency.
- [x] Validasi build production frontend (`npm run build`).

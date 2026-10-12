# 04 - Kontrak Database & REST API

Dokumen ini mendokumentasikan skema database relasional, model Eloquent, serta kontrak endpoint REST API yang digunakan di platform Undangan Digital.

---

## 1. Diagram Relasi Entitas (ERD Ringkas)

```
[Users] (role: 'admin' | 'user')
   │
   ├── 1:N ──> [Invitations] (slug, status: 'draft'|'published')
   │              │
   │              ├── 1:1 ──> [InvitationDesigns] (design_json, schema_version)
   │              ├── 1:N ──> [PublishedVersions] (snapshot_json, version_number)
   │              ├── 1:N ──> [Events] (title, starts_at, ends_at, venue)
   │              ├── 1:N ──> [Guests] (name, slug, category, pax, qr_code_hash)
   │              │              └── 1:1 ──> [RSVPs] (attendance, pax, message)
   │              └── 1:N ──> [Wishes] (name, message, is_hidden)
   │
   ├── 1:N ──> [Orders] (order_number, amount, status: 'pending'|'paid')
   │              └── 1:1 ──> [Payments] (provider, transaction_id, status)
   │
   └── 1:N ──> [ServiceRequests] (subject, category, status)
```

---

## 2. Rincian Tabel Utama

### A. Tabel `invitations`
- `id` (BigIncrements, PK)
- `user_id` (BigInteger, FK ke `users.id`, cascade on delete)
- `template_id` (BigInteger, FK ke `templates.id`)
- `title` (String, misal: *"The Wedding of Dimas & Anisa"*)
- `slug` (String, Unique, URL ramah SEO misal: `"dimas-anisa"`)
- `status` (Enum: `'draft'`, `'published'`, `'archived'`)
- `published_version_id` (Nullable BigInteger, menunjuk ke snapshot aktif di `published_versions`)
- `created_at`, `updated_at`

### B. Tabel `invitation_designs`
- `id` (BigIncrements, PK)
- `invitation_id` (BigInteger, Unique FK ke `invitations.id`)
- `schema_version` (Integer, default `1`)
- `design_json` (JSONB / JSON, menyimpan data lengkap teks, palet, galeri, amplop, dll)
- `updated_at`

### C. Tabel `published_versions`
- `id` (BigIncrements, PK)
- `invitation_id` (BigInteger, FK ke `invitations.id`)
- `version_number` (Integer, incrementing)
- `snapshot_json` (JSONB / JSON, snapshot data saat tombol Publish ditekan)
- `published_at` (Timestamp)

### D. Tabel `guests`
- `id` (BigIncrements, PK)
- `invitation_id` (BigInteger, FK ke `invitations.id`)
- `name` (String, nama tamu, misal: *"Bpk. Joko Widodo & Rekan"*)
- `slug` (String, slug unik untuk URL personal: `?to=nama-tamu`)
- `category` (String, misal: *"Keluarga"*, *"Sahabat"*, *"VIP"*, *"Umum"*)
- `phone` (Nullable String, untuk pengiriman via WhatsApp Web)
- `pax` (Integer, kuota undangan)
- `attendance_status` (Enum: `'pending'`, `'attending'`, `'declined'`)
- `qr_code_hash` (String, hash unik untuk scan QR resepsionis di meja penerima tamu)

---

## 3. Kontrak Endpoint REST / AJAX

| Method | Endpoint | Deskripsi | Autentikasi |
|---|---|---|---|
| `POST` | `/api/invitations/{id}/design` | Menyimpan perubahan desain (`design_json`) secara autosave | `auth` (Hanya pemilik) |
| `POST` | `/api/invitations/{id}/publish` | Menerbitkan undangan ke versi publik & mengembalikan `public_url` | `auth` (Hanya pemilik) |
| `POST` | `/api/upload` | Mengunggah foto mempelai, galeri, barcode QRIS, atau audio MP3 | `auth` |
| `POST` | `/api/guests/{id}/checkin` | Memvalidasi kehadiran tamu via QR Scanner resepsionis | `auth` / Resepsionis |
| `POST` | `/api/sandbox/simulate-payment/{orderId}` | Menyimulasikan pelunasan pembayaran paket premium | `auth` |
| `POST` | `/i/{slug}/rsvp` | Tamu mengirimkan konfirmasi kehadiran & jumlah pax | Publik |
| `POST` | `/i/{slug}/wish` | Tamu mengirimkan ucapan selamat & doa restu | Publik |

---

## 4. Mekanisme Rute Publik Undangan (`/i/{slug}`)

Halaman publik undangan di-handle oleh `PublicInvitationController`:

1. **Akses Publik Normal (`/i/{slug}`)**:
   - Sistem mencari data undangan berdasarkan `slug`.
   - Jika status masih `draft`, sistem menampilkan halaman pemberitahuan bahwa undangan belum diterbitkan.
   - Jika status `published`, sistem membaca data dari snapshot aktif `published_versions`.
2. **Akses Undangan Khusus Tamu (`/i/{slug}?to={guest_slug}`)**:
   - Sistem mencocokkan `guest_slug` dengan tabel `guests`.
   - Mengisi otomatis nama tamu pada sampul pembuka, amplop nama, dan form RSVP.
3. **Akses Mode Pratinjau Editor (`/i/{slug}?preview=1`)**:
   - Hanya dapat diakses oleh pemilik undangan atau admin yang sedang login.
   - Mengabaikan status publikasi dan langsung memuat data draf dari `invitation_designs`.
   - Mengaktifkan listener `postMessage` untuk menerima pembaruan instan dari panel editor.

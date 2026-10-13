# Architecture Specification — Platform Undangan Digital SaaS

Platform SaaS multi-pelanggan untuk pembuatan, kustomisasi visual drag-and-drop, publikasi, dan pengelolaan undangan digital pernikahan dengan monetisasi freemium/premium.

---

## 1. High-Level Architecture Overview

Aplikasi mengadopsi arsitektur **Modular Monolith** berbasis **Laravel 11** dan **Vue 3 (Inertia.js)** dengan pembagian domain yang tegas:

```
[ Web Browser / Client Devices ]
       │              │
       ▼              ▼
[ Public Visitor ] [ Authenticated User / Admin ]
  (Guest/RSVP)       (Dashboard, Editor, Billing)
       │              │
       └──────┬───────┘
              │ HTTPS (Inertia.js / JSON API / Blade)
              ▼
    ┌──────────────────────────────────────────────┐
    │          Laravel 11 Modular Monolith          │
    │                                              │
    │  ┌────────────────┐     ┌────────────────┐   │
    │  │ Authentication │     │ Authorization  │   │
    │  │  & Rate Limit  │     │   (Policies)   │   │
    │  └────────────────┘     └────────────────┘   │
    │                                              │
    │  ┌────────────────────────────────────────┐  │
    │  │             Domain Modules             │  │
    │  │  • Accounts        • Templates         │  │
    │  │  • Invitations     • Design Editor     │  │
    │  │  • Media           • Events (Akad/etc) │  │
    │  │  • Guests & RSVP   • Guestbook         │  │
    │  │  • Billing & Plans • Admin & Audit     │  │
    │  └────────────────────────────────────────┘  │
    │                        │                     │
    │  ┌─────────────────────┴──────────────────┐  │
    │  │   Payment Gateway Adapter & Webhooks   │  │
    │  └────────────────────────────────────────┘  │
    └───────────────────────┬──────────────────────┘
                            │
              ┌─────────────┴─────────────┐
              ▼                           ▼
     [ Relational Database ]     [ File Storage ]
      (PostgreSQL / SQLite)       (Local / S3)
```

---

## 2. Layer & Domain Decomposition

### 2.1 Frontend Layer (Vue 3 + Inertia.js + Tailwind CSS)
- **Framework**: Vue 3 (Composition API `<script setup lang="ts">`) + TypeScript.
- **Routing & State**: Inertia.js (menghilangkan duplikasi state server-client; SSR-ready).
- **Styling**: Tailwind CSS dengan theme responsif dan elegan.
- **Visual Editor Engine**: Fabric.js canvas dengan wrapper reaktif Vue:
  - Hybrid model: Section organizer (Cover, Couple, Event, Gallery, RSVP, Story) digabungkan dengan visual element canvas (Text, Image, Ornament, Shapes).
  - State management editor: Undo/Redo stack, autosave debounced, layer ordering, element transform.

### 2.2 Backend Application Layer (Laravel 11)
- **Controllers & Inertia Actions**: Thin controllers yang mendelegasikan logika ke Services/Actions.
- **Form Requests**: Validasi ketat untuk input, termasuk schema JSON desain dan unggahan file.
- **Policies**: Enforce tenant ownership di setiap level model (`InvitationPolicy`, `GuestPolicy`, dll.) untuk mencegah IDOR (Insecure Direct Object Reference).
- **Service Layer**:
  - `InvitationPublishingService`: Menangani snapshot atomik desain dan acara ke `invitation_versions`.
  - `BillingEntitlementService`: Memeriksa apakah user berhak menggunakan template premium atau melebihi kuota tamu/fitur.
  - `PaymentGatewayManager`: Mengabstraksi penyedia pembayaran (Midtrans/Xendit/Sandbox Mock).

---

## 3. Data & Storage Strategy

1. **Design Versioning**:
   - Draft desain disimpan di `invitation_designs` (`design_json`); acara draft disimpan di `events`.
   - Setiap kali user melakukan **Publish**, snapshot desain dan acara disalin ke `invitation_versions` dengan nomor versi inkremental (`version_number`). Autosave tidak mengubah snapshot yang aktif.
   - Pointer `published_version_id` pada tabel `invitations` diperbarui secara atomik dalam database transaction.
2. **Public Invitation Rendering**:
   - Halaman publik `/i/{slug}` membaca snapshot aktif. Versi lama yang belum memiliki `events_json` memakai data acara lama sebagai fallback.
   - Draft yang belum dipublikasikan tidak pernah diekspos ke pengunjung publik.
3. **Media Storage**:
   - Berkas media (foto pengantin, galeri, musik) diunggah melalui endpoint terotorisasi.
   - File tersimpan pada disk `public` di `storage/app/public`; deployment perlu memasang volume persisten atau object storage agar file bertahan saat container diganti.
   - Validasi ketat: MIME type (`image/jpeg`, `image/png`, `image/webp`, `audio/mpeg`), max size 5MB untuk gambar.

---

## 4. Keamanan & Keandalan

- **Tenant Isolation**: Setiap query resource pengguna disaring berdasarkan `user_id` pemilik melalui Policy & Global Scope.
- **XSS Prevention**: Teks dari pengguna selalu di-escape oleh Vue template engine; Canvas Fabric.js hanya merender primitif canvas tanpa menyisipkan node HTML sembarangan.
- **Anti-Spam & Moderasi**:
  - Endpoint publik RSVP dan Buku Tamu dibatasi rate limiting; undangan harus terbit dan belum kedaluwarsa.
  - Ucapan pengunjung menunggu moderasi sebelum ditampilkan di halaman publik.
  - Guest link menggunakan token acak terenkripsi/hash.
- **Payment & Webhook Idempotency**:
  - Webhook diverifikasi menggunakan signature hash.
  - Setiap webhook mencatat `provider_event_id` unik pada tabel `webhook_events` untuk mencegah replay attack.
  - Transisi status transaksi diverifikasi secara ketat (Pending -> Paid / Expired).

---

## 5. Performance & Caching

- Asset statis (CSS/JS) di-bundle dan di-cache melalui Vite version hashing.
- Query undangan publik dioptimalkan dengan eager loading relasi yang relevan (`events`, `approvedGuestbooks`, `publishedVersion`).
- Indexing pada kolom penting: `slug` (unique), `user_id`, `invitation_id`, `published_version_id`, `token`.

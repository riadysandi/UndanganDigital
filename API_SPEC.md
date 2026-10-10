# API & Route Specification — Platform Undangan Digital

Dokumen ini mendefinisikan spesifikasi rute, endpoint, payload, dan respon untuk Platform Undangan Digital SaaS sesuai Bagian 7 PRD.

---

## 1. Authentication & Session

| Method | Endpoint | Akses | Keterangan |
|---|---|---|---|
| `POST` | `/register` | Publik | Mendaftarkan akun baru (nama, email, password) |
| `POST` | `/login` | Publik | Autentikasi pengguna, rate limited |
| `POST` | `/logout` | Auth | Menghapus sesi pengguna |
| `GET`  | `/user` | Auth | Mengambil informasi profil & status langganan |

---

## 2. Templates

| Method | Endpoint | Akses | Keterangan |
|---|---|---|---|
| `GET` | `/api/templates` | Publik/Auth | Mengambil daftar template aktif (filter kategori & free/premium) |
| `GET` | `/api/templates/{slug}` | Publik/Auth | Mengambil detail spesifik dan preview template |

---

## 3. Customer Invitations & Management

| Method | Endpoint | Akses | Keterangan |
|---|---|---|---|
| `GET` | `/dashboard` (Inertia) | Auth | Menampilkan ringkasan dan daftar undangan milik user |
| `POST` | `/api/invitations` | Auth | Membuat undangan baru dari template terpilih |
| `GET` | `/api/invitations/{id}` | Auth (Owner) | Mengambil data undangan lengkap (desain, acara, tamu) |
| `PATCH`| `/api/invitations/{id}` | Auth (Owner) | Memperbarui judul, slug, atau metadata undangan |
| `DELETE`|`/api/invitations/{id}` | Auth (Owner) | Menghapus undangan beserta relasinya |

---

## 4. Visual Canvas Editor & Design

| Method | Endpoint | Akses | Keterangan |
|---|---|---|---|
| `GET` | `/invitations/{id}/editor` | Auth (Owner) | Membuka antarmuka Visual Canvas Editor (Inertia) |
| `GET` | `/api/invitations/{id}/design` | Auth (Owner) | Mengambil draft JSON desain terkini |
| `PUT` | `/api/invitations/{id}/design` | Auth (Owner) | Autosave / Update JSON desain (divalidasi Schema v1) |

### Format JSON Desain (Schema Version 1):
```json
{
  "schemaVersion": 1,
  "canvas": {
    "width": 1080,
    "height": 1920,
    "background": "#FDFBF7",
    "primaryColor": "#5D6B50",
    "fontFamily": "Playfair Display"
  },
  "sections": [
    {
      "id": "cover",
      "type": "cover",
      "visible": true,
      "order": 1,
      "elements": [
        {
          "id": "heading-title",
          "type": "text",
          "x": 140,
          "y": 300,
          "width": 800,
          "height": 120,
          "text": "The Wedding Of",
          "fontSize": 36,
          "fontFamily": "Playfair Display",
          "color": "#7D8C71",
          "align": "center"
        },
        {
          "id": "couple-names",
          "type": "text",
          "x": 100,
          "y": 480,
          "width": 880,
          "height": 180,
          "text": "Andi & Siti",
          "fontSize": 64,
          "fontFamily": "Great Vibes",
          "color": "#2C3E50",
          "align": "center"
        }
      ]
    }
  ]
}
```

---

## 5. Publishing & Public Routes

| Method | Endpoint | Akses | Keterangan |
|---|---|---|---|
| `POST` | `/api/invitations/{id}/publish` | Auth (Owner) | Membuat snapshot immutable versi baru & mempublikasikan |
| `POST` | `/api/invitations/{id}/unpublish`| Auth (Owner) | Mengubah status menjadi unpublish / draft |
| `GET`  | `/i/{slug}` | Publik | Menampilkan undangan digital interaktif yang telah dipublikasikan |

---

## 6. Events, Guests & RSVP

| Method | Endpoint | Akses | Keterangan |
|---|---|---|---|
| `POST`   | `/api/invitations/{id}/events` | Auth (Owner) | Menambah/memperbarui jadwal acara (Akad / Resepsi) |
| `GET`    | `/api/invitations/{id}/guests` | Auth (Owner) | Mengambil daftar tamu, token, dan status RSVP |
| `POST`   | `/api/invitations/{id}/guests` | Auth (Owner) | Menambah data tamu undangan baru |
| `DELETE` | `/api/invitations/{id}/guests/{guestId}` | Auth (Owner) | Menghapus tamu |
| `POST`   | `/api/public/{slug}/rsvp` | Publik | Tamu mengirimkan konfirmasi kehadiran (rate-limited) |
| `POST`   | `/api/public/{slug}/guestbook` | Publik | Tamu mengirimkan ucapan & doa restu |
| `PATCH`  | `/api/invitations/{id}/guestbook/{entryId}` | Auth (Owner) | Moderasi ucapan (approved/rejected) |

---

## 7. Media & Uploads

| Method | Endpoint | Akses | Keterangan |
|---|---|---|---|
| `POST`   | `/api/invitations/{id}/media` | Auth (Owner) | Mengunggah foto atau musik (validasi mime & size) |
| `DELETE` | `/api/invitations/{id}/media/{mediaId}` | Auth (Owner) | Menghapus file media |

---

## 8. Billing, Orders & Webhooks

| Method | Endpoint | Akses | Keterangan |
|---|---|---|---|
| `GET`  | `/pricing` | Publik/Auth | Menampilkan katalog paket (Free, Gold, Platinum) |
| `POST` | `/api/checkout` | Auth | Membuat order pembelian paket premium |
| `GET`  | `/api/orders/{id}` | Auth (Owner) | Memeriksa status pembayaran order |
| `POST` | `/api/sandbox/simulate-payment/{orderId}` | Auth (Dev/Sandbox) | Simulasi pembayaran berhasil secara instan |
| `POST` | `/api/webhooks/{provider}` | Gateway | Webhook receiver dengan signature verification & idempotency |

---

## 9. Administration

| Method | Endpoint | Akses | Keterangan |
|---|---|---|---|
| `GET`   | `/admin/dashboard` | Admin | Ringkasan statistik pengguna, order, dan undangan aktif |
| `GET`   | `/admin/users` | Admin | Manajemen akun pengguna |
| `GET`   | `/admin/templates` | Admin | Kelola template katalog, status premium, dan thumbnail |
| `POST`  | `/admin/templates` | Admin | Menambah template baru |
| `GET`   | `/admin/orders` | Admin | Melihat daftar seluruh transaksi dan status webhook |
| `POST`  | `/admin/guestbook/moderate` | Admin | Moderasi global ucapan buku tamu |

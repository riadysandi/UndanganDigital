# Route Specification — Platform Undangan Digital

Daftar di bawah mencatat endpoint yang benar-benar terdaftar di `routes/web.php`. Halaman katalog dan daftar template dikirim melalui Inertia, bukan endpoint JSON `/api/templates`.

---

## 1. Authentication & Session

| Method | Endpoint | Akses | Keterangan |
|---|---|---|---|
| `POST` | `/register` | Publik | Mendaftarkan akun baru (nama, email, password) |
| `GET` | `/login` | Publik | Form login terpadu untuk admin dan customer |
| `POST` | `/login` | Publik | Autentikasi pengguna, rate limited |
| `POST` | `/logout` | Auth | Menghapus sesi pengguna |
| `GET` | `/admin/login` | Publik | Alias yang mengarah ke `/login` |

---

## 2. Templates

Template aktif dimuat oleh halaman Inertia (`/`, `/invitations/create`) dari model `Template`.

---

## 3. Customer Invitations & Management

| Method | Endpoint | Akses | Keterangan |
|---|---|---|---|
| `GET` | `/dashboard` (Inertia) | Auth | Menampilkan ringkasan dan daftar undangan milik user |
| `GET` | `/invitations/create` | Auth | Memilih template dan memulai undangan |
| `POST` | `/invitations` | Auth | Membuat undangan baru dari template terpilih |
| `GET` | `/invitations/{invitation}` | Auth (Owner) | Menampilkan detail undangan |
| `PATCH`| `/invitations/{invitation}` | Auth (Owner) | Memperbarui judul dan slug |
| `DELETE`|`/invitations/{invitation}` | Auth (Owner) | Menghapus undangan beserta relasinya |

---

## 4. Visual Canvas Editor & Design

| Method | Endpoint | Akses | Keterangan |
|---|---|---|---|
| `GET` | `/invitations/{invitation}/editor` | Auth (Owner) | Membuka antarmuka Visual Canvas Editor (Inertia) |
| `GET` | `/api/invitations/{invitation}/design` | Auth (Owner) | Mengambil draft desain dan acara terkini |
| `PUT` | `/api/invitations/{invitation}/design` | Auth (Owner) | Autosave draft desain dan memperbarui acara yang sudah ada |

Autosave hanya mengubah draft. Desain memakai `schemaVersion`, `canvas`, `theme`, `musicUrl`, dan `sections`; detail kontrak ada di [panduan sistem tema](../02_TEMPLATE_AND_THEME_SYSTEM.md).

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
| `POST` | `/api/invitations/{invitation}/publish` | Auth (Owner) | Membuat snapshot desain dan acara versi baru & mempublikasikan |
| `POST` | `/api/invitations/{invitation}/unpublish`| Auth (Owner) | Mengubah status menjadi draft |
| `GET`  | `/i/{slug}` | Publik | Menampilkan undangan digital interaktif yang telah dipublikasikan |

---

## 6. Events, Guests & RSVP

| Method | Endpoint | Akses | Keterangan |
|---|---|---|---|
| `GET`    | `/invitations/{invitation}/guests` | Auth (Owner) | Mengambil daftar tamu, RSVP, dan ucapan |
| `POST`   | `/api/invitations/{invitation}/guests` | Auth (Owner) | Menambah tamu |
| `POST`   | `/api/invitations/{invitation}/guests/import` | Auth (Owner) | Impor tamu CSV atau teks |
| `DELETE` | `/api/invitations/{invitation}/guests/{guest}` | Auth (Owner) | Menghapus tamu |
| `POST` | `/api/invitations/{invitation}/guests/check-in` | Auth (Owner) | Check-in tamu berdasarkan token, nama, telepon, atau ID |
| `POST`   | `/api/public/{slug}/rsvp` | Publik | RSVP untuk undangan terbit dan belum kedaluwarsa (rate-limited) |
| `POST`   | `/api/public/{slug}/guestbook` | Publik | Kirim ucapan dalam status `pending` (rate-limited) |
| `PATCH`  | `/api/invitations/{invitation}/guestbook/{entryId}` | Auth (Owner) | Moderasi (`approved`, `pending`, `rejected`) |

---

## 7. Media & Uploads

| Method | Endpoint | Akses | Keterangan |
|---|---|---|---|
| `POST`   | `/api/invitations/{invitation}/upload` | Auth (Owner) | Unggah foto atau audio, maksimal 15 MB |

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
| `GET`   | `/admin/users` | Admin | Melihat akun pengguna |
| `GET`   | `/admin/templates` | Admin | Melihat template katalog |
| `POST`  | `/admin/templates/{template}/toggle` | Admin | Mengubah status aktif template |
| `GET`   | `/admin/orders` | Admin | Melihat transaksi |
| `GET`   | `/admin/service-requests` | Admin | Melihat permintaan layanan |
| `PATCH` | `/admin/service-requests/{serviceRequest}` | Admin | Memperbarui status permintaan layanan |
| `GET`   | `/admin/audit-logs` | Admin | Melihat catatan audit |

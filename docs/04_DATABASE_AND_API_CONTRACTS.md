# 04 - Kontrak Database dan Endpoint

Dokumen ini mengikuti migration, model, dan `routes/web.php` yang berjalan saat ini. Kontrak pembayaran tidak dibahas di sini.

## Relasi utama

```text
User
├── Invitations
│   ├── InvitationDesign (draft design_json)
│   ├── InvitationVersion (published design_json + events_json snapshots)
│   ├── Events (draft/current event data)
│   ├── Guests ── RSVP
│   └── GuestbookEntries (pending/approved/rejected)
└── Orders / subscriptions
```

Role akun yang dipakai saat ini adalah `admin` dan `customer`. Policy undangan membatasi akses pemilik; admin dapat mengelola semua undangan.

## Undangan dan versi

- `invitations`: pemilik, template, judul, slug, status, pointer `published_version_id`, dan masa berlaku.
- `invitation_designs`: JSON draft dan `schema_version`.
- `invitation_versions`: versi berurutan dengan `design_json`, `schema_version`, `events_json`, pembuat, dan waktu publikasi. `events_json` boleh null untuk versi lama.
- `events`: jadwal dan lokasi acara draft/current.
- `guests`: nama, token undangan personal, nomor telepon, dan kuota.
- `rsvps`: respons kehadiran tamu.
- `guestbook_entries`: ucapan dengan status moderasi.

Autosave menggunakan `PUT /api/invitations/{invitation}/design`. Saat Publish, service membuat snapshot desain dan acara baru, lalu mengganti pointer versi publik. Autosave berikutnya hanya memperbarui draft. Versi lama tanpa snapshot acara menggunakan relasi `events` sebagai fallback.

## Route utama

| Method | Route | Fungsi | Akses |
|---|---|---|---|
| `GET` | `/login` | Login admin/customer satu pintu | Guest |
| `GET` | `/admin/login` | Alias menuju `/login` | Guest |
| `GET/POST` | `/register` | Registrasi customer | Guest |
| `GET` | `/invitations/{invitation}/editor` | Editor visual | Pemilik/admin |
| `GET/PUT` | `/api/invitations/{invitation}/design` | Baca/simpan draft desain dan acara | Auth + policy |
| `POST` | `/api/invitations/{invitation}/upload` | Unggah media undangan | Auth + policy |
| `POST` | `/api/invitations/{invitation}/publish` | Buat snapshot versi publik | Auth + policy |
| `POST` | `/api/invitations/{invitation}/unpublish` | Kembalikan status ke draft | Auth + policy |
| `GET` | `/i/{slug}` | Lihat undangan publik | Publik; versi harus terbit |
| `POST` | `/api/public/{slug}/rsvp` | Kirim RSVP | Publik; undangan aktif dan belum kedaluwarsa |
| `POST` | `/api/public/{slug}/guestbook` | Kirim ucapan | Publik; undangan aktif dan belum kedaluwarsa |
| `GET/POST` | `/invitations/{invitation}/guests` | Kelola dan check-in tamu | Auth + policy |

RSVP membatasi nilai status dan jumlah hadir. Ucapan dari RSVP maupun endpoint guestbook disimpan dengan status `pending`; pengelola dapat mengubah status moderasi melalui route guestbook admin/pemilik. Halaman publik hanya menerima kiriman untuk undangan yang masih terbit dan belum kedaluwarsa, dan hanya menampilkan ucapan `approved`.

## Mode preview editor

`/i/{slug}?preview=1` menampilkan draft hanya untuk pemilik undangan atau admin yang login. Preview membaca acara draft dan mendengarkan pesan `LIVE_UPDATE_DESIGN` dari iframe editor. Mode preview tidak mengizinkan endpoint RSVP/guestbook menerima kiriman karena endpoint tersebut mensyaratkan undangan publik aktif.

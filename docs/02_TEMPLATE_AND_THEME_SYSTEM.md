# 02 - Sistem Template, Editor, dan Kontrak Desain

Dokumen ini mengikuti implementasi saat ini. Kontrak aktif `design_json` adalah schema versi 1 yang dibuat oleh `DatabaseSeeder` dan diedit melalui `resources/js/pages/Editor/Index.vue`.

## Aturan kompatibilitas

1. Perubahan skema harus aditif dan mempertahankan fallback agar undangan lama tetap bisa dibuka.
2. Preview editor wajib memakai `postMessage` `LIVE_UPDATE_DESIGN`; editor mengirim objek desain penuh tanpa reload iframe.
3. Draft dan versi publik terpisah. Autosave tidak boleh mengubah snapshot versi yang sedang diterbitkan.
4. Publikasi menyalin desain dan acara ke versi baru. RSVP dan ucapan tetap menjadi data hidup; ucapan baru menunggu moderasi.

## Kontrak `design_json` saat ini

Template menyimpan objek dengan bentuk utama berikut. Setiap template dapat memiliki section dan data konten tambahan yang dipakai editor.

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
  "theme": "modern",
  "musicUrl": "https://example.com/music.ogg",
  "sections": [
    { "id": "cover", "type": "cover", "title": "The Wedding Of", "visible": true, "order": 1 },
    { "id": "couple", "type": "couple", "visible": true, "order": 2 },
    { "id": "events", "type": "events", "visible": true, "order": 3 },
    { "id": "gallery", "type": "gallery", "photos": [], "visible": true, "order": 4 },
    { "id": "rsvp", "type": "rsvp", "visible": true, "order": 5 }
  ]
}
```

Saat menyimpan, editor mengirim `design` beserta `schemaVersion` ke `PUT /api/invitations/{invitation}/design`. Backend membersihkan tag pada teks elemen, menyimpan draft, dan memperbarui acara draft bila dikirim. Properti tambahan yang sudah ada—seperti `content`, `gift`, dan data section—harus dipertahankan.

## Sinkronisasi preview langsung

Pengirim berada di `resources/js/pages/Editor/Index.vue`; penerima berada di `resources/js/pages/Public/InvitationView.vue`.

```ts
previewIframeRef.value.contentWindow.postMessage({
  type: 'LIVE_UPDATE_DESIGN',
  design: structuredClone(designData),
}, '*');
```

Halaman preview menerima pesan `LIVE_UPDATE_DESIGN` dan mengganti desain tampilannya. Jangan mengganti nama event atau memuat ulang iframe saat pengguna mengedit.

## Draft dan publikasi

- Draft berada di `invitation_designs` dan tabel `events`.
- `InvitationPublishingService` membuat `InvitationVersion` baru yang memuat `design_json`, `schema_version`, dan `events_json` saat tombol Publish digunakan.
- Pengunjung membaca snapshot yang ditunjuk `invitations.published_version_id`. Autosave setelah publikasi tidak mengubah versi aktif; publikasikan lagi untuk menampilkan perubahan.
- Versi lama yang belum memiliki `events_json` tetap memakai acara lama dari tabel `events` sebagai fallback.
- RSVP dan buku tamu hanya menerima kiriman untuk undangan terbit yang belum kedaluwarsa. Kiriman ucapan masuk status `pending`; hanya entri `approved` yang tampil ke pengunjung.

## Palet warna

Editor saat ini menyediakan preset berikut:

| Preset | ID | Primary | Secondary |
|---|---|---|---|
| Royal Gold | `gold` | `#D4AF37` | `#F3E5AB` |
| Rose Gold | `rose` | `#E0A9A5` | `#FDE8E7` |
| Emerald Jade | `emerald` | `#10B981` | `#A7F3D0` |
| Terracotta Warm | `terracotta` | `#C25E43` | `#F8D5C8` |
| Sapphire Navy | `sapphire` | `#38BDF8` | `#BAE6FD` |
| Burgundy Wine | `burgundy` | `#F43F5E` | `#FECDD3` |

Saat menambah pilihan, pertahankan properti desain yang sudah dipakai dan gunakan `<input type="color">` untuk warna bebas.

## Menambah template

Tambahkan template melalui seeder dengan `slug`, `name`, `category`, `default_design_json`, `schema_version`, dan status aktif/premium. Pastikan bentuk JSON dapat dibaca editor dan halaman publik, lalu tambahkan pengujian bila perilakunya baru.

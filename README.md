# UndanganDigital

Platform SaaS untuk membuat dan mengelola undangan pernikahan digital. Aplikasi menggunakan Laravel 11, Inertia, Vue 3, TypeScript, Tailwind CSS, dan database yang didukung Laravel (SQLite untuk pengembangan; PostgreSQL untuk deployment).

## Fitur utama

- Akun customer dan admin dengan login satu pintu di `/login`.
- Template dan editor visual dengan preview langsung.
- Draft terpisah dari snapshot versi undangan yang diterbitkan.
- Daftar tamu, RSVP, buku tamu dengan moderasi, dan check-in.
- Paket, layanan pembuatan undangan, dan panel admin.

## Menjalankan secara lokal

1. Salin `.env.example` menjadi `.env`, lalu isi `APP_KEY` dengan `php artisan key:generate`.
2. Pasang dependency PHP dan JavaScript dengan `composer install` dan `npm install`.
3. Jalankan migrasi dan data demo dengan `php artisan migrate --seed`.
4. Jalankan aplikasi dan Vite dengan `composer run dev`.

Seeder hanya membuat akun dan undangan demo pada environment `local` atau `testing`:

- Admin: `admin@undangan.id` / `password123`
- Customer: `pengantin@undangan.id` / `password123`

Jangan gunakan kredensial demo di produksi. Atur `ADMIN_EMAIL` dan `ADMIN_PASSWORD` (minimal 12 karakter) di environment produksi; `db:seed` akan membuat atau merotasi akun admin terpadu.

## Verifikasi perubahan

```sh
php artisan test
npm run build
```

Dokumen panduan ada di [`docs/`](docs/README.md); diagram, spesifikasi route, dan roadmap ada di [`docs/reference/`](docs/reference/). PRD sumber ada di `docs/source/`. Deployment memakai Docker; media pengguna harus disimpan pada storage persisten yang dikonfigurasi untuk environment deployment.

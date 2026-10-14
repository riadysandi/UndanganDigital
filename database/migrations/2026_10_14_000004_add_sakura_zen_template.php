<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $exists = DB::table('templates')->where('slug', 'sakura-zen-3d')->exists();
        if (!$exists) {
            $designSakura = [
                'schemaVersion' => 1,
                'theme' => [
                    'palette' => 'sakura-zen',
                    'isLight' => true,
                    'primaryColor' => '#D9777F',
                    'secondaryColor' => '#C23B22',
                    'bgColor' => '#FBF8F3',
                    'cardBgColor' => '#FFFFFF',
                    'envelopeBg' => '#F7F3E9',
                    'envelopePocketL' => '#EAE1D0',
                    'envelopePocketR' => '#DFD4C0',
                    'envelopePocketB' => '#F2ECE0',
                    'envelopeFlap' => '#F7F3E9',
                    'sealColor' => '#C23B22',
                    'textColor' => '#2D2424',
                    'fontFamily' => 'Cinzel, serif',
                    'goldAccent' => '#D4AF37',
                    'roseAccent' => '#E89CAE',
                    'sakuraPetals' => true,
                    'musicUrl' => '/audio/classic-romance.mp3',
                ],
                'canvas' => [
                    'width' => 1080,
                    'height' => 1920,
                    'background' => '#FBF8F3',
                    'primaryColor' => '#D9777F',
                    'fontFamily' => 'Cinzel, serif',
                ],
                'content' => [
                    'weddingTitle' => 'Japanese Zen & Sakura Harmony Wedding',
                    'weddingDate' => '2026-12-12',
                    'groomNickname' => 'Kenji',
                    'groomName' => 'Kenji Pratama, S.T.',
                    'groomParents' => 'Bpk. Ir. Bambang Pratama & Ibu Etsuko Tanaka',
                    'groomInstagram' => '@kenji.pratama',
                    'groomPhotoUrl' => 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?auto=format&fit=crop&w=400&q=80',
                    'brideNickname' => 'Ayumi',
                    'brideName' => 'Ayumi Larasati, B.Des.',
                    'brideParents' => 'Bpk. Hendra Gunawan & Ibu Rini Larasati',
                    'brideInstagram' => '@ayumilarasati',
                    'bridePhotoUrl' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=400&q=80',
                    'coverPhotoUrl' => 'https://images.unsplash.com/photo-1583939003579-730e3918a45a?auto=format&fit=crop&w=600&q=80',
                    'gallery' => [
                        'https://images.unsplash.com/photo-1583939003579-730e3918a45a?auto=format&fit=crop&w=600&q=80',
                        'https://images.unsplash.com/photo-1519741497674-611481863552?auto=format&fit=crop&w=600&q=80',
                        'https://images.unsplash.com/photo-1465495976277-4387d4b0b4c6?auto=format&fit=crop&w=600&q=80',
                        'https://images.unsplash.com/photo-1520854221256-17451cc331bf?auto=format&fit=crop&w=600&q=80',
                        'https://images.unsplash.com/photo-1511285560929-80b456fea0bc?auto=format&fit=crop&w=600&q=80',
                        'https://images.unsplash.com/photo-1519225421980-715cb0215aed?auto=format&fit=crop&w=600&q=80',
                    ],
                    'story' => [
                        [
                            'year' => '2021',
                            'title' => 'Pertemuan Pertama di Kyoto',
                            'description' => 'Takdir mempertemukan kami di kuil Fushimi Inari, berawal dari percakapan sederhana di tengah rintik hujan dan hembusan angin musim semi.',
                        ],
                        [
                            'year' => '2024',
                            'title' => 'Janji Setia di Bawah Sakura',
                            'description' => 'Di tepi Danau Biwa saat musim mekar sakura, kami mengikrarkan janji untuk melangkah bersama mengarungi setiap musim kehidupan.',
                        ],
                        [
                            'year' => '2026',
                            'title' => 'Menuju Ikatan Abadi (The Sacred Vows)',
                            'description' => 'Dengan penuh rasa syukur dan doa restu kedua orang tua, kami mengikat janji suci pernikahan dalam keharmonisan cinta abadi.',
                        ],
                    ],
                ],
                'events' => [
                    [
                        'id' => 1,
                        'name' => 'Akad Nikah / Pemberkatan Suci',
                        'starts_at' => '2026-12-12 08:30:00',
                        'ends_at' => '2026-12-12 10:30:00',
                        'timezone' => 'WIB',
                        'venue_name' => 'Kyoto Pavilion & Zen Glasshouse',
                        'address' => 'Jl. Taman Bunga Sakura No. 88, Kebayoran Baru, Jakarta Selatan',
                        'maps_url' => 'https://maps.google.com',
                    ],
                    [
                        'id' => 2,
                        'name' => 'Resepsi Kebun Zen & Grand Gala',
                        'starts_at' => '2026-12-12 11:30:00',
                        'ends_at' => '2026-12-12 14:30:00',
                        'timezone' => 'WIB',
                        'venue_name' => 'The Royal Bamboo Forest Ballroom',
                        'address' => 'Jl. Taman Bunga Sakura No. 88, Kebayoran Baru, Jakarta Selatan',
                        'maps_url' => 'https://maps.google.com',
                    ],
                ],
                'gift' => [
                    'enabled' => true,
                    'title' => 'Amplop Digital & Kado Hadiah',
                    'description' => 'Doa restu Anda adalah karunia terindah bagi kami. Namun jika memberi adalah ungkapan kasih, Anda dapat menyalurkannya melalui:',
                    'accounts' => [
                        [
                            'bank_name' => 'BCA',
                            'account_number' => '8830192847',
                            'account_holder' => 'Kenji Pratama',
                            'qr_image' => 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=BCA_KENJI_8830192847',
                        ],
                        [
                            'bank_name' => 'Bank Mandiri',
                            'account_number' => '1370019283741',
                            'account_holder' => 'Ayumi Larasati',
                            'qr_image' => 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=MANDIRI_AYUMI_1370019283741',
                        ],
                    ],
                ],
                'guestbook' => [
                    [
                        'id' => 1,
                        'guest_name' => 'Hiroshi & Keluarga Besar',
                        'message' => 'Omedetou gozaimasu Kenji & Ayumi! Semoga pernikahan kalian senantiasa dipenuhi ketenangan, kebahagiaan, dan keberkahan seperti indahnya bunga sakura di musim semi.',
                        'created_at' => '10 menit yang lalu',
                    ],
                    [
                        'id' => 2,
                        'guest_name' => 'Sahabat Arsitektur ITB & Todai',
                        'message' => 'Selamat menempuh hidup baru sahabat terbaik! Semoga cinta kalian abadi, langgeng sampai kakek nenek. See you on the big day!',
                        'created_at' => '30 menit yang lalu',
                    ],
                ],
            ];

            DB::table('templates')->insert([
                'slug' => 'sakura-zen-3d',
                'name' => 'Sakura (Japanese Zen & Sakura Garden 3D)',
                'category' => '3d',
                'thumbnail_path' => '/images/templates/sakura-original.png',
                'default_design_json' => json_encode($designSakura),
                'schema_version' => 1,
                'is_premium' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            DB::table('templates')
                ->where('slug', 'sakura-zen-3d')
                ->update([
                    'thumbnail_path' => '/images/templates/sakura-original.png',
                    'is_active' => true,
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('templates')->where('slug', 'sakura-zen-3d')->delete();
    }
};

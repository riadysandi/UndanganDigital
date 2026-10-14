<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Guest;
use App\Models\GuestbookEntry;
use App\Models\Invitation;
use App\Models\InvitationDesign;
use App\Models\InvitationVersion;
use App\Models\Plan;
use App\Models\PlanPrice;
use App\Models\Rsvp;
use App\Models\Template;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Demo accounts and invitations belong only in local/test databases.
        $customer = null;
        if (app()->environment(['local', 'testing'])) {
            User::firstOrCreate(
                ['email' => 'admin@undangan.id'],
                [
                    'name' => 'Administrator Undangan',
                    'password' => Hash::make('password123'),
                    'role' => 'admin',
                    'email_verified_at' => now(),
                ]
            );

            $customer = User::firstOrCreate(
                ['email' => 'pengantin@undangan.id'],
                [
                    'name' => 'Dimas & Anisa',
                    'password' => Hash::make('password123'),
                    'role' => 'customer',
                    'phone_number' => '081234567890',
                    'email_verified_at' => now(),
                ]
            );
        } elseif (app()->isProduction() && env('ADMIN_EMAIL') && env('ADMIN_PASSWORD')) {
            if (Str::length(env('ADMIN_PASSWORD')) < 12) {
                throw new \RuntimeException('ADMIN_PASSWORD must contain at least 12 characters in production.');
            }

            User::updateOrCreate(
                ['email' => env('ADMIN_EMAIL')],
                [
                    'name' => 'Administrator Undangan',
                    'password' => Hash::make(env('ADMIN_PASSWORD')),
                    'role' => 'admin',
                    'email_verified_at' => now(),
                ]
            );
        }

        // 2. Plans
        $planFree = Plan::firstOrCreate(
            ['code' => 'free'],
            [
                'name' => 'Paket Gratis',
                'description' => 'Cocok untuk mencoba fitur dasar undangan digital pernikahan.',
                'features_json' => [
                    'max_guests' => 50,
                    'premium_templates' => false,
                    'remove_watermark' => false,
                    'background_music' => false,
                    'photo_gallery_limit' => 3,
                    'active_months' => 1,
                ],
                'is_active' => true,
            ]
        );

        PlanPrice::firstOrCreate(
            ['plan_id' => $planFree->id, 'billing_interval' => 'once'],
            [
                'amount' => 0,
                'currency' => 'IDR',
            ]
        );

        $planGold = Plan::firstOrCreate(
            ['code' => 'gold'],
            [
                'name' => 'Premium Gold',
                'description' => 'Pilihan terpopuler. Akses template premium, tanpa watermark, dan musik latar.',
                'features_json' => [
                    'max_guests' => 500,
                    'premium_templates' => true,
                    'remove_watermark' => true,
                    'background_music' => true,
                    'photo_gallery_limit' => 10,
                    'active_months' => 12,
                ],
                'is_active' => true,
            ]
        );

        PlanPrice::firstOrCreate(
            ['plan_id' => $planGold->id, 'billing_interval' => 'once'],
            [
                'amount' => 149000,
                'currency' => 'IDR',
            ]
        );

        $planPlatinum = Plan::firstOrCreate(
            ['code' => 'platinum'],
            [
                'name' => 'Exclusive Platinum',
                'description' => 'Fitur VIP lengkap, kuota tamu tanpa batas, dan prioritas dukungan.',
                'features_json' => [
                    'max_guests' => 5000,
                    'premium_templates' => true,
                    'remove_watermark' => true,
                    'background_music' => true,
                    'photo_gallery_limit' => 30,
                    'active_months' => 12,
                    'vip_support' => true,
                ],
                'is_active' => true,
            ]
        );

        PlanPrice::firstOrCreate(
            ['plan_id' => $planPlatinum->id, 'billing_interval' => 'once'],
            [
                'amount' => 299000,
                'currency' => 'IDR',
            ]
        );

        // 3. Templates with Default Design JSON Schema v1
        $designModern = [
            'schemaVersion' => 1,
            'canvas' => [
                'width' => 1080,
                'height' => 1920,
                'background' => '#FDFBF7',
                'primaryColor' => '#5D6B50',
                'fontFamily' => 'Playfair Display',
            ],
            'theme' => 'modern',
            'musicUrl' => 'https://actions.google.com/sounds/v1/ambient/relaxing_music.ogg',
            'sections' => [
                [
                    'id' => 'cover',
                    'type' => 'cover',
                    'title' => 'The Wedding Of',
                    'groomName' => 'Dimas Prasetyo',
                    'brideName' => 'Anisa Rahmawati',
                    'weddingDate' => '2026-12-28',
                    'coverImage' => 'https://images.unsplash.com/photo-1519741497674-611481863552?auto=format&fit=crop&w=1200&q=80',
                    'visible' => true,
                    'order' => 1,
                ],
                [
                    'id' => 'couple',
                    'type' => 'couple',
                    'title' => 'Mempelai Pengantin',
                    'groom' => [
                        'name' => 'Dimas Prasetyo, S.Kom',
                        'parents' => 'Putra pertama dari Bpk. Bambang Prasetyo & Ibu Sri Wahyuni',
                        'photo' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=600&q=80',
                        'instagram' => '@dimas_prasetyo',
                    ],
                    'bride' => [
                        'name' => 'Anisa Rahmawati, S.E',
                        'parents' => 'Putri kedua dari Bpk. H. Rahmat Hidayat & Ibu Siti Aminah',
                        'photo' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=600&q=80',
                        'instagram' => '@anisa_rahma',
                    ],
                    'quote' => 'Dan di antara tanda-tanda kekuasaan-Nya ialah Dia menciptakan untukmu pasangan-pasangan dari jenismu sendiri, supaya kamu cenderung dan merasa tenteram kepadanya, dan dijadikan-Nya diantaramu rasa kasih dan sayang.',
                    'quoteSource' => 'QS. Ar-Rum: 21',
                    'visible' => true,
                    'order' => 2,
                ],
                [
                    'id' => 'events',
                    'type' => 'events',
                    'title' => 'Agenda Bahagia',
                    'visible' => true,
                    'order' => 3,
                ],
                [
                    'id' => 'gallery',
                    'type' => 'gallery',
                    'title' => 'Momen Kebersamaan',
                    'photos' => [
                        'https://images.unsplash.com/photo-1511285560929-80b456fea0bc?auto=format&fit=crop&w=800&q=80',
                        'https://images.unsplash.com/photo-1583939003579-730e3918a45a?auto=format&fit=crop&w=800&q=80',
                        'https://images.unsplash.com/photo-1520854221256-17451cc331bf?auto=format&fit=crop&w=800&q=80',
                        'https://images.unsplash.com/photo-1465495976277-4387d4b0b4c6?auto=format&fit=crop&w=800&q=80',
                    ],
                    'visible' => true,
                    'order' => 4,
                ],
                [
                    'id' => 'rsvp',
                    'type' => 'rsvp',
                    'title' => 'Konfirmasi Kehadiran & Doa Restu',
                    'visible' => true,
                    'order' => 5,
                ],
            ],
        ];

        // -------------------------------------------------------------
        // HELPER TO CONSTRUCT IMMERSIVE, DISTINCT TEMPLATE DESIGNS
        // -------------------------------------------------------------
        $makeDesign = function (
            array $theme,
            array $content,
            array $events,
            array $giftAccounts,
            array $guestbook,
            string $musicUrl = '/audio/romantic-wedding.mp3'
        ) {
            return [
                'schemaVersion' => 1,
                'theme' => $theme,
                'canvas' => [
                    'width' => 1080,
                    'height' => 1920,
                    'background' => $theme['bgColor'],
                    'primaryColor' => $theme['primaryColor'],
                    'fontFamily' => $theme['fontFamily'],
                ],
                'content' => $content,
                'events' => $events,
                'gift' => [
                    'enabled' => true,
                    'title' => 'Amplop Digital & Kado Hadiah',
                    'description' => 'Doa restu Anda adalah karunia terindah bagi kami. Namun jika memberi adalah ungkapan kasih, Anda dapat menyalurkannya melalui:',
                    'accounts' => $giftAccounts,
                    'physical_gift' => [
                        'enabled' => true,
                        'receiver_name' => $content['weddingTitle'],
                        'phone' => '0812-3456-7890',
                        'address' => 'Jl. Aster Bahagia No. 88, Kebayoran Baru, Jakarta Selatan',
                    ],
                ],
                'guestbook' => $guestbook,
                'musicUrl' => $musicUrl,
                'is_3d' => true,
            ];
        };

        // 1. NINGRUM (Fairytale & Vintage 3D) - Javanese Royal Elegance & Antique Gold
        $designNingrum = $makeDesign(
            theme: [
                'palette' => 'ningrum',
                'isLight' => false,
                'primaryColor' => '#D4AF37',
                'secondaryColor' => '#FBEFD3',
                'bgColor' => '#1A1016',
                'cardBgColor' => '#271622',
                'envelopeBg' => '#2E1828',
                'envelopePocketL' => '#241320',
                'envelopePocketR' => '#1E0E1A',
                'envelopePocketB' => '#2A1524',
                'envelopeFlap' => '#331A2C',
                'sealColor' => '#8A1C3C',
                'sealSecondary' => '#D4AF37',
                'textColor' => '#FFF9F2',
                'fontFamily' => 'Cinzel',
                'fallingPetals' => true,
                'petalColors' => ['#FCE7F3', '#F472B6', '#D4AF37'],
            ],
            content: [
                'coverBadge' => 'Fairytale Royal 3D Edition',
                'coverSubtitle' => 'Serat Palakrama & The Royal Fairytale',
                'weddingTitle' => 'Raden Arya & Putri Ningrum',
                'weddingDateText' => 'Sabtu, 14 November 2026',
                'coverImage' => '/images/templates/ningrum/couple-3d.gif',
                'quote' => 'Mugi Gusti paring berkah ingkang kathah, nyawijekaken katresnan luhur ing salebeting bebrayan agung ingkang langgeng.',
                'quoteSource' => 'Serat Wulangreh',
                'groom' => [
                    'nickname' => 'Arya',
                    'fullName' => 'Raden Mas Arya Wicaksana, S.T.',
                    'parents' => 'Putra sulung dari K.R.T. Bambang Wicaksono & R.Ay. Sri Handayani',
                    'photo' => 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?auto=format&fit=crop&w=600&q=80',
                    'instagram' => '@arya_wicaksana',
                ],
                'bride' => [
                    'nickname' => 'Ningrum',
                    'fullName' => 'Nimas Ayu Sekar Ningrum, S.Pd.',
                    'parents' => 'Putri kedua dari Bpk. H. Rahmat Soedibyo & Ibu Hj. Siti Nurjanah',
                    'photo' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=600&q=80',
                    'instagram' => '@ningrum_ayu',
                ],
                'gallery' => [
                    'https://images.unsplash.com/photo-1583939003579-730e3918a45a?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1511285560929-80b456fea0bc?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1519741497674-611481863552?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1520854221256-17451cc331bf?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            events: [
                [
                    'id' => 1,
                    'name' => 'Akad Nikah & Panggih Keraton',
                    'starts_at' => now()->addDays(32)->setTime(8, 0)->toIso8601String(),
                    'ends_at' => now()->addDays(32)->setTime(10, 30)->toIso8601String(),
                    'timezone' => 'WIB',
                    'venue_name' => 'Sasana Kriya Grand Ballroom, TMII',
                    'address' => 'Jl. Raya TMII, Cipayung, Jakarta Timur',
                    'maps_url' => 'https://maps.google.com',
                ],
                [
                    'id' => 2,
                    'name' => 'Resepsi Ageng Palakrama',
                    'starts_at' => now()->addDays(32)->setTime(11, 30)->toIso8601String(),
                    'ends_at' => now()->addDays(32)->setTime(14, 30)->toIso8601String(),
                    'timezone' => 'WIB',
                    'venue_name' => 'Pendopo Agung Royal Ballroom',
                    'address' => 'Kawasan Heritage Budaya Jakarta',
                    'maps_url' => 'https://maps.google.com',
                ],
            ],
            giftAccounts: [
                [
                    'bank' => 'BCA',
                    'account_number' => '8410293847',
                    'account_name' => 'Arya Wicaksana',
                    'qr_image' => 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=BCA_8410293847_ARYA',
                ],
                [
                    'bank' => 'Bank Mandiri',
                    'account_number' => '1370018829104',
                    'account_name' => 'Sekar Ningrum',
                    'qr_image' => 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=MANDIRI_1370018829104_NINGRUM',
                ],
            ],
            guestbook: [
                [
                    'id' => 1,
                    'guest_name' => 'GPH. Purbaya & Keluarga',
                    'message' => 'Ndherek mangayubagya Mas Arya & Mbak Ningrum. Mugi dados keluarga ingkang sakinah, mawaddah, warahmah.',
                    'created_at' => '1 jam yang lalu',
                ],
                [
                    'id' => 2,
                    'guest_name' => 'Sahabat Teknik UI 2018',
                    'message' => 'Selamat menempuh hidup baru Arya! Lancar acaranya dan bahagia selalu selamanya.',
                    'created_at' => '3 jam yang lalu',
                ],
            ]
        );

        $tplNingrum = Template::updateOrCreate(
            ['slug' => 'ningrum-fairytale-3d'],
            [
                'name' => 'Ningrum (Fairytale & Vintage 3D)',
                'category' => 'fairytale',
                'thumbnail_path' => '/images/templates/ningrum-original.jpeg',
                'default_design_json' => $designNingrum,
                'schema_version' => 1,
                'is_premium' => true,
                'is_active' => true,
            ]
        );

        // 2. VICTORIA (Minimalist Luxury 3D) - High Fashion Obsidian Black & Champagne Gold
        $designVictoria = $makeDesign(
            theme: [
                'palette' => 'victoria',
                'isLight' => false,
                'primaryColor' => '#DFC15D',
                'secondaryColor' => '#FAF3DC',
                'bgColor' => '#0B0C0E',
                'cardBgColor' => '#131519',
                'envelopeBg' => '#1A1D23',
                'envelopePocketL' => '#121418',
                'envelopePocketR' => '#0E1013',
                'envelopePocketB' => '#181A20',
                'envelopeFlap' => '#20242C',
                'sealColor' => '#A67C1E',
                'sealSecondary' => '#FAF3DC',
                'textColor' => '#F8FAFC',
                'fontFamily' => 'Playfair Display',
                'fallingPetals' => true,
                'petalColors' => ['#FEF3C7', '#FBBF24', '#D97706'],
            ],
            content: [
                'coverBadge' => 'Editorial Luxury 3D Edition',
                'coverSubtitle' => 'The Grand Black-Tie Celebration',
                'weddingTitle' => 'Jonathan & Victoria',
                'weddingDateText' => 'Sabtu, 28 November 2026',
                'coverImage' => 'https://images.unsplash.com/photo-1519741497674-611481863552?auto=format&fit=crop&w=1000&q=80',
                'quote' => 'Two lives, two hearts, joined together in friendship, united forever in love and sophistication.',
                'quoteSource' => 'John Keats',
                'groom' => [
                    'nickname' => 'Jonathan',
                    'fullName' => 'Jonathan Alexander, B.A.',
                    'parents' => 'Son of Mr. Robert Alexander & Mrs. Evelyn Alexander',
                    'photo' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=600&q=80',
                    'instagram' => '@jonathan.alexander',
                ],
                'bride' => [
                    'nickname' => 'Victoria',
                    'fullName' => 'Victoria Valerie, M.Sc.',
                    'parents' => 'Daughter of Mr. David Hartono & Mrs. Catherine Hartono',
                    'photo' => 'https://images.unsplash.com/photo-1517841905240-472988babdf9?auto=format&fit=crop&w=600&q=80',
                    'instagram' => '@victoria.valerie',
                ],
                'gallery' => [
                    'https://images.unsplash.com/photo-1519741497674-611481863552?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1465495976277-4387d4b0b4c6?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1511285560929-80b456fea0bc?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1520854221256-17451cc331bf?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            events: [
                [
                    'id' => 1,
                    'name' => 'Holy Matrimony',
                    'starts_at' => now()->addDays(46)->setTime(9, 30)->toIso8601String(),
                    'ends_at' => now()->addDays(46)->setTime(11, 30)->toIso8601String(),
                    'timezone' => 'WIB',
                    'venue_name' => 'The Cathedral of Jakarta',
                    'address' => 'Jl. Katedral No. 7B, Pasar Baru, Jakarta Pusat',
                    'maps_url' => 'https://maps.google.com',
                ],
                [
                    'id' => 2,
                    'name' => 'Grand Gala Dinner Reception',
                    'starts_at' => now()->addDays(46)->setTime(18, 30)->toIso8601String(),
                    'ends_at' => now()->addDays(46)->setTime(22, 0)->toIso8601String(),
                    'timezone' => 'WIB',
                    'venue_name' => 'The St. Regis Jakarta Grand Ballroom',
                    'address' => 'Rajawali Place, Jl. H. R. Rasuna Said, Jakarta Selatan',
                    'maps_url' => 'https://maps.google.com',
                ],
            ],
            giftAccounts: [
                [
                    'bank' => 'BCA',
                    'account_number' => '0019284755',
                    'account_name' => 'Jonathan Alexander',
                    'qr_image' => 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=BCA_JONATHAN_0019284755',
                ],
            ],
            guestbook: [
                [
                    'id' => 1,
                    'guest_name' => 'Hartono & Associates',
                    'message' => 'Congratulations Jonathan & Victoria! Wishing you a lifetime of endless joy and prosperity.',
                    'created_at' => '30 menit yang lalu',
                ],
            ]
        );

        $tplVictoria = Template::updateOrCreate(
            ['slug' => 'victoria-luxury-3d'],
            [
                'name' => 'Victoria (Minimalist Luxury 3D)',
                'category' => 'minimalist',
                'thumbnail_path' => '/images/templates/victoria-original.png',
                'default_design_json' => $designVictoria,
                'schema_version' => 1,
                'is_premium' => true,
                'is_active' => true,
            ]
        );

        // 3. BIANCA (Modern Botanical Serenity) - Fresh Light Sage Green & Ivory Nature
        $designBianca = $makeDesign(
            theme: [
                'palette' => 'bianca',
                'isLight' => true,
                'primaryColor' => '#2D5A43',
                'secondaryColor' => '#1F3A2B',
                'bgColor' => '#F4F7F4',
                'cardBgColor' => '#FFFFFF',
                'envelopeBg' => '#E2ECE5',
                'envelopePocketL' => '#D6E4DB',
                'envelopePocketR' => '#CDE0D4',
                'envelopePocketB' => '#DFEAE3',
                'envelopeFlap' => '#CFE1D6',
                'sealColor' => '#3B6E55',
                'sealSecondary' => '#A3D2BA',
                'textColor' => '#192820',
                'fontFamily' => 'Plus Jakarta Sans',
                'fallingPetals' => true,
                'petalColors' => ['#D1FAE5', '#6EE7B7', '#059669'],
            ],
            content: [
                'coverBadge' => 'Botanical Garden 3D Edition',
                'coverSubtitle' => 'A Sunlit Nature Romance',
                'weddingTitle' => 'Bintang & Bianca',
                'weddingDateText' => 'Minggu, 6 Desember 2026',
                'coverImage' => 'https://images.unsplash.com/photo-1515934751635-c81c6bc9a2d8?auto=format&fit=crop&w=1000&q=80',
                'quote' => 'Seperti pepohonan yang tumbuh beriringan, cinta kita berakar kuat dalam harmoni dan keteduhan alam semesta.',
                'quoteSource' => 'Kahlil Gibran',
                'groom' => [
                    'nickname' => 'Bintang',
                    'fullName' => 'Bintang Pratama, S.Ars.',
                    'parents' => 'Putra pertama dari Bpk. Ir. Hendrawan & Ibu Maya Sartika',
                    'photo' => 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?auto=format&fit=crop&w=600&q=80',
                    'instagram' => '@bintang_pratama',
                ],
                'bride' => [
                    'nickname' => 'Bianca',
                    'fullName' => 'Bianca Olivia, S.Ds.',
                    'parents' => 'Putri kedua dari Bpk. Dr. Gunawan & Ibu Rina Kusuma',
                    'photo' => 'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?auto=format&fit=crop&w=600&q=80',
                    'instagram' => '@bianca_olivia',
                ],
                'gallery' => [
                    'https://images.unsplash.com/photo-1465495976277-4387d4b0b4c6?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1519741497674-611481863552?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1520854221256-17451cc331bf?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1511285560929-80b456fea0bc?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            events: [
                [
                    'id' => 1,
                    'name' => 'Outdoor Holy Vows',
                    'starts_at' => now()->addDays(54)->setTime(8, 30)->toIso8601String(),
                    'ends_at' => now()->addDays(54)->setTime(10, 30)->toIso8601String(),
                    'timezone' => 'WIB',
                    'venue_name' => 'Pine Hill Sanctuary, Lembang',
                    'address' => 'Jl. Maribaya No. 108, Lembang, Bandung Barat',
                    'maps_url' => 'https://maps.google.com',
                ],
                [
                    'id' => 2,
                    'name' => 'Garden Party Celebration',
                    'starts_at' => now()->addDays(54)->setTime(12, 0)->toIso8601String(),
                    'ends_at' => now()->addDays(54)->setTime(16, 0)->toIso8601String(),
                    'timezone' => 'WIB',
                    'venue_name' => 'The Forest Greenhouse Cibodas',
                    'address' => 'Pine Hill Forest Area, Bandung',
                    'maps_url' => 'https://maps.google.com',
                ],
            ],
            giftAccounts: [
                [
                    'bank' => 'BCA',
                    'account_number' => '5019283746',
                    'account_name' => 'Bintang Pratama',
                    'qr_image' => 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=BCA_BINTANG_5019283746',
                ],
            ],
            guestbook: [
                [
                    'id' => 1,
                    'guest_name' => 'Arsitek ITB Squad',
                    'message' => 'Happy wedding Bintang & Bianca! Konsep acaranya asri banget. Bahagia selalu ya kalian berdua!',
                    'created_at' => '4 jam yang lalu',
                ],
            ]
        );

        $tplBianca = Template::updateOrCreate(
            ['slug' => 'bianca-botanical-3d'],
            [
                'name' => 'Bianca (Modern Botanical Serenity)',
                'category' => 'nature',
                'thumbnail_path' => '/images/templates/bianca-original.png',
                'default_design_json' => $designBianca,
                'schema_version' => 1,
                'is_premium' => false,
                'is_active' => true,
            ]
        );

        // 4. ELOISE (Floral Romance & Fairy Dust) - Dreamy Rose Plum & Sparkling Fairytale
        $designEloise = $makeDesign(
            theme: [
                'palette' => 'eloise',
                'isLight' => false,
                'primaryColor' => '#F4A7B9',
                'secondaryColor' => '#FFF0F5',
                'bgColor' => '#22131C',
                'cardBgColor' => '#2F1A27',
                'envelopeBg' => '#3D1E31',
                'envelopePocketL' => '#2E1524',
                'envelopePocketR' => '#26111E',
                'envelopePocketB' => '#35182B',
                'envelopeFlap' => '#432136',
                'sealColor' => '#C24168',
                'sealSecondary' => '#FBCFE8',
                'textColor' => '#FFF5F8',
                'fontFamily' => 'Cormorant Garamond',
                'fallingPetals' => true,
                'petalColors' => ['#FFE4E6', '#FB7185', '#E11D48'],
            ],
            content: [
                'coverBadge' => 'Romantic Rose Gold 3D Edition',
                'coverSubtitle' => 'An Enchanted Fairy Tale of Love',
                'weddingTitle' => 'Gabriel & Eloise',
                'weddingDateText' => 'Sabtu, 19 Desember 2026',
                'coverImage' => 'https://images.unsplash.com/photo-1511285560929-80b456fea0bc?auto=format&fit=crop&w=1000&q=80',
                'quote' => 'Every love story is beautiful, but ours is my favorite fairy tale come true.',
                'quoteSource' => 'A Beautiful Romance',
                'groom' => [
                    'nickname' => 'Gabriel',
                    'fullName' => 'Gabriel Darren, S.E.',
                    'parents' => 'Putra pertama dari Bpk. Antonius Darren & Ibu Fransisca',
                    'photo' => 'https://images.unsplash.com/photo-1492562080023-ab3db95bfbce?auto=format&fit=crop&w=600&q=80',
                    'instagram' => '@gabriel.darren',
                ],
                'bride' => [
                    'nickname' => 'Eloise',
                    'fullName' => 'Eloise Clarissa, B.Des.',
                    'parents' => 'Putri bungsu dari Bpk. Vincent Wijaya & Ibu Helena',
                    'photo' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=600&q=80',
                    'instagram' => '@eloise.clarissa',
                ],
                'gallery' => [
                    'https://images.unsplash.com/photo-1511285560929-80b456fea0bc?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1519741497674-611481863552?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1520854221256-17451cc331bf?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1465495976277-4387d4b0b4c6?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            events: [
                [
                    'id' => 1,
                    'name' => 'Pemberkatan Suci',
                    'starts_at' => now()->addDays(67)->setTime(10, 0)->toIso8601String(),
                    'ends_at' => now()->addDays(67)->setTime(12, 0)->toIso8601String(),
                    'timezone' => 'WIB',
                    'venue_name' => 'Gereja Katedral Santo Paulus',
                    'address' => 'Jl. Diponegoro No. 24, Menteng, Jakarta Pusat',
                    'maps_url' => 'https://maps.google.com',
                ],
                [
                    'id' => 2,
                    'name' => 'Evening Fairy Lights Dinner',
                    'starts_at' => now()->addDays(67)->setTime(18, 0)->toIso8601String(),
                    'ends_at' => now()->addDays(67)->setTime(21, 30)->toIso8601String(),
                    'timezone' => 'WIB',
                    'venue_name' => 'Plataran Dharmawangsa Glass Pavilion',
                    'address' => 'Jl. Dharmawangsa Raya No. 6, Kebayoran Baru, Jakarta Selatan',
                    'maps_url' => 'https://maps.google.com',
                ],
            ],
            giftAccounts: [
                [
                    'bank' => 'BCA',
                    'account_number' => '7119203841',
                    'account_name' => 'Gabriel Darren',
                    'qr_image' => 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=BCA_GABRIEL_7119203841',
                ],
            ],
            guestbook: [
                [
                    'id' => 1,
                    'guest_name' => 'Clarissa Family',
                    'message' => 'Dearest Eloise & Gabriel, wishing you joy, love, and happiness on your wedding day and as you begin your new life together.',
                    'created_at' => '2 jam yang lalu',
                ],
            ]
        );

        $tplEloise = Template::updateOrCreate(
            ['slug' => 'eloise-romance-3d'],
            [
                'name' => 'Eloise (Floral Romance & Fairy Dust)',
                'category' => 'floral',
                'thumbnail_path' => '/images/templates/eloise-original.png',
                'default_design_json' => $designEloise,
                'schema_version' => 1,
                'is_premium' => true,
                'is_active' => true,
            ]
        );

        // 5. ALSA (Royal Adat & Songket Gold) - Majestic Royal Maroon & Gilded Nusantara Songket
        $designAlsa = $makeDesign(
            theme: [
                'palette' => 'alsa',
                'isLight' => false,
                'primaryColor' => '#F5BD47',
                'secondaryColor' => '#FFF6DE',
                'bgColor' => '#260D12',
                'cardBgColor' => '#381119',
                'envelopeBg' => '#4A121E',
                'envelopePocketL' => '#300B13',
                'envelopePocketR' => '#27080F',
                'envelopePocketB' => '#3D0F19',
                'envelopeFlap' => '#521422',
                'sealColor' => '#9E1A2B',
                'sealSecondary' => '#F5BD47',
                'textColor' => '#FFF8EE',
                'fontFamily' => 'Cinzel',
                'fallingPetals' => true,
                'petalColors' => ['#FEF08A', '#FACC15', '#CA8A04'],
            ],
            content: [
                'coverBadge' => 'Royal Songket Nusantara 3D Edition',
                'coverSubtitle' => 'Baralek Gadang & Pernikahan Adat Nusantara',
                'weddingTitle' => 'Sutan Alif & Siti Alsa',
                'weddingDateText' => 'Minggu, 20 Desember 2026',
                'coverImage' => 'https://images.unsplash.com/photo-1583939003579-730e3918a45a?auto=format&fit=crop&w=1000&q=80',
                'quote' => 'Bulek aia dek pambuluah, bulek kato dek mupakaik. Sauh saundang sarangkai budi, basatu janji suci sampai ka akia hayat.',
                'quoteSource' => 'Pepatah Adat Minangkabau',
                'groom' => [
                    'nickname' => 'Alif',
                    'fullName' => 'Sutan Alif Firdaus, S.T.',
                    'parents' => 'Putra sulung dari Bpk. Dt. Bagindo Marajo & Ibu Hj. Rosdiana',
                    'photo' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=600&q=80',
                    'instagram' => '@alif_firdaus',
                ],
                'bride' => [
                    'nickname' => 'Alsa',
                    'fullName' => 'Siti Sarah Alsa, S.Pd.',
                    'parents' => 'Putri kedua dari Bpk. H. Syamsul Bahri & Ibu Hj. Nurmaida',
                    'photo' => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=600&q=80',
                    'instagram' => '@sarah_alsa',
                ],
                'gallery' => [
                    'https://images.unsplash.com/photo-1583939003579-730e3918a45a?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1511285560929-80b456fea0bc?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1519741497674-611481863552?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1520854221256-17451cc331bf?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            events: [
                [
                    'id' => 1,
                    'name' => 'Akad Nikah Adat',
                    'starts_at' => now()->addDays(68)->setTime(8, 0)->toIso8601String(),
                    'ends_at' => now()->addDays(68)->setTime(10, 0)->toIso8601String(),
                    'timezone' => 'WIB',
                    'venue_name' => 'Masjid Agung Sunda Kelapa',
                    'address' => 'Jl. Taman Sunda Kelapa No. 16, Menteng, Jakarta Pusat',
                    'maps_url' => 'https://maps.google.com',
                ],
                [
                    'id' => 2,
                    'name' => 'Resepsi Baralek Gadang',
                    'starts_at' => now()->addDays(68)->setTime(11, 0)->toIso8601String(),
                    'ends_at' => now()->addDays(68)->setTime(15, 0)->toIso8601String(),
                    'timezone' => 'WIB',
                    'venue_name' => 'Grand Ballroom Balai Sudirman',
                    'address' => 'Jl. Dr. Saharjo No. 268, Menteng Dalam, Tebet, Jakarta Selatan',
                    'maps_url' => 'https://maps.google.com',
                ],
            ],
            giftAccounts: [
                [
                    'bank' => 'Bank Mandiri',
                    'account_number' => '1370098273641',
                    'account_name' => 'Sutan Alif Firdaus',
                    'qr_image' => 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=MANDIRI_ALIF_1370098273641',
                ],
            ],
            guestbook: [
                [
                    'id' => 1,
                    'guest_name' => 'Keluarga Gadang Dt. Marajo',
                    'message' => 'Barakallahu lakuma Alif & Alsa. Salamaik manampuah hiduik baru, samawa sampai ka anak cucu.',
                    'created_at' => '1 jam yang lalu',
                ],
            ]
        );

        $tplAlsa = Template::updateOrCreate(
            ['slug' => 'alsa-royal-adat-3d'],
            [
                'name' => 'Alsa (Royal Adat & Songket Gold)',
                'category' => 'adat',
                'thumbnail_path' => '/images/templates/alsa-original.png',
                'default_design_json' => $designAlsa,
                'schema_version' => 1,
                'is_premium' => true,
                'is_active' => true,
            ]
        );

        // 6. CELESTIA (Royal 3D Celestial & Emerald Conservatory) - Cosmic Starlight & 3D Astrolabe
        $designCelestia = $makeDesign(
            theme: [
                'palette' => 'celestia',
                'isLight' => false,
                'primaryColor' => '#E6CA65',
                'secondaryColor' => '#6EE7B7',
                'bgColor' => '#040D0A',
                'cardBgColor' => '#0B221A',
                'envelopeBg' => '#0F2A21',
                'envelopePocketL' => '#081C15',
                'envelopePocketR' => '#05140F',
                'envelopePocketB' => '#0D241C',
                'envelopeFlap' => '#14382B',
                'sealColor' => '#D4AF37',
                'sealSecondary' => '#6EE7B7',
                'textColor' => '#F8FAFC',
                'fontFamily' => 'Cinzel',
                'fallingPetals' => true,
                'petalColors' => ['#FEF08A', '#6EE7B7', '#E6CA65'],
            ],
            content: [
                'coverBadge' => 'Royal 3D Celestial Astrolabe Edition',
                'coverSubtitle' => 'The Celestial Starlight & Royal Conservatory',
                'weddingTitle' => 'Arkan & Nadia',
                'weddingDateText' => 'Sabtu, 28 November 2026',
                'coverImage' => 'https://images.unsplash.com/photo-1519741497674-611481863552?auto=format&fit=crop&w=1000&q=80',
                'quote' => 'Dan di antara tanda-tanda kekuasaan-Nya ialah Dia menciptakan untukmu isteri-isteri dari jenismu sendiri, supaya kamu cenderung dan merasa tenteram kepadanya, dan dijadikan-Nya diantaramu rasa kasih dan sayang.',
                'quoteSource' => 'QS. Ar-Rum: 21',
                'groom' => [
                    'nickname' => 'Arkan',
                    'fullName' => 'Muhammad Arkan Pratama, S.T., M.Sc.',
                    'parents' => 'Putra pertama dari Bpk. Ir. H. Bambang Pratama & Ibu Hj. Retno Wardani',
                    'photo' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=600&q=80',
                    'instagram' => '@arkan.pratama',
                ],
                'bride' => [
                    'nickname' => 'Nadia',
                    'fullName' => 'Nadia Sekar Maharani, B.Des., M.Arch.',
                    'parents' => 'Putri kedua dari Bpk. Dr. H. Faisal Anwar & Ibu Hj. Dewi Sartika',
                    'photo' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=600&q=80',
                    'instagram' => '@nadiasekar',
                ],
                'gallery' => [
                    'https://images.unsplash.com/photo-1519741497674-611481863552?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1511285560929-80b456fea0bc?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1583939003579-730e3918a45a?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1520854221256-17451cc331bf?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            events: [
                [
                    'id' => 1,
                    'name' => 'Akad Nikah Sakral',
                    'starts_at' => now()->addDays(45)->setTime(8, 0)->toIso8601String(),
                    'ends_at' => now()->addDays(45)->setTime(10, 0)->toIso8601String(),
                    'timezone' => 'WIB',
                    'venue_name' => 'Masjid Raya Al-Bukhari',
                    'address' => 'Jl. Dr. Saharjo No. 112, Tebet, Jakarta Selatan',
                    'maps_url' => 'https://maps.google.com',
                ],
                [
                    'id' => 2,
                    'name' => 'Resepsi Royal Celestial',
                    'starts_at' => now()->addDays(45)->setTime(18, 30)->toIso8601String(),
                    'ends_at' => now()->addDays(45)->setTime(21, 30)->toIso8601String(),
                    'timezone' => 'WIB',
                    'venue_name' => 'The Starlight Glasshouse Grand Pavilion',
                    'address' => 'Jl. Jenderal Sudirman Kav. 52-53, SCBD, Jakarta Selatan',
                    'maps_url' => 'https://maps.google.com',
                ],
            ],
            giftAccounts: [
                [
                    'bank' => 'BCA',
                    'account_number' => '8210984721',
                    'account_name' => 'Muhammad Arkan Pratama',
                    'qr_image' => 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=BCA_8210984721_ARKAN',
                ],
                [
                    'bank' => 'Bank Mandiri',
                    'account_number' => '1370018829104',
                    'account_name' => 'Nadia Sekar Maharani',
                    'qr_image' => 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=MANDIRI_1370018829104_NADIA',
                ],
            ],
            guestbook: [
                [
                    'id' => 1,
                    'guest_name' => 'Prof. Dr. Ir. H. Bambang & Keluarga',
                    'message' => 'Selamat menempuh hidup baru Arkan & Nadia. Semoga menjadi keluarga yang sakinah mawaddah warahmah, dilimpahi berkah dan cinta seumur hidup.',
                    'created_at' => '30 menit yang lalu',
                ],
                [
                    'id' => 2,
                    'guest_name' => 'Alumni ITB 2017 & Arsitektur UI',
                    'message' => 'Happy wedding Arkan & Nadia! Wishing you endless happiness, laughter, and joy in your new journey together.',
                    'created_at' => '1 jam yang lalu',
                ],
            ]
        );

        $tplCelestia = Template::updateOrCreate(
            ['slug' => 'celestia-starlight-3d'],
            [
                'name' => 'Celestia (Royal 3D Celestial & Emerald)',
                'category' => '3d',
                'thumbnail_path' => '/images/templates/celestia-original.png',
                'default_design_json' => $designCelestia,
                'schema_version' => 1,
                'is_premium' => true,
                'is_active' => true,
            ]
        );

        // 6. MODERN MINIMALIST (Clean Scandinavian White & Royal Blue)
        $designModern = $makeDesign(
            theme: [
                'palette' => 'modern',
                'isLight' => true,
                'primaryColor' => '#2563EB',
                'secondaryColor' => '#1E293B',
                'bgColor' => '#F8FAFC',
                'cardBgColor' => '#FFFFFF',
                'envelopeBg' => '#E2E8F0',
                'envelopePocketL' => '#CBD5E1',
                'envelopePocketR' => '#94A3B8',
                'envelopePocketB' => '#E2E8F0',
                'envelopeFlap' => '#CBD5E1',
                'sealColor' => '#1D4ED8',
                'sealSecondary' => '#93C5FD',
                'textColor' => '#0F172A',
                'fontFamily' => 'Plus Jakarta Sans',
                'fallingPetals' => false,
                'petalColors' => ['#BFDBFE', '#60A5FA', '#2563EB'],
            ],
            content: [
                'coverBadge' => 'Clean Minimalist Edition',
                'coverSubtitle' => 'Together With Our Families',
                'weddingTitle' => 'Kevin & Cheryl',
                'weddingDateText' => 'Sabtu, 5 Desember 2026',
                'coverImage' => 'https://images.unsplash.com/photo-1519741497674-611481863552?auto=format&fit=crop&w=1200&q=80',
                'quote' => 'Love is not about how many days, months, or years you have been together. Love is about how much you love each other every single day.',
                'quoteSource' => 'Anonymous',
                'groom' => [
                    'nickname' => 'Kevin',
                    'fullName' => 'Kevin Sanjaya, S.Bns.',
                    'parents' => 'Putra dari Bpk. Irwan Sanjaya & Ibu Linda',
                    'photo' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=400&q=80',
                    'instagram' => '@kevin.sanjaya',
                ],
                'bride' => [
                    'nickname' => 'Cheryl',
                    'fullName' => 'Cheryl Amanda, B.Com.',
                    'parents' => 'Putri dari Bpk. Andreas Amanda & Ibu Silvy',
                    'photo' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=400&q=80',
                    'instagram' => '@cheryl.amanda',
                ],
                'gallery' => [
                    'https://images.unsplash.com/photo-1519741497674-611481863552?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1511285560929-80b456fea0bc?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1583939003579-730e3918a45a?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1520854221256-17451cc331bf?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            events: [
                [
                    'id' => 1,
                    'name' => 'Holy Matrimony & Reception',
                    'starts_at' => now()->addDays(53)->setTime(16, 0)->toIso8601String(),
                    'ends_at' => now()->addDays(53)->setTime(20, 0)->toIso8601String(),
                    'timezone' => 'WIB',
                    'venue_name' => 'Glasshouse Urban Sanctuary Jakarta',
                    'address' => 'SCBD Lot 8, Senayan, Jakarta Selatan',
                    'maps_url' => 'https://maps.google.com',
                ],
            ],
            giftAccounts: [
                [
                    'bank' => 'BCA',
                    'account_number' => '1234567890',
                    'account_name' => 'Kevin Sanjaya',
                    'qr_image' => 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=BCA_KEVIN_1234567890',
                ],
            ],
            guestbook: [
                [
                    'id' => 1,
                    'guest_name' => 'Rekan Bisnis Jakarta',
                    'message' => 'Congratulations Kevin & Cheryl! Wishing you both a lifetime of happiness.',
                    'created_at' => '5 jam yang lalu',
                ],
            ]
        );

        $tplModern = Template::updateOrCreate(
            ['slug' => 'modern-minimalist'],
            [
                'name' => 'Modern Minimalist',
                'category' => 'modern',
                'thumbnail_path' => 'https://images.unsplash.com/photo-1519741497674-611481863552?auto=format&fit=crop&w=600&q=80',
                'default_design_json' => $designModern,
                'schema_version' => 1,
                'is_premium' => false,
                'is_active' => true,
            ]
        );

        // 7. RUSTIC FLORAL BLISS (Warm Kraft Earth & Pampas Terracotta)
        $designRustic = $makeDesign(
            theme: [
                'palette' => 'rustic',
                'isLight' => true,
                'primaryColor' => '#9C6644',
                'secondaryColor' => '#5C4033',
                'bgColor' => '#F7F2EA',
                'cardBgColor' => '#FFFDF9',
                'envelopeBg' => '#E8DFD3',
                'envelopePocketL' => '#DECDBE',
                'envelopePocketR' => '#D4C1B0',
                'envelopePocketB' => '#E4D8CA',
                'envelopeFlap' => '#DECDBE',
                'sealColor' => '#8B4513',
                'sealSecondary' => '#DDB892',
                'textColor' => '#3E2723',
                'fontFamily' => 'Cormorant Garamond',
                'fallingPetals' => true,
                'petalColors' => ['#EDE0D4', '#DDB892', '#B08968'],
            ],
            content: [
                'coverBadge' => 'Bohemian Earth Rustic Edition',
                'coverSubtitle' => 'Warm Earth & Bohemian Romance',
                'weddingTitle' => 'Bumi & Senja',
                'weddingDateText' => 'Sabtu, 12 Desember 2026',
                'coverImage' => 'https://images.unsplash.com/photo-1465495976277-4387d4b0b4c6?auto=format&fit=crop&w=1200&q=80',
                'quote' => 'Dan di antara tanda-tanda kekuasaan-Nya ialah Dia menciptakan pasangan-pasangan untukmu dari jenismu sendiri agar kamu hidup dalam ketenangan dan cinta.',
                'quoteSource' => 'QS. Ar-Rum: 21',
                'groom' => [
                    'nickname' => 'Bumi',
                    'fullName' => 'Bumi Satria, S.P.',
                    'parents' => 'Putra dari Bpk. H. Sukardi & Ibu Siti Fatimah',
                    'photo' => 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?auto=format&fit=crop&w=400&q=80',
                    'instagram' => '@bumisatria',
                ],
                'bride' => [
                    'nickname' => 'Senja',
                    'fullName' => 'Senja Lestari, S.H.',
                    'parents' => 'Putri dari Bpk. Ir. Danang & Ibu Wulandari',
                    'photo' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=400&q=80',
                    'instagram' => '@senjalestari',
                ],
                'gallery' => [
                    'https://images.unsplash.com/photo-1465495976277-4387d4b0b4c6?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1511285560929-80b456fea0bc?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1520854221256-17451cc331bf?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1583939003579-730e3918a45a?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            events: [
                [
                    'id' => 1,
                    'name' => 'Akad Nikah & Syukuran Rustic',
                    'starts_at' => now()->addDays(60)->setTime(9, 0)->toIso8601String(),
                    'ends_at' => now()->addDays(60)->setTime(13, 0)->toIso8601String(),
                    'timezone' => 'WIB',
                    'venue_name' => 'Omah Kopi Barn & Garden',
                    'address' => 'Kawasan Wisata Kaliurang, Yogyakarta',
                    'maps_url' => 'https://maps.google.com',
                ],
            ],
            giftAccounts: [
                [
                    'bank' => 'BCA',
                    'account_number' => '9876543210',
                    'account_name' => 'Bumi Satria',
                    'qr_image' => 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=BCA_BUMI_9876543210',
                ],
            ],
            guestbook: [
                [
                    'id' => 1,
                    'guest_name' => 'Keluarga Besar Jogja',
                    'message' => 'Selamat menempuh hidup baru Bumi & Senja! Langgeng dan rukun selamanya.',
                    'created_at' => '3 jam yang lalu',
                ],
            ]
        );

        $tplRustic = Template::updateOrCreate(
            ['slug' => 'rustic-floral'],
            [
                'name' => 'Rustic Floral Bliss',
                'category' => 'rustic',
                'thumbnail_path' => 'https://images.unsplash.com/photo-1465495976277-4387d4b0b4c6?auto=format&fit=crop&w=600&q=80',
                'default_design_json' => $designRustic,
                'schema_version' => 1,
                'is_premium' => false,
                'is_active' => true,
            ]
        );

        // 8. ROYAL EMERALD & GOLD
        $designGold = $makeDesign(
            theme: [
                'palette' => 'emerald',
                'isLight' => false,
                'primaryColor' => '#10B981',
                'secondaryColor' => '#FDE047',
                'bgColor' => '#062015',
                'cardBgColor' => '#0A2D1F',
                'envelopeBg' => '#0D3B29',
                'envelopePocketL' => '#08251A',
                'envelopePocketR' => '#061D14',
                'envelopePocketB' => '#0B3323',
                'envelopeFlap' => '#104531',
                'sealColor' => '#059669',
                'sealSecondary' => '#FDE047',
                'textColor' => '#ECFDF5',
                'fontFamily' => 'Cinzel',
                'fallingPetals' => true,
                'petalColors' => ['#A7F3D0', '#34D399', '#059669'],
            ],
            content: [
                'coverBadge' => 'Royal Emerald & Gold Edition',
                'coverSubtitle' => 'The Royal Emerald Celebration',
                'weddingTitle' => 'Reihan & Natasha',
                'weddingDateText' => 'Sabtu, 26 Desember 2026',
                'coverImage' => 'https://images.unsplash.com/photo-1520854221256-17451cc331bf?auto=format&fit=crop&w=1200&q=80',
                'quote' => 'May your love be as evergreen as the emerald and as radiant as gold.',
                'quoteSource' => 'Royal Blessing',
                'groom' => [
                    'nickname' => 'Reihan',
                    'fullName' => 'Reihan Pratama, S.T.',
                    'parents' => 'Putra dari Bpk. Ir. Suryadi & Ibu Desi',
                    'photo' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=400&q=80',
                    'instagram' => '@reihan_pratama',
                ],
                'bride' => [
                    'nickname' => 'Natasha',
                    'fullName' => 'Natasha Aurelia, B.A.',
                    'parents' => 'Putri dari Bpk. Harun & Ibu Melly',
                    'photo' => 'https://images.unsplash.com/photo-1517841905240-472988babdf9?auto=format&fit=crop&w=400&q=80',
                    'instagram' => '@natasha.aurelia',
                ],
                'gallery' => [
                    'https://images.unsplash.com/photo-1520854221256-17451cc331bf?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1519741497674-611481863552?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1511285560929-80b456fea0bc?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1583939003579-730e3918a45a?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            events: [
                [
                    'id' => 1,
                    'name' => 'Royal Emerald Matrimony & Banquet',
                    'starts_at' => now()->addDays(74)->setTime(17, 0)->toIso8601String(),
                    'ends_at' => now()->addDays(74)->setTime(21, 0)->toIso8601String(),
                    'timezone' => 'WIB',
                    'venue_name' => 'The Mulia Grand Ballroom Jakarta',
                    'address' => 'Jl. Asia Afrika No. 1, Senayan, Jakarta Pusat',
                    'maps_url' => 'https://maps.google.com',
                ],
            ],
            giftAccounts: [
                [
                    'bank' => 'BCA',
                    'account_number' => '4419283710',
                    'account_name' => 'Reihan Pratama',
                    'qr_image' => 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=BCA_REIHAN_4419283710',
                ],
            ],
            guestbook: [
                [
                    'id' => 1,
                    'guest_name' => 'Keluarga Besar Pratama',
                    'message' => 'Congratulations Reihan & Natasha! Semoga senantiasa diberkahi kebahagiaan seumur hidup.',
                    'created_at' => '2 jam yang lalu',
                ],
            ]
        );

        $tplGold = Template::updateOrCreate(
            ['slug' => 'royal-emerald-gold'],
            [
                'name' => 'Royal Emerald & Gold',
                'category' => 'floral',
                'thumbnail_path' => 'https://images.unsplash.com/photo-1520854221256-17451cc331bf?auto=format&fit=crop&w=600&q=80',
                'default_design_json' => $designGold,
                'schema_version' => 1,
                'is_premium' => true,
                'is_active' => true,
            ]
        );

        $tpl3D = Template::updateOrCreate(
            ['slug' => 'royal-3d-envelope'],
            [
                'name' => 'Royal 3D Envelope & Wax Seal',
                'category' => '3d',
                'thumbnail_path' => 'https://images.unsplash.com/photo-1511285560929-80b456fea0bc?auto=format&fit=crop&w=600&q=80',
                'default_design_json' => $designVictoria,
                'schema_version' => 1,
                'is_premium' => true,
                'is_active' => true,
            ]
        );

        $tplTraditional = Template::updateOrCreate(
            ['slug' => 'nusantara-romance'],
            [
                'name' => 'Nusantara Romance',
                'category' => 'traditional',
                'thumbnail_path' => 'https://images.unsplash.com/photo-1583939003579-730e3918a45a?auto=format&fit=crop&w=600&q=80',
                'default_design_json' => $designAlsa,
                'schema_version' => 1,
                'is_premium' => true,
                'is_active' => true,
            ]
        );

        if (!$customer) {
            return;
        }

        // 4. Sample Invitation for Customer
        $invitation = Invitation::firstOrCreate(
            ['slug' => 'dimas-anisa'],
            [
                'user_id' => $customer->id,
                'template_id' => $tplModern->id,
                'title' => 'Pernikahan Dimas & Anisa',
                'status' => 'published',
                'published_at' => now(),
                'expires_at' => now()->addMonths(12),
            ]
        );

        // Invitation Design Draft
        $design = InvitationDesign::updateOrCreate(
            ['invitation_id' => $invitation->id],
            [
                'design_json' => $designModern,
                'schema_version' => 1,
            ]
        );

        // Invitation Version 1 (Immutable snapshot)
        $version = InvitationVersion::firstOrCreate(
            ['invitation_id' => $invitation->id, 'version_number' => 1],
            [
                'design_json' => $designModern,
                'schema_version' => 1,
                'created_by' => $customer->id,
                'published_at' => now(),
            ]
        );

        // Link published version
        $invitation->update(['published_version_id' => $version->id]);

        // 5. Events for Invitation
        Event::firstOrCreate(
            ['invitation_id' => $invitation->id, 'name' => 'Akad Nikah'],
            [
                'starts_at' => '2026-12-28 08:00:00',
                'ends_at' => '2026-12-28 10:00:00',
                'timezone' => 'WIB',
                'venue_name' => 'Masjid Agung Jawa Tengah',
                'address' => 'Jl. Gajah Raya, Sambirejo, Kec. Gayamsari, Kota Semarang, Jawa Tengah',
                'maps_url' => 'https://maps.google.com/?q=Masjid+Agung+Jawa+Tengah',
            ]
        );

        Event::firstOrCreate(
            ['invitation_id' => $invitation->id, 'name' => 'Resepsi Pernikahan'],
            [
                'starts_at' => '2026-12-28 11:00:00',
                'ends_at' => '2026-12-28 14:00:00',
                'timezone' => 'WIB',
                'venue_name' => 'Grand Ballroom Rama Shinta',
                'address' => 'Jl. Pemuda No. 123, Semarang, Jawa Tengah',
                'maps_url' => 'https://maps.google.com/?q=Semarang',
            ]
        );

        // 6. Sample Guests
        $guestBudi = Guest::firstOrCreate(
            ['invitation_id' => $invitation->id, 'name' => 'Budi Santoso & Partner'],
            [
                'token' => 'guest-budi-2026',
                'phone_number' => '081234567891',
                'max_attendees' => 2,
            ]
        );

        $guestSiti = Guest::firstOrCreate(
            ['invitation_id' => $invitation->id, 'name' => 'Keluarga Bpk. Hendra Gunawan'],
            [
                'token' => 'guest-hendra-2026',
                'phone_number' => '081234567892',
                'max_attendees' => 4,
            ]
        );

        // 7. Sample RSVPs
        Rsvp::firstOrCreate(
            ['invitation_id' => $invitation->id, 'guest_id' => $guestBudi->id],
            [
                'guest_name' => 'Budi Santoso & Partner',
                'attendance_status' => 'attending',
                'attendee_count' => 2,
                'message' => 'Selamat untuk Dimas dan Anisa! Semoga menjadi keluarga yang sakinah, mawaddah, warahmah.',
                'submitted_at' => now()->subDays(2),
            ]
        );

        // 8. Sample Guestbook Entries
        GuestbookEntry::firstOrCreate(
            ['invitation_id' => $invitation->id, 'guest_name' => 'Budi Santoso'],
            [
                'message' => 'Barakallahu lakuma wa baraka alaikuma wa jama\'a bainakuma fii khair. Selamat menempuh hidup baru!',
                'moderation_status' => 'approved',
            ]
        );

        GuestbookEntry::firstOrCreate(
            ['invitation_id' => $invitation->id, 'guest_name' => 'Rina & Adit'],
            [
                'message' => 'Happy wedding Dimas & Anisa! Langgeng sampai kakek nenek yaaa ✨🎉',
                'moderation_status' => 'approved',
            ]
        );

        // 9. Dedicated 3D Model Demo Invitation
        $invitation3D = Invitation::firstOrCreate(
            ['slug' => 'dimas-anisa-3d'],
            [
                'user_id' => $customer->id,
                'template_id' => $tpl3D->id,
                'title' => 'The Wedding of Dimas & Anisa (3D Royal Envelope & Wax Seal)',
                'status' => 'published',
                'published_at' => now(),
                'expires_at' => now()->addMonths(12),
            ]
        );

        $designDraft3D = InvitationDesign::updateOrCreate(
            ['invitation_id' => $invitation3D->id],
            [
                'design_json' => $designVictoria,
                'schema_version' => 1,
            ]
        );

        $version3D = InvitationVersion::firstOrCreate(
            ['invitation_id' => $invitation3D->id, 'version_number' => 1],
            [
                'design_json' => $designVictoria,
                'schema_version' => 1,
                'created_by' => $customer->id,
                'published_at' => now(),
            ]
        );

        $invitation3D->update(['published_version_id' => $version3D->id]);

        Event::firstOrCreate(
            ['invitation_id' => $invitation3D->id, 'name' => 'Akad Nikah'],
            [
                'starts_at' => '2026-12-28 08:00:00',
                'ends_at' => '2026-12-28 10:00:00',
                'timezone' => 'WIB',
                'venue_name' => 'Masjid Agung Jawa Tengah',
                'address' => 'Jl. Gajah Raya, Sambirejo, Kec. Gayamsari, Kota Semarang, Jawa Tengah',
                'maps_url' => 'https://maps.google.com/?q=Masjid+Agung+Jawa+Tengah',
            ]
        );

        Event::firstOrCreate(
            ['invitation_id' => $invitation3D->id, 'name' => 'Resepsi Akbar'],
            [
                'starts_at' => '2026-12-28 11:00:00',
                'ends_at' => '2026-12-28 14:00:00',
                'timezone' => 'WIB',
                'venue_name' => 'Grand Ballroom Rama Shinta',
                'address' => 'Jl. Pemuda No. 123, Semarang, Jawa Tengah',
                'maps_url' => 'https://maps.google.com/?q=Semarang',
            ]
        );

        Guest::firstOrCreate(
            ['invitation_id' => $invitation3D->id, 'name' => 'Bpk. Ridwan Kamil & Keluarga'],
            [
                'token' => 'vip-ridwan-3d',
                'phone_number' => '081234567899',
                'max_attendees' => 2,
            ]
        );

        GuestbookEntry::firstOrCreate(
            ['invitation_id' => $invitation3D->id, 'guest_name' => 'Bpk. Ridwan Kamil & Keluarga'],
            [
                'message' => 'Selamat untuk Dimas dan Anisa! Desain undangannya sangat istimewa dengan efek 3D wax seal mewah. Semoga bahagia selalu!',
                'moderation_status' => 'approved',
            ]
        );
    }
}

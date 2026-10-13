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

        $tplModern = Template::firstOrCreate(
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

        $designRustic = $designModern;
        $designRustic['theme'] = 'rustic';
        $designRustic['canvas']['background'] = '#F9F5EF';
        $designRustic['canvas']['primaryColor'] = '#8A6D3B';
        $designRustic['canvas']['fontFamily'] = 'Cormorant Garamond';

        $tplRustic = Template::firstOrCreate(
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

        $designGold = $designModern;
        $designGold['theme'] = 'gold';
        $designGold['canvas']['background'] = '#1A1D20';
        $designGold['canvas']['primaryColor'] = '#D4AF37';
        $designGold['canvas']['fontFamily'] = 'Cinzel';

        $tplGold = Template::firstOrCreate(
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

        $design3D = $designModern;
        $design3D['theme'] = '3d-royal';
        $design3D['is_3d'] = true;
        $design3D['canvas']['background'] = '#16191E';
        $design3D['canvas']['primaryColor'] = '#E5C07B';
        $design3D['canvas']['fontFamily'] = 'Cinzel';

        $tpl3D = Template::firstOrCreate(
            ['slug' => 'royal-3d-envelope'],
            [
                'name' => 'Royal 3D Envelope & Wax Seal',
                'category' => '3d',
                'thumbnail_path' => 'https://images.unsplash.com/photo-1511285560929-80b456fea0bc?auto=format&fit=crop&w=600&q=80',
                'default_design_json' => $design3D,
                'schema_version' => 1,
                'is_premium' => true,
                'is_active' => true,
            ]
        );

        $designTraditional = $designModern;
        $designTraditional['theme'] = 'traditional';
        $designTraditional['canvas']['background'] = '#FFFBF2';
        $designTraditional['canvas']['primaryColor'] = '#795548';
        $designTraditional['canvas']['fontFamily'] = 'Playfair Display';

        $tplTraditional = Template::firstOrCreate(
            ['slug' => 'nusantara-romance'],
            [
                'name' => 'Nusantara Romance',
                'category' => 'traditional',
                'thumbnail_path' => 'https://images.unsplash.com/photo-1583939003579-730e3918a45a?auto=format&fit=crop&w=600&q=80',
                'default_design_json' => $designTraditional,
                'schema_version' => 1,
                'is_premium' => true,
                'is_active' => true,
            ]
        );

        // 5 Top Katsudoto-inspired Themes (with 3D elements, luxury palettes, and immersive animations)
        // 1. Ningrum (Fairytale & Vintage 3D)
        $designNingrum = $designModern;
        $designNingrum['theme'] = [
            'palette' => 'ningrum',
            'primaryColor' => '#C5A059',
            'secondaryColor' => '#F5E8CE',
            'bgColor' => '#1A1215',
            'cardBgColor' => '#261820',
            'textColor' => '#FDFBF7',
            'fontFamily' => 'Cinzel',
            'fallingPetals' => true,
        ];
        $designNingrum['canvas']['background'] = '#1A1215';
        $designNingrum['canvas']['primaryColor'] = '#C5A059';
        $designNingrum['canvas']['fontFamily'] = 'Cinzel';
        $designNingrum['is_3d'] = true;

        $tplNingrum = Template::firstOrCreate(
            ['slug' => 'ningrum-fairytale-3d'],
            [
                'name' => 'Ningrum (Fairytale & Vintage 3D)',
                'category' => 'fairytale',
                'thumbnail_path' => 'https://katsudoto.id/media/template/preview/thumbnail/ningrum-original.jpeg',
                'default_design_json' => $designNingrum,
                'schema_version' => 1,
                'is_premium' => true,
                'is_active' => true,
            ]
        );

        // 2. Victoria (Minimalist Luxury 3D)
        $designVictoria = $designModern;
        $designVictoria['theme'] = [
            'palette' => 'victoria',
            'primaryColor' => '#D4AF37',
            'secondaryColor' => '#F7E7CE',
            'bgColor' => '#121316',
            'cardBgColor' => '#1A1D23',
            'textColor' => '#F3F4F6',
            'fontFamily' => 'Playfair Display',
            'fallingPetals' => true,
        ];
        $designVictoria['canvas']['background'] = '#121316';
        $designVictoria['canvas']['primaryColor'] = '#D4AF37';
        $designVictoria['canvas']['fontFamily'] = 'Playfair Display';
        $designVictoria['is_3d'] = true;

        $tplVictoria = Template::firstOrCreate(
            ['slug' => 'victoria-luxury-3d'],
            [
                'name' => 'Victoria (Minimalist Luxury 3D)',
                'category' => 'minimalist',
                'thumbnail_path' => 'https://katsudoto.id/media/template/preview/thumbnail/victoria-original.png',
                'default_design_json' => $designVictoria,
                'schema_version' => 1,
                'is_premium' => true,
                'is_active' => true,
            ]
        );

        // 3. Bianca (Modern Botanical Serenity)
        $designBianca = $designModern;
        $designBianca['theme'] = [
            'palette' => 'bianca',
            'primaryColor' => '#7A9A8B',
            'secondaryColor' => '#E2EDE6',
            'bgColor' => '#121A16',
            'cardBgColor' => '#1B2721',
            'textColor' => '#F4F7F4',
            'fontFamily' => 'Plus Jakarta Sans',
            'fallingPetals' => true,
        ];
        $designBianca['canvas']['background'] = '#121A16';
        $designBianca['canvas']['primaryColor'] = '#7A9A8B';
        $designBianca['canvas']['fontFamily'] = 'Plus Jakarta Sans';
        $designBianca['is_3d'] = true;

        $tplBianca = Template::firstOrCreate(
            ['slug' => 'bianca-botanical-3d'],
            [
                'name' => 'Bianca (Modern Botanical Serenity)',
                'category' => 'nature',
                'thumbnail_path' => 'https://katsudoto.id/media/template/preview/thumbnail/bianca-original.png',
                'default_design_json' => $designBianca,
                'schema_version' => 1,
                'is_premium' => false,
                'is_active' => true,
            ]
        );

        // 4. Eloise (Floral Romance & Fairy Dust)
        $designEloise = $designModern;
        $designEloise['theme'] = [
            'palette' => 'eloise',
            'primaryColor' => '#E8A598',
            'secondaryColor' => '#FDF0EE',
            'bgColor' => '#1C1316',
            'cardBgColor' => '#2A1A20',
            'textColor' => '#FFF5F5',
            'fontFamily' => 'Cormorant Garamond',
            'fallingPetals' => true,
        ];
        $designEloise['canvas']['background'] = '#1C1316';
        $designEloise['canvas']['primaryColor'] = '#E8A598';
        $designEloise['canvas']['fontFamily'] = 'Cormorant Garamond';
        $designEloise['is_3d'] = true;

        $tplEloise = Template::firstOrCreate(
            ['slug' => 'eloise-romance-3d'],
            [
                'name' => 'Eloise (Floral Romance & Fairy Dust)',
                'category' => 'floral',
                'thumbnail_path' => 'https://katsudoto.id/media/template/preview/thumbnail/eloise-original.png',
                'default_design_json' => $designEloise,
                'schema_version' => 1,
                'is_premium' => true,
                'is_active' => true,
            ]
        );

        // 5. Alsa (Royal Adat & Songket Gold)
        $designAlsa = $designModern;
        $designAlsa['theme'] = [
            'palette' => 'alsa',
            'primaryColor' => '#E5A93C',
            'secondaryColor' => '#FCE8C3',
            'bgColor' => '#1A130E',
            'cardBgColor' => '#281B13',
            'textColor' => '#FFF8EE',
            'fontFamily' => 'Cinzel',
            'fallingPetals' => true,
        ];
        $designAlsa['canvas']['background'] = '#1A130E';
        $designAlsa['canvas']['primaryColor'] = '#E5A93C';
        $designAlsa['canvas']['fontFamily'] = 'Cinzel';
        $designAlsa['is_3d'] = true;

        $tplAlsa = Template::firstOrCreate(
            ['slug' => 'alsa-royal-adat-3d'],
            [
                'name' => 'Alsa (Royal Adat & Songket Gold)',
                'category' => 'adat',
                'thumbnail_path' => 'https://katsudoto.id/media/template/preview/thumbnail/alsa-original.png',
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
                'design_json' => $design3D,
                'schema_version' => 1,
            ]
        );

        $version3D = InvitationVersion::firstOrCreate(
            ['invitation_id' => $invitation3D->id, 'version_number' => 1],
            [
                'design_json' => $design3D,
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

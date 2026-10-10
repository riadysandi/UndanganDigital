# Entity Relationship Diagram (ERD) & Database Schema

Dokumen ini mendefinisikan skema basis data relasional untuk Platform Undangan Digital SaaS sesuai Bagian 5 PRD.

---

## 1. Mermaid Entity-Relationship Diagram

```mermaid
erDiagram
    users ||--o{ invitations : owns
    users ||--o{ orders : places
    users ||--o{ subscriptions : holds
    users ||--o{ audit_logs : triggers

    templates ||--o{ invitations : instantiated_from

    invitations ||--|| invitation_designs : has_draft
    invitations ||--o{ invitation_versions : has_snapshots
    invitations ||--o{ invitation_media : contains
    invitations ||--o{ events : schedules
    invitations ||--o{ guests : invites
    invitations ||--o{ rsvps : receives
    invitations ||--o{ guestbook_entries : displays
    invitations ||--o{ orders : relates_to

    plans ||--o{ plan_prices : defines
    plans ||--o{ orders : purchased_in
    plans ||--o{ subscriptions : subscribed_to

    orders ||--o{ payments : processed_by
    webhook_events ||--o{ payments : validates

    users {
        bigint id PK
        string name
        string email UK
        string password
        string role "admin|customer"
        timestamp email_verified_at
        timestamps created_at_updated_at
    }

    plans {
        bigint id PK
        string name
        string code UK
        json features_json
        boolean is_active
        timestamps created_at_updated_at
    }

    plan_prices {
        bigint id PK
        bigint plan_id FK
        string billing_interval "once|monthly|yearly"
        decimal amount
        string currency "IDR"
        string provider_price_id
        timestamp active_from
        timestamp active_to
    }

    templates {
        bigint id PK
        string name
        string slug UK
        string category "modern|rustic|floral|traditional"
        string thumbnail_path
        json default_design_json
        integer schema_version
        boolean is_premium
        boolean is_active
        timestamps created_at_updated_at
    }

    invitations {
        bigint id PK
        bigint user_id FK
        bigint template_id FK
        string title
        string slug UK
        string status "draft|published|archived"
        bigint published_version_id FK
        timestamp published_at
        timestamp expires_at
        timestamps created_at_updated_at
    }

    invitation_designs {
        bigint id PK
        bigint invitation_id FK_UK
        json design_json
        integer schema_version
        timestamps created_at_updated_at
    }

    invitation_versions {
        bigint id PK
        bigint invitation_id FK
        integer version_number
        json design_json
        integer schema_version
        bigint created_by FK
        timestamp published_at
    }

    invitation_media {
        bigint id PK
        bigint invitation_id FK
        string storage_key
        string media_type "image|audio|video"
        string mime_type
        bigint size_bytes
        json metadata_json
        timestamps created_at_updated_at
    }

    events {
        bigint id PK
        bigint invitation_id FK
        string name "Akad Nikah / Resepsi"
        datetime starts_at
        datetime ends_at
        string timezone "WIB|WITA|WIT"
        string venue_name
        text address
        string maps_url
        timestamps created_at_updated_at
    }

    guests {
        bigint id PK
        bigint invitation_id FK
        string name
        string token UK
        string phone_number
        integer max_attendees
        timestamps created_at_updated_at
    }

    rsvps {
        bigint id PK
        bigint invitation_id FK
        bigint guest_id FK
        string attendance_status "attending|declined|tentative"
        integer attendee_count
        text message
        timestamp submitted_at
        timestamps created_at_updated_at
    }

    guestbook_entries {
        bigint id PK
        bigint invitation_id FK
        string guest_name
        text message
        string moderation_status "approved|pending|spam"
        timestamps created_at_updated_at
    }

    orders {
        bigint id PK
        bigint user_id FK
        bigint invitation_id FK
        bigint plan_id FK
        string order_number UK
        string order_type "one_time|subscription"
        decimal amount
        string currency
        string status "pending|paid|failed|expired"
        json product_snapshot_json
        timestamps created_at_updated_at
    }

    payments {
        bigint id PK
        bigint order_id FK
        string provider "midtrans|xendit|sandbox"
        string provider_transaction_id UK
        string status "pending|settlement|expire|cancel"
        decimal amount
        string currency
        timestamp paid_at
        json metadata_json
        timestamps created_at_updated_at
    }

    subscriptions {
        bigint id PK
        bigint user_id FK
        bigint plan_id FK
        string provider
        string provider_subscription_id UK
        string status "active|past_due|canceled"
        timestamp current_period_start
        timestamp current_period_end
        boolean cancel_at_period_end
        timestamps created_at_updated_at
    }

    webhook_events {
        bigint id PK
        string provider
        string provider_event_id UK
        string event_type
        timestamp processed_at
        string status "processed|failed|ignored"
        json sanitized_payload_json
        timestamps created_at_updated_at
    }

    audit_logs {
        bigint id PK
        bigint actor_user_id FK
        string action
        string subject_type
        bigint subject_id
        json metadata_json
        timestamps created_at_updated_at
    }
```

---

## 2. Integritas Relasi & Konstrain

1. **Unique Constraints**:
   - `users.email`
   - `templates.slug`
   - `invitations.slug`
   - `invitation_designs.invitation_id` (1-to-1 dengan undangan)
   - `(invitation_versions.invitation_id, invitation_versions.version_number)` (kombinasi unik)
   - `guests.token`
   - `orders.order_number`
   - `webhook_events.provider_event_id` (pencegahan webhook ganda / idempotency)
2. **Cascading / Deletion Policy**:
   - Menghapus `invitations` akan menghapus (cascade) desain, versi, media, event, tamu, rsvp, dan ucapan terkait.
   - Tabel `orders`, `payments`, dan `audit_logs` **tidak boleh** terhapus saat user/undangan dihapus (soft-delete / restrict) untuk menjaga rekam jejak audit keuangan.
3. **Penyimpanan Sensitif**:
   - Token tamu disimpan dalam bentuk hash/token unik yang sulit ditebak.
   - Tidak ada kredensial pembayaran sensitif (nomor kartu kredit, CVV) yang disimpan dalam sistem.

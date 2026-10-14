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
        DB::table('templates')
            ->where('slug', 'celestia-starlight-3d')
            ->update([
                'thumbnail_path' => '/images/templates/celestia-original.png',
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('templates')
            ->where('slug', 'celestia-starlight-3d')
            ->update([
                'thumbnail_path' => '/images/templates/ningrum-original.jpeg',
            ]);
    }
};

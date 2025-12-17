<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Hanya tambahkan kolom jika belum ada, untuk menghindari error duplicate column
        if (!Schema::hasColumn('media', 'original_name')) {
            Schema::table('media', function (Blueprint $table) {
                $table->string('original_name')->nullable()->after('path');
            });
        }
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->dropColumn('original_name');
        });
    }
};

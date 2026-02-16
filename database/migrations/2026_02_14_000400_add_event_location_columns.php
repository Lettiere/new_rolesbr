<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('evt_eventos_tb')) {
            Schema::table('evt_eventos_tb', function (Blueprint $table) {
                if (!Schema::hasColumn('evt_eventos_tb', 'endereco_evento')) {
                    $table->string('endereco_evento', 255)->nullable()->after('local_customizado');
                }
                if (!Schema::hasColumn('evt_eventos_tb', 'latitude_evento')) {
                    $table->decimal('latitude_evento', 10, 7)->nullable()->after('endereco_evento');
                }
                if (!Schema::hasColumn('evt_eventos_tb', 'longitude_evento')) {
                    $table->decimal('longitude_evento', 10, 7)->nullable()->after('latitude_evento');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('evt_eventos_tb')) {
            Schema::table('evt_eventos_tb', function (Blueprint $table) {
                if (Schema::hasColumn('evt_eventos_tb', 'endereco_evento')) {
                    $table->dropColumn('endereco_evento');
                }
                if (Schema::hasColumn('evt_eventos_tb', 'latitude_evento')) {
                    $table->dropColumn('latitude_evento');
                }
                if (Schema::hasColumn('evt_eventos_tb', 'longitude_evento')) {
                    $table->dropColumn('longitude_evento');
                }
            });
        }
    }
};


<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('evt_lotes_ingressos_tb')) {
            Schema::table('evt_lotes_ingressos_tb', function (Blueprint $table) {
                if (!Schema::hasColumn('evt_lotes_ingressos_tb', 'tipo')) {
                    $table->string('tipo', 20)->default('inteira')->after('nome');
                }
                if (!Schema::hasColumn('evt_lotes_ingressos_tb', 'status')) {
                    $table->string('status', 30)->nullable()->after('ativo');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('evt_lotes_ingressos_tb')) {
            Schema::table('evt_lotes_ingressos_tb', function (Blueprint $table) {
                if (Schema::hasColumn('evt_lotes_ingressos_tb', 'tipo')) {
                    $table->dropColumn('tipo');
                }
                if (Schema::hasColumn('evt_lotes_ingressos_tb', 'status')) {
                    $table->dropColumn('status');
                }
            });
        }
    }
};


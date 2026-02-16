<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('evt_ingressos_convidados_tb')) {
            Schema::table('evt_ingressos_convidados_tb', function (Blueprint $table) {
                if (!Schema::hasColumn('evt_ingressos_convidados_tb', 'whatsapp')) {
                    $table->string('whatsapp', 30)->nullable()->after('email');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('evt_ingressos_convidados_tb')) {
            Schema::table('evt_ingressos_convidados_tb', function (Blueprint $table) {
                if (Schema::hasColumn('evt_ingressos_convidados_tb', 'whatsapp')) {
                    $table->dropColumn('whatsapp');
                }
            });
        }
    }
};


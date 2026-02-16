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
                if (!Schema::hasColumn('evt_eventos_tb','logo_img_1')) $table->string('logo_img_1',255)->nullable()->after('imagem_capa');
                if (!Schema::hasColumn('evt_eventos_tb','logo_img_2')) $table->string('logo_img_2',255)->nullable()->after('logo_img_1');
                if (!Schema::hasColumn('evt_eventos_tb','logo_img_3')) $table->string('logo_img_3',255)->nullable()->after('logo_img_2');
                if (!Schema::hasColumn('evt_eventos_tb','logo_img_4')) $table->string('logo_img_4',255)->nullable()->after('logo_img_3');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('evt_eventos_tb')) {
            Schema::table('evt_eventos_tb', function (Blueprint $table) {
                if (Schema::hasColumn('evt_eventos_tb','logo_img_1')) $table->dropColumn('logo_img_1');
                if (Schema::hasColumn('evt_eventos_tb','logo_img_2')) $table->dropColumn('logo_img_2');
                if (Schema::hasColumn('evt_eventos_tb','logo_img_3')) $table->dropColumn('logo_img_3');
                if (Schema::hasColumn('evt_eventos_tb','logo_img_4')) $table->dropColumn('logo_img_4');
            });
        }
    }
};


<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evt_eventos_tb', function (Blueprint $table) {
            if (!Schema::hasColumn('evt_eventos_tb', 'is_destaque')) {
                $table->boolean('is_destaque')->default(false)->after('video_youtube_url');
            }
            if (!Schema::hasColumn('evt_eventos_tb', 'comprovante_pagamento')) {
                $table->string('comprovante_pagamento')->nullable()->after('is_destaque');
            }
        });
    }

    public function down(): void
    {
        Schema::table('evt_eventos_tb', function (Blueprint $table) {
            if (Schema::hasColumn('evt_eventos_tb', 'comprovante_pagamento')) {
                $table->dropColumn('comprovante_pagamento');
            }
            if (Schema::hasColumn('evt_eventos_tb', 'is_destaque')) {
                $table->dropColumn('is_destaque');
            }
        });
    }
};

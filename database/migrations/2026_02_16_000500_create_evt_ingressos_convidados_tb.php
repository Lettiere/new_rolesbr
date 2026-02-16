<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('evt_ingressos_convidados_tb')) {
            Schema::create('evt_ingressos_convidados_tb', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('evento_id')->index();
                $table->unsignedBigInteger('lote_id')->index();
                $table->string('codigo_unico', 32)->index();
                $table->boolean('is_titular')->default(false)->index();
                $table->string('titular_nome', 255)->nullable();
                $table->string('titular_cpf', 20)->nullable();
                $table->string('nome', 255);
                $table->string('cpf', 20)->nullable();
                $table->date('data_nascimento')->nullable();
                $table->string('email', 255)->nullable();
                $table->string('whatsapp', 30)->nullable();
                $table->timestamps();
                $table->index(['evento_id', 'lote_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('evt_ingressos_convidados_tb');
    }
};


<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('establishment_social_links', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bares_id');
            $table->string('network', 50);
            $table->string('handle', 255);
            $table->timestamps();

            $table->index(['bares_id', 'network']);
        });

        Schema::create('establishment_facilities', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 100)->unique();
            $table->string('nome', 150);
            $table->string('descricao', 255)->nullable();
            $table->boolean('ativo')->default(true);
            $table->unsignedInteger('ordem')->default(0);
            $table->timestamps();
        });

        Schema::create('establishment_facility_pivot', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bares_id');
            $table->unsignedBigInteger('facility_id');
            $table->timestamps();

            $table->unique(['bares_id', 'facility_id']);
            $table->index('facility_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('establishment_facility_pivot');
        Schema::dropIfExists('establishment_facilities');
        Schema::dropIfExists('establishment_social_links');
    }
};


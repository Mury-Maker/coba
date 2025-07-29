<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('database_data', function (Blueprint $table) {
            $table->id('id_database'); // Primary key kustom 'id_database'
            $table->foreignId('use_case_id')->constrained('use_cases')->onDelete('cascade'); // Mereferensikan 'id' di tabel 'use_cases'
            $table->text('keterangan')->nullable();
            // Kolom 'gambar_database' dihapus
            $table->text('relasi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('database_data');
    }
};

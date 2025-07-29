<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('uat_data', function (Blueprint $table) {
            $table->id('id_uat'); // Primary key kustom 'id_uat'
            $table->foreignId('use_case_id')->constrained('use_cases')->onDelete('cascade'); // Mereferensikan 'id' di tabel 'use_cases'
            $table->string('nama_proses_usecase');
            $table->text('keterangan_uat')->nullable();
            $table->string('status_uat')->nullable();
            // Kolom 'gambar_uat' dihapus
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('uat_data');
    }
};

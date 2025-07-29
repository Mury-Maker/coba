<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('uat_images', function (Blueprint $table) {
            $table->id(); // Primary key default 'id'
            // Perbaikan di sini: Menentukan secara eksplisit 'id_uat' sebagai kolom yang direferensikan
            $table->foreignId('uat_data_id')->constrained('uat_data', 'id_uat')->onDelete('cascade');
            $table->string('path'); // Path penyimpanan file gambar
            $table->string('filename')->nullable(); // Nama asli file gambar
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('uat_images');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('report_images', function (Blueprint $table) {
            $table->id(); // Primary key default 'id'
            // Perbaikan di sini: Menentukan secara eksplisit 'id_report' sebagai kolom yang direferensikan
            $table->foreignId('report_data_id')->constrained('report_data', 'id_report')->onDelete('cascade');
            $table->string('path'); // Path penyimpanan file gambar
            $table->string('filename')->nullable(); // Nama asli file gambar
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_images');
    }
};

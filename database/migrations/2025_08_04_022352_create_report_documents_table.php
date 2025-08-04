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
        Schema::create('report_documents', function (Blueprint $table) {
            $table->id();
            // Foreign key yang merujuk ke tabel 'report_data'
            $table->foreignId('report_data_id')->constrained('report_data', 'id_report')->onDelete('cascade');
            $table->string('path'); // Path penyimpanan file
            $table->string('filename')->nullable(); // Nama asli file
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_documents');
    }
};

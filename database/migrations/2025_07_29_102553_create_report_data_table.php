<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_data', function (Blueprint $table) {
            $table->id('id_report'); // Primary key kustom 'id_report'
            $table->foreignId('use_case_id')->constrained('use_cases')->onDelete('cascade'); // Mereferensikan 'id' di tabel 'use_cases'
            $table->string('aktor');
            $table->string('nama_report');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_data');
    }
};

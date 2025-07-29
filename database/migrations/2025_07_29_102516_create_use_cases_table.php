<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('use_cases', function (Blueprint $table) {
            $table->id(); // Primary key default 'id'
            $table->unsignedInteger('menu_id'); // Foreign key ke navmenu (sesuaikan tipe data dengan menu_id di navmenu)
            $table->string('usecase_id')->nullable()->comment('ID UseCase seperti di video, misal: UC-TAMBAH-SISWA');
            $table->string('nama_proses');
            $table->text('deskripsi_aksi')->nullable();
            $table->string('aktor')->nullable();
            $table->text('tujuan')->nullable();
            $table->text('kondisi_awal')->nullable();
            $table->text('kondisi_akhir')->nullable();
            $table->text('aksi_reaksi')->nullable();
            $table->text('reaksi_sistem')->nullable();
            $table->timestamps();

            // Foreign key constraint ke tabel 'navmenu'
            $table->foreign('menu_id')->references('menu_id')->on('navmenu')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('use_cases');
    }
};

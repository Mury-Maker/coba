<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('navmenu', function (Blueprint $table) {
            $table->increments('menu_id'); // Primary key kustom 'menu_id'
            $table->unsignedBigInteger('category_id'); // Foreign key ke tabel categories
            $table->string('menu_nama', 50)->nullable();
            $table->string('menu_link', 100);
            $table->string('menu_icon', 30)->default('fa-regular fa-circle')->nullable();
            $table->unsignedInteger('menu_child')->default(0); // ID parent menu
            $table->integer('menu_order')->default(0); // Urutan menu
            $table->boolean('menu_status')->default(true)->comment('0=folder, 1=memiliki daftar use case');
            // Kolom 'category' string lama dihapus
            $table->timestamps(); // Tambahkan timestamps jika belum ada

            // Foreign key constraint ke tabel 'categories'
            $table->foreign('category_id')
                  ->references('id')->on('categories')
                  ->onDelete('cascade'); // Jika kategori dihapus, menu-menu ikut terhapus
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('navmenu');
    }
};

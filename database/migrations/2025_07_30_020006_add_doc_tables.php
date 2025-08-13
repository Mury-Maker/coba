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
        Schema::create('doc_tables', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id'); //Foreign key ke navmenu_id
            $table->string('nama_tabel');
            $table->text('syntax');
            $table->timestamps();

            // Foreign Key
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doc_tables');
    }
};

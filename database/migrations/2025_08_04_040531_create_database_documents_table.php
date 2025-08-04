<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('database_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('database_data_id')->constrained('database_data', 'id_database')->onDelete('cascade');
            $table->string('path');
            $table->string('filename')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('database_documents');
    }
};

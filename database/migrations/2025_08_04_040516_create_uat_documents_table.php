<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('uat_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('uat_data_id')->constrained('uat_data', 'id_uat')->onDelete('cascade');
            $table->string('path');
            $table->string('filename')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('uat_documents');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recursos', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->text('descricao');
            $table->enum('tipo', ['video', 'pdf', 'link']);
            $table->string('url');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('tipo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recursos');
    }
};

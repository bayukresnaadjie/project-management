<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Board columns per project
        Schema::create('boards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('nama');
            $table->string('warna', 20)->default('#6B7280');
            $table->integer('urutan')->default(0);
            $table->boolean('is_done')->default(false);     // kolom "selesai"
            $table->boolean('is_default')->default(false);  // kolom default saat buat task
            $table->timestamps();
        });

        // Sprint untuk project tipe Scrum
        Schema::create('sprints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('nama');
            $table->text('tujuan')->nullable();
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->enum('status', ['perencanaan','aktif','selesai'])->default('perencanaan');
            $table->integer('kapasitas')->default(0);       // story points
            $table->timestamps();
        });

        // Label untuk task
        Schema::create('labels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('nama', 50);
            $table->string('warna', 20)->default('#3B82F6');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('labels');
        Schema::dropIfExists('sprints');
        Schema::dropIfExists('boards');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('board_id')->constrained('boards')->cascadeOnDelete();
            $table->foreignId('sprint_id')->nullable()->constrained('sprints')->nullOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('tasks')->nullOnDelete();
            $table->foreignId('dibuat_oleh')->constrained('users');
            $table->foreignId('assignee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nomor', 20)->unique();           // e.g. WEB-001, WEB-002
            $table->string('judul');
            $table->longText('deskripsi')->nullable();
            $table->enum('tipe', ['task','bug','feature','improvement','epic'])->default('task');
            $table->enum('prioritas', ['critical','high','medium','low'])->default('medium');
            $table->integer('story_point')->default(0);
            $table->integer('estimasi_jam')->default(0);
            $table->integer('aktual_jam')->default(0);
            $table->integer('progres')->default(0);         // 0-100%
            $table->date('due_date')->nullable();
            $table->timestamp('selesai_at')->nullable();
            $table->integer('urutan')->default(0);          // urutan di board column
            $table->timestamps();
            $table->softDeletes();
        });

        // Assignee bisa lebih dari 1
        Schema::create('task_assignees', function (Blueprint $table) {
            $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->primary(['task_id','user_id']);
        });

        // Label per task
        Schema::create('task_labels', function (Blueprint $table) {
            $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
            $table->foreignId('label_id')->constrained('labels')->cascadeOnDelete();
            $table->primary(['task_id','label_id']);
        });

        // Komentar task
        Schema::create('task_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('task_comments')->nullOnDelete();
            $table->text('isi');
            $table->timestamps();
            $table->softDeletes();
        });

        // Attachment task
        Schema::create('task_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('nama_file');
            $table->string('path_file');
            $table->string('mime_type', 100);
            $table->bigInteger('ukuran')->default(0);
            $table->timestamps();
        });

        // Activity log per task
        Schema::create('task_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('aksi');                         // moved, assigned, commented, dll
            $table->string('field')->nullable();            // field yang diubah
            $table->text('nilai_lama')->nullable();
            $table->text('nilai_baru')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        // Notifikasi
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('dari_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('task_id')->nullable()->constrained('tasks')->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->string('judul');
            $table->text('pesan');
            $table->string('tipe')->default('info');        // assigned, commented, due, moved
            $table->string('url')->nullable();
            $table->boolean('dibaca')->default(false);
            $table->timestamp('dibaca_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('task_activities');
        Schema::dropIfExists('task_attachments');
        Schema::dropIfExists('task_comments');
        Schema::dropIfExists('task_labels');
        Schema::dropIfExists('task_assignees');
        Schema::dropIfExists('tasks');
    }
};

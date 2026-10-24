<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar')->nullable()->after('email');
            $table->string('jabatan')->nullable()->after('avatar');
            $table->string('bio')->nullable()->after('jabatan');
            $table->boolean('aktif')->default(true)->after('bio');
            $table->timestamp('last_seen')->nullable()->after('aktif');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['avatar','jabatan','bio','aktif','last_seen']);
        });
    }
};

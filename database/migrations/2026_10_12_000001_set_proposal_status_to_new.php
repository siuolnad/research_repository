<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('proposals')->update(['status' => 'new']);

        Schema::table('proposals', function (Blueprint $table) {
            $table->string('status')->default('new')->change();
        });
    }

    public function down(): void
    {
        Schema::table('proposals', function (Blueprint $table) {
            $table->string('status')->default('draft')->change();
        });
    }
};

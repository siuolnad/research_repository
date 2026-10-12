<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The proponent is now the account that owns the proposal (proposals.user_id).
     */
    public function up(): void
    {
        Schema::table('proposals', function (Blueprint $table) {
            $table->dropColumn('proponent');
        });
    }

    public function down(): void
    {
        Schema::table('proposals', function (Blueprint $table) {
            $table->string('proponent')->default('')->after('title');
        });

        DB::table('proposals')->orderBy('id')->each(function (object $proposal): void {
            $name = DB::table('users')->where('id', $proposal->user_id)->value('name');

            DB::table('proposals')->where('id', $proposal->id)->update(['proponent' => $name ?? '']);
        });
    }
};

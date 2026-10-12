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
        Schema::table('proposals', function (Blueprint $table) {
            $table->string('proponent')->nullable()->after('title');
        });

        // Keep the first name of each existing list of proponents.
        DB::table('proposals')->orderBy('id')->each(function (object $proposal): void {
            $names = json_decode($proposal->proponents, true);

            DB::table('proposals')->where('id', $proposal->id)->update([
                'proponent' => is_array($names) && $names !== [] ? (string) $names[0] : '',
            ]);
        });

        Schema::table('proposals', function (Blueprint $table) {
            $table->dropColumn('proponents');
        });

        Schema::table('proposals', function (Blueprint $table) {
            $table->string('proponent')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('proposals', function (Blueprint $table) {
            $table->json('proponents')->nullable()->after('title');
        });

        DB::table('proposals')->orderBy('id')->each(function (object $proposal): void {
            DB::table('proposals')->where('id', $proposal->id)->update([
                'proponents' => json_encode([$proposal->proponent]),
            ]);
        });

        Schema::table('proposals', function (Blueprint $table) {
            $table->dropColumn('proponent');
        });
    }
};

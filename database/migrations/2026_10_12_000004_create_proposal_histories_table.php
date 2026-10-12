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
        Schema::create('proposal_histories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('proposal_id')->constrained()->cascadeOnDelete();

            $table->string('event');
            $table->text('description')->nullable();

            $table->timestamp('created_at')->useCurrent();

            $table->index(['proposal_id', 'id']);
        });

        $this->backfill();
    }

    public function down(): void
    {
        Schema::dropIfExists('proposal_histories');
    }

    /**
     * Give proposals that already exist a starting history.
     */
    private function backfill(): void
    {
        DB::table('proposals')->orderBy('id')->each(function (object $proposal): void {
            DB::table('proposal_histories')->insert([
                'proposal_id' => $proposal->id,
                'event' => 'created',
                'description' => null,
                'created_at' => $proposal->created_at,
            ]);

            if (in_array($proposal->status, ['approved', 'disapproved'], true)) {
                DB::table('proposal_histories')->insert([
                    'proposal_id' => $proposal->id,
                    'event' => $proposal->status,
                    'description' => $proposal->status === 'disapproved' ? $proposal->remarks : null,
                    'created_at' => $proposal->updated_at,
                ]);
            }
        });
    }
};

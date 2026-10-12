<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proposals', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->text('abstract');
            $table->json('proponents');
            $table->json('objectives');
            $table->json('keywords')->nullable();

            $table->string('research_area')->index();

            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('budget', 15, 2);

            $table->string('status')->default('new')->index();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'start_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proposals');
    }
};

<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proposal_files', function (Blueprint $table) {
            $table->id();

            $table->foreignId('proposal_id')->constrained()->cascadeOnDelete();

            $table->string('name');
            $table->string('path');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proposal_files');
    }
};

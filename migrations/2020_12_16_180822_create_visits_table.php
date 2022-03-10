<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVisitsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create(
            config('visit.table_names.pivot'),
            function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->unsignedBigInteger(config('visit.column_names.user_foreign_key'))
                    ->index()
                    ->nullable()
                    ->comment('user_id');
                $table->morphs('visitable');
                $table->timestamps();
                $table->index([config('visit.column_names.user_foreign_key'), 'visitable_type', 'visitable_id']);
            }
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('visit.table_names.visits'));
    }
}

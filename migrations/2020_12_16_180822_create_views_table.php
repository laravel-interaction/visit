<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateViewsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create(
            config('eloquent-view.table_names.views'),
            function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->unsignedBigInteger(config('eloquent-view.column_names.user_foreign_key'))->index()->nullable()->comment('user_id');
                $table->morphs('viewable');
                $table->timestamps();
                $table->index([config('eloquent-view.column_names.user_foreign_key'), 'viewable_type', 'viewable_id']);
            }
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('eloquent-view.table_names.views'));
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create generations without the composite current-attempt foreign key.
     * That key is added after generation_attempts exists.
     */
    public function up(): void
    {
        Schema::create('generations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->text('project_id');
            $table->text('request_id');
            $table->text('status');
            $table->uuid('current_attempt_id')->nullable();
            $table->jsonb('sitespec')->nullable();
            $table->jsonb('revision_metadata')->nullable();
            $table->jsonb('usage')->nullable();
            $table->timestampTz('accepted_at', 6)->nullable();
            $table->timestampTz('completed_at', 6)->nullable();
            $table->timestampTz('expires_at', 6)->nullable();
            $table->timestampsTz(6);

            $table->unique(['project_id', 'request_id'], 'generations_project_request_unique');
            $table->index(['project_id', 'status'], 'generations_project_status_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('generations');
    }
};

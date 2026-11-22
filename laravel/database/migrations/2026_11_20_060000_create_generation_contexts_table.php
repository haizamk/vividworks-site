<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Request context lives beside generations. It is not part of sitespec,
     * revision_metadata, or the callback hash.
     */
    public function up(): void
    {
        Schema::create('generation_contexts', function (Blueprint $table) {
            $table->uuid('generation_id')->primary();
            $table->text('project_id');
            $table->text('request_id');
            $table->unsignedTinyInteger('page_count');
            $table->json('active_templates');
            $table->json('media_allowlist')->nullable();
            $table->timestampsTz(6);

            $table->unique(['project_id', 'request_id'], 'generation_contexts_project_request_unique');
            $table->foreign('generation_id', 'generation_contexts_generation_id_foreign')
                ->references('id')
                ->on('generations')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('generation_contexts');
    }
};

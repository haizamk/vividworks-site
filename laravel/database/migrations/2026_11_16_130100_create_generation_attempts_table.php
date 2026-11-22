<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Attempts reference generations. The composite foreign key is added only
     * after both tables and UNIQUE (generation_id, id) exist.
     *
     * PostgreSQL enforces generations (id, current_attempt_id) →
     * generation_attempts (generation_id, id) ON DELETE RESTRICT. A null
     * current_attempt_id is allowed because PostgreSQL uses MATCH SIMPLE.
 * SQLite cannot add a foreign key or check constraint with ALTER TABLE, so
 * the composite foreign key and http_status range check are PostgreSQL-only.
 * Application code still rechecks the generation id and rejects status 0.
     */
    public function up(): void
    {
        Schema::create('generation_attempts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('generation_id');
            $table->text('job_id')->nullable();
            $table->integer('attempt_number');
            $table->text('status');
            $table->timestampTz('accepted_at', 6)->nullable();
            $table->timestampTz('submitted_at', 6)->nullable();
            $table->timestampTz('completed_at', 6)->nullable();
            $table->integer('latency_ms')->nullable();
            $table->integer('http_status')->nullable();
            $table->text('error_code')->nullable();
            $table->text('error_message')->nullable();
            $table->jsonb('error_details')->nullable();
            $table->boolean('retryable')->nullable();
            $table->char('callback_payload_hash', 64)->nullable();
            $table->timestampsTz(6);

            $table->unique(['generation_id', 'id'], 'generation_attempts_generation_id_id_unique');
            $table->unique(['generation_id', 'attempt_number'], 'generation_attempts_generation_number_unique');
            $table->foreign('generation_id', 'generation_attempts_generation_id_foreign')
                ->references('id')
                ->on('generations')
                ->restrictOnDelete();
        });

        DB::statement('CREATE UNIQUE INDEX generation_attempts_job_id_unique ON generation_attempts (job_id) WHERE job_id IS NOT NULL');
        DB::statement("CREATE UNIQUE INDEX generation_attempts_one_open_unique ON generation_attempts (generation_id) WHERE status IN ('pending', 'submitted', 'accepted')");

        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE generation_attempts ADD CONSTRAINT generation_attempts_http_status_range CHECK (http_status IS NULL OR (http_status >= 100 AND http_status <= 599))');
            DB::statement('ALTER TABLE generations ADD CONSTRAINT generations_current_attempt_same_generation FOREIGN KEY (id, current_attempt_id) REFERENCES generation_attempts (generation_id, id) ON DELETE RESTRICT');
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE generations DROP CONSTRAINT IF EXISTS generations_current_attempt_same_generation');
            DB::statement('ALTER TABLE generation_attempts DROP CONSTRAINT IF EXISTS generation_attempts_http_status_range');
        }

        Schema::dropIfExists('generation_attempts');
    }
};

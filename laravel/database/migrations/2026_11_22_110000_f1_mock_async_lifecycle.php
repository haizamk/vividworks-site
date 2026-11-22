<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * F1 mock tables. No retry schedule and no callback id generator.
     * invoke_correlation_key stays empty until D-FUTURE is decided.
     */
    public function up(): void
    {
        Schema::table('generation_attempts', function (Blueprint $table) {
            $table->text('invoke_correlation_key')->nullable();
        });

        DB::statement('DROP INDEX IF EXISTS generation_attempts_one_open_unique');
        DB::statement("CREATE UNIQUE INDEX generation_attempts_one_open_unique ON generation_attempts (generation_id) WHERE status IN ('pending', 'submitted', 'pending_invoke', 'invoking', 'accepted')");

        Schema::create('callback_inbox', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestampTz('received_at', 6);
            $table->text('raw_body');
            $table->char('payload_hash', 64);
            $table->text('callback_id')->nullable();
            $table->text('job_id')->nullable();
            $table->text('request_id')->nullable();
            $table->text('project_id')->nullable();
            $table->text('supplied_attempt_id')->nullable();
            $table->text('classification')->nullable();
            $table->timestampTz('processed_at', 6)->nullable();
            $table->timestampsTz(6);
        });

        DB::statement('CREATE UNIQUE INDEX callback_inbox_callback_id_unique ON callback_inbox (callback_id) WHERE callback_id IS NOT NULL');

        Schema::create('generation_outbox', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('attempt_id');
            $table->text('state');
            $table->text('delivery_classification')->nullable();
            $table->json('body');
            $table->integer('http_status')->nullable();
            $table->timestampTz('claimed_at', 6)->nullable();
            $table->timestampTz('dead_at', 6)->nullable();
            $table->timestampsTz(6);

            $table->foreign('attempt_id', 'generation_outbox_attempt_id_foreign')
                ->references('id')
                ->on('generation_attempts')
                ->restrictOnDelete();
        });

        DB::statement("CREATE UNIQUE INDEX generation_outbox_one_live_unique ON generation_outbox (attempt_id) WHERE state IN ('pending', 'sending')");

        Schema::create('generation_audit_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->text('event');
            $table->uuid('generation_id')->nullable();
            $table->uuid('attempt_id')->nullable();
            $table->uuid('outbox_id')->nullable();
            $table->uuid('inbox_id')->nullable();
            $table->json('context');
            $table->timestampTz('created_at', 6);
        });

        Schema::create('generation_reopens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('generation_id');
            $table->uuid('previous_attempt_id');
            $table->uuid('new_attempt_id');
            $table->text('actor');
            $table->text('reason');
            $table->boolean('duplicate_risk_acknowledged');
            $table->timestampTz('created_at', 6);

            $table->foreign('generation_id', 'generation_reopens_generation_id_foreign')
                ->references('id')
                ->on('generations')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('generation_reopens');
        Schema::dropIfExists('generation_audit_events');
        Schema::dropIfExists('generation_outbox');
        Schema::dropIfExists('callback_inbox');

        DB::statement('DROP INDEX IF EXISTS generation_attempts_one_open_unique');
        DB::statement("CREATE UNIQUE INDEX generation_attempts_one_open_unique ON generation_attempts (generation_id) WHERE status IN ('pending', 'submitted', 'accepted')");

        Schema::table('generation_attempts', function (Blueprint $table) {
            $table->dropColumn('invoke_correlation_key');
        });
    }
};

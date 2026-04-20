<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('workflow_instances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_template_id')->constrained()->restrictOnDelete();
            $table->string('trigger_event');
            $table->string('source_type');
            $table->unsignedBigInteger('source_id');
            $table->string('status');
            $table->foreignId('requested_by_user_id')->constrained('users')->restrictOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['source_type', 'source_id']);
            $table->index(['status', 'source_type', 'source_id']);
            $table->index('workflow_template_id');
        });

        Schema::create('workflow_instance_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_instance_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sequence');
            $table->string('status');
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_role_id')->nullable()->constrained('roles')->nullOnDelete();
            $table->foreignId('actioned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('decision')->nullable();
            $table->text('comment')->nullable();
            $table->timestamp('actioned_at')->nullable();
            $table->timestamps();

            $table->unique(['workflow_instance_id', 'sequence'], 'uq_workflow_instance_steps_instance_sequence');
            $table->index(['status', 'assigned_user_id'], 'idx_wis_status_user');
            $table->index(['status', 'assigned_role_id'], 'idx_wis_status_role');
            $table->index(['workflow_instance_id', 'status'], 'idx_wis_instance_status');
        });

        Schema::create('workflow_step_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_instance_step_id')->constrained()->cascadeOnDelete();
            $table->foreignId('workflow_instance_id')->constrained()->cascadeOnDelete();
            $table->foreignId('acted_by')->constrained('users')->restrictOnDelete();
            $table->string('decision');
            $table->text('comment')->nullable();
            $table->timestamp('acted_at');
            $table->timestamps();

            $table->index(['workflow_instance_id', 'acted_at']);
            $table->index(['workflow_instance_step_id', 'acted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_step_actions');
        Schema::dropIfExists('workflow_instance_steps');
        Schema::dropIfExists('workflow_instances');
    }
};

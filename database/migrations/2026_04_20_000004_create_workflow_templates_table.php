<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('workflow_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('trigger_event');
            $table->boolean('is_active')->default(false);
            $table->timestamps();

            $table->unique(['trigger_event', 'is_active'], 'uq_workflow_templates_trigger_event_is_active');
            $table->index('trigger_event');
        });

        Schema::create('workflow_template_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_template_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sequence');
            $table->string('approver_type');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('role_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->unique(['workflow_template_id', 'sequence'], 'uq_workflow_template_steps_template_sequence');
            $table->index(['approver_type', 'user_id']);
            $table->index(['approver_type', 'role_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_template_steps');
        Schema::dropIfExists('workflow_templates');
    }
};

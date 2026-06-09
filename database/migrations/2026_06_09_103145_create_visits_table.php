<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // salesperson
            $table->date('visit_date')->index();
            $table->string('person_met')->nullable();
            $table->string('contact_phone')->nullable();
            // Pipeline stage recorded at this visit.
            $table->string('visit_level')->default('cold')->index();
            $table->boolean('decision_maker_met')->default(false);
            $table->boolean('interested')->default(false);
            $table->boolean('follow_up_done')->default(false);
            $table->decimal('revenue_potential', 12, 2)->default(0); // RM
            $table->text('notes')->nullable();
            $table->string('photo_path')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'visit_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};

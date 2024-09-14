<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\LucasDotVin\Soulbscription\Models\Plan::class);
            $table->timestamp('canceled_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamp('grace_days_ended_at')->nullable();
            $table->date('started_at')->nullable();
            $table->date('end_at')->nullable();
            $table->string('status')->nullable();
            $table->timestamp('suppressed_at')->nullable();
            $table->boolean('was_switched')->default(false);
            $table->softDeletes();
            $table->timestamps();
            $table->unsignedBigInteger('comapny_id')->nullable();
            $table->foreign('comapny_id')->references('id')->on('companies')->onUpdate('cascade')
                ->onDelete('cascade');
            $table->string('tenant_id')->nullable();
            $table->foreign('tenant_id')
                ->references('id')->on('tenants')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->string('subdomain')->nullable();
            if (config('soulbscription.models.subscriber.uses_uuid')) {
                $table->uuidMorphs('subscriber');
            } else {
                $table->numericMorphs('subscriber');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subscriptions');
    }
};

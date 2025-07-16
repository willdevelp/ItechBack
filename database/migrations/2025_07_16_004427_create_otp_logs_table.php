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
        Schema::create('otp_logs', function (Blueprint $table) {
            $table->id();
            $table->string('identifier'); // email ou numéro de téléphone
            $table->string('method'); // 'email' ou 'phone'
            $table->string('code');
            $table->string('ip_address');
            $table->text('user_agent')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->index('identifier'); // Index pour identifier rapidement les logs par email ou téléphone
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('otp_logs');
    }
};

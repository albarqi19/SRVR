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
        Schema::create('whatsapp_conversations', function (Blueprint $table) {
            $table->id();
            $table->string('phone_number')->comment('رقم الهاتف');
            $table->string('current_state')->default('idle')->comment('الحالة الحالية للمحادثة');
            $table->json('context_data')->nullable()->comment('بيانات السياق');
            $table->timestamp('expires_at')->nullable()->comment('وقت انتهاء الصلاحية');
            $table->timestamps();
            
            // إضافة فهارس
            $table->index('phone_number');
            $table->index('current_state');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_conversations');
    }
};

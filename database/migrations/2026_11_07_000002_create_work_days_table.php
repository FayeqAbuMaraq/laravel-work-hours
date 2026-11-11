<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->dateTime('started_at');
            // null = المستخدم سجّل دخول ولسا ما سجّل خروج
            $table->dateTime('ended_at')->nullable();
            // معرّف فريد من الجهاز: يمنع تكرار السجل عند المزامنة بعد انقطاع الإنترنت
            $table->uuid('client_uuid')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'started_at']);
            $table->unique(['user_id', 'client_uuid']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_days');
    }
};

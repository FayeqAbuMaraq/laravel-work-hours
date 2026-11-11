<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ينشئ جدول users الأساسي فقط إذا كان غير موجود في المشروع.
// (الأعمدة الإضافية username و hourly_rate تُضاف بالمهاجرات اللاحقة)
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users')) {
            return;
        }

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable()->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // لا نحذف الجدول هنا: قد يكون تابعاً للمشروع الأصلي
    }
};

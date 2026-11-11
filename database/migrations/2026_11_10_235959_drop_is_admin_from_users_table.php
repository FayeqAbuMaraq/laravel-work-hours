<?php

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// يحذف عمود is_admin القديم (إن وُجد) بعد نقل المشرفين إلى دور admin في Spatie.
// اسم الملف متأخر عمداً ليعمل بعد migration جداول Spatie (publish) إن نُشرت في نفس اليوم.
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'is_admin')) {
            return;
        }

        // جداول Spatie موجودة؟ انقل المشرفين الحاليين إلى الدور
        if (Schema::hasTable('roles') && Schema::hasTable('model_has_roles')) {
            (new RolesAndPermissionsSeeder())->run();

            $adminIds = DB::table('users')->where('is_admin', true)->pluck('id');
            User::whereIn('id', $adminIds)->get()->each->assignRole('admin');
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_admin');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('users', 'is_admin')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_admin')->default(false)->after('hourly_rate');
            });
        }
    }
};

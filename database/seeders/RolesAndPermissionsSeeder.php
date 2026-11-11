<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /** الصلاحيات المعرّفة في النظام (أضف هنا أي صلاحية جديدة لاحقاً). */
    public const PERMISSIONS = [
        'view-admin-dashboard', // فتح لوحة المشرف ورؤية كل المستخدمين ورواتبهم
    ];

    /** آمن للتشغيل أكثر من مرة (findOrCreate + syncPermissions). */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::PERMISSIONS as $name) {
            Permission::findOrCreate($name, 'web');
        }

        // مشرف: كل الصلاحيات
        Role::findOrCreate('admin', 'web')->syncPermissions(Permission::all());

        // مستخدم عادي: بدون صلاحيات إدارية
        Role::findOrCreate('user', 'web');

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}

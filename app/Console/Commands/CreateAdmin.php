<?php

namespace App\Console\Commands;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateAdmin extends Command
{
    protected $signature = 'admin:create {username? : اسم المستخدم أو رقم الهاتف}';

    protected $description = 'إنشاء مشرف (دور admin) جديد أو ترقية مستخدم موجود إلى مشرف';

    public function handle(): int
    {
        $username = Str::lower(trim($this->argument('username') ?: $this->ask('اسم المستخدم (أو رقم الهاتف) للمشرف')));

        if (! preg_match('/^[A-Za-z0-9_.+\-]{3,30}$/', $username)) {
            $this->error('اسم المستخدم غير صالح: أحرف إنجليزية وأرقام فقط (3 إلى 30 خانة).');

            return self::FAILURE;
        }

        // يتأكد من وجود الأدوار والصلاحيات (آمن للتكرار)
        (new RolesAndPermissionsSeeder())->run();

        $user = User::where('username', $username)->first();

        if ($user) {
            $user->assignRole('admin');
            $this->info("تمت ترقية المستخدم «{$user->name}» ({$username}) إلى مشرف.");

            return self::SUCCESS;
        }

        $name = $this->ask('الاسم', 'فايق أبو مرق');
        $password = $this->secret('كلمة المرور (6 خانات على الأقل)');

        if (strlen((string) $password) < 6) {
            $this->error('كلمة المرور قصيرة.');

            return self::FAILURE;
        }
        if ($password !== $this->secret('أعد كتابة كلمة المرور')) {
            $this->error('كلمتا المرور غير متطابقتين.');

            return self::FAILURE;
        }

        $user = User::create([
            'name' => $name,
            'username' => $username,
            'password' => Hash::make($password),
            'hourly_rate' => 4,
        ]);
        $user->assignRole('admin');

        $this->info("تم إنشاء المشرف «{$name}» باسم المستخدم {$username}.");

        return self::SUCCESS;
    }
}
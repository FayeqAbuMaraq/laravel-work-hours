<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
    private const USERNAME_RULE = ['required', 'string', 'min:3', 'max:30', 'regex:/^[A-Za-z0-9_.+\-]+$/'];

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $username = Str::lower(trim($credentials['username']));

        // حد المحاولات: 5 محاولات فاشلة بالدقيقة لكل (مستخدم + IP)
        $key = 'login:' . $username . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            return back()
                ->withErrors(['username' => "محاولات دخول كثيرة، حاول مرة أخرى بعد {$seconds} ثانية"])
                ->onlyInput('username');
        }

        if (Auth::attempt(['username' => $username, 'password' => $credentials['password']], $request->boolean('remember'))) {
            RateLimiter::clear($key);
            $request->session()->regenerate();

            return redirect()->intended(route('work-days.index'))
                ->with('success', 'أهلاً بعودتك!');
        }

        RateLimiter::hit($key, 60);

        return back()->withErrors(['username' => 'اسم المستخدم أو كلمة المرور غير صحيحة'])->onlyInput('username');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->merge(['username' => Str::lower(trim((string) $request->input('username')))]);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'username' => array_merge(self::USERNAME_RULE, ['unique:users,username']),
            'password' => 'required|string|min:6|confirmed',
            'hourly_rate' => 'nullable|numeric|min:0.5|max:10000',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'username' => $data['username'],
            'password' => Hash::make($data['password']),
            'hourly_rate' => $data['hourly_rate'] ?? 4,
        ]);

        // كل حساب جديد = دور "user" فقط (الأدمن يُعيَّن عبر php artisan admin:create)
        $user->assignRole(Role::findOrCreate('user', 'web'));

        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->route('work-days.index')->with('success', 'تم إنشاء حسابك بنجاح');
    }

    /** تسجيل الخروج اليدوي */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'تم تسجيل الخروج');
    }
}

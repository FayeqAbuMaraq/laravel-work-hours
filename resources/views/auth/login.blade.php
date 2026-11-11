@extends('layouts.guest')
@section('title', 'تسجيل الدخول')

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
    <h2 class="font-bold text-lg mb-4">تسجيل الدخول</h2>
    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">اسم المستخدم أو رقم الهاتف</label>
            <input type="text" name="username" value="{{ old('username') }}" required autofocus autocomplete="username" inputmode="text" dir="ltr"
                   class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">كلمة المرور</label>
            <input type="password" name="password" required autocomplete="current-password" dir="ltr"
                   class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
        </div>
        <label class="flex items-center gap-2 text-sm text-slate-600">
            <input type="checkbox" name="remember" value="1" checked class="rounded border-slate-300 text-indigo-600"> تذكرني على هذا الجهاز
        </label>
        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2.5 rounded-xl transition">دخول</button>
    </form>
    <p class="text-sm text-center text-slate-500 mt-4">ما عندك حساب؟ <a href="{{ route('register') }}" class="text-indigo-600 font-medium">أنشئ حساب</a></p>
</div>
@endsection

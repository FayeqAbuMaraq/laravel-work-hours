@extends('layouts.guest')
@section('title', 'إنشاء حساب')

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
    <h2 class="font-bold text-lg mb-4">إنشاء حساب جديد</h2>
    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">الاسم</label>
            <input type="text" name="name" value="{{ old('name') }}" required
                   class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">اسم المستخدم أو رقم الهاتف</label>
            <input type="text" name="username" value="{{ old('username') }}" required autocomplete="username" dir="ltr" placeholder="مثال: 0599123456 أو ahmad"
                   class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            <p class="text-xs text-slate-400 mt-1">أحرف إنجليزية وأرقام فقط (3 إلى 30 خانة). هذا ما ستدخل به لاحقاً.</p>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">أجرة الساعة (₪) <span class="text-slate-400 font-normal">(اختياري)</span></label>
            <input type="number" name="hourly_rate" min="0.5" step="0.5" value="{{ old('hourly_rate') }}" placeholder="الافتراضي 4"
                   class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">كلمة المرور</label>
            <input type="password" name="password" required minlength="6" autocomplete="new-password" dir="ltr"
                   class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            <p class="text-xs text-slate-400 mt-1">6 خانات على الأقل.</p>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">تأكيد كلمة المرور</label>
            <input type="password" name="password_confirmation" required minlength="6" autocomplete="new-password" dir="ltr"
                   class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
        </div>
        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2.5 rounded-xl transition">إنشاء الحساب</button>
    </form>
    <p class="text-sm text-center text-slate-500 mt-4">عندك حساب؟ <a href="{{ route('login') }}" class="text-indigo-600 font-medium">سجّل دخول</a></p>
</div>
@endsection

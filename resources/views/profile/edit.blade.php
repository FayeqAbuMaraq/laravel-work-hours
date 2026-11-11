@extends('layouts.app')
@section('title', 'بياناتي الشخصية')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    {{-- بطاقة: الاسم والبريد وأجرة الساعة --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <h2 class="font-bold text-lg mb-4 flex items-center gap-2"><i data-lucide="user" class="w-5 h-5 text-indigo-500"></i> البيانات الأساسية</h2>
        <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">الاسم</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">اسم المستخدم أو رقم الهاتف</label>
                <input type="text" name="username" value="{{ old('username', $user->username) }}" required dir="ltr" class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">البريد الإلكتروني <span class="text-slate-400 font-normal">(اختياري)</span></label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" dir="ltr" class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">أجرة الساعة (₪)</label>
                <input type="number" name="hourly_rate" min="0.5" step="0.5" value="{{ old('hourly_rate', $user->hourly_rate) }}" required class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <p class="text-xs text-slate-400 mt-1">تغيير الأجرة يعيد حساب الراتب لجميع الأيام المسجلة.</p>
            </div>
            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2.5 rounded-xl transition">حفظ</button>
        </form>
    </div>

    {{-- بطاقة: تغيير كلمة المرور --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <h2 class="font-bold text-lg mb-4 flex items-center gap-2"><i data-lucide="lock" class="w-5 h-5 text-indigo-500"></i> تغيير كلمة المرور</h2>
        <form method="POST" action="{{ route('profile.password') }}" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">كلمة المرور الحالية</label>
                <input type="password" name="current_password" required dir="ltr" class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">كلمة المرور الجديدة</label>
                <input type="password" name="password" required minlength="6" dir="ltr" class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">تأكيد كلمة المرور الجديدة</label>
                <input type="password" name="password_confirmation" required minlength="6" dir="ltr" class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <button type="submit" class="w-full bg-slate-800 hover:bg-slate-900 text-white font-medium py-2.5 rounded-xl transition">تغيير كلمة المرور</button>
        </form>
    </div>
</div>
@endsection

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>@yield('title', 'حاسبة ساعات العمل')</title>
    @include('partials.head-pwa')
</head>
<body class="bg-slate-100 text-slate-800 antialiased">

{{-- شريط عدم الاتصال --}}
<div id="offline-banner" class="hidden fixed top-0 inset-x-0 z-[70] bg-amber-500 text-white text-center text-xs font-medium py-1.5 px-2">
    غير متصل بالإنترنت: الحضور والانصراف بينحفظ على جهازك وبيتزامن لما يرجع الاتصال
</div>

<div class="min-h-screen md:flex">

    {{-- ===== السايدبار (شاشات كبيرة) ===== --}}
    <aside class="hidden md:flex md:flex-col w-64 bg-white border-l border-slate-200 p-5 gap-6 sticky top-0 h-screen">
        <div class="flex items-center gap-3">
            <div class="bg-indigo-100 text-indigo-600 p-2.5 rounded-xl"><i data-lucide="calculator" class="w-6 h-6"></i></div>
            <span class="font-extrabold text-lg">ساعات العمل</span>
        </div>

        <nav class="space-y-1 flex-1">
            @can('view-admin-dashboard')
            <a href="{{ route('admin.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('admin.*') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-50' }}">
                <i data-lucide="shield-check" class="w-5 h-5"></i> لوحة المشرف
            </a>
            @endcan
            <a href="{{ route('attendance.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('attendance.*') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-50' }}">
                <i data-lucide="fingerprint" class="w-5 h-5"></i> الحضور والانصراف
            </a>
            <a href="{{ route('work-days.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('work-days.*') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-50' }}">
                <i data-lucide="clock" class="w-5 h-5"></i> أيام العمل
            </a>
            <a href="{{ route('withdrawals.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('withdrawals.*') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-50' }}">
                <i data-lucide="hand-coins" class="w-5 h-5"></i> المسحوبات
            </a>
        </nav>

        <div class="space-y-2">
            <button type="button" class="pwa-install-btn hidden w-full flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium py-2.5 rounded-xl transition">
                <i data-lucide="download" class="w-4 h-4"></i> تثبيت التطبيق
            </button>
            <a href="{{ route('profile.edit') }}"
               class="flex items-center gap-2 px-3 py-2.5 rounded-xl bg-slate-50 hover:bg-slate-100 text-sm font-medium text-slate-700 transition">
                <i data-lucide="user" class="w-4 h-4 text-slate-400"></i>
                <span class="truncate">{{ auth()->user()->name }}</span>
            </a>
            <form method="POST" action="{{ route('logout') }}" onsubmit="try{caches.delete('work-hours-pages-v1')}catch(e){}">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center gap-2 text-sm font-medium text-red-600 bg-red-50 hover:bg-red-100 py-2.5 rounded-xl transition">
                    <i data-lucide="log-out" class="w-4 h-4"></i> تسجيل الخروج
                </button>
            </form>
        </div>
    </aside>

    <div class="flex-1 min-w-0">
        {{-- ===== التوب بار ===== --}}
        <header class="bg-white border-b border-slate-200 px-4 py-3 flex items-center justify-between sticky top-0 z-30">
            <div class="flex items-center gap-2 md:hidden">
                <div class="bg-indigo-100 text-indigo-600 p-2 rounded-xl"><i data-lucide="calculator" class="w-5 h-5"></i></div>
                <span class="font-extrabold">ساعات العمل</span>
            </div>
            <h1 class="hidden md:block font-bold text-slate-700">@yield('title')</h1>

            <div class="flex items-center gap-2">
                <button type="button" class="pwa-install-btn hidden md:hidden flex items-center gap-1.5 bg-indigo-600 text-white text-xs font-medium px-3 py-2 rounded-xl">
                    <i data-lucide="download" class="w-4 h-4"></i> تثبيت
                </button>
                <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 text-sm font-medium text-slate-700 bg-slate-50 hover:bg-slate-100 px-3 py-2 rounded-xl transition">
                    <i data-lucide="user" class="w-4 h-4 text-slate-400"></i>
                    <span class="max-w-[8rem] truncate">{{ auth()->user()->name }}</span>
                </a>
                <form method="POST" action="{{ route('logout') }}" onsubmit="try{caches.delete('work-hours-pages-v1')}catch(e){}" class="md:hidden">
                    @csrf
                    <button type="submit" class="p-2 rounded-xl text-red-600 bg-red-50 hover:bg-red-100" title="تسجيل الخروج">
                        <i data-lucide="log-out" class="w-4 h-4"></i>
                    </button>
                </form>
            </div>
        </header>

        <main class="max-w-6xl mx-auto p-4 md:p-8 pb-28 md:pb-8">
            @if(session('success'))
                <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-2xl px-4 py-3 text-sm font-medium">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 rounded-2xl px-4 py-3 text-sm font-medium">{{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 rounded-2xl px-4 py-3 text-sm">
                    <ul class="list-disc pr-5 space-y-1">
                        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

{{-- ===== تنقل سفلي للهاتف ===== --}}
<nav class="md:hidden fixed bottom-0 inset-x-0 bg-white border-t border-slate-200 grid {{ auth()->user()->can('view-admin-dashboard') ? 'grid-cols-5' : 'grid-cols-4' }} z-30" style="padding-bottom: env(safe-area-inset-bottom)">
    @can('view-admin-dashboard')
    <a href="{{ route('admin.index') }}" class="flex flex-col items-center gap-1 py-2.5 text-xs font-medium {{ request()->routeIs('admin.*') ? 'text-indigo-600' : 'text-slate-500' }}">
        <i data-lucide="shield-check" class="w-5 h-5"></i> المشرف
    </a>
    @endcan
    <a href="{{ route('attendance.index') }}" class="flex flex-col items-center gap-1 py-2.5 text-xs font-medium {{ request()->routeIs('attendance.*') ? 'text-indigo-600' : 'text-slate-500' }}">
        <i data-lucide="fingerprint" class="w-5 h-5"></i> الحضور
    </a>
    <a href="{{ route('work-days.index') }}" class="flex flex-col items-center gap-1 py-2.5 text-xs font-medium {{ request()->routeIs('work-days.*') ? 'text-indigo-600' : 'text-slate-500' }}">
        <i data-lucide="clock" class="w-5 h-5"></i> أيام العمل
    </a>
    <a href="{{ route('withdrawals.index') }}" class="flex flex-col items-center gap-1 py-2.5 text-xs font-medium {{ request()->routeIs('withdrawals.*') ? 'text-indigo-600' : 'text-slate-500' }}">
        <i data-lucide="hand-coins" class="w-5 h-5"></i> المسحوبات
    </a>
    <a href="{{ route('profile.edit') }}" class="flex flex-col items-center gap-1 py-2.5 text-xs font-medium {{ request()->routeIs('profile.*') ? 'text-indigo-600' : 'text-slate-500' }}">
        <i data-lucide="user" class="w-5 h-5"></i> حسابي
    </a>
</nav>

{{-- ===== الجافاسكريبت المشترك (AJAX) ===== --}}
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    // تنبيه منبثق أسفل الشاشة
    function ajaxToast(message, type = 'success') {
        const colors = { success: 'bg-emerald-600', error: 'bg-red-600', info: 'bg-slate-800' };
        const el = document.createElement('div');
        el.className = `fixed bottom-24 md:bottom-6 left-1/2 -translate-x-1/2 z-[60] px-5 py-3 rounded-2xl text-white text-sm font-medium shadow-lg transition-all duration-300 opacity-0 translate-y-2 ${colors[type] || colors.info}`;
        el.textContent = message;
        document.body.appendChild(el);
        requestAnimationFrame(() => el.classList.remove('opacity-0', 'translate-y-2'));
        setTimeout(() => {
            el.classList.add('opacity-0', 'translate-y-2');
            setTimeout(() => el.remove(), 300);
        }, 2800);
    }

    // تحديث الأرقام الإجمالية في الصفحة إذا رجّع السيرفر totals
    function applyTotals(totals) {
        if (!totals) return;
        Object.entries(totals).forEach(([key, value]) => {
            document.querySelectorAll(`[data-total="${key}"]`).forEach(el => el.textContent = value);
        });
    }

    // حذف عبر AJAX: تأكيد ثم DELETE ثم إخفاء الصف/البطاقة بتأثير انسيابي
    function ajaxDelete(url, rowSelector, confirmMessage = 'هل أنت متأكد من الحذف؟') {
        if (!confirm(confirmMessage)) return;

        fetch(url, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
            .then(res => { if (!res.ok) throw new Error(); return res.json(); })
            .then(data => {
                document.querySelectorAll(rowSelector).forEach(el => {
                    el.style.transition = 'opacity .3s ease, transform .3s ease';
                    el.style.opacity = '0';
                    el.style.transform = 'scale(.97)';
                    setTimeout(() => el.remove(), 300);
                });
                applyTotals(data.totals);
                ajaxToast(data.message || 'تم الحذف');
            })
            .catch(() => ajaxToast('حدث خطأ، حاول مرة أخرى', 'error'));
    }

    // تحديث سريع بدون reload (للحالات غير المالية)
    function ajaxUpdate(url, method = 'PUT', body = {}, onSuccess = null) {
        fetch(url, {
            method: method,
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(body),
        })
            .then(res => { if (!res.ok) throw new Error(); return res.json(); })
            .then(data => {
                if (data.message) ajaxToast(data.message);
                if (typeof onSuccess === 'function') onSuccess(data);
            })
            .catch(() => ajaxToast('حدث خطأ، حاول مرة أخرى', 'error'));
    }

    lucide.createIcons();
</script>

<script src="/js/attendance-offline.js?v=1"></script>
@include('partials.pwa-scripts')
@stack('scripts')
</body>
</html>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>@yield('title') - حاسبة ساعات العمل</title>
    @include('partials.head-pwa')
</head>
<body class="bg-slate-100 text-slate-800 antialiased min-h-screen flex items-center justify-center p-4">

<div class="w-full max-w-md">
    <div class="text-center mb-6">
        <div class="inline-flex bg-indigo-100 text-indigo-600 p-3 rounded-2xl mb-3"><i data-lucide="calculator" class="w-8 h-8"></i></div>
        <h1 class="text-2xl font-extrabold">حاسبة ساعات العمل</h1>
        <p class="text-sm text-slate-500 mt-1">احسب ساعات عملك، مسحوباتك، وصافي راتبك بسهولة</p>
    </div>

    @if(session('success'))
        <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-2xl px-4 py-3 text-sm font-medium">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-4 bg-red-50 border border-red-200 text-red-700 rounded-2xl px-4 py-3 text-sm">
            <ul class="list-disc pr-5 space-y-1">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    @yield('content')

    <button type="button" class="pwa-install-btn hidden mt-4 w-full flex items-center justify-center gap-2 bg-white border border-indigo-200 text-indigo-600 hover:bg-indigo-50 text-sm font-medium py-2.5 rounded-xl transition">
        <i data-lucide="download" class="w-4 h-4"></i> تثبيت التطبيق على جهازك
    </button>
</div>

<script>lucide.createIcons();</script>
@include('partials.pwa-scripts')
</body>
</html>

@extends('layouts.app')
@section('title', 'لوحة المشرف')

@section('content')
<div class="space-y-6">

    <div class="flex items-center gap-3 bg-white rounded-2xl shadow-sm border border-slate-100 p-4">
        <div class="bg-indigo-100 text-indigo-600 p-2.5 rounded-xl"><i data-lucide="shield-check" class="w-5 h-5"></i></div>
        <div class="flex-1">
            <p class="font-bold">كل المستخدمين ورواتبهم</p>
            <p class="text-xs text-slate-500">{{ $usersCount }} مستخدم · {{ $workingCount }} على رأس عمله الآن</p>
        </div>
    </div>

    {{-- مجموع كل المستخدمين للشهر المختار --}}
    @include('partials.summary-cards', ['totals' => $totals])

    {{-- ===== الفلاتر والبحث ===== --}}
    <form id="filter-form" method="GET" action="{{ route('admin.index') }}" class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4 flex flex-col md:flex-row gap-3 md:items-center">
        <div class="flex items-center gap-2">
            <label class="text-sm font-medium text-slate-600 whitespace-nowrap">الشهر</label>
            <input type="month" name="month" value="{{ $month->format('Y-m') }}" onchange="this.form.submit()"
                   class="border border-slate-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div class="flex-1 relative">
            <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute right-3 top-1/2 -translate-y-1/2"></i>
            <input type="text" id="search-input" name="q" value="{{ request('q') }}" autocomplete="off" placeholder="ابحث بالاسم أو اسم المستخدم..."
                   class="w-full border border-slate-300 rounded-xl pr-9 pl-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
    </form>

    {{-- ===== النتائج ===== --}}
    <div id="results">
        @include('partials.admin-users-results')
    </div>
</div>
@endsection

@push('scripts')
<script>
    // ===== بحث فوري =====
    (function () {
        const input = document.getElementById('search-input');
        const results = document.getElementById('results');
        const form = document.getElementById('filter-form');
        let timer = null;

        input.addEventListener('input', () => {
            clearTimeout(timer);
            timer = setTimeout(() => {
                const params = new URLSearchParams(new FormData(form));
                fetch(`${form.action}?${params.toString()}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                })
                    .then(res => res.text())
                    .then(html => { results.innerHTML = html; lucide.createIcons(); })
                    .catch(() => ajaxToast('تعذر البحث', 'error'));
            }, 300);
        });
        form.addEventListener('submit', e => { if (document.activeElement === input) e.preventDefault(); });
    })();
</script>
@endpush

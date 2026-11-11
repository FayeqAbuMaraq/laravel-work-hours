@extends('layouts.app')
@section('title', 'المسحوبات')

@section('content')
<div class="space-y-6">

    @include('partials.summary-cards', ['totals' => $totals])

    {{-- ===== الفلاتر والبحث ===== --}}
    <form id="filter-form" method="GET" action="{{ route('withdrawals.index') }}" class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4 flex flex-col md:flex-row gap-3 md:items-center">
        <div class="flex items-center gap-2">
            <label class="text-sm font-medium text-slate-600 whitespace-nowrap">الشهر</label>
            <input type="month" name="month" value="{{ $month->format('Y-m') }}" onchange="this.form.submit()"
                   class="border border-slate-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div class="flex-1 relative">
            <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute right-3 top-1/2 -translate-y-1/2"></i>
            <input type="text" id="search-input" name="q" value="{{ request('q') }}" autocomplete="off" placeholder="ابحث في الملاحظات..."
                   class="w-full border border-slate-300 rounded-xl pr-9 pl-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <button type="button" onclick="document.getElementById('modal-add-withdrawal').classList.remove('hidden')"
                class="bg-red-600 hover:bg-red-700 text-white font-medium px-5 py-2 rounded-xl transition flex items-center justify-center gap-2 text-sm">
            <i data-lucide="minus" class="w-4 h-4"></i> تسجيل سلفة / مسحوبات
        </button>
    </form>

    {{-- ===== النتائج ===== --}}
    <div id="results">
        @include('partials.withdrawals-results')
    </div>
</div>

{{-- ===== مودال إضافة ===== --}}
<div id="modal-add-withdrawal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-lg">تسجيل سلفة / مسحوبات</h3>
            <button type="button" onclick="document.getElementById('modal-add-withdrawal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <form method="POST" action="{{ route('withdrawals.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">التاريخ</label>
                <input type="date" name="date" value="{{ now()->format('Y-m-d') }}" required class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">المبلغ (شيكل)</label>
                <input type="number" name="amount" min="0.5" step="0.5" required placeholder="مثال: 50" class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">ملاحظة <span class="text-slate-400 font-normal">(اختياري)</span></label>
                <input type="text" name="note" placeholder="مثال: مواصلات، سلفة الغداء..." class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500">
            </div>
            <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-medium py-2.5 rounded-xl transition">خصم المبلغ</button>
        </form>
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

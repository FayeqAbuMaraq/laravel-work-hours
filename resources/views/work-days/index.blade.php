@extends('layouts.app')
@section('title', 'أيام العمل')

@section('content')
<div class="space-y-6">

    @include('partials.summary-cards', ['totals' => $totals])

    {{-- ===== تسجيل الحضور والانصراف  --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
        @if($openSession)
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <span class="relative flex h-3 w-3">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                    </span>
                    <div>
                        <p class="font-bold">أنت الآن على رأس عملك</p>
                        <p class="text-sm text-slate-500">
                            دخلت: {{ $openSession->date_label }} - {{ $openSession->start_label }}
                            · المدة: <span id="elapsed-timer" data-start="{{ $openSession->started_at->toIso8601String() }}" class="font-bold text-slate-700" dir="ltr">00:00:00</span>
                        </p>
                    </div>
                </div>
                <form method="POST" action="{{ route('attendance.check-out') }}">
                    @csrf
                    <button type="submit" class="w-full sm:w-auto bg-red-600 hover:bg-red-700 text-white font-bold px-6 py-3 rounded-xl transition flex items-center justify-center gap-2">
                        <i data-lucide="log-out" class="w-5 h-5"></i> تسجيل خروج
                    </button>
                </form>
            </div>
        @else
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="bg-emerald-50 text-emerald-600 p-2.5 rounded-xl"><i data-lucide="log-in" class="w-5 h-5"></i></div>
                    <div>
                        <p class="font-bold">تسجيل الحضور</p>
                        <p class="text-sm text-slate-500">اضغط عند بدء العمل وسيُسجّل تاريخ اليوم ووقت الآن تلقائياً</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('attendance.check-in') }}">
                    @csrf
                    <button type="submit" class="w-full sm:w-auto bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-6 py-3 rounded-xl transition flex items-center justify-center gap-2">
                        <i data-lucide="log-in" class="w-5 h-5"></i> تسجيل دخول
                    </button>
                </form>
            </div>
        @endif
    </div>

    {{-- ===== الفلاتر والبحث ===== --}}
    <form id="filter-form" method="GET" action="{{ route('work-days.index') }}" class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4 flex flex-col md:flex-row gap-3 md:items-center">
        <div class="flex items-center gap-2">
            <label class="text-sm font-medium text-slate-600 whitespace-nowrap">الشهر</label>
            <input type="month" name="month" value="{{ $month->format('Y-m') }}" onchange="this.form.submit()"
                   class="border border-slate-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div class="flex-1 relative">
            <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute right-3 top-1/2 -translate-y-1/2"></i>
            <input type="text" id="search-input" name="q" value="{{ request('q') }}" autocomplete="off" placeholder="ابحث بالتاريخ (مثال: 2026-11-05)"
                   class="w-full border border-slate-300 rounded-xl pr-9 pl-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <button type="button" onclick="document.getElementById('modal-add-work-day').classList.remove('hidden')"
                class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-5 py-2 rounded-xl transition flex items-center justify-center gap-2 text-sm">
            <i data-lucide="plus" class="w-4 h-4"></i> إضافة يدوية
        </button>
    </form>

    {{-- ===== النتائج (جدول + بطاقات + مودالز الصفوف) ===== --}}
    <div id="results">
        @include('partials.work-days-results')
    </div>
</div>

{{-- ===== مودال إضافة ===== --}}
<div id="modal-add-work-day" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-lg">إضافة يوم عمل يدوياً</h3>
            <button type="button" onclick="document.getElementById('modal-add-work-day').classList.add('hidden')" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <form method="POST" action="{{ route('work-days.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">التاريخ</label>
                <input type="date" name="date" value="{{ now()->format('Y-m-d') }}" required class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">وقت البدء</label>
                    <input type="time" name="start_time" required class="w-full border border-slate-300 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">وقت الانتهاء</label>
                    <input type="time" name="end_time" required class="w-full border border-slate-300 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
            <p class="text-xs text-slate-400">إذا كان وقت الانتهاء أقل من البدء يُحسب العمل حتى اليوم التالي.</p>
            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2.5 rounded-xl transition">إضافة الساعات</button>
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

    // ===== عدّاد مدة العمل الحالية =====
    (function () {
        const el = document.getElementById('elapsed-timer');
        if (!el) return;
        const start = new Date(el.dataset.start).getTime();
        const pad = n => String(n).padStart(2, '0');
        const tick = () => {
            const s = Math.max(0, Math.floor((Date.now() - start) / 1000));
            el.textContent = `${pad(Math.floor(s / 3600))}:${pad(Math.floor(s % 3600 / 60))}:${pad(s % 60)}`;
        };
        tick();
        setInterval(tick, 1000);
    })();
</script>
@endpush

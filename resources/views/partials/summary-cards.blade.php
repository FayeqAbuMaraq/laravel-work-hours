{{-- يحتاج المتغير $totals (hours, gross, withdrawals, net) --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4">
    <div class="bg-white rounded-2xl p-4 md:p-5 shadow-sm border border-slate-100 flex items-center gap-3">
        <div class="bg-indigo-50 p-2.5 rounded-full text-indigo-500 hidden sm:block"><i data-lucide="clock" class="w-5 h-5"></i></div>
        <div>
            <p class="text-xs md:text-sm text-slate-500 font-medium">إجمالي الساعات</p>
            <p class="text-xl md:text-2xl font-bold" data-total="hours">{{ $totals['hours'] }}</p>
        </div>
    </div>
    <div class="bg-white rounded-2xl p-4 md:p-5 shadow-sm border border-slate-100 flex items-center gap-3">
        <div class="bg-emerald-50 p-2.5 rounded-full text-emerald-500 hidden sm:block"><i data-lucide="banknote" class="w-5 h-5"></i></div>
        <div>
            <p class="text-xs md:text-sm text-slate-500 font-medium">الراتب الإجمالي</p>
            <p class="text-xl md:text-2xl font-bold text-emerald-600"><span data-total="gross">{{ $totals['gross'] }}</span> ₪</p>
        </div>
    </div>
    <div class="bg-white rounded-2xl p-4 md:p-5 shadow-sm border border-slate-100 flex items-center gap-3">
        <div class="bg-red-50 p-2.5 rounded-full text-red-500 hidden sm:block"><i data-lucide="arrow-down-circle" class="w-5 h-5"></i></div>
        <div>
            <p class="text-xs md:text-sm text-slate-500 font-medium">إجمالي المسحوبات</p>
            <p class="text-xl md:text-2xl font-bold text-red-600"><span data-total="withdrawals">{{ $totals['withdrawals'] }}</span> ₪</p>
        </div>
    </div>
    <div class="bg-indigo-600 rounded-2xl p-4 md:p-5 shadow-sm flex items-center gap-3 text-white">
        <div class="bg-indigo-500 p-2.5 rounded-full hidden sm:block"><i data-lucide="wallet" class="w-5 h-5"></i></div>
        <div>
            <p class="text-xs md:text-sm text-indigo-100 font-medium">الصافي المستحق</p>
            <p class="text-xl md:text-3xl font-bold"><span data-total="net">{{ $totals['net'] }}</span> ₪</p>
        </div>
    </div>
</div>

{{-- يحتاج: $rows (مصفوفات المستخدمين), $month --}}

{{-- ===== جدول (شاشات كبيرة) ===== --}}
<div class="hidden md:block bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <table class="w-full text-right text-sm">
        <thead class="bg-slate-50 border-b border-slate-200">
            <tr class="text-slate-500">
                <th class="px-4 py-3 font-medium">المستخدم</th>
                <th class="px-4 py-3 font-medium">الحالة</th>
                <th class="px-4 py-3 font-medium">الساعات</th>
                <th class="px-4 py-3 font-medium">الراتب الإجمالي</th>
                <th class="px-4 py-3 font-medium">المسحوبات</th>
                <th class="px-4 py-3 font-medium">الصافي</th>
                <th class="px-4 py-3 font-medium text-center">عرض</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($rows as $r)
                <tr class="hover:bg-slate-50 transition">
                    <td class="px-4 py-3">
                        <p class="font-bold">{{ $r['name'] }} @if($r['is_admin'])<span class="text-[10px] font-medium bg-indigo-50 text-indigo-600 px-1.5 py-0.5 rounded-md mr-1">مشرف</span>@endif</p>
                        <p class="text-xs text-slate-400" dir="ltr" style="text-align:right">{{ $r['username'] }}</p>
                    </td>
                    <td class="px-4 py-3">
                        @if($r['working'])
                            <span class="text-emerald-700 text-xs bg-emerald-50 px-2 py-1 rounded-lg">على رأس عمله</span>
                        @else
                            <span class="text-slate-400 text-xs">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 font-bold">{{ number_format($r['hours'], 2) }}</td>
                    <td class="px-4 py-3 font-bold text-emerald-600">{{ number_format($r['gross'], 2) }} ₪</td>
                    <td class="px-4 py-3 font-bold text-red-600">{{ number_format($r['withdrawn'], 2) }} ₪</td>
                    <td class="px-4 py-3 font-extrabold text-indigo-700">{{ number_format($r['net'], 2) }} ₪</td>
                    <td class="px-4 py-3 text-center">
                        <button type="button" onclick="document.getElementById('modal-show-user-{{ $r['id'] }}').classList.remove('hidden')" class="p-1.5 text-slate-400 hover:text-indigo-600 transition" title="عرض"><i data-lucide="eye" class="w-4 h-4"></i></button>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="p-8 text-center text-slate-400">لا يوجد مستخدمون مطابقون.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- ===== بطاقات (هاتف) ===== --}}
<div class="md:hidden space-y-3">
    @forelse($rows as $r)
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4">
            <div class="flex items-start justify-between mb-3">
                <div>
                    <p class="font-bold">{{ $r['name'] }} @if($r['is_admin'])<span class="text-[10px] font-medium bg-indigo-50 text-indigo-600 px-1.5 py-0.5 rounded-md">مشرف</span>@endif</p>
                    <p class="text-xs text-slate-400" dir="ltr" style="text-align:right">{{ $r['username'] }}</p>
                </div>
                @if($r['working'])
                    <span class="text-emerald-700 text-xs bg-emerald-50 px-2 py-1 rounded-lg">على رأس عمله</span>
                @endif
            </div>
            <div class="grid grid-cols-2 gap-2 text-center text-sm mb-3">
                <div class="bg-slate-50 rounded-xl py-2"><p class="text-[11px] text-slate-500">الساعات</p><p class="font-bold">{{ number_format($r['hours'], 2) }}</p></div>
                <div class="bg-emerald-50 rounded-xl py-2"><p class="text-[11px] text-slate-500">الراتب الإجمالي</p><p class="font-bold text-emerald-600">{{ number_format($r['gross'], 2) }} ₪</p></div>
                <div class="bg-red-50 rounded-xl py-2"><p class="text-[11px] text-slate-500">المسحوبات</p><p class="font-bold text-red-600">{{ number_format($r['withdrawn'], 2) }} ₪</p></div>
                <div class="bg-indigo-50 rounded-xl py-2"><p class="text-[11px] text-slate-500">الصافي</p><p class="font-extrabold text-indigo-700">{{ number_format($r['net'], 2) }} ₪</p></div>
            </div>
            <div class="flex justify-end border-t border-slate-100 pt-2">
                <button type="button" onclick="document.getElementById('modal-show-user-{{ $r['id'] }}').classList.remove('hidden')" class="p-2 text-slate-400 hover:text-indigo-600"><i data-lucide="eye" class="w-4 h-4"></i></button>
            </div>
        </div>
    @empty
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8 text-center text-slate-400">لا يوجد مستخدمون مطابقون.</div>
    @endforelse
</div>

{{-- ===== المودالز: حلقة منفصلة خارج الجدول والبطاقات ===== --}}
@foreach($rows as $r)
    <div id="modal-show-user-{{ $r['id'] }}" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-lg">{{ $r['name'] }}</h3>
                <button type="button" onclick="document.getElementById('modal-show-user-{{ $r['id'] }}').classList.add('hidden')" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500">اسم المستخدم</dt><dd class="font-bold" dir="ltr">{{ $r['username'] }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">الشهر</dt><dd class="font-bold">{{ $month->format('Y/m') }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">أجرة الساعة</dt><dd class="font-bold">{{ number_format($r['rate'], 2) }} ₪</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">أيام العمل المكتملة</dt><dd class="font-bold">{{ $r['days'] }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">إجمالي الساعات</dt><dd class="font-bold">{{ number_format($r['hours'], 2) }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">الراتب الإجمالي</dt><dd class="font-bold text-emerald-600">{{ number_format($r['gross'], 2) }} ₪</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">المسحوبات</dt><dd class="font-bold text-red-600">{{ number_format($r['withdrawn'], 2) }} ₪</dd></div>
                <div class="flex justify-between border-t border-slate-100 pt-3"><dt class="text-slate-500">الصافي المستحق</dt><dd class="font-extrabold text-indigo-700">{{ number_format($r['net'], 2) }} ₪</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">الحالة الآن</dt><dd class="font-bold">{{ $r['working'] ? 'على رأس عمله' : 'غير مسجّل دخول' }}</dd></div>
            </dl>
        </div>
    </div>
@endforeach

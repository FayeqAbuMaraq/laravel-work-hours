{{-- يحتاج: $workDays, $rate --}}

{{-- ===== جدول (شاشات كبيرة) ===== --}}
<div class="hidden md:block bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <table class="w-full text-right text-sm">
        <thead class="bg-slate-50 border-b border-slate-200">
            <tr class="text-slate-500">
                <th class="px-4 py-3 font-medium">التاريخ</th>
                <th class="px-4 py-3 font-medium">من</th>
                <th class="px-4 py-3 font-medium">إلى</th>
                <th class="px-4 py-3 font-medium">الساعات</th>
                <th class="px-4 py-3 font-medium">الأجرة (₪)</th>
                <th class="px-4 py-3 font-medium text-center">إجراءات</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($workDays as $d)
                <tr data-row="work-day-{{ $d->id }}" class="hover:bg-slate-50 transition">
                    <td class="px-4 py-3 whitespace-nowrap">{{ $d->date_label }}</td>
                    <td class="px-4 py-3 text-indigo-600">{{ $d->start_label }}</td>
                    <td class="px-4 py-3 text-red-500">{{ $d->end_label }}</td>
                    <td class="px-4 py-3 font-bold">
                        @if($d->is_open)
                            <span class="text-amber-600 text-xs bg-amber-50 px-2 py-1 rounded-lg">جارٍ العمل</span>
                        @else
                            {{ number_format($d->hours, 2) }}
                        @endif
                    </td>
                    <td class="px-4 py-3 font-bold text-emerald-600">
                        {{ $d->is_open ? '—' : number_format($d->earnedAt($rate), 2) . ' ₪' }}
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-center gap-1">
                            <button type="button" onclick="document.getElementById('modal-show-work-day-{{ $d->id }}').classList.remove('hidden')" class="p-1.5 text-slate-400 hover:text-indigo-600 transition" title="عرض"><i data-lucide="eye" class="w-4 h-4"></i></button>
                            <button type="button" onclick="document.getElementById('modal-edit-work-day-{{ $d->id }}').classList.remove('hidden')" class="p-1.5 text-slate-400 hover:text-amber-600 transition" title="تعديل"><i data-lucide="pencil" class="w-4 h-4"></i></button>
                            <button type="button" onclick="ajaxDelete('{{ route('work-days.destroy', $d->id) }}', '[data-row=work-day-{{ $d->id }}]', 'هل أنت متأكد من حذف يوم العمل هذا؟')" class="p-1.5 text-slate-400 hover:text-red-500 transition" title="حذف"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="p-8 text-center text-slate-400">لا يوجد أيام عمل مسجلة.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- ===== بطاقات (هاتف) ===== --}}
<div class="md:hidden space-y-3">
    @forelse($workDays as $d)
        <div data-row="work-day-{{ $d->id }}" class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4">
            <div class="flex items-center justify-between mb-3">
                <span class="font-bold">{{ $d->date_label }}</span>
                @if($d->is_open)
                    <span class="text-amber-600 text-xs bg-amber-50 px-2 py-1 rounded-lg">جارٍ العمل</span>
                @else
                    <span class="font-bold text-emerald-600">{{ number_format($d->earnedAt($rate), 2) }} ₪</span>
                @endif
            </div>
            <div class="grid grid-cols-3 gap-2 text-center text-sm mb-3">
                <div class="bg-indigo-50 rounded-xl py-2"><p class="text-[11px] text-slate-500">من</p><p class="font-bold text-indigo-600">{{ $d->start_label }}</p></div>
                <div class="bg-red-50 rounded-xl py-2"><p class="text-[11px] text-slate-500">إلى</p><p class="font-bold text-red-500">{{ $d->end_label }}</p></div>
                <div class="bg-slate-50 rounded-xl py-2"><p class="text-[11px] text-slate-500">الساعات</p><p class="font-bold">{{ $d->is_open ? '—' : number_format($d->hours, 2) }}</p></div>
            </div>
            <div class="flex items-center justify-end gap-1 border-t border-slate-100 pt-2">
                <button type="button" onclick="document.getElementById('modal-show-work-day-{{ $d->id }}').classList.remove('hidden')" class="p-2 text-slate-400 hover:text-indigo-600"><i data-lucide="eye" class="w-4 h-4"></i></button>
                <button type="button" onclick="document.getElementById('modal-edit-work-day-{{ $d->id }}').classList.remove('hidden')" class="p-2 text-slate-400 hover:text-amber-600"><i data-lucide="pencil" class="w-4 h-4"></i></button>
                <button type="button" onclick="ajaxDelete('{{ route('work-days.destroy', $d->id) }}', '[data-row=work-day-{{ $d->id }}]', 'هل أنت متأكد من حذف يوم العمل هذا؟')" class="p-2 text-slate-400 hover:text-red-500"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
            </div>
        </div>
    @empty
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8 text-center text-slate-400">لا يوجد أيام عمل مسجلة.</div>
    @endforelse
</div>

{{-- ===== المودالز: حلقة منفصلة خارج الجدول والبطاقات ===== --}}
@foreach($workDays as $d)
    {{-- مودال عرض --}}
    <div id="modal-show-work-day-{{ $d->id }}" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-lg">تفاصيل يوم العمل</h3>
                <button type="button" onclick="document.getElementById('modal-show-work-day-{{ $d->id }}').classList.add('hidden')" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500">التاريخ</dt><dd class="font-bold">{{ $d->date_label }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">وقت الدخول</dt><dd class="font-bold text-indigo-600">{{ $d->start_label }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">وقت الخروج</dt><dd class="font-bold text-red-500">{{ $d->end_label }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">الساعات</dt><dd class="font-bold">{{ $d->is_open ? 'جارٍ العمل' : number_format($d->hours, 2) }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">أجرة الساعة</dt><dd class="font-bold">{{ number_format($rate, 2) }} ₪</dd></div>
                <div class="flex justify-between border-t border-slate-100 pt-3"><dt class="text-slate-500">الأجرة</dt><dd class="font-bold text-emerald-600">{{ $d->is_open ? '—' : number_format($d->earnedAt($rate), 2) . ' ₪' }}</dd></div>
            </dl>
        </div>
    </div>

    {{-- مودال تعديل --}}
    <div id="modal-edit-work-day-{{ $d->id }}" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-lg">تعديل يوم العمل</h3>
                <button type="button" onclick="document.getElementById('modal-edit-work-day-{{ $d->id }}').classList.add('hidden')" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form method="POST" action="{{ route('work-days.update', $d->id) }}" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">التاريخ</label>
                    <input type="date" name="date" value="{{ $d->started_at->format('Y-m-d') }}" required class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">وقت البدء</label>
                        <input type="time" name="start_time" value="{{ $d->started_at->format('H:i') }}" required class="w-full border border-slate-300 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">وقت الانتهاء <span class="text-slate-400 font-normal">(اختياري)</span></label>
                        <input type="time" name="end_time" value="{{ $d->ended_at?->format('H:i') }}" class="w-full border border-slate-300 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2.5 rounded-xl transition">حفظ التعديلات</button>
            </form>
        </div>
    </div>
@endforeach

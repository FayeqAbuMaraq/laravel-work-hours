{{-- يحتاج: $withdrawals --}}

{{-- ===== جدول (شاشات كبيرة) ===== --}}
<div class="hidden md:block bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <table class="w-full text-right text-sm">
        <thead class="bg-slate-50 border-b border-slate-200">
            <tr class="text-slate-500">
                <th class="px-4 py-3 font-medium">التاريخ</th>
                <th class="px-4 py-3 font-medium">المبلغ (₪)</th>
                <th class="px-4 py-3 font-medium">ملاحظة</th>
                <th class="px-4 py-3 font-medium text-center">إجراءات</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($withdrawals as $w)
                <tr data-row="withdrawal-{{ $w->id }}" class="hover:bg-slate-50 transition">
                    <td class="px-4 py-3 whitespace-nowrap">{{ $w->date->format('Y/m/d') }}</td>
                    <td class="px-4 py-3 font-bold text-red-600">{{ number_format($w->amount, 2) }} ₪</td>
                    <td class="px-4 py-3 text-slate-600">{{ $w->note ?: 'بدون ملاحظة' }}</td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-center gap-1">
                            <button type="button" onclick="document.getElementById('modal-show-withdrawal-{{ $w->id }}').classList.remove('hidden')" class="p-1.5 text-slate-400 hover:text-indigo-600 transition" title="عرض"><i data-lucide="eye" class="w-4 h-4"></i></button>
                            <button type="button" onclick="document.getElementById('modal-edit-withdrawal-{{ $w->id }}').classList.remove('hidden')" class="p-1.5 text-slate-400 hover:text-amber-600 transition" title="تعديل"><i data-lucide="pencil" class="w-4 h-4"></i></button>
                            <button type="button" onclick="ajaxDelete('{{ route('withdrawals.destroy', $w->id) }}', '[data-row=withdrawal-{{ $w->id }}]', 'هل أنت متأكد من حذف هذا السحب؟')" class="p-1.5 text-slate-400 hover:text-red-500 transition" title="حذف"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="p-8 text-center text-slate-400">لا يوجد مسحوبات مسجلة.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- ===== بطاقات (هاتف) ===== --}}
<div class="md:hidden space-y-3">
    @forelse($withdrawals as $w)
        <div data-row="withdrawal-{{ $w->id }}" class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4">
            <div class="flex items-center justify-between mb-1">
                <span class="font-bold">{{ $w->date->format('Y/m/d') }}</span>
                <span class="font-bold text-red-600">{{ number_format($w->amount, 2) }} ₪</span>
            </div>
            <p class="text-sm text-slate-500 mb-3">{{ $w->note ?: 'بدون ملاحظة' }}</p>
            <div class="flex items-center justify-end gap-1 border-t border-slate-100 pt-2">
                <button type="button" onclick="document.getElementById('modal-show-withdrawal-{{ $w->id }}').classList.remove('hidden')" class="p-2 text-slate-400 hover:text-indigo-600"><i data-lucide="eye" class="w-4 h-4"></i></button>
                <button type="button" onclick="document.getElementById('modal-edit-withdrawal-{{ $w->id }}').classList.remove('hidden')" class="p-2 text-slate-400 hover:text-amber-600"><i data-lucide="pencil" class="w-4 h-4"></i></button>
                <button type="button" onclick="ajaxDelete('{{ route('withdrawals.destroy', $w->id) }}', '[data-row=withdrawal-{{ $w->id }}]', 'هل أنت متأكد من حذف هذا السحب؟')" class="p-2 text-slate-400 hover:text-red-500"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
            </div>
        </div>
    @empty
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8 text-center text-slate-400">لا يوجد مسحوبات مسجلة.</div>
    @endforelse
</div>

{{-- ===== المودالز: حلقة منفصلة خارج الجدول والبطاقات ===== --}}
@foreach($withdrawals as $w)
    {{-- مودال عرض --}}
    <div id="modal-show-withdrawal-{{ $w->id }}" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-lg">تفاصيل السحب</h3>
                <button type="button" onclick="document.getElementById('modal-show-withdrawal-{{ $w->id }}').classList.add('hidden')" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500">التاريخ</dt><dd class="font-bold">{{ $w->date->format('Y/m/d') }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">المبلغ</dt><dd class="font-bold text-red-600">{{ number_format($w->amount, 2) }} ₪</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">ملاحظة</dt><dd class="font-medium">{{ $w->note ?: 'بدون ملاحظة' }}</dd></div>
            </dl>
        </div>
    </div>

    {{-- مودال تعديل --}}
    <div id="modal-edit-withdrawal-{{ $w->id }}" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-lg">تعديل السحب</h3>
                <button type="button" onclick="document.getElementById('modal-edit-withdrawal-{{ $w->id }}').classList.add('hidden')" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form method="POST" action="{{ route('withdrawals.update', $w->id) }}" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">التاريخ</label>
                    <input type="date" name="date" value="{{ $w->date->format('Y-m-d') }}" required class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">المبلغ (شيكل)</label>
                    <input type="number" name="amount" min="0.5" step="0.5" value="{{ $w->amount }}" required class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">ملاحظة <span class="text-slate-400 font-normal">(اختياري)</span></label>
                    <input type="text" name="note" value="{{ $w->note }}" class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2.5 rounded-xl transition">حفظ التعديلات</button>
            </form>
        </div>
    </div>
@endforeach

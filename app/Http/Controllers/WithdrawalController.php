<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class WithdrawalController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'month' => 'nullable|date_format:Y-m',
            'q' => 'nullable|string|max:50',
        ]);

        $user = $request->user();
        $month = Carbon::createFromFormat('Y-m-d', ($request->input('month') ?: now()->format('Y-m')) . '-01')->startOfDay();

        $withdrawals = $user->withdrawals()
            ->whereBetween('date', [$month->copy()->startOfMonth()->toDateString(), $month->copy()->endOfMonth()->toDateString()])
            ->when($request->filled('q'), fn ($q) => $q->where('note', 'like', '%' . $request->q . '%'))
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();

        if ($request->ajax()) {
            return view('partials.withdrawals-results', compact('withdrawals'))->render();
        }

        return view('withdrawals.index', [
            'withdrawals' => $withdrawals,
            'month' => $month,
            'totals' => $user->monthTotals($month),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0.5|max:1000000',
            'note' => 'nullable|string|max:255',
        ]);

        $request->user()->withdrawals()->create($data);

        return redirect()->route('withdrawals.index', ['month' => Carbon::parse($data['date'])->format('Y-m')])
            ->with('success', 'تم تسجيل المسحوبات');
    }

    public function update(Request $request, $id)
    {
        $withdrawal = $request->user()->withdrawals()->findOrFail($id);

        $data = $request->validate([
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0.5|max:1000000',
            'note' => 'nullable|string|max:255',
        ]);

        $withdrawal->update($data);

        return redirect()->route('withdrawals.index', ['month' => Carbon::parse($data['date'])->format('Y-m')])
            ->with('success', 'تم تعديل المسحوبات');
    }

    public function destroy(Request $request, $id)
    {
        $withdrawal = $request->user()->withdrawals()->findOrFail($id);
        $month = Carbon::parse($withdrawal->date);
        $withdrawal->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'تم حذف السحب',
                'totals' => $request->user()->monthTotals($month),
            ]);
        }

        return redirect()->route('withdrawals.index')->with('success', 'تم حذف السحب');
    }
}

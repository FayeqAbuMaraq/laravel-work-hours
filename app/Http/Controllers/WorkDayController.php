<?php

namespace App\Http\Controllers;

use App\Models\WorkDay;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class WorkDayController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'month' => 'nullable|date_format:Y-m',
            'q' => 'nullable|string|max:50',
        ]);

        $user = $request->user();
        $month = Carbon::createFromFormat('Y-m-d', ($request->input('month') ?: now()->format('Y-m')) . '-01')->startOfDay();

        $workDays = $user->workDays()
            ->whereBetween('started_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
            ->when($request->filled('q'), fn ($q) => $q->where('started_at', 'like', '%' . $request->q . '%'))
            ->orderByDesc('started_at')
            ->get();

        $rate = $user->hourly_rate;

        if ($request->ajax()) {
            return view('partials.work-days-results', compact('workDays', 'rate'))->render();
        }

        return view('work-days.index', [
            'workDays' => $workDays,
            'rate' => $rate,
            'month' => $month,
            'totals' => $user->monthTotals($month),
            'openSession' => $user->workDays()->whereNull('ended_at')->latest('started_at')->first(),
        ]);
    }

    /** إضافة يدوية: تاريخ + وقت بدء + وقت انتهاء */
    public function store(Request $request)
    {
        $data = $request->validate([
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
        ]);

        [$start, $end] = $this->buildRange($data['date'], $data['start_time'], $data['end_time']);

        $request->user()->workDays()->create([
            'started_at' => $start,
            'ended_at' => $end,
        ]);

        return redirect()->route('work-days.index', ['month' => $start->format('Y-m')])
            ->with('success', 'تمت إضافة ساعات العمل');
    }

    public function update(Request $request, $id)
    {
        $workDay = $request->user()->workDays()->findOrFail($id);

        $data = $request->validate([
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
        ]);

        [$start, $end] = $this->buildRange($data['date'], $data['start_time'], $data['end_time'] ?? null);

        $workDay->update([
            'started_at' => $start,
            'ended_at' => $end,
        ]);

        return redirect()->route('work-days.index', ['month' => $start->format('Y-m')])
            ->with('success', 'تم تعديل يوم العمل');
    }

    public function destroy(Request $request, $id)
    {
        $workDay = $request->user()->workDays()->findOrFail($id);
        $month = $workDay->started_at->copy();
        $workDay->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'تم حذف يوم العمل',
                'totals' => $request->user()->monthTotals($month),
            ]);
        }

        return redirect()->route('work-days.index')->with('success', 'تم حذف يوم العمل');
    }

    /** تسجيل دخول بنفس اللحظة (تاريخ ووقت الآن) */
    public function checkIn(Request $request)
    {
        $user = $request->user();

        if ($user->workDays()->whereNull('ended_at')->exists()) {
            return redirect()->route('work-days.index')
                ->with('error', 'عندك جلسة عمل مفتوحة، سجّل الخروج أولاً');
        }

        $user->workDays()->create([
            'started_at' => now(),
            'client_uuid' => (string) Str::uuid(),
        ]);

        return redirect()->route('work-days.index')->with('success', 'تم تسجيل دخولك، يعطيك العافية');
    }

    /** تسجيل خروج يدوي */
    public function checkOut(Request $request)
    {
        $open = $request->user()->workDays()->whereNull('ended_at')->latest('started_at')->first();

        if (! $open) {
            return redirect()->route('work-days.index')->with('error', 'ما في جلسة عمل مفتوحة');
        }

        $open->update(['ended_at' => now()]);

        return redirect()->route('work-days.index')
            ->with('success', 'تم تسجيل خروجك، مدة العمل: ' . number_format($open->hours, 2) . ' ساعة');
    }

    /** يبني بداية ونهاية العمل؛ لو الانتهاء أقل من/يساوي البدء يعتبر اليوم التالي (بعد منتصف الليل). */
    private function buildRange(string $date, string $startTime, ?string $endTime): array
    {
        $start = Carbon::parse("$date $startTime");
        $end = null;

        if ($endTime) {
            $end = Carbon::parse("$date $endTime");
            if ($end->lessThanOrEqualTo($start)) {
                $end->addDay();
            }
        }

        return [$start, $end];
    }
}

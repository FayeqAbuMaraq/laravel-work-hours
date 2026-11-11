<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class AttendanceController extends Controller
{
    /** صفحة الحضور والانصراف (خفيفة وبدون أي أرقام مالية، وتُخزَّن للعمل بدون إنترنت) */
    public function index(Request $request)
    {
        $open = $request->user()->workDays()->whereNull('ended_at')->latest('started_at')->first();

        // جلسة مفتوحة قديمة بدون uuid (مثلاً أُضيفت يدوياً): نعطيها واحداً ليتمكن الجهاز من إغلاقها
        if ($open && ! $open->client_uuid) {
            $open->update(['client_uuid' => (string) Str::uuid()]);
        }

        return view('attendance.index', ['openSession' => $open]);
    }

    /** توكن CSRF جديد: تحتاجه المزامنة لأن الصفحة المخزّنة بالكاش قد يكون توكنها قديم */
    public function token()
    {
        return response()->json(['token' => csrf_token()]);
    }

    /**
     * مزامنة عمليات الحضور المسجّلة بدون إنترنت.
     * كل عملية = جلسة (uuid, started_at, ended_at?) ومعالجتها idempotent: إعادة الإرسال ما بتكرر السجل.
     */
    public function sync(Request $request)
    {
        $data = $request->validate([
            'sessions' => 'required|array|max:50',
            'sessions.*.uuid' => 'required|uuid',
            'sessions.*.started_at' => 'required|date',
            'sessions.*.ended_at' => 'nullable|date',
        ]);

        $user = $request->user();
        $tz = config('app.timezone');
        $limit = now()->addMinutes(5); // سماحية بسيطة لفرق ساعة الجهاز

        $synced = [];
        $rejected = [];

        foreach ($data['sessions'] as $s) {
            $start = Carbon::parse($s['started_at'])->setTimezone($tz);
            $end = ! empty($s['ended_at']) ? Carbon::parse($s['ended_at'])->setTimezone($tz) : null;

            if ($start->gt($limit) || ($end && ($end->lte($start) || $end->gt($limit)))) {
                $rejected[] = $s['uuid'];
                continue;
            }

            $workDay = $user->workDays()->where('client_uuid', $s['uuid'])->first();

            if (! $workDay) {
                $user->workDays()->create([
                    'client_uuid' => $s['uuid'],
                    'started_at' => $start,
                    'ended_at' => $end,
                ]);
            } elseif ($end && $workDay->ended_at === null && $end->gt($workDay->started_at)) {
                $workDay->update(['ended_at' => $end]);
            }

            $synced[] = $s['uuid'];
        }

        return response()->json([
            'message' => count($synced) . ' عملية تمت مزامنتها',
            'synced' => $synced,
            'rejected' => $rejected,
        ]);
    }
}

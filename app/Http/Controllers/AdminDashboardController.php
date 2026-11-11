<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'month' => 'nullable|date_format:Y-m',
            'q' => 'nullable|string|max:50',
        ]);

        $month = Carbon::createFromFormat('Y-m-d', ($request->input('month') ?: now()->format('Y-m')) . '-01')->startOfDay();
        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();

        // استعلام واحد لكل المستخدمين مع أيام عملهم ومسحوباتهم للشهر المختار
        $users = User::query()
            ->with([
                'roles',
                'workDays' => fn ($q) => $q->whereBetween('started_at', [$start, $end]),
            ])
            ->withSum(['withdrawals as withdrawn' => fn ($q) => $q->whereBetween('date', [$start->toDateString(), $end->toDateString()])], 'amount')
            ->withCount(['workDays as open_count' => fn ($q) => $q->whereNull('ended_at')])
            ->orderBy('name')
            ->get();

        $allRows = $users->map(function (User $u) {
            $closed = $u->workDays->whereNotNull('ended_at');
            $hours = round($closed->sum(fn ($d) => $d->hours), 2);
            $gross = round($hours * $u->hourly_rate, 2);
            $withdrawn = round((float) $u->withdrawn, 2);

            return [
                'id' => $u->id,
                'name' => $u->name,
                'username' => $u->username,
                'is_admin' => $u->hasRole('admin'),
                'rate' => $u->hourly_rate,
                'days' => $closed->count(),
                'hours' => $hours,
                'gross' => $gross,
                'withdrawn' => $withdrawn,
                'net' => round($gross - $withdrawn, 2),
                'working' => $u->open_count > 0,
            ];
        });

        $totals = [
            'hours' => number_format($allRows->sum('hours'), 2),
            'gross' => number_format($allRows->sum('gross'), 2),
            'withdrawals' => number_format($allRows->sum('withdrawn'), 2),
            'net' => number_format($allRows->sum('net'), 2),
        ];

        $q = Str::lower(trim((string) $request->input('q')));
        $rows = $q === ''
            ? $allRows
            : $allRows->filter(fn ($r) => Str::contains(Str::lower($r['name'] . ' ' . $r['username']), $q))->values();

        if ($request->ajax()) {
            return view('partials.admin-users-results', compact('rows', 'month'))->render();
        }

        return view('admin.index', [
            'rows' => $rows,
            'totals' => $totals,
            'month' => $month,
            'usersCount' => $allRows->count(),
            'workingCount' => $allRows->where('working', true)->count(),
        ]);
    }
}

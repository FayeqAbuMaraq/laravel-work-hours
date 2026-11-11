<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use Notifiable, HasRoles;

    protected $fillable = ['name', 'username', 'email', 'password', 'hourly_rate'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'hourly_rate' => 'float',
        ];
    }

    public function workDays(): HasMany
    {
        return $this->hasMany(WorkDay::class);
    }

    public function withdrawals(): HasMany
    {
        return $this->hasMany(Withdrawal::class);
    }

    /**
     * ملخص الشهر: الساعات، الراتب الإجمالي، المسحوبات، الصافي (منسّقة جاهزة للعرض).
     */
    public function monthTotals(Carbon $month): array
    {
        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();

        $hours = $this->workDays()
            ->whereBetween('started_at', [$start, $end])
            ->whereNotNull('ended_at')
            ->get()
            ->sum(fn (WorkDay $d) => $d->hours);

        $gross = round($hours * $this->hourly_rate, 2);

        $withdrawn = (float) $this->withdrawals()
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->sum('amount');

        return [
            'hours' => number_format($hours, 2),
            'gross' => number_format($gross, 2),
            'withdrawals' => number_format($withdrawn, 2),
            'net' => number_format($gross - $withdrawn, 2),
        ];
    }
}

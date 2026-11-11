<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkDay extends Model
{
    protected $fillable = ['user_id', 'started_at', 'ended_at', 'client_uuid'];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getIsOpenAttribute(): bool
    {
        return $this->ended_at === null;
    }

    /** عدد الساعات (null إذا لسا ما سجّل خروج). */
    public function getHoursAttribute(): ?float
    {
        if ($this->ended_at === null) {
            return null;
        }

        return round(abs($this->started_at->diffInSeconds($this->ended_at)) / 3600, 2);
    }

    public function earnedAt(float $rate): ?float
    {
        return $this->hours === null ? null : round($this->hours * $rate, 2);
    }

    public function getDateLabelAttribute(): string
    {
        return $this->started_at->format('Y/m/d');
    }

    public function getStartLabelAttribute(): string
    {
        return self::arabicTime($this->started_at->format('h:i A'));
    }

    public function getEndLabelAttribute(): string
    {
        return $this->ended_at ? self::arabicTime($this->ended_at->format('h:i A')) : '—';
    }

    private static function arabicTime(string $time): string
    {
        return str_replace(['AM', 'PM'], ['ص', 'م'], $time);
    }
}

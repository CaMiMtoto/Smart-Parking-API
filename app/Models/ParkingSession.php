<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParkingSession extends Model
{
    use HasFactory;
    protected $casts = [
        'entry_time' => 'datetime',
        'exit_time' => 'datetime',
        'amount' => 'int'
    ];

    public function calculateAmount($duration): float|int|null
    {
        return Rate::calculateParkingFee($duration);
    }

    // Define constants for statuses
    const STATUS_ACTIVE = 'active';
    const STATUS_COMPLETED = 'completed';

    // Scope to filter active sessions
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    // Scope to filter completed sessions
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    // Optional: Accessor to get duration in hours and minutes nicely formatted
    public function getFormattedDurationAttribute(): ?string
    {
        if (!$this->duration_minutes) return null;

        $hours = floor($this->duration_minutes / 60);
        $minutes = $this->duration_minutes % 60;
        return sprintf('%dh %02dm', $hours, $minutes);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}

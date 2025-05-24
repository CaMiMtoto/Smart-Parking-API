<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rate extends Model
{
    public static function calculateParkingFee(int $hours): float|int|null
    {
        $rule = Rate::where('min_hours', '<=', $hours)
            ->where(function ($query) use ($hours) {
                $query->where('max_hours', '>=', $hours)
                    ->orWhereNull('max_hours'); // for open-ended ranges
            })
            ->first();

        if ($rule) {
            return $hours * $rule->price_per_hour;
        }

        return null; // Or throw exception, or return default
    }
}

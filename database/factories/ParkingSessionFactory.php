<?php

namespace Database\Factories;

use App\Models\ParkingSession;
use App\Models\Rate;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class ParkingSessionFactory extends Factory
{
    protected $model = ParkingSession::class;

    public function definition(): array
    {
//        plate number like RAH123A
        $entry_time = $this->faker->dateTimeBetween('-1 month', 'now');
        $exit_time = $this->faker->dateTimeBetween($entry_time, 'now');
        $minutes = ceil(Carbon::parse($entry_time)->diffInMinutes(Carbon::parse($exit_time)));
        return [
            'plate_number' => strtoupper("RA" . $this->faker->randomLetter() . $this->faker->randomNumber(3) . $this->faker->randomLetter()),
            'entry_time' => $entry_time,
            'phone' => $this->faker->phoneNumber(),
            'exit_time' => $exit_time,
            'duration_minutes' => $minutes,
            'amount' => Rate::calculateParkingFee(ceil($minutes / 60)),
            'status'=>'completed'
        ];
    }
}

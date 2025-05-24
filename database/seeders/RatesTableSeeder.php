<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RatesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        DB::table('rates')->truncate();

        DB::table('rates')->insert([
            ['min_hours' => 1, 'max_hours' => 2, 'price_per_hour' => 200],
            ['min_hours' => 3, 'max_hours' => 5, 'price_per_hour' => 500],
            ['min_hours' => 6, 'max_hours' => 8, 'price_per_hour' => 1000],
            ['min_hours' => 9, 'max_hours' => null, 'price_per_hour' => 2000],
        ]);
    }
}

<?php

namespace Database\Seeders;

use App\Models\FeeType;
use Illuminate\Database\Seeder;

class FeeTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        FeeType::updateOrCreate(
            ['name' => 'Iuran Satpam'],
            [
                'amount' => 100000,
                'is_active' => true,
            ]
        );

        FeeType::updateOrCreate(
            ['name' => 'Iuran Kebersihan'],
            [
                'amount' => 15000,
                'is_active' => true,
            ]
        );
    }
}

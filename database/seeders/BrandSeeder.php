<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $names = [
            'Giant', 'Trek', 'Specialized', 'Cannondale', 'Merida', 'Scott',
            'Shimano', 'SRAM', 'Campagnolo', 'Fox', 'RockShox', 'Continental', 'Maxxis',
        ];

        foreach ($names as $index => $name) {
            Brand::firstOrCreate(['name' => $name], ['sort_order' => $index]);
        }
    }
}
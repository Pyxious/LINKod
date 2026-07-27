<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MaterialsSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('materials')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $materials = [
            ['material_name' => 'Light Bulb', 'unit_of_measurement' => 'pc',     'unit_cost' => 0.00],
            ['material_name' => 'Pipe',        'unit_of_measurement' => 'length', 'unit_cost' => 0.00],
            ['material_name' => 'Doorknob',    'unit_of_measurement' => 'pc',     'unit_cost' => 0.00],
        ];

        DB::table('materials')->insert($materials);

        $this->command->info('✅ ' . count($materials) . ' materials seeded.');
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('category')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $categories = [
            ['category_name' => 'Carpentry/Masonry/Electrical', 'description' => 'Carpentry, masonry, and electrical works.'],
            ['category_name' => 'Plumbing',                     'description' => 'Plumbing installation and repair services.'],
            ['category_name' => 'Painting',                     'description' => 'Interior and exterior painting services.'],
            ['category_name' => 'Janitorial',                   'description' => 'Cleaning, sanitation, and housekeeping services.'],
            ['category_name' => 'Manpower',                     'description' => 'General manpower and labor assistance.'],
            ['category_name' => 'Landscaping',                  'description' => 'Grounds maintenance, lawn care, and landscaping services.'],
        ];

        DB::table('category')->insert($categories);

        $this->command->info('✅ ' . count($categories) . ' categories seeded.');
    }
}

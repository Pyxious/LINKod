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
            ['category_name' => 'Carpentry/Masonry/Electrical Services', 'description' => 'Carpentry, masonry, and electrical works.'],
            ['category_name' => 'Plumbing Services',                     'description' => 'Plumbing installation and repair services.'],
            ['category_name' => 'Painting Services',                     'description' => 'Interior and exterior painting services.'],
            ['category_name' => 'Janitorial and Manpower Services',       'description' => 'Cleaning, sanitation, and manpower assistance services.'],
            ['category_name' => 'Landscaping Services',                  'description' => 'Grounds maintenance, lawn care, and landscaping services.'],
        ];

        DB::table('category')->insert($categories);

        $this->command->info('✅ ' . count($categories) . ' categories seeded.');
    }
}

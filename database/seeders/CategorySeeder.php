<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name_en' => 'Food', 'name_ar' => 'طعام', 'icon' => 'food'],
            ['name_en' => 'Transportation', 'name_ar' => 'مواصلات', 'icon' => 'transportation'],
            ['name_en' => 'Shopping', 'name_ar' => 'تسوق', 'icon' => 'shopping'],
            ['name_en' => 'Bills', 'name_ar' => 'فواتير', 'icon' => 'bills'],
            ['name_en' => 'Health', 'name_ar' => 'صحة', 'icon' => 'health'],
            ['name_en' => 'Education', 'name_ar' => 'تعليم', 'icon' => 'education'],
            ['name_en' => 'Entertainment', 'name_ar' => 'ترفيه', 'icon' => 'entertainment'],
            ['name_en' => 'Travel', 'name_ar' => 'سفر', 'icon' => 'travel'],
            ['name_en' => 'Other', 'name_ar' => 'أخرى', 'icon' => 'other'],
        ];

        foreach ($categories as $category) {
            Category::query()->updateOrCreate(
                ['name_en' => $category['name_en']],
                [
                    'name_ar' => $category['name_ar'],
                    'icon' => $category['icon'],
                    'is_active' => true,
                ]
            );
        }
    }
}

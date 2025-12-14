<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CategoriesSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now();

        $categories = [
            [
                'name' => 'Hoa sinh nhật',
                'slug' => 'hoa-sinh-nhat',
                'image' => null,
                'sort_order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Hoa tình yêu',
                'slug' => 'hoa-tinh-yeu',
                'image' => null,
                'sort_order' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Hoa khai trương',
                'slug' => 'hoa-khai-truong',
                'image' => null,
                'sort_order' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Hoa chúc mừng',
                'slug' => 'hoa-chuc-mung',
                'image' => null,
                'sort_order' => 4,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        foreach ($categories as $cat) {
            if (! DB::table('categories')->where('slug', $cat['slug'])->exists()) {
                DB::table('categories')->insert($cat);
            }
        }
    }
}

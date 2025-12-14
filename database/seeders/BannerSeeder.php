<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Banner;

class BannerSeeder extends Seeder
{
    public function run()
    {
        $banners = [
            [
                'image' => '/image/bannerqc1.png',
                'alt' => 'Khuyến mãi đặc biệt',
                'sort_order' => 1,
                'active' => true,
            ],
            [
                'image' => '/image/banner2.png',
                'alt' => 'Hoa tươi mỗi ngày',
                'sort_order' => 2,
                'active' => true,
            ],
            [
                'image' => '/image/banner3.png',
                'alt' => 'Giao hàng toàn quốc',
                'sort_order' => 3,
                'active' => true,
            ],
        ];

        foreach ($banners as $banner) {
            Banner::create($banner);
        }
    }
}

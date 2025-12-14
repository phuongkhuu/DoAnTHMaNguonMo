<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Adjust these IDs to match your categories table
        $categories = [
            'Hoa Sinh Nhật'  => 1,
            'Hoa Tình Yêu'   => 2,
            'Hoa Khai Trương' => 3,
            'Hoa Chúc Mừng' => 4,
        ];

        $products = [
            // Hoa Chúc Mừng
            [
                'category_id' => $categories['Hoa Chúc Mừng'],
                'name' => 'Hoa Hướng Dương Tỏa Sáng',
                'slug' => Str::slug('Hoa Hướng Dương Tỏa Sáng'),
                'price' => 450000,
                'image' => '/image/hoahuongduong.png',
            ],
            [
                'category_id' => $categories['Hoa Chúc Mừng'],
                'name' => 'Bó Hoa Đồng Tiền Chúc Mừng',
                'slug' => Str::slug('Bó Hoa Đồng Tiền Chúc Mừng'),
                'price' => 500000,
                'image' => '/image/bohoadongtien.png',
            ],
            [
                'category_id' => $categories['Hoa Chúc Mừng'],
                'name' => 'Bó Hoa Trạng Nguyên Đỏ Rực',
                'slug' => Str::slug('Bó Hoa Trạng Nguyên Đỏ Rực'),
                'price' => 650000,
                'image' => '/image/hoatrangnguyen.png',
            ],

            // Hoa Khai Trương
            [
                'category_id' => $categories['Hoa Khai Trương'],
                'name' => 'Hoa Cát Tường May Mắn',
                'slug' => Str::slug('Hoa Cát Tường May Mắn'),
                'price' => 620000,
                'image' => '/image/hoacattuong.png',
            ],
            [
                'category_id' => $categories['Hoa Khai Trương'],
                'name' => 'Bó Hoa Lily Trắng Thịnh Vượng',
                'slug' => Str::slug('Bó Hoa Lily Trắng Thịnh Vượng'),
                'price' => 780000,
                'image' => '/image/hoalilytrang.png',
            ],
            [
                'category_id' => $categories['Hoa Khai Trương'],
                'name' => 'Bó Hoa Lan Vàng Đại Cát',
                'slug' => Str::slug('Bó Hoa Lan Vàng Đại Cát'),
                'price' => 850000,
                'image' => '/image/hoalanvang.png',
            ],

            // Hoa Sinh Nhật
            [
                'category_id' => $categories['Hoa Sinh Nhật'],
                'name' => 'Bó Hoa Lavender Thơm Ngát',
                'slug' => Str::slug('Bó Hoa Lavender Thơm Ngát'),
                'price' => 700000,
                'image' => '/image/hoalavender.png',
            ],
            [
                'category_id' => $categories['Hoa Sinh Nhật'],
                'name' => 'Giỏ hoa tulip hồng',
                'slug' => Str::slug('Giỏ hoa tulip hồng'),
                'price' => 520000, // discounted price
                'short_description' => 'Giá gốc 650.000đ, giảm còn 520.000đ',
                'image' => '/image/giohoatulip.png',
            ],
            [
                'category_id' => $categories['Hoa Sinh Nhật'],
                'name' => 'Bó Hoa Cẩm Tú Cầu Hồng Nhạt',
                'slug' => Str::slug('Bó Hoa Cẩm Tú Cầu Hồng Nhạt'),
                'price' => 760000,
                'image' => '/image/hoacamtucauhong.png',
            ],
            [
                'category_id' => $categories['Hoa Sinh Nhật'],
                'name' => 'Bó Hoa Hồng Đỏ Lớn Ấn Tượng',
                'slug' => Str::slug('Bó Hoa Hồng Đỏ Lớn Ấn Tượng'),
                'price' => 850000,
                'image' => '/image/hoahongdolon.png',
            ],
            [
                'category_id' => $categories['Hoa Sinh Nhật'],
                'name' => 'Bó Hoa Baby Trắng',
                'slug' => Str::slug('Bó Hoa Baby Trắng'),
                'price' => 320000, // discounted price
                'short_description' => 'Giá gốc 400.000đ, giảm còn 320.000đ',
                'image' => '/image/hoababy.png',
            ],

            // Hoa Tình Yêu
            [
                'category_id' => $categories['Hoa Tình Yêu'],
                'name' => 'Hoa Hồng Đỏ Tình Yêu',
                'slug' => Str::slug('Hoa Hồng Đỏ Tình Yêu'),
                'price' => 500000,
                'image' => '/image/hoahongdo.png',
            ],
            [
                'category_id' => $categories['Hoa Tình Yêu'],
                'name' => 'Bó Hoa Hồng Trắng Lãng Mạn',
                'slug' => Str::slug('Bó Hoa Hồng Trắng Lãng Mạn'),
                'price' => 450000,
                'image' => '/image/hoahongtrang.png',
            ],
            [
                'category_id' => $categories['Hoa Tình Yêu'],
                'name' => 'Lẵng Hoa Hồng Tím Thủy Chung',
                'slug' => Str::slug('Lẵng Hoa Hồng Tím Thủy Chung'),
                'price' => 600000,
                'image' => '/image/hoahongtim.png',
            ],
            [
                'category_id' => $categories['Hoa Tình Yêu'],
                'name' => 'Hoa Hồng Pastel',
                'slug' => Str::slug('Hoa Hồng Pastel'),
                'price' => 400000, // discounted price
                'short_description' => 'Giá gốc 500.000đ, giảm còn 400.000đ',
                'image' => '/image/hongpastel.png',
            ],
        ];

        foreach ($products as $p) {
            Product::create(array_merge([
                'short_description' => $p['short_description'] ?? null,
                'description' => null,
                'is_best_seller' => 0,
            ], $p));
        }
    }
}

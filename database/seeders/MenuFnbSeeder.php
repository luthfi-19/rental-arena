<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MenuFnb;

class MenuFnbSeeder extends Seeder
{
    public function run(): void
    {
        $menus = [
            // Kategori: Makanan
            ['nama_menu' => 'Indomie Goreng Double Telur', 'kategori' => 'makanan', 'harga' => 15000],
            ['nama_menu' => 'Indomie Rebus Kornet', 'kategori' => 'makanan', 'harga' => 18000],
            ['nama_menu' => 'Nasi Goreng Spesial Arena', 'kategori' => 'makanan', 'harga' => 25000],
            ['nama_menu' => 'Snack Platter (Sosis, Kentang, Nugget)', 'kategori' => 'makanan', 'harga' => 30000],
            ['nama_menu' => 'Roti Bakar Coklat Keju', 'kategori' => 'makanan', 'harga' => 15000],
            ['nama_menu' => 'Kentang Goreng (French Fries)', 'kategori' => 'makanan', 'harga' => 18000],
            ['nama_menu' => 'Seblak Spesial Arena', 'kategori' => 'makanan', 'harga' => 20000],
            
            // Kategori: Minuman
            ['nama_menu' => 'Blaugrana Berry Squash', 'kategori' => 'minuman', 'harga' => 22000], // Soda dengan sirup blueberry & strawberry
            ['nama_menu' => 'Es Kopi Susu Aren', 'kategori' => 'minuman', 'harga' => 18000],
            ['nama_menu' => 'Ice Lemon Tea', 'kategori' => 'minuman', 'harga' => 12000],
            ['nama_menu' => 'Es Teh Manis Jumbo', 'kategori' => 'minuman', 'harga' => 8000],
            ['nama_menu' => 'Matcha Latte Dingin', 'kategori' => 'minuman', 'harga' => 20000],
            ['nama_menu' => 'Coca Cola Dingin', 'kategori' => 'minuman', 'harga' => 10000],
            ['nama_menu' => 'Air Mineral Botol 600ml', 'kategori' => 'minuman', 'harga' => 5000],
        ];

        foreach ($menus as $menu) {
            MenuFnb::create(array_merge($menu, [
                'gambar' => null, // Dibiarkan null agar fallback 'No Image' di UI bekerja
                'status_aktif' => true
            ]));
        }
    }
}
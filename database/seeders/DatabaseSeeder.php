<?php

namespace database\seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Perangkat;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Users (Owner & Kasir)
        User::create([
            'nama' => 'Muhammad Luthfi Ramadhan',
            'username' => 'owner_luthfi',
            'password' => Hash::make('secret123'),
            'role' => 'owner'
        ]);

        User::create([
            'nama' => 'Budi Kasir',
            'username' => 'kasir_budi',
            'password' => Hash::make('kasir123'),
            'role' => 'kasir'
        ]);

        // 2. Seed Perangkat (Campuran status & jam terbang)
        $dataPerangkat = [
            // Zona VIP (Konsol & Aksesoris)
            ['kode_unit' => 'PS5-VIP-01', 'jenis' => 'konsol', 'zona' => 'VIP Room', 'jam_terbang_total' => 120, 'ambang_batas_servis' => 600, 'status' => 'tersedia'],
            ['kode_unit' => 'JS-VIP-01A', 'jenis' => 'joystick', 'zona' => 'VIP Room', 'jam_terbang_total' => 285, 'ambang_batas_servis' => 300, 'status' => 'tersedia'], // Mendekati ambang batas (Kuning)
            ['kode_unit' => 'HS-VIP-01A', 'jenis' => 'headset', 'zona' => 'VIP Room', 'jam_terbang_total' => 510, 'ambang_batas_servis' => 500, 'status' => 'maintenance'], // Melewati batas / Rusak (Merah)

            // Zona Regular (Menggunakan Jam Terbang Bervariasi)
            ['kode_unit' => 'PS4-REG-01', 'jenis' => 'konsol', 'zona' => 'Regular Area', 'jam_terbang_total' => 450, 'ambang_batas_servis' => 800, 'status' => 'tersedia'],
            ['kode_unit' => 'PS4-REG-02', 'jenis' => 'konsol', 'zona' => 'Regular Area', 'jam_terbang_total' => 320, 'ambang_batas_servis' => 800, 'status' => 'tersedia'],
            
            // Zona PC E-Sport Arena
            ['kode_unit' => 'PC-ESP-01', 'jenis' => 'konsol', 'zona' => 'E-Sport Stage', 'jam_terbang_total' => 50, 'ambang_batas_servis' => 1000, 'status' => 'tersedia'],
            ['kode_unit' => 'HS-ESP-01A', 'jenis' => 'headset', 'zona' => 'E-Sport Stage', 'jam_terbang_total' => 495, 'ambang_batas_servis' => 500, 'status' => 'tersedia'], // Mendekati batas (Kuning)
        ];

        foreach ($dataPerangkat as $perangkat) {
            Perangkat::create(array_merge($perangkat, ['tanggal_servis_terakhir' => now()->subMonths(2)]));
        }
    }
}
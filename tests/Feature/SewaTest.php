<?php
namespace Tests\Feature;
use Tests\TestCase;
use App\Models\User;
use App\Models\Perangkat;
use App\Models\TransaksiSewa;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SewaTest extends TestCase
{
    use RefreshDatabase;

    public function test_mencegah_double_booking_pada_perangkat_yang_sama()
    {
        $kasir = User::factory()->create(['role' => 'kasir']);
        $perangkat = Perangkat::factory()->create(['status' => 'dipakai']);

        // Kasir mencoba menyewa perangkat yang sedang 'dipakai'
        $response = $this->actingAs($kasir)->post(route('sewa.mulai'), [
            'id_perangkat' => $perangkat->id_perangkat,
            'tarif_per_jam' => 15000
        ]);

        // Harus dikembalikan dengan error Exception guard yang kita buat
        $response->assertSessionHasErrors();
        $this->assertEquals(0, TransaksiSewa::count());
    }

    public function test_kalkulasi_jam_terbang_dan_trigger_maintenance()
    {
        $kasir = User::factory()->create(['role' => 'kasir']);
        $perangkat = Perangkat::factory()->create([
            'status' => 'dipakai',
            'ambang_batas_servis' => 100,
            'jam_terbang_total' => 99 // Sisa 1 jam
        ]);

        $sewa = TransaksiSewa::factory()->create([
            'id_perangkat' => $perangkat->id_perangkat,
            'waktu_mulai' => now()->subMinutes(120), // Disewa selama 2 jam
            'tarif_per_jam' => 10000,
            'status_sesi' => 'berjalan'
        ]);

        $response = $this->actingAs($kasir)->post(route('sewa.selesai', $sewa->id_sewa));

        $perangkat->refresh();
        
        $this->assertEquals(101, $perangkat->jam_terbang_total);
        $this->assertEquals('maintenance', $perangkat->status);
        $this->assertEquals(1, $perangkat->riwayat_kerusakan); // Nambah 1
    }
}
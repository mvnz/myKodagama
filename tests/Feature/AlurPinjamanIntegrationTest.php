<?php
/**
 * Integration Test: Alur lengkap Pinjaman
 *
 * Menguji end-to-end flow:
 * Pengajuan → Verifikasi → Persetujuan → Pembayaran Angsuran → Lunas
 */

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\Angsuran;
use App\Models\Permission;
use App\Models\Pinjaman;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlurPinjamanIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $petugas;
    private Anggota $anggota;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup roles & permissions
        $roleAdmin = Role::factory()->create(['slug' => 'admin', 'nama' => 'Administrator']);
        $permisi   = [
            'pinjaman.lihat', 'pinjaman.tambah', 'pinjaman.edit',
            'pinjaman.hapus', 'pinjaman.verifikasi', 'pinjaman.setujui',
            'angsuran.lihat', 'angsuran.bayar', 'anggota.lihat',
        ];
        foreach ($permisi as $slug) {
            $perm = Permission::firstOrCreate(['slug' => $slug], [
                'nama' => ucwords(str_replace('.', ' ', $slug)),
                'grup' => explode('.', $slug)[0],
            ]);
            $roleAdmin->permissions()->syncWithoutDetaching([$perm->id]);
        }

        $this->admin   = User::factory()->admin()->create(['role_id' => $roleAdmin->id]);
        $this->petugas = User::factory()->petugas()->create(['role_id' => $roleAdmin->id]);
        $this->anggota = Anggota::factory()->create(['status' => 'aktif']);
    }

    /** STEP 1: Anggota dapat mengajukan pinjaman */
    public function test_step1_anggota_dapat_mengajukan_pinjaman(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('pinjaman.store'), [
                'anggota_id'     => $this->anggota->id,
                'jumlah_pinjaman'=> 2_400_000,
                'jangka_waktu'   => 12,
                'bunga'          => 1.5,
                'tujuan'         => 'Modal usaha kecil',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('pinjaman', [
            'anggota_id' => $this->anggota->id,
            'status'     => 'diajukan',
        ]);
    }

    /** STEP 2: Petugas memverifikasi pinjaman */
    public function test_step2_petugas_dapat_verifikasi_pinjaman(): void
    {
        $pinjaman = Pinjaman::factory()->create([
            'anggota_id' => $this->anggota->id,
            'status'     => 'diajukan',
        ]);

        $this->actingAs($this->petugas)
            ->post(route('pinjaman.verifikasi', $pinjaman))
            ->assertRedirect();

        $this->assertDatabaseHas('pinjaman', [
            'id'     => $pinjaman->id,
            'status' => 'diverifikasi',
        ]);
    }

    /** STEP 3: Admin menyetujui pinjaman & jadwal angsuran dibuat otomatis */
    public function test_step3_admin_menyetujui_pinjaman_dan_jadwal_angsuran_terbuat(): void
    {
        $pinjaman = Pinjaman::factory()->create([
            'anggota_id'  => $this->anggota->id,
            'status'      => 'diverifikasi',
            'jangka_waktu'=> 6,
        ]);

        $this->actingAs($this->admin)
            ->post(route('pinjaman.setujui', $pinjaman))
            ->assertRedirect();

        $this->assertDatabaseHas('pinjaman', [
            'id'     => $pinjaman->id,
            'status' => 'disetujui',
        ]);

        // Jadwal angsuran harus dibuat sebanyak jangka waktu
        $this->assertDatabaseCount('angsuran', 6);
    }

    /** STEP 4: Tidak boleh membuat jadwal angsuran duplikat */
    public function test_step4_tidak_membuat_angsuran_duplikat_saat_disetujui_ulang(): void
    {
        $pinjaman = Pinjaman::factory()->disetujui()->create([
            'anggota_id'  => $this->anggota->id,
            'jangka_waktu'=> 12,
        ]);
        Angsuran::factory()->count(12)->create(['pinjaman_id' => $pinjaman->id]);

        // Coba setujui lagi
        $this->actingAs($this->admin)
            ->post(route('pinjaman.setujui', $pinjaman));

        // Harus tetap 12, tidak bertambah
        $this->assertCount(12, $pinjaman->fresh()->angsuran);
    }

    /** STEP 5: Bayar angsuran satu per satu */
    public function test_step5_bayar_angsuran_satu_persatu(): void
    {
        $pinjaman = Pinjaman::factory()->disetujui()->create([
            'anggota_id' => $this->anggota->id,
        ]);
        $angsuran = Angsuran::factory()->create([
            'pinjaman_id' => $pinjaman->id,
            'status'      => 'belum',
        ]);

        $this->actingAs($this->admin)
            ->post(route('angsuran.bayar', $angsuran), [
                'tanggal_bayar' => now()->toDateString(),
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('angsuran', [
            'id'     => $angsuran->id,
            'status' => 'sudah',
        ]);
    }

    /** STEP 6: Tidak bisa bayar angsuran yang sudah lunas */
    public function test_step6_tidak_bisa_bayar_angsuran_yang_sudah_lunas(): void
    {
        $pinjaman = Pinjaman::factory()->disetujui()->create(['anggota_id' => $this->anggota->id]);
        $angsuran = Angsuran::factory()->sudahBayar()->create(['pinjaman_id' => $pinjaman->id]);

        $this->actingAs($this->admin)
            ->post(route('angsuran.bayar', $angsuran), [
                'tanggal_bayar' => now()->toDateString(),
            ])
            ->assertRedirect();

        // Status harus tetap 'sudah', tidak berubah
        $this->assertEquals('sudah', $angsuran->fresh()->status);
    }

    /** STEP 7: Pinjaman dapat ditolak */
    public function test_step7_pinjaman_dapat_ditolak(): void
    {
        $pinjaman = Pinjaman::factory()->create([
            'anggota_id' => $this->anggota->id,
            'status'     => 'diajukan',
        ]);

        $this->actingAs($this->admin)
            ->post(route('pinjaman.tolak', $pinjaman))
            ->assertRedirect();

        $this->assertDatabaseHas('pinjaman', [
            'id'     => $pinjaman->id,
            'status' => 'ditolak',
        ]);
    }

    /** STEP 8: Pinjaman dapat dihapus oleh admin */
    public function test_step8_pinjaman_dapat_dihapus(): void
    {
        $pinjaman = Pinjaman::factory()->create(['anggota_id' => $this->anggota->id]);

        $this->actingAs($this->admin)
            ->delete(route('pinjaman.destroy', $pinjaman))
            ->assertRedirect();

        $this->assertDatabaseMissing('pinjaman', ['id' => $pinjaman->id]);
    }

    /** Cicilan per bulan dihitung benar saat store */
    public function test_cicilan_per_bulan_dihitung_otomatis_saat_store(): void
    {
        $jumlah = 1_200_000;
        $jangka = 12;
        $bunga  = 1.5;
        $expectedCicilan = round(($jumlah / $jangka) + ($jumlah * $bunga / 100), 2);

        $this->actingAs($this->admin)
            ->post(route('pinjaman.store'), [
                'anggota_id'     => $this->anggota->id,
                'jumlah_pinjaman'=> $jumlah,
                'jangka_waktu'   => $jangka,
                'bunga'          => $bunga,
                'tujuan'         => 'Test cicilan',
            ]);

        $pinjaman = Pinjaman::where('anggota_id', $this->anggota->id)->first();
        $this->assertNotNull($pinjaman);
        $this->assertEquals($expectedCicilan, $pinjaman->cicilan_per_bulan);
    }

    /** User_id otomatis diisi dari user yang login */
    public function test_user_id_otomatis_diisi_saat_store(): void
    {
        $this->actingAs($this->admin)
            ->post(route('pinjaman.store'), [
                'anggota_id'     => $this->anggota->id,
                'jumlah_pinjaman'=> 500_000,
                'jangka_waktu'   => 6,
                'bunga'          => 1.5,
                'tujuan'         => 'Test user_id',
            ]);

        $pinjaman = Pinjaman::where('anggota_id', $this->anggota->id)->first();
        $this->assertEquals($this->admin->id, $pinjaman->user_id);
    }

    /** Validasi: jumlah pinjaman di bawah minimum ditolak */
    public function test_store_gagal_jumlah_pinjaman_kurang_dari_minimum(): void
    {
        $this->actingAs($this->admin)
            ->post(route('pinjaman.store'), [
                'anggota_id'     => $this->anggota->id,
                'jumlah_pinjaman'=> 50_000, // di bawah min 100.000
                'jangka_waktu'   => 6,
                'bunga'          => 1.5,
                'tujuan'         => 'Test',
            ])
            ->assertSessionHasErrors('jumlah_pinjaman');
    }

    /** Validasi: anggota_id wajib ada */
    public function test_store_gagal_tanpa_anggota_id(): void
    {
        $this->actingAs($this->admin)
            ->post(route('pinjaman.store'), [
                'jumlah_pinjaman'=> 1_000_000,
                'jangka_waktu'   => 12,
                'bunga'          => 1.5,
                'tujuan'         => 'Test',
            ])
            ->assertSessionHasErrors('anggota_id');
    }
}

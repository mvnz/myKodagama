<?php

namespace Tests\Feature\Laporan;

use App\Models\Koperasi\Angsuran;
use App\Models\Koperasi\Pendapatan;
use App\Models\Koperasi\Pengeluaran;
use App\Models\Koperasi\Pinjaman;
use App\Models\Koperasi\Simpanan;
use App\Models\Koperasi\Anggota;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaporanTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    // ─── Akses ─────────────────────────────────────────────────

    public function test_admin_bisa_akses_laporan_keuangan(): void
    {
        $this->actingAs($this->admin)
            ->get(route('laporan.keuangan'))
            ->assertOk();
    }

    public function test_admin_bisa_akses_laporan_laba_rugi(): void
    {
        $this->actingAs($this->admin)
            ->get(route('laporan.laba-rugi'))
            ->assertOk();
    }

    public function test_tamu_tidak_bisa_akses_laporan(): void
    {
        $this->get(route('laporan.keuangan'))
            ->assertRedirect(route('masuk'));

        $this->get(route('laporan.laba-rugi'))
            ->assertRedirect(route('masuk'));
    }

    // ─── Filter Tahun / Bulan ──────────────────────────────────

    public function test_laporan_keuangan_bisa_difilter_per_tahun(): void
    {
        $this->actingAs($this->admin)
            ->get(route('laporan.keuangan', ['tahun' => '2026']))
            ->assertOk();
    }

    public function test_laporan_keuangan_bisa_difilter_per_bulan(): void
    {
        $this->actingAs($this->admin)
            ->get(route('laporan.keuangan', ['tahun' => '2026', 'bulan' => '5']))
            ->assertOk();
    }

    public function test_laporan_laba_rugi_bisa_difilter_per_bulan(): void
    {
        $this->actingAs($this->admin)
            ->get(route('laporan.laba-rugi', ['tahun' => '2026', 'bulan' => '3']))
            ->assertOk();
    }

    // ─── Kalkulasi Laporan Keuangan ────────────────────────────

    public function test_total_simpanan_masuk_dihitung_benar(): void
    {
        $anggota = Anggota::factory()->create();

        Simpanan::factory()->create([
            'anggota_id'     => $anggota->id,
            'jenis_simpanan' => 'pokok',
            'jumlah'         => 200_000,
            'tanggal'        => '2026-01-01',
        ]);
        Simpanan::factory()->create([
            'anggota_id'     => $anggota->id,
            'jenis_simpanan' => 'wajib',
            'jumlah'         => 100_000,
            'tanggal'        => '2026-01-15',
        ]);
        Simpanan::factory()->create([
            'anggota_id'     => $anggota->id,
            'jenis_simpanan' => 'sukarela',
            'jumlah'         => 300_000,
            'tanggal'        => '2026-02-01',
        ]);

        $totalPokok    = Simpanan::where('jenis_simpanan', 'pokok')->whereYear('tanggal', 2026)->sum('jumlah');
        $totalWajib    = Simpanan::where('jenis_simpanan', 'wajib')->whereYear('tanggal', 2026)->sum('jumlah');
        $totalSukarela = Simpanan::where('jenis_simpanan', 'sukarela')->whereYear('tanggal', 2026)->sum('jumlah');

        $this->assertEquals(200_000, $totalPokok);
        $this->assertEquals(100_000, $totalWajib);
        $this->assertEquals(300_000, $totalSukarela);
        $this->assertEquals(600_000, $totalPokok + $totalWajib + $totalSukarela);
    }

    public function test_total_angsuran_dan_bunga_diterima(): void
    {
        $pinjaman = Pinjaman::factory()->disetujui()->create([
            'anggota_id' => Anggota::factory()->create()->id,
        ]);

        Angsuran::factory()->create([
            'pinjaman_id'    => $pinjaman->id,
            'status'         => 'sudah',
            'tanggal_bayar'  => '2026-03-01',
            'jumlah_angsuran'=> 550_000,
            'pokok'          => 500_000,
            'bunga'          => 50_000,
        ]);
        Angsuran::factory()->create([
            'pinjaman_id'    => $pinjaman->id,
            'status'         => 'sudah',
            'tanggal_bayar'  => '2026-04-01',
            'jumlah_angsuran'=> 550_000,
            'pokok'          => 500_000,
            'bunga'          => 50_000,
        ]);
        // Angsuran belum bayar — tidak masuk
        Angsuran::factory()->create([
            'pinjaman_id'    => $pinjaman->id,
            'status'         => 'belum',
            'jumlah_angsuran'=> 550_000,
            'bunga'          => 50_000,
        ]);

        $totalDiterima = Angsuran::where('status', 'sudah')
            ->whereYear('tanggal_bayar', 2026)->sum('jumlah_angsuran');
        $totalBunga = Angsuran::where('status', 'sudah')
            ->whereYear('tanggal_bayar', 2026)->sum('bunga');

        $this->assertEquals(1_100_000, $totalDiterima);
        $this->assertEquals(100_000, $totalBunga);
    }

    // ─── Kalkulasi Laba Rugi ───────────────────────────────────

    public function test_shu_positif_jika_pendapatan_lebih_besar(): void
    {
        $pinjaman = Pinjaman::factory()->disetujui()->create([
            'anggota_id' => Anggota::factory()->create()->id,
        ]);

        // Bunga masuk dari angsuran
        Angsuran::factory()->create([
            'pinjaman_id'   => $pinjaman->id,
            'status'        => 'sudah',
            'tanggal_bayar' => '2026-02-01',
            'bunga'         => 300_000,
            'pokok'         => 500_000,
            'jumlah_angsuran'=> 800_000,
        ]);

        // Pendapatan lain
        Pendapatan::factory()->create([
            'user_id'  => $this->admin->id,
            'tanggal'  => '2026-02-10',
            'kategori' => 'jasa_admin',
            'jumlah'   => 200_000,
        ]);

        // Pengeluaran
        Pengeluaran::factory()->create([
            'user_id'  => $this->admin->id,
            'tanggal'  => '2026-02-15',
            'kategori' => 'atk',
            'jumlah'   => 150_000,
        ]);

        $bungaMasuk          = Angsuran::where('status', 'sudah')->whereYear('tanggal_bayar', 2026)->sum('bunga');
        $totalPendapatanLain = Pendapatan::whereYear('tanggal', 2026)->sum('jumlah');
        $totalPengeluaran    = Pengeluaran::whereYear('tanggal', 2026)->sum('jumlah');
        $shu                 = ($bungaMasuk + $totalPendapatanLain) - $totalPengeluaran;

        $this->assertEquals(300_000, $bungaMasuk);
        $this->assertEquals(200_000, $totalPendapatanLain);
        $this->assertEquals(150_000, $totalPengeluaran);
        $this->assertEquals(350_000, $shu);
        $this->assertGreaterThan(0, $shu);
    }

    public function test_shu_negatif_jika_pengeluaran_lebih_besar(): void
    {
        Pengeluaran::factory()->create([
            'user_id'  => $this->admin->id,
            'tanggal'  => '2026-01-10',
            'kategori' => 'gaji_honor',
            'jumlah'   => 5_000_000,
        ]);

        $totalPendapatan  = 0; // tidak ada pendapatan bulan ini
        $totalPengeluaran = Pengeluaran::whereYear('tanggal', 2026)->sum('jumlah');
        $shu              = $totalPendapatan - $totalPengeluaran;

        $this->assertLessThan(0, $shu);
        $this->assertEquals(-5_000_000, $shu);
    }

    public function test_laporan_keuangan_kosong_jika_belum_ada_data(): void
    {
        // Tidak ada data sama sekali — laporan harus tetap tampil (tidak error)
        $this->actingAs($this->admin)
            ->get(route('laporan.keuangan', ['tahun' => '2026']))
            ->assertOk();

        $this->actingAs($this->admin)
            ->get(route('laporan.laba-rugi', ['tahun' => '2026']))
            ->assertOk();
    }

    // ─── Cetak ─────────────────────────────────────────────────

    public function test_admin_bisa_cetak_laporan_keuangan(): void
    {
        $this->actingAs($this->admin)
            ->get(route('laporan.keuangan.cetak', ['tahun' => '2026']))
            ->assertOk();
    }
}

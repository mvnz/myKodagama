<?php

/**
 * Test: Alur lengkap Simpan Pinjam
 *
 * Menguji skenario nyata:
 * - Anggota GOL 1 bergabung, setor simpanan rutin
 * - Mengajukan pinjaman dan melunasi angsuran
 * - Naik golongan → simpanan baru pakai golongan baru, lama tidak berubah
 */

namespace Tests\Feature\SimpanPinjam;

use App\Models\Koperasi\Anggota;
use App\Models\Koperasi\Angsuran;
use App\Models\Permission;
use App\Models\Koperasi\Pinjaman;
use App\Models\Role;
use App\Models\Koperasi\Simpanan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlurSimpanPinjamTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Anggota $anggota;

    protected function setUp(): void
    {
        parent::setUp();

        $role    = Role::factory()->create(['slug' => 'admin', 'nama' => 'Administrator']);
        $slugs   = [
            'anggota.lihat', 'anggota.tambah', 'anggota.edit',
            'simpanan.lihat', 'simpanan.tambah', 'simpanan.edit', 'simpanan.hapus',
            'pinjaman.lihat', 'pinjaman.tambah', 'pinjaman.edit',
            'pinjaman.verifikasi', 'pinjaman.setujui',
            'angsuran.lihat', 'angsuran.bayar',
        ];
        foreach ($slugs as $slug) {
            $perm = Permission::firstOrCreate(['slug' => $slug], [
                'nama' => ucwords(str_replace('.', ' ', $slug)),
                'grup' => explode('.', $slug)[0],
            ]);
            $role->permissions()->syncWithoutDetaching([$perm->id]);
        }

        $this->admin   = User::factory()->admin()->create(['role_id' => $role->id]);
        $this->anggota = Anggota::factory()->golongan('PNS GOL 1')->create(['status' => 'aktif']);
    }

    // ─────────────────────────────────────────────────────────────
    // SIMPANAN
    // ─────────────────────────────────────────────────────────────

    /** Simpanan pokok saat pertama bergabung */
    public function test_simpanan_pokok_awal_bergabung(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('simpanan.store'), [
                'anggota_id'     => $this->anggota->id,
                'jenis_simpanan' => 'pokok',
                'jumlah'         => 50000,
                'tanggal'        => '2026-01-01',
                'keterangan'     => 'Simpanan pokok awal',
            ]);

        $response->assertRedirect(route('simpanan.index'));
        $this->assertDatabaseHas('simpanan', [
            'anggota_id'     => $this->anggota->id,
            'jenis_simpanan' => 'pokok',
            'jumlah'         => 50000,
            'jenis_anggota'  => 'PNS GOL 1',
        ]);
    }

    /** Simpanan wajib rutin bulanan selama 5 bulan */
    public function test_simpanan_wajib_rutin_bulanan(): void
    {
        $bulan = ['2026-01-15', '2026-02-15', '2026-03-15', '2026-04-15', '2026-05-15'];

        foreach ($bulan as $tgl) {
            $this->actingAs($this->admin)->post(route('simpanan.store'), [
                'anggota_id'     => $this->anggota->id,
                'jenis_simpanan' => 'wajib',
                'jumlah'         => 100000,
                'tanggal'        => $tgl,
            ]);
        }

        $this->assertEquals(5, Simpanan::where([
            'anggota_id'     => $this->anggota->id,
            'jenis_simpanan' => 'wajib',
        ])->count());

        $total = Simpanan::where('anggota_id', $this->anggota->id)
            ->where('jenis_simpanan', 'wajib')
            ->sum('jumlah');

        $this->assertEquals(500000, $total);
    }

    /** Simpanan sukarela bisa berbeda nominal tiap bulan */
    public function test_simpanan_sukarela_berbeda_nominal(): void
    {
        $data = [
            ['tanggal' => '2026-01-20', 'jumlah' => 200000],
            ['tanggal' => '2026-02-20', 'jumlah' => 350000],
            ['tanggal' => '2026-03-20', 'jumlah' => 500000],
        ];

        foreach ($data as $item) {
            $this->actingAs($this->admin)->post(route('simpanan.store'), [
                'anggota_id'     => $this->anggota->id,
                'jenis_simpanan' => 'sukarela',
                'jumlah'         => $item['jumlah'],
                'tanggal'        => $item['tanggal'],
            ]);
        }

        $this->assertEquals(1050000,
            Simpanan::where('anggota_id', $this->anggota->id)
                ->where('jenis_simpanan', 'sukarela')
                ->sum('jumlah')
        );
    }

    /** Total semua jenis simpanan terhitung benar */
    public function test_total_seluruh_simpanan_anggota(): void
    {
        Simpanan::factory()->create(['anggota_id' => $this->anggota->id, 'jenis_simpanan' => 'pokok',    'jumlah' => 50000]);
        Simpanan::factory()->create(['anggota_id' => $this->anggota->id, 'jenis_simpanan' => 'wajib',    'jumlah' => 100000]);
        Simpanan::factory()->create(['anggota_id' => $this->anggota->id, 'jenis_simpanan' => 'sukarela', 'jumlah' => 250000]);

        $total = Simpanan::where('anggota_id', $this->anggota->id)->sum('jumlah');

        $this->assertEquals(400000, $total);
    }

    // ─────────────────────────────────────────────────────────────
    // PINJAMAN
    // ─────────────────────────────────────────────────────────────

    /** Pengajuan pinjaman berhasil dengan status diajukan */
    public function test_pengajuan_pinjaman_berhasil(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('pinjaman.store'), [
                'anggota_id'      => $this->anggota->id,
                'jumlah_pinjaman' => 3_000_000,
                'jangka_waktu'    => 12,
                'bunga'           => 1.5,
                'tujuan'          => 'Renovasi rumah',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('pinjaman', [
            'anggota_id' => $this->anggota->id,
            'status'     => 'diajukan',
        ]);
    }

    /** Pinjaman tidak bisa diajukan dengan jumlah 0 */
    public function test_pinjaman_ditolak_jika_jumlah_nol(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('pinjaman.store'), [
                'anggota_id'      => $this->anggota->id,
                'jumlah_pinjaman' => 0,
                'jangka_waktu'    => 12,
                'bunga'           => 1.5,
                'tujuan'          => 'Test',
            ]);

        $response->assertSessionHasErrors('jumlah_pinjaman');
    }

    /** Verifikasi pinjaman mengubah status ke diverifikasi */
    public function test_verifikasi_pinjaman(): void
    {
        $pinjaman = Pinjaman::factory()->create([
            'anggota_id' => $this->anggota->id,
            'status'     => 'diajukan',
        ]);

        $this->actingAs($this->admin)
            ->post(route('pinjaman.verifikasi', $pinjaman))
            ->assertRedirect();

        $this->assertEquals('diverifikasi', $pinjaman->fresh()->status);
    }

    /** Persetujuan pinjaman mengubah status ke disetujui */
    public function test_persetujuan_pinjaman(): void
    {
        $pinjaman = Pinjaman::factory()->create([
            'anggota_id' => $this->anggota->id,
            'status'     => 'diverifikasi',
        ]);

        $this->actingAs($this->admin)
            ->post(route('pinjaman.setujui', $pinjaman))
            ->assertRedirect();

        $this->assertEquals('disetujui', $pinjaman->fresh()->status);
    }

    /** Setelah disetujui, angsuran otomatis terbuat sesuai jangka waktu */
    public function test_angsuran_terbuat_setelah_pinjaman_disetujui(): void
    {
        $pinjaman = Pinjaman::factory()->create([
            'anggota_id'   => $this->anggota->id,
            'status'       => 'diverifikasi',
            'jangka_waktu' => 12,
        ]);

        $this->actingAs($this->admin)
            ->post(route('pinjaman.setujui', $pinjaman));

        $this->assertEquals(12, Angsuran::where('pinjaman_id', $pinjaman->id)->count());
    }

    /** Pembayaran angsuran mengubah status angsuran ke sudah */
    public function test_bayar_angsuran_berhasil(): void
    {
        $pinjaman = Pinjaman::factory()->disetujui()->create([
            'anggota_id' => $this->anggota->id,
        ]);
        $angsuran = Angsuran::factory()->create([
            'pinjaman_id' => $pinjaman->id,
            'status'      => 'belum',
        ]);

        $this->actingAs($this->admin)
            ->post(route('angsuran.bayar', $angsuran))
            ->assertRedirect();

        $this->assertEquals('sudah', $angsuran->fresh()->status);
    }

    // ─────────────────────────────────────────────────────────────
    // ALUR LENGKAP: Simpan + Pinjam + Naik Golongan
    // ─────────────────────────────────────────────────────────────

    /**
     * Skenario nyata:
     * 1. Anggota GOL 1 setor simpanan Januari–Mei
     * 2. Naik ke GOL 2 bulan Juni
     * 3. Setor simpanan Juni–Agustus
     * 4. Simpanan Jan–Mei tetap tercatat GOL 1
     * 5. Simpanan Jun–Ags tercatat GOL 2
     */
    public function test_alur_naik_golongan_tidak_mengubah_histori(): void
    {
        // Setor simpanan saat masih GOL 1 (Jan–Mei)
        $bulanGol1 = ['2026-01-15', '2026-02-15', '2026-03-15', '2026-04-15', '2026-05-15'];
        foreach ($bulanGol1 as $tgl) {
            $this->actingAs($this->admin)->post(route('simpanan.store'), [
                'anggota_id'     => $this->anggota->id,
                'jenis_simpanan' => 'wajib',
                'jumlah'         => 100000,
                'tanggal'        => $tgl,
            ]);
        }

        // Anggota naik golongan
        $this->anggota->update(['jenis_anggota' => 'PNS GOL 2']);

        // Setor simpanan setelah naik (Jun–Ags)
        $bulanGol2 = ['2026-06-15', '2026-07-15', '2026-08-15'];
        foreach ($bulanGol2 as $tgl) {
            $this->actingAs($this->admin)->post(route('simpanan.store'), [
                'anggota_id'     => $this->anggota->id,
                'jenis_simpanan' => 'wajib',
                'jumlah'         => 150000,
                'tanggal'        => $tgl,
            ]);
        }

        $semua = Simpanan::where('anggota_id', $this->anggota->id)->get();

        $this->assertEquals(5, $semua->where('jenis_anggota', 'PNS GOL 1')->count(), '5 simpanan harus tetap GOL 1');
        $this->assertEquals(3, $semua->where('jenis_anggota', 'PNS GOL 2')->count(), '3 simpanan harus GOL 2');
        $this->assertCount(8, $semua);
    }

    /**
     * Skenario: Anggota mengajukan pinjaman → disetujui → bayar sebagian angsuran
     *           → naik golongan di tengah jalan → sisa angsuran tidak terpengaruh
     */
    public function test_alur_pinjaman_aktif_saat_naik_golongan(): void
    {
        // Pinjaman disetujui
        $pinjaman = Pinjaman::factory()->disetujui()->create([
            'anggota_id'   => $this->anggota->id,
            'jangka_waktu' => 6,
        ]);

        // Buat angsuran manual
        $angsuranList = Angsuran::factory()->count(6)->create([
            'pinjaman_id' => $pinjaman->id,
            'status'      => 'belum',
        ]);

        // Bayar 3 angsuran pertama
        foreach ($angsuranList->take(3) as $angsuran) {
            $this->actingAs($this->admin)
                ->post(route('angsuran.bayar', $angsuran));
        }

        // Anggota naik golongan di tengah cicilan
        $this->anggota->update(['jenis_anggota' => 'PNS GOL 2']);

        // Angsuran yang sudah dibayar tetap "sudah"
        $this->assertEquals(3, Angsuran::where('pinjaman_id', $pinjaman->id)
            ->where('status', 'sudah')->count());

        // Angsuran yang belum bayar tetap "belum" (tidak terpengaruh naik golongan)
        $this->assertEquals(3, Angsuran::where('pinjaman_id', $pinjaman->id)
            ->where('status', 'belum')->count());

        // Pinjaman masih aktif
        $this->assertEquals('disetujui', $pinjaman->fresh()->status);
    }

    /**
     * Skenario: Anggota Non-PNS lalu diangkat jadi PNS GOL 1
     * Simpanan sebelumnya tetap "Non-PNS"
     */
    public function test_alur_anggota_non_pns_diangkat_menjadi_pns(): void
    {
        $anggota = Anggota::factory()->golongan('Non-PNS')->create(['status' => 'aktif']);

        // Simpanan saat Non-PNS
        Simpanan::factory()->count(4)->create([
            'anggota_id'    => $anggota->id,
            'jenis_anggota' => 'Non-PNS',
        ]);

        // Diangkat jadi PNS GOL 1
        $anggota->update(['jenis_anggota' => 'PNS GOL 1']);

        // Simpanan baru setelah diangkat
        $this->actingAs($this->admin)->post(route('simpanan.store'), [
            'anggota_id'     => $anggota->id,
            'jenis_simpanan' => 'pokok',
            'jumlah'         => 100000,
            'tanggal'        => now()->format('Y-m-d'),
        ]);

        $semua = Simpanan::where('anggota_id', $anggota->id)->get();

        $this->assertEquals(4, $semua->where('jenis_anggota', 'Non-PNS')->count());
        $this->assertEquals(1, $semua->where('jenis_anggota', 'PNS GOL 1')->count());
    }

    // ─────────────────────────────────────────────────────────────
    // VALIDASI
    // ─────────────────────────────────────────────────────────────

    /** Simpanan dengan jumlah di bawah minimum ditolak */
    public function test_simpanan_ditolak_jika_jumlah_terlalu_kecil(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('simpanan.store'), [
                'anggota_id'     => $this->anggota->id,
                'jenis_simpanan' => 'wajib',
                'jumlah'         => 500, // minimum 1000
                'tanggal'        => '2026-01-01',
            ]);

        $response->assertSessionHasErrors('jumlah');
        $this->assertEquals(0, Simpanan::count());
    }

    /** Simpanan dengan jenis tidak valid ditolak */
    public function test_simpanan_ditolak_jika_jenis_tidak_valid(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('simpanan.store'), [
                'anggota_id'     => $this->anggota->id,
                'jenis_simpanan' => 'investasi', // bukan pokok/wajib/sukarela
                'jumlah'         => 100000,
                'tanggal'        => '2026-01-01',
            ]);

        $response->assertSessionHasErrors('jenis_simpanan');
    }

    /** Simpanan untuk anggota tidak aktif */
    public function test_simpanan_anggota_tidak_aktif_tetap_dapat_diinput(): void
    {
        $anggota = Anggota::factory()->tidakAktif()->create();

        // Sistem tidak memblokir input simpanan berdasarkan status anggota di sisi controller
        $response = $this->actingAs($this->admin)
            ->post(route('simpanan.store'), [
                'anggota_id'     => $anggota->id,
                'jenis_simpanan' => 'wajib',
                'jumlah'         => 100000,
                'tanggal'        => '2026-01-01',
            ]);

        // Hanya memverifikasi bahwa anggota_id valid (exists di tabel)
        $response->assertSessionDoesntHaveErrors('anggota_id');
    }

    /** Hapus simpanan berhasil */
    public function test_hapus_simpanan_berhasil(): void
    {
        $simpanan = Simpanan::factory()->create(['anggota_id' => $this->anggota->id]);

        $this->actingAs($this->admin)
            ->delete(route('simpanan.destroy', $simpanan))
            ->assertRedirect();

        $this->assertDatabaseMissing('simpanan', ['id' => $simpanan->id]);
    }
}

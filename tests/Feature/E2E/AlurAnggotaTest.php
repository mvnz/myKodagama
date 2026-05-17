<?php

namespace Tests\Feature\E2E;

use App\Models\Anggota;
use App\Models\Angsuran;
use App\Models\Pinjaman;
use App\Models\Simpanan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * E2E Workflow Test: Alur lengkap siklus anggota koperasi.
 *
 * Mensimulasikan:
 * 1. Pengguna login
 * 2. Mendaftarkan anggota baru
 * 3. Mencatat simpanan pokok
 * 4. Mengajukan pinjaman
 * 5. Admin menyetujui pinjaman (jadwal angsuran terbuat otomatis)
 * 6. Membayar angsuran satu per satu
 * 7. Pinjaman berubah jadi lunas setelah semua angsuran terbayar
 * 8. Melakukan logout
 */
class AlurAnggotaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_alur_lengkap_siklus_anggota(): void
    {
        // ── 1. Login ─────────────────────────────────────────────
        $admin = User::factory()->admin()->create(['email' => 'admin@tes.com']);

        $this->actingAs($admin);

        // ── 2. Daftar anggota baru ────────────────────────────────
        $responseAnggota = $this->post(route('anggota.store'), [
            'nomor_anggota'     => 'KOP-202601-0001',
            'nama'              => 'Wulandari Putri',
            'nik'               => '3471011234560001',
            'tempat_lahir'      => 'Sleman',
            'tanggal_lahir'     => '1988-06-15',
            'jenis_kelamin'     => 'Perempuan',
            'alamat'            => 'Jl. Kaliurang Km.10',
            'telepon'           => '087712345601',
            'pekerjaan'         => 'Guru',
            'email'             => 'wulan@example.com',
            'status'            => 'aktif',
            'tanggal_bergabung' => today()->format('Y-m-d'),
        ]);

        $responseAnggota->assertRedirect(route('anggota.index'));
        $anggota = Anggota::where('nik', '3471011234560001')->firstOrFail();
        $this->assertEquals('Wulandari Putri', $anggota->nama);

        // ── 3. Catat simpanan pokok ──────────────────────────────
        $responseSimpanan = $this->post(route('simpanan.store'), [
            'anggota_id'     => $anggota->id,
            'jenis_simpanan' => 'pokok',
            'jumlah'         => 100000,
            'tanggal'        => today()->format('Y-m-d'),
            'keterangan'     => 'Simpanan pokok awal bergabung',
        ]);

        $responseSimpanan->assertRedirect(route('simpanan.index'));
        $this->assertDatabaseHas('simpanan', [
            'anggota_id'     => $anggota->id,
            'jenis_simpanan' => 'pokok',
            'jumlah'         => 100000,
        ]);

        // ── 4. Ajukan pinjaman ───────────────────────────────────
        $responsePinjaman = $this->post(route('pinjaman.store'), [
            'anggota_id'      => $anggota->id,
            'jumlah_pinjaman' => 600000,
            'jangka_waktu'    => 6,
            'bunga'           => 0,
            'tujuan'          => 'Modal warung',
        ]);

        $responsePinjaman->assertRedirect(route('pinjaman.index'));
        $pinjaman = Pinjaman::where('anggota_id', $anggota->id)->firstOrFail();
        $this->assertEquals('diajukan', $pinjaman->status);

        // ── 5. Setujui pinjaman → jadwal angsuran terbuat ────────
        $this->put(route('pinjaman.update', $pinjaman), [
            'status'              => 'disetujui',
            'tanggal_persetujuan' => today()->format('Y-m-d'),
        ]);

        $pinjaman->refresh();
        $this->assertEquals('disetujui', $pinjaman->status);
        $this->assertEquals(6, $pinjaman->angsuran()->count());

        // ── 6. Bayar semua angsuran ──────────────────────────────
        $angsuranList = $pinjaman->angsuran()->orderBy('ke')->get();

        foreach ($angsuranList as $idx => $angsuran) {
            $response = $this->post(route('angsuran.bayar', $angsuran), [
                'tanggal_bayar' => today()->addMonths($idx + 1)->format('Y-m-d'),
            ]);

            $response->assertSessionHas('sukses');
            $this->assertEquals('sudah', $angsuran->fresh()->status);
        }

        // ── 7. Semua angsuran terbayar → pinjaman lunas ──────────
        $pinjaman->refresh();
        $this->assertEquals('lunas', $pinjaman->status);
        $this->assertEquals(0, $pinjaman->angsuran()->where('status', 'belum')->count());

        // ── 8. Logout ────────────────────────────────────────────
        $responseKeluar = $this->post(route('keluar'));
        $responseKeluar->assertRedirect(route('beranda'));
        $this->assertGuest();
    }

    public function test_anggota_tidak_dapat_mengajukan_pinjaman_tanpa_login(): void
    {
        $anggota = Anggota::factory()->create();

        $this->post(route('pinjaman.store'), [
            'anggota_id'      => $anggota->id,
            'jumlah_pinjaman' => 1000000,
            'jangka_waktu'    => 12,
            'bunga'           => 1.5,
            'tujuan'          => 'Modal',
        ])->assertRedirect('/masuk');
    }
}

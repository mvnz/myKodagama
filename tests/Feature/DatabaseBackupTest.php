<?php

namespace Tests\Feature;

use App\Models\Koperasi\Anggota;
use App\Models\Koperasi\Pengaturan;
use App\Models\User;
use App\Support\DatabaseBackup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class DatabaseBackupTest extends TestCase
{
    use RefreshDatabase;

    private string $dir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'kodagama-test-' . uniqid();
        Pengaturan::set('backup_lokasi', 'custom', 'backup');
        Pengaturan::set('backup_path', $this->dir, 'backup');
    }

    protected function tearDown(): void
    {
        foreach (glob($this->dir . DIRECTORY_SEPARATOR . '*') ?: [] as $f) {
            @unlink($f);
        }
        @rmdir($this->dir);
        parent::tearDown();
    }

    public function test_backup_dan_restore_mengembalikan_data(): void
    {
        Anggota::factory()->count(3)->create();
        $path = DatabaseBackup::buat();
        $this->assertFileExists($path);

        Anggota::query()->delete();
        Anggota::factory()->create(['nama' => 'Data Baru']);

        $hasil = DatabaseBackup::pulihkan($path);

        $this->assertSame(3, Anggota::count());
        $this->assertDatabaseMissing('anggota', ['nama' => 'Data Baru']);
        $this->assertGreaterThan(0, $hasil['baris']);
    }

    public function test_restore_menolak_file_bukan_backup(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'x');
        file_put_contents($file, gzencode("{\"foo\":1}\n"));

        $this->expectExceptionMessage('bukan backup Kodagama');
        try {
            DatabaseBackup::pulihkan($file);
        } finally {
            @unlink($file);
        }
    }

    public function test_halaman_dan_aksi_hanya_untuk_admin(): void
    {
        $anggota = User::factory()->create(['role' => 'anggota']);

        $this->actingAs($anggota)->get(route('pengaturan.database'))->assertForbidden();
        $this->actingAs($anggota)->post(route('pengaturan.database.backup'))->assertForbidden();
    }

    public function test_admin_buat_unduh_dan_hapus_backup(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('pengaturan.database'))->assertOk();
        $this->actingAs($admin)->post(route('pengaturan.database.backup'))->assertRedirect();

        $nama = DatabaseBackup::daftar()[0]['nama'];
        $this->actingAs($admin)->get(route('pengaturan.database.unduh', $nama))->assertOk();
        $this->actingAs($admin)->get(route('pengaturan.database.unduh', '..%2Fsecret'))->assertNotFound();
        $this->actingAs($admin)->delete(route('pengaturan.database.hapus', $nama))->assertRedirect();
        $this->assertSame([], DatabaseBackup::daftar());
    }

    public function test_restore_via_upload_butuh_konfirmasi(): void
    {
        $admin = User::factory()->admin()->create();
        Anggota::factory()->create(['nama' => 'Asli']);
        $path = DatabaseBackup::buat();
        Anggota::query()->delete();

        $file = new UploadedFile($path, 'b.ndjson.gz', null, null, true);
        $this->actingAs($admin)->post(route('pengaturan.database.restore'), ['file' => $file])
            ->assertSessionHasErrors('konfirmasi');
        $this->assertSame(0, Anggota::count());

        $this->actingAs($admin)->post(route('pengaturan.database.restore'), ['file' => $file, 'konfirmasi' => 'RESTORE'])
            ->assertSessionHas('sukses');
        $this->assertDatabaseHas('anggota', ['nama' => 'Asli']);
    }

    public function test_simpan_pengaturan_auto_backup(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->put(route('pengaturan.database.simpan'), [
            'backup_auto' => '1', 'backup_waktu' => '03:30', 'backup_retensi' => 7,
            'backup_lokasi' => 'custom', 'backup_path' => $this->dir,
        ])->assertRedirect();

        $this->assertSame('1', Pengaturan::get('backup_auto'));
        $this->assertSame('03:30', Pengaturan::get('backup_waktu'));

        $this->artisan('backup:run')->assertSuccessful();
        $this->assertCount(1, DatabaseBackup::daftar());
    }

    public function test_auto_backup_berjalan_sekali_per_hari_setelah_jam_diatur(): void
    {
        Pengaturan::set('backup_auto', '1', 'backup');
        Pengaturan::set('backup_waktu', '10:00', 'backup');

        $this->travelTo(now()->setTime(9, 0));
        DatabaseBackup::jalankanBilaJatuhTempo();
        $this->assertCount(0, DatabaseBackup::daftar());

        $this->travelTo(now()->setTime(10, 5));
        DatabaseBackup::jalankanBilaJatuhTempo();
        DatabaseBackup::jalankanBilaJatuhTempo();
        $this->assertCount(1, DatabaseBackup::daftar());
        $this->assertStringContainsString('|ok|', Pengaturan::get('backup_terakhir'));
    }

    public function test_auto_backup_nonaktif_tidak_membuat_file(): void
    {
        Pengaturan::set('backup_auto', '0', 'backup');
        Pengaturan::set('backup_waktu', '00:00', 'backup');

        DatabaseBackup::jalankanBilaJatuhTempo();

        $this->assertCount(0, DatabaseBackup::daftar());
    }

    public function test_restore_tanpa_login_hanya_saat_belum_ada_akun(): void
    {
        Anggota::factory()->create(['nama' => 'Dari Server Lama']);
        $path = DatabaseBackup::buat();
        Anggota::query()->delete();
        User::query()->delete();

        $file = new UploadedFile($path, 'b.ndjson.gz', null, null, true);
        $this->get(route('pemulihan'))->assertOk();
        $this->post(route('pemulihan.proses'), ['file' => $file])->assertRedirect(route('masuk'));
        $this->assertDatabaseHas('anggota', ['nama' => 'Dari Server Lama']);

        User::factory()->create();
        $this->get(route('pemulihan'))->assertNotFound();
        $this->post(route('pemulihan.proses'), ['file' => $file])->assertNotFound();
    }
}
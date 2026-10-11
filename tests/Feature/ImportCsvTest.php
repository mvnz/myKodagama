<?php

namespace Tests\Feature;

use App\Models\Koperasi\Anggota;
use App\Models\Koperasi\Simpanan;
use App\Models\Koperasi\UnitKerja;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ImportCsvTest extends TestCase
{
    use RefreshDatabase;

    private function csv(string $isi): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('data.csv', $isi);
    }

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    public function test_import_anggota_menerima_header_alias_dan_huruf_besar(): void
    {
        $isi = "no_anggota;Nama;NIK;Jenis Kelamin;tempat_lahir;tanggal_lahir;alamat;telepon;tanggal_bergabung;status;ket\n"
            . "ANG-77;Budi;3201010101010001;laki-laki;Bogor;1990-01-01;Jl A;812345;2020-01-01;Aktif;lama\n";

        $this->actingAs($this->admin())
            ->post(route('import.anggota'), ['file' => $this->csv($isi)])
            ->assertOk()
            ->assertViewHas('berhasil', fn ($b) => count($b) === 1)
            ->assertViewHas('error', []);

        $this->assertDatabaseHas('anggota', [
            'nomor_anggota' => 'ANG-77',
            'jenis_kelamin' => 'Laki-laki',
            'status' => 'aktif',
        ]);
    }

    public function test_import_anggota_tanpa_nomor_menjadi_error_dan_tidak_dibuat_otomatis(): void
    {
        $isi = "nomor_anggota;nama;nik;jenis_kelamin;tempat_lahir;tanggal_lahir;alamat;telepon;tanggal_bergabung\n"
            . ";Budi;3201010101010001;Laki-laki;Bogor;1990-01-01;Jl A;812345;2020-01-01\n";

        $this->actingAs($this->admin())
            ->post(route('import.anggota'), ['file' => $this->csv($isi)])
            ->assertViewHas('error', fn ($e) => count($e) === 1 && str_contains($e[0]['pesan'], 'Nomor anggota wajib'));

        $this->assertDatabaseCount('anggota', 0);
    }

    public function test_import_anggota_nomor_duplikat_dan_nik_duplikat(): void
    {
        Anggota::factory()->create(['nomor_anggota' => 'ANG-1', 'nik' => '3201010101010009']);

        $isi = "nomor_anggota;nama;nik;jenis_kelamin;tempat_lahir;tanggal_lahir;alamat;telepon;tanggal_bergabung\n"
            . "ANG-1;Budi;3201010101010001;Laki-laki;Bogor;1990-01-01;Jl A;812345;2020-01-01\n"
            . "ANG-2;Sari;3201010101010009;Perempuan;Bogor;1990-01-01;Jl A;812345;2020-01-01\n";

        $this->actingAs($this->admin())
            ->post(route('import.anggota'), ['file' => $this->csv($isi)])
            ->assertViewHas('error', fn ($e) => count($e) === 1 && $e[0]['baris'] === 2 && $e[0]['pesan'] === 'Nomor anggota sudah terdaftar.')
            ->assertViewHas('dilewati', fn ($d) => count($d) === 1 && $d[0]['baris'] === 3);
    }

    public function test_import_simpanan_menerima_alias_dan_jenis_huruf_besar(): void
    {
        $anggota = Anggota::factory()->create(['nomor_anggota' => 'ANG-5']);

        $isi = "no_anggota,jenis_simpanan,jumlah,tanggal\nANG-5,Pokok,50000,2024-01-01\nANG-X,wajib,50000,2024-01-01\n";

        $this->actingAs($this->admin())
            ->post(route('import.simpanan'), ['file' => $this->csv($isi)])
            ->assertViewHas('berhasil', fn ($b) => count($b) === 1)
            ->assertViewHas('dilewati', fn ($d) => count($d) === 1);

        $this->assertSame(1, Simpanan::where('anggota_id', $anggota->id)->where('jenis_simpanan', 'pokok')->count());
    }

    public function test_import_unit_kerja_dan_kode_duplikat_dilewati(): void
    {
        UnitKerja::factory()->create(['kode' => 'UK1']);

        $isi = "Kode,Nama,Singkatan\nUK1,Satu,S1\nUK2,Dua,S2\n";

        $this->actingAs($this->admin())
            ->post(route('import.unit-kerja'), ['file' => $this->csv($isi)])
            ->assertViewHas('berhasil', fn ($b) => count($b) === 1)
            ->assertViewHas('dilewati', fn ($d) => count($d) === 1);
    }

    public function test_export_error_csv_berisi_baris_error_dan_hanya_sekali_unduh(): void
    {
        $admin = $this->admin();
        $isi = "nomor_anggota;nama;nik;jenis_kelamin;tempat_lahir;tanggal_lahir;alamat;telepon;tanggal_bergabung\n"
            . ";Budi;123;Laki-laki;Bogor;1990-01-01;Jl A;812345;2020-01-01\n";

        $this->actingAs($admin)->post(route('import.anggota'), ['file' => $this->csv($isi)]);

        $res = $this->get(route('import.errors.csv', 'anggota'))->assertOk();
        $body = $res->streamedContent();
        $this->assertStringContainsString('Nomor Anggota', $body);
        $this->assertStringContainsString('NIK harus 16 digit.', $body);

        $this->get(route('import.errors.csv', 'anggota'))->assertNotFound();
    }

    public function test_export_error_csv_mengamankan_formula_injection(): void
    {
        $isi = "nomor_anggota;nama;nik;jenis_kelamin;tempat_lahir;tanggal_lahir;alamat;telepon;tanggal_bergabung\n"
            . "=CMD();Budi;123;Laki-laki;Bogor;1990-01-01;Jl A;812345;2020-01-01\n";

        $this->actingAs($this->admin())->post(route('import.anggota'), ['file' => $this->csv($isi)]);

        $body = $this->get(route('import.errors.csv', 'anggota'))->streamedContent();
        $this->assertStringContainsString("'=CMD()", $body);
    }
}
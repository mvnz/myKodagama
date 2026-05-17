<?php

namespace Tests\Feature\Pengaturan;

use App\Models\Pengaturan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PengaturanControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();

        // Seed pengaturan default
        $defaults = [
            ['kunci' => 'nama_koperasi',   'nilai' => 'Kodagama',                   'grup' => 'profil',    'label' => 'Nama Koperasi',        'tipe' => 'text'],
            ['kunci' => 'sub_nama',        'nilai' => 'Koperasi Simpan Pinjam',     'grup' => 'profil',    'label' => 'Sub Nama',             'tipe' => 'text'],
            ['kunci' => 'tagline',         'nilai' => 'Keuangan Lebih Baik',        'grup' => 'profil',    'label' => 'Tagline',              'tipe' => 'text'],
            ['kunci' => 'deskripsi',       'nilai' => 'Deskripsi koperasi.',        'grup' => 'profil',    'label' => 'Deskripsi',            'tipe' => 'textarea'],
            ['kunci' => 'tahun_berdiri',   'nilai' => '2005',                       'grup' => 'profil',    'label' => 'Tahun Berdiri',        'tipe' => 'text'],
            ['kunci' => 'slogan_resmi',    'nilai' => 'Terdaftar Kemenkop RI',      'grup' => 'profil',    'label' => 'Slogan Resmi',         'tipe' => 'text'],
            ['kunci' => 'telepon',         'nilai' => '(0274) 123-4567',            'grup' => 'kontak',    'label' => 'Telepon',              'tipe' => 'tel'],
            ['kunci' => 'email',           'nilai' => 'info@koperasi.id',           'grup' => 'kontak',    'label' => 'Email',                'tipe' => 'email'],
            ['kunci' => 'jam_operasional', 'nilai' => 'Senin–Jumat',               'grup' => 'kontak',    'label' => 'Jam Operasional',      'tipe' => 'text'],
            ['kunci' => 'alamat',          'nilai' => 'Jl. Koperasi No.1',         'grup' => 'kontak',    'label' => 'Alamat',               'tipe' => 'text'],
            ['kunci' => 'jumlah_anggota',  'nilai' => '2.500+',                    'grup' => 'statistik', 'label' => 'Jumlah Anggota',       'tipe' => 'text'],
            ['kunci' => 'bunga_pinjaman',  'nilai' => '1,5%',                      'grup' => 'statistik', 'label' => 'Bunga Pinjaman',       'tipe' => 'text'],
            ['kunci' => 'tahun_operasi',   'nilai' => '20+',                       'grup' => 'statistik', 'label' => 'Tahun Operasi',        'tipe' => 'text'],
            ['kunci' => 'nav_tentang_label', 'nilai' => 'Tentang',                 'grup' => 'navigasi',  'label' => 'Menu 1 Label',         'tipe' => 'text'],
            ['kunci' => 'nav_tentang_url',   'nilai' => '#',                       'grup' => 'navigasi',  'label' => 'Menu 1 URL',           'tipe' => 'text'],
            ['kunci' => 'nav_layanan_label', 'nilai' => 'Layanan',                 'grup' => 'navigasi',  'label' => 'Menu 2 Label',         'tipe' => 'text'],
            ['kunci' => 'nav_layanan_url',   'nilai' => '#',                       'grup' => 'navigasi',  'label' => 'Menu 2 URL',           'tipe' => 'text'],
            ['kunci' => 'nav_kontak_label',  'nilai' => 'Kontak',                  'grup' => 'navigasi',  'label' => 'Menu 3 Label',         'tipe' => 'text'],
            ['kunci' => 'nav_kontak_url',    'nilai' => '#',                       'grup' => 'navigasi',  'label' => 'Menu 3 URL',           'tipe' => 'text'],
        ];
        foreach ($defaults as $d) {
            Pengaturan::create($d);
        }
    }

    // ─── Beranda (publik) ─────────────────────────────────────────

    public function test_beranda_dapat_diakses_tanpa_login(): void
    {
        $response = $this->get(route('beranda'));

        $response->assertStatus(200);
        $response->assertSee('Kodagama');
    }

    public function test_beranda_menampilkan_data_dari_pengaturan(): void
    {
        Pengaturan::where('kunci', 'nama_koperasi')->update(['nilai' => 'KoperasiTest']);

        $response = $this->get(route('beranda'));

        $response->assertSee('KoperasiTest');
    }

    // ─── Index ───────────────────────────────────────────────────

    public function test_form_pengaturan_butuh_autentikasi(): void
    {
        $this->get(route('pengaturan.aplikasi'))->assertRedirect('/masuk');
    }

    public function test_form_pengaturan_dapat_diakses_admin(): void
    {
        $this->actingAs($this->admin)
            ->get(route('pengaturan.aplikasi'))
            ->assertStatus(200)
            ->assertSee('Pengaturan Aplikasi')
            ->assertSee('Profil Koperasi')
            ->assertSee('Informasi Kontak')
            ->assertSee('Statistik Beranda');
    }

    public function test_form_pengaturan_menampilkan_nilai_tersimpan(): void
    {
        $this->actingAs($this->admin)
            ->get(route('pengaturan.aplikasi'))
            ->assertSee('Kodagama')
            ->assertSee('(0274) 123-4567');
    }

    // ─── Update ──────────────────────────────────────────────────

    public function test_update_butuh_autentikasi(): void
    {
        $this->put(route('pengaturan.update'), [])->assertRedirect('/masuk');
    }

    public function test_update_berhasil_menyimpan_perubahan(): void
    {
        $response = $this->actingAs($this->admin)->put(route('pengaturan.update'), [
            'pengaturan' => [
                'nama_koperasi' => 'Koperasi Baru',
                'telepon'       => '(0274) 999-8888',
                'email'         => 'baru@koperasi.id',
            ],
        ]);

        $response->assertRedirect(route('pengaturan.aplikasi'));
        $response->assertSessionHas('sukses');

        $this->assertEquals('Koperasi Baru', Pengaturan::get('nama_koperasi'));
        $this->assertEquals('(0274) 999-8888', Pengaturan::get('telepon'));
        $this->assertEquals('baru@koperasi.id', Pengaturan::get('email'));
    }

    public function test_update_tidak_mengubah_kunci_yang_tidak_dikirim(): void
    {
        $this->actingAs($this->admin)->put(route('pengaturan.update'), [
            'pengaturan' => ['nama_koperasi' => 'Koperasi X'],
        ]);

        // Kunci lain tetap tidak berubah
        $this->assertEquals('Koperasi Simpan Pinjam', Pengaturan::get('sub_nama'));
    }

    public function test_update_label_dan_url_navigasi(): void
    {
        $response = $this->actingAs($this->admin)->put(route('pengaturan.update'), [
            'pengaturan' => [
                'nav_tentang_label' => 'Profil Kami',
                'nav_tentang_url'   => '/tentang',
                'nav_layanan_label' => 'Produk',
                'nav_layanan_url'   => '/produk',
            ],
        ]);

        $response->assertRedirect(route('pengaturan.aplikasi'));
        $this->assertEquals('Profil Kami', Pengaturan::get('nav_tentang_label'));
        $this->assertEquals('/tentang', Pengaturan::get('nav_tentang_url'));
        $this->assertEquals('Produk', Pengaturan::get('nav_layanan_label'));
    }

    public function test_beranda_menampilkan_label_navigasi_yang_diubah(): void
    {
        Pengaturan::where('kunci', 'nav_tentang_label')->update(['nilai' => 'Profil']);
        Pengaturan::where('kunci', 'nav_tentang_url')->update(['nilai' => '/tentang']);

        $response = $this->get(route('beranda'));

        $response->assertSee('Profil');
        $response->assertSee('/tentang');
    }

    public function test_update_menolak_nilai_terlalu_panjang(): void
    {
        $response = $this->actingAs($this->admin)->put(route('pengaturan.update'), [
            'pengaturan' => [
                'nama_koperasi' => str_repeat('a', 501),
            ],
        ]);

        $response->assertSessionHasErrors('pengaturan.nama_koperasi');
    }

    public function test_update_nilai_boleh_kosong(): void
    {
        $response = $this->actingAs($this->admin)->put(route('pengaturan.update'), [
            'pengaturan' => ['telepon' => ''],
        ]);

        $response->assertRedirect(route('pengaturan.aplikasi'));
        $this->assertNull(Pengaturan::get('telepon'));
    }

    // ─── Helper Model ────────────────────────────────────────────

    public function test_get_mengembalikan_default_jika_kunci_tidak_ada(): void
    {
        $this->assertEquals('default', Pengaturan::get('kunci_tidak_ada', 'default'));
    }

    public function test_semua_nilai_mengembalikan_array(): void
    {
        $nilai = Pengaturan::semuaNilai();

        $this->assertIsArray($nilai);
        $this->assertArrayHasKey('nama_koperasi', $nilai);
        $this->assertArrayHasKey('telepon', $nilai);
    }
}

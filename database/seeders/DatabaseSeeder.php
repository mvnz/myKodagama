<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Anggota;
use App\Models\Simpanan;
use App\Models\Pinjaman;
use App\Models\Angsuran;
use App\Models\Pengaturan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Permissions ───────────────────────────────────────────
        $permissions = [
            // Dashboard
            ['nama' => 'Lihat Dashboard',     'slug' => 'dashboard.lihat',    'grup' => 'Dashboard'],

            // Anggota
            ['nama' => 'Lihat Data Anggota',  'slug' => 'anggota.lihat',      'grup' => 'Anggota'],
            ['nama' => 'Tambah Anggota',       'slug' => 'anggota.tambah',     'grup' => 'Anggota'],
            ['nama' => 'Edit Anggota',         'slug' => 'anggota.edit',       'grup' => 'Anggota'],
            ['nama' => 'Hapus Anggota',        'slug' => 'anggota.hapus',      'grup' => 'Anggota'],

            // Simpanan
            ['nama' => 'Lihat Simpanan',       'slug' => 'simpanan.lihat',     'grup' => 'Simpanan'],
            ['nama' => 'Tambah Simpanan',      'slug' => 'simpanan.tambah',    'grup' => 'Simpanan'],
            ['nama' => 'Edit Simpanan',        'slug' => 'simpanan.edit',      'grup' => 'Simpanan'],
            ['nama' => 'Hapus Simpanan',       'slug' => 'simpanan.hapus',     'grup' => 'Simpanan'],

            // Pinjaman
            ['nama' => 'Lihat Pinjaman',       'slug' => 'pinjaman.lihat',       'grup' => 'Pinjaman'],
            ['nama' => 'Ajukan Pinjaman',      'slug' => 'pinjaman.tambah',      'grup' => 'Pinjaman'],
            ['nama' => 'Edit Pinjaman',        'slug' => 'pinjaman.edit',        'grup' => 'Pinjaman'],
            ['nama' => 'Hapus Pinjaman',       'slug' => 'pinjaman.hapus',       'grup' => 'Pinjaman'],
            ['nama' => 'Verifikasi Pinjaman',  'slug' => 'pinjaman.verifikasi',  'grup' => 'Pinjaman'],
            ['nama' => 'Setujui Pinjaman',     'slug' => 'pinjaman.setujui',     'grup' => 'Pinjaman'],

            // Angsuran
            ['nama' => 'Lihat Angsuran',       'slug' => 'angsuran.lihat',     'grup' => 'Angsuran'],
            ['nama' => 'Bayar Angsuran',       'slug' => 'angsuran.bayar',     'grup' => 'Angsuran'],

            // Pengguna
            ['nama' => 'Lihat Pengguna',       'slug' => 'pengguna.lihat',     'grup' => 'Pengguna'],
            ['nama' => 'Tambah Pengguna',      'slug' => 'pengguna.tambah',    'grup' => 'Pengguna'],
            ['nama' => 'Edit Pengguna',        'slug' => 'pengguna.edit',      'grup' => 'Pengguna'],
            ['nama' => 'Hapus Pengguna',       'slug' => 'pengguna.hapus',     'grup' => 'Pengguna'],

            // Pengaturan
            ['nama' => 'Kelola Role & Hak Akses', 'slug' => 'role.kelola',          'grup' => 'Pengaturan'],
            ['nama' => 'Kelola Pengaturan Aplikasi', 'slug' => 'pengaturan.aplikasi', 'grup' => 'Pengaturan'],
        ];

        $createdPermissions = [];
        foreach ($permissions as $p) {
            $createdPermissions[$p['slug']] = Permission::create($p);
        }

        // ─── Roles ─────────────────────────────────────────────────
        $roleAdmin = Role::create([
            'nama'      => 'Administrator',
            'slug'      => 'admin',
            'deskripsi' => 'Akses penuh ke seluruh fitur sistem',
        ]);
        $roleAdmin->permissions()->sync(array_column($createdPermissions, 'id'));

        $rolePetugas = Role::create([
            'nama'      => 'Petugas',
            'slug'      => 'petugas',
            'deskripsi' => 'Akses operasional harian koperasi',
        ]);
        $slugPetugas = [
            'dashboard.lihat',
            'anggota.lihat', 'anggota.tambah', 'anggota.edit',
            'simpanan.lihat', 'simpanan.tambah', 'simpanan.edit',
            'pinjaman.lihat', 'pinjaman.tambah', 'pinjaman.edit',
            'angsuran.lihat', 'angsuran.bayar',
        ];
        $rolePetugas->permissions()->sync(
            collect($slugPetugas)->map(fn($s) => $createdPermissions[$s]->id)->toArray()
        );

        // ─── Users ─────────────────────────────────────────────────
        // Admin user
        $admin = User::create([
            'name'     => 'Administrator',
            'email'    => 'admin@kodagama.id',
            'password' => Hash::make('password'),
            'role'     => 'admin',
            'role_id'  => $roleAdmin->id,
        ]);

        // Petugas
        $petugas = User::create([
            'name'     => 'Petugas Koperasi',
            'email'    => 'petugas@kodagama.id',
            'password' => Hash::make('password'),
            'role'     => 'petugas',
            'role_id'  => $rolePetugas->id,
        ]);

        // Sample anggota
        $anggotaData = [
            ['nama'=>'Budi Santoso','nik'=>'3471010101800001','tempat_lahir'=>'Yogyakarta','tanggal_lahir'=>'1980-01-01','jenis_kelamin'=>'Laki-laki','alamat'=>'Jl. Mawar No.1, Yogyakarta','telepon'=>'081234567801','pekerjaan'=>'PNS','email'=>'budi@example.com'],
            ['nama'=>'Siti Rahayu','nik'=>'3471010101850002','tempat_lahir'=>'Solo','tanggal_lahir'=>'1985-05-15','jenis_kelamin'=>'Perempuan','alamat'=>'Jl. Melati No.2, Yogyakarta','telepon'=>'081234567802','pekerjaan'=>'Guru','email'=>'siti@example.com'],
            ['nama'=>'Agus Widodo','nik'=>'3471010101790003','tempat_lahir'=>'Semarang','tanggal_lahir'=>'1979-03-20','jenis_kelamin'=>'Laki-laki','alamat'=>'Jl. Anggrek No.3, Yogyakarta','telepon'=>'081234567803','pekerjaan'=>'Wiraswasta','email'=>null],
            ['nama'=>'Dwi Lestari','nik'=>'3471010101900004','tempat_lahir'=>'Bantul','tanggal_lahir'=>'1990-11-11','jenis_kelamin'=>'Perempuan','alamat'=>'Jl. Kenanga No.4, Bantul','telepon'=>'081234567804','pekerjaan'=>'Pedagang','email'=>'dwi@example.com'],
            ['nama'=>'Eko Prasetyo','nik'=>'3471010101820005','tempat_lahir'=>'Kulon Progo','tanggal_lahir'=>'1982-07-07','jenis_kelamin'=>'Laki-laki','alamat'=>'Jl. Dahlia No.5, Kulon Progo','telepon'=>'081234567805','pekerjaan'=>'Petani','email'=>null],
        ];

        $nomor = 1001;
        $anggotaList = [];
        foreach ($anggotaData as $data) {
            $anggota = Anggota::create(array_merge($data, [
                'nomor_anggota'    => 'KOP-' . date('Ym') . '-' . str_pad($nomor++, 4, '0', STR_PAD_LEFT),
                'status'           => 'aktif',
                'tanggal_bergabung'=> now()->subYears(rand(1,5))->format('Y-m-d'),
            ]));
            $anggotaList[] = $anggota;

            // Simpanan pokok
            Simpanan::create([
                'anggota_id'     => $anggota->id,
                'jenis_simpanan' => 'pokok',
                'jumlah'         => 100000,
                'tanggal'        => $anggota->tanggal_bergabung,
                'keterangan'     => 'Simpanan pokok saat bergabung',
                'user_id'        => $admin->id,
            ]);

            // Simpanan wajib beberapa bulan
            for ($i = 1; $i <= rand(3,8); $i++) {
                Simpanan::create([
                    'anggota_id'     => $anggota->id,
                    'jenis_simpanan' => 'wajib',
                    'jumlah'         => 50000,
                    'tanggal'        => now()->subMonths($i)->format('Y-m-d'),
                    'keterangan'     => 'Simpanan wajib bulan ' . now()->subMonths($i)->format('F Y'),
                    'user_id'        => $petugas->id,
                ]);
            }
        }

        // Buat pinjaman untuk 2 anggota pertama
        foreach (array_slice($anggotaList, 0, 2) as $idx => $anggota) {
            $jumlah   = ($idx + 1) * 3000000;
            $jangka   = 12;
            $bunga    = 1.5;
            $bungaBln = $jumlah * ($bunga / 100);
            $cicilan  = round(($jumlah / $jangka) + $bungaBln);

            $pinjaman = Pinjaman::create([
                'nomor_pinjaman'      => 'PIN-' . date('Ym') . '-' . str_pad(1001 + $idx, 4, '0', STR_PAD_LEFT),
                'anggota_id'          => $anggota->id,
                'jumlah_pinjaman'     => $jumlah,
                'jangka_waktu'        => $jangka,
                'bunga'               => $bunga,
                'cicilan_per_bulan'   => $cicilan,
                'tujuan'              => 'Modal usaha dan kebutuhan rumah tangga',
                'status'              => 'disetujui',
                'tanggal_pengajuan'   => now()->subMonths(3)->format('Y-m-d'),
                'tanggal_persetujuan' => now()->subMonths(3)->format('Y-m-d'),
                'user_id'             => $admin->id,
            ]);

            // Buat jadwal angsuran
            $pokok = round($jumlah / $jangka);
            for ($ke = 1; $ke <= $jangka; $ke++) {
                $jatuhTempo = now()->subMonths(3)->addMonths($ke);
                $sudah      = $ke <= 3; // 3 angsuran sudah dibayar

                Angsuran::create([
                    'pinjaman_id'       => $pinjaman->id,
                    'ke'                => $ke,
                    'jumlah_angsuran'   => $cicilan,
                    'pokok'             => $pokok,
                    'bunga'             => round($bungaBln),
                    'tanggal_jatuh_tempo' => $jatuhTempo->format('Y-m-d'),
                    'tanggal_bayar'     => $sudah ? $jatuhTempo->format('Y-m-d') : null,
                    'status'            => $sudah ? 'sudah' : 'belum',
                ]);
            }
        }

        // Satu pinjaman dengan status diajukan
        Pinjaman::create([
            'nomor_pinjaman'    => 'PIN-' . date('Ym') . '-1003',
            'anggota_id'        => $anggotaList[2]->id,
            'jumlah_pinjaman'   => 5000000,
            'jangka_waktu'      => 6,
            'bunga'             => 1.5,
            'cicilan_per_bulan' => null,
            'tujuan'            => 'Renovasi rumah',
            'status'            => 'diajukan',
            'tanggal_pengajuan' => now()->format('Y-m-d'),
            'user_id'           => $petugas->id,
        ]);

        // ─── Pengaturan Aplikasi ────────────────────────────────────
        $defaultPengaturan = [
            // Profil
            ['kunci' => 'nama_koperasi',   'nilai' => 'Koperasi ABC',                                   'grup' => 'profil',    'label' => 'Nama Koperasi',           'tipe' => 'text'],
            ['kunci' => 'sub_nama',        'nilai' => 'Koperasi Simpan Pinjam',                     'grup' => 'profil',    'label' => 'Sub Nama / Jenis',        'tipe' => 'text'],
            ['kunci' => 'tagline',         'nilai' => 'Keuangan Lebih Baik Bersama Koperasi Terpercaya', 'grup' => 'profil', 'label' => 'Tagline',              'tipe' => 'text'],
            ['kunci' => 'deskripsi',       'nilai' => 'Layanan simpan pinjam yang mudah, transparan, dan berbunga rendah untuk seluruh anggota. Kami hadir untuk mewujudkan kesejahteraan bersama.', 'grup' => 'profil', 'label' => 'Deskripsi Singkat', 'tipe' => 'textarea'],
            ['kunci' => 'tahun_berdiri',   'nilai' => '2026',                                       'grup' => 'profil',    'label' => 'Tahun Berdiri',           'tipe' => 'text'],
            ['kunci' => 'slogan_resmi',    'nilai' => 'Terdaftar Kementerian Koperasi RI',           'grup' => 'profil',    'label' => 'Slogan Resmi',            'tipe' => 'text'],
            // Kontak
            ['kunci' => 'telepon',         'nilai' => '(0274) 123-4567',                            'grup' => 'kontak',    'label' => 'Nomor Telepon',           'tipe' => 'tel'],
            ['kunci' => 'email',           'nilai' => 'info@koperasiabc.id',                          'grup' => 'kontak',    'label' => 'Email',                   'tipe' => 'email'],
            ['kunci' => 'jam_operasional', 'nilai' => 'Senin–Jumat, 08.00–16.00',                   'grup' => 'kontak',    'label' => 'Jam Operasional',         'tipe' => 'text'],
            ['kunci' => 'alamat',          'nilai' => 'Jl. Koperasi No. 1, Jakarta',             'grup' => 'kontak',    'label' => 'Alamat',                  'tipe' => 'text'],
            // Statistik
            ['kunci' => 'jumlah_anggota',  'nilai' => '2.500+',                                     'grup' => 'statistik', 'label' => 'Jumlah Anggota Aktif',    'tipe' => 'text'],
            ['kunci' => 'bunga_pinjaman',  'nilai' => '1,5%',                                       'grup' => 'statistik', 'label' => 'Bunga Pinjaman/Bulan',    'tipe' => 'text'],
            ['kunci' => 'tahun_operasi',   'nilai' => '20+',                                        'grup' => 'statistik', 'label' => 'Tahun Beroperasi',        'tipe' => 'text'],
            // Navigasi
            ['kunci' => 'nav_tentang_label', 'nilai' => 'Tentang',  'grup' => 'navigasi', 'label' => 'Menu 1 Label', 'tipe' => 'text'],
            ['kunci' => 'nav_tentang_url',   'nilai' => '#',         'grup' => 'navigasi', 'label' => 'Menu 1 URL',   'tipe' => 'text'],
            ['kunci' => 'nav_layanan_label', 'nilai' => 'Layanan',  'grup' => 'navigasi', 'label' => 'Menu 2 Label', 'tipe' => 'text'],
            ['kunci' => 'nav_layanan_url',   'nilai' => '#',         'grup' => 'navigasi', 'label' => 'Menu 2 URL',   'tipe' => 'text'],
            ['kunci' => 'nav_kontak_label',  'nilai' => 'Kontak',   'grup' => 'navigasi', 'label' => 'Menu 3 Label', 'tipe' => 'text'],
            ['kunci' => 'nav_kontak_url',    'nilai' => '#',         'grup' => 'navigasi', 'label' => 'Menu 3 URL',   'tipe' => 'text'],
        ];

        foreach ($defaultPengaturan as $p) {
            Pengaturan::create($p);
        }
    }
}

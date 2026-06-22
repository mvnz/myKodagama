<?php

namespace Database\Seeders;

use App\Models\Pos\Kategori;
use App\Models\Pos\Produk;
use App\Models\Pos\Transaksi;
use App\Models\Pos\Item;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class PosSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Kategori ─────────────────────────────────────────────
        $kategoris = [
            ['nama' => 'Minuman',      'urutan' => 1],
            ['nama' => 'Makanan',      'urutan' => 2],
            ['nama' => 'Snack',        'urutan' => 3],
            ['nama' => 'Sembako',      'urutan' => 4],
            ['nama' => 'Alat Tulis',   'urutan' => 5],
            ['nama' => 'Peralatan',    'urutan' => 6],
        ];

        foreach ($kategoris as $k) {
            Kategori::firstOrCreate(['nama' => $k['nama']], $k);
        }

        $kat = Kategori::pluck('id', 'nama');

        // ─── Produk ───────────────────────────────────────────────
        $produkList = [
            // Minuman
            ['kode' => 'PRD-0001', 'nama' => 'Air Mineral 600ml',    'kategori' => 'Minuman',    'harga_beli' => 2500,  'harga_jual' => 3000,  'stok' => 100, 'satuan' => 'pcs'],
            ['kode' => 'PRD-0002', 'nama' => 'Air Mineral 1500ml',   'kategori' => 'Minuman',    'harga_beli' => 4000,  'harga_jual' => 5000,  'stok' => 80,  'satuan' => 'pcs'],
            ['kode' => 'PRD-0003', 'nama' => 'Teh Botol Sosro',      'kategori' => 'Minuman',    'harga_beli' => 4500,  'harga_jual' => 5500,  'stok' => 60,  'satuan' => 'pcs'],
            ['kode' => 'PRD-0004', 'nama' => 'Kopi Sachet',          'kategori' => 'Minuman',    'harga_beli' => 1500,  'harga_jual' => 2000,  'stok' => 200, 'satuan' => 'pcs'],
            ['kode' => 'PRD-0005', 'nama' => 'Juice Kemasan',        'kategori' => 'Minuman',    'harga_beli' => 3500,  'harga_jual' => 4500,  'stok' => 50,  'satuan' => 'pcs'],

            // Makanan
            ['kode' => 'PRD-0006', 'nama' => 'Mie Instan Goreng',    'kategori' => 'Makanan',    'harga_beli' => 2800,  'harga_jual' => 3500,  'stok' => 150, 'satuan' => 'pcs'],
            ['kode' => 'PRD-0007', 'nama' => 'Mie Instan Kuah',      'kategori' => 'Makanan',    'harga_beli' => 2800,  'harga_jual' => 3500,  'stok' => 150, 'satuan' => 'pcs'],
            ['kode' => 'PRD-0008', 'nama' => 'Sarden Kaleng',        'kategori' => 'Makanan',    'harga_beli' => 8500,  'harga_jual' => 10000, 'stok' => 40,  'satuan' => 'pcs'],
            ['kode' => 'PRD-0009', 'nama' => 'Kecap Manis 135ml',    'kategori' => 'Makanan',    'harga_beli' => 5500,  'harga_jual' => 7000,  'stok' => 30,  'satuan' => 'pcs'],

            // Snack
            ['kode' => 'PRD-0010', 'nama' => 'Keripik Singkong',     'kategori' => 'Snack',      'harga_beli' => 3000,  'harga_jual' => 4000,  'stok' => 90,  'satuan' => 'pcs'],
            ['kode' => 'PRD-0011', 'nama' => 'Biskuit Marie',        'kategori' => 'Snack',      'harga_beli' => 4500,  'harga_jual' => 6000,  'stok' => 70,  'satuan' => 'pcs'],
            ['kode' => 'PRD-0012', 'nama' => 'Wafer Cokelat',        'kategori' => 'Snack',      'harga_beli' => 5000,  'harga_jual' => 6500,  'stok' => 60,  'satuan' => 'pcs'],
            ['kode' => 'PRD-0013', 'nama' => 'Permen Mint',          'kategori' => 'Snack',      'harga_beli' => 1000,  'harga_jual' => 1500,  'stok' => 200, 'satuan' => 'pcs'],

            // Sembako
            ['kode' => 'PRD-0014', 'nama' => 'Beras 1kg',            'kategori' => 'Sembako',    'harga_beli' => 13000, 'harga_jual' => 15000, 'stok' => 50,  'satuan' => 'kg'],
            ['kode' => 'PRD-0015', 'nama' => 'Gula Pasir 1kg',       'kategori' => 'Sembako',    'harga_beli' => 14000, 'harga_jual' => 16000, 'stok' => 40,  'satuan' => 'kg'],
            ['kode' => 'PRD-0016', 'nama' => 'Minyak Goreng 1L',     'kategori' => 'Sembako',    'harga_beli' => 14500, 'harga_jual' => 17000, 'stok' => 35,  'satuan' => 'ltr'],
            ['kode' => 'PRD-0017', 'nama' => 'Tepung Terigu 1kg',    'kategori' => 'Sembako',    'harga_beli' => 9000,  'harga_jual' => 11000, 'stok' => 30,  'satuan' => 'kg'],
            ['kode' => 'PRD-0018', 'nama' => 'Telur Ayam (10 pcs)',  'kategori' => 'Sembako',    'harga_beli' => 22000, 'harga_jual' => 26000, 'stok' => 25,  'satuan' => 'pack'],

            // Alat Tulis
            ['kode' => 'PRD-0019', 'nama' => 'Pulpen Biru',          'kategori' => 'Alat Tulis', 'harga_beli' => 1500,  'harga_jual' => 2500,  'stok' => 100, 'satuan' => 'pcs'],
            ['kode' => 'PRD-0020', 'nama' => 'Buku Tulis 40lbr',     'kategori' => 'Alat Tulis', 'harga_beli' => 2500,  'harga_jual' => 3500,  'stok' => 80,  'satuan' => 'pcs'],
            ['kode' => 'PRD-0021', 'nama' => 'Pensil HB',            'kategori' => 'Alat Tulis', 'harga_beli' => 1000,  'harga_jual' => 1500,  'stok' => 120, 'satuan' => 'pcs'],
            ['kode' => 'PRD-0022', 'nama' => 'Penghapus',            'kategori' => 'Alat Tulis', 'harga_beli' => 700,   'harga_jual' => 1000,  'stok' => 150, 'satuan' => 'pcs'],

            // Peralatan
            ['kode' => 'PRD-0023', 'nama' => 'Sabun Mandi Batang',   'kategori' => 'Peralatan',  'harga_beli' => 2500,  'harga_jual' => 3500,  'stok' => 60,  'satuan' => 'pcs'],
            ['kode' => 'PRD-0024', 'nama' => 'Shampo Sachet',        'kategori' => 'Peralatan',  'harga_beli' => 700,   'harga_jual' => 1000,  'stok' => 200, 'satuan' => 'pcs'],
            ['kode' => 'PRD-0025', 'nama' => 'Pasta Gigi 75ml',      'kategori' => 'Peralatan',  'harga_beli' => 8000,  'harga_jual' => 10000, 'stok' => 40,  'satuan' => 'pcs'],
        ];

        foreach ($produkList as $p) {
            Produk::firstOrCreate(
                ['kode' => $p['kode']],
                [
                    'nama'        => $p['nama'],
                    'kategori_id' => $kat[$p['kategori']] ?? null,
                    'harga_beli'  => $p['harga_beli'],
                    'harga_jual'  => $p['harga_jual'],
                    'stok'        => $p['stok'],
                    'satuan'      => $p['satuan'],
                    'is_aktif'    => true,
                ]
            );
        }

        // ─── Transaksi dummy (30 hari terakhir) ───────────────────
        $kasir = User::whereHas('roleModel', fn($q) => $q->whereIn('slug', ['admin', 'petugas']))
            ->first();

        if (! $kasir) {
            $kasir = User::first();
        }

        if (! $kasir) {
            return; // tidak ada user, skip transaksi
        }

        $produkAll = Produk::where('is_aktif', true)->get();
        $metode    = ['tunai', 'tunai', 'tunai', 'qris', 'transfer'];
        $jumlahTrx = 60; // ~2 transaksi per hari selama 30 hari

        for ($i = 0; $i < $jumlahTrx; $i++) {
            $tanggal = Carbon::now()->subDays(rand(0, 29))->format('Y-m-d');
            $prefix  = 'TRX-' . str_replace('-', '', $tanggal) . '-';

            // Hitung nomor urut untuk tanggal ini
            $existing = Transaksi::where('nomor', 'like', $prefix . '%')
                ->selectRaw('MAX(CAST(SUBSTRING(nomor, ' . (strlen($prefix) + 1) . ') AS UNSIGNED)) as maks')
                ->value('maks');
            $nomor = $prefix . str_pad(($existing ?? 0) + 1, 4, '0', STR_PAD_LEFT);

            // Pilih 1-4 produk acak
            $pilihanProduk = $produkAll->random(rand(1, 4));
            $items = [];
            $subtotal = 0;

            foreach ($pilihanProduk as $produk) {
                $qty      = rand(1, 5);
                $harga    = $produk->harga_jual;
                $itemSub  = $harga * $qty;
                $subtotal += $itemSub;

                $items[] = [
                    'produk_id'    => $produk->id,
                    'nama_produk'  => $produk->nama,
                    'kode_produk'  => $produk->kode,
                    'harga'        => $harga,
                    'qty'          => $qty,
                    'subtotal'     => $itemSub,
                ];
            }

            $diskon = rand(0, 1) ? rand(1, 3) * 1000 : 0;
            $total  = $subtotal - $diskon;
            $bayar  = $total + (rand(0, 1) ? rand(0, 5) * 1000 : 0);

            $trx = Transaksi::create([
                'nomor'       => $nomor,
                'kasir_id'    => $kasir->id,
                'subtotal'    => $subtotal,
                'diskon'      => $diskon,
                'total'       => $total,
                'bayar'       => $bayar,
                'kembalian'   => $bayar - $total,
                'metode_bayar'=> $metode[array_rand($metode)],
                'tanggal'     => $tanggal,
            ]);

            foreach ($items as $item) {
                $item['transaksi_id'] = $trx->id;
                Item::create($item);
            }
        }
    }
}

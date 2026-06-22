<?php

namespace Database\Seeders;

use App\Models\Pos\Toko;
use App\Models\Pos\Produk;
use App\Models\Pos\SaldoLog;
use App\Models\Pos\Transaksi;
use App\Models\Pos\Item;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TokoSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Hapus toko lama (single-toko dari versi sebelumnya) ──
        // Hanya hapus jika saldo 0 dan belum punya produk terhubung
        Toko::where('saldo', 0)->whereDoesntHave('produk')->delete();

        // ─── Buat Supplier ────────────────────────────────────────
        $suppliers = [
            [
                'nama'      => 'CV Sumber Berkah',
                'pemilik'   => 'Pak Hendra',
                'telepon'   => '0812-3456-7890',
                'email'     => 'sumberberkah@gmail.com',
                'alamat'    => 'Jl. Raya Magelang No. 45',
                'kota'      => 'Magelang',
                'deskripsi' => 'Supplier minuman dan makanan kemasan',
                'saldo'     => 0,
            ],
            [
                'nama'      => 'Toko Maju Jaya',
                'pemilik'   => 'Bu Siti Rahayu',
                'telepon'   => '0856-7890-1234',
                'email'     => 'majujaya@yahoo.com',
                'alamat'    => 'Pasar Induk Blok C No. 12',
                'kota'      => 'Yogyakarta',
                'deskripsi' => 'Supplier sembako dan kebutuhan dapur',
                'saldo'     => 0,
            ],
            [
                'nama'      => 'UD Karya Mandiri',
                'pemilik'   => 'Mas Budi Santoso',
                'telepon'   => '0878-5432-1098',
                'email'     => 'karyamandiri@gmail.com',
                'alamat'    => 'Jl. Pemuda No. 88',
                'kota'      => 'Purworejo',
                'deskripsi' => 'Supplier alat tulis dan snack',
                'saldo'     => 0,
            ],
            [
                'nama'      => 'PT Griya Niaga',
                'pemilik'   => 'Ibu Dewi Lestari',
                'telepon'   => '0811-2233-4455',
                'email'     => 'griyaniaga@gmail.com',
                'alamat'    => 'Jl. Sudirman No. 15A',
                'kota'      => 'Kebumen',
                'deskripsi' => 'Supplier peralatan rumah tangga dan kebersihan',
                'saldo'     => 0,
            ],
        ];

        $createdSuppliers = [];
        foreach ($suppliers as $s) {
            $createdSuppliers[] = Toko::firstOrCreate(['nama' => $s['nama']], $s);
        }

        // ─── Mapping produk ke supplier ───────────────────────────
        // CV Sumber Berkah → Minuman + Makanan kemasan
        $supBerkah  = $createdSuppliers[0];
        // Toko Maju Jaya → Sembako
        $supMaju    = $createdSuppliers[1];
        // UD Karya Mandiri → Snack + Alat Tulis
        $supKarya   = $createdSuppliers[2];
        // PT Griya Niaga → Peralatan
        $supGriya   = $createdSuppliers[3];

        $mapping = [
            // Minuman → CV Sumber Berkah
            'PRD-0001' => $supBerkah->id,
            'PRD-0002' => $supBerkah->id,
            'PRD-0003' => $supBerkah->id,
            'PRD-0004' => $supBerkah->id,
            'PRD-0005' => $supBerkah->id,
            // Makanan → CV Sumber Berkah
            'PRD-0006' => $supBerkah->id,
            'PRD-0007' => $supBerkah->id,
            'PRD-0008' => $supBerkah->id,
            'PRD-0009' => $supBerkah->id,
            // Snack → UD Karya Mandiri
            'PRD-0010' => $supKarya->id,
            'PRD-0011' => $supKarya->id,
            'PRD-0012' => $supKarya->id,
            'PRD-0013' => $supKarya->id,
            // Sembako → Toko Maju Jaya
            'PRD-0014' => $supMaju->id,
            'PRD-0015' => $supMaju->id,
            'PRD-0016' => $supMaju->id,
            'PRD-0017' => $supMaju->id,
            'PRD-0018' => $supMaju->id,
            // Alat Tulis → UD Karya Mandiri
            'PRD-0019' => $supKarya->id,
            'PRD-0020' => $supKarya->id,
            'PRD-0021' => $supKarya->id,
            'PRD-0022' => $supKarya->id,
            // Peralatan → PT Griya Niaga
            'PRD-0023' => $supGriya->id,
            'PRD-0024' => $supGriya->id,
            'PRD-0025' => $supGriya->id,
        ];

        foreach ($mapping as $kode => $supplierId) {
            Produk::where('kode', $kode)->update(['supplier_id' => $supplierId]);
        }

        // ─── Backfill harga_pokok di pos_item yang masih 0 ───────
        // Update langsung via JOIN di database
        DB::statement('
            UPDATE pos_item pi
            JOIN pos_produk pp ON pp.id = pi.produk_id
            SET pi.harga_pokok = pp.harga_beli
            WHERE (pi.harga_pokok IS NULL OR pi.harga_pokok = 0)
              AND pp.harga_beli > 0
        ');

        // ─── Hitung ulang saldo supplier dari transaksi yang ada ──
        // Ambil semua item yang terjual, group by supplier, sum harga_pokok
        $items = Item::with('produk:id,supplier_id,harga_beli')
            ->whereHas('transaksi')
            ->get();

        // Hitung total harga_pokok per supplier dari item transaksi
        $saldoPerSupplier = [];
        foreach ($items as $item) {
            $supId = $item->produk?->supplier_id;
            if (! $supId) continue;
            $hp = $item->harga_pokok ?: ($item->produk->harga_beli ?? 0);
            $saldoPerSupplier[$supId] = ($saldoPerSupplier[$supId] ?? 0) + ($hp * $item->qty);
        }

        // Update saldo supplier
        foreach ($saldoPerSupplier as $supId => $total) {
            Toko::where('id', $supId)->update(['saldo' => $total]);
        }

        // ─── Buat SaldoLog dari transaksi yang ada ────────────────
        // Hapus log lama dulu supaya tidak duplikat
        SaldoLog::truncate();

        $transaksis = Transaksi::with([
            'items' => fn($q) => $q->with('produk:id,supplier_id,harga_beli'),
        ])->orderBy('created_at')->get();

        // Track saldo berjalan per supplier
        $saldoBerjalan = [];

        foreach ($transaksis as $trx) {
            // Group item by supplier
            $grouped = [];
            foreach ($trx->items as $item) {
                $supId = $item->produk?->supplier_id;
                if (! $supId) continue;
                $hp = $item->harga_pokok ?: ($item->produk->harga_beli ?? 0);
                $grouped[$supId] = ($grouped[$supId] ?? 0) + ($hp * $item->qty);
            }

            foreach ($grouped as $supId => $jumlah) {
                $sebelum = $saldoBerjalan[$supId] ?? 0;
                $sesudah = $sebelum + $jumlah;
                $saldoBerjalan[$supId] = $sesudah;

                DB::table('pos_saldo_log')->insert([
                    'toko_id'       => $supId,
                    'transaksi_id'  => $trx->id,
                    'jumlah'        => $jumlah,
                    'saldo_sebelum' => $sebelum,
                    'saldo_sesudah' => $sesudah,
                    'keterangan'    => "Penjualan #{$trx->nomor}",
                    'created_at'    => $trx->created_at,
                ]);
            }
        }

        // Sinkronkan saldo final ke tabel pos_toko
        foreach ($saldoBerjalan as $supId => $saldoFinal) {
            Toko::where('id', $supId)->update(['saldo' => $saldoFinal]);
        }

        $this->command->info('TokoSeeder: ' . count($createdSuppliers) . ' supplier dibuat, produk ditautkan, saldo log diisi.');
    }
}

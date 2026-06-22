<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Struk {{ $transaksi->nomor }}</title>
  
  <link rel="stylesheet" href="{{ asset('css/pages/inline-extracted.css') }}">
</head>
<body>
<div class="struk">
  <div class="text-center bold is-0cf08eb5">{{ config('app.name') }}</div>
  <div class="text-center is-bcf88ef7">Point of Sale</div>
  <div class="separator"></div>

  <div class="row"><span>No. Transaksi</span><span class="bold">{{ $transaksi->nomor }}</span></div>
  <div class="row"><span>Tanggal</span><span>{{ $transaksi->tanggal->format('d/m/Y') }}</span></div>
  <div class="row"><span>Waktu</span><span>{{ $transaksi->created_at->format('H:i:s') }}</span></div>
  <div class="row"><span>Kasir</span><span>{{ $transaksi->kasir?->name }}</span></div>
  <div class="separator"></div>

  @foreach($transaksi->items as $item)
  <div class="item-name">{{ $item->nama_produk }}</div>
  <div class="row">
    <span>&nbsp;&nbsp;{{ $item->qty }} × {{ number_format($item->harga,0,',','.') }}</span>
    <span>{{ number_format($item->subtotal,0,',','.') }}</span>
  </div>
  @endforeach

  <div class="separator"></div>
  <div class="row"><span>Subtotal</span><span>{{ number_format($transaksi->subtotal,0,',','.') }}</span></div>
  @if($transaksi->diskon > 0)
  <div class="row"><span>Diskon</span><span>- {{ number_format($transaksi->diskon,0,',','.') }}</span></div>
  @endif
  <div class="row grand"><span>TOTAL</span><span>{{ number_format($transaksi->total,0,',','.') }}</span></div>
  <div class="separator"></div>
  <div class="row"><span>Bayar ({{ strtoupper($transaksi->metode_bayar) }})</span><span>{{ number_format($transaksi->bayar,0,',','.') }}</span></div>
  <div class="row"><span>Kembalian</span><span>{{ number_format($transaksi->kembalian,0,',','.') }}</span></div>
  <div class="separator"></div>

  <div class="text-center is-012d43ed">Terima kasih atas kunjungan Anda</div>
  <div class="text-center is-bcf88ef7">Barang yang sudah dibeli tidak dapat dikembalikan</div>
  <br>
</div>

<div class="no-print text-center is-002535c2">
  <button onclick="window.print()" class="is-cb6ea729">
    🖨 Cetak
  </button>
  <button onclick="window.close()" class="is-2f8fd2bc">
    ✕ Tutup
  </button>
</div>

<script>
  // Auto print on load
  window.onload = function(){ window.print(); }
</script>
</body>
</html>

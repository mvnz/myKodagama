<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Struk {{ $transaksi->nomor }}</title>
  <style>
    :root {
      --paper-width: 80mm;
      --ink: #111;
      --muted: #666;
      --line: #000;
    }

    * {
      box-sizing: border-box;
    }

    html,
    body {
      margin: 0;
      padding: 0;
      background: #f4f4f4;
      color: var(--ink);
      font-family: "Courier New", Courier, monospace;
      line-height: 1.25;
      -webkit-print-color-adjust: exact;
      print-color-adjust: exact;
    }

    .receipt-wrap {
      width: min(100%, 360px);
      margin: 16px auto;
      padding: 0 8px;
    }

    .receipt {
      width: var(--paper-width);
      max-width: 100%;
      margin: 0 auto;
      background: #fff;
      border: 1px solid #ddd;
      border-radius: 8px;
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
      padding: 10px;
      font-size: 12px;
    }

    .center {
      text-align: center;
    }

    .store-name {
      font-size: 16px;
      font-weight: 700;
      letter-spacing: 0.4px;
      text-transform: uppercase;
    }

    .store-sub,
    .muted {
      color: var(--muted);
      font-size: 11px;
    }

    .divider {
      border-top: 1px dashed var(--line);
      margin: 8px 0;
    }

    .meta-row,
    .summary-row {
      display: flex;
      justify-content: space-between;
      gap: 8px;
      margin: 2px 0;
      white-space: nowrap;
    }

    .meta-row .label,
    .summary-row .label {
      color: var(--muted);
    }

    .summary-row.total {
      margin-top: 5px;
      padding-top: 5px;
      border-top: 1px solid #000;
      font-weight: 700;
      font-size: 13px;
      color: #000;
    }

    .item {
      margin: 6px 0;
    }

    .item-name {
      font-weight: 700;
      text-transform: uppercase;
      word-break: break-word;
    }

    .item-detail {
      display: flex;
      justify-content: space-between;
      gap: 8px;
      color: #222;
      padding-left: 10px;
    }

    .amount {
      text-align: right;
      min-width: 88px;
    }

    .footer-note {
      margin-top: 8px;
      text-align: center;
      font-size: 11px;
    }

    .actions {
      text-align: center;
      margin: 14px 0;
      display: flex;
      justify-content: center;
      gap: 8px;
    }

    .actions button {
      border: 1px solid #222;
      background: #fff;
      color: #111;
      padding: 8px 12px;
      border-radius: 6px;
      font-family: inherit;
      font-size: 12px;
      cursor: pointer;
    }

    .actions button.primary {
      background: #111;
      color: #fff;
    }

    @media print {
      @page {
        size: 80mm auto;
        margin: 0;
      }

      html,
      body {
        background: #fff;
      }

      .receipt-wrap {
        width: 80mm;
        margin: 0;
        padding: 0;
      }

      .receipt {
        border: none;
        border-radius: 0;
        box-shadow: none;
        margin: 0;
        width: 80mm;
        padding: 8px;
      }

      .no-print {
        display: none !important;
      }
    }
  </style>
</head>
<body>
  <div class="receipt-wrap">
    <div class="receipt">
      <div class="center store-name">{{ config('app.name') }}</div>
      <div class="center store-sub">POINT OF SALE</div>
      <div class="divider"></div>

      <div class="meta-row"><span class="label">No. Transaksi</span><span>{{ $transaksi->nomor }}</span></div>
      <div class="meta-row"><span class="label">Tanggal</span><span>{{ $transaksi->tanggal->format('d/m/Y') }}</span></div>
      <div class="meta-row"><span class="label">Waktu</span><span>{{ $transaksi->created_at->format('H:i:s') }}</span></div>
      <div class="meta-row"><span class="label">Kasir</span><span>{{ $transaksi->kasir?->name ?? '-' }}</span></div>

      <div class="divider"></div>

      @foreach($transaksi->items as $item)
        <div class="item">
          <div class="item-name">{{ $item->nama_produk }}</div>
          <div class="item-detail">
            <span>{{ number_format($item->qty, 0, ',', '.') }} x {{ number_format($item->harga, 0, ',', '.') }}</span>
            <span class="amount">{{ number_format($item->subtotal, 0, ',', '.') }}</span>
          </div>
        </div>
      @endforeach

      <div class="divider"></div>

      <div class="summary-row"><span class="label">Subtotal</span><span class="amount">{{ number_format($transaksi->subtotal, 0, ',', '.') }}</span></div>
      @if($transaksi->diskon > 0)
        <div class="summary-row"><span class="label">Diskon</span><span class="amount">-{{ number_format($transaksi->diskon, 0, ',', '.') }}</span></div>
      @endif
      <div class="summary-row total"><span>TOTAL</span><span class="amount">{{ number_format($transaksi->total, 0, ',', '.') }}</span></div>

      <div class="divider"></div>

      <div class="summary-row"><span class="label">Bayar ({{ strtoupper($transaksi->metode_bayar) }})</span><span class="amount">{{ number_format($transaksi->bayar, 0, ',', '.') }}</span></div>
      @if($transaksi->metode_bayar === 'tunai')
        <div class="summary-row"><span class="label">Kembalian</span><span class="amount">{{ number_format($transaksi->kembalian, 0, ',', '.') }}</span></div>
      @endif

      <div class="divider"></div>

      <div class="footer-note">Terima kasih atas kunjungan Anda</div>
      <div class="footer-note muted">Barang yang sudah dibeli tidak dapat dikembalikan</div>
    </div>

    <div class="actions no-print">
      <button type="button" class="primary" onclick="window.print()">Cetak</button>
      <button type="button" onclick="window.close()">Tutup</button>
    </div>
  </div>

  <script>
    window.onload = function () {
      window.print();
    };
  </script>
</body>
</html>

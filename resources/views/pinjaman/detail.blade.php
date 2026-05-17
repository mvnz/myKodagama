@extends('layouts.aplikasi')

@section('judul', 'Detail Pinjaman')

@section('konten')

{{-- Header cetak (hanya muncul saat print) --}}
<div class="d-none d-print-block mb-4 pb-3" style="border-bottom:2px solid #175cdd">
  <div class="d-flex align-items-start justify-content-between">
    <div>
      @php $namaKop = \App\Models\Pengaturan::get('nama_koperasi', 'Koperasi'); @endphp
      <div style="font-size:1.3rem;font-weight:800;color:#112344">{{ $namaKop }}</div>
      <div style="font-size:.85rem;color:#6b7280">Simpan Pinjam</div>
    </div>
    <div class="text-end">
      <div style="font-size:1rem;font-weight:700;color:#175cdd">DETAIL PINJAMAN</div>
      <div style="font-size:.78rem;color:#6b7280">Dicetak: {{ now()->isoFormat('D MMMM YYYY, HH:mm') }}</div>
    </div>
  </div>
</div>

{{-- ══ LAYOUT PRINT ONLY (menggantikan layout screen saat dicetak) ══ --}}
@php
  $sortedAng = $pinjaman->angsuran->sortBy('ke');
@endphp
<div class="d-none d-print-block">

  {{-- Info pinjaman: 3 baris --}}
  <div class="print-info-bar mb-2">
    {{-- Baris 1 --}}
    <div class="pi-row">
      <div><span class="pi-lbl">No. Pinjaman</span><strong>{{ $pinjaman->nomor_pinjaman }}</strong></div>
      <div><span class="pi-lbl">Anggota</span><strong>{{ $pinjaman->anggota->nama ?? '—' }}</strong></div>
      <div><span class="pi-lbl">Jumlah Pinjaman</span><strong>Rp {{ number_format($pinjaman->jumlah_pinjaman,0,',','.') }}</strong></div>
      <div><span class="pi-lbl">Jangka</span><strong>{{ $pinjaman->jangka_waktu }} Bulan</strong></div>
      <div><span class="pi-lbl">Bunga</span><strong>{{ $pinjaman->bunga }}% / Bulan</strong></div>
      <div><span class="pi-lbl">Cicilan/Bulan</span><strong style="color:#175cdd">Rp {{ number_format($pinjaman->cicilan_per_bulan,0,',','.') }}</strong></div>
    </div>
    {{-- Baris 2 --}}
    <div class="pi-row">
      <div><span class="pi-lbl">Status</span><strong style="text-transform:capitalize">{{ $pinjaman->status }}</strong></div>
      <div><span class="pi-lbl">Tgl Pengajuan</span><strong>{{ $pinjaman->tanggal_pengajuan->format('d M Y') }}</strong></div>
      @if($pinjaman->tanggal_verifikasi)
      <div><span class="pi-lbl">Tgl Verifikasi</span><strong>{{ $pinjaman->tanggal_verifikasi->format('d M Y') }}</strong></div>
      @endif
      @if($pinjaman->tanggal_persetujuan)
      <div><span class="pi-lbl">Tgl Persetujuan</span><strong>{{ $pinjaman->tanggal_persetujuan->format('d M Y') }}</strong></div>
      @endif
      @if($pinjaman->jenis_pinjaman)
      <div><span class="pi-lbl">Jenis</span><strong>{{ $pinjaman->jenis_pinjaman }}</strong></div>
      @endif
    </div>
    {{-- Baris 3 --}}
    @if($pinjaman->tujuan)
    <div class="pi-row">
      <div style="grid-column:1/-1"><span class="pi-lbl">Tujuan</span><strong>{{ $pinjaman->tujuan }}</strong></div>
    </div>
    @endif
  </div>

  {{-- Tabel angsuran satu baris --}}
  @if($sortedAng->count() > 0)
  <table class="print-ang-table w-100">
    <thead>
      <tr>
        <th>Ke</th><th>Jatuh Tempo</th><th>Pokok</th><th>Bunga</th>
        <th>Total</th><th>Denda</th><th>Tgl Bayar</th><th>Status</th>
      </tr>
    </thead>
    <tbody>
      @foreach($sortedAng as $ang)
      <tr class="{{ $ang->status === 'sudah' ? 'print-row-lunas' : ($ang->tanggal_jatuh_tempo < now() ? 'print-row-telat' : '') }}">
        <td><strong>{{ $ang->ke }}</strong></td>
        <td>{{ $ang->tanggal_jatuh_tempo->format('d M Y') }}</td>
        <td>Rp {{ number_format($ang->pokok,0,',','.') }}</td>
        <td>Rp {{ number_format($ang->bunga,0,',','.') }}</td>
        <td><strong>Rp {{ number_format($ang->jumlah_angsuran,0,',','.') }}</strong></td>
        <td>
          @if($ang->status === 'sudah' && $ang->denda > 0)
            Rp {{ number_format($ang->denda,0,',','.') }}
          @elseif($ang->status === 'belum' && $ang->tanggal_jatuh_tempo < now())
            @php $est = $ang->hitungDenda() @endphp
            {{ $est > 0 ? '~Rp '.number_format($est,0,',','.') : '—' }}
          @else —
          @endif
        </td>
        <td>{{ $ang->tanggal_bayar ? $ang->tanggal_bayar->format('d M Y') : '—' }}</td>
        <td>{{ $ang->status === 'sudah' ? 'Lunas' : 'Belum' }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>
  @endif

  {{-- Footer --}}
  <div class="mt-2 pt-1" style="border-top:1px solid #e5e7eb;font-size:7pt;color:#9ca3af;text-align:center">
    Total Terbayar: <strong>Rp {{ number_format($pinjaman->totalTerbayar(),0,',','.') }}</strong>
    &nbsp;|&nbsp; Sisa Angsuran: <strong>{{ $pinjaman->sisaAngsuran() }} kali</strong>
  </div>
</div>

{{-- ══ LAYOUT SCREEN (disembunyikan saat print) ══ --}}
<div class="row g-4 d-print-none">
  <!-- Info Pinjaman -->
  <div class="col-xl-4">
    <div class="form-card">
      @php $badgeMap = ['diajukan'=>'badge-diajukan','diverifikasi'=>'badge-diverifikasi','disetujui'=>'badge-disetujui','ditolak'=>'badge-ditolak','lunas'=>'badge-lunas']; @endphp
      <div class="text-center mb-4 pb-4 border-bottom d-print-none">
        <div class="d-flex align-items-center justify-content-center rounded-circle mx-auto mb-3"
          style="width:70px;height:70px;background:rgba(245,158,11,.12)">
          <i class="bi bi-cash-coin" style="color:#f59e0b;font-size:2rem"></i>
        </div>
        <code class="d-block text-primary mb-2">{{ $pinjaman->nomor_pinjaman }}</code>
        <span class="badge-status {{ $badgeMap[$pinjaman->status] ?? '' }} d-inline-block">
          {{ ucfirst($pinjaman->status) }}
        </span>
        @if($pinjaman->jenis_pinjaman)
          <div class="mt-2">
            <span class="badge rounded-pill" style="background:rgba(23,92,221,.1);color:#175cdd;font-size:.75rem;font-weight:600;padding:.3em .75em">
              <i class="bi bi-tag-fill me-1"></i>{{ $pinjaman->jenis_pinjaman }}
            </span>
          </div>
        @endif

        {{-- Progress stepper --}}
        @if(!in_array($pinjaman->status, ['ditolak','lunas']))
        {{-- progress stepper: disembunyikan saat print karena ada di header --}}
        <div class="d-print-none">
        @php
          $steps = ['diajukan','diverifikasi','disetujui'];
          $aktif = array_search($pinjaman->status, $steps) ?? 0;
        @endphp
        <div class="d-flex align-items-center justify-content-center gap-1 mt-3">
          @foreach($steps as $si => $step)
            @php
              $done   = $si < $aktif;
              $current= $si === $aktif;
              $labels = ['diajukan'=>'Diajukan','diverifikasi'=>'Diverifikasi','disetujui'=>'Disetujui'];
            @endphp
            <div class="d-flex flex-column align-items-center" style="min-width:70px">
              <div class="rounded-circle d-flex align-items-center justify-content-center mb-1"
                style="width:28px;height:28px;font-size:.7rem;font-weight:700;
                background:{{ $done ? '#10b981' : ($current ? '#175cdd' : '#e5e7eb') }};
                color:{{ ($done || $current) ? '#fff' : '#9ca3af' }}">
                @if($done) <i class="bi bi-check-lg"></i>
                @else {{ $si + 1 }}
                @endif
              </div>
              <span style="font-size:.65rem;color:{{ $current ? '#175cdd' : ($done ? '#10b981' : '#9ca3af') }};font-weight:{{ $current ? '700' : '400' }}">
                {{ $labels[$step] }}
              </span>
            </div>
            @if(!$loop->last)
              <div style="height:2px;width:24px;background:{{ $si < $aktif ? '#10b981' : '#e5e7eb' }};margin-bottom:18px"></div>
            @endif
          @endforeach
        </div>
        </div>{{-- /d-print-none stepper --}}
        @endif
      </div>

      <div class="d-flex flex-column gap-3 mb-4 print-info-items">
        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
          <span class="text-muted small">Anggota</span>
          <strong style="color:#112344">{{ $pinjaman->anggota->nama ?? '—' }}</strong>
        </div>
        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
          <span class="text-muted small">Jumlah Pinjaman</span>
          <strong style="color:#112344">Rp {{ number_format($pinjaman->jumlah_pinjaman, 0, ',', '.') }}</strong>
        </div>
        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
          <span class="text-muted small">Jangka Waktu</span>
          <strong>{{ $pinjaman->jangka_waktu }} Bulan</strong>
        </div>
        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
          <span class="text-muted small">Bunga</span>
          <strong>{{ $pinjaman->bunga }}% / Bulan</strong>
        </div>
        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
          <span class="text-muted small">Cicilan / Bulan</span>
          <strong style="color:#175cdd">
            @if($pinjaman->cicilan_per_bulan)
              Rp {{ number_format($pinjaman->cicilan_per_bulan, 0, ',', '.') }}
            @else —
            @endif
          </strong>
        </div>
        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
          <span class="text-muted small">Total Terbayar</span>
          <strong style="color:#10b981">Rp {{ number_format($pinjaman->totalTerbayar(), 0, ',', '.') }}</strong>
        </div>
        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
          <span class="text-muted small">Sisa Angsuran</span>
          <strong style="color:#ef4444">{{ $pinjaman->sisaAngsuran() }} kali</strong>
        </div>
        <div class="d-flex justify-content-between py-2 border-bottom">
          <span class="text-muted small">Tgl Pengajuan</span>
          <strong>{{ $pinjaman->tanggal_pengajuan->format('d M Y') }}</strong>
        </div>
        @if($pinjaman->tanggal_verifikasi)
        <div class="d-flex justify-content-between py-2 border-bottom">
          <span class="text-muted small">Tgl Verifikasi</span>
          <strong>{{ $pinjaman->tanggal_verifikasi->format('d M Y') }}</strong>
        </div>
        @endif
        @if($pinjaman->tanggal_persetujuan)
        <div class="d-flex justify-content-between py-2 border-bottom">
          <span class="text-muted small">Tgl Persetujuan</span>
          <strong>{{ $pinjaman->tanggal_persetujuan->format('d M Y') }}</strong>
        </div>
        @endif
        <div>
          <span class="text-muted small d-block mb-1">Tujuan</span>
          <div style="color:#374151;font-size:.9rem">{{ $pinjaman->tujuan }}</div>
        </div>
      </div>

      {{-- Tombol aksi: disembunyikan saat print --}}
      <div class="d-flex flex-column gap-2 d-print-none">

        <button onclick="window.print()" class="btn btn-outline-secondary d-flex align-items-center justify-content-center gap-2">
          <i class="bi bi-printer"></i> Cetak
        </button>
        <hr class="my-1">

        {{-- ── Alur: Verifikasi → Setujui / Tolak ── --}}
        @if($pinjaman->status === 'diajukan')
          @can('pinjaman.verifikasi')
          <form action="{{ route('pinjaman.verifikasi', $pinjaman) }}" method="POST"
            onsubmit="return confirm('Verifikasi pinjaman ini? Status akan berubah ke Diverifikasi.')">
            @csrf
            <button type="submit" class="btn btn-info text-white w-100 d-flex align-items-center justify-content-center gap-2">
              <i class="bi bi-patch-check"></i> Verifikasi Pinjaman
            </button>
          </form>
          @endcan
          @can('pinjaman.hapus')
          <form action="{{ route('pinjaman.tolak', $pinjaman) }}" method="POST"
            onsubmit="return confirm('Tolak pinjaman ini?')">
            @csrf
            <button type="submit" class="btn btn-outline-danger w-100 d-flex align-items-center justify-content-center gap-2">
              <i class="bi bi-x-circle"></i> Tolak
            </button>
          </form>
          @endcan
        @endif

        @if($pinjaman->status === 'diverifikasi')
          @can('pinjaman.setujui')
          <div class="p-3 rounded-3 border mb-1" style="background:#f0fff4;border-color:#86efac !important">
            <div class="fw-600 mb-2" style="color:#15803d;font-size:.85rem">
              <i class="bi bi-check-circle-fill me-1"></i> Sudah diverifikasi — siap disetujui
            </div>
            <form action="{{ route('pinjaman.setujui', $pinjaman) }}" method="POST"
              onsubmit="return confirm('Setujui pinjaman ini? Jadwal angsuran akan dibuat otomatis.')">
              @csrf
              <div class="input-group input-group-sm mb-2">
                <span class="input-group-text">Tgl Persetujuan</span>
                <input type="date" name="tanggal_persetujuan" class="form-control"
                  value="{{ today()->toDateString() }}">
              </div>
              <button type="submit" class="btn btn-success w-100 d-flex align-items-center justify-content-center gap-2">
                <i class="bi bi-check2-circle"></i> Setujui Pinjaman
              </button>
            </form>
          </div>
          @endcan
          @can('pinjaman.hapus')
          <form action="{{ route('pinjaman.tolak', $pinjaman) }}" method="POST"
            onsubmit="return confirm('Tolak pinjaman ini?')">
            @csrf
            <button type="submit" class="btn btn-outline-danger w-100 d-flex align-items-center justify-content-center gap-2">
              <i class="bi bi-x-circle"></i> Tolak
            </button>
          </form>
          @endcan
        @endif

        @can('pinjaman.edit')
        <a href="{{ route('pinjaman.edit', $pinjaman) }}" class="btn btn-warning d-flex align-items-center justify-content-center gap-2">
          <i class="bi bi-pencil"></i> Ubah Data
        </a>
        @endcan
        @can('anggota.lihat')
        <a href="{{ route('anggota.show', $pinjaman->anggota_id) }}" class="btn btn-outline-primary d-flex align-items-center justify-content-center gap-2">
          <i class="bi bi-person"></i> Profil Anggota
        </a>
        @endcan
        @can('pinjaman.hapus')
        <form action="{{ route('pinjaman.destroy', $pinjaman) }}" method="POST"
          onsubmit="return confirm('Hapus pinjaman ini? Semua angsuran terkait juga akan dihapus.')">
          @csrf @method('DELETE')
          <button type="submit" class="btn btn-outline-danger w-100 d-flex align-items-center justify-content-center gap-2">
            <i class="bi bi-trash"></i> Hapus
          </button>
        </form>
        @endcan
      </div>
    </div>
  </div>

  <!-- Jadwal Angsuran -->
  <div class="col-xl-8">
    <div class="form-card p-0 overflow-hidden">
      <div class="p-4 border-bottom d-flex align-items-center justify-content-between">
        <h6 class="mb-0 fw-700" style="color:#112344">
          <i class="bi bi-list-check me-2" style="color:#175cdd"></i>Jadwal Angsuran
          <span class="badge bg-primary ms-2">{{ $pinjaman->angsuran->count() }}</span>
        </h6>
      </div>

      @if($pinjaman->angsuran->count() > 0)
        <div class="table-responsive">
          <table class="koperasi-table mb-0">
            <thead>
              <tr>
                <th>Ke</th>
                <th>Jatuh Tempo</th>
                <th>Pokok</th>
                <th>Bunga</th>
                <th>Total</th>
                <th>Denda</th>
                <th>Tgl Bayar</th>
                <th>Status</th>
                <th class="text-center d-print-none">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @foreach($pinjaman->angsuran->sortBy('ke') as $angsuran)
                <tr class="{{ $angsuran->status === 'sudah' ? 'table-success' : ($angsuran->tanggal_jatuh_tempo < now() ? 'table-danger' : '') }}"
                  style="opacity:{{ $angsuran->status === 'sudah' ? '.7' : '1' }}">
                  <td><strong>{{ $angsuran->ke }}</strong></td>
                  <td>{{ $angsuran->tanggal_jatuh_tempo->format('d M Y') }}</td>
                  <td>Rp {{ number_format($angsuran->pokok, 0, ',', '.') }}</td>
                  <td>Rp {{ number_format($angsuran->bunga, 0, ',', '.') }}</td>
                  <td><strong>Rp {{ number_format($angsuran->jumlah_angsuran, 0, ',', '.') }}</strong></td>
                  <td>
                    @if($angsuran->status === 'sudah' && $angsuran->denda > 0)
                      <span style="color:#ef4444;font-weight:600;font-size:.82rem">Rp {{ number_format($angsuran->denda, 0, ',', '.') }}</span>
                      <div><small class="text-muted">{{ $angsuran->hari_terlambat }}h</small></div>
                    @elseif($angsuran->status === 'belum' && $angsuran->tanggal_jatuh_tempo < now())
                      @php $est = $angsuran->hitungDenda() @endphp
                      @if($est > 0)
                        <span style="color:#f59e0b;font-size:.8rem">~Rp {{ number_format($est, 0, ',', '.') }}</span>
                      @else
                        <span class="text-muted">—</span>
                      @endif
                    @else
                      <span class="text-muted">—</span>
                    @endif
                  </td>
                  <td>
                    @if($angsuran->tanggal_bayar)
                      {{ $angsuran->tanggal_bayar->format('d M Y') }}
                    @else
                      <span class="text-muted">—</span>
                    @endif
                  </td>
                  <td>
                    <span class="badge-status {{ $angsuran->status === 'sudah' ? 'badge-sudah' : 'badge-belum' }}">
                      {{ $angsuran->status === 'sudah' ? 'Lunas' : 'Belum' }}
                    </span>
                  </td>
                  <td class="text-center d-print-none">
                    @if($angsuran->status === 'belum')
                      @can('angsuran.bayar')
                      <form action="{{ route('angsuran.bayar', $angsuran) }}" method="POST"
                        onsubmit="return confirm('Tandai angsuran ke-{{ $angsuran->ke }} sebagai lunas?')">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-success">
                          <i class="bi bi-check2"></i> Bayar
                        </button>
                      </form>
                      @endcan
                    @else
                      <span class="text-success"><i class="bi bi-check-circle-fill"></i></span>
                    @endif
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @else
        <div class="text-center py-5 text-muted">
          <i class="bi bi-calendar-x display-4 d-block mb-2 opacity-25"></i>
          <p class="mb-0">Jadwal angsuran belum tersedia</p>
          @if($pinjaman->status === 'diajukan')
            <small>Setujui pinjaman untuk membuat jadwal angsuran otomatis</small>
          @endif
        </div>
      @endif
    </div>
  </div>
</div>
@endsection

@push('css')
<style>
@media print {
  .sidebar, .topnav-app, .d-print-none { display: none !important; }
  .main-content { margin-left: 0 !important; background: #fff !important; padding: 0 !important; }
  body { background: #fff !important; }
  @page { margin: 1cm; size: A4 portrait; }
}

/* Info bar: 3 baris */
.print-info-bar {
  border-bottom: 1px solid #e5e7eb;
  margin-bottom: .5rem;
  padding-bottom: .3rem;
}
.pi-row {
  display: flex;
  gap: .1rem 2rem;
  padding: .15rem 0;
  border-bottom: 1px dashed #f3f4f6;
}
.pi-row:last-child { border-bottom: none; }
.pi-row > div { font-size: 8pt; }
.pi-lbl { font-size: 7pt; color: #9ca3af; display: block; }

/* Tabel angsuran print */
.print-ang-table { border-collapse: collapse; width: 100%; font-size: 7.5pt; }
.print-ang-table th {
  background: #175cdd;
  color: #fff;
  padding: .2rem .3rem;
  text-align: left;
  font-size: 7pt;
}
.print-ang-table td { border-bottom: 1px solid #e5e7eb; padding: .18rem .3rem; }
.print-row-lunas td { background: #f0fdf4; color: #166534; }
.print-row-telat td { background: #fff1f2; }
</style>
@endpush

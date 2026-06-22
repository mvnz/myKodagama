@extends('layouts.aplikasi')

@section('judul', 'Detail Pinjaman')

@section('konten')

{{-- Header cetak (hanya muncul saat print) --}}
<div class="d-none d-print-block mb-4 pb-3 is-8f03c7c6">
  <div class="d-flex align-items-start justify-content-between">
    <div>
      @php $namaKop = \App\Models\Koperasi\Pengaturan::get('nama_koperasi', 'Koperasi'); @endphp
      <div class="is-158349ce">{{ $namaKop }}</div>
      <div class="is-7198b4a4">Simpan Pinjam</div>
    </div>
    <div class="text-end">
      <div class="is-15092d38">DETAIL PINJAMAN</div>
      <div class="is-89ab8e63">Dicetak: {{ now()->isoFormat('D MMMM YYYY, HH:mm') }}</div>
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
      <div><span class="pi-lbl">Cicilan/Bulan</span><strong class="is-bc519fd0">Rp {{ number_format($pinjaman->cicilan_per_bulan,0,',','.') }}</strong></div>
    </div>
    {{-- Baris 2 --}}
    <div class="pi-row">
      <div><span class="pi-lbl">Status</span><strong class="is-aab31f83">{{ $pinjaman->status }}</strong></div>
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
      <div class="is-1b9b41ec"><span class="pi-lbl">Tujuan</span><strong>{{ $pinjaman->tujuan }}</strong></div>
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
  <div class="mt-2 pt-1 is-9e58ecd2">
    Total Terbayar: <strong>Rp {{ number_format($pinjaman->totalTerbayar(),0,',','.') }}</strong>
    &nbsp;|&nbsp; Sisa Angsuran: <strong>{{ $pinjaman->sisaAngsuran() }} kali</strong>
  </div>
</div>

{{-- ══ TRACKING CARD (screen only) ══ --}}
<div class="row d-print-none mb-2">
  <div class="col-12">
    <div class="form-card py-4">
      @php
        $isDitolak = $pinjaman->status === 'ditolak';
        $isLunas   = $pinjaman->status === 'lunas';

        $namaVerif = $pinjaman->verifikator_id
          ? optional(\App\Models\User::find($pinjaman->verifikator_id))->name
          : null;

        $tglLunas = $isLunas
          ? $pinjaman->angsuran->where('status','sudah')->sortByDesc('tanggal_bayar')->first()?->tanggal_bayar
          : null;

        // Build nodes dynamically
        $tNodes = [[
          'label'  => 'Pengajuan',
          'icon'   => 'bi-file-earmark-text-fill',
          'tanggal'=> $pinjaman->tanggal_pengajuan,
          'sub'    => 'Berkas diterima',
          'type'   => 'done',
        ]];

        if ($isDitolak && !$pinjaman->tanggal_verifikasi) {
          $tNodes[] = ['label'=>'Ditolak','icon'=>'bi-x-circle-fill','tanggal'=>null,'sub'=>'Pengajuan tidak lolos','type'=>'rejected'];
        } else {
          $vDone = in_array($pinjaman->status, ['diverifikasi','disetujui','lunas'])
            || ($isDitolak && $pinjaman->tanggal_verifikasi);
          $tNodes[] = [
            'label'  => 'Verifikasi',
            'icon'   => 'bi-patch-check-fill',
            'tanggal'=> $pinjaman->tanggal_verifikasi,
            'sub'    => $namaVerif ?? ($pinjaman->status === 'diajukan' ? 'Menunggu' : '—'),
            'type'   => $vDone ? 'done' : ($pinjaman->status === 'diajukan' ? 'pending' : 'active'),
          ];
          if ($isDitolak) {
            $tNodes[] = ['label'=>'Ditolak','icon'=>'bi-x-circle-fill','tanggal'=>null,'sub'=>'Tidak memenuhi syarat','type'=>'rejected'];
          } else {
            $sDone = in_array($pinjaman->status, ['disetujui','lunas']);
            $tNodes[] = [
              'label'  => 'Persetujuan',
              'icon'   => 'bi-check-circle-fill',
              'tanggal'=> $pinjaman->tanggal_persetujuan,
              'sub'    => optional($pinjaman->petugas)->name ?? ($pinjaman->status === 'diverifikasi' ? 'Menunggu' : '—'),
              'type'   => $sDone ? 'done' : ($pinjaman->status === 'diverifikasi' ? 'active' : 'pending'),
            ];
            $tNodes[] = [
              'label'  => 'Pelunasan',
              'icon'   => 'bi-trophy-fill',
              'tanggal'=> $tglLunas,
              'sub'    => $isLunas ? 'Semua cicilan terbayar' : ($pinjaman->sisaAngsuran().' cicilan tersisa'),
              'type'   => $isLunas ? 'done' : ($pinjaman->status === 'disetujui' ? 'active' : 'pending'),
            ];
          }
        }
      @endphp

      <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-bottom">
        <h6 class="fw-700 mb-0 is-5ca88772">
          <i class="bi bi-geo-alt-fill me-2 is-bc519fd0"></i>Tracking Pengajuan
        </h6>
        <div class="d-flex align-items-center gap-2">
          <span class="text-muted is-bb0425a5">{{ $pinjaman->nomor_pinjaman }}</span>
          @php $bm2 = ['diajukan'=>'badge-diajukan','diverifikasi'=>'badge-diverifikasi','disetujui'=>'badge-disetujui','ditolak'=>'badge-ditolak','lunas'=>'badge-lunas']; @endphp
          <span class="badge-status {{ $bm2[$pinjaman->status] ?? '' }}">{{ ucfirst($pinjaman->status) }}</span>
        </div>
      </div>

      <div class="d-flex align-items-start is-755ffe95">
        @foreach($tNodes as $tNode)
          @php
            $tCirc = match($tNode['type']) {
              'done'     => '#10b981',
              'active'   => '#175cdd',
              'rejected' => '#ef4444',
              default    => '#e5e7eb',
            };
            $tIcon  = in_array($tNode['type'], ['done','active','rejected']) ? '#fff' : '#9ca3af';
            $tLbl   = match($tNode['type']) {
              'done'     => '#10b981',
              'active'   => '#175cdd',
              'rejected' => '#ef4444',
              default    => '#9ca3af',
            };
            $tShad  = $tNode['type'] === 'active' ? '0 0 0 6px rgba(23,92,221,.15)' : 'none';
            $tConn  = match($tNode['type']) {
              'done'     => '#10b981',
              'rejected' => '#ef4444',
              default    => '#e5e7eb',
            };
            $tFw    = in_array($tNode['type'], ['done','active','rejected']) ? '700' : '400';
          @endphp

          <div class="d-flex flex-column align-items-center text-center is-bae173dd">
            <div class="rounded-circle d-flex align-items-center justify-content-center mb-2 is-22adff74"
             >
              @if($tNode['type'] === 'done')
                <i class="bi bi-check-lg is-15de2739"></i>
              @else
                <i class="bi {{ $tNode['icon'] }} is-dbbb13e9"></i>
              @endif
            </div>
            <div class="is-e9c1b010">{{ $tNode['label'] }}</div>
            @if($tNode['tanggal'])
              <div class="is-8711f035">{{ $tNode['tanggal']->format('d M Y') }}</div>
            @elseif($tNode['type'] === 'active')
              <div class="is-6713d1bd">Saat ini</div>
            @else
              <div class="is-d614f550">—</div>
            @endif
            <div class="is-a5db1d45">{{ $tNode['sub'] }}</div>
          </div>

          @if(!$loop->last)
            <div class="is-aeda5427"></div>
          @endif
        @endforeach
      </div>

    </div>
  </div>
</div>

{{-- ══ LAYOUT SCREEN (disembunyikan saat print) ══ --}}
<div class="row g-4 d-print-none">
  <!-- Info Pinjaman -->
  <div class="col-xl-4">
    <div class="form-card">
      @php $badgeMap = ['diajukan'=>'badge-diajukan','diverifikasi'=>'badge-diverifikasi','disetujui'=>'badge-disetujui','ditolak'=>'badge-ditolak','lunas'=>'badge-lunas']; @endphp
      <div class="text-center mb-4 pb-4 border-bottom d-print-none">
        <div class="d-flex align-items-center justify-content-center rounded-circle mx-auto mb-3 is-bb7e6ae2"
         >
          <i class="bi bi-cash-coin is-14c6158c"></i>
        </div>
        <code class="d-block text-primary mb-2">{{ $pinjaman->nomor_pinjaman }}</code>
        <span class="badge-status {{ $badgeMap[$pinjaman->status] ?? '' }} d-inline-block">
          {{ ucfirst($pinjaman->status) }}
        </span>
        @if($pinjaman->jenis_pinjaman)
          <div class="mt-2">
            <span class="badge rounded-pill is-757341fe">
              <i class="bi bi-tag-fill me-1"></i>{{ $pinjaman->jenis_pinjaman }}
            </span>
          </div>
        @endif


      </div>

      <div class="d-flex flex-column gap-3 mb-4 print-info-items">
        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
          <span class="text-muted small">Anggota</span>
          <strong class="is-5ca88772">{{ $pinjaman->anggota->nama ?? '—' }}</strong>
        </div>
        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
          <span class="text-muted small">Jumlah Pinjaman</span>
          <strong class="is-5ca88772">Rp {{ number_format($pinjaman->jumlah_pinjaman, 0, ',', '.') }}</strong>
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
          <strong class="is-bc519fd0">
            @if($pinjaman->cicilan_per_bulan)
              Rp {{ number_format($pinjaman->cicilan_per_bulan, 0, ',', '.') }}
            @else —
            @endif
          </strong>
        </div>
        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
          <span class="text-muted small">Total Terbayar</span>
          <strong class="is-29fb9df2">Rp {{ number_format($pinjaman->totalTerbayar(), 0, ',', '.') }}</strong>
        </div>
        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
          <span class="text-muted small">Sisa Angsuran</span>
          <strong class="is-c9e34862">{{ $pinjaman->sisaAngsuran() }} kali</strong>
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
          <div class="is-2e23fa0f">{{ $pinjaman->tujuan }}</div>
        </div>
      </div>

      {{-- Tombol aksi: disembunyikan saat print --}}
      <div class="d-flex flex-column gap-2 d-print-none">

        <button onclick="window.print()" class="btn btn-outline-secondary">
          <i class="bi bi-printer"></i> Cetak
        </button>
        <hr class="my-1">

        {{-- ── Alur: Verifikasi → Setujui / Tolak ── --}}
        @if($pinjaman->status === 'diajukan')
          @if($bisaVerifikasi)
          <form action="{{ route('pinjaman.verifikasi', $pinjaman) }}" method="POST"
            onsubmit="return confirm('Verifikasi pinjaman ini? Status akan berubah ke Diverifikasi.')">
            @csrf
            <button type="submit" class="btn btn-info text-white w-100 d-flex align-items-center justify-content-center gap-2">
              <i class="bi bi-patch-check"></i> Verifikasi Pinjaman
            </button>
          </form>
          @endif
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
          @if($bisaSetujui)
          <div class="p-3 rounded-3 border mb-1 is-13e01680">
            <div class="fw-600 mb-2 is-5846ef9c">
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
          @endif
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
        @if(!in_array($pinjaman->status, ['disetujui', 'lunas']))
        <a href="{{ route('pinjaman.edit', $pinjaman) }}" class="btn btn-warning d-flex align-items-center justify-content-center gap-2">
          <i class="bi bi-pencil"></i> Ubah Data
        </a>
        @endif
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
        <h6 class="mb-0 fw-700 is-5ca88772">
          <i class="bi bi-list-check me-2 is-bc519fd0"></i>Jadwal Angsuran
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
                <tr class="{{ $angsuran->status === 'sudah' ? 'table-success is-opacity-07' : ($angsuran->tanggal_jatuh_tempo < now() ? 'table-danger' : '') }}">
                  <td><strong>{{ $angsuran->ke }}</strong></td>
                  <td>{{ $angsuran->tanggal_jatuh_tempo->format('d M Y') }}</td>
                  <td>Rp {{ number_format($angsuran->pokok, 0, ',', '.') }}</td>
                  <td>Rp {{ number_format($angsuran->bunga, 0, ',', '.') }}</td>
                  <td><strong>Rp {{ number_format($angsuran->jumlah_angsuran, 0, ',', '.') }}</strong></td>
                  <td>
                    @if($angsuran->status === 'sudah' && $angsuran->denda > 0)
                      <span class="is-568658f8">Rp {{ number_format($angsuran->denda, 0, ',', '.') }}</span>
                      <div><small class="text-muted">{{ $angsuran->hari_terlambat }}h</small></div>
                    @elseif($angsuran->status === 'belum' && $angsuran->tanggal_jatuh_tempo < now())
                      @php $est = $angsuran->hitungDenda() @endphp
                      @if($est > 0)
                        <span class="is-988b6ff5">~Rp {{ number_format($est, 0, ',', '.') }}</span>
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
<link rel="stylesheet" href="{{ asset('css/pages/pinjaman-detail.css') }}">
@endpush
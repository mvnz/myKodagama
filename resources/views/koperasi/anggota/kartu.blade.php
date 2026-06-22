@extends('layouts.aplikasi')

@section('judul', 'Kartu Anggota – ' . $anggota->nama)

@push('css')
<link rel="stylesheet" href="{{ asset('css/pages/kartu-anggota.css') }}">
@endpush

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('anggota.index') }}">Anggota</a></li>
  <li class="breadcrumb-item"><a href="{{ route('anggota.rekap', $anggota) }}">{{ $anggota->nomor_anggota }}</a></li>
  <li class="breadcrumb-item active">Kartu</li>
@endsection

@section('konten')
<div class="d-flex justify-content-end gap-2 mb-3 d-print-none">
  <button onclick="window.print()" class="btn btn-primary">
    <i class="bi bi-printer me-1"></i> Cetak Kartu
  </button>
  <a href="{{ route('anggota.rekap', $anggota) }}" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-arrow-left me-1"></i> Kembali
  </a>
</div>

<div class="kartu-wrapper">
  @php
    $jkRaw = trim((string) ($anggota->jenis_kelamin ?? ''));
    $jk = match (strtolower($jkRaw)) {
      'l', 'laki-laki', 'laki laki' => 'Laki-laki',
      'p', 'perempuan' => 'Perempuan',
      default => $jkRaw !== '' ? $jkRaw : '-',
    };
    $fotoUrl = $anggota->foto ? Storage::url($anggota->foto) : null;
    $inisial = strtoupper(substr($anggota->nama, 0, 1));
  @endphp

  <div class="member-card-preview">
    <div class="member-card">
      <div class="member-card__shine"></div>
      <div class="member-card__accent"></div>

      <div class="member-card__title">KODAGAMA</div>

      <div class="member-card__content">
        <div class="member-card__meta">
          <div class="member-card__label">NAMA</div>
          <div class="member-card__value">{{ strtoupper($anggota->nama) }}</div>
          <div class="member-card__label">NOMOR</div>
          <div class="member-card__value">{{ $anggota->nomor_anggota }}</div>
        </div>

        <div class="member-card__photo" aria-label="Foto anggota">
          @if($fotoUrl)
            <img src="{{ $fotoUrl }}" alt="{{ $anggota->nama }}">
          @else
            <span>{{ $inisial }}</span>
          @endif
        </div>
      </div>

      <div class="member-card__footer">
        <div class="member-card__brand-text">{{ 'Koperasi Juanda Tiga Lima' }}</div>
      </div>
    </div>
  </div>

  <div class="kartu-body d-print-none mt-4">
    <div class="row g-3 mb-4">
      <div class="col-sm-6">
        <div class="kartu-label">NIK</div>
        <div class="kartu-value">{{ $anggota->nik }}</div>
      </div>
      <div class="col-sm-6">
        <div class="kartu-label">Tempat, Tanggal Lahir</div>
        <div class="kartu-value">{{ $anggota->tempat_lahir }}, {{ \Carbon\Carbon::parse($anggota->tanggal_lahir)->isoFormat('D MMMM Y') }}</div>
      </div>
      <div class="col-sm-6">
        <div class="kartu-label">Jenis Kelamin</div>
        <div class="kartu-value">{{ $jk }}</div>
      </div>
      <div class="col-sm-6">
        <div class="kartu-label">Telepon</div>
        <div class="kartu-value">{{ $anggota->telepon ?: '-' }}</div>
      </div>
      <div class="col-12">
        <div class="kartu-label">Alamat</div>
        <div class="kartu-value">{{ $anggota->alamat }}</div>
      </div>
      @if($anggota->unitKerja)
      <div class="col-sm-6">
        <div class="kartu-label">Unit Kerja</div>
        <div class="kartu-value">{{ $anggota->unitKerja->nama }}</div>
      </div>
      @endif
      @if($anggota->jenis_anggota)
      <div class="col-sm-6">
        <div class="kartu-label">Jenis Anggota</div>
        <div class="kartu-value">{{ $anggota->jenis_anggota }}</div>
      </div>
      @endif
    </div>

    <hr>

    {{-- Rekap simpanan --}}
    <div class="mb-3">
      <div class="fw-700 mb-2 is-03dc4856">REKAP SIMPANAN</div>
      <div class="row g-2">
        <div class="col-4">
          <div class="border rounded p-2 text-center">
            <div class="kartu-label">Pokok</div>
            <div class="kartu-value text-primary">Rp {{ number_format($totalPokok, 0, ',', '.') }}</div>
          </div>
        </div>
        <div class="col-4">
          <div class="border rounded p-2 text-center">
            <div class="kartu-label">Wajib</div>
            <div class="kartu-value text-primary">Rp {{ number_format($totalWajib, 0, ',', '.') }}</div>
          </div>
        </div>
        <div class="col-4">
          <div class="border rounded p-2 text-center">
            <div class="kartu-label">Sukarela</div>
            <div class="kartu-value text-primary">Rp {{ number_format($totalSukarel, 0, ',', '.') }}</div>
          </div>
        </div>
      </div>
      <div class="mt-2 text-end is-65411ab9">
        Total simpanan: <strong class="text-dark">Rp {{ number_format($totalPokok + $totalWajib + $totalSukarel, 0, ',', '.') }}</strong>
      </div>
    </div>

    @if($pinjamanAktif->count())
    <hr>
    <div>
      <div class="fw-700 mb-2 is-03dc4856">PINJAMAN AKTIF</div>
      <div class="table-responsive">
        <table class="table table-sm mb-0 is-0de4e77b">
          <thead class="table-light">
            <tr>
              <th>Tgl Pengajuan</th>
              <th>Jumlah</th>
              <th>Tenor</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            @foreach($pinjamanAktif as $p)
            <tr>
              <td>{{ \Carbon\Carbon::parse($p->tanggal_pengajuan)->isoFormat('D MMM Y') }}</td>
              <td>Rp {{ number_format($p->jumlah, 0, ',', '.') }}</td>
              <td>{{ $p->tenor }} bln</td>
              <td><span class="badge bg-success">Disetujui</span></td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
    @endif

    <hr class="mt-4">
    <div class="d-flex justify-content-between align-items-end is-cd8e7d28">
      <div>Dicetak: {{ now()->isoFormat('D MMMM Y, HH:mm') }}</div>
      <div>{{ $namaKoperasiLayout ?? 'Koperasi' }}</div>
    </div>
  </div>
</div>
@endsection
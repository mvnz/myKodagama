@extends('layouts.aplikasi')

@section('judul', 'Struktur Organisasi')

@push('css')
<link rel="stylesheet" href="{{ asset('css/pages/struktur-organisasi.css') }}">
@endpush

@section('konten')

{{-- Page Banner --}}
<div class="dashboard-welcome mb-4">
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
    <div>
      <div class="welcome-label mb-1">Pengaturan Koperasi</div>
      <h4 class="mb-1 fw-700">Struktur Organisasi Pengurus</h4>
      <p class="mb-0 welcome-date">
        <i class="bi bi-calendar3 me-1"></i>
        {{ now()->translatedFormat('l, d F Y') }}
      </p>
    </div>
    <div class="welcome-icon-wrap">
      <i class="bi bi-diagram-3-fill"></i>
    </div>
  </div>
</div>

<div class="form-card">
  @if(empty($grouped))
    <div class="text-center py-5 text-muted">
      <i class="bi bi-people is-951da32f"></i>
      <p class="mt-3 mb-0 fw-600">Data pengurus belum diisi.</p>
      @can('pengaturan.pengurus')
      <a href="{{ route('pengaturan.pengurus') }}" class="btn btn-primary btn-sm mt-3">
        <i class="bi bi-plus me-1"></i> Isi Sekarang
      </a>
      @endcan
    </div>
  @else
    <div class="orgchart-area">
      @php
        $colorClass = fn($baris) => match(true) {
          $baris == 1 => 'org-baris-1',
          $baris == 2 => 'org-baris-2',
          $baris == 3 => 'org-baris-3',
          $baris == 4 => 'org-baris-4',
          $baris == 5 => 'org-baris-5',
          default     => 'org-baris-other',
        };
      @endphp

      @foreach($grouped as $baris => $posisi)
        @if(!$loop->first)
          <div class="d-flex justify-content-center my-1">
            <div class="org-vline"></div>
          </div>
        @endif
        <div class="org-level-row {{ $loop->last ? '' : 'mb-1' }}">
          @foreach($posisi as $p)
            <div class="org-card {{ $colorClass((int)$baris) }}">
              <div class="org-card-avatar">
                <i class="bi bi-person-fill"></i>
              </div>
              <div class="org-card-jabatan">{{ $p['jabatan'] ?: '—' }}</div>
              <div class="org-card-nama">{{ $p['nama'] ?: '—' }}</div>
              @if(!empty($p['periode']))
                <div class="org-card-periode"><i class="bi bi-clock me-1 is-3166ff78"></i>{{ $p['periode'] }}</div>
              @endif
            </div>
          @endforeach
        </div>
      @endforeach
    </div>

    @if($skNomor || $skTanggal)
      <div class="mt-4 pt-3 border-top d-flex flex-wrap gap-3">
        @if($skNomor)
          <div class="sk-badge">
            <i class="bi bi-file-earmark-text text-warning"></i>
            <span><strong>No. SK:</strong> {{ $skNomor }}</span>
          </div>
        @endif
        @if($skTanggal)
          <div class="sk-badge">
            <i class="bi bi-calendar-check text-primary"></i>
            <span><strong>Tanggal SK:</strong> {{ \Carbon\Carbon::parse($skTanggal)->isoFormat('D MMMM Y') }}</span>
          </div>
        @endif
      </div>
    @endif
  @endif
</div>
@endsection
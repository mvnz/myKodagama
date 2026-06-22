@extends('layouts.aplikasi')

@section('judul', 'Detail Log Aktivitas')

@push('css')
<link rel="stylesheet" href="{{ asset('css/pages/log-detail.css') }}">
@endpush

@section('konten')

<div class="mb-3">
  <a href="{{ route('log.index') }}" class="btn btn-outline-secondary btn-sm is-36afef86">
    <i class="bi bi-arrow-left me-1"></i> Kembali ke Log
  </a>
</div>

{{-- INFO UTAMA --}}
<div class="detail-log-card">
  <div class="detail-log-header">
    <i class="bi bi-clock-history fs-5 is-bc519fd0"></i>
    <h6>Detail Aktivitas</h6>
    <span class="aksi-pill aksi-{{ $log->aksi }} ms-auto">
      <i class="bi {{ $log->ikonAksi() }}"></i> {{ ucfirst($log->aksi) }}
    </span>
  </div>
  <div class="detail-log-body">
    <div class="info-row">
      <div class="info-label">Waktu</div>
      <div class="info-value">
        <strong>{{ $log->created_at->format('d M Y') }}</strong>
        &nbsp;pukul <strong>{{ $log->created_at->format('H:i:s') }}</strong>
        <span class="text-muted ms-2 is-6c660c40">({{ $log->created_at->diffForHumans() }})</span>
      </div>
    </div>
    <div class="info-row">
      <div class="info-label">Pengguna</div>
      <div class="info-value d-flex align-items-center gap-2">
        @if($log->pengguna)
          <div class="is-89a7fc37">
            {{ mb_substr($log->pengguna->name, 0, 2) }}
          </div>
          <div>
            <strong>{{ $log->pengguna->name }}</strong>
            <span class="text-muted ms-1 is-0de4e77b">({{ ucfirst($log->pengguna->role) }})</span>
          </div>
        @else
          <span class="text-muted">–</span>
        @endif
      </div>
    </div>
    <div class="info-row">
      <div class="info-label">Modul</div>
      <div class="info-value"><span class="modul-chip">{{ $log->modul }}</span></div>
    </div>
    <div class="info-row">
      <div class="info-label">Data Terkait</div>
      <div class="info-value"><strong>{{ $log->label ?? '–' }}</strong></div>
    </div>
    <div class="info-row">
      <div class="info-label">Keterangan</div>
      <div class="info-value">{{ $log->keterangan ?? '–' }}</div>
    </div>
    <div class="info-row">
      <div class="info-label">IP Address</div>
      <div class="info-value">
        <code class="is-4057a0b0">
          {{ $log->ip_address ?? '–' }}
        </code>
      </div>
    </div>
  </div>
</div>

{{-- DIFF DATA --}}
@if($log->data_lama || $log->data_baru)
<div class="row g-3">
  @if($log->data_lama)
  <div class="{{ $log->data_baru ? 'col-md-6' : 'col-12' }}">
    <div class="diff-card">
      <div class="diff-card-header lama">
        <i class="bi bi-dash-circle-fill"></i> Data Sebelum
      </div>
      <pre>{{ json_encode($log->data_lama, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
    </div>
  </div>
  @endif
  @if($log->data_baru)
  <div class="{{ $log->data_lama ? 'col-md-6' : 'col-12' }}">
    <div class="diff-card">
      <div class="diff-card-header baru">
        <i class="bi bi-plus-circle-fill"></i> Data Sesudah
      </div>
      <pre>{{ json_encode($log->data_baru, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
    </div>
  </div>
  @endif
</div>
@endif

@endsection
@extends('layouts.aplikasi')

@section('judul', 'History Log Aktivitas')

@push('css')
<link rel="stylesheet" href="{{ asset('css/pages/log.css') }}">
@endpush

@section('konten')

@php
  $sortBy = $sortBy ?? request('sort', 'created_at');
  $sortDir = $sortDir ?? request('dir', 'desc');
@endphp

{{-- Page Banner --}}
<div class="dashboard-welcome mb-4">
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
    <div>
      <div class="welcome-label mb-1">Riwayat Aktivitas</div>
      <h4 class="mb-1 fw-700">Log Aktivitas Sistem</h4>
      <p class="mb-0 welcome-date">
        <i class="bi bi-calendar3 me-1"></i>
        {{ now()->translatedFormat('l, d F Y') }}
      </p>
    </div>
    <div class="welcome-icon-wrap">
      <i class="bi bi-journal-text"></i>
    </div>
  </div>
</div>

{{-- STAT CARDS --}}
<div class="row g-4 mb-4">
  <div class="col-xl-3 col-sm-6">
    <div class="kstat-card kstat-blue">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-journal-text"></i></div>
      <div class="kstat-value">{{ number_format($statTotal) }}</div>
      <div class="kstat-label">Total Log</div>
      <div class="kstat-trend"><i class="bi bi-clock-history me-1"></i> Semua aktivitas</div>
    </div>
  </div>
  <div class="col-xl-3 col-sm-6">
    <div class="kstat-card kstat-green">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-calendar-check-fill"></i></div>
      <div class="kstat-value">{{ number_format($statHariIni) }}</div>
      <div class="kstat-label">Hari Ini</div>
      <div class="kstat-trend"><i class="bi bi-calendar-day-fill me-1"></i> Aktivitas hari ini</div>
    </div>
  </div>
  <div class="col-xl-3 col-sm-6">
    <div class="kstat-card kstat-amber">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-calendar-week-fill"></i></div>
      <div class="kstat-value">{{ number_format($statMingguIni) }}</div>
      <div class="kstat-label">Minggu Ini</div>
      <div class="kstat-trend"><i class="bi bi-calendar3 me-1"></i> 7 hari terakhir</div>
    </div>
  </div>
  <div class="col-xl-3 col-sm-6">
    <div class="kstat-card kstat-red">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-bar-chart-fill"></i></div>
      <div class="kstat-value is-ab66dd84">
        {{ $statPerAksi->isEmpty() ? '—' : ucfirst($statPerAksi->sortDesc()->keys()->first()) }}
      </div>
      <div class="kstat-label">Aksi Terbanyak</div>
      <div class="kstat-trend"><i class="bi bi-lightning-fill me-1"></i> Aktivitas dominan</div>
    </div>
  </div>
</div>

{{-- FILTER --}}
<div class="form-card mb-4">
  <form method="GET" action="{{ route('log.index') }}">
    <div class="row g-3 align-items-end">
      <div class="col">
        <label class="form-label"><i class="bi bi-search me-1"></i>Cari</label>
        <input type="text" name="cari" class="form-control" placeholder="Nama, keterangan..." value="{{ $cari ?? '' }}">
      </div>
      <div class="col-md-2">
        <label class="form-label"><i class="bi bi-grid-3x3-gap me-1"></i>Modul</label>
        <select name="modul" class="form-select">
          <option value="">Semua Modul</option>
          @foreach($modulList as $m)
            <option value="{{ $m }}" {{ ($modul ?? '') === $m ? 'selected' : '' }}>{{ $m }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label"><i class="bi bi-lightning me-1"></i>Aksi</label>
        <select name="aksi" class="form-select">
          <option value="">Semua Aksi</option>
          @foreach($aksiList as $a)
            <option value="{{ $a }}" {{ ($aksi ?? '') === $a ? 'selected' : '' }}>{{ ucfirst($a) }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label"><i class="bi bi-calendar me-1"></i>Dari</label>
        <input type="date" name="dari" class="form-control" value="{{ $dari ?? '' }}">
      </div>
      <div class="col-md-2">
        <label class="form-label"><i class="bi bi-calendar-check me-1"></i>Sampai</label>
        <input type="date" name="sampai" class="form-control" value="{{ $sampai ?? '' }}">
      </div>
      <div class="col-md-auto d-flex gap-2 align-items-end">
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-funnel me-1"></i> Filter
        </button>
        @if($cari || $modul || $aksi || $dari || $sampai)
          <a href="{{ route('log.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-x-circle me-1"></i> Reset
          </a>
        @endif
      </div>
    </div>
  </form>
</div>

{{-- TABLE --}}
<div class="log-table-card">
  <div class="log-table-header">
    <div class="log-table-title">
      <i class="bi bi-clock-history is-bc519fd0"></i>
      Riwayat Aktivitas
      <span class="badge ms-1 is-eb0227cb">
        {{ $log->total() }}
      </span>
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
      @foreach(['tambah','ubah','hapus','bayar','setujui','tolak'] as $k)
        @if(($statPerAksi[$k] ?? 0) > 0)
          <span class="aksi-pill aksi-{{ $k }} is-bc9efe64">
            {{ ucfirst($k) }}&nbsp;{{ $statPerAksi[$k] }}×
          </span>
        @endif
      @endforeach
    </div>
  </div>

  <div class="table-responsive">
    @php
      $sUrl = fn($k) => request()->fullUrlWithQuery(['sort' => $k, 'dir' => ($sortBy===$k && $sortDir==='asc') ? 'desc' : 'asc']);
      $sIco = fn($k) => $sortBy===$k
        ? ($sortDir==='asc'
            ? '<i class="bi bi-sort-up ms-1 is-0a789cad"></i>'
            : '<i class="bi bi-sort-down ms-1 is-0a789cad"></i>')
        : '<i class="bi bi-arrow-down-up text-muted opacity-50 ms-1 is-bc9efe64"></i>';
    @endphp
    <table class="log-tabel w-100 mb-0">
      <thead>
        <tr>
          <th class="is-0d0b0fe8">#</th>
          <th class="is-729192c0"><a href="{{ $sUrl('created_at') }}" class="text-decoration-none text-reset d-inline-flex align-items-center">Waktu {!! $sIco('created_at') !!}</a></th>
          <th>Pengguna</th>
          <th class="is-1a30558f"><a href="{{ $sUrl('aksi') }}" class="text-decoration-none text-reset d-inline-flex align-items-center">Aksi {!! $sIco('aksi') !!}</a></th>
          <th class="is-1a30558f"><a href="{{ $sUrl('modul') }}" class="text-decoration-none text-reset d-inline-flex align-items-center">Modul {!! $sIco('modul') !!}</a></th>
          <th>Keterangan</th>
          <th class="is-088cf005">IP Address</th>
          <th class="is-de266d63"></th>
        </tr>
      </thead>
      <tbody>
        @forelse($log as $item)
        <tr>
          <td class="is-8ffd3619">
            {{ $loop->iteration + ($log->currentPage() - 1) * $log->perPage() }}
          </td>
          <td>
            <div class="is-099dcab2">{{ $item->created_at->format('H:i:s') }}</div>
            <div class="is-22508fb3">{{ $item->created_at->format('d M Y') }}</div>
          </td>
          <td>
            <div class="d-flex align-items-center gap-2">
              <div class="user-avatar">{{ $item->pengguna ? mb_substr($item->pengguna->name, 0, 2) : '??' }}</div>
              <div>
                <div class="is-6200193f">
                  {{ $item->pengguna?->name ?? 'â€“' }}
                </div>
                <div class="is-ff79c0b3">{{ ucfirst($item->pengguna?->role ?? '') }}</div>
              </div>
            </div>
          </td>
          <td>
            <span class="aksi-pill aksi-{{ $item->aksi }}">
              <i class="bi {{ $item->ikonAksi() }}"></i> {{ ucfirst($item->aksi) }}
            </span>
          </td>
          <td><span class="modul-chip">{{ $item->modul }}</span></td>
          <td>
            @if($item->label)
              <div class="is-a90fa8ce">{{ $item->label }}</div>
            @endif
            @if($item->keterangan)
              <div class="is-dadd1655">{{ $item->keterangan }}</div>
            @endif
          </td>
          <td>
            <code class="is-98b211d2">
              {{ $item->ip_address ?? 'â€“' }}
            </code>
          </td>
          <td>
            @if($item->data_lama || $item->data_baru)
              <a href="{{ route('log.show', $item) }}" class="btn-log-detail" title="Lihat detail">
                <i class="bi bi-eye"></i>
              </a>
            @endif
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="8">
            <div class="log-empty">
              <i class="bi bi-inbox"></i>
              <div class="is-53aa8842">Belum ada log aktivitas</div>
              <div class="is-1e28117f">Log muncul otomatis saat ada aksi tambah, ubah, atau hapus.</div>
            </div>
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  @if($log->hasPages())
  <div class="px-4 py-3 border-top is-45e002f8">
    {{ $log->links() }}
  </div>
  @endif
</div>

@endsection
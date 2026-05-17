@extends('layouts.aplikasi')

@section('judul', 'Detail Log Aktivitas')

@push('css')
<style>
.detail-log-card {
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0,0,0,.06);
  border: 1px solid #e8eef8;
  overflow: hidden;
  margin-bottom: 1.5rem;
}
.detail-log-header {
  padding: 1.2rem 1.6rem;
  border-bottom: 1px solid #f0f4ff;
  background: #fafbff;
  display: flex; align-items: center; gap: .75rem;
}
.detail-log-header h6 {
  margin: 0; font-family: 'Montserrat', sans-serif;
  font-weight: 700; font-size: 1rem; color: #112344;
}
.detail-log-body { padding: 1.6rem; }

.info-row { display: flex; gap: 1rem; padding: .6rem 0; border-bottom: 1px solid #f3f6ff; align-items: flex-start; }
.info-row:last-child { border-bottom: none; }
.info-label { width: 150px; flex-shrink: 0; font-size: .8rem; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: .4px; padding-top: .1rem; }
.info-value { font-size: .9rem; color: #112344; }

.aksi-pill {
  display: inline-flex; align-items: center; gap: .3rem;
  padding: .35rem .85rem; border-radius: 50px;
  font-size: .78rem; font-weight: 600; white-space: nowrap;
}
.aksi-tambah  { background:#d1fae5; color:#065f46; }
.aksi-ubah    { background:#fef3c7; color:#92400e; }
.aksi-hapus   { background:#fee2e2; color:#991b1b; }
.aksi-bayar   { background:#dbeafe; color:#1e40af; }
.aksi-setujui { background:#ede9fe; color:#5b21b6; }
.aksi-tolak   { background:#f3f4f6; color:#374151; }

.modul-chip {
  padding: .3rem .8rem; border-radius: 8px;
  font-size: .78rem; font-weight: 600;
  background: #f0f4ff; color: #175cdd;
  border: 1px solid #dbe8ff;
}

.diff-card {
  border-radius: 12px;
  overflow: hidden;
  border: 1px solid #e8eef8;
}
.diff-card-header {
  padding: .7rem 1rem;
  font-size: .82rem; font-weight: 700;
  display: flex; align-items: center; gap: .4rem;
}
.diff-card-header.lama { background:#fff5f5; color:#991b1b; border-bottom: 1px solid #fecaca; }
.diff-card-header.baru { background:#f0fdf4; color:#065f46; border-bottom: 1px solid #bbf7d0; }
.diff-card pre {
  margin: 0; padding: 1rem;
  font-size: .78rem; line-height: 1.7;
  max-height: 380px; overflow: auto;
  background: #fff; color: #374151;
}
</style>
@endpush

@section('konten')

<div class="mb-3">
  <a href="{{ route('log.index') }}" class="btn btn-outline-secondary btn-sm" style="border-radius:9px">
    <i class="bi bi-arrow-left me-1"></i> Kembali ke Log
  </a>
</div>

{{-- INFO UTAMA --}}
<div class="detail-log-card">
  <div class="detail-log-header">
    <i class="bi bi-clock-history fs-5" style="color:#175cdd"></i>
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
        <span class="text-muted ms-2" style="font-size:.8rem">({{ $log->created_at->diffForHumans() }})</span>
      </div>
    </div>
    <div class="info-row">
      <div class="info-label">Pengguna</div>
      <div class="info-value d-flex align-items-center gap-2">
        @if($log->pengguna)
          <div style="width:30px;height:30px;border-radius:50%;background:linear-gradient(135deg,#175cdd,#8b5cf6);color:#fff;font-size:.7rem;font-weight:700;display:flex;align-items:center;justify-content:center">
            {{ mb_substr($log->pengguna->name, 0, 2) }}
          </div>
          <div>
            <strong>{{ $log->pengguna->name }}</strong>
            <span class="text-muted ms-1" style="font-size:.82rem">({{ ucfirst($log->pengguna->role) }})</span>
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
        <code style="background:#f3f4f6;padding:2px 8px;border-radius:6px;font-size:.83rem;color:#374151">
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

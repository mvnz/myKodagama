@extends('layouts.aplikasi')

@section('judul', 'History Log Aktivitas')

@push('css')
<style>
/* â”€â”€ Stat Cards Log â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
.log-stat-card {
  background: #fff;
  border-radius: 16px;
  padding: 1.4rem 1.6rem;
  box-shadow: 0 4px 20px rgba(0,0,0,.06);
  border: 1px solid rgba(23,92,221,.08);
  display: flex;
  align-items: center;
  gap: 1.1rem;
  position: relative;
  overflow: hidden;
  transition: transform .2s, box-shadow .2s;
}
.log-stat-card:hover { transform: translateY(-3px); box-shadow: 0 10px 30px rgba(23,92,221,.12); }
.log-stat-card::before {
  content: '';
  position: absolute;
  top: 0; left: 0; right: 0;
  height: 3px;
}
.log-stat-card.card-biru::before  { background: linear-gradient(90deg,#175cdd,#3b7ae0); }
.log-stat-card.card-hijau::before { background: linear-gradient(90deg,#10b981,#34d399); }
.log-stat-card.card-ungu::before  { background: linear-gradient(90deg,#8b5cf6,#a78bfa); }
.log-stat-card.card-oranye::before{ background: linear-gradient(90deg,#f59e0b,#fbbf24); }
.log-stat-icon {
  width: 52px; height: 52px;
  border-radius: 14px;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.4rem; flex-shrink: 0;
}
.log-stat-label { font-size: .77rem; color: #6b7280; font-weight: 600; text-transform: uppercase; letter-spacing: .5px; }
.log-stat-value { font-size: 1.75rem; font-weight: 700; color: #112344; line-height: 1.2; }

/* â”€â”€ Filter Card â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
.filter-card {
  background: #fff;
  border-radius: 16px;
  padding: 1.3rem 1.5rem;
  box-shadow: 0 4px 20px rgba(0,0,0,.05);
  border: 1px solid #e8eef8;
  margin-bottom: 1.5rem;
}
.filter-card .form-label { font-size: .78rem; font-weight: 600; color: #374151; margin-bottom: .3rem; }
.filter-card .form-control,
.filter-card .form-select {
  border: 1.5px solid #e5e7eb;
  border-radius: 9px;
  font-size: .86rem;
  padding: .5rem .85rem;
  transition: border-color .2s, box-shadow .2s;
}
.filter-card .form-control:focus,
.filter-card .form-select:focus {
  border-color: #175cdd;
  box-shadow: 0 0 0 3px rgba(23,92,221,.1);
}

/* â”€â”€ Log Table Card â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
.log-table-card {
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0,0,0,.06);
  border: 1px solid #e8eef8;
  overflow: hidden;
}
.log-table-header {
  padding: 1.1rem 1.5rem;
  border-bottom: 1px solid #f0f4ff;
  display: flex; align-items: center; justify-content: space-between;
  background: #fafbff;
}
.log-table-title {
  font-family: 'Montserrat', sans-serif;
  font-weight: 700; font-size: 1rem; color: #112344;
  display: flex; align-items: center; gap: .5rem;
}

/* â”€â”€ Aksi Pills â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
.aksi-pill {
  display: inline-flex; align-items: center; gap: .3rem;
  padding: .3rem .75rem; border-radius: 50px;
  font-size: .75rem; font-weight: 600; white-space: nowrap;
}
.aksi-tambah  { background:#d1fae5; color:#065f46; }
.aksi-ubah    { background:#fef3c7; color:#92400e; }
.aksi-hapus   { background:#fee2e2; color:#991b1b; }
.aksi-bayar   { background:#dbeafe; color:#1e40af; }
.aksi-setujui { background:#ede9fe; color:#5b21b6; }
.aksi-tolak   { background:#f3f4f6; color:#374151; }

/* â”€â”€ Modul Chip â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
.modul-chip {
  padding: .25rem .65rem; border-radius: 8px;
  font-size: .73rem; font-weight: 600;
  background: #f0f4ff; color: #175cdd;
  border: 1px solid #dbe8ff; white-space: nowrap;
}

/* â”€â”€ Avatar Initials â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
.user-avatar {
  width: 32px; height: 32px; border-radius: 50%;
  background: linear-gradient(135deg,#175cdd,#8b5cf6);
  color: #fff; font-size: .72rem; font-weight: 700;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0; text-transform: uppercase;
}

/* â”€â”€ Tabel Row â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
.log-tabel thead th {
  background: #f5f8ff; color: #112344;
  font-weight: 700; font-size: .75rem;
  text-transform: uppercase; letter-spacing: .55px;
  border-bottom: 2px solid #e5ecfa;
  padding: .9rem 1rem; white-space: nowrap;
}
.log-tabel tbody td {
  padding: .82rem 1rem; vertical-align: middle;
  border-bottom: 1px solid #f3f6ff; font-size: .875rem;
}
.log-tabel tbody tr:last-child td { border-bottom: none; }
.log-tabel tbody tr:hover { background: #f8fbff; }

/* â”€â”€ Detail Btn â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
.btn-log-detail {
  width: 30px; height: 30px; border-radius: 8px;
  border: 1.5px solid #e5e7eb; background: #fff; color: #6b7280;
  display: inline-flex; align-items: center; justify-content: center;
  font-size: .85rem; transition: all .2s;
}
.btn-log-detail:hover { border-color: #175cdd; color: #175cdd; background: #f0f4ff; }

/* â”€â”€ Empty State â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
.log-empty { padding: 3.5rem 1rem; text-align: center; color: #9ca3af; }
.log-empty i { font-size: 2.8rem; display: block; margin-bottom: .75rem; opacity: .4; }
</style>
@endpush

@section('konten')

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
      <div class="kstat-value" style="font-size:1.4rem">
        {{ $statPerAksi->isEmpty() ? '—' : ucfirst($statPerAksi->sortDesc()->keys()->first()) }}
      </div>
      <div class="kstat-label">Aksi Terbanyak</div>
      <div class="kstat-trend"><i class="bi bi-lightning-fill me-1"></i> Aktivitas dominan</div>
    </div>
  </div>
</div>

{{-- FILTER --}}
<div class="filter-card">
  <form method="GET" action="{{ route('log.index') }}">
    <div class="row g-2 align-items-end">
      <div class="col-md-3">
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
      <div class="col-md-1 d-flex gap-1">
        <button type="submit" class="btn btn-primary flex-fill" title="Filter" style="border-radius:9px">
          <i class="bi bi-funnel-fill"></i>
        </button>
        @if($cari || $modul || $aksi || $dari || $sampai)
          <a href="{{ route('log.index') }}" class="btn btn-outline-secondary flex-fill" title="Reset" style="border-radius:9px">
            <i class="bi bi-x-lg"></i>
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
      <i class="bi bi-clock-history" style="color:#175cdd"></i>
      Riwayat Aktivitas
      <span class="badge ms-1" style="background:#175cdd;font-size:.72rem;border-radius:50px">
        {{ $log->total() }}
      </span>
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
      @foreach(['tambah','ubah','hapus','bayar','setujui','tolak'] as $k)
        @if(($statPerAksi[$k] ?? 0) > 0)
          <span class="aksi-pill aksi-{{ $k }}" style="font-size:.7rem">
            {{ ucfirst($k) }}&nbsp;{{ $statPerAksi[$k] }}×
          </span>
        @endif
      @endforeach
    </div>
  </div>

  <div class="table-responsive">
    <table class="log-tabel w-100 mb-0">
      <thead>
        <tr>
          <th style="width:45px">#</th>
          <th style="width:130px">Waktu</th>
          <th>Pengguna</th>
          <th style="width:110px">Aksi</th>
          <th style="width:110px">Modul</th>
          <th>Keterangan</th>
          <th style="width:120px">IP Address</th>
          <th style="width:46px"></th>
        </tr>
      </thead>
      <tbody>
        @forelse($log as $item)
        <tr>
          <td style="color:#9ca3af;font-size:.78rem">
            {{ $loop->iteration + ($log->currentPage() - 1) * $log->perPage() }}
          </td>
          <td>
            <div style="font-size:.83rem;font-weight:600;color:#112344">{{ $item->created_at->format('H:i:s') }}</div>
            <div style="font-size:.73rem;color:#9ca3af">{{ $item->created_at->format('d M Y') }}</div>
          </td>
          <td>
            <div class="d-flex align-items-center gap-2">
              <div class="user-avatar">{{ $item->pengguna ? mb_substr($item->pengguna->name, 0, 2) : '??' }}</div>
              <div>
                <div style="font-weight:600;font-size:.85rem;color:#112344;line-height:1.2">
                  {{ $item->pengguna?->name ?? 'â€“' }}
                </div>
                <div style="font-size:.72rem;color:#9ca3af">{{ ucfirst($item->pengguna?->role ?? '') }}</div>
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
              <div style="font-weight:600;font-size:.85rem;color:#112344">{{ $item->label }}</div>
            @endif
            @if($item->keterangan)
              <div style="font-size:.8rem;color:#6b7280">{{ $item->keterangan }}</div>
            @endif
          </td>
          <td>
            <code style="font-size:.74rem;color:#6b7280;background:#f3f4f6;padding:2px 7px;border-radius:5px">
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
              <div style="font-weight:600;color:#374151;margin-bottom:.3rem">Belum ada log aktivitas</div>
              <div style="font-size:.85rem">Log muncul otomatis saat ada aksi tambah, ubah, atau hapus.</div>
            </div>
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  @if($log->hasPages())
  <div class="px-4 py-3 border-top" style="background:#fafbff">
    {{ $log->links() }}
  </div>
  @endif
</div>

@endsection

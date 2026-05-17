@extends('layouts.aplikasi')

@section('judul', 'Struktur Organisasi')

@push('css')
<style>
/* ===== ORG CHART WRAPPER ===== */
.orgchart-area {
  background: linear-gradient(160deg, #f0f6ff 0%, #f8faff 60%, #fff 100%);
  border-radius: 18px;
  border: 1px solid #e3eaff;
  padding: 2.5rem 1.5rem 2rem;
  overflow-x: auto;
}

/* ===== LEVEL ROWS ===== */
.org-level-row {
  display: flex;
  justify-content: center;
  align-items: flex-start;
  flex-wrap: wrap;
  gap: 1.25rem;
}

/* ===== CONNECTORS ===== */
.org-vline {
  width: 2px;
  height: 36px;
  background: linear-gradient(to bottom, #c7d8ff, #a5b4fc);
  margin: 0 auto;
}
.org-hline-wrap {
  display: flex;
  justify-content: center;
  align-items: center;
  position: relative;
  height: 0;
  margin-bottom: 36px;
}
.org-hline-wrap::before {
  content: '';
  position: absolute;
  top: 0;
  left: 50%;
  transform: translateX(-50%);
  width: 2px;
  height: 36px;
  background: linear-gradient(to bottom, #a5b4fc, #c7d8ff);
}

/* ===== CARD BASE ===== */
.org-card {
  border-radius: 18px;
  padding: 1.4rem 1.2rem 1.1rem;
  min-width: 145px;
  max-width: 170px;
  text-align: center;
  position: relative;
  overflow: hidden;
  transition: transform .2s, box-shadow .2s;
}
.org-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 32px rgba(0,0,0,.2) !important;
}
.org-card::before {
  content: '';
  position: absolute;
  top: -28px; right: -28px;
  width: 90px; height: 90px;
  border-radius: 50%;
  background: rgba(255,255,255,.1);
}
.org-card::after {
  content: '';
  position: absolute;
  bottom: -20px; left: -20px;
  width: 70px; height: 70px;
  border-radius: 50%;
  background: rgba(255,255,255,.07);
}

/* ===== CARD INNER ===== */
.org-card-avatar {
  width: 48px; height: 48px;
  border-radius: 50%;
  background: rgba(255,255,255,.22);
  display: flex; align-items: center; justify-content: center;
  margin: 0 auto .8rem;
  font-size: 1.4rem;
  color: rgba(255,255,255,.9);
  position: relative; z-index: 1;
  border: 2px solid rgba(255,255,255,.3);
}
.org-card-jabatan {
  font-size: .67rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: .08em;
  opacity: .85;
  margin-bottom: .35rem;
  position: relative; z-index: 1;
}
.org-card-nama {
  font-size: .9rem;
  font-weight: 700;
  line-height: 1.3;
  position: relative; z-index: 1;
}
.org-card-periode {
  font-size: .68rem;
  margin-top: .4rem;
  opacity: .72;
  position: relative; z-index: 1;
  display: inline-block;
  background: rgba(0,0,0,.15);
  border-radius: 20px;
  padding: .1rem .55rem;
}

/* ===== COLOR VARIANTS ===== */
.org-baris-1 { background: linear-gradient(135deg,#0f2a5c 0%,#175cdd 100%); color:#fff; box-shadow: 0 8px 28px rgba(23,92,221,.4); }
.org-baris-2 { background: linear-gradient(135deg,#312e81 0%,#6366f1 100%); color:#fff; box-shadow: 0 8px 28px rgba(99,102,241,.35); }
.org-baris-3 { background: linear-gradient(135deg,#064e3b 0%,#10b981 100%); color:#fff; box-shadow: 0 8px 28px rgba(16,185,129,.35); }
.org-baris-4 { background: linear-gradient(135deg,#92400e 0%,#f59e0b 100%); color:#fff; box-shadow: 0 8px 28px rgba(245,158,11,.35); }
.org-baris-5 { background: linear-gradient(135deg,#4c1d95 0%,#8b5cf6 100%); color:#fff; box-shadow: 0 8px 28px rgba(139,92,246,.35); }
.org-baris-other { background: linear-gradient(135deg,#1e293b 0%,#475569 100%); color:#fff; box-shadow: 0 8px 28px rgba(30,41,59,.3); }

/* ===== SK BADGE ===== */
.sk-badge {
  display: inline-flex;
  align-items: center;
  gap: .5rem;
  background: #f0f6ff;
  border: 1px solid #c3d9ff;
  border-radius: 12px;
  padding: .5rem 1rem;
  font-size: .82rem;
  color: #1e3a6e;
}
</style>
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
      <i class="bi bi-people" style="font-size:3rem;opacity:.25"></i>
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
                <div class="org-card-periode"><i class="bi bi-clock me-1" style="font-size:.6rem"></i>{{ $p['periode'] }}</div>
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
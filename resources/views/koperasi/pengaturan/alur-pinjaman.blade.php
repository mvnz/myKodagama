@extends('layouts.aplikasi')

@section('judul', 'Alur Persetujuan Pinjaman')

@section('breadcrumb')
  <li class="breadcrumb-item">Pengaturan</li>
  <li class="breadcrumb-item active">Alur Pinjaman</li>
@endsection

@push('css')
<style>
  .flow-wrap {
    display: grid;
    grid-template-columns: repeat(7, minmax(0, 1fr));
    gap: 12px;
    align-items: center;
    padding: 12px;
    border-radius: 16px;
    background:
      radial-gradient(circle at 95% 5%, rgba(37, 99, 235, .12), transparent 42%),
      linear-gradient(180deg, #f8fbff 0%, #f3f8ff 100%);
    border: 1px solid #dbe8ff;
  }

  .flow-step {
    position: relative;
    background: rgba(255, 255, 255, .94);
    border: 1px solid #d8e5fb;
    border-radius: 16px;
    padding: 16px 12px 14px;
    text-align: center;
    min-height: 134px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    box-shadow: 0 10px 24px rgba(17, 35, 68, .1);
    transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
  }

  .flow-step:hover {
    transform: translateY(-3px);
    box-shadow: 0 14px 30px rgba(17, 35, 68, .16);
    border-color: #b7cff7;
  }

  .flow-step .flow-step-num {
    position: absolute;
    top: 8px;
    left: 8px;
    font-size: .66rem;
    letter-spacing: .08em;
    font-weight: 800;
    color: #2b4d86;
    background: #e7efff;
    border: 1px solid #c8d9ff;
    border-radius: 999px;
    padding: 2px 8px;
  }

  .flow-step .flow-icon {
    width: 46px;
    height: 46px;
    border-radius: 50%;
    margin: 0 auto 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 1.05rem;
    font-weight: 700;
    box-shadow: 0 8px 16px rgba(20, 37, 68, .24);
  }

  .flow-step .flow-title {
    font-weight: 700;
    color: #112344;
    line-height: 1.25;
  }

  .flow-step .flow-sub {
    margin-top: 3px;
    font-size: .75rem;
    color: #7a8cae;
  }

  .flow-step .flow-role {
    margin-top: 9px;
    display: inline-block;
    padding: 3px 11px;
    border-radius: 999px;
    font-size: .7rem;
    font-weight: 600;
    border: 1px solid #bfd6ff;
    background: linear-gradient(180deg, #f8fbff 0%, #eef5ff 100%);
    color: #234a8f;
  }

  .flow-arrow {
    text-align: center;
    color: #5a7fbe;
    font-size: 1.1rem;
    user-select: none;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .flow-arrow::before {
    content: "";
    position: absolute;
    width: 74%;
    height: 2px;
    background: linear-gradient(90deg, #b9cef6 0%, #7ea3eb 100%);
    border-radius: 999px;
    top: 50%;
    left: 13%;
    transform: translateY(-50%);
    z-index: 0;
  }

  .flow-arrow i {
    position: relative;
    z-index: 1;
    background: #f4f8ff;
    border: 1px solid #c6d9ff;
    border-radius: 999px;
    padding: 2px 6px;
  }

  .flow-anggota .flow-icon { background: linear-gradient(135deg, #2563eb, #1d4ed8); }
  .flow-verif .flow-icon { background: linear-gradient(135deg, #f59e0b, #d97706); }
  .flow-approve .flow-icon { background: linear-gradient(135deg, #0ea5a2, #0f766e); }
  .flow-cair .flow-icon { background: linear-gradient(135deg, #16a34a, #15803d); }

  @media (max-width: 992px) {
    .flow-wrap {
      grid-template-columns: 1fr;
      gap: 8px;
    }

    .flow-arrow {
      transform: none;
      line-height: 1;
    }

    .flow-arrow::before {
      width: 2px;
      height: 22px;
      left: 50%;
      top: 0;
      transform: translateX(-50%);
      background: linear-gradient(180deg, #b9cef6 0%, #7ea3eb 100%);
    }

    .flow-arrow i {
      transform: rotate(90deg);
    }

    .flow-step {
      min-height: 104px;
    }
  }
</style>
@endpush

@section('konten')
<div class="row justify-content-center">
  <div class="col-xl-9">

    {{-- Page Banner --}}
    <div class="dashboard-welcome mb-4">
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div>
          <div class="welcome-label mb-1">Pengaturan Sistem</div>
          <h4 class="mb-1 fw-700">Alur Persetujuan Pinjaman</h4>
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

    @if(session('sukses'))
      <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert">
        <i class="bi bi-check-circle-fill"></i>
        <span>{{ session('sukses') }}</span>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
      </div>
    @endif

    @if($errors->any())
      <div class="alert alert-danger alert-dismissible fade show d-flex align-items-start gap-2 mb-4" role="alert">
        <i class="bi bi-exclamation-triangle-fill mt-1"></i>
        <div><strong>Ada kesalahan:</strong>
          <ul class="mb-0 mt-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
      </div>
    @endif

    {{-- Diagram alur (visualisasi) --}}
    <div class="form-card mb-4">
      <h6 class="fw-700 mb-4 d-flex align-items-center gap-2 is-bc519fd0">
        <i class="bi bi-diagram-3-fill"></i> Visualisasi Alur Saat Ini
      </h6>
      <div class="flow-wrap py-1">
        @php
          $vRole = $verifikatorRole ? $roles->firstWhere('slug', $verifikatorRole)?->nama ?? $verifikatorRole : '—';
          $aRole = $approverRole    ? $roles->firstWhere('slug', $approverRole)?->nama  ?? $approverRole    : '—';
        @endphp

        <div class="flow-step flow-anggota">
          <span class="flow-step-num">01</span>
          <div class="flow-icon"><i class="bi bi-person-fill"></i></div>
          <div class="flow-title">Anggota</div>
          <div class="flow-sub">Mengajukan pinjaman</div>
        </div>

        <div class="flow-arrow"><i class="bi bi-arrow-right"></i></div>

        <div class="flow-step flow-verif">
          <span class="flow-step-num">02</span>
          <div class="flow-icon"><i class="bi bi-patch-check-fill"></i></div>
          <div class="flow-title">Verifikasi</div>
          <div class="flow-sub">Cek data dan kelengkapan</div>
          <span class="flow-role">{{ $vRole }}</span>
        </div>

        <div class="flow-arrow"><i class="bi bi-arrow-right"></i></div>

        <div class="flow-step flow-approve">
          <span class="flow-step-num">03</span>
          <div class="flow-icon"><i class="bi bi-check2-circle"></i></div>
          <div class="flow-title">Persetujuan</div>
          <div class="flow-sub">Keputusan akhir pinjaman</div>
          <span class="flow-role">{{ $aRole }}</span>
        </div>

        <div class="flow-arrow"><i class="bi bi-arrow-right"></i></div>

        <div class="flow-step flow-cair">
          <span class="flow-step-num">04</span>
          <div class="flow-icon"><i class="bi bi-cash-coin"></i></div>
          <div class="flow-title">Dana Cair</div>
          <div class="flow-sub">Sistem buat jadwal angsuran</div>
        </div>
      </div>
    </div>

    {{-- Form konfigurasi --}}
    <form action="{{ route('pengaturan.alur-pinjaman.update') }}" method="POST">
      @csrf
      @method('PUT')

      <div class="form-card">
        <h6 class="fw-700 mb-4 d-flex align-items-center gap-2 is-5ca88772">
          <i class="bi bi-sliders me-1 is-bc519fd0"></i> Konfigurasi Role Berjenjang
        </h6>

        <div class="row g-4">

          {{-- ── Langkah 1: Verifikasi ────────────────────────────── --}}
          <div class="col-md-6">
            <div class="p-4 rounded-3 border h-100 is-934c8791"
             >
              <div class="d-flex align-items-center gap-3 mb-3 pb-3 border-bottom is-884f0b88">
                <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0 is-73c9ab73"
                 >
                  <i class="bi bi-patch-check-fill is-f5bb42f5"></i>
                </div>
                <div>
                  <div class="fw-700 is-ade5e435">Langkah 1: Verifikasi</div>
                  <div class="text-muted is-7bc1a737">Role yang memeriksa kelengkapan berkas</div>
                </div>
              </div>

              <label class="form-label fw-600 is-5ca88772">Pilih Role Verifikator</label>
              <select name="verifikator_role" class="form-select @error('verifikator_role') is-invalid @enderror">
                <option value="">-- Pilih Role --</option>
                @foreach($roles as $role)
                  <option value="{{ $role->slug }}"
                    {{ old('verifikator_role', $verifikatorRole) === $role->slug ? 'selected' : '' }}>
                    {{ $role->nama }}
                    @if($role->deskripsi) — {{ \Illuminate\Support\Str::limit($role->deskripsi, 35) }} @endif
                  </option>
                @endforeach
              </select>
              @error('verifikator_role')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
              <div class="form-text mt-2">
                <i class="bi bi-info-circle me-1 text-info"></i>
                Pengguna dengan role ini akan melihat tombol <strong>Verifikasi</strong> pada halaman detail pinjaman.
              </div>
            </div>
          </div>

          {{-- ── Langkah 2: Persetujuan ───────────────────────────── --}}
          <div class="col-md-6">
            <div class="p-4 rounded-3 border h-100 is-13e01680"
             >
              <div class="d-flex align-items-center gap-3 mb-3 pb-3 border-bottom is-89627fe1">
                <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0 is-7fd14ced"
                 >
                  <i class="bi bi-check2-circle is-1af34acb"></i>
                </div>
                <div>
                  <div class="fw-700 is-d4ac9708">Langkah 2: Persetujuan</div>
                  <div class="text-muted is-7bc1a737">Role yang memberikan persetujuan akhir</div>
                </div>
              </div>

              <label class="form-label fw-600 is-5ca88772">Pilih Role Approver</label>
              <select name="approver_role" class="form-select @error('approver_role') is-invalid @enderror">
                <option value="">-- Pilih Role --</option>
                @foreach($roles as $role)
                  <option value="{{ $role->slug }}"
                    {{ old('approver_role', $approverRole) === $role->slug ? 'selected' : '' }}>
                    {{ $role->nama }}
                    @if($role->deskripsi) — {{ \Illuminate\Support\Str::limit($role->deskripsi, 35) }} @endif
                  </option>
                @endforeach
              </select>
              @error('approver_role')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
              <div class="form-text mt-2">
                <i class="bi bi-info-circle me-1 text-success"></i>
                Pengguna dengan role ini akan melihat tombol <strong>Setujui</strong> pada halaman detail pinjaman.
              </div>
            </div>
          </div>
        </div>

        {{-- Catatan admin bypass --}}
        <div class="alert alert-warning d-flex align-items-start gap-2 mt-4 mb-0 is-9ee0bb07">
          <i class="bi bi-shield-fill-check mt-1 flex-shrink-0 is-0c118ea4"></i>
          <div>
            <strong>Catatan:</strong> Role <span class="badge is-7bd5b3b8">Admin</span>
            selalu dapat memverifikasi dan menyetujui pinjaman terlepas dari konfigurasi di atas.
            Konfigurasi ini berlaku untuk role lainnya.
          </div>
        </div>

        <div class="d-flex gap-2 mt-4 pt-4 border-top">
          <button type="submit" class="btn btn-primary px-4">
            <i class="bi bi-check-lg me-2"></i> Simpan Konfigurasi
          </button>
          <a href="{{ route('pengaturan.simpan-pinjam') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-2"></i> Ke Simpan Pinjam
          </a>
        </div>
      </div>
    </form>

  </div>
</div>
@endsection

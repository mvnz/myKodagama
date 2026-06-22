@extends('layouts.aplikasi')

@section('judul', 'Alur Persetujuan Pinjaman')

@section('breadcrumb')
  <li class="breadcrumb-item">Pengaturan</li>
  <li class="breadcrumb-item active">Alur Pinjaman</li>
@endsection

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
      <div class="d-flex align-items-center justify-content-center flex-wrap gap-2 py-2">
        @php
          $vRole = $verifikatorRole ? $roles->firstWhere('slug', $verifikatorRole)?->nama ?? $verifikatorRole : '—';
          $aRole = $approverRole    ? $roles->firstWhere('slug', $approverRole)?->nama  ?? $approverRole    : '—';
        @endphp

        {{-- Node: Anggota --}}
        <div class="text-center is-ec46ce82">
          <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2 is-d7e2a924"
           >
            <i class="bi bi-person-fill is-0d2a9139"></i>
          </div>
          <div class="is-540b70cb">Anggota</div>
          <div class="is-be4fbb3a">Pengajuan</div>
        </div>

        <div class="is-7b21330b"></div>

        {{-- Node: Verifikator --}}
        <div class="text-center is-e5a6788e">
          <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2 is-ff579043"
           >
            <i class="bi bi-patch-check-fill is-5360a6df"></i>
          </div>
          <div class="is-479ede28">Verifikasi</div>
          <div class="badge rounded-pill mt-1 is-370bf44e"
           >
            {{ $vRole }}
          </div>
        </div>

        <div class="is-f8ee3704"></div>

        {{-- Node: Approver --}}
        <div class="text-center is-e5a6788e">
          <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2 is-d223a0de"
           >
            <i class="bi bi-check2-circle is-8fb9d369"></i>
          </div>
          <div class="is-82474a11">Persetujuan</div>
          <div class="badge rounded-pill mt-1 is-ffa89392"
           >
            {{ $aRole }}
          </div>
        </div>

        <div class="is-6c70baf7"></div>

        {{-- Node: Cair --}}
        <div class="text-center is-ec46ce82">
          <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2 is-f9110219"
           >
            <i class="bi bi-cash-coin is-a4c5c709"></i>
          </div>
          <div class="is-540b70cb">Dana Cair</div>
          <div class="is-be4fbb3a">Jadwal angsuran</div>
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

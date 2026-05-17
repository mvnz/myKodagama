@extends('layouts.aplikasi')

@section('judul', 'Pengaturan Pengurus')

@section('konten')
<form action="{{ route('pengaturan.pengurus.update') }}" method="POST">
  @csrf
  @method('PUT')

  @if(session('sukses'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
      <i class="bi bi-check-circle-fill me-2"></i>{{ session('sukses') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  {{-- Page Banner --}}
  <div class="dashboard-welcome mb-4">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
      <div>
        <div class="welcome-label mb-1">Pengaturan Sistem</div>
        <h4 class="mb-1 fw-700">Data Pengurus Koperasi</h4>
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

  {{-- Struktur Pengurus --}}
  <div class="form-card mb-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
      <h6 class="section-title mb-0">
        <i class="bi bi-diagram-3-fill me-2" style="color:#175cdd"></i>Struktur Pengurus
      </h6>
      <button type="button" class="btn btn-sm btn-success" onclick="tambahPosisi()">
        <i class="bi bi-plus-circle me-1"></i> Tambah Posisi
      </button>
    </div>

    {{-- Header kolom --}}
    <div class="d-none d-md-flex gap-2 mb-2 px-1" style="font-size:.72rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.05em">
      <div style="flex:2.5">Jabatan / Posisi</div>
      <div style="flex:2.5">Nama</div>
      <div style="flex:2">Periode</div>
      <div style="width:110px;text-align:center">Baris</div>
      <div style="width:38px"></div>
    </div>

    <div id="strukturContainer" class="d-flex flex-column gap-2">
      @foreach($struktur as $i => $s)
        <div class="row g-2 align-items-center posisi-item">
          <div class="col-12 col-md">
            <input type="text" name="struktur[{{ $i }}][jabatan]" value="{{ $s['jabatan'] }}"
              class="form-control" placeholder="Jabatan (cth: Ketua, Manajer, Koordinator...)">
          </div>
          <div class="col-12 col-md">
            <input type="text" name="struktur[{{ $i }}][nama]" value="{{ $s['nama'] }}"
              class="form-control" placeholder="Nama lengkap">
          </div>
          <div class="col-12 col-md" style="flex:2">
            <input type="text" name="struktur[{{ $i }}][periode]" value="{{ $s['periode'] }}"
              class="form-control" placeholder="cth: 2024-2027" maxlength="50">
          </div>
          <div class="col-6 col-md-auto" style="width:110px">
            <select name="struktur[{{ $i }}][baris]" class="form-select form-select-sm" title="Baris di org chart">
              @for($b = 1; $b <= 10; $b++)
                <option value="{{ $b }}" {{ (int)($s['baris'] ?? 1) === $b ? 'selected' : '' }}>Baris {{ $b }}</option>
              @endfor
            </select>
          </div>
          <div class="col-6 col-md-auto" style="width:38px">
            <button type="button" class="btn btn-sm btn-outline-danger px-2" onclick="hapusPosisi(this)" title="Hapus">
              <i class="bi bi-trash"></i>
            </button>
          </div>
        </div>
      @endforeach
    </div>

    <div class="mt-3 pt-3 border-top">
      <small class="text-muted">
        <i class="bi bi-info-circle me-1"></i>
        Posisi dengan <strong>Baris</strong> yang sama akan ditampilkan sejajar dalam struktur organisasi.
        Baris 1 = paling atas.
      </small>
    </div>
  </div>

  {{-- SK Pengurus --}}
  <div class="form-card mb-4">
    <h6 class="section-title mb-4"><i class="bi bi-file-earmark-text me-2" style="color:#f59e0b"></i>SK Pengurus</h6>
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Nomor SK</label>
        <input type="text" name="pengaturan[pgrs_sk_nomor]"
          value="{{ old('pengaturan.pgrs_sk_nomor', $pengaturan->get('pgrs_sk_nomor')?->nilai) }}"
          class="form-control" placeholder="contoh: 01/SK/RAT/KOP/2024" maxlength="100">
      </div>
      <div class="col-md-6">
        <label class="form-label">Tanggal SK</label>
        <input type="date" name="pengaturan[pgrs_sk_tanggal]"
          value="{{ old('pengaturan.pgrs_sk_tanggal', $pengaturan->get('pgrs_sk_tanggal')?->nilai) }}"
          class="form-control">
      </div>
    </div>
  </div>

  <div class="d-flex justify-content-end gap-2">
    <button type="submit" class="btn btn-primary px-4">
      <i class="bi bi-check-lg me-1"></i> Simpan Perubahan
    </button>
  </div>
</form>
@endsection

@push('js')
<script>
let posisiCount = {{ count($struktur) }};

function tambahPosisi() {
  const container = document.getElementById('strukturContainer');
  const idx = posisiCount++;
  let options = '';
  for (let b = 1; b <= 10; b++) {
    options += `<option value="${b}"${b === 1 ? ' selected' : ''}>Baris ${b}</option>`;
  }
  const div = document.createElement('div');
  div.className = 'row g-2 align-items-center posisi-item';
  div.innerHTML = `
    <div class="col-12 col-md">
      <input type="text" name="struktur[${idx}][jabatan]" class="form-control"
        placeholder="Jabatan (cth: Ketua, Manajer, Koordinator...)">
    </div>
    <div class="col-12 col-md">
      <input type="text" name="struktur[${idx}][nama]" class="form-control"
        placeholder="Nama lengkap">
    </div>
    <div class="col-12 col-md" style="flex:2">
      <input type="text" name="struktur[${idx}][periode]" class="form-control"
        placeholder="cth: 2024-2027" maxlength="50">
    </div>
    <div class="col-6 col-md-auto" style="width:110px">
      <select name="struktur[${idx}][baris]" class="form-select form-select-sm" title="Baris di org chart">
        ${options}
      </select>
    </div>
    <div class="col-6 col-md-auto" style="width:38px">
      <button type="button" class="btn btn-sm btn-outline-danger px-2"
        onclick="hapusPosisi(this)" title="Hapus">
        <i class="bi bi-trash"></i>
      </button>
    </div>`;
  container.appendChild(div);
  div.querySelector('input').focus();
}

function hapusPosisi(btn) {
  const container = document.getElementById('strukturContainer');
  if (container.querySelectorAll('.posisi-item').length <= 1) return;
  btn.closest('.posisi-item').remove();
}
</script>
@endpush
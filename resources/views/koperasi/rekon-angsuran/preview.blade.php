@extends('layouts.aplikasi')

@section('judul', 'Pratinjau Rekon Angsuran')

@section('konten')
@php
  $totalOk    = collect($hasil)->where('status', 'ok')->count();
  $totalGagal = collect($hasil)->where('status', 'gagal')->count();
@endphp

<div class="row">
  <div class="col-12">

    {{-- Ringkasan --}}
    <div class="row g-3 mb-4">
      <div class="col-md-4">
        <div class="stat-card p-3 rounded-3 d-flex align-items-center gap-3 is-7c98676d">
          <div class="d-flex align-items-center justify-content-center rounded-3 is-87e4e8fd"
           >
            <i class="bi bi-file-earmark-text-fill is-cb3e2554"></i>
          </div>
          <div>
            <div class="fw-700 fs-5 is-5ca88772">{{ count($hasil) }}</div>
            <small class="text-muted">Total Baris</small>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="stat-card p-3 rounded-3 d-flex align-items-center gap-3 is-7c98676d">
          <div class="d-flex align-items-center justify-content-center rounded-3 is-a9e2592f"
           >
            <i class="bi bi-check-circle-fill is-cee33bce"></i>
          </div>
          <div>
            <div class="fw-700 fs-5 is-29fb9df2">{{ $totalOk }}</div>
            <small class="text-muted">Siap Diimpor</small>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="stat-card p-3 rounded-3 d-flex align-items-center gap-3 is-7c98676d">
          <div class="d-flex align-items-center justify-content-center rounded-3 is-c302c598"
           >
            <i class="bi bi-exclamation-triangle-fill is-c3e045d0"></i>
          </div>
          <div>
            <div class="fw-700 fs-5 is-c9e34862">{{ $totalGagal }}</div>
            <small class="text-muted">Tidak Cocok</small>
          </div>
        </div>
      </div>
    </div>

    @if($totalOk === 0)
    <div class="alert alert-warning">
      <i class="bi bi-exclamation-triangle-fill me-2"></i>
      Tidak ada baris yang berhasil dicocokkan. Periksa kembali format berita dan pemetaan kolom.
    </div>
    <a href="{{ route('rekon-angsuran.index') }}" class="btn btn-outline-warning">
      <i class="bi bi-arrow-left me-1"></i> Kembali ke Unggah
    </a>
    @else

    <form action="{{ route('rekon-angsuran.import') }}" method="POST" id="formImport">
      @csrf

      <div class="form-card p-0">
        <div class="d-flex align-items-center justify-content-between p-4 border-bottom flex-wrap gap-2">
          <div>
            <h6 class="fw-700 mb-0 is-5ca88772">
              <i class="bi bi-table me-2"></i>Pratinjau Data
            </h6>
            <small class="text-muted">Pilih baris angsuran yang ingin diimpor</small>
          </div>
          <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" id="btnPilihSemua">
              <i class="bi bi-check-all me-1"></i>Pilih Semua
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="btnBatalSemua">
              <i class="bi bi-x-lg me-1"></i>Batal Semua
            </button>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-hover mb-0 is-6bf47011">
            <thead class="is-bf063d1c">
              <tr>
                <th class="ps-4 is-93de2d4b"></th>
                <th>Tanggal</th>
                <th>Berita / Keterangan</th>
                <th>Nomor Anggota</th>
                <th>Nama</th>
                <th>No. Pinjaman</th>
                <th class="text-center">Angsuran Ke</th>
                <th class="text-end">Tagihan</th>
                <th class="text-end pe-4">Nominal CSV</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              @foreach($hasil as $i => $row)
              <tr class="{{ $row['status'] === 'gagal' ? 'table-danger opacity-75' : '' }}">
                <td class="ps-4">
                  @if($row['status'] === 'ok')
                  <input type="checkbox" name="dipilih[]" value="{{ $i }}"
                    class="form-check-input chk-baris" checked>
                  @endif
                </td>
                <td>{{ $row['tanggal'] }}</td>
                <td
                  title="{{ $row['berita'] }}" class="is-cf1012e4">{{ $row['berita'] }}</td>
                <td>
                  @if($row['nomor_anggota'])
                    <code>{{ $row['nomor_anggota'] }}</code>
                  @else
                    <span class="text-danger">—</span>
                  @endif
                </td>
                <td>{{ $row['nama_anggota'] ?? '—' }}</td>
                <td>
                  @if($row['nomor_pinjaman'])
                    <code>{{ $row['nomor_pinjaman'] }}</code>
                  @else
                    <span class="text-muted">—</span>
                  @endif
                </td>
                <td class="text-center">
                  @if($row['angsuran_ke'])
                    <span class="badge bg-secondary">ke-{{ $row['angsuran_ke'] }}</span>
                  @else
                    <span class="text-muted">—</span>
                  @endif
                </td>
                <td class="text-end fw-600">
                  @if($row['jumlah_tagihan'])
                    Rp {{ number_format($row['jumlah_tagihan'], 0, ',', '.') }}
                  @else
                    <span class="text-muted">—</span>
                  @endif
                </td>
                <td class="text-end pe-4 is-d2abea56">
                  Rp {{ number_format($row['nominal'], 0, ',', '.') }}
                </td>
                <td>
                  @if($row['status'] === 'ok')
                    <span class="badge bg-success"><i class="bi bi-check-lg me-1"></i>OK</span>
                  @else
                    <span class="badge bg-danger" title="{{ $row['alasan'] }}">
                      <i class="bi bi-x-lg me-1"></i>Lewati
                    </span>
                    <small class="d-block text-danger is-640fb1f6">{{ $row['alasan'] }}</small>
                  @endif
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        <div class="p-4 border-top d-flex gap-2 align-items-center flex-wrap">
          <button type="submit" class="btn btn-warning px-4 text-white">
            <i class="bi bi-cloud-upload-fill me-2"></i>Import Data Dipilih
          </button>
          <a href="{{ route('rekon-angsuran.index') }}" class="btn btn-outline-secondary px-4">
            <i class="bi bi-arrow-left me-1"></i>Unggah Ulang
          </a>
          <span class="ms-2 text-muted is-6bf47011" id="infoTerpilih"></span>
        </div>
      </div>
    </form>

    @endif
  </div>
</div>
@endsection

@push('js')
<script>
  const checkboxes = document.querySelectorAll('.chk-baris');
  const infoEl     = document.getElementById('infoTerpilih');

  function updateInfo() {
    const n = document.querySelectorAll('.chk-baris:checked').length;
    if (infoEl) infoEl.textContent = n + ' baris dipilih';
  }

  checkboxes.forEach(cb => cb.addEventListener('change', updateInfo));
  updateInfo();

  document.getElementById('btnPilihSemua')?.addEventListener('click', () => {
    checkboxes.forEach(cb => cb.checked = true);
    updateInfo();
  });

  document.getElementById('btnBatalSemua')?.addEventListener('click', () => {
    checkboxes.forEach(cb => cb.checked = false);
    updateInfo();
  });
</script>
@endpush

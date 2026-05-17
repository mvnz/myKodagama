@extends('layouts.aplikasi')

@section('judul', 'Pengaturan Simpan Pinjam')

@section('breadcrumb')
  <li class="breadcrumb-item">Pengaturan</li>
  <li class="breadcrumb-item active">Simpan Pinjam</li>
@endsection

@section('konten')

<div class="row justify-content-center">
  <div class="col-xl-10">

    {{-- Page Banner --}}
    <div class="dashboard-welcome mb-4">
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div>
          <div class="welcome-label mb-1">Pengaturan Sistem</div>
          <h4 class="mb-1 fw-700">Pengaturan Simpan Pinjam</h4>
          <p class="mb-0 welcome-date">
            <i class="bi bi-calendar3 me-1"></i>
            {{ now()->translatedFormat('l, d F Y') }}
          </p>
        </div>
        <div class="welcome-icon-wrap">
          <i class="bi bi-bank2"></i>
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

    <form action="{{ route('pengaturan.simpan-pinjam.update') }}" method="POST">
      @csrf
      @method('PUT')

      <div class="row g-4">

        {{-- ── Simpanan ─────────────────────────────────────────── --}}
        <div class="col-12">
          <div class="form-card">
            <h6 class="fw-700 mb-3 d-flex align-items-center gap-2" style="color:#175cdd">
              <i class="bi bi-piggy-bank-fill"></i> Ketentuan Simpanan
            </h6>
            <div class="row g-3">

              <div class="col-md-4">
                <label class="form-label">Nominal Simpanan Pokok (Rp)</label>
                <div class="input-group">
                  <span class="input-group-text">Rp</span>
                  <input type="number" class="form-control @error('pengaturan.sp_simpanan_pokok') is-invalid @enderror"
                    name="pengaturan[sp_simpanan_pokok]"
                    value="{{ old('pengaturan.sp_simpanan_pokok', $pengaturan->get('sp_simpanan_pokok')?->nilai) }}"
                    min="0" step="1000" placeholder="100000">
                </div>
                <div class="form-text">Dibayar sekali saat pertama bergabung.</div>
                @error('pengaturan.sp_simpanan_pokok')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-4">
                <label class="form-label">Bunga Simpanan Sukarela (% / tahun)</label>
                <div class="input-group">
                  <input type="number" class="form-control @error('pengaturan.sp_bunga_simpanan') is-invalid @enderror"
                    name="pengaturan[sp_bunga_simpanan]"
                    value="{{ old('pengaturan.sp_bunga_simpanan', $pengaturan->get('sp_bunga_simpanan')?->nilai) }}"
                    min="0" max="100" step="0.1" placeholder="5">
                  <span class="input-group-text">% / tahun</span>
                </div>
                <div class="form-text">Bunga yang diberikan untuk simpanan sukarela.</div>
                @error('pengaturan.sp_bunga_simpanan')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

            </div>

            {{-- Simpanan Wajib per Jenis Anggota --}}
            <hr class="my-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
              <div>
                <h6 class="mb-0 fw-700" style="color:#112344">
                  <i class="bi bi-table me-1" style="color:#175cdd"></i>
                  Simpanan Wajib per Jenis Anggota
                </h6>
                <small class="text-muted">Nominal simpanan wajib bulanan berbeda sesuai jenis keanggotaan</small>
              </div>
              <button type="button" class="btn btn-sm btn-outline-primary" id="btnTambahJenis">
                <i class="bi bi-plus-lg me-1"></i> Tambah Jenis
              </button>
            </div>

            <div class="table-responsive">
              <table class="koperasi-table mb-0" id="tabelWajibJenis">
                <thead>
                  <tr>
                    <th style="width:50%">Jenis Anggota</th>
                    <th>Nominal Wajib / Bulan (Rp)</th>
                    <th class="text-center" style="width:60px"></th>
                  </tr>
                </thead>
                <tbody id="bodyWajibJenis">
                  @foreach($wajibPerJenis as $i => $item)
                    <tr>
                      <td>
                        <input type="text" class="form-control form-control-sm"
                          name="wajib_per_jenis[{{ $i }}][jenis]"
                          value="{{ $item['jenis'] }}"
                          placeholder="cth: Anggota Biasa" required>
                      </td>
                      <td>
                        <div class="input-group input-group-sm">
                          <span class="input-group-text">Rp</span>
                          <input type="number" class="form-control"
                            name="wajib_per_jenis[{{ $i }}][nominal]"
                            value="{{ $item['nominal'] }}"
                            min="0" step="1000" placeholder="50000">
                        </div>
                      </td>
                      <td class="text-center">
                        <button type="button" class="btn btn-sm btn-outline-danger px-2 btn-hapus-jenis" title="Hapus">
                          <i class="bi bi-trash"></i>
                        </button>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
            <div class="form-text mt-2">
              <i class="bi bi-info-circle me-1"></i>
              Jenis anggota ini akan tampil sebagai pilihan saat menambah/mengubah data anggota.
            </div>

          </div>
        </div>

        {{-- ── Pinjaman ─────────────────────────────────────────── --}}
        <div class="col-12">
          <div class="form-card">
            <h6 class="fw-700 mb-3 d-flex align-items-center gap-2" style="color:#175cdd">
              <i class="bi bi-cash-stack"></i> Ketentuan Pinjaman
            </h6>
            <div class="row g-3">

              <div class="col-md-4">
                <label class="form-label">Bunga Pinjaman (% / bulan)</label>
                <div class="input-group">
                  <input type="number" class="form-control @error('pengaturan.sp_bunga_pinjaman') is-invalid @enderror"
                    name="pengaturan[sp_bunga_pinjaman]"
                    value="{{ old('pengaturan.sp_bunga_pinjaman', $pengaturan->get('sp_bunga_pinjaman')?->nilai) }}"
                    min="0" max="100" step="0.1" placeholder="1.5">
                  <span class="input-group-text">% / bulan</span>
                </div>
                <div class="form-text">Bunga yang dikenakan per bulan atas saldo pinjaman.</div>
                @error('pengaturan.sp_bunga_pinjaman')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-4">
                <label class="form-label">Minimum Jumlah Pinjaman (Rp)</label>
                <div class="input-group">
                  <span class="input-group-text">Rp</span>
                  <input type="number" class="form-control @error('pengaturan.sp_min_pinjaman') is-invalid @enderror"
                    name="pengaturan[sp_min_pinjaman]"
                    value="{{ old('pengaturan.sp_min_pinjaman', $pengaturan->get('sp_min_pinjaman')?->nilai) }}"
                    min="0" step="100000" placeholder="500000">
                </div>
                @error('pengaturan.sp_min_pinjaman')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-4">
                <label class="form-label">Maksimum Jumlah Pinjaman (Rp)</label>
                <div class="input-group">
                  <span class="input-group-text">Rp</span>
                  <input type="number" class="form-control @error('pengaturan.sp_maks_pinjaman') is-invalid @enderror"
                    name="pengaturan[sp_maks_pinjaman]"
                    value="{{ old('pengaturan.sp_maks_pinjaman', $pengaturan->get('sp_maks_pinjaman')?->nilai) }}"
                    min="0" step="1000000" placeholder="50000000">
                </div>
                @error('pengaturan.sp_maks_pinjaman')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-4">
                <label class="form-label">Minimum Jangka Waktu (bulan)</label>
                <div class="input-group">
                  <input type="number" class="form-control @error('pengaturan.sp_min_jangka') is-invalid @enderror"
                    name="pengaturan[sp_min_jangka]"
                    value="{{ old('pengaturan.sp_min_jangka', $pengaturan->get('sp_min_jangka')?->nilai) }}"
                    min="1" step="1" placeholder="3">
                  <span class="input-group-text">bulan</span>
                </div>
                @error('pengaturan.sp_min_jangka')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-4">
                <label class="form-label">Maksimum Jangka Waktu (bulan)</label>
                <div class="input-group">
                  <input type="number" class="form-control @error('pengaturan.sp_maks_jangka') is-invalid @enderror"
                    name="pengaturan[sp_maks_jangka]"
                    value="{{ old('pengaturan.sp_maks_jangka', $pengaturan->get('sp_maks_jangka')?->nilai) }}"
                    min="1" step="1" placeholder="36">
                  <span class="input-group-text">bulan</span>
                </div>
                @error('pengaturan.sp_maks_jangka')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-4">
                <label class="form-label">Batas Kelipatan Pinjaman dari Simpanan</label>
                <div class="input-group">
                  <input type="number" class="form-control @error('pengaturan.sp_batas_kelipatan_pinjaman') is-invalid @enderror"
                    name="pengaturan[sp_batas_kelipatan_pinjaman]"
                    value="{{ old('pengaturan.sp_batas_kelipatan_pinjaman', $pengaturan->get('sp_batas_kelipatan_pinjaman')?->nilai) }}"
                    min="1" step="1" placeholder="5">
                  <span class="input-group-text">× simpanan</span>
                </div>
                <div class="form-text">Contoh: nilai 5 berarti maks. pinjaman = 5× total simpanan.</div>
                @error('pengaturan.sp_batas_kelipatan_pinjaman')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

            </div>
          </div>
        </div>

        {{-- ── Jenis Pinjaman ──────────────────────────────────── --}}
        <div class="col-12">
          <div class="form-card">
            <div class="d-flex align-items-center justify-content-between mb-3">
              <div>
                <h6 class="mb-0 fw-700 d-flex align-items-center gap-2" style="color:#175cdd">
                  <i class="bi bi-tags-fill"></i> Jenis Pinjaman
                </h6>
                <small class="text-muted">Setiap jenis pinjaman memiliki bunga dan batas tersendiri. Akan tampil sebagai pilihan saat pengajuan.</small>
              </div>
              <button type="button" class="btn btn-sm btn-outline-primary" id="btnTambahJenisPinjaman">
                <i class="bi bi-plus-lg me-1"></i> Tambah Jenis
              </button>
            </div>

            <div class="table-responsive">
              <table class="koperasi-table mb-0" id="tabelJenisPinjaman">
                <thead>
                  <tr>
                    <th style="min-width:160px">Nama Jenis</th>
                    <th style="min-width:110px">Bunga (% / bln)</th>
                    <th style="min-width:130px">Min. Pinjaman (Rp)</th>
                    <th style="min-width:130px">Maks. Pinjaman (Rp)</th>
                    <th style="min-width:110px">Min. Jangka (bln)</th>
                    <th style="min-width:110px">Maks. Jangka (bln)</th>
                    <th style="width:50px"></th>
                  </tr>
                </thead>
                <tbody id="bodyJenisPinjaman">
                  @foreach($jenisPinjaman as $i => $jp)
                    <tr>
                      <td>
                        <input type="text" class="form-control form-control-sm"
                          name="jenis_pinjaman[{{ $i }}][nama]"
                          value="{{ $jp['nama'] }}"
                          placeholder="cth: Pinjaman Reguler" required>
                      </td>
                      <td>
                        <div class="input-group input-group-sm">
                          <input type="number" class="form-control"
                            name="jenis_pinjaman[{{ $i }}][bunga]"
                            value="{{ $jp['bunga'] }}"
                            min="0" max="100" step="0.1" placeholder="1.5">
                          <span class="input-group-text">%</span>
                        </div>
                      </td>
                      <td>
                        <div class="input-group input-group-sm">
                          <span class="input-group-text">Rp</span>
                          <input type="number" class="form-control"
                            name="jenis_pinjaman[{{ $i }}][min_pinjaman]"
                            value="{{ $jp['min_pinjaman'] }}"
                            min="0" step="100000" placeholder="500000">
                        </div>
                      </td>
                      <td>
                        <div class="input-group input-group-sm">
                          <span class="input-group-text">Rp</span>
                          <input type="number" class="form-control"
                            name="jenis_pinjaman[{{ $i }}][maks_pinjaman]"
                            value="{{ $jp['maks_pinjaman'] }}"
                            min="0" step="1000000" placeholder="10000000">
                        </div>
                      </td>
                      <td>
                        <div class="input-group input-group-sm">
                          <input type="number" class="form-control"
                            name="jenis_pinjaman[{{ $i }}][min_jangka]"
                            value="{{ $jp['min_jangka'] }}"
                            min="1" step="1" placeholder="3">
                          <span class="input-group-text">bln</span>
                        </div>
                      </td>
                      <td>
                        <div class="input-group input-group-sm">
                          <input type="number" class="form-control"
                            name="jenis_pinjaman[{{ $i }}][maks_jangka]"
                            value="{{ $jp['maks_jangka'] }}"
                            min="1" step="1" placeholder="24">
                          <span class="input-group-text">bln</span>
                        </div>
                      </td>
                      <td>
                        <button type="button" class="btn btn-sm btn-outline-danger btn-hapus-jenis-pinjaman" title="Hapus">
                          <i class="bi bi-trash3"></i>
                        </button>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
            @if(empty($jenisPinjaman))
              <p class="text-muted mt-3 mb-0" id="kosongJenisPinjaman">
                <i class="bi bi-info-circle me-1"></i>Belum ada jenis pinjaman. Klik "Tambah Jenis" untuk menambahkan.
              </p>
            @endif
          </div>
        </div>

        {{-- ── Denda ────────────────────────────────────────────── --}}
        <div class="col-12">
          <div class="form-card">
            <h6 class="fw-700 mb-3 d-flex align-items-center gap-2" style="color:#175cdd">
              <i class="bi bi-exclamation-triangle-fill"></i> Denda & Sanksi
            </h6>
            <div class="row g-3">

              <div class="col-md-6">
                <label class="form-label">Denda Keterlambatan Angsuran (% / hari)</label>
                <div class="input-group">
                  <input type="number" class="form-control @error('pengaturan.sp_denda_keterlambatan') is-invalid @enderror"
                    name="pengaturan[sp_denda_keterlambatan]"
                    value="{{ old('pengaturan.sp_denda_keterlambatan', $pengaturan->get('sp_denda_keterlambatan')?->nilai) }}"
                    min="0" max="10" step="0.01" placeholder="0.1">
                  <span class="input-group-text">% / hari</span>
                </div>
                <div class="form-text">Denda administratif per hari (untuk referensi / informasi).</div>
                @error('pengaturan.sp_denda_keterlambatan')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-6">
                <label class="form-label">Bunga Keterlambatan (% / bulan)</label>
                <div class="input-group">
                  <input type="number" class="form-control @error('pengaturan.sp_bunga_keterlambatan') is-invalid @enderror"
                    name="pengaturan[sp_bunga_keterlambatan]"
                    value="{{ old('pengaturan.sp_bunga_keterlambatan', $pengaturan->get('sp_bunga_keterlambatan')?->nilai) }}"
                    min="0" max="30" step="0.1" placeholder="2">
                  <span class="input-group-text">% / bulan</span>
                </div>
                <div class="form-text">Dikenakan pada cicilan yang terlambat — dihitung harian dari bunga bulanan ini.</div>
                @error('pengaturan.sp_bunga_keterlambatan')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

            </div>
          </div>
        </div>

      </div>{{-- /row --}}

      {{-- Actions --}}
      <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-primary px-4">
          <i class="bi bi-check-lg me-1"></i> Simpan Perubahan
        </button>
        <a href="{{ route('pengaturan.aplikasi') }}" class="btn btn-outline-secondary px-4">
          <i class="bi bi-sliders me-1"></i> Ke Pengaturan Aplikasi
        </a>
      </div>

    </form>

  </div>
</div>
@endsection

@push('js')
<script>
(function () {
  const tbody = document.getElementById('bodyWajibJenis');
  if (!tbody) return;

  function reindex() {
    tbody.querySelectorAll('tr').forEach((tr, i) => {
      tr.querySelectorAll('[name]').forEach(el => {
        el.name = el.name.replace(/wajib_per_jenis\[\d+\]/, `wajib_per_jenis[${i}]`);
      });
    });
  }

  function buatBaris(idx) {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>
        <input type="text" class="form-control form-control-sm"
          name="wajib_per_jenis[${idx}][jenis]"
          placeholder="cth: Anggota Biasa" required>
      </td>
      <td>
        <div class="input-group input-group-sm">
          <span class="input-group-text">Rp</span>
          <input type="number" class="form-control"
            name="wajib_per_jenis[${idx}][nominal]"
            value="0" min="0" step="1000">
        </div>
      </td>
      <td class="text-center">
        <button type="button" class="btn btn-sm btn-outline-danger px-2 btn-hapus-jenis" title="Hapus">
          <i class="bi bi-trash"></i>
        </button>
      </td>`;
    return tr;
  }

  document.getElementById('btnTambahJenis').addEventListener('click', () => {
    const idx = tbody.querySelectorAll('tr').length;
    const tr = buatBaris(idx);
    tbody.appendChild(tr);
    tr.querySelector('input[type=text]').focus();
  });

  tbody.addEventListener('click', e => {
    const btn = e.target.closest('.btn-hapus-jenis');
    if (!btn) return;
    if (tbody.querySelectorAll('tr').length <= 1) return;
    btn.closest('tr').remove();
    reindex();
  });
})();

// ── Jenis Pinjaman ─────────────────────────────────────────────
(function () {
  const tbodyJP   = document.getElementById('bodyJenisPinjaman');
  const kosong    = document.getElementById('kosongJenisPinjaman');
  if (!tbodyJP) return;

  function reindexJP() {
    tbodyJP.querySelectorAll('tr').forEach((tr, i) => {
      tr.querySelectorAll('[name]').forEach(el => {
        el.name = el.name.replace(/jenis_pinjaman\[\d+\]/, `jenis_pinjaman[${i}]`);
      });
    });
  }

  function buatBarisJP(idx) {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td><input type="text" class="form-control form-control-sm" name="jenis_pinjaman[${idx}][nama]" placeholder="cth: Pinjaman Reguler" required></td>
      <td><div class="input-group input-group-sm">
        <input type="number" class="form-control" name="jenis_pinjaman[${idx}][bunga]" min="0" max="100" step="0.1" placeholder="1.5">
        <span class="input-group-text">%</span>
      </div></td>
      <td><div class="input-group input-group-sm">
        <span class="input-group-text">Rp</span>
        <input type="number" class="form-control" name="jenis_pinjaman[${idx}][min_pinjaman]" min="0" step="100000" placeholder="500000">
      </div></td>
      <td><div class="input-group input-group-sm">
        <span class="input-group-text">Rp</span>
        <input type="number" class="form-control" name="jenis_pinjaman[${idx}][maks_pinjaman]" min="0" step="1000000" placeholder="10000000">
      </div></td>
      <td><div class="input-group input-group-sm">
        <input type="number" class="form-control" name="jenis_pinjaman[${idx}][min_jangka]" min="1" step="1" placeholder="3">
        <span class="input-group-text">bln</span>
      </div></td>
      <td><div class="input-group input-group-sm">
        <input type="number" class="form-control" name="jenis_pinjaman[${idx}][maks_jangka]" min="1" step="1" placeholder="24">
        <span class="input-group-text">bln</span>
      </div></td>
      <td>
        <button type="button" class="btn btn-sm btn-outline-danger btn-hapus-jenis-pinjaman" title="Hapus">
          <i class="bi bi-trash3"></i>
        </button>
      </td>`;
    return tr;
  }

  document.getElementById('btnTambahJenisPinjaman').addEventListener('click', () => {
    if (kosong) kosong.style.display = 'none';
    const idx = tbodyJP.querySelectorAll('tr').length;
    const tr  = buatBarisJP(idx);
    tbodyJP.appendChild(tr);
    tr.querySelector('input[type=text]').focus();
  });

  tbodyJP.addEventListener('click', e => {
    const btn = e.target.closest('.btn-hapus-jenis-pinjaman');
    if (!btn) return;
    btn.closest('tr').remove();
    reindexJP();
    if (kosong && tbodyJP.querySelectorAll('tr').length === 0) kosong.style.display = '';
  });
})();
</script>
@endpush

@extends('layouts.aplikasi')

@section('judul', 'Ajukan Pinjaman')

@section('konten')
<div class="row justify-content-center">
  <div class="col-xl-10">
    <div class="form-card">
      <div class="d-flex align-items-center gap-3 mb-4 pb-4 border-bottom">
        <div class="d-flex align-items-center justify-content-center rounded-3"
          style="width:50px;height:50px;background:rgba(245,158,11,.1)">
          <i class="bi bi-cash-coin" style="color:#f59e0b;font-size:1.4rem"></i>
        </div>
        <div>
          <h5 class="mb-0 fw-700" style="color:#112344">Ajukan Pinjaman Baru</h5>
          <small class="text-muted">Isi formulir pengajuan pinjaman anggota</small>
        </div>
      </div>

      <form action="{{ route('pinjaman.store') }}" method="POST" id="formPinjaman">
        @csrf
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label">Anggota <span class="text-danger">*</span></label>
            @if($anggotaSaya)
              <input type="hidden" name="anggota_id" value="{{ $anggotaSaya->id }}">
              <input type="text" class="form-control" value="{{ $anggotaSaya->nomor_anggota }} — {{ $anggotaSaya->nama }}" readonly>
            @else
              <select name="anggota_id" class="form-select @error('anggota_id') is-invalid @enderror">
                <option value="">-- Pilih Anggota --</option>
                @foreach($anggota as $a)
                  <option value="{{ $a->id }}"
                    {{ old('anggota_id', request('anggota_id')) == $a->id ? 'selected' : '' }}>
                    {{ $a->nomor_anggota }} — {{ $a->nama }}
                  </option>
                @endforeach
              </select>
              @error('anggota_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            @endif
          </div>

          {{-- Jenis Pinjaman --}}
          @if($jenisPinjaman->count())
          <div class="col-12">
            <label class="form-label">Jenis Pinjaman</label>
            <select name="jenis_pinjaman" id="jenisPinjamanSelect"
              class="form-select @error('jenis_pinjaman') is-invalid @enderror">
              <option value="">-- Pilih jenis (opsional) --</option>
              @foreach($jenisPinjaman as $jp)
                <option value="{{ $jp['nama'] }}"
                  data-bunga="{{ $jp['bunga'] }}"
                  data-min-pinjaman="{{ $jp['min_pinjaman'] }}"
                  data-maks-pinjaman="{{ $jp['maks_pinjaman'] }}"
                  data-min-jangka="{{ $jp['min_jangka'] }}"
                  data-maks-jangka="{{ $jp['maks_jangka'] }}"
                  {{ old('jenis_pinjaman') == $jp['nama'] ? 'selected' : '' }}>
                  {{ $jp['nama'] }}
                </option>
              @endforeach
            </select>
            <div class="form-text">Pilih jenis untuk mengisi bunga &amp; batas otomatis.</div>
            @error('jenis_pinjaman')<div class="invalid-feedback">{{ $message }}</div>@enderror

            {{-- Info box jenis yang dipilih --}}
            <div id="infoJenisPinjaman" class="mt-2 p-3 rounded-3 border d-none"
              style="background:#f0f6ff;border-color:#c3d9ff !important">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <strong id="infoJenisNama" style="color:#112344;font-size:.9rem"></strong>
                <span class="badge rounded-pill" style="background:#175cdd;color:#fff;font-size:.75rem" id="infoBungaBadge"></span>
              </div>
              <div class="row g-2 text-center" style="font-size:.8rem">
                <div class="col-6">
                  <div style="color:#6b7280">Jumlah Pinjaman</div>
                  <div style="font-weight:600;color:#112344" id="infoRangePinjaman">—</div>
                </div>
                <div class="col-6">
                  <div style="color:#6b7280">Jangka Waktu</div>
                  <div style="font-weight:600;color:#112344" id="infoRangeJangka">—</div>
                </div>
              </div>
            </div>
          </div>
          @endif

          <div class="col-md-4">
            <label class="form-label">Jumlah Pinjaman (Rp) <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text">Rp</span>
              <input type="number" name="jumlah_pinjaman" id="jumlahPinjaman"
                class="form-control @error('jumlah_pinjaman') is-invalid @enderror"
                value="{{ old('jumlah_pinjaman') }}" min="0" step="100000" placeholder="0">
              @error('jumlah_pinjaman')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="form-text" id="infoJumlah"></div>
          </div>

          <div class="col-md-4">
            <label class="form-label">Jangka Waktu (Bulan) <span class="text-danger">*</span></label>
            <div class="input-group">
              <input type="number" name="jangka_waktu" id="jangkaWaktu"
                class="form-control @error('jangka_waktu') is-invalid @enderror"
                value="{{ old('jangka_waktu') }}" min="1" max="60" step="1" placeholder="12">
              <span class="input-group-text">bulan</span>
            </div>
            <div class="form-text" id="infoJangka"></div>
            @error('jangka_waktu')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="col-md-4">
            <label class="form-label">Bunga (% / Bulan) <span class="text-danger">*</span></label>
            <div class="input-group">
              <input type="number" name="bunga" id="bungaPinjaman"
                class="form-control @error('bunga') is-invalid @enderror"
                value="{{ old('bunga', 1.5) }}" min="0" max="100" step="0.1">
              <span class="input-group-text" id="iconBunga">%</span>
              @error('bunga')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="form-text" id="infoBunga"></div>
          </div>

          <!-- Kalkulator Cicilan -->
          <div class="col-12">
            <div class="p-4 rounded-3 border" style="background:#f4f8ff;border-color:#dce8ff !important" id="kalkulator" style="display:none">
              <h6 class="fw-700 mb-3" style="color:#112344">
                <i class="bi bi-calculator me-2" style="color:#175cdd"></i>Perkiraan Cicilan
              </h6>
              <div class="row g-3">
                <div class="col-md-4 text-center">
                  <div style="font-size:.8rem;color:#6b7280;margin-bottom:.25rem">Cicilan per Bulan</div>
                  <div style="font-size:1.5rem;font-weight:700;color:#175cdd" id="cicilanPerBulan">Rp 0</div>
                </div>
                <div class="col-md-4 text-center">
                  <div style="font-size:.8rem;color:#6b7280;margin-bottom:.25rem">Total yang Dibayar</div>
                  <div style="font-size:1.5rem;font-weight:700;color:#112344" id="totalBayar">Rp 0</div>
                </div>
                <div class="col-md-4 text-center">
                  <div style="font-size:.8rem;color:#6b7280;margin-bottom:.25rem">Total Bunga</div>
                  <div style="font-size:1.5rem;font-weight:700;color:#f59e0b" id="totalBunga">Rp 0</div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-md-4">
            <label class="form-label">Tanggal Pengajuan <span class="text-danger">*</span></label>
            <input type="date" name="tanggal_pengajuan" class="form-control @error('tanggal_pengajuan') is-invalid @enderror"
              value="{{ old('tanggal_pengajuan', date('Y-m-d')) }}">
            @error('tanggal_pengajuan')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="col-12">
            <label class="form-label">Tujuan Pinjaman <span class="text-danger">*</span></label>
            <textarea name="tujuan" class="form-control @error('tujuan') is-invalid @enderror"
              rows="3" placeholder="Jelaskan tujuan penggunaan dana pinjaman...">{{ old('tujuan') }}</textarea>
            @error('tujuan')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
        </div>

        <div class="d-flex gap-2 mt-4">
          <button type="submit" class="btn-simpan">
            <i class="bi bi-send me-2"></i> Ajukan Pinjaman
          </button>
          <a href="{{ route('pinjaman.index') }}" class="btn btn-secondary">
            <i class="bi bi-x-lg me-2"></i> Batal
          </a>
        </div>
      </form>
    </div>
  </div>
</div>

@push('js')
<script>
const fmt = v => 'Rp ' + Math.round(v).toLocaleString('id-ID');
const fmtShort = v => {
  if (v >= 1_000_000) return 'Rp ' + (v / 1_000_000).toLocaleString('id-ID', {maximumFractionDigits:1}) + ' jt';
  if (v >= 1_000)     return 'Rp ' + (v / 1_000).toLocaleString('id-ID', {maximumFractionDigits:0}) + ' rb';
  return 'Rp ' + v;
};

// ── Sesuaikan kolom dari jenis pinjaman ────────────────────
const selJenis = document.getElementById('jenisPinjamanSelect');
if (selJenis) {
  function terapkanJenis(opt) {
    const elBunga       = document.getElementById('bungaPinjaman');
    const elJumlah      = document.getElementById('jumlahPinjaman');
    const elJangka      = document.getElementById('jangkaWaktu');
    const elInfoJumlah  = document.getElementById('infoJumlah');
    const elInfoJangka  = document.getElementById('infoJangka');
    const elInfoBunga   = document.getElementById('infoBunga');
    const elIconBunga   = document.getElementById('iconBunga');
    const infoBox       = document.getElementById('infoJenisPinjaman');
    const infoNama      = document.getElementById('infoJenisNama');
    const infoBadge     = document.getElementById('infoBungaBadge');
    const infoRpRange   = document.getElementById('infoRangePinjaman');
    const infoJkRange   = document.getElementById('infoRangeJangka');

    if (!opt.value) {
      // Reset
      elBunga.readOnly = false;
      elBunga.classList.remove('bg-light');
      elIconBunga.innerHTML = '%';
      elJumlah.min = 0; elJumlah.max = '';
      elJumlah.placeholder = '0';
      elJangka.min = 1; elJangka.max = 60;
      elJangka.placeholder = '12';
      if (elInfoJumlah) elInfoJumlah.textContent = '';
      if (elInfoJangka) elInfoJangka.textContent = '';
      if (elInfoBunga)  elInfoBunga.textContent  = '';
      if (infoBox) infoBox.classList.add('d-none');
      return;
    }

    const bunga        = parseFloat(opt.dataset.bunga)     || 0;
    const minPinjaman  = parseInt(opt.dataset.minPinjaman) || 0;
    const maksPinjaman = parseInt(opt.dataset.maksPinjaman)|| 0;
    const minJangka    = parseInt(opt.dataset.minJangka)   || 1;
    const maksJangka   = parseInt(opt.dataset.maksJangka)  || 60;

    // Bunga terkunci dari jenis
    elBunga.value    = bunga;
    elBunga.readOnly = true;
    elBunga.classList.add('bg-light');
    elIconBunga.innerHTML = '<i class="bi bi-lock-fill text-primary" title="Diatur dari jenis pinjaman"></i>';

    // Jumlah pinjaman — atur batas dan placeholder
    elJumlah.min         = minPinjaman;
    elJumlah.max         = maksPinjaman || '';
    elJumlah.placeholder = minPinjaman.toLocaleString('id-ID');
    if (elInfoJumlah) elInfoJumlah.innerHTML =
      `<i class="bi bi-info-circle me-1"></i>Batas: ${fmtShort(minPinjaman)} – ${fmtShort(maksPinjaman)}`;

    // Jangka waktu — atur batas dan placeholder
    elJangka.min         = minJangka;
    elJangka.max         = maksJangka;
    elJangka.placeholder = minJangka;
    if (parseInt(elJangka.value) < minJangka) elJangka.value = minJangka;
    if (parseInt(elJangka.value) > maksJangka) elJangka.value = maksJangka;
    if (elInfoJangka) elInfoJangka.innerHTML =
      `<i class="bi bi-info-circle me-1"></i>Batas: ${minJangka} – ${maksJangka} bulan`;

    if (elInfoBunga) elInfoBunga.innerHTML =
      `<i class="bi bi-lock-fill me-1 text-primary"></i>Bunga ditetapkan dari jenis pinjaman`;

    // Info box
    if (infoBox) {
      infoBox.classList.remove('d-none');
      if (infoNama)  infoNama.textContent  = opt.value;
      if (infoBadge) infoBadge.textContent = bunga + '% / bulan';
      if (infoRpRange) infoRpRange.textContent = `${fmtShort(minPinjaman)} – ${fmtShort(maksPinjaman)}`;
      if (infoJkRange) infoJkRange.textContent = `${minJangka} – ${maksJangka} bulan`;
    }

    hitungCicilan();
  }

  selJenis.addEventListener('change', function () {
    terapkanJenis(this.options[this.selectedIndex]);
  });

  // Terapkan saat load jika ada old value
  if (selJenis.value) terapkanJenis(selJenis.options[selJenis.selectedIndex]);
}

// ── Kalkulator cicilan ─────────────────────────────────────
function hitungCicilan() {
  const jumlah = parseFloat(document.getElementById('jumlahPinjaman').value) || 0;
  const jangka = parseInt(document.getElementById('jangkaWaktu').value) || 0;
  const bunga  = parseFloat(document.getElementById('bungaPinjaman').value) || 0;

  if (jumlah > 0 && jangka > 0) {
    const bungaBulanan = jumlah * (bunga / 100);
    const cicilan      = (jumlah / jangka) + bungaBulanan;
    const totalBayar   = cicilan * jangka;
    const totalBunga   = bungaBulanan * jangka;

    document.getElementById('cicilanPerBulan').textContent = fmt(cicilan);
    document.getElementById('totalBayar').textContent      = fmt(totalBayar);
    document.getElementById('totalBunga').textContent      = fmt(totalBunga);
    document.getElementById('kalkulator').style.display    = '';
  }
}

['jumlahPinjaman','jangkaWaktu','bungaPinjaman'].forEach(id => {
  const el = document.getElementById(id);
  if (el) {
    el.addEventListener('input', hitungCicilan);
    el.addEventListener('change', hitungCicilan);
  }
});
</script>
@endpush
@endsection

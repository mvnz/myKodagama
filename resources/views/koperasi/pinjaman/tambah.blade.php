@extends('layouts.aplikasi')

@section('judul', 'Ajukan Pinjaman')

@section('konten')
<div class="row justify-content-center">
  <div class="col-xl-10">
    <div class="form-card">
      <div class="d-flex align-items-center gap-3 mb-4 pb-4 border-bottom">
        <div class="d-flex align-items-center justify-content-center rounded-3 is-1dc83824"
         >
          <i class="bi bi-cash-coin is-deae9fc6"></i>
        </div>
        <div>
          <h5 class="mb-0 fw-700 is-5ca88772">Ajukan Pinjaman Baru</h5>
          <small class="text-muted">Isi formulir pengajuan pinjaman anggota</small>
        </div>
      </div>

      <form action="{{ route('pinjaman.store') }}" method="POST" id="formPinjaman" data-anggota-jenis-aktif="{{ $anggotaJenisAktif ?? '' }}">
        @csrf
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label">Anggota <span class="text-danger">*</span></label>
            @if($anggotaSaya)
              <input type="hidden" name="anggota_id" value="{{ $anggotaSaya->id }}">
              <input type="hidden" name="anggota_jenis_aktif" value="{{ $anggotaSaya->jenis_anggota ?? '' }}">
              <input type="text" class="form-control" value="{{ $anggotaSaya->nomor_anggota }} — {{ $anggotaSaya->nama }}" readonly>
            @else
              <select name="anggota_id" class="form-select @error('anggota_id') is-invalid @enderror">
                <option value="">-- Pilih Anggota --</option>
                @foreach($anggota as $a)
                  <option value="{{ $a->id }}" data-jenis-anggota="{{ $a->jenis_anggota ?? '' }}"
                    {{ old('anggota_id', request('anggota_id')) == $a->id ? 'selected' : '' }}>
                    {{ $a->nomor_anggota }} — {{ $a->nama }}{{ !empty($a->jenis_anggota) ? ' (' . $a->jenis_anggota . ')' : '' }}
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
              @foreach(($jenisPinjamanMaster ?? $jenisPinjaman) as $jp)
                @php $jpValue = ($jp['nama'] ?? '') . '||' . ($jp['jenis_anggota'] ?? ''); @endphp
                <option value="{{ $jpValue }}"
                  data-jenis-anggota="{{ $jp['jenis_anggota'] ?? '' }}"
                  data-bunga="{{ $jp['bunga'] }}"
                  data-min-pinjaman="{{ $jp['min_pinjaman'] }}"
                  data-maks-pinjaman="{{ $jp['maks_pinjaman'] }}"
                  data-min-jangka="{{ $jp['min_jangka'] }}"
                  data-maks-jangka="{{ $jp['maks_jangka'] }}"
                  {{ old('jenis_pinjaman') == $jpValue || old('jenis_pinjaman') == ($jp['nama'] ?? '') ? 'selected' : '' }}>
                  {{ $jp['nama'] }}{{ !empty($jp['jenis_anggota']) ? ' - ' . $jp['jenis_anggota'] : ' - Semua Anggota' }}
                </option>
              @endforeach
            </select>
            <div class="form-text">Pilih jenis untuk mengisi bunga &amp; batas otomatis sesuai jenis anggota.</div>
            @error('jenis_pinjaman')<div class="invalid-feedback">{{ $message }}</div>@enderror

            {{-- Info box jenis yang dipilih --}}
            <div id="infoJenisPinjaman" class="mt-2 p-3 rounded-3 border d-none is-04a88df5"
             >
              <div class="d-flex align-items-center justify-content-between mb-2">
                <strong id="infoJenisNama" class="is-03dc4856"></strong>
                <span class="badge rounded-pill is-9225cf74" id="infoBungaBadge"></span>
              </div>
              <div class="row g-2 text-center is-6c660c40">
                <div class="col-6">
                  <div class="is-908c641a">Jumlah Pinjaman</div>
                  <div id="infoRangePinjaman" class="is-6467c5ff">—</div>
                </div>
                <div class="col-6">
                  <div class="is-908c641a">Jangka Waktu</div>
                  <div id="infoRangeJangka" class="is-6467c5ff">—</div>
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
            <div class="p-4 rounded-3 border is-f87f6740 is-cb458930" id="kalkulator">
              <h6 class="fw-700 mb-3 is-5ca88772">
                <i class="bi bi-calculator me-2 is-bc519fd0"></i>Perkiraan Cicilan
              </h6>
              <div class="row g-3">
                <div class="col-md-4 text-center">
                  <div class="is-2f760700">Cicilan per Bulan</div>
                  <div id="cicilanPerBulan" class="is-ec818e52">Rp 0</div>
                </div>
                <div class="col-md-4 text-center">
                  <div class="is-2f760700">Total yang Dibayar</div>
                  <div id="totalBayar" class="is-19d222ab">Rp 0</div>
                </div>
                <div class="col-md-4 text-center">
                  <div class="is-2f760700">Total Bunga</div>
                  <div id="totalBunga" class="is-644339eb">Rp 0</div>
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

        <div class="d-flex gap-2 mt-4 pt-4 border-top">
          <button type="submit" class="btn btn-primary px-4">
            <i class="bi bi-check-lg me-2"></i> Simpan
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
<script src="{{ asset('js/pages/pinjaman-tambah.js') }}"></script>
@endpush
@endsection
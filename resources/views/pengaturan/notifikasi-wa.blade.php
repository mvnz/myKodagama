@extends('layouts.aplikasi')

@section('judul', 'Notifikasi WhatsApp')

@section('konten')
<div class="row justify-content-center">
  <div class="col-xl-10">

    {{-- Banner --}}
    <div class="dashboard-welcome mb-4">
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div>
          <div class="welcome-label mb-1">Pengaturan Koperasi</div>
          <h4 class="mb-1 fw-700">Notifikasi WhatsApp</h4>
          <p class="mb-0 welcome-date">
            <i class="bi bi-whatsapp me-1"></i>
            Kirim notifikasi otomatis ke anggota via WhatsApp
          </p>
        </div>
        <div class="welcome-icon-wrap">
          <i class="bi bi-whatsapp"></i>
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

    <form action="{{ route('pengaturan.notifikasi-wa.update') }}" method="POST" id="formWa">
      @csrf @method('PUT')

      {{-- ── Koneksi API ──────────────────────────────────────────── --}}
      <div class="form-card mb-4">
        <h6 class="fw-700 mb-4 d-flex align-items-center gap-2" style="color:#175cdd">
          <i class="bi bi-plug-fill"></i> Koneksi API Gateway
        </h6>

        {{-- Toggle aktif --}}
        <div class="d-flex align-items-center justify-content-between p-3 rounded-3 mb-4"
          style="background:#f0f6ff;border:1px solid #c3d9ff">
          <div>
            <div class="fw-700" style="color:#112344">Aktifkan Notifikasi WhatsApp</div>
            <div class="text-muted" style="font-size:.83rem">Jika dimatikan, semua notifikasi WA tidak akan dikirim.</div>
          </div>
          <div class="form-check form-switch mb-0">
            <input class="form-check-input" type="checkbox" role="switch"
              name="wa_aktif" id="waAktif" style="width:3rem;height:1.5rem"
              {{ ($pengaturan->get('wa_aktif')?->nilai ?? '0') === '1' ? 'checked' : '' }}>
          </div>
        </div>

        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label fw-600">Provider / Gateway</label>
            <select name="wa_provider" class="form-select">
              <option value="fonnte" {{ ($pengaturan->get('wa_provider')?->nilai ?? 'fonnte') === 'fonnte' ? 'selected' : '' }}>
                Fonnte (fonnte.com)
              </option>
              <option value="wablas" {{ ($pengaturan->get('wa_provider')?->nilai ?? '') === 'wablas' ? 'selected' : '' }}>
                Wablas (wablas.com)
              </option>
            </select>
            <div class="form-text">Pilih provider gateway WhatsApp yang digunakan.</div>
          </div>

          <div class="col-md-4">
            <label class="form-label fw-600">Token API <span class="text-danger">*</span></label>
            <div class="input-group">
              <input type="password" class="form-control @error('wa_token') is-invalid @enderror"
                name="wa_token" id="waToken"
                value="{{ old('wa_token', $pengaturan->get('wa_token')?->nilai) }}"
                placeholder="Masukkan token API gateway">
              <button class="btn btn-outline-secondary" type="button" id="toggleToken"
                title="Tampilkan/sembunyikan token">
                <i class="bi bi-eye" id="tokenEyeIcon"></i>
              </button>
            </div>
            @error('wa_token')<div class="invalid-feedback">{{ $message }}</div>@enderror
            <div class="form-text">Dapatkan token dari dashboard gateway Anda.</div>
          </div>

          <div class="col-md-4">
            <label class="form-label fw-600">Nomor Pengirim (opsional)</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-phone"></i></span>
              <input type="text" class="form-control"
                name="wa_nomor_pengirim"
                value="{{ old('wa_nomor_pengirim', $pengaturan->get('wa_nomor_pengirim')?->nilai) }}"
                placeholder="628123456789">
            </div>
            <div class="form-text">Nomor WhatsApp yang terdaftar di gateway (dengan kode negara).</div>
          </div>
        </div>

        {{-- Test Kirim --}}
        <div class="mt-4 p-3 rounded-3" style="background:#fffbeb;border:1px solid #fde68a">
          <div class="fw-700 mb-2" style="color:#92400e">
            <i class="bi bi-send-fill me-1" style="color:#f59e0b"></i> Test Kirim Pesan
          </div>
          <div class="row g-2 align-items-end">
            <div class="col-md-5">
              <label class="form-label mb-1" style="font-size:.82rem">Nomor Tujuan Test</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-whatsapp"></i></span>
                <input type="text" class="form-control" id="testNomor" placeholder="628123456789">
              </div>
            </div>
            <div class="col-md-3">
              <button type="button" class="btn btn-warning w-100" id="btnTest">
                <i class="bi bi-send me-1"></i> Kirim Test
              </button>
            </div>
            <div class="col-12">
              <div id="testResult" class="d-none mt-2 rounded-3 px-3 py-2" style="font-size:.85rem"></div>
            </div>
          </div>
        </div>
      </div>

      {{-- ── Jenis Notifikasi ─────────────────────────────────────── --}}
      <div class="form-card mb-4">
        <h6 class="fw-700 mb-4 d-flex align-items-center gap-2" style="color:#175cdd">
          <i class="bi bi-bell-fill"></i> Jenis Notifikasi
        </h6>

        @php
          $notifList = [
            'wa_notif_pinjaman_diajukan'    => ['label' => 'Pinjaman Baru Diajukan',       'icon' => 'bi-cash-coin',        'desc' => 'Dikirim ke admin/petugas saat anggota mengajukan pinjaman baru.'],
            'wa_notif_pinjaman_disetujui'   => ['label' => 'Pinjaman Disetujui',            'icon' => 'bi-check-circle-fill','desc' => 'Dikirim ke anggota saat pengajuan pinjaman disetujui.'],
            'wa_notif_pinjaman_ditolak'     => ['label' => 'Pinjaman Ditolak',              'icon' => 'bi-x-circle-fill',   'desc' => 'Dikirim ke anggota saat pengajuan pinjaman ditolak.'],
            'wa_notif_angsuran_jatuh_tempo' => ['label' => 'Pengingat Jatuh Tempo Angsuran','icon' => 'bi-alarm-fill',       'desc' => 'Dikirim H-3 sebelum tanggal jatuh tempo angsuran.'],
            'wa_notif_angsuran_terlambat'   => ['label' => 'Angsuran Terlambat',            'icon' => 'bi-exclamation-triangle-fill','desc' => 'Dikirim saat angsuran melewati tanggal jatuh tempo.'],
            'wa_notif_simpanan'             => ['label' => 'Konfirmasi Penerimaan Simpanan','icon' => 'bi-piggy-bank-fill',  'desc' => 'Dikirim ke anggota saat simpanan berhasil dicatat.'],
          ];
        @endphp

        <div class="row g-3">
          @foreach($notifList as $kunci => $info)
          <div class="col-md-6">
            <div class="d-flex align-items-start justify-content-between p-3 rounded-3 h-100"
              style="background:#f8faff;border:1px solid #e3eaff">
              <div class="d-flex align-items-start gap-3">
                <div class="d-flex align-items-center justify-content-center rounded-2 flex-shrink-0"
                  style="width:38px;height:38px;background:#e8f0ff">
                  <i class="bi {{ $info['icon'] }}" style="color:#175cdd;font-size:1rem"></i>
                </div>
                <div>
                  <div class="fw-700" style="font-size:.9rem;color:#112344">{{ $info['label'] }}</div>
                  <div class="text-muted" style="font-size:.78rem;line-height:1.4">{{ $info['desc'] }}</div>
                </div>
              </div>
              <div class="form-check form-switch mb-0 ms-3 flex-shrink-0">
                <input class="form-check-input" type="checkbox" role="switch"
                  name="{{ $kunci }}" style="width:2.5rem;height:1.25rem"
                  {{ ($pengaturan->get($kunci)?->nilai ?? '0') === '1' ? 'checked' : '' }}>
              </div>
            </div>
          </div>
          @endforeach
        </div>
      </div>

      {{-- ── Template Pesan ───────────────────────────────────────── --}}
      <div class="form-card mb-4">
        <h6 class="fw-700 mb-1 d-flex align-items-center gap-2" style="color:#175cdd">
          <i class="bi bi-chat-text-fill"></i> Template Pesan
        </h6>
        <p class="text-muted mb-4" style="font-size:.82rem">
          Gunakan variabel: <code>{nama}</code> nama anggota, <code>{nomor}</code> nomor anggota,
          <code>{jumlah}</code> nilai pinjaman, <code>{tanggal}</code> tanggal jatuh tempo,
          <code>{koperasi}</code> nama koperasi.
        </p>

        @php
          $templateList = [
            'wa_template_pinjaman_diajukan'    => ['label'=>'Pinjaman Diajukan (ke petugas)',
              'default'=>"Halo Admin,\nAda pengajuan pinjaman baru dari *{nama}* ({nomor}).\nJumlah: *Rp {jumlah}*\nSilakan tinjau di sistem.\n\n_{koperasi}_"],
            'wa_template_pinjaman_disetujui'   => ['label'=>'Pinjaman Disetujui (ke anggota)',
              'default'=>"Halo *{nama}*,\nSelamat! Pengajuan pinjaman Anda sebesar *Rp {jumlah}* telah *disetujui*.\nSilakan hubungi petugas untuk pencairan.\n\n_{koperasi}_"],
            'wa_template_pinjaman_ditolak'     => ['label'=>'Pinjaman Ditolak (ke anggota)',
              'default'=>"Halo *{nama}*,\nMohon maaf, pengajuan pinjaman Anda sebesar *Rp {jumlah}* tidak dapat disetujui saat ini.\nSilakan hubungi petugas untuk informasi lebih lanjut.\n\n_{koperasi}_"],
            'wa_template_angsuran_jatuh_tempo' => ['label'=>'Pengingat Jatuh Tempo (ke anggota)',
              'default'=>"Halo *{nama}*,\nIni adalah pengingat bahwa angsuran pinjaman Anda akan jatuh tempo pada *{tanggal}*.\nHarap segera melakukan pembayaran.\n\n_{koperasi}_"],
          ];
        @endphp

        <div class="row g-4">
          @foreach($templateList as $kunci => $info)
          <div class="col-md-6">
            <label class="form-label fw-600">{{ $info['label'] }}</label>
            <textarea name="{{ $kunci }}" rows="5"
              class="form-control" style="font-size:.83rem;font-family:monospace;resize:vertical"
              placeholder="{{ $info['default'] }}">{{ old($kunci, $pengaturan->get($kunci)?->nilai ?? $info['default']) }}</textarea>
          </div>
          @endforeach
        </div>
      </div>

      {{-- Submit --}}
      <div class="d-flex gap-2 justify-content-end">
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
          <i class="bi bi-x-lg me-1"></i> Batal
        </a>
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-floppy-fill me-1"></i> Simpan Pengaturan
        </button>
      </div>

    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
// Toggle show/hide token
document.getElementById('toggleToken').addEventListener('click', function () {
  const input = document.getElementById('waToken');
  const icon  = document.getElementById('tokenEyeIcon');
  if (input.type === 'password') {
    input.type = 'text';
    icon.className = 'bi bi-eye-slash';
  } else {
    input.type = 'password';
    icon.className = 'bi bi-eye';
  }
});

// Test kirim WA
document.getElementById('btnTest').addEventListener('click', function () {
  const nomor  = document.getElementById('testNomor').value.trim();
  const result = document.getElementById('testResult');

  if (!nomor) {
    result.className = 'mt-2 rounded-3 px-3 py-2 alert alert-warning';
    result.textContent = 'Masukkan nomor tujuan terlebih dahulu.';
    return;
  }

  this.disabled = true;
  this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Mengirim...';

  fetch('{{ route("pengaturan.notifikasi-wa.test") }}', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
    },
    body: JSON.stringify({ nomor }),
  })
  .then(r => r.json())
  .then(data => {
    result.className = 'mt-2 rounded-3 px-3 py-2 ' + (data.sukses ? 'alert alert-success' : 'alert alert-danger');
    result.innerHTML = '<i class="bi bi-' + (data.sukses ? 'check-circle-fill' : 'x-circle-fill') + ' me-1"></i>' + data.pesan;
  })
  .catch(() => {
    result.className = 'mt-2 rounded-3 px-3 py-2 alert alert-danger';
    result.textContent = 'Terjadi kesalahan koneksi.';
  })
  .finally(() => {
    this.disabled = false;
    this.innerHTML = '<i class="bi bi-send me-1"></i> Kirim Test';
  });
});
</script>
@endpush

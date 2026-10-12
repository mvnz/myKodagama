@extends('layouts.aplikasi')

@section('judul', 'Database')

@section('konten')
<div class="row justify-content-center">
  <div class="col-xl-10">

    <div class="dashboard-welcome mb-4">
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div>
          <div class="welcome-label mb-1">Pengaturan Koperasi</div>
          <h4 class="mb-1 fw-700">Backup &amp; Restore Database</h4>
          <p class="mb-0 welcome-date">
            <i class="bi bi-database me-1"></i>
            Ekspor seluruh data untuk cadangan atau pindah server
          </p>
        </div>
        <div class="welcome-icon-wrap"><i class="bi bi-database-fill-gear"></i></div>
      </div>
    </div>

    @if($errors->any())
      <div class="alert alert-danger mb-4">
        @foreach($errors->all() as $err)<div>{{ $err }}</div>@endforeach
      </div>
    @endif

    <div class="row g-4 mb-4">
      {{-- Backup manual --}}
      <div class="col-lg-5">
        <div class="form-card h-100">
          <h6 class="fw-700 mb-3"><i class="bi bi-cloud-arrow-down-fill me-2"></i>Backup Manual</h6>
          <p class="text-muted small">Membuat file backup (<code>.ndjson.gz</code>) berisi struktur dan seluruh data. File disimpan di:</p>
          <code class="d-block small mb-3 text-break">{{ $direktori }}</code>
          <form action="{{ route('pengaturan.database.backup') }}" method="POST">
            @csrf
            <button class="btn btn-primary"><i class="bi bi-download me-1"></i> Buat Backup Sekarang</button>
          </form>
          @if($terakhir)
            <div class="small mt-3 {{ $terakhir['status'] === 'ok' ? 'text-success' : 'text-danger' }}">
              <i class="bi bi-clock-history me-1"></i>
              Auto backup terakhir: {{ $terakhir['waktu'] }} —
              {{ $terakhir['status'] === 'ok' ? 'berhasil' : 'gagal: ' . $terakhir['info'] }}
            </div>
          @endif
        </div>
      </div>

      {{-- Auto backup --}}
      <div class="col-lg-7">
        <div class="form-card h-100">
          <h6 class="fw-700 mb-3"><i class="bi bi-calendar-check-fill me-2"></i>Auto Backup Harian</h6>
          <form action="{{ route('pengaturan.database.simpan') }}" method="POST">
            @csrf @method('PUT')
            <div class="form-check form-switch mb-3">
              <input class="form-check-input" type="checkbox" role="switch" name="backup_auto" id="backupAuto" value="1"
                {{ old('backup_auto', $pengaturan['backup_auto'] ?? '0') == '1' ? 'checked' : '' }}>
              <label class="form-check-label fw-600" for="backupAuto">Aktifkan auto backup</label>
            </div>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label fw-600">Dimulai dari jam</label>
                <input type="time" name="backup_waktu" class="form-control"
                  value="{{ old('backup_waktu', $pengaturan['backup_waktu'] ?? '02:00') }}" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-600">Lokasi</label>
                @php $lokasi = old('backup_lokasi', $pengaturan['backup_lokasi'] ?? 'server'); @endphp
                <select name="backup_lokasi" id="backupLokasi" class="form-select">
                  <option value="server" @selected($lokasi === 'server')>Server (storage aplikasi)</option>
                  <option value="custom" @selected($lokasi === 'custom')>Folder kustom / lokal</option>
                </select>
              </div>
              <div class="col-12" id="wrapPath">
                <label class="form-label fw-600">Folder tujuan</label>
                <input type="text" name="backup_path" class="form-control" placeholder="D:\Backup\Kodagama atau /var/backups/kodagama"
                  value="{{ old('backup_path', $pengaturan['backup_path'] ?? '') }}">
              </div>
            </div>
            <button class="btn btn-success mt-3"><i class="bi bi-save me-1"></i> Simpan Pengaturan</button>
          </form>
          <div class="alert alert-info small mt-3 mb-0">
            Backup dibuat sekali per hari, dimulai dari jam yang diatur (saat aplikasi pertama kali dibuka setelah jam tersebut). File backup tidak dihapus otomatis; hapus manual dari daftar di bawah bila perlu.
          </div>
        </div>
      </div>
    </div>

    {{-- Daftar backup --}}
    <div class="form-card mb-4">
      <h6 class="fw-700 mb-3"><i class="bi bi-archive-fill me-2"></i>Daftar File Backup</h6>
      <div class="table-responsive">
        <table class="koperasi-table mb-0">
          <thead><tr><th>Nama File</th><th>Waktu</th><th class="text-end">Ukuran</th><th class="text-end">Aksi</th></tr></thead>
          <tbody>
            @forelse($daftar as $f)
              <tr>
                <td><code>{{ $f['nama'] }}</code></td>
                <td>{{ $f['waktu']->format('d-m-Y H:i') }}</td>
                <td class="text-end">{{ number_format($f['ukuran'] / 1024, 1, ',', '.') }} KB</td>
                <td class="text-end">
                  <a href="{{ route('pengaturan.database.unduh', $f['nama']) }}" class="btn btn-sm btn-outline-primary" title="Unduh"><i class="bi bi-download"></i></a>
                  <button type="button" class="btn btn-sm btn-outline-warning" title="Restore"
                    data-bs-toggle="modal" data-bs-target="#modalRestore" data-nama="{{ $f['nama'] }}">
                    <i class="bi bi-arrow-counterclockwise"></i>
                  </button>
                  <form action="{{ route('pengaturan.database.hapus', $f['nama']) }}" method="POST" class="d-inline"
                    onsubmit="return confirm('Hapus file backup ini?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger" title="Hapus"><i class="bi bi-trash"></i></button>
                  </form>
                </td>
              </tr>
            @empty
              <tr><td colspan="4" class="text-center text-muted py-3">Belum ada file backup.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    {{-- Restore dari upload --}}
    <div class="form-card mb-4">
      <h6 class="fw-700 mb-1 text-danger"><i class="bi bi-upload me-2"></i>Restore</h6>
      <p class="text-muted small mb-3">
        Unggah file backup dari server lama. Struktur tabel dibuat otomatis, lalu <strong>seluruh data saat ini diganti</strong>.
        Kondisi sebelumnya dicadangkan otomatis (<code>prerestore</code>). Di server baru yang belum punya akun, restore tersedia dari halaman login.
      </p>
      <form action="{{ route('pengaturan.database.restore') }}" method="POST" enctype="multipart/form-data" class="row g-3 align-items-end">
        @csrf
        <div class="col-md-5">
          <label class="form-label fw-600">File backup</label>
          <input type="file" name="file" class="form-control" accept=".gz" required>
        </div>
        <div class="col-md-4">
          <label class="form-label fw-600">Ketik <code>RESTORE</code></label>
          <input type="text" name="konfirmasi" class="form-control" autocomplete="off" required>
        </div>
        <div class="col-md-3">
          <button class="btn btn-danger w-100"><i class="bi bi-arrow-counterclockwise me-1"></i> Restore</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Modal restore dari daftar --}}
<div class="modal fade" id="modalRestore" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" action="{{ route('pengaturan.database.restore') }}" method="POST">
      @csrf
      <input type="hidden" name="nama" id="restoreNama">
      <div class="modal-header"><h6 class="modal-title">Restore Database</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <p class="small">Seluruh data saat ini akan diganti dengan isi <code id="restoreLabel"></code>.</p>
        <label class="form-label fw-600">Ketik <code>RESTORE</code> untuk konfirmasi</label>
        <input type="text" name="konfirmasi" class="form-control" autocomplete="off" required>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
        <button class="btn btn-danger">Restore</button>
      </div>
    </form>
  </div>
</div>
@endsection

@push('js')
<script>
  document.getElementById('modalRestore').addEventListener('show.bs.modal', function (e) {
    var nama = e.relatedTarget.getAttribute('data-nama');
    document.getElementById('restoreNama').value = nama;
    document.getElementById('restoreLabel').textContent = nama;
  });
  var lokasi = document.getElementById('backupLokasi');
  function togglePath() { document.getElementById('wrapPath').style.display = lokasi.value === 'custom' ? '' : 'none'; }
  lokasi.addEventListener('change', togglePath); togglePath();
</script>
@endpush
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Daftar Anggota — Koperasi Kodagama</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="{{ asset('css/koperasi.css') }}">
</head>
<body class="auth-page">

  <div class="auth-branding">
    <div>
      <a href="{{ route('beranda') }}" class="auth-logo">
        <i class="bi bi-bank2"></i>
        <div>
          <div class="name">Kodagama</div>
          <div class="tagline">Koperasi Simpan Pinjam</div>
        </div>
      </a>
      <h2 class="mt-5 mb-3" style="font-size:2rem;font-weight:300;line-height:1.4">
        Bergabung &amp; <br><strong style="font-weight:700">Jadilah Anggota</strong>
      </h2>
      <p class="mb-5" style="opacity:.8;line-height:1.7">
        Daftarkan diri Anda sebagai anggota koperasi. Pendaftaran Anda akan diverifikasi oleh petugas sebelum aktif.
      </p>
      <div class="d-flex flex-column gap-3">
        <div class="d-flex align-items-center gap-3">
          <div class="d-flex align-items-center justify-content-center rounded-circle"
            style="width:40px;height:40px;background:rgba(255,255,255,.15);flex-shrink:0">
            <i class="bi bi-1-circle-fill"></i>
          </div>
          <span style="opacity:.85">Isi formulir pendaftaran</span>
        </div>
        <div class="d-flex align-items-center gap-3">
          <div class="d-flex align-items-center justify-content-center rounded-circle"
            style="width:40px;height:40px;background:rgba(255,255,255,.15);flex-shrink:0">
            <i class="bi bi-2-circle-fill"></i>
          </div>
          <span style="opacity:.85">Tunggu verifikasi petugas</span>
        </div>
        <div class="d-flex align-items-center gap-3">
          <div class="d-flex align-items-center justify-content-center rounded-circle"
            style="width:40px;height:40px;background:rgba(255,255,255,.15);flex-shrink:0">
            <i class="bi bi-3-circle-fill"></i>
          </div>
          <span style="opacity:.85">Akun aktif &amp; siap digunakan</span>
        </div>
      </div>
    </div>
  </div>

  <div class="auth-form-side">
    <div class="auth-card">
      <div class="auth-card-header">
        <h3>Pendaftaran Anggota</h3>
        <p>Isi data di bawah untuk mendaftar sebagai anggota koperasi</p>
      </div>

      @if(session('sukses'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <i class="bi bi-check-circle-fill me-2"></i>
          {{ session('sukses') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      @endif

      @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <i class="bi bi-exclamation-triangle-fill me-2"></i>
          {{ $errors->first() }}
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      @endif

      <form method="POST" action="{{ route('daftar.proses') }}">
        @csrf
        <div class="mb-3">
          <label for="nama" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-person"></i></span>
            <input type="text" class="form-control @error('nama') is-invalid @enderror"
              id="nama" name="nama" value="{{ old('nama') }}"
              placeholder="Nama lengkap Anda" autofocus autocomplete="off">
            @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
        </div>
        <div class="mb-3">
          <label for="telepon" class="form-label">No. WhatsApp Aktif <span class="text-danger">*</span></label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-whatsapp"></i></span>
            <input type="text" class="form-control @error('telepon') is-invalid @enderror"
              id="telepon" name="telepon" value="{{ old('telepon') }}"
              placeholder="cth: 08123456789" inputmode="tel">
            @error('telepon')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
        </div>
        <div class="mb-4">
          <label for="email" class="form-label">Alamat Email <span class="text-danger">*</span></label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
            <input type="email" class="form-control @error('email') is-invalid @enderror"
              id="email" name="email" value="{{ old('email') }}"
              placeholder="email@contoh.com">
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
        </div>
        <div class="d-grid">
          <button type="submit" class="btn btn-primary btn-lg" style="border-radius:12px;font-weight:600;padding:.9rem">
            <i class="bi bi-send me-2"></i> Kirim Pendaftaran
          </button>
        </div>
      </form>

      <div class="mt-3 text-center small">
        Sudah punya akun? <a href="{{ route('masuk') }}" class="fw-600">Masuk di sini</a>
      </div>
      <div class="mt-3 text-center">
        <a href="{{ route('beranda') }}" class="text-muted small d-inline-flex align-items-center gap-1">
          <i class="bi bi-arrow-left"></i> Kembali ke Beranda
        </a>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

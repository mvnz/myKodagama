<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Masuk — Koperasi Kodagama</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="{{ asset('css/koperasi.css') }}">
</head>
<body class="auth-page">

  <!-- Kolom kiri: branding -->
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
        Kelola Keuangan <br><strong style="font-weight:700">Lebih Mudah</strong>
      </h2>
      <p class="mb-5" style="opacity:.8;line-height:1.7">
        Platform manajemen koperasi yang lengkap untuk mengelola simpanan, pinjaman, dan angsuran anggota secara efisien.
      </p>

      <div class="d-flex flex-column gap-3">
        <div class="d-flex align-items-center gap-3">
          <div class="d-flex align-items-center justify-content-center rounded-circle"
            style="width:40px;height:40px;background:rgba(255,255,255,.15);flex-shrink:0">
            <i class="bi bi-shield-check-fill"></i>
          </div>
          <span style="opacity:.85">Sistem aman &amp; terenkripsi</span>
        </div>
        <div class="d-flex align-items-center gap-3">
          <div class="d-flex align-items-center justify-content-center rounded-circle"
            style="width:40px;height:40px;background:rgba(255,255,255,.15);flex-shrink:0">
            <i class="bi bi-graph-up-arrow"></i>
          </div>
          <span style="opacity:.85">Laporan keuangan real-time</span>
        </div>
        <div class="d-flex align-items-center gap-3">
          <div class="d-flex align-items-center justify-content-center rounded-circle"
            style="width:40px;height:40px;background:rgba(255,255,255,.15);flex-shrink:0">
            <i class="bi bi-people-fill"></i>
          </div>
          <span style="opacity:.85">Manajemen anggota terpadu</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Kolom kanan: form login -->
  <div class="auth-form-side">
    <div class="auth-card">
      <div class="auth-card-header">
        <h3>Selamat Datang</h3>
        <p>Masuk ke akun Anda untuk melanjutkan</p>
      </div>

      @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <i class="bi bi-exclamation-triangle-fill me-2"></i>
          {{ session('error') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      @endif

      <form method="POST" action="{{ route('masuk.proses') }}">
        @csrf

        <div class="mb-3">
          <label for="email" class="form-label">Alamat Email</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
            <input type="email" class="form-control @error('email') is-invalid @enderror"
              id="email" name="email" value="{{ old('email') }}"
              placeholder="email@contoh.com" autofocus>
            @error('email')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>

        <div class="mb-3">
          <label for="password" class="form-label">Kata Sandi</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input type="password" class="form-control @error('password') is-invalid @enderror"
              id="password" name="password" placeholder="••••••••">
            <button class="btn btn-outline-secondary" type="button" id="togglePassword" tabindex="-1">
              <i class="bi bi-eye"></i>
            </button>
            @error('password')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>

        <div class="mb-4 d-flex justify-content-between align-items-center">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="ingat_saya" id="ingatSaya">
            <label class="form-check-label small" for="ingatSaya">Ingat saya</label>
          </div>
        </div>

        <div class="d-grid">
          <button type="submit" class="btn btn-primary btn-lg" style="border-radius:12px;font-weight:600;padding:.9rem">
            <i class="bi bi-box-arrow-in-right me-2"></i> Masuk
          </button>
        </div>
      </form>

      <div class="mt-4 text-center">
        <div class="small mb-2">
          Belum punya akun?
          <a href="{{ route('daftar') }}" class="fw-600">Daftar di sini</a>
        </div>
        <a href="{{ route('beranda') }}" class="text-muted small d-inline-flex align-items-center gap-1">
          <i class="bi bi-arrow-left"></i> Kembali ke Beranda
        </a>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.getElementById('togglePassword').addEventListener('click', function () {
      const pwd = document.getElementById('password');
      const icon = this.querySelector('i');
      if (pwd.type === 'password') {
        pwd.type = 'text';
        icon.classList.replace('bi-eye', 'bi-eye-slash');
      } else {
        pwd.type = 'password';
        icon.classList.replace('bi-eye-slash', 'bi-eye');
      }
    });
  </script>
</body>
</html>

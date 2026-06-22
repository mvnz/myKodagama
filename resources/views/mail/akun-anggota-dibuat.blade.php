<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Akun Koperasi Anda</title>
  
  <link rel="stylesheet" href="{{ asset('css/pages/inline-extracted.css') }}">
</head>
<body>
  <div class="wrap">
    <div class="header">
      <h1>{{ $namaApp }}</h1>
      <p>Sistem Manajemen Koperasi</p>
    </div>
    <div class="body">
      <div class="greeting">Halo, {{ $namaAnggota }} 👋</div>
      <p class="text">
        Selamat datang! Akun login untuk sistem koperasi Anda telah berhasil dibuat.
        Gunakan informasi berikut untuk masuk ke aplikasi.
      </p>

      <div class="info-box">
        <div class="info-row">
          <span class="info-label">No. Anggota</span>
          <span class="info-value">{{ $nomorAnggota }}</span>
        </div>
        <div class="info-row">
          <span class="info-label">Email Login</span>
          <span class="info-value">{{ $emailLogin }}</span>
        </div>
        <div class="info-row">
          <span class="info-label">Password</span>
          <span class="info-value">{{ $passwordPlain }}</span>
        </div>
      </div>

      <div class="warning">
        ⚠️ <strong>Segera ganti password</strong> Anda setelah pertama kali masuk demi keamanan akun.
      </div>
    </div>
    <div class="footer">
      Email ini dikirim otomatis oleh sistem {{ $namaApp }}. Jangan balas email ini.
    </div>
  </div>
</body>
</html>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Restore Data</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container py-5" style="max-width:560px">
    <div class="card shadow-sm">
      <div class="card-body p-4">
        <h5 class="fw-bold mb-1">Restore Data</h5>
        <p class="text-muted small">
          Aplikasi ini belum memiliki akun. Unggah file backup (<code>.ndjson.gz</code>) dari server lama untuk memulihkan seluruh data, termasuk akun login.
        </p>

        @if(session('galat'))
          <div class="alert alert-danger small">{{ session('galat') }}</div>
        @endif
        @error('file')
          <div class="alert alert-danger small">{{ $message }}</div>
        @enderror

        <form action="{{ route('pemulihan.proses') }}" method="POST" enctype="multipart/form-data">
          @csrf
          <input type="file" name="file" class="form-control mb-3" accept=".gz" required>
          <div class="d-flex gap-2">
            <button class="btn btn-primary">Restore</button>
            <a href="{{ route('masuk') }}" class="btn btn-outline-secondary">Kembali</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</body>
</html>

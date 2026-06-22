<div class="row g-3">
  <div class="col-sm-12">
    <label class="form-label fw-600">Nama Supplier <span class="text-danger">*</span></label>
    <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror"
           value="{{ old('nama', $toko->nama ?? '') }}" required placeholder="cth. Toko Sumber Rejeki">
    @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
  <div class="col-sm-6">
    <label class="form-label fw-600">Nama Pemilik</label>
    <input type="text" name="pemilik" class="form-control"
           value="{{ old('pemilik', $toko->pemilik ?? '') }}" placeholder="Nama pemilik/PIC">
  </div>
  <div class="col-sm-6">
    <label class="form-label fw-600">Telepon</label>
    <input type="text" name="telepon" class="form-control"
           value="{{ old('telepon', $toko->telepon ?? '') }}" placeholder="08xx-xxxx-xxxx">
  </div>
  <div class="col-sm-6">
    <label class="form-label fw-600">Email</label>
    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
           value="{{ old('email', $toko->email ?? '') }}" placeholder="supplier@email.com">
    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
  <div class="col-sm-6">
    <label class="form-label fw-600">Kota</label>
    <input type="text" name="kota" class="form-control"
           value="{{ old('kota', $toko->kota ?? '') }}" placeholder="cth. Yogyakarta">
  </div>
  <div class="col-12">
    <label class="form-label fw-600">Alamat</label>
    <textarea name="alamat" class="form-control" rows="2" placeholder="Jl. ...">{{ old('alamat', $toko->alamat ?? '') }}</textarea>
  </div>
  <div class="col-12">
    <label class="form-label fw-600">Keterangan</label>
    <textarea name="deskripsi" class="form-control" rows="2"
              placeholder="Catatan tentang supplier ini...">{{ old('deskripsi', $toko->deskripsi ?? '') }}</textarea>
  </div>
</div>

<div class="row g-3">
  <div class="col-sm-6">
    <label class="form-label fw-600">Kode Produk <span class="text-danger">*</span></label>
    <input type="text" name="kode" class="form-control @error('kode') is-invalid @enderror"
           value="{{ old('kode', $kode) }}" required>
    @error('kode')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
  <div class="col-sm-6">
    <label class="form-label fw-600">Barcode</label>
    <input type="text" name="barcode" class="form-control @error('barcode') is-invalid @enderror"
           value="{{ old('barcode', $produk?->barcode ?? $prefillBarcode ?? '') }}" placeholder="Scan barcode di sini">
    @error('barcode')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
  <div class="col-sm-6">
    <label class="form-label fw-600">Nama Produk <span class="text-danger">*</span></label>
    <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror"
           value="{{ old('nama', $produk?->nama) }}" required>
    @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
  <div class="col-sm-6">
    <label class="form-label fw-600">Kategori</label>
    <select name="kategori_id" class="form-select">
      <option value="">-- Tanpa Kategori --</option>
      @foreach($kategoris as $kat)
      <option value="{{ $kat->id }}" {{ old('kategori_id', $produk?->kategori_id) == $kat->id ? 'selected':'' }}>
        {{ $kat->nama }}
      </option>
      @endforeach
    </select>
  </div>
  <div class="col-sm-6">
    <label class="form-label fw-600">Supplier</label>
    <select name="supplier_id" class="form-select">
      <option value="">-- Tanpa Supplier --</option>
      @foreach($suppliers as $sup)
      <option value="{{ $sup->id }}" {{ old('supplier_id', $produk?->supplier_id) == $sup->id ? 'selected':'' }}>
        {{ $sup->nama }}
      </option>
      @endforeach
    </select>
    <div class="form-text">Harga pokok masuk ke saldo supplier ini.</div>
  </div>
  <div class="col-sm-6">
    <label class="form-label fw-600">Satuan <span class="text-danger">*</span></label>
    <select name="satuan" class="form-select @error('satuan') is-invalid @enderror">
      @foreach(['pcs','kg','gram','liter','ml','lusin','dus','botol','bungkus','pack','lembar','roll'] as $s)
      <option value="{{ $s }}" {{ old('satuan', $produk?->satuan ?? 'pcs') == $s ? 'selected':'' }}>{{ $s }}</option>
      @endforeach
    </select>
    @error('satuan')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>

  <div class="col-12"><hr class="my-1"></div>

  <div class="col-sm-6">
    <label class="form-label fw-600">Harga Beli</label>
    <div class="input-group">
      <span class="input-group-text">Rp</span>
      <input type="number" name="harga_beli" min="0" class="form-control text-end @error('harga_beli') is-invalid @enderror"
             value="{{ old('harga_beli', $produk?->harga_beli ?? 0) }}">
      @error('harga_beli')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
  </div>
  <div class="col-sm-6">
    <label class="form-label fw-600">Harga Jual <span class="text-danger">*</span></label>
    <div class="input-group">
      <span class="input-group-text">Rp</span>
      <input type="number" name="harga_jual" min="0" class="form-control text-end @error('harga_jual') is-invalid @enderror"
             value="{{ old('harga_jual', $produk?->harga_jual) }}" required>
      @error('harga_jual')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
  </div>
  <div class="col-sm-6">
    <label class="form-label fw-600">Stok Awal</label>
    <input type="number" name="stok" min="0" class="form-control @error('stok') is-invalid @enderror"
           value="{{ old('stok', $produk?->stok ?? 0) }}">
    @error('stok')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
  <div class="col-sm-6 d-flex align-items-end pb-1">
    <div class="form-check form-switch">
      <input class="form-check-input" type="checkbox" role="switch" name="is_aktif" id="isAktif"
             value="1" {{ old('is_aktif', $produk?->is_aktif ?? true) ? 'checked':'' }}>
      <label class="form-check-label fw-600" for="isAktif">Produk Aktif</label>
    </div>
  </div>
  <div class="col-12">
    <label class="form-label fw-600">Deskripsi</label>
    <textarea name="deskripsi" rows="2" class="form-control" placeholder="Keterangan produk (opsional)">{{ old('deskripsi', $produk?->deskripsi) }}</textarea>
  </div>
</div>

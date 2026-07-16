@extends('layouts.pos')
@section('judul', $toko->nama)
@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('pos.toko.index') }}">Supplier</a></li>
  <li class="breadcrumb-item active">{{ $toko->nama }}</li>
@endsection

@section('konten')

<div class="d-flex justify-content-between align-items-center mb-4">
  <h5 class="fw-bold mb-0 is-feb6723d">
    <i class="bi bi-truck me-2 is-f571fe9b"></i>Detail Supplier
  </h5>
  <div class="d-flex gap-2 flex-wrap">
    @if($toko->saldo > 0)
    <button class="btn btn-sm btn-success fw-semibold" data-bs-toggle="modal" data-bs-target="#modalCairkan">
      <i class="bi bi-cash-coin me-1"></i>Cairkan Saldo
    </button>
    @endif
    <a href="{{ route('pos.toko.edit', $toko) }}" class="btn btn-sm btn-warning text-white fw-semibold">
      <i class="bi bi-pencil me-1"></i>Edit
    </a>
    <a href="{{ route('pos.toko.index') }}" class="btn btn-sm btn-outline-secondary">
      <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
  </div>
</div>

{{-- Stat Cards --}}
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="kstat-card kstat-amber">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <i class="kstat-icon bi bi-wallet2"></i>
      <div class="kstat-value kstat-value-sm">Rp {{ number_format($toko->saldo, 0, ',', '.') }}</div>
      <div class="kstat-label">Saldo Aktif</div>
      <div class="kstat-trend"><i class="bi bi-cash-stack me-1"></i> Bisa dicairkan</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="kstat-card kstat-green">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <i class="kstat-icon bi bi-box-seam"></i>
      <div class="kstat-value kstat-value-sm">{{ $toko->produk_count }}</div>
      <div class="kstat-label">Total Produk</div>
      <div class="kstat-trend"><i class="bi bi-box-seam-fill me-1"></i> Produk terdaftar</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="kstat-card kstat-teal">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <i class="kstat-icon bi bi-calendar-month"></i>
      <div class="kstat-value kstat-value-sm">Rp {{ number_format($omzetBulanIni, 0, ',', '.') }}</div>
      <div class="kstat-label">Masuk Bulan Ini</div>
      <div class="kstat-trend"><i class="bi bi-graph-up me-1"></i> Saldo bulan ini</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="kstat-card kstat-red">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <i class="kstat-icon bi bi-cash-stack"></i>
      <div class="kstat-value kstat-value-sm">Rp {{ number_format($totalDicairkan, 0, ',', '.') }}</div>
      <div class="kstat-label">Total Dicairkan</div>
      <div class="kstat-trend"><i class="bi bi-arrow-down-circle me-1"></i> Riwayat pencairan</div>
    </div>
  </div>
</div>

{{-- Info + Tabs --}}
<div class="row g-4">

  {{-- Info Supplier --}}
  <div class="col-lg-3">
    <div class="card border-0 shadow-sm h-100 supplier-info-card">
      <div class="card-body p-4">
        <div class="text-center mb-3 pb-3 border-bottom supplier-head">
          <div class="mx-auto d-flex align-items-center justify-content-center mb-3 supplier-avatar">
            <i class="bi bi-truck"></i>
          </div>
          <div class="fw-700 mb-1 supplier-name">{{ $toko->nama }}</div>
          @if($toko->pemilik)
            <div class="supplier-owner"><i class="bi bi-person me-1"></i>{{ $toko->pemilik }}</div>
          @endif
        </div>

        <div class="d-flex flex-column gap-3 supplier-meta-list">
          @if($toko->telepon)
          <div class="supplier-meta-item">
            <i class="bi bi-telephone supplier-meta-icon"></i>
            <div>
              <div class="supplier-meta-label">Telepon</div>
              <div class="supplier-meta-value">{{ $toko->telepon }}</div>
            </div>
          </div>
          @endif
          @if($toko->email)
          <div class="supplier-meta-item">
            <i class="bi bi-envelope supplier-meta-icon"></i>
            <div>
              <div class="supplier-meta-label">Email</div>
              <div class="supplier-meta-value text-break">{{ $toko->email }}</div>
            </div>
          </div>
          @endif
          @if($toko->alamat || $toko->kota)
          <div class="supplier-meta-item">
            <i class="bi bi-geo-alt supplier-meta-icon"></i>
            <div>
              <div class="supplier-meta-label">Alamat</div>
              <div class="supplier-meta-value">{{ $toko->alamat }}{{ $toko->kota ? ', '.$toko->kota : '' }}</div>
            </div>
          </div>
          @endif
          @if($toko->deskripsi)
          <div class="supplier-meta-item">
            <i class="bi bi-info-circle supplier-meta-icon"></i>
            <div>
              <div class="supplier-meta-label">Catatan</div>
              <div class="supplier-meta-value supplier-note">{{ $toko->deskripsi }}</div>
            </div>
          </div>
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- Tabs Riwayat --}}
  <div class="col-lg-9">
    <ul class="nav nav-tabs mb-0">
      <li class="nav-item">
        <button class="nav-link fw-600 {{ $activeTab === 'produk' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#tabProduk" type="button">
          <i class="bi bi-box-seam me-1 is-f571fe9b"></i>Produk Supplier
          <span class="badge bg-secondary ms-1">{{ $produkList->total() }}</span>
        </button>
      </li>
      <li class="nav-item">
        <button class="nav-link fw-600 {{ $activeTab === 'mutasi' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#tabMutasi" type="button">
          <i class="bi bi-arrow-left-right me-1 text-success"></i>Mutasi Saldo
          <span class="badge bg-secondary ms-1">{{ $mutasiList->total() }}</span>
        </button>
      </li>
    </ul>

    <div class="tab-content">

      {{-- Tab: Daftar Produk Supplier --}}
      <div class="tab-pane fade {{ $activeTab === 'produk' ? 'show active' : '' }}" id="tabProduk">
        <div class="card border-0 shadow-sm is-e03ef8be">
          <div class="table-responsive">
            <table class="table tabel-koperasi mb-0">
              <thead class="is-98f358ac">
                <tr>
                  <th>Kode</th>
                  <th>Nama Produk</th>
                  <th class="text-center">Stok</th>
                  <th class="text-end">Harga Beli</th>
                  <th class="text-end">Harga Jual</th>
                  <th class="text-center">Status</th>
                </tr>
              </thead>
              <tbody>
                @forelse($produkList as $produk)
                <tr>
                  <td><code>{{ $produk->kode }}</code></td>
                  <td class="fw-600 is-feb6723d">{{ $produk->nama }}</td>
                  <td class="text-center">{{ number_format($produk->stok) }}</td>
                  <td class="text-end">Rp {{ number_format($produk->harga_beli, 0, ',', '.') }}</td>
                  <td class="text-end fw-600">Rp {{ number_format($produk->harga_jual, 0, ',', '.') }}</td>
                  <td class="text-center">
                    <span class="badge {{ $produk->is_aktif ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">
                      {{ $produk->is_aktif ? 'Aktif' : 'Nonaktif' }}
                    </span>
                  </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">
                  <i class="bi bi-box-seam is-a4ea9cc4"></i>
                  <div class="mt-2">Belum ada produk untuk supplier ini.</div>
                </td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
          @if($produkList->hasPages())
          <div class="card-footer bg-white border-top py-2 px-4">{{ $produkList->appends(['tab' => 'produk'])->links() }}</div>
          @endif
        </div>
      </div>

      {{-- Tab: Mutasi Saldo (Debet/Kredit) --}}
      <div class="tab-pane fade {{ $activeTab === 'mutasi' ? 'show active' : '' }}" id="tabMutasi">
        <div class="card border-0 shadow-sm is-e03ef8be">
          <div class="table-responsive">
            <table class="table tabel-koperasi mb-0">
              <thead class="is-98f358ac">
                <tr>
                  <th>Waktu</th>
                  <th>Aktivitas</th>
                  <th class="text-nowrap">Referensi</th>
                  <th class="text-end">Debet</th>
                  <th class="text-end">Kredit</th>
                  <th class="text-end">Saldo Akhir</th>
                  <th>Keterangan</th>
                </tr>
              </thead>
              <tbody>
                @forelse($mutasiList as $m)
                <tr>
                  <td class="is-70f18850">{{ \Illuminate\Support\Carbon::parse($m->waktu)->format('d M Y H:i') }}</td>
                  <td>
                    <span class="badge {{ $m->tipe === 'kredit' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                      {{ $m->tipe === 'kredit' ? 'Saldo Masuk' : 'Pencairan ' . strtoupper($m->metode ?? '') }}
                    </span>
                  </td>
                  <td class="text-nowrap">
                    @if($m->tipe === 'kredit' && $m->referensi)
                      <span class="mutasi-ref" title="{{ $m->referensi }}">{{ $m->referensi }}</span>
                    @elseif($m->tipe === 'debet')
                      <div class="fw-600">{{ $m->nama_penerima ?? '—' }}</div>
                      @if(($m->metode ?? '') === 'transfer' && $m->nama_bank)
                        <div class="text-muted is-9ee0bb07">{{ $m->nama_bank }} — {{ $m->no_rekening }}</div>
                      @endif
                    @else
                      <span class="text-muted">—</span>
                    @endif
                  </td>
                  <td class="text-end fw-600 text-danger">{{ $m->tipe === 'debet' ? '- Rp ' . number_format($m->jumlah, 0, ',', '.') : '—' }}</td>
                  <td class="text-end fw-600 text-success">{{ $m->tipe === 'kredit' ? '+ Rp ' . number_format($m->jumlah, 0, ',', '.') : '—' }}</td>
                  <td class="text-end fw-600 is-feb6723d">Rp {{ number_format($m->saldo_sesudah, 0, ',', '.') }}</td>
                  <td class="text-muted is-9ee0bb07">{{ $m->keterangan ?: '—' }}</td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">
                  <i class="bi bi-arrow-left-right is-a4ea9cc4"></i>
                  <div class="mt-2">Belum ada mutasi saldo.</div>
                </td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
          @if($mutasiList->hasPages())
          <div class="card-footer bg-white border-top py-2 px-4">{{ $mutasiList->appends(['tab' => 'mutasi'])->links() }}</div>
          @endif
        </div>
      </div>

    </div>
  </div>

</div>

{{-- Modal Cairkan Saldo --}}
<div class="modal fade" id="modalCairkan" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header is-13cd7689">
        <h5 class="modal-title"><i class="bi bi-cash-coin me-2"></i>Cairkan Saldo — {{ $toko->nama }}</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="{{ route('pos.toko.cairkan', $toko) }}">
        @csrf
        <div class="modal-body">
          <div class="rounded-3 p-3 mb-4 d-flex justify-content-between align-items-center is-7359c1bc">
            <span class="fw-600 text-success">Saldo Tersedia</span>
            <span class="fw-700 fs-5 is-16cde0ce">Rp {{ number_format($toko->saldo, 0, ',', '.') }}</span>
          </div>

          @if($errors->any())
          <div class="alert alert-danger py-2 mb-3 is-1e28117f">
            <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
          </div>
          @endif

          <div class="mb-3">
            <label class="form-label fw-600">Jumlah Pencairan <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text">Rp</span>
              <input type="number" name="jumlah" class="form-control form-control-lg text-end @error('jumlah') is-invalid @enderror"
                     value="{{ old('jumlah') }}" min="1" max="{{ $toko->saldo }}" required placeholder="0">
            </div>
            @error('jumlah')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
          </div>

          <div class="mb-3">
            <label class="form-label fw-600">Metode Pencairan <span class="text-danger">*</span></label>
            <div class="d-flex gap-2">
              <input type="radio" class="btn-check" name="metode" id="met_cash" value="cash" {{ old('metode','cash') === 'cash' ? 'checked' : '' }}>
              <label class="btn btn-outline-success flex-fill" for="met_cash"><i class="bi bi-cash me-1"></i>Cash / Tunai</label>
              <input type="radio" class="btn-check" name="metode" id="met_transfer" value="transfer" {{ old('metode') === 'transfer' ? 'checked' : '' }}>
              <label class="btn btn-outline-primary flex-fill" for="met_transfer"><i class="bi bi-bank me-1"></i>Transfer Bank</label>
            </div>
          </div>

          <div id="fieldTransfer" class="is-a94f46c0">
            <div class="mb-3">
              <label class="form-label fw-600">Nama Bank <span class="text-danger">*</span></label>
              <input type="text" name="nama_bank" class="form-control @error('nama_bank') is-invalid @enderror"
                     value="{{ old('nama_bank') }}" placeholder="cth. BRI, BCA, Mandiri">
              @error('nama_bank')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
              <label class="form-label fw-600">Nomor Rekening <span class="text-danger">*</span></label>
              <input type="text" name="no_rekening" class="form-control @error('no_rekening') is-invalid @enderror"
                     value="{{ old('no_rekening') }}" placeholder="xxxx-xxxx-xxxx">
              @error('no_rekening')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-600">Nama Penerima</label>
            <input type="text" name="nama_penerima" class="form-control"
                   value="{{ old('nama_penerima', $toko->pemilik ?? '') }}" placeholder="Nama pemilik / penerima dana">
          </div>

          <div class="mb-1">
            <label class="form-label fw-600">Keterangan</label>
            <textarea name="keterangan" class="form-control" rows="2" placeholder="Catatan pencairan...">{{ old('keterangan') }}</textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-success fw-bold px-4">
            <i class="bi bi-check2-circle me-1"></i>Proses Pencairan
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

@push('js')
<script>
  const fieldTransfer = document.getElementById('fieldTransfer');
  const metodeInputs = document.querySelectorAll('[name=metode]');

  function syncTransferField() {
    const selected = document.querySelector('[name=metode]:checked');
    if (!fieldTransfer || !selected) return;
    fieldTransfer.style.display = selected.value === 'transfer' ? '' : 'none';
  }

  metodeInputs.forEach(el => {
    el.addEventListener('change', syncTransferField);
  });

  syncTransferField();

  document.getElementById('modalCairkan')?.addEventListener('shown.bs.modal', syncTransferField);

  @if($errors->any())
    new bootstrap.Modal(document.getElementById('modalCairkan')).show();
  @endif
</script>
@endpush

@endsection
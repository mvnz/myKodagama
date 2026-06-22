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
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <div class="text-center mb-3 pb-3 border-bottom">
          <div class="mx-auto d-flex align-items-center justify-content-center mb-3 is-6ef98e8e"
              >
            <i class="bi bi-truck"></i>
          </div>
          <div class="fw-700 mb-1 is-3a464b54">{{ $toko->nama }}</div>
          @if($toko->pemilik)
            <div class="text-muted small"><i class="bi bi-person me-1"></i>{{ $toko->pemilik }}</div>
          @endif
        </div>
        <div class="d-flex flex-column gap-2 is-a7560b14">
          @if($toko->telepon)
          <div class="d-flex gap-2 align-items-start"><i class="bi bi-telephone text-muted mt-1 flex-shrink-0"></i><span>{{ $toko->telepon }}</span></div>
          @endif
          @if($toko->email)
          <div class="d-flex gap-2 align-items-start"><i class="bi bi-envelope text-muted mt-1 flex-shrink-0"></i><span class="text-break">{{ $toko->email }}</span></div>
          @endif
          @if($toko->alamat || $toko->kota)
          <div class="d-flex gap-2 align-items-start"><i class="bi bi-geo-alt text-muted mt-1 flex-shrink-0"></i><span>{{ $toko->alamat }}{{ $toko->kota ? ', '.$toko->kota : '' }}</span></div>
          @endif
          @if($toko->deskripsi)
          <div class="d-flex gap-2 align-items-start mt-1"><i class="bi bi-info-circle text-muted mt-1 flex-shrink-0"></i><span class="text-muted">{{ $toko->deskripsi }}</span></div>
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- Tabs Riwayat --}}
  <div class="col-lg-9">
    <ul class="nav nav-tabs mb-0">
      <li class="nav-item">
        <button class="nav-link active fw-600" data-bs-toggle="tab" data-bs-target="#tabSaldo">
          <i class="bi bi-clock-history me-1 is-f571fe9b"></i>Saldo Masuk
          <span class="badge bg-secondary ms-1">{{ $saldoLog->total() }}</span>
        </button>
      </li>
      <li class="nav-item">
        <button class="nav-link fw-600" data-bs-toggle="tab" data-bs-target="#tabCairan">
          <i class="bi bi-cash-coin me-1 text-success"></i>Pencairan
          <span class="badge bg-secondary ms-1">{{ $pencairanList->total() }}</span>
        </button>
      </li>
    </ul>

    <div class="tab-content">

      {{-- Tab: Saldo Masuk --}}
      <div class="tab-pane fade show active" id="tabSaldo">
        <div class="card border-0 shadow-sm is-e03ef8be">
          <div class="table-responsive">
            <table class="table tabel-koperasi mb-0">
              <thead class="is-98f358ac">
                <tr>
                  <th>Waktu</th>
                  <th>Transaksi</th>
                  <th class="text-end">Jumlah Masuk</th>
                  <th class="text-end">Saldo Sesudah</th>
                  <th>Keterangan</th>
                </tr>
              </thead>
              <tbody>
                @forelse($saldoLog as $log)
                <tr>
                  <td class="is-70f18850">{{ $log->created_at->format('d M Y H:i') }}</td>
                  <td>
                    @if($log->transaksi)
                      <a href="{{ route('pos.riwayat.show', $log->transaksi) }}" class="fw-600 text-decoration-none is-link-amber-sm">
                        {{ $log->transaksi->nomor }}
                      </a>
                    @else
                      <span class="text-muted">—</span>
                    @endif
                  </td>
                  <td class="text-end fw-600 text-success">+ Rp {{ number_format($log->jumlah, 0, ',', '.') }}</td>
                  <td class="text-end fw-600 is-feb6723d">Rp {{ number_format($log->saldo_sesudah, 0, ',', '.') }}</td>
                  <td class="text-muted is-9ee0bb07">{{ $log->keterangan }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted py-4">
                  <i class="bi bi-inbox is-a4ea9cc4"></i>
                  <div class="mt-2">Belum ada riwayat saldo.</div>
                </td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
          @if($saldoLog->hasPages())
          <div class="card-footer bg-white border-top py-2 px-4">{{ $saldoLog->links() }}</div>
          @endif
        </div>
      </div>

      {{-- Tab: Riwayat Pencairan --}}
      <div class="tab-pane fade" id="tabCairan">
        <div class="card border-0 shadow-sm is-e03ef8be">
          <div class="table-responsive">
            <table class="table tabel-koperasi mb-0">
              <thead class="is-98f358ac">
                <tr>
                  <th>Waktu</th>
                  <th>Metode</th>
                  <th>Penerima / Rekening</th>
                  <th class="text-end">Jumlah</th>
                  <th class="text-end">Saldo Sesudah</th>
                  <th>Keterangan</th>
                </tr>
              </thead>
              <tbody>
                @forelse($pencairanList as $cair)
                <tr>
                  <td class="is-70f18850">{{ $cair->created_at->format('d M Y H:i') }}</td>
                  <td>
                    <span class="badge {{ $cair->metode === 'cash' ? 'bg-success-subtle text-success' : 'bg-primary-subtle text-primary' }}">
                      {{ strtoupper($cair->metode) }}
                    </span>
                  </td>
                  <td class="is-9ee0bb07">
                    @if($cair->nama_penerima)<div class="fw-600">{{ $cair->nama_penerima }}</div>@endif
                    @if($cair->metode === 'transfer')<div class="text-muted">{{ $cair->nama_bank }} — {{ $cair->no_rekening }}</div>@endif
                    @if(!$cair->nama_penerima && $cair->metode === 'cash')<span class="text-muted">—</span>@endif
                  </td>
                  <td class="text-end fw-600 text-danger">- Rp {{ number_format($cair->jumlah, 0, ',', '.') }}</td>
                  <td class="text-end fw-600 is-feb6723d">Rp {{ number_format($cair->saldo_sesudah, 0, ',', '.') }}</td>
                  <td class="text-muted is-9ee0bb07">{{ $cair->keterangan ?? '—' }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">
                  <i class="bi bi-cash-coin is-a4ea9cc4"></i>
                  <div class="mt-2">Belum ada riwayat pencairan.</div>
                </td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
          @if($pencairanList->hasPages())
          <div class="card-footer bg-white border-top py-2 px-4">{{ $pencairanList->links() }}</div>
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
  document.querySelectorAll('[name=metode]').forEach(el => {
    el.addEventListener('change', () => {
      document.getElementById('fieldTransfer').style.display =
        el.value === 'transfer' && el.checked ? '' : 'none';
    });
  });
  @if($errors->any())
    new bootstrap.Modal(document.getElementById('modalCairkan')).show();
  @endif
</script>
@endpush

@endsection
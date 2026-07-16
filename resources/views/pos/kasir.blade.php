@extends('layouts.pos')
@section('judul','Kasir')
@section('breadcrumb')<li class="breadcrumb-item active">Kasir</li>@endsection

@push('css')

@endpush

@section('konten')
<div class="kasir-wrap">
  {{-- Panel Produk --}}
  <div class="produk-panel">
    <div class="produk-toolbar">
      <form method="GET" action="{{ route('pos.kasir') }}" id="filterForm">
        <div class="d-flex gap-2 mb-2">
          <div class="input-group input-group-sm flex-fill">
            <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
            <input type="text" id="kasirScanInput" name="q" value="{{ request('q') }}" class="form-control border-start-0" placeholder="Scan barcode / cari produk..." autocomplete="off">
          </div>
          @if(request('q') || request('kategori'))
          <a href="{{ route('pos.kasir') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x"></i></a>
          @endif
        </div>
        <div class="kategori-tabs">
          <a href="{{ route('pos.kasir', ['q'=>request('q')]) }}" class="{{ !request('kategori') ? 'active' : '' }}">Semua</a>
          @foreach($kategoris as $kat)
          <a href="{{ route('pos.kasir', ['kategori'=>$kat->id,'q'=>request('q')]) }}"
             class="{{ request('kategori') == $kat->id ? 'active' : '' }}">{{ $kat->nama }}</a>
          @endforeach
        </div>
      </form>
    </div>

    <div class="produk-grid">
      @if($produk->isEmpty())
        <div class="text-center text-muted py-5"><i class="bi bi-box-seam display-4 d-block mb-2"></i>Tidak ada produk</div>
      @else
      <div class="row g-2">
        @foreach($produk as $p)
        <div class="col-6 col-lg-4 col-xl-3">
          <div class="produk-card {{ $p->stok == 0 ? 'habis' : '' }}"
               onclick="{{ $p->stok > 0 ? "addToCart({$p->id},'{$p->kode}',".json_encode($p->nama).",{$p->harga_jual},{$p->stok})" : '' }}">
            <div class="kode">{{ $p->kode }}</div>
            <div class="nama">{{ $p->nama }}</div>
            <div class="d-flex align-items-end justify-content-between mt-2">
              <div class="harga">Rp {{ number_format($p->harga_jual,0,',','.') }}</div>
              <span class="badge stok-badge {{ $p->stok > 5 ? 'bg-success-subtle text-success' : ($p->stok > 0 ? 'bg-warning-subtle text-warning' : 'bg-danger-subtle text-danger') }}">
                {{ $p->stok > 0 ? "Stok: {$p->stok}" : 'Habis' }}
              </span>
            </div>
          </div>
        </div>
        @endforeach
      </div>
      @endif
    </div>
  </div>

  {{-- Panel Keranjang --}}
  <div class="cart-panel">
    <div class="cart-header d-flex align-items-center justify-content-between">
      <span><i class="bi bi-cart3 me-2"></i>Keranjang</span>
      <button class="btn btn-sm btn-outline-light py-0" onclick="clearCart()"><i class="bi bi-trash"></i></button>
    </div>

    <div class="cart-items" id="cartItems">
      <div class="empty-cart" id="emptyCart">
        <i class="bi bi-cart-x display-5 d-block mb-2"></i>
        <span>Pilih produk untuk memulai</span>
      </div>
    </div>

    <div class="cart-footer">
      <div class="total-row"><span>Subtotal</span><span id="subtotalText">Rp 0</span></div>
      <div class="total-row d-flex align-items-center gap-2">
        <span>Diskon</span>
        <input type="number" id="diskonInput" min="0" value="0"
               class="form-control form-control-sm text-end ms-auto is-cbea7c77"
               oninput="recalc()">
      </div>
      <div class="total-row grand"><span>TOTAL</span><span id="totalText">Rp 0</span></div>
      <button class="btn-bayar" id="btnBayar" disabled onclick="openBayar()">
        <i class="bi bi-credit-card me-2"></i>BAYAR
      </button>
    </div>
  </div>
</div>

{{-- Modal Pembayaran --}}
<div class="modal fade" id="modalBayar" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header is-edba2dcf">
        <h5 class="modal-title"><i class="bi bi-credit-card me-2"></i>Pembayaran</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="{{ route('pos.checkout') }}" id="checkoutForm">
        @csrf
        <input type="hidden" name="items" id="itemsInput">
        <input type="hidden" name="diskon" id="diskonHidden">
        <div class="modal-body">
          <div class="d-flex justify-content-between mb-3 fs-5 fw-bold">
            <span>Total</span><span id="modalTotal" class="is-2a4cda41">Rp 0</span>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Metode Bayar</label>
            <div class="d-flex gap-2">
              @foreach(['tunai'=>'Tunai','transfer'=>'Transfer','qris'=>'QRIS'] as $val=>$lbl)
              <input type="radio" class="btn-check" name="metode_bayar" id="m_{{ $val }}" value="{{ $val }}" {{ $val=='tunai'?'checked':'' }}>
              <label class="btn btn-outline-secondary btn-sm flex-fill text-center" for="m_{{ $val }}">{{ $lbl }}</label>
              @endforeach
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Jumlah Bayar</label>
            <input type="number" name="bayar" id="bayarInput" class="form-control form-control-lg text-end"
                   placeholder="0" min="0" oninput="hitungKembalian()">
          </div>
          <div class="p-3 rounded-3 text-center is-113f3c0e">
            <div class="text-muted small">Kembalian</div>
            <div class="fs-3 fw-bold text-success" id="kembalianText">Rp 0</div>
          </div>
          <div class="mt-3">
            <label class="form-label">Catatan (opsional)</label>
            <input type="text" name="catatan" class="form-control" placeholder="Catatan transaksi...">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="button" class="btn btn-warning text-white fw-bold px-4" id="btnProses" disabled onclick="submitCheckout()">
            <i class="bi bi-check2-circle me-1"></i>Proses
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Modal Sukses --}}
<div class="modal fade" id="modalSukses" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body text-center py-4">
        <div class="d-flex align-items-center justify-content-center mx-auto mb-3 is-ffd1c8dc"
            >
          <i class="bi bi-check2"></i>
        </div>
        <h5 class="fw-bold text-success mb-1">Transaksi Berhasil!</h5>
        <div class="text-muted small mb-3" id="suksesMeta"></div>

        <div class="rounded-3 p-3 mb-3 is-113f3c0e">
          <div class="d-flex justify-content-between mb-2">
            <span class="text-muted">Total</span>
            <span class="fw-700" id="suksesTotal"></span>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <span class="text-muted">Dibayar</span>
            <span class="fw-600" id="suksesBayar"></span>
          </div>
          <div class="d-flex justify-content-between border-top pt-2">
            <span class="fw-700">Kembalian</span>
            <span class="fw-700 text-success fs-5" id="suksesKembalian"></span>
          </div>
        </div>

        <div class="d-flex gap-2">
          <a href="#" id="btnLihatStruk" target="_blank" class="btn btn-outline-warning flex-fill">
            <i class="bi bi-printer me-1"></i>Lihat Struk
          </a>
          <button onclick="transaksiBaruDanTutup()" class="btn btn-warning text-white fw-bold flex-fill">
            <i class="bi bi-plus-lg me-1"></i>Transaksi Baru
          </button>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('js')
<script>
let cart = {};
let scanBusy = false;

function fmt(n){ return 'Rp ' + Number(n).toLocaleString('id-ID'); }

function addToCart(id, kode, nama, harga, stok){
  if(cart[id]){
    if(cart[id].qty >= stok){ alert('Stok tidak cukup!'); return; }
    cart[id].qty++;
  } else {
    cart[id] = { id, kode, nama, harga, stok, qty: 1 };
  }
  renderCart();
}

function changeQty(id, delta){
  if(!cart[id]) return;
  cart[id].qty += delta;
  if(cart[id].qty <= 0) delete cart[id];
  else if(cart[id].qty > cart[id].stok){ cart[id].qty = cart[id].stok; }
  renderCart();
}

function clearCart(){
  cart = {};
  renderCart();
}

function renderCart(){
  const keys = Object.keys(cart);
  const el = document.getElementById('cartItems');
  const empty = document.getElementById('emptyCart');

  if(keys.length === 0){
    el.innerHTML = '';
    el.appendChild(Object.assign(document.createElement('div'),{id:'emptyCart',className:'empty-cart',innerHTML:'<i class="bi bi-cart-x display-5 d-block mb-2"></i><span>Pilih produk untuk memulai</span>'}));
    document.getElementById('btnBayar').disabled = true;
    recalc();
    return;
  }

  let html = '';
  keys.forEach(id => {
    const item = cart[id];
    html += `<div class="cart-item">
      <div class="flex-fill">
        <div class="item-nama">${item.nama}</div>
        <div class="item-harga">${fmt(item.harga)} × ${item.qty} = ${fmt(item.harga * item.qty)}</div>
      </div>
      <div class="qty-ctrl">
        <button class="qty-btn" onclick="changeQty(${id},-1)"><i class="bi bi-dash"></i></button>
        <span class="qty-num">${item.qty}</span>
        <button class="qty-btn" onclick="changeQty(${id},1)"><i class="bi bi-plus"></i></button>
      </div>
    </div>`;
  });
  el.innerHTML = html;
  document.getElementById('btnBayar').disabled = false;
  recalc();
}

function recalc(){
  let sub = Object.values(cart).reduce((s,i) => s + i.harga * i.qty, 0);
  let diskon = parseInt(document.getElementById('diskonInput').value) || 0;
  let total = Math.max(0, sub - diskon);
  document.getElementById('subtotalText').textContent = fmt(sub);
  document.getElementById('totalText').textContent = fmt(total);
}

function openBayar(){
  let sub = Object.values(cart).reduce((s,i) => s + i.harga * i.qty, 0);
  let diskon = parseInt(document.getElementById('diskonInput').value) || 0;
  let total = Math.max(0, sub - diskon);
  document.getElementById('modalTotal').textContent = fmt(total);
  document.getElementById('bayarInput').value = '';
  document.getElementById('kembalianText').textContent = fmt(0);
  document.getElementById('btnProses').disabled = true;
  new bootstrap.Modal(document.getElementById('modalBayar')).show();
}

function hitungKembalian(){
  let sub = Object.values(cart).reduce((s,i) => s + i.harga * i.qty, 0);
  let diskon = parseInt(document.getElementById('diskonInput').value) || 0;
  let total = Math.max(0, sub - diskon);
  let bayar = parseInt(document.getElementById('bayarInput').value) || 0;
  let kmb = bayar - total;
  document.getElementById('kembalianText').textContent = fmt(Math.max(0, kmb));
  document.getElementById('kembalianText').style.color = kmb >= 0 ? '#16a34a' : '#dc2626';
  document.getElementById('btnProses').disabled = (bayar < total);
}

document.getElementById('checkoutForm').addEventListener('submit', function(e){ e.preventDefault(); });

function submitCheckout(){
  const items = Object.values(cart).map(i => ({id: i.id, qty: i.qty}));
  const diskon = parseInt(document.getElementById('diskonInput').value) || 0;
  const form   = document.getElementById('checkoutForm');
  const data   = new FormData(form);
  data.set('items',  JSON.stringify(items));
  data.set('diskon', diskon);

  const btn = document.getElementById('btnProses');
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Memproses...';

  fetch(form.action, {
    method: 'POST',
    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('[name=_token]').value },
    body: data,
  })
  .then(r => r.json())
  .then(res => {
    if (res.sukses) {
      // Tutup modal bayar
      bootstrap.Modal.getInstance(document.getElementById('modalBayar')).hide();

      // Isi modal sukses
      document.getElementById('suksesMeta').textContent = res.nomor;
      document.getElementById('suksesTotal').textContent = fmt(res.total);
      document.getElementById('suksesBayar').textContent = fmt(res.bayar);
      document.getElementById('suksesKembalian').textContent = fmt(res.kembalian);
      document.getElementById('btnLihatStruk').href = res.struk_url;

      // Tampilkan modal sukses
      new bootstrap.Modal(document.getElementById('modalSukses')).show();
    } else {
      alert(res.error || 'Terjadi kesalahan.');
      btn.disabled = false;
      btn.innerHTML = '<i class="bi bi-check2-circle me-1"></i>Proses';
    }
  })
  .catch(() => {
    alert('Gagal terhubung ke server.');
    btn.disabled = false;
    btn.innerHTML = '<i class="bi bi-check2-circle me-1"></i>Proses';
  });
}

function transaksiBaruDanTutup(){
  bootstrap.Modal.getInstance(document.getElementById('modalSukses')).hide();
  clearCart();
  document.getElementById('diskonInput').value = 0;
  recalc();
  // Reset tombol proses untuk transaksi berikutnya
  const btn = document.getElementById('btnProses');
  btn.disabled = true;
  btn.innerHTML = '<i class="bi bi-check2-circle me-1"></i>Proses';
}

const scanInput = document.getElementById('kasirScanInput');
const filterForm = document.getElementById('filterForm');

async function scanAndAddProduct(rawValue) {
  const code = (rawValue || '').trim();
  if (!code || scanBusy) return;

  scanBusy = true;

  try {
    const res = await fetch(`{{ route('pos.kasir.scan') }}?q=${encodeURIComponent(code)}`, {
      headers: { 'Accept': 'application/json' }
    });

    const data = await res.json();

    if (res.ok && data.ok && data.product) {
      const p = data.product;
      addToCart(p.id, p.kode, p.nama, p.harga, p.stok);
      scanInput.value = '';
      scanInput.focus();
      return;
    }

    if (res.status === 404) {
      filterForm.submit();
      return;
    }

    alert(data.message || 'Produk tidak dapat diproses.');
  } catch (e) {
    alert('Gagal terhubung ke server scanner kasir.');
  } finally {
    scanBusy = false;
  }
}

if (scanInput) {
  scanInput.addEventListener('keydown', function (e) {
    if (e.key !== 'Enter') return;
    e.preventDefault();
    scanAndAddProduct(scanInput.value);
  });
}
</script>
@endpush

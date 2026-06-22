@extends('layouts.aplikasi')

@section('judul', 'Detail Anggota')

@section('konten')
<div class="row g-4">
  <!-- Profile Card -->
  <div class="col-xl-4">
    <div class="form-card text-center">
      <!-- Avatar / Foto -->
      @if($anggota->foto)
        <img src="{{ Storage::url($anggota->foto) }}" alt="{{ $anggota->nama }}"
          class="rounded-circle object-fit-cover mx-auto d-block mb-3 shadow"
          style="width:100px;height:100px;border:4px solid #e5ecfa">
      @else
        <div class="d-flex align-items-center justify-content-center rounded-circle mx-auto mb-3 text-white shadow is-184cc61f"
         >
          {{ strtoupper(substr($anggota->nama, 0, 1)) }}
        </div>
      @endif

      <h5 class="fw-700 mb-1 is-5ca88772">{{ $anggota->nama }}</h5>
      <code class="text-primary small">{{ $anggota->nomor_anggota }}</code>

      <div class="d-flex justify-content-center mt-2 mb-3">
        <span class="badge-status {{ $anggota->status === 'aktif' ? 'badge-aktif' : 'badge-ditolak' }}">
          {{ $anggota->status === 'aktif' ? 'Aktif' : 'Tidak Aktif' }}
        </span>
      </div>

      <!-- Quick Stats -->
      <div class="row g-2 mb-4">
        <div class="col-6">
          <div class="kstat-card kstat-blue is-512ba245">
            <div class="kstat-deco is-8188d9b8"></div>
            <div class="kstat-icon is-5b20e6ef"><i class="bi bi-piggy-bank-fill"></i></div>
            <div class="kstat-value kstat-value-sm is-fd91441e">Rp {{ number_format($anggota->totalSimpanan(), 0, ',', '.') }}</div>
            <div class="kstat-label is-24aba5ad">Total Simpanan</div>
          </div>
        </div>
        <div class="col-6">
          <div class="kstat-card kstat-amber is-512ba245">
            <div class="kstat-deco is-8188d9b8"></div>
            <div class="kstat-icon is-5b20e6ef"><i class="bi bi-cash-coin"></i></div>
            <div class="kstat-value kstat-value-sm is-fd91441e">Rp {{ number_format($anggota->totalPinjamanAktif(), 0, ',', '.') }}</div>
            <div class="kstat-label is-24aba5ad">Pinjaman Aktif</div>
          </div>
        </div>
      </div>

      <div class="d-flex flex-column gap-2">
        <a href="{{ route('anggota.rekap', $anggota) }}" class="btn btn-primary d-flex align-items-center justify-content-center gap-2">
          <i class="bi bi-bar-chart-line"></i> Rekap Profil
        </a>
        <a href="{{ route('anggota.kartu', $anggota) }}" target="_blank" class="btn btn-outline-secondary d-flex align-items-center justify-content-center gap-2">
          <i class="bi bi-credit-card-2-front"></i> Kartu Anggota
        </a>
        <a href="{{ route('anggota.edit', $anggota) }}" class="btn btn-warning d-flex align-items-center justify-content-center gap-2">
          <i class="bi bi-pencil"></i> Ubah Data
        </a>
        <form action="{{ route('anggota.destroy', $anggota) }}" method="POST"
          onsubmit="return confirm('Hapus anggota ini? Tindakan tidak dapat dibatalkan.')">
          @csrf @method('DELETE')
          <button type="submit" class="btn btn-outline-danger w-100 d-flex align-items-center justify-content-center gap-2">
            <i class="bi bi-trash"></i> Hapus Anggota
          </button>
        </form>
      </div>
    </div>
  </div>

  <!-- Detail & Riwayat -->
  <div class="col-xl-8 d-flex flex-column gap-4">
    <!-- Informasi Pribadi -->
    <div class="form-card">
      <h6 class="fw-700 mb-4 is-5ca88772">
        <i class="bi bi-person-vcard me-2 is-bc519fd0"></i>Informasi Pribadi
      </h6>
      <div class="row g-3">
        @php
          $fields = [
            ['NIK', $anggota->nik ?? '—'],
            ['Tempat, Tgl Lahir', trim(($anggota->tempat_lahir ?? '') . ($anggota->tanggal_lahir ? ', ' . $anggota->tanggal_lahir->format('d M Y') : '')) ?: '—'],
            ['Jenis Kelamin', $anggota->jenis_kelamin ?? '—'],
            ['Pekerjaan/Unit Kerja', $anggota->unitKerja->nama ?? '-'],
            ['Telepon', $anggota->telepon ?? '—'],
            ['Email', $anggota->email ?? '—'],
            ['Alamat', $anggota->alamat ?? '—'],
            ['Tgl Bergabung', $anggota->tanggal_bergabung ? $anggota->tanggal_bergabung->format('d M Y') : '—'],
          ];
        @endphp
        @foreach($fields as [$label, $value])
          <div class="col-md-6">
            <div class="is-b403a39c">{{ $label }}</div>
            <div class="is-d8aa2421">{{ $value }}</div>
          </div>
        @endforeach
      </div>
    </div>

    <!-- Riwayat Simpanan -->
    <div class="form-card p-0 overflow-hidden">
      <div class="p-4 border-bottom d-flex align-items-center justify-content-between">
        <h6 class="mb-0 fw-700 is-5ca88772">
          <i class="bi bi-piggy-bank me-2 is-29fb9df2"></i>Riwayat Simpanan
        </h6>
        @can('simpanan.tambah')
        <a href="{{ route('simpanan.create') }}?anggota_id={{ $anggota->id }}" class="btn btn-sm btn-success">
          <i class="bi bi-plus"></i> Tambah
        </a>
        @endcan
      </div>
      <div class="table-responsive">
        <table class="koperasi-table mb-0 is-628f2cdc">
          <thead>
            <tr>
              <th>Jenis</th>
              <th>Jumlah</th>
              <th>Tanggal</th>
              <th>Keterangan</th>
            </tr>
          </thead>
          <tbody>
            @forelse($anggota->simpanan()->latest('tanggal')->take(10)->get() as $simpanan)
              <tr>
                <td>
                  @php $jenisMap = ['pokok'=>'badge-aktif','wajib'=>'badge-disetujui','sukarela'=>'badge-sudah']; @endphp
                  <span class="badge-status {{ $jenisMap[$simpanan->jenis_simpanan] ?? '' }}">
                    {{ ucfirst($simpanan->jenis_simpanan) }}
                  </span>
                </td>
                <td class="fw-600 is-29fb9df2">Rp {{ number_format($simpanan->jumlah, 0, ',', '.') }}</td>
                <td>{{ $simpanan->tanggal->format('d M Y') }}</td>
                <td class="text-muted">{{ $simpanan->keterangan ?? '—' }}</td>
              </tr>
            @empty
              <tr><td colspan="4" class="text-center text-muted py-3">Belum ada simpanan</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    <!-- Riwayat Pinjaman -->
    <div class="form-card p-0 overflow-hidden">
      <div class="p-4 border-bottom d-flex align-items-center justify-content-between">
        <h6 class="mb-0 fw-700 is-5ca88772">
          <i class="bi bi-cash-coin me-2 is-2a4cda41"></i>Riwayat Pinjaman
        </h6>
        <a href="{{ route('pinjaman.create') }}?anggota_id={{ $anggota->id }}" class="btn btn-sm btn-warning text-white">
          <i class="bi bi-plus"></i> Ajukan
        </a>
      </div>
      <div class="table-responsive">
        <table class="koperasi-table mb-0 is-628f2cdc">
          <thead>
            <tr>
              <th>No. Pinjaman</th>
              <th>Jumlah</th>
              <th>Status</th>
              <th>Tanggal</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            @forelse($anggota->pinjaman()->latest()->get() as $pinjaman)
              <tr>
                <td><code>{{ $pinjaman->nomor_pinjaman }}</code></td>
                <td>Rp {{ number_format($pinjaman->jumlah_pinjaman, 0, ',', '.') }}</td>
                <td>
                  @php $badgeMap = ['diajukan'=>'badge-diajukan','disetujui'=>'badge-disetujui','ditolak'=>'badge-ditolak','lunas'=>'badge-lunas']; @endphp
                  <span class="badge-status {{ $badgeMap[$pinjaman->status] ?? '' }}">{{ ucfirst($pinjaman->status) }}</span>
                </td>
                <td>{{ $pinjaman->tanggal_pengajuan->format('d M Y') }}</td>
                <td>
                  <a href="{{ route('pinjaman.show', $pinjaman) }}" class="btn btn-sm btn-outline-info">
                    <i class="bi bi-eye"></i>
                  </a>
                </td>
              </tr>
            @empty
              <tr><td colspan="5" class="text-center text-muted py-3">Belum ada pinjaman</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection

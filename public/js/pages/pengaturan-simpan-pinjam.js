// ── Simpanan Wajib per Jenis ────────────────────────────────────
(function () {
    const tbody = document.getElementById('bodyWajibJenis');
    if (!tbody) return;

    function reindex() {
        tbody.querySelectorAll('tr').forEach((tr, i) => {
            tr.querySelectorAll('[name]').forEach(el => {
                el.name = el.name.replace(/wajib_per_jenis\[\d+\]/, `wajib_per_jenis[${i}]`);
            });
        });
    }

    function buatBaris(idx) {
        const tr = document.createElement('tr');
        tr.innerHTML = `
      <td>
        <input type="text" class="form-control form-control-sm"
          name="wajib_per_jenis[${idx}][jenis]"
          placeholder="cth: Anggota Biasa" required>
      </td>
      <td>
        <div class="input-group input-group-sm">
          <span class="input-group-text">Rp</span>
          <input type="number" class="form-control"
            name="wajib_per_jenis[${idx}][nominal]"
            value="0" min="0" step="1000">
        </div>
      </td>
      <td class="text-center">
        <button type="button" class="btn btn-sm btn-outline-danger px-2 btn-hapus-jenis" title="Hapus">
          <i class="bi bi-trash"></i>
        </button>
      </td>`;
        return tr;
    }

    document.getElementById('btnTambahJenis')?.addEventListener('click', () => {
        const idx = tbody.querySelectorAll('tr').length;
        const tr = buatBaris(idx);
        tbody.appendChild(tr);
        tr.querySelector('input[type=text]').focus();
    });

    tbody.addEventListener('click', e => {
        const btn = e.target.closest('.btn-hapus-jenis');
        if (!btn) return;
        if (tbody.querySelectorAll('tr').length <= 1) return;
        btn.closest('tr').remove();
        reindex();
    });
})();

// ── Jenis Pinjaman ─────────────────────────────────────────────
(function () {
    const tbodyJP = document.getElementById('bodyJenisPinjaman');
    const kosong  = document.getElementById('kosongJenisPinjaman');
    if (!tbodyJP) return;

  function initJenisAnggotaSelect(scope) {
    if (typeof TomSelect === 'undefined') return;

    const root = scope || document;
    root.querySelectorAll('select.js-jenis-anggota-select').forEach(el => {
      if (el.tomselect) return;
      try {
        new TomSelect(el, {
          allowEmptyOption: true,
          placeholder: el.dataset.placeholder || 'Semua Jenis Anggota',
          dropdownParent: 'body',
          onInitialize: function () {
            this.wrapper.classList.add('ts-jenis-anggota');
            this.dropdown.classList.add('ts-jenis-anggota-dropdown');
          },
        });
      } catch (_) {}
    });
  }

  let jenisAnggotaList = [];
  try {
    jenisAnggotaList = JSON.parse(tbodyJP.dataset.jenisAnggota || '[]');
  } catch (_) {
    jenisAnggotaList = [];
  }

  const escapeHtml = (str) => String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');

  const jenisAnggotaOptions = ['<option value="">Semua Jenis Anggota</option>']
    .concat(jenisAnggotaList.map(j => `<option value="${escapeHtml(j)}">${escapeHtml(j)}</option>`))
    .join('');

    function reindexJP() {
        tbodyJP.querySelectorAll('tr').forEach((tr, i) => {
            tr.querySelectorAll('[name]').forEach(el => {
                el.name = el.name.replace(/jenis_pinjaman\[\d+\]/, `jenis_pinjaman[${i}]`);
            });
        });
    }

    function buatBarisJP(idx) {
        const tr = document.createElement('tr');
        tr.innerHTML = `
      <td><select class="form-select form-select-sm js-jenis-anggota-select" name="jenis_pinjaman[${idx}][jenis_anggota]" data-placeholder="Semua Jenis Anggota">${jenisAnggotaOptions}</select></td>
      <td><input type="text" class="form-control form-control-sm" name="jenis_pinjaman[${idx}][nama]" placeholder="cth: Pinjaman Reguler" required></td>
      <td><div class="input-group input-group-sm">
        <input type="number" class="form-control" name="jenis_pinjaman[${idx}][bunga]" min="0" max="100" step="0.1" placeholder="1.5">
        <span class="input-group-text">%</span>
      </div></td>
      <td><div class="input-group input-group-sm">
        <span class="input-group-text">Rp</span>
        <input type="number" class="form-control" name="jenis_pinjaman[${idx}][min_pinjaman]" min="0" step="100000" placeholder="500000">
      </div></td>
      <td><div class="input-group input-group-sm">
        <span class="input-group-text">Rp</span>
        <input type="number" class="form-control" name="jenis_pinjaman[${idx}][maks_pinjaman]" min="0" step="1000000" placeholder="10000000">
      </div></td>
      <td><div class="input-group input-group-sm">
        <input type="number" class="form-control" name="jenis_pinjaman[${idx}][min_jangka]" min="1" step="1" placeholder="3">
        <span class="input-group-text">bln</span>
      </div></td>
      <td><div class="input-group input-group-sm">
        <input type="number" class="form-control" name="jenis_pinjaman[${idx}][maks_jangka]" min="1" step="1" placeholder="24">
        <span class="input-group-text">bln</span>
      </div></td>
      <td>
        <button type="button" class="btn btn-sm btn-outline-danger btn-hapus-jenis-pinjaman" title="Hapus">
          <i class="bi bi-trash3"></i>
        </button>
      </td>`;
        return tr;
    }

    document.getElementById('btnTambahJenisPinjaman')?.addEventListener('click', () => {
        if (kosong) kosong.style.display = 'none';
        const idx = tbodyJP.querySelectorAll('tr').length;
        const tr  = buatBarisJP(idx);
        tbodyJP.appendChild(tr);
      initJenisAnggotaSelect(tr);
        tr.querySelector('input[type=text]')?.focus();
    });

    tbodyJP.addEventListener('click', e => {
        const btn = e.target.closest('.btn-hapus-jenis-pinjaman');
        if (!btn) return;
        btn.closest('tr').remove();
        reindexJP();
        if (kosong && tbodyJP.querySelectorAll('tr').length === 0) kosong.style.display = '';
    });

      initJenisAnggotaSelect(document);
})();

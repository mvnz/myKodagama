// posisiCount dideklarasikan secara inline di blade:
// <script>var _posisiCount = {{ count($struktur) }};</script>
var posisiCount = (typeof _posisiCount !== 'undefined') ? _posisiCount : 0;

function tambahPosisi() {
    const container = document.getElementById('strukturContainer');
    const idx = posisiCount++;
    let options = '';
    for (let b = 1; b <= 10; b++) {
        options += `<option value="${b}"${b === 1 ? ' selected' : ''}>Baris ${b}</option>`;
    }
    const div = document.createElement('div');
    div.className = 'row g-2 align-items-center posisi-item';
    div.innerHTML = `
    <div class="col-12 col-md">
      <input type="text" name="struktur[${idx}][jabatan]" class="form-control"
        placeholder="Jabatan (cth: Ketua, Manajer, Koordinator...)">
    </div>
    <div class="col-12 col-md">
      <input type="text" name="struktur[${idx}][nama]" class="form-control"
        placeholder="Nama lengkap">
    </div>
    <div class="col-12 col-md" style="flex:2">
      <input type="text" name="struktur[${idx}][periode]" class="form-control"
        placeholder="cth: 2024-2027" maxlength="50">
    </div>
    <div class="col-6 col-md-auto" style="width:110px">
      <select name="struktur[${idx}][baris]" class="form-select form-select-sm" title="Baris di org chart">
        ${options}
      </select>
    </div>
    <div class="col-6 col-md-auto" style="width:38px">
      <button type="button" class="btn btn-sm btn-outline-danger px-2"
        onclick="hapusPosisi(this)" title="Hapus">
        <i class="bi bi-trash"></i>
      </button>
    </div>`;
    container.appendChild(div);
    div.querySelector('input').focus();
}

function hapusPosisi(btn) {
    const container = document.getElementById('strukturContainer');
    if (container.querySelectorAll('.posisi-item').length <= 1) return;
    btn.closest('.posisi-item').remove();
}

window.tambahPosisi = tambahPosisi;
window.hapusPosisi  = hapusPosisi;

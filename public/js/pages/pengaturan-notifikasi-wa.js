// _waTestUrl dideklarasikan inline di blade:
// <script>var _waTestUrl = '{{ route("pengaturan.notifikasi-wa.test") }}';</script>

// Toggle show/hide token
document.getElementById('toggleToken')?.addEventListener('click', function () {
    const input = document.getElementById('waToken');
    const icon  = document.getElementById('tokenEyeIcon');
    if (!input) return;
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'bi bi-eye';
    }
});

// Test kirim WA
document.getElementById('btnTest')?.addEventListener('click', function () {
    const nomor  = document.getElementById('testNomor').value.trim();
    const result = document.getElementById('testResult');

    if (!nomor) {
        result.className = 'mt-2 rounded-3 px-3 py-2 alert alert-warning';
        result.textContent = 'Masukkan nomor tujuan terlebih dahulu.';
        return;
    }

    this.disabled = true;
    this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Mengirim...';

    fetch(window._waTestUrl || '', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({ nomor }),
    })
    .then(r => r.json())
    .then(data => {
        result.className = 'mt-2 rounded-3 px-3 py-2 ' + (data.sukses ? 'alert alert-success' : 'alert alert-danger');
        result.innerHTML = '<i class="bi bi-' + (data.sukses ? 'check-circle-fill' : 'x-circle-fill') + ' me-1"></i>' + data.pesan;
    })
    .catch(() => {
        result.className = 'mt-2 rounded-3 px-3 py-2 alert alert-danger';
        result.textContent = 'Terjadi kesalahan koneksi.';
    })
    .finally(() => {
        this.disabled = false;
        this.innerHTML = '<i class="bi bi-send me-1"></i> Kirim Test';
    });
});

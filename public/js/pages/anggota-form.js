// Preview foto yang diupload
const fotoInput = document.getElementById('fotoInput');
if (fotoInput) {
    fotoInput.addEventListener('change', function () {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = e => {
                const preview = document.getElementById('fotoPreview');
                const box     = document.getElementById('fotoPreviewBox');
                if (preview) preview.src = e.target.result;
                if (box)     box.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    });
}

// Toggle show/hide password (untuk halaman ubah)
function togglePass(id, btn) {
    const el = document.getElementById(id);
    if (!el) return;
    if (el.type === 'password') {
        el.type = 'text';
        btn.querySelector('i').className = 'bi bi-eye-slash';
    } else {
        el.type = 'password';
        btn.querySelector('i').className = 'bi bi-eye';
    }
}
window.togglePass = togglePass;

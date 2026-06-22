function formatRp(val) {
    return 'Rp ' + Math.max(0, val).toLocaleString('id-ID');
}
function updatePreview() {
    const pendapatan = parseFloat(document.querySelector('[name=total_pendapatan]').value) || 0;
    const biaya      = parseFloat(document.querySelector('[name=total_biaya]').value)      || 0;
    document.getElementById('previewShu').textContent = formatRp(pendapatan - biaya);
}
document.querySelector('[name=total_pendapatan]')?.addEventListener('input', updatePreview);
// total_biaya is read-only, run preview once on load
document.addEventListener('DOMContentLoaded', updatePreview);

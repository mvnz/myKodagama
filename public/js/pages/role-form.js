function pilihSemua() {
    document.querySelectorAll('.permission-check').forEach(cb => cb.checked = true);
}
function hapusSemua() {
    document.querySelectorAll('.permission-check').forEach(cb => cb.checked = false);
}
function pilihGrup(slug) {
    document.querySelectorAll('.grup-' + slug).forEach(cb => cb.checked = true);
}
window.pilihSemua = pilihSemua;
window.hapusSemua = hapusSemua;
window.pilihGrup  = pilihGrup;

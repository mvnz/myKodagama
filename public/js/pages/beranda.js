function togglePw() {
    const input = document.getElementById('password');
    const icon  = document.getElementById('pwIcon');
    if (!input || !icon) return;
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'bi bi-eye-slash-fill';
    } else {
        input.type = 'password';
        icon.className = 'bi bi-eye-fill';
    }
}
window.togglePw = togglePw;

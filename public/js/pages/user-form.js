// _isEdit dideklarasikan inline di blade:
// <script>var _isEdit = {{ $isEdit ? 'true' : 'false' }};</script>

if (!window._isEdit) {
    const nameField     = document.getElementById('nameField');
    const usernameField = document.getElementById('usernameField');
    if (nameField && usernameField) {
        nameField.addEventListener('input', function () {
            if (!usernameField.dataset.manual) {
                usernameField.value = this.value.toLowerCase().trim()
                    .replace(/\s+/g, '_').replace(/[^a-z0-9_\-]/g, '');
            }
        });
        usernameField.addEventListener('input', function () {
            this.dataset.manual = this.value ? '1' : '';
        });
    }
}

function toggleVis(btnId, inputId) {
    document.getElementById(btnId)?.addEventListener('click', function () {
        const input = document.getElementById(inputId);
        const icon  = this.querySelector('i');
        if (!input) return;
        if (input.type === 'password') {
            input.type = 'text'; icon.classList.replace('bi-eye', 'bi-eye-slash');
        } else {
            input.type = 'password'; icon.classList.replace('bi-eye-slash', 'bi-eye');
        }
    });
}
toggleVis('togglePassword', 'passwordField');
toggleVis('toggleConfirm',  'passwordConfirmField');

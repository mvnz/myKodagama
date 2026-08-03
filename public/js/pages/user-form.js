// _isEdit dideklarasikan inline di blade:
// <script>var _isEdit = {{ $isEdit ? 'true' : 'false' }};</script>

const nameField = document.getElementById('nameField');
const emailField = document.querySelector('input[name="email"]');
const usernameField = document.getElementById('usernameField');

function normalizeToUsername(text) {
    return (text || '')
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9_\-]+/g, '_')
        .replace(/^[_-]+|[_-]+$/g, '')
        .slice(0, 50);
}

function usernameFromEmail(email) {
    const localPart = (email || '').split('@')[0] || '';
    return normalizeToUsername(localPart);
}

function usernameFromName(name) {
    return normalizeToUsername((name || '').replace(/\s+/g, '_'));
}

function syncUsernameAuto() {
    if (!usernameField || usernameField.dataset.manual) {
        return;
    }

    const byEmail = usernameFromEmail(emailField?.value || '');
    const byName = usernameFromName(nameField?.value || '');
    usernameField.value = byEmail || byName;
}

if (usernameField) {
    if (usernameField.value.trim() !== '') {
        usernameField.dataset.manual = '1';
    }

    usernameField.addEventListener('input', function () {
        this.dataset.manual = this.value.trim() ? '1' : '';
        if (!this.dataset.manual) {
            syncUsernameAuto();
        }
    });

    emailField?.addEventListener('input', syncUsernameAuto);
    nameField?.addEventListener('input', syncUsernameAuto);
    syncUsernameAuto();
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

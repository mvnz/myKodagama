// Tom Select — init semua form-select (kecuali form-select-sm)
function initTomSelect(scope) {
    const root = scope || document;
    root.querySelectorAll('select.form-select:not(.form-select-sm):not(.ts-hidden-accessible)').forEach(el => {
        if (el.tomselect) return;
        const opts = {
            allowEmptyOption: true,
            placeholder: el.dataset.placeholder || el.querySelector('option[value=""]')?.textContent?.trim() || 'Pilih...',
            plugins: [],
        };
        if (el.options.length <= 5) {
            opts.controlInput = null;
        }
        try { new TomSelect(el, opts); } catch(e) {}
    });
}
document.addEventListener('DOMContentLoaded', () => initTomSelect());
window.initTomSelect = initTomSelect;

document.getElementById('cariNama')?.addEventListener('input', function () {
    const q    = this.value.toLowerCase().trim();
    const rows = document.querySelectorAll('#belumSetorBody tr');
    let visible = 0;
    rows.forEach(row => {
        const nama  = row.querySelector('td:nth-child(2)')?.textContent.toLowerCase() ?? '';
        const nomor = row.querySelector('td:nth-child(3)')?.textContent.toLowerCase() ?? '';
        const show  = !q || nama.includes(q) || nomor.includes(q);
        row.style.display = show ? '' : 'none';
        if (show) visible++;
    });
    const countEl = document.getElementById('belumSetorCount');
    if (countEl) {
        const prefix = countEl.textContent.split('·')[0].trim();
        countEl.textContent = prefix + ' · ' + visible + ' anggota' + (q ? ' (filter)' : '');
    }
});

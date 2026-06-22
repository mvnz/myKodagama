const fmt = v => 'Rp ' + Math.round(v).toLocaleString('id-ID');
const fmtShort = v => {
    if (v >= 1_000_000) return 'Rp ' + (v / 1_000_000).toLocaleString('id-ID', { maximumFractionDigits: 1 }) + ' jt';
    if (v >= 1_000)     return 'Rp ' + (v / 1_000).toLocaleString('id-ID', { maximumFractionDigits: 0 }) + ' rb';
    return 'Rp ' + v;
};

// Sesuaikan kolom dari jenis pinjaman
const selJenis = document.getElementById('jenisPinjamanSelect');
if (selJenis) {
    function terapkanJenis(opt) {
        const elBunga       = document.getElementById('bungaPinjaman');
        const elJumlah      = document.getElementById('jumlahPinjaman');
        const elJangka      = document.getElementById('jangkaWaktu');
        const elInfoJumlah  = document.getElementById('infoJumlah');
        const elInfoJangka  = document.getElementById('infoJangka');
        const elInfoBunga   = document.getElementById('infoBunga');
        const elIconBunga   = document.getElementById('iconBunga');
        const infoBox       = document.getElementById('infoJenisPinjaman');
        const infoNama      = document.getElementById('infoJenisNama');
        const infoBadge     = document.getElementById('infoBungaBadge');
        const infoRpRange   = document.getElementById('infoRangePinjaman');
        const infoJkRange   = document.getElementById('infoRangeJangka');

        if (!opt.value) {
            elBunga.readOnly = false;
            elBunga.classList.remove('bg-light');
            elIconBunga.innerHTML = '%';
            elJumlah.min = 0; elJumlah.max = '';
            elJumlah.placeholder = '0';
            elJangka.min = 1; elJangka.max = 60;
            elJangka.placeholder = '12';
            if (elInfoJumlah) elInfoJumlah.textContent = '';
            if (elInfoJangka) elInfoJangka.textContent = '';
            if (elInfoBunga)  elInfoBunga.textContent  = '';
            if (infoBox) infoBox.classList.add('d-none');
            return;
        }

        const bunga        = parseFloat(opt.dataset.bunga)      || 0;
        const minPinjaman  = parseInt(opt.dataset.minPinjaman)  || 0;
        const maksPinjaman = parseInt(opt.dataset.maksPinjaman) || 0;
        const minJangka    = parseInt(opt.dataset.minJangka)    || 1;
        const maksJangka   = parseInt(opt.dataset.maksJangka)   || 60;

        elBunga.value    = bunga;
        elBunga.readOnly = true;
        elBunga.classList.add('bg-light');
        elIconBunga.innerHTML = '<i class="bi bi-lock-fill text-primary" title="Diatur dari jenis pinjaman"></i>';

        elJumlah.min         = minPinjaman;
        elJumlah.max         = maksPinjaman || '';
        elJumlah.placeholder = minPinjaman.toLocaleString('id-ID');
        if (elInfoJumlah) elInfoJumlah.innerHTML =
            `<i class="bi bi-info-circle me-1"></i>Batas: ${fmtShort(minPinjaman)} – ${fmtShort(maksPinjaman)}`;

        elJangka.min         = minJangka;
        elJangka.max         = maksJangka;
        elJangka.placeholder = minJangka;
        if (parseInt(elJangka.value) < minJangka) elJangka.value = minJangka;
        if (parseInt(elJangka.value) > maksJangka) elJangka.value = maksJangka;
        if (elInfoJangka) elInfoJangka.innerHTML =
            `<i class="bi bi-info-circle me-1"></i>Batas: ${minJangka} – ${maksJangka} bulan`;

        if (elInfoBunga) elInfoBunga.innerHTML =
            `<i class="bi bi-lock-fill me-1 text-primary"></i>Bunga ditetapkan dari jenis pinjaman`;

        if (infoBox) {
            infoBox.classList.remove('d-none');
            if (infoNama)    infoNama.textContent    = opt.value;
            if (infoBadge)   infoBadge.textContent   = bunga + '% / bulan';
            if (infoRpRange) infoRpRange.textContent = `${fmtShort(minPinjaman)} – ${fmtShort(maksPinjaman)}`;
            if (infoJkRange) infoJkRange.textContent = `${minJangka} – ${maksJangka} bulan`;
        }

        hitungCicilan();
    }

    selJenis.addEventListener('change', function () {
        terapkanJenis(this.options[this.selectedIndex]);
    });

    if (selJenis.value) terapkanJenis(selJenis.options[selJenis.selectedIndex]);
}

// Kalkulator cicilan
function hitungCicilan() {
    const jumlah = parseFloat(document.getElementById('jumlahPinjaman').value) || 0;
    const jangka = parseInt(document.getElementById('jangkaWaktu').value)      || 0;
    const bunga  = parseFloat(document.getElementById('bungaPinjaman').value)  || 0;

    if (jumlah > 0 && jangka > 0) {
        const bungaBulanan = jumlah * (bunga / 100);
        const cicilan      = (jumlah / jangka) + bungaBulanan;
        const totalBayar   = cicilan * jangka;
        const totalBunga   = bungaBulanan * jangka;

        document.getElementById('cicilanPerBulan').textContent = fmt(cicilan);
        document.getElementById('totalBayar').textContent      = fmt(totalBayar);
        document.getElementById('totalBunga').textContent      = fmt(totalBunga);
        document.getElementById('kalkulator').style.display    = '';
    }
}

['jumlahPinjaman', 'jangkaWaktu', 'bungaPinjaman'].forEach(id => {
    const el = document.getElementById(id);
    if (el) {
        el.addEventListener('input', hitungCicilan);
        el.addEventListener('change', hitungCicilan);
    }
});

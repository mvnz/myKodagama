// Sidebar Toggle Mobile
const sidebarToggle = document.getElementById('sidebarToggle');
const sidebar = document.getElementById('sidebar');
if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener('click', () => sidebar.classList.toggle('open'));
}
document.addEventListener('click', (e) => {
    if (window.innerWidth < 992 && sidebar && !sidebar.contains(e.target) && !sidebarToggle?.contains(e.target)) {
        sidebar.classList.remove('open');
    }
});

// Auto hide alerts
setTimeout(() => {
    document.querySelectorAll('.alert-sukses, .alert-galat').forEach(el => {
        el.style.transition = 'opacity .5s';
        el.style.opacity = '0';
        setTimeout(() => el.remove(), 500);
    });
}, 4000);

// Chevron rotate on data master collapse
const collapseDataMaster = document.getElementById('submenuDataMaster');
const chevronDataMaster  = document.getElementById('chevronDataMaster');
if (collapseDataMaster && chevronDataMaster) {
    collapseDataMaster.addEventListener('show.bs.collapse', () => chevronDataMaster.style.transform = 'rotate(180deg)');
    collapseDataMaster.addEventListener('hide.bs.collapse', () => chevronDataMaster.style.transform = 'rotate(0deg)');
    if (collapseDataMaster.classList.contains('show')) chevronDataMaster.style.transform = 'rotate(180deg)';
}

// Chevron rotate on transaksi collapse
const collapseTransaksi = document.getElementById('submenuTransaksi');
const chevronTransaksi  = document.getElementById('chevronTransaksi');
if (collapseTransaksi && chevronTransaksi) {
    collapseTransaksi.addEventListener('show.bs.collapse', () => chevronTransaksi.style.transform = 'rotate(180deg)');
    collapseTransaksi.addEventListener('hide.bs.collapse', () => chevronTransaksi.style.transform = 'rotate(0deg)');
    if (collapseTransaksi.classList.contains('show')) chevronTransaksi.style.transform = 'rotate(180deg)';
}

// Chevron rotate on pengaturan collapse
const collapseEl = document.getElementById('submenuPengaturan');
const chevron    = document.getElementById('chevronPengaturan');
if (collapseEl && chevron) {
    collapseEl.addEventListener('show.bs.collapse',  () => chevron.style.transform = 'rotate(180deg)');
    collapseEl.addEventListener('hide.bs.collapse',  () => chevron.style.transform = 'rotate(0deg)');
    if (collapseEl.classList.contains('show')) chevron.style.transform = 'rotate(180deg)';
}

// Mobile nav toggle
const toggle = document.getElementById('mobile-toggle');
const mobileNav = document.getElementById('mobile-nav');
if (toggle && mobileNav) {
    toggle.addEventListener('click', () => {
        mobileNav.style.display = mobileNav.style.display === 'none' ? 'block' : 'none';
    });
}

// Scroll top
const scrollTopBtn = document.getElementById('scrollTop');
window.addEventListener('scroll', () => {
    if (scrollTopBtn) {
        scrollTopBtn.classList.toggle('active', window.scrollY > 300);
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

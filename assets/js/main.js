// Form Validation and submission handling
document.querySelectorAll('form[data-validate]').forEach(f => {
    f.addEventListener('submit', e => {
        const r = f.querySelector('input[type=file]');
        if (r && r.files[0] && r.files[0].size > 2 * 1024 * 1024) {
            e.preventDefault();
            alert('Resume must be under 2 MB.');
            return;
        }
        const b = f.querySelector('button[type=submit]');
        if (b) setTimeout(() => b.disabled = true, 10);
    });
});

// Auto-dismiss Alerts after 7 seconds
document.querySelectorAll('.alert').forEach(a => setTimeout(() => {
    a.style.transition = 'opacity 0.4s ease';
    a.style.opacity = '0';
    setTimeout(() => a.style.display = 'none', 400);
}, 7000));

// Mobile Navigation Controller
const burgerBtn = document.getElementById('burgerBtn');
const navMenu = document.getElementById('navMenu');
const navBackdrop = document.getElementById('navBackdrop');

function toggleMobileMenu(open) {
    if (!navMenu) return;
    const shouldOpen = open !== undefined ? open : !navMenu.classList.contains('open');
    navMenu.classList.toggle('open', shouldOpen);
    if (navBackdrop) navBackdrop.classList.toggle('open', shouldOpen);
    if (burgerBtn) burgerBtn.innerHTML = shouldOpen ? '&#10005;' : '&#9776;';
    document.body.style.overflow = shouldOpen ? 'hidden' : '';
}

if (burgerBtn) {
    burgerBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        toggleMobileMenu();
    });
}
if (navBackdrop) {
    navBackdrop.addEventListener('click', () => toggleMobileMenu(false));
}
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') toggleMobileMenu(false);
});

// Testimonial Auto-Slider
let slideIndex = 0;
let slideTimer;

function showSlides(n) {
    let slides = document.querySelectorAll(".test-slide");
    let dots = document.querySelectorAll(".dot");
    
    if (slides.length === 0) return;
    
    if (n !== undefined) {
        slideIndex = n;
    } else {
        slideIndex++;
    }
    
    if (slideIndex >= slides.length) { slideIndex = 0; }
    if (slideIndex < 0) { slideIndex = slides.length - 1; }
    
    slides.forEach(slide => {
        slide.classList.remove("active");
        slide.style.display = "none";
    });
    dots.forEach(dot => dot.classList.remove("active"));
    
    slides[slideIndex].classList.add("active");
    slides[slideIndex].style.display = "block";
    if (dots[slideIndex]) dots[slideIndex].classList.add("active");
}

function currentSlide(n) {
    clearInterval(slideTimer);
    showSlides(n);
    startSlider();
}

function startSlider() {
    let slides = document.querySelectorAll(".test-slide");
    if (slides.length > 1) {
        slideTimer = setInterval(() => showSlides(), 5000);
    }
}

window.currentSlide = currentSlide;

document.addEventListener("DOMContentLoaded", () => {
    let slides = document.querySelectorAll(".test-slide");
    if (slides.length > 0) {
        slides.forEach((slide, idx) => {
            if (idx !== 0) slide.style.display = "none";
        });
        startSlider();
    }
});

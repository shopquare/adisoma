document.querySelectorAll('form[data-validate]').forEach(f=>f.addEventListener('submit',e=>{
 const r=f.querySelector('input[type=file]');
 if(r&&r.files[0]&&r.files[0].size>2*1024*1024){e.preventDefault();alert('Resume must be under 2 MB.');return;}
 const b=f.querySelector('button[type=submit]');if(b)setTimeout(()=>b.disabled=true,10);}));
document.querySelectorAll('.alert').forEach(a=>setTimeout(()=>a.style.display='none',8000));

/* =========================================
   Testimonial Auto-Slider
   ========================================= */
let slideIndex = 0;
let slideTimer;

function showSlides(n) {
    let slides = document.querySelectorAll(".test-slide");
    let dots = document.querySelectorAll(".dot");
    
    if (slides.length === 0) return; // Exit if not on homepage
    
    // Handle manual clicks
    if (n !== undefined) {
        slideIndex = n;
    } else {
        slideIndex++;
    }
    
    // Reset index if out of bounds
    if (slideIndex >= slides.length) { slideIndex = 0; }
    if (slideIndex < 0) { slideIndex = slides.length - 1; }
    
    // Hide all slides and remove active class from dots
    slides.forEach(slide => {
        slide.classList.remove("active");
        slide.style.display = "none";
    });
    dots.forEach(dot => dot.classList.remove("active"));
    
    // Show current slide
    slides[slideIndex].classList.add("active");
    slides[slideIndex].style.display = "block";
    dots[slideIndex].classList.add("active");
}

function currentSlide(n) {
    clearInterval(slideTimer); // Stop auto-play when clicked manually
    showSlides(n);
    startSlider(); // Restart auto-play
}

function startSlider() {
    let slides = document.querySelectorAll(".test-slide");
    if (slides.length > 0) {
        slideTimer = setInterval(() => showSlides(), 5000); // Change slide every 5 seconds
    }
}

// Make sure functions are available globally so the inline onclick handlers work
window.currentSlide = currentSlide;

// Initialize slider when page loads
document.addEventListener("DOMContentLoaded", () => {
    // Hide all slides except the first one initially
    let slides = document.querySelectorAll(".test-slide");
    if (slides.length > 0) {
        slides.forEach((slide, idx) => {
            if (idx !== 0) slide.style.display = "none";
        });
        startSlider();
    }
});

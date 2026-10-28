const slides = document.querySelectorAll(".slide");
let currentSlide = 0;

function showNextSlide() {
    if (slides.length === 0) return;

    slides[currentSlide].classList.remove("active");

    currentSlide = (currentSlide + 1) % slides.length;

    slides[currentSlide].classList.add("active");
}

if (slides.length > 1) {
    setInterval(showNextSlide, 4000);
}

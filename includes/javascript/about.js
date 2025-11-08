tailwind.config = {
    theme: {
        extend: {
            colors: {
                primary: '#0056b3',
                secondary: '#003d7a',
                accent: '#ff6b00',
                dark: '#1a1a1a',
                light: '#f8f9fa'
            },
            fontFamily: {
                sans: ['"Open Sans"', 'sans-serif'],
                heading: ['"Montserrat"', 'sans-serif'],
                alice: ['"Alice"', 'serif'],
                tinos: ['"Tinos"', 'serif']
            },
            animation: {
                'fade-in': 'fadeIn 1s ease-in-out',
                'float': 'float 3s ease-in-out infinite'
            },
            keyframes: {
                fadeIn: {
                    '0%': {
                        opacity: '0'
                    },
                    '100%': {
                        opacity: '1'
                    }
                },
                float: {
                    '0%, 100%': {
                        transform: 'translateY(0)'
                    },
                    '50%': {
                        transform: 'translateY(-10px)'
                    }
                }
            }
        }
    }
}

AOS.init({
    duration: 800,
    easing: 'ease-in-out',
    once: true
});

// Initialize Bootstrap tooltips
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl)
});

document.addEventListener("DOMContentLoaded", function() {
    const btn = document.getElementById("playPauseBtn");
    const audio = document.getElementById("customAudio");
    const playIcon = document.getElementById("playIcon");
    const pauseIcon = document.getElementById("pauseIcon");
    const buttonText = document.getElementById("buttonText");

    // FIX: Only run if all elements exist
    if (btn && audio && playIcon && pauseIcon && buttonText) {
        btn.addEventListener("click", function() {
            if (audio.paused) {
                audio.play();
                playIcon.classList.add("hidden");
                pauseIcon.classList.remove("hidden");
                buttonText.textContent = "Pause Audio";
            } else {
                audio.pause();
                playIcon.classList.remove("hidden");
                pauseIcon.classList.add("hidden");
                buttonText.textContent = "Play Audio";
            }
        });

        audio.addEventListener("ended", function() {
            playIcon.classList.remove("hidden");
            pauseIcon.classList.add("hidden");
            buttonText.textContent = "Play Audio";
        });
    }
});

// 1. Exit modal with Escape key
document.addEventListener('keydown', function(e) {
    const aboutModal = document.getElementById('aboutModal');
    if (e.key === 'Escape' && aboutModal && aboutModal.style.display === 'block') {
        aboutModal.style.display = 'none';
    }
});

// 2. Open about modal by clicking the whole about card (if such a structure exists)
document.querySelectorAll('.about-card').forEach(card => {
    card.addEventListener('click', function(e) {
        if (e.target.closest('button') || e.target.tagName === 'BUTTON' || e.target.tagName === 'A') return;
        const viewBtn = card.querySelector('.view-details');
        if (viewBtn) viewBtn.click();
    });
});

function toggleImage(el) {
    const imageWrapper = el.querySelector('.timeline-image');
    if (!imageWrapper) return;

    // Toggle visibility with Tailwind classes
    const isVisible = imageWrapper.classList.contains('opacity-100');

    if (isVisible) {
        imageWrapper.classList.remove('opacity-100', 'max-h-96');
        imageWrapper.classList.add('opacity-0', 'max-h-0');
    } else {
        imageWrapper.classList.remove('opacity-0', 'max-h-0');
        imageWrapper.classList.add('opacity-100', 'max-h-96');
    }
}
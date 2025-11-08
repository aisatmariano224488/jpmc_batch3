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

            if (btn && audio) {
                btn.addEventListener("click", function() {
                    if (audio.paused) {
                        audio.play();
                        playIcon.classList.add("hidden");
                        pauseIcon.classList.remove("hidden");
                        if (buttonText) buttonText.textContent = "Pause Audio";
                    } else {
                        audio.pause();
                        playIcon.classList.remove("hidden");
                        pauseIcon.classList.add("hidden");
                        if (buttonText) buttonText.textContent = "Play Audio";
                    }
                });

                audio.addEventListener("ended", function() {
                    playIcon.classList.remove("hidden");
                    pauseIcon.classList.add("hidden");
                    if (buttonText) buttonText.textContent = "Play Audio";
                });
            }

            // Smooth scroll for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });

            // Enhanced scroll behavior for snap sections
            let isScrolling = false;
            const sections = document.querySelectorAll('.snap-section');

            function scrollToSection(index) {
                if (index >= 0 && index < sections.length) {
                    sections[index].scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }

            // Keyboard navigation
            document.addEventListener('keydown', function(e) {
                if (isScrolling) return;

                const currentSection = getCurrentSection();

                if (e.key === 'ArrowDown' || e.key === 'PageDown') {
                    e.preventDefault();
                    scrollToSection(currentSection + 1);
                } else if (e.key === 'ArrowUp' || e.key === 'PageUp') {
                    e.preventDefault();
                    scrollToSection(currentSection - 1);
                }
            });

            function getCurrentSection() {
                const scrollPosition = window.scrollY + window.innerHeight / 2;
                for (let i = 0; i < sections.length; i++) {
                    const sectionTop = sections[i].offsetTop;
                    const sectionBottom = sectionTop + sections[i].offsetHeight;
                    if (scrollPosition >= sectionTop && scrollPosition < sectionBottom) {
                        return i;
                    }
                }
                return 0;
            }

            // Touch/swipe navigation for mobile
            let startY = 0;
            let endY = 0;

            document.addEventListener('touchstart', function(e) {
                startY = e.touches[0].clientY;
            });

            document.addEventListener('touchend', function(e) {
                endY = e.changedTouches[0].clientY;
                const diff = startY - endY;
                const currentSection = getCurrentSection();

                if (Math.abs(diff) > 50) { // Minimum swipe distance
                    if (diff > 0) {
                        // Swipe up - next section
                        scrollToSection(currentSection + 1);
                    } else {
                        // Swipe down - previous section
                        scrollToSection(currentSection - 1);
                    }
                }
            });

            // Individual Background Sections with Percentage-Based Transitions
            function initBackgroundSections() {
                const backgrounds = document.querySelectorAll('.background-section');

                if (!backgrounds.length) {
                    console.log('Background sections not found');
                    return;
                }

                function updateBackgrounds() {
                    const scrolled = window.pageYOffset;
                    const windowHeight = window.innerHeight;
                    const documentHeight = document.documentElement.scrollHeight;
                    const maxScroll = documentHeight - windowHeight;

                    // Calculate scroll percentage (0 to 1)
                    const scrollPercentage = Math.min(scrolled / maxScroll, 1);

                    // Determine which background should be active based on scroll percentage
                    // 0-25% = Background 1, 25-50% = Background 2, 50-75% = Background 3, 75-100% = Background 4
                    let currentBackgroundIndex = 1;
                    if (scrollPercentage >= 0.75) {
                        currentBackgroundIndex = 4;
                    } else if (scrollPercentage >= 0.5) {
                        currentBackgroundIndex = 3;
                    } else if (scrollPercentage >= 0.25) {
                        currentBackgroundIndex = 2;
                    } else {
                        currentBackgroundIndex = 1;
                    }

                    // Update background visibility
                    backgrounds.forEach((bg) => {
                        const bgSection = parseInt(bg.getAttribute('data-section'));
                        if (bgSection === currentBackgroundIndex) {
                            bg.classList.add('active');
                        } else {
                            bg.classList.remove('active');
                        }

                        // Apply parallax effect
                        const rate = scrolled * -0.2; // Reduced parallax speed for smoother effect
                        bg.style.transform = `translateY(${rate}px)`;
                    });

                    // Debug info
                    console.log(`Scroll: ${(scrollPercentage * 100).toFixed(1)}% | Active background: ${currentBackgroundIndex}`);
                }

                window.addEventListener('scroll', updateBackgrounds);
                updateBackgrounds(); // Initial call
                console.log('Percentage-based background transitions initialized');
            }

            // Original Full Page Parallax Effect (Disabled)
            function initFullPageParallax() {
                // This function is now disabled in favor of multiple backgrounds
                return;
            }

            // CTA Section Parallax Effect (Disabled)
            function initCTAParallax() {
                // This function is now disabled - CTA section uses static gradient background
                return;
            }

            // Initialize individual background sections when DOM is loaded
            initBackgroundSections();
            // initFullPageParallax(); // Disabled
            initCTAParallax();
        });
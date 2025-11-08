tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#0066cc',
                        secondary: '#004d99',
                        dark: '#222222',
                        light: '#f5f5f5'
                    },
                    screens: {
                        'xl': '1280px',
                    }
                }
            }
        }

AOS.init({
            duration: 800,
            once: true,
        });

class WorldClassCarousel {
            constructor() {
                this.slides = document.querySelectorAll('.carousel-slide');
                this.totalSlides = this.slides.length;
                this.currentSlide = 0;
                this.track = document.getElementById('carouselTrack');
                this.dots = document.querySelectorAll('.carousel-dot');
                this.touchStartX = 0;
                this.touchEndX = 0;
                this.isAnimating = false;
                this.autoPlayInterval = null;

                // Only initialize if there are slides
                if (this.totalSlides > 0) {
                    this.init();
                }
            }

            init() {
                this.updateCounter();
                this.setupEventListeners();
                this.showTouchIndicator();
                this.preloadImages();
                if (this.totalSlides > 1) {
                    this.startAutoPlay();
                }
            }

            setupEventListeners() {
                if (!this.track) return;

                // Touch events for mobile
                this.track.addEventListener('touchstart', (e) => {
                    this.touchStartX = e.changedTouches[0].screenX;
                });
                this.track.addEventListener('touchend', (e) => {
                    this.touchEndX = e.changedTouches[0].screenX;
                    this.handleSwipe();
                });

                // Keyboard navigation
                document.addEventListener('keydown', (e) => {
                    if (e.key === 'ArrowLeft') {
                        this.prevSlide();
                    } else if (e.key === 'ArrowRight') {
                        this.nextSlide();
                    } else if (e.key === 'Escape') {
                        closeFullscreen();
                    }
                });
            }

            handleSwipe() {
                if (this.totalSlides < 2) return;
                const swipeThreshold = 50;
                const diff = this.touchStartX - this.touchEndX;
                if (Math.abs(diff) > swipeThreshold) {
                    if (diff > 0) {
                        this.nextSlide();
                    } else {
                        this.prevSlide();
                    }
                }
            }

            goToSlide(index) {
                if (this.isAnimating || index === this.currentSlide || this.totalSlides < 1) return;
                if (index < 0 || index >= this.totalSlides) return;

                this.isAnimating = true;
                this.currentSlide = index;

                if (this.track) {
                    this.track.style.transform = `translateX(-${index * 100}%)`;
                }

                this.updateActiveStates();
                this.updateCounter();
                this.updateMediaInfo();

                if (this.totalSlides > 1) {
                    this.resetAutoPlay();
                }

                setTimeout(() => {
                    this.isAnimating = false;
                }, 600);
            }

            nextSlide() {
                if (this.totalSlides < 2) return;
                const nextIndex = (this.currentSlide + 1) % this.totalSlides;
                this.goToSlide(nextIndex);
            }

            prevSlide() {
                if (this.totalSlides < 2) return;
                const prevIndex = this.currentSlide === 0 ? this.totalSlides - 1 : this.currentSlide - 1;
                this.goToSlide(prevIndex);
            }

            updateActiveStates() {
                this.slides.forEach((slide, index) => {
                    slide.classList.toggle('active', index === this.currentSlide);
                });

                this.dots.forEach((dot, index) => {
                    dot.classList.toggle('active', index === this.currentSlide);
                    if (index === this.currentSlide) {
                        dot.style.background = '#0066cc';
                        dot.style.transform = 'scale(1.1)';
                        dot.style.boxShadow = '0 0 8px rgba(0, 102, 204, 0.4)';
                    } else {
                        dot.style.background = '#dee2e6';
                        dot.style.transform = 'scale(1)';
                        dot.style.boxShadow = 'none';
                    }
                });
            }

            updateCounter() {
                const currentSlideElement = document.getElementById('currentSlide');
                const totalSlidesElement = document.getElementById('totalSlides');

                if (currentSlideElement) {
                    currentSlideElement.textContent = this.currentSlide + 1;
                }
                if (totalSlidesElement) {
                    totalSlidesElement.textContent = this.totalSlides;
                }
            }

            updateMediaInfo() {
                const mediaInfo = document.getElementById('mediaInfo');
                const currentSlide = this.slides[this.currentSlide];

                if (currentSlide && mediaInfo) {
                    try {
                        const mediaData = JSON.parse(currentSlide.getAttribute('data-media') || '{}');
                        let description = '';

                        if (mediaData.type === 'image') {
                            description = mediaData.alt_text || mediaData.title || '';
                        } else if (mediaData.type === 'video') {
                            if (mediaData.title) {
                                description = mediaData.title;
                                if (mediaData.description) {
                                    description += ` - ${mediaData.description}`;
                                }
                            } else if (mediaData.description) {
                                description = mediaData.description;
                            }
                        }

                        if (description.trim()) {
                            mediaInfo.style.opacity = '0';
                            setTimeout(() => {
                                mediaInfo.textContent = description;
                                mediaInfo.style.opacity = '1';
                            }, 150);
                        } else {
                            mediaInfo.style.opacity = '0';
                            setTimeout(() => {
                                mediaInfo.textContent = '';
                            }, 150);
                        }
                    } catch (error) {
                        console.error('Error parsing media data:', error);
                    }
                }
            }

            showTouchIndicator() {
                if (this.totalSlides < 2) return;
                const indicator = document.getElementById('touchIndicator');
                if (indicator && 'ontouchstart' in window) {
                    indicator.classList.add('show');
                    setTimeout(() => {
                        indicator.classList.remove('show');
                    }, 3000);
                }
            }

            preloadImages() {
                this.slides.forEach(slide => {
                    const img = slide.querySelector('img');
                    if (img) {
                        img.addEventListener('load', () => {
                            slide.style.opacity = '1';
                        });
                        // If image is already loaded
                        if (img.complete) {
                            slide.style.opacity = '1';
                        }
                    }
                });
            }

            startAutoPlay() {
                if (this.totalSlides < 2) return;
                this.autoPlayInterval = setInterval(() => {
                    const currentSlide = this.slides[this.currentSlide];
                    const video = currentSlide.querySelector('video');
                    const iframe = currentSlide.querySelector('iframe');

                    if (!video || video.paused) {
                        if (!iframe) {
                            this.nextSlide();
                        }
                    }
                }, 5000);
            }

            resetAutoPlay() {
                if (this.autoPlayInterval) {
                    clearInterval(this.autoPlayInterval);
                    this.startAutoPlay();
                }
            }
        }

        // Initialize carousel when DOM is loaded
        document.addEventListener('DOMContentLoaded', () => {
            const carouselContainer = document.querySelector('.carousel-container');
            const carouselTrack = document.getElementById('carouselTrack');
            const slides = document.querySelectorAll('.carousel-slide');

            console.log('Carousel debug info:', {
                container: !!carouselContainer,
                track: !!carouselTrack,
                slidesCount: slides.length,
                slides: Array.from(slides).map(slide => ({
                    index: slide.getAttribute('data-index'),
                    media: slide.getAttribute('data-media')
                }))
            });

            if (carouselContainer && slides.length > 0) {
                try {
                    window.carousel = new WorldClassCarousel();
                    console.log('Carousel initialized successfully with', window.carousel.totalSlides, 'slides');
                } catch (error) {
                    console.error('Error initializing carousel:', error);
                }
            } else {
                console.log('Carousel not initialized - missing container or slides');
            }

            // Initialize section carousels
            const contentSections = document.querySelectorAll('.mb-8');
            let sectionIndex = 0;
            contentSections.forEach((section, index) => {
                const sectionCarousel = section.querySelector('.carousel-container');
                if (sectionCarousel) {
                    initSectionCarousel(sectionIndex);
                    console.log(`Section carousel ${sectionIndex} initialized`);
                    sectionIndex++;
                }
            });
        });

        // Global functions for navigation
        function goToSlide(index) {
            if (window.carousel) {
                window.carousel.goToSlide(index);
            }
        }

        function nextSlide() {
            if (window.carousel) {
                window.carousel.nextSlide();
            }
        }

        function prevSlide() {
            if (window.carousel) {
                window.carousel.prevSlide();
            }
        }

        // Content Section Carousel Functions
        const sectionCarousels = {};

        function initSectionCarousel(sectionIndex) {
            const track = document.getElementById(`sectionCarouselTrack${sectionIndex}`);
            const slides = track ? track.querySelectorAll('.carousel-slide') : [];
            const dots = document.querySelectorAll(`#sectionCarouselDots${sectionIndex} .carousel-dot`);

            if (slides.length === 0) return;

            sectionCarousels[sectionIndex] = {
                slides: slides,
                totalSlides: slides.length,
                currentSlide: 0,
                track: track,
                dots: dots,
                isAnimating: false
            };

            updateSectionCounter(sectionIndex);
            setupSectionEventListeners(sectionIndex);
        }

        function setupSectionEventListeners(sectionIndex) {
            const carousel = sectionCarousels[sectionIndex];
            if (!carousel || !carousel.track) return;

            // Touch events for mobile
            carousel.track.addEventListener('touchstart', (e) => {
                carousel.touchStartX = e.changedTouches[0].screenX;
            });
            carousel.track.addEventListener('touchend', (e) => {
                carousel.touchEndX = e.changedTouches[0].screenX;
                handleSectionSwipe(sectionIndex);
            });
        }

        function handleSectionSwipe(sectionIndex) {
            const carousel = sectionCarousels[sectionIndex];
            if (!carousel || carousel.totalSlides < 2) return;

            const swipeThreshold = 50;
            const diff = carousel.touchStartX - carousel.touchEndX;
            if (Math.abs(diff) > swipeThreshold) {
                if (diff > 0) {
                    sectionNextSlide(sectionIndex);
                } else {
                    sectionPrevSlide(sectionIndex);
                }
            }
        }

        function sectionGoToSlide(sectionIndex, index) {
            const carousel = sectionCarousels[sectionIndex];
            if (!carousel || carousel.isAnimating || index === carousel.currentSlide || carousel.totalSlides < 1) return;
            if (index < 0 || index >= carousel.totalSlides) return;

            carousel.isAnimating = true;
            carousel.currentSlide = index;

            if (carousel.track) {
                carousel.track.style.transform = `translateX(-${index * 100}%)`;
            }

            updateSectionActiveStates(sectionIndex);
            updateSectionCounter(sectionIndex);

            setTimeout(() => {
                carousel.isAnimating = false;
            }, 600);
        }

        function sectionNextSlide(sectionIndex) {
            const carousel = sectionCarousels[sectionIndex];
            if (!carousel || carousel.totalSlides < 2) return;
            const nextIndex = (carousel.currentSlide + 1) % carousel.totalSlides;
            sectionGoToSlide(sectionIndex, nextIndex);
        }

        function sectionPrevSlide(sectionIndex) {
            const carousel = sectionCarousels[sectionIndex];
            if (!carousel || carousel.totalSlides < 2) return;
            const prevIndex = carousel.currentSlide === 0 ? carousel.totalSlides - 1 : carousel.currentSlide - 1;
            sectionGoToSlide(sectionIndex, prevIndex);
        }

        function updateSectionActiveStates(sectionIndex) {
            const carousel = sectionCarousels[sectionIndex];
            if (!carousel) return;

            carousel.slides.forEach((slide, index) => {
                slide.classList.toggle('active', index === carousel.currentSlide);
            });

            carousel.dots.forEach((dot, index) => {
                dot.classList.toggle('active', index === carousel.currentSlide);
                if (index === carousel.currentSlide) {
                    dot.style.background = '#0066cc';
                    dot.style.transform = 'scale(1.1)';
                    dot.style.boxShadow = '0 0 8px rgba(0, 102, 204, 0.4)';
                } else {
                    dot.style.background = '#dee2e6';
                    dot.style.transform = 'scale(1)';
                    dot.style.boxShadow = 'none';
                }
            });
        }

        function updateSectionCounter(sectionIndex) {
            const carousel = sectionCarousels[sectionIndex];
            if (!carousel) return;

            const currentSlideElement = document.getElementById(`sectionCurrentSlide${sectionIndex}`);
            const totalSlidesElement = document.getElementById(`sectionTotalSlides${sectionIndex}`);

            if (currentSlideElement) {
                currentSlideElement.textContent = carousel.currentSlide + 1;
            }
            if (totalSlidesElement) {
                totalSlidesElement.textContent = carousel.totalSlides;
            }
        }

        // Fullscreen functionality
        function openFullscreen(src, type) {
            const modal = document.getElementById('fullscreenModal');
            const content = document.getElementById('fullscreenContent');

            if (type === 'image') {
                content.innerHTML = `<img src="${src}" alt="Fullscreen view">`;
            } else if (type === 'video') {
                content.innerHTML = `<video controls autoplay><source src="${src}" type="video/mp4"></video>`;
            }

            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        function closeFullscreen() {
            const modal = document.getElementById('fullscreenModal');
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';

            // Stop any playing videos
            const video = modal.querySelector('video');
            if (video) {
                video.pause();
            }
        }

        // Close fullscreen when clicking outside
        document.getElementById('fullscreenModal').addEventListener('click', (e) => {
            if (e.target.id === 'fullscreenModal') {
                closeFullscreen();
            }
        });

        // Smooth scroll to top when opening fullscreen
        function smoothScrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

// 1. Global Escape key for closing modal (if not already present)
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeFullscreen();
    }
});

// 2. Open modal when clicking the whole carousel container
document.addEventListener('DOMContentLoaded', function() {
    // Main carousel
    const mainCarouselContainer = document.querySelector('.carousel-container');
    if (mainCarouselContainer) {
        mainCarouselContainer.addEventListener('click', function(e) {
            // Prevent if clicking on navigation, dots, or controls
            if (
                e.target.closest('.carousel-nav') ||
                e.target.closest('.carousel-dot') ||
                e.target.closest('button') ||
                e.target.closest('a')
            ) return;

            // Find the active slide
            const activeSlide = mainCarouselContainer.querySelector('.carousel-slide.active');
            if (activeSlide) {
                const mediaData = JSON.parse(activeSlide.getAttribute('data-media') || '{}');
                if (mediaData.type === 'image') {
                    const img = activeSlide.querySelector('img');
                    if (img) openFullscreen(img.src, 'image');
                } else if (mediaData.type === 'video') {
                    if (mediaData.video_type === 'local') {
                        const videoSource = activeSlide.querySelector('video source');
                        if (videoSource) openFullscreen(videoSource.src, 'video');
                    } else {
                        const iframe = activeSlide.querySelector('iframe');
                        if (iframe) openFullscreen(iframe.src, 'video');
                    }
                }
            }
        });
    }

    // Section carousels
    document.querySelectorAll('.carousel-container[id^="sectionCarousel"]').forEach(sectionContainer => {
        sectionContainer.addEventListener('click', function(e) {
            if (
                e.target.closest('.carousel-nav') ||
                e.target.closest('.carousel-dot') ||
                e.target.closest('button') ||
                e.target.closest('a')
            ) return;

            const activeSlide = sectionContainer.querySelector('.carousel-slide.active');
            if (activeSlide) {
                const mediaData = JSON.parse(activeSlide.getAttribute('data-media') || '{}');
                if (mediaData.type === 'image') {
                    const img = activeSlide.querySelector('img');
                    if (img) openFullscreen(img.src, 'image');
                } else if (mediaData.type === 'video') {
                    if (mediaData.video_type === 'local') {
                        const videoSource = activeSlide.querySelector('video source');
                        if (videoSource) openFullscreen(videoSource.src, 'video');
                    } else {
                        const iframe = activeSlide.querySelector('iframe');
                        if (iframe) openFullscreen(iframe.src, 'video');
                    }
                }
            }
        });
    });
});
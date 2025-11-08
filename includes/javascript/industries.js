tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#0066cc',
                        secondary: '#004d99',
                        dark: '#222222',
                        light: '#f5f5f5'
                    }
                }
            }
        }

document.addEventListener('DOMContentLoaded', function() {
            const viewDetailsButtons = document.querySelectorAll('.view-details');
            const industryModal = document.getElementById('industryModal');
            const closeModal = document.getElementById('closeModal');

            viewDetailsButtons.forEach(button => {
                button.addEventListener('click', async function(e) {
                    e.preventDefault();
                    const industryId = this.getAttribute('data-industry-id');

                    // Show loading state
                    document.getElementById('modalIndustryTitle').textContent = 'Loading...';
                    document.getElementById('modalIndustryDescription').textContent = '';
                    document.getElementById('modalIndustrySolutions').innerHTML = '';
                    industryModal.style.display = 'block';

                    try {
                        // Fetch all solutions via AJAX
                        const response = await fetch(`get_industry_solutions.php?industry_id=${industryId}`);
                        const data = await response.json();

                        if (data.error) {
                            throw new Error(data.error);
                        }

                        // Populate modal with all solutions
                        document.getElementById('modalIndustryTitle').textContent = data.industry.name;
                        document.getElementById('modalIndustryDescription').textContent = data.industry.description;
                        document.getElementById('modalIndustryImage').style.backgroundImage = `url('${data.industry.image_url}')`;

                        const solutionsList = document.getElementById('modalIndustrySolutions');
                        solutionsList.innerHTML = '';

                        data.solutions.forEach(solution => {
                            const li = document.createElement('li');
                            li.className = 'flex items-start';
                            li.innerHTML = `
                            <i class="fas fa-check-circle text-primary mt-1 mr-3"></i>
                            <span>${solution}</span>
                        `;
                            solutionsList.appendChild(li);
                        });

                    } catch (error) {
                        console.error('Error fetching industry solutions:', error);
                        document.getElementById('modalIndustryTitle').textContent = 'Error';
                        document.getElementById('modalIndustryDescription').textContent = 'Could not load industry solutions. Please try again.';
                    }
                });
            });

            // Close modal
            closeModal.addEventListener('click', function() {
                industryModal.style.display = 'none';
            });

            // Close modal when clicking outside
            window.addEventListener('click', function(e) {
                if (e.target === industryModal) {
                    industryModal.style.display = 'none';
                }
            });
        });

        // 1. Exit modal with Escape key
        document.addEventListener('keydown', function(e) {
            const industryModal = document.getElementById('industryModal');
            if (e.key === 'Escape' && industryModal && industryModal.style.display === 'block') {
                industryModal.style.display = 'none';
            }
        });

        // 2. Open industry modal by clicking the whole industry card
        document.querySelectorAll('.industry-card').forEach(card => {
            card.addEventListener('click', function(e) {
                if (e.target.closest('.view-details') || e.target.tagName === 'BUTTON' || e.target.tagName === 'A') return;
                const viewBtn = card.querySelector('.view-details');
                if (viewBtn) viewBtn.click();
            });
        });

        // Industries Carousel Implementation
        class IndustriesCarousel {
            constructor() {
                this.slides = document.querySelectorAll('.industries-carousel-slide');
                this.totalSlides = this.slides.length;
                this.currentSlide = 0;
                this.track = document.getElementById('industriesCarouselTrack');
                this.dots = document.querySelectorAll('.industries-carousel-dot');
                this.isAnimating = false;
                this.autoPlayInterval = null;

                // Drag state variables
                this.isDragging = false;
                this.startX = 0;
                this.currentX = 0;
                this.translateX = 0;
                this.dragStartTime = 0;
                this.lastDragTime = 0;
                this.velocity = 0;
                this.momentumInterval = null;
                this.isFreeScrolling = true; // Enable free scrolling by default
                this.scrollBounds = {
                    min: 0,
                    max: 0
                };

                // Only initialize if there are slides
                if (this.totalSlides > 0) {
                    this.init();
                }
            }

            init() {
                this.updateCounter();
                this.setupEventListeners();
                this.createDragIndicator();
                this.calculateScrollBounds();
                if (this.totalSlides > 1) {
                    this.startAutoPlay();
                }
            }

            calculateScrollBounds() {
                if (!this.track) return;

                const trackWidth = this.track.offsetWidth;
                const slideWidth = trackWidth / this.getVisibleSlides();
                const totalWidth = slideWidth * this.totalSlides;
                const containerWidth = trackWidth;

                this.scrollBounds.min = -(totalWidth - containerWidth);
                this.scrollBounds.max = 0;
            }

            createDragIndicator() {
                const indicator = document.createElement('div');
                indicator.className = 'industries-drag-indicator';
                indicator.innerHTML = '<i class="fas fa-hand-pointer mr-2"></i>Scroll freely';
                this.track.parentElement.appendChild(indicator);
                this.dragIndicator = indicator;
            }

            setupEventListeners() {
                if (!this.track) return;

                // Touch events for mobile
                this.track.addEventListener('touchstart', (e) => {
                    this.handleDragStart(e.changedTouches[0].clientX);
                });

                this.track.addEventListener('touchmove', (e) => {
                    e.preventDefault();
                    this.handleDragMove(e.changedTouches[0].clientX);
                });

                this.track.addEventListener('touchend', (e) => {
                    this.handleDragEnd();
                });

                // Mouse events for desktop
                this.track.addEventListener('mousedown', (e) => {
                    e.preventDefault();
                    this.handleDragStart(e.clientX);
                });

                document.addEventListener('mousemove', (e) => {
                    if (this.isDragging) {
                        this.handleDragMove(e.clientX);
                    }
                });

                document.addEventListener('mouseup', () => {
                    if (this.isDragging) {
                        this.handleDragEnd();
                    }
                });

                // Keyboard navigation
                document.addEventListener('keydown', (e) => {
                    if (e.key === 'ArrowLeft') {
                        e.preventDefault();
                        this.prevSlide();
                    } else if (e.key === 'ArrowRight') {
                        e.preventDefault();
                        this.nextSlide();
                    }
                });

                // Pause autoplay on hover
                this.track.addEventListener('mouseenter', () => {
                    this.stopAutoPlay();
                });

                this.track.addEventListener('mouseleave', () => {
                    if (this.totalSlides > 1) {
                        this.startAutoPlay();
                    }
                });

                // Handle window resize
                window.addEventListener('resize', () => {
                    this.calculateScrollBounds();
                });
            }

            handleDragStart(clientX) {
                if (this.isAnimating || this.totalSlides < 2) return;

                this.isDragging = true;
                this.startX = clientX;
                this.currentX = clientX;
                this.dragStartTime = Date.now();
                this.translateX = this.getCurrentTranslateX();

                this.track.classList.add('dragging');
                this.showDragIndicator();

                // Pause autoplay during drag
                this.stopAutoPlay();
            }

            handleDragMove(clientX) {
                if (!this.isDragging) return;

                const now = Date.now();
                const deltaTime = now - this.lastDragTime;
                const deltaX = clientX - this.currentX;

                // Calculate velocity (pixels per millisecond)
                if (deltaTime > 0) {
                    this.velocity = deltaX / deltaTime;
                }

                this.currentX = clientX;
                this.lastDragTime = now;

                const totalDeltaX = this.currentX - this.startX;
                let newTranslateX = this.translateX + totalDeltaX;

                // Apply scroll bounds for free scrolling
                if (this.isFreeScrolling) {
                    newTranslateX = Math.max(this.scrollBounds.min, Math.min(this.scrollBounds.max, newTranslateX));
                }

                this.track.style.transform = `translateX(${newTranslateX}px)`;
            }

            handleDragEnd() {
                if (!this.isDragging) return;

                this.isDragging = false;
                this.track.classList.remove('dragging');
                this.hideDragIndicator();

                // For free scrolling, just start momentum without snapping
                if (this.isFreeScrolling) {
                    this.startMomentumScroll();
                } else {
                    // Traditional carousel behavior with snapping
                    const deltaX = this.currentX - this.startX;
                    const dragDuration = Date.now() - this.dragStartTime;
                    const avgVelocity = Math.abs(deltaX) / dragDuration;

                    const swipeThreshold = 50;
                    const velocityThreshold = 0.5;

                    if (Math.abs(deltaX) > swipeThreshold || avgVelocity > velocityThreshold) {
                        if (deltaX > 0) {
                            this.prevSlide();
                        } else {
                            this.nextSlide();
                        }
                    } else {
                        this.snapToNearestSlide();
                    }
                }

                // Resume autoplay after momentum ends
                setTimeout(() => {
                    if (this.totalSlides > 1) {
                        this.startAutoPlay();
                    }
                }, 1000);
            }

            startMomentumScroll() {
                if (this.momentumInterval) {
                    clearInterval(this.momentumInterval);
                }

                const momentum = this.velocity * 100; // Amplify the velocity
                let currentVelocity = momentum;
                const friction = 0.95; // Decay factor

                // Add momentum class for smooth scrolling
                this.track.classList.add('momentum');

                this.momentumInterval = setInterval(() => {
                    if (Math.abs(currentVelocity) < 0.1) {
                        clearInterval(this.momentumInterval);
                        this.momentumInterval = null;
                        this.track.classList.remove('momentum');

                        // Only snap if not in free scroll mode
                        if (!this.isFreeScrolling) {
                            this.snapToNearestSlide();
                        }
                        return;
                    }

                    const currentTranslateX = this.getCurrentTranslateX();
                    let newTranslateX = currentTranslateX + currentVelocity;

                    // Apply scroll bounds for free scrolling
                    if (this.isFreeScrolling) {
                        newTranslateX = Math.max(this.scrollBounds.min, Math.min(this.scrollBounds.max, newTranslateX));
                    }

                    this.track.style.transform = `translateX(${newTranslateX}px)`;
                    currentVelocity *= friction;
                }, 16); // 60fps
            }

            snapToNearestSlide() {
                const currentTranslateX = this.getCurrentTranslateX();
                const slideWidth = this.track.offsetWidth / this.getVisibleSlides();
                const currentSlideIndex = Math.round(Math.abs(currentTranslateX) / slideWidth);

                // Ensure we stay within bounds with infinite loop
                let targetSlide = currentSlideIndex;
                if (targetSlide < 0) targetSlide = this.totalSlides - 1;
                if (targetSlide >= this.totalSlides) targetSlide = 0;

                this.goToSlide(targetSlide);
            }

            getCurrentTranslateX() {
                const transform = this.track.style.transform;
                if (transform && transform !== 'none') {
                    const match = transform.match(/translateX\(([^)]+)px\)/);
                    return match ? parseFloat(match[1]) : 0;
                }
                return 0;
            }

            showDragIndicator() {
                if (this.dragIndicator) {
                    this.dragIndicator.classList.add('show');
                }
            }

            hideDragIndicator() {
                if (this.dragIndicator) {
                    this.dragIndicator.classList.remove('show');
                }
            }

            updateCounter() {
                const currentSlideElement = document.getElementById('industriesCurrentSlide');
                const totalSlidesElement = document.getElementById('industriesTotalSlides');

                if (currentSlideElement) {
                    currentSlideElement.textContent = this.currentSlide + 1;
                }
                if (totalSlidesElement) {
                    totalSlidesElement.textContent = this.totalSlides;
                }
            }

            updateActiveStates() {
                // Update slide active states
                this.slides.forEach((slide, index) => {
                    slide.classList.toggle('active', index === this.currentSlide);
                });

                // Update dot active states
                this.dots.forEach((dot, index) => {
                    dot.classList.toggle('active', index === this.currentSlide);
                });
            }



            getVisibleSlides() {
                const width = window.innerWidth;
                if (width < 769) return 1; // Mobile
                if (width < 1025) return 2; // Tablet
                if (width < 1281) return 3; // Small desktop
                return 4; // Large desktop
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

            // Enhanced navigation with free scroll support
            goToSlide(index) {
                if (this.isAnimating || this.totalSlides < 1) return;

                // Handle infinite loop bounds
                if (index < 0) {
                    index = this.totalSlides - 1;
                } else if (index >= this.totalSlides) {
                    index = 0;
                }

                if (index === this.currentSlide) return;

                this.isAnimating = true;
                this.currentSlide = index;

                if (this.track) {
                    const slideWidth = this.track.offsetWidth / this.getVisibleSlides();
                    const translateX = -(index * slideWidth);

                    // Add smooth transition for button clicks
                    this.track.style.transition = 'transform 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
                    this.track.style.transform = `translateX(${translateX}px)`;

                    // Remove transition after animation
                    setTimeout(() => {
                        this.track.style.transition = '';
                    }, 300);
                }

                this.updateActiveStates();
                this.updateCounter();

                setTimeout(() => {
                    this.isAnimating = false;
                }, 300);
            }

            startAutoPlay() {
                if (this.autoPlayInterval) {
                    clearInterval(this.autoPlayInterval);
                }

                this.autoPlayInterval = setInterval(() => {
                    this.nextSlide();
                }, 5000); // Auto-advance every 5 seconds
            }

            stopAutoPlay() {
                if (this.autoPlayInterval) {
                    clearInterval(this.autoPlayInterval);
                    this.autoPlayInterval = null;
                }
            }

            cleanup() {
                this.stopAutoPlay();
                if (this.momentumInterval) {
                    clearInterval(this.momentumInterval);
                    this.momentumInterval = null;
                }
            }
        }

        // Initialize carousel when DOM is loaded </script>
        document.addEventListener('DOMContentLoaded', function() {
            const carouselContainer = document.querySelector('.industries-carousel-container');
            const carouselTrack = document.getElementById('industriesCarouselTrack');
            const slides = document.querySelectorAll('.industries-carousel-slide');

            if (carouselContainer && slides.length > 0) {
                try {
                    window.industriesCarousel = new IndustriesCarousel();
                    console.log('Industries carousel initialized successfully with', window.industriesCarousel.totalSlides, 'slides');
                } catch (error) {
                    console.error('Error initializing industries carousel:', error);
                }
            } else {
                console.log('Industries carousel not initialized - missing container or slides');
            }
        });

        // Global functions for navigation
        function industriesGoToSlide(index) {
            if (window.industriesCarousel) {
                window.industriesCarousel.goToSlide(index);
            }
        }

        function industriesNextSlide() {
            if (window.industriesCarousel) {
                window.industriesCarousel.nextSlide();
            }
        }

        function industriesPrevSlide() {
            if (window.industriesCarousel) {
                window.industriesCarousel.prevSlide();
            }
        }


// Gallery functionality
        let currentImages = [];
        let currentImageIndex = 0;

        function openGallery(images, startIndex = 0) {
            currentImages = images;
            currentImageIndex = startIndex;
            document.getElementById('galleryImage').src = images[startIndex];
            document.getElementById('galleryModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeGallery() {
            document.getElementById('galleryModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        function changeImage(direction) {
            currentImageIndex = (currentImageIndex + direction + currentImages.length) % currentImages.length;
            document.getElementById('galleryImage').src = currentImages[currentImageIndex];
        }

        // Close gallery on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeGallery();
            } else if (e.key === 'ArrowLeft') {
                changeImage(-1);
            } else if (e.key === 'ArrowRight') {
                changeImage(1);
            }
        });

        // Close gallery on overlay click
        document.getElementById('galleryModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeGallery();
            }
        });

        // Like and Bookmark functionality
        const actionButtons = document.querySelectorAll('.far.fa-heart, .far.fa-bookmark');
        actionButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();

                // Toggle between filled and outline icons
                if (this.classList.contains('far')) {
                    this.classList.remove('far');
                    this.classList.add('fas');
                } else {
                    this.classList.remove('fas');
                    this.classList.add('far');
                }
            });
        });

        // Start carousel auto-advance
        startCarouselAutoAdvance();

        // Carousel functionality
        function changeSlide(carouselId, direction) {
            let container;
            if (carouselId === 'featured') {
                container = document.querySelector('.featured-item .carousel-container');
            } else {
                container = document.querySelector(`[data-item-id="${carouselId}"] .carousel-container`);
            }

            if (!container) return;

            const slides = container.querySelectorAll('.carousel-slide');
            const indicators = container.querySelectorAll('.carousel-indicator');
            let currentIndex = 0;

            slides.forEach((slide, index) => {
                if (slide.classList.contains('active')) {
                    currentIndex = index;
                }
            });

            const newIndex = (currentIndex + direction + slides.length) % slides.length;

            slides[currentIndex].classList.remove('active');
            slides[newIndex].classList.add('active');

            if (indicators.length > 0) {
                indicators[currentIndex].classList.remove('active');
                indicators[newIndex].classList.add('active');
            }
        }

        function goToSlide(carouselId, index) {
            let container;
            if (carouselId === 'featured') {
                container = document.querySelector('.featured-item .carousel-container');
            } else {
                container = document.querySelector(`[data-item-id="${carouselId}"] .carousel-container`);
            }

            if (!container) return;

            const slides = container.querySelectorAll('.carousel-slide');
            const indicators = container.querySelectorAll('.carousel-indicator');

            slides.forEach(slide => slide.classList.remove('active'));
            indicators.forEach(indicator => indicator.classList.remove('active'));

            slides[index].classList.add('active');
            if (indicators[index]) {
                indicators[index].classList.add('active');
            }
        }

        // Auto-advance carousel for featured item
        function startCarouselAutoAdvance() {
            const featuredCarousel = document.querySelector('.featured-item .carousel-container');
            if (featuredCarousel) {
                const slides = featuredCarousel.querySelectorAll('.carousel-slide');
                if (slides.length > 1) {
                    setInterval(() => {
                        changeSlide('featured', 1);
                    }, 5000); // Change slide every 5 seconds
                }
            }
        }

        // Filter buttons functionality
        document.addEventListener('DOMContentLoaded', function() {
            const filterButtons = document.querySelectorAll('.filter-button');
            const contentCards = document.querySelectorAll('.content-card');

            filterButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Remove active class from all buttons
                    filterButtons.forEach(btn => btn.classList.remove('active'));

                    // Add active class to clicked button
                    this.classList.add('active');

                    // Get filter type
                    const filterType = this.getAttribute('data-filter');

                    // Filter content cards
                    contentCards.forEach(card => {
                        const cardType = card.getAttribute('data-type');

                        if (filterType === 'all' || filterType === 'latest' || filterType === cardType) {
                            card.style.display = 'block';
                        } else {
                            card.style.display = 'none';
                        }
                    });
                });
            });

            // Search functionality
            const searchInput = document.querySelector('input[type="search"]');
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();

                contentCards.forEach(card => {
                    const title = card.querySelector('h4').textContent.toLowerCase();
                    const description = card.querySelector('p.card-description').textContent.toLowerCase();

                    if (title.includes(searchTerm) || description.includes(searchTerm)) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });

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
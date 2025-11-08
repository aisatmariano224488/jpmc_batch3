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

// Simple tab functionality
        document.addEventListener('DOMContentLoaded', function() {
            const tabButtons = document.querySelectorAll('.tab-btn');

            tabButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Remove active class from all buttons
                    tabButtons.forEach(btn => {
                        btn.classList.remove('bg-primary', 'text-white');
                        btn.classList.add('bg-gray-200', 'hover:bg-gray-300');
                    });

                    // Add active class to clicked button
                    this.classList.remove('bg-gray-200', 'hover:bg-gray-300');
                    this.classList.add('bg-primary', 'text-white');

                    // Get category to filter
                    const category = this.getAttribute('data-category');
                    const productCards = document.querySelectorAll('.product-card');

                    // Show/hide products based on category
                    productCards.forEach(card => {
                        if (category === 'all' || card.getAttribute('data-category') === category) {
                            card.style.display = 'block';
                        } else {
                            card.style.display = 'none';
                        }
                    });
                });
            });

            // Modal functionality for products
            const viewDetailsButtons = document.querySelectorAll('.view-details');
            const productModal = document.getElementById('productModal');
            const closeModal = document.getElementById('closeModal');

            viewDetailsButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const productCard = this.closest('.product-card');
                    const productId = this.getAttribute('data-product-id');
                    const productName = productCard.querySelector('h3').textContent;
                    const productImage = productCard.querySelector('div[style*="background-image"]').style.backgroundImage;
                    const productDescription = this.getAttribute('data-description');
                    const productFeatures = JSON.parse(this.getAttribute('data-features'));

                    // Populate modal
                    document.getElementById('modalProductTitle').textContent = productName;
                    document.getElementById('modalProductDescription').textContent = productDescription;
                    document.getElementById('modalProductImage').style.backgroundImage = productImage;

                    const featuresList = document.getElementById('modalProductFeatures');
                    featuresList.innerHTML = '';
                    productFeatures.forEach(feature => {
                        const li = document.createElement('li');
                        li.className = 'flex items-start';
                        li.innerHTML = `
                            <i class="fas fa-check-circle text-primary mt-1 mr-3"></i>
                            <span>${feature}</span>
                        `;
                        featuresList.appendChild(li);
                    });

                    // Show modal
                    productModal.style.display = 'flex';
            });

            // Modal functionality for services
            const learnMoreButtons = document.querySelectorAll('.learn-more');
            const serviceModal = document.getElementById('serviceModal');
            const closeServiceModal = document.getElementById('closeServiceModal');

            learnMoreButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const serviceCard = this.closest('.service-card');
                    const serviceId = this.getAttribute('data-service-id');
                    const serviceName = serviceCard.querySelector('h3').textContent;
                    const serviceDescription = serviceCard.querySelector('p').textContent;
                    const youtubeUrl = this.getAttribute('data-youtube-url');

                    // Populate modal
                    document.getElementById('modalServiceTitle').textContent = serviceName;
                    document.getElementById('modalServiceDescription').textContent = serviceDescription;
                    
                    // Create YouTube embed URL from the provided URL
                    let videoId = '';
                    if (youtubeUrl) {
                        // Extract video ID from various YouTube URL formats
                        const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/;
                        const match = youtubeUrl.match(regExp);
                        videoId = (match && match[2].length === 11) ? match[2] : '';
                    }

                    const videoContainer = document.getElementById('modalServiceVideo');
                    if (videoId) {
                        videoContainer.innerHTML = `
                            <div class="flex justify-center items-center">
                                <div class="w-full max-w-4xl">
                                    <div style="position: relative; padding-bottom: 45%; height: 0; overflow: hidden;">
                                        <iframe 
                                            style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;"
                                            class="rounded-lg" 
                                            src="https://www.youtube.com/embed/${videoId}?rel=0&showinfo=0" 
                                            frameborder="0" 
                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                            allowfullscreen>
                                        </iframe>
                                    </div>
                                </div>
                            </div>
                        `;
                    } else {
                        // Fallback to image if no YouTube URL is provided
                        const serviceImage = serviceCard.querySelector('div[style*="background-image"]').style.backgroundImage;
                        videoContainer.innerHTML = `
                            <div class="h-64 bg-gray-200 bg-cover bg-center rounded-lg" style="background-image: ${serviceImage}"></div>
                        `;
                    }

                    // Show modal
                    serviceModal.style.display = 'flex';
                });
            });
            // Close modals
            closeModal.addEventListener('click', function() {
                productModal.style.display = 'none';
            });
                serviceModal.style.display = 'none';
            });

            // Close modal when clicking outside
            window.addEventListener('click', function(e) {
                if (e.target === productModal) {
                    productModal.style.display = 'none';
                }
                if (e.target === serviceModal) {
                    serviceModal.style.display = 'none';
                }
            });
        });

// 1. Exit modals with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        if (productModal && productModal.style.display === 'flex') {
            productModal.style.display = 'none';
        }
        if (serviceModal && serviceModal.style.display === 'flex') {
            serviceModal.style.display = 'none';
        }
    }
});

// 2. Open product modal by clicking the whole product card
document.querySelectorAll('.product-card').forEach(card => {
    card.addEventListener('click', function(e) {
        // Prevent if clicking on a button or link inside the card
        if (e.target.closest('.view-details') || e.target.tagName === 'BUTTON' || e.target.tagName === 'A') return;
        const viewBtn = card.querySelector('.view-details');
        if (viewBtn) viewBtn.click();
    });
});

// 3. Open service modal by clicking the whole service card
document.querySelectorAll('.service-card').forEach(card => {
    card.addEventListener('click', function(e) {
        if (e.target.closest('.learn-more') || e.target.tagName === 'BUTTON' || e.target.tagName === 'A') return;
        const learnBtn = card.querySelector('.learn-more');
        if (learnBtn) learnBtn.click();
    });
});        
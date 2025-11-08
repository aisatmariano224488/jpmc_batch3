// FAQ Toggle Functionality with smoother animations
        document.querySelectorAll('.faq-item').forEach(item => {
            item.querySelector('.faq-question').addEventListener('click', () => {
                const isActive = item.classList.contains('active');
                const answer = item.querySelector('.faq-answer');

                // Close all other FAQs with smooth animation
                document.querySelectorAll('.faq-item').forEach(otherItem => {
                    if (otherItem !== item && otherItem.classList.contains('active')) {
                        otherItem.classList.remove('active');
                        // Add a small delay to prevent animation conflicts
                        setTimeout(() => {
                            otherItem.style.transform = 'translateY(0)';
                        }, 50);
                    }
                });

                // Toggle current FAQ with enhanced animation
                if (!isActive) {
                    // Add active class first
                    item.classList.add('active');

                    // Smooth scroll to the FAQ if it's not fully visible
                    setTimeout(() => {
                        const rect = item.getBoundingClientRect();
                        const isVisible = rect.top >= 0 && rect.bottom <= window.innerHeight;

                        if (!isVisible) {
                            item.scrollIntoView({
                                behavior: 'smooth',
                                block: 'nearest',
                                inline: 'nearest'
                            });
                        }
                    }, 300);

                } else {
                    item.classList.remove('active');
                }

                // Add smooth animation class
                if (!isActive) {
                    item.classList.add('animate-slide-up');
                    // Remove animation class after animation completes
                    setTimeout(() => {
                        item.classList.remove('animate-slide-up');
                    }, 600);
                }
            });
        });

        // Enhanced Search Functionality with smooth filtering
        let searchTimeout;
        const faqSearchInput = document.getElementById('faqSearch');
        if (faqSearchInput) {
            let searchTimeout;
            faqSearchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    const searchTerm = this.value.toLowerCase();
                    const faqItems = document.querySelectorAll('.faq-item');
                    let visibleCount = 0;

                    faqItems.forEach((item, index) => {
                        const question = item.querySelector('h3').textContent.toLowerCase();
                        const answer = item.querySelector('.faq-answer-content').textContent.toLowerCase();

                        if (question.includes(searchTerm) || answer.includes(searchTerm)) {
                            item.style.display = 'block';
                            item.style.animation = `fadeIn 0.4s ease-out ${index * 0.05}s both`;
                            visibleCount++;
                        } else {
                            item.style.display = 'none';
                        }
                    });

                    const noResultsElement = document.querySelector('.no-results');
                    if (visibleCount === 0 && searchTerm.length > 0) {
                        if (!noResultsElement) {
                            const noResults = document.createElement('div');
                            noResults.className = 'no-results text-center py-12 animate-fade-in';
                            noResults.innerHTML = `
                                <div class="w-20 h-20 bg-gradient-to-r from-gray-100 to-gray-200 rounded-full flex items-center justify-center mx-auto mb-6">
                                    <i class="fas fa-search text-3xl text-gray-400"></i>
                                </div>
                                <h3 class="text-2xl font-bold text-gray-700 mb-4">No results found</h3>
                                <p class="text-gray-500 text-lg mb-6">Try searching with different keywords or contact our technical support team</p>
                                <a href="contact.php" class="btn-primary text-white px-6 py-3 rounded-lg font-semibold inline-flex items-center gap-2">
                                    <i class="fas fa-envelope"></i>
                                    Contact Support
                                </a>
                            `;
                            const container = document.querySelector('.faq-container .space-y-6');
                            if (container) container.appendChild(noResults);
                        }
                    } else if (noResultsElement) {
                        noResultsElement.remove();
                    }
                }, 300);
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

        // Enhanced Intersection Observer for smoother animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                    entry.target.style.transition = 'all 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94)';
                }
            });
        }, observerOptions);

        // Observe all FAQ items with staggered animation
        document.querySelectorAll('.faq-item').forEach((item, index) => {
            item.style.opacity = '0';
            item.style.transform = 'translateY(30px)';
            item.style.transition = `all 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94) ${index * 0.1}s`;
            observer.observe(item);
        });

        // Add keyboard navigation support
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                // Close all open FAQs when Escape is pressed
                document.querySelectorAll('.faq-item.active').forEach(item => {
                    item.classList.remove('active');
                });
            }
        });

        // Add scroll-triggered animations for stats cards
        const statsObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.transform = 'translateY(0) scale(1)';
                    entry.target.style.opacity = '1';
                }
            });
        }, {
            threshold: 0.5
        });

        document.querySelectorAll('.stats-card').forEach(card => {
            card.style.transform = 'translateY(20px) scale(0.95)';
            card.style.opacity = '0';
            card.style.transition = 'all 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94)';
            statsObserver.observe(card);
        });
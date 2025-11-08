document.addEventListener('DOMContentLoaded', function() {
            // Modal functionality for awards
            const awardCards = document.querySelectorAll('.award-card');
            const awardModal = document.getElementById('awardModal');
            const closeAwardModal = document.getElementById('closeAwardModal');

            awardCards.forEach(card => {
                card.addEventListener('click', function() {
                    const title = this.querySelector('h3').textContent;
                    const description = this.querySelector('p') ? this.querySelector('p').textContent : '';
                    const year = this.querySelector('.text-sm span') ? this.querySelector('.text-sm span').textContent : '';
                    const image = this.querySelector('img') ? this.querySelector('img').src.split('/').pop() : '';

                    // Populate modal
                    document.getElementById('awardModalTitle').textContent = title;
                    document.getElementById('awardModalDesc').textContent = description || 'This is a sample description for the achievement.';
                    document.getElementById('awardModalDate').textContent = year ? `Received: March 15, ${year}` : 'Received: March 15, 2022';

                    const modalImage = document.getElementById('awardModalImage');
                    if (image && image !== 'null' && image !== '') {
                        // Construct the full image path for modal
                        const imagePath = 'assets/img/awards/' + image;
                        modalImage.style.backgroundImage = `url('${imagePath}')`;
                        modalImage.style.display = 'block';
                    } else {
                        modalImage.style.display = 'none';
                    }

                    // Show modal
                    awardModal.style.display = 'block';
                });
            });

            // Close modal
            closeAwardModal.addEventListener('click', function() {
                awardModal.style.display = 'none';
            });

            // Close modal when clicking outside
            window.addEventListener('click', function(e) {
                if (e.target === awardModal) {
                    awardModal.style.display = 'none';
                }
            });

            // 1. Exit modal with Escape key
            document.addEventListener('keydown', function(e) {
                const awardModal = document.getElementById('awardModal');
                if (e.key === 'Escape' && awardModal && awardModal.style.display === 'block') {
                    awardModal.style.display = 'none';
                }
            });

            // 2. Open award modal by clicking the whole award card
            document.querySelectorAll('.award-card').forEach(card => {
                card.addEventListener('click', function(e) {
                    // Prevent if clicking on a button or link inside the card
                    if (e.target.closest('button') || e.target.tagName === 'BUTTON' || e.target.tagName === 'A') return;
                    // Simulate click on the card to open modal (already opens modal on card click)
                    // But if you want to ensure it always opens, you can call the click handler directly
                    const closeAwardModal = document.getElementById('closeAwardModal');
                    if (closeAwardModal) {
                        // No-op, since the card click already opens the modal
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
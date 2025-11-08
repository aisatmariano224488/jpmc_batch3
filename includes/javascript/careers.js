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

function updateStepIndicator(step) {
            const indicators = document.querySelectorAll('.step-indicator');
            indicators.forEach((indicator, index) => {
                const circle = indicator.querySelector('.step-circle');
                const text = indicator.querySelector('span');

                if (index + 1 < step) {
                    circle.classList.remove('bg-gray-200', 'text-gray-600');
                    circle.classList.add('bg-primary', 'text-white');
                    text.classList.remove('text-gray-600');
                    text.classList.add('text-primary');
                } else if (index + 1 === step) {
                    circle.classList.remove('bg-gray-200', 'text-gray-600');
                    circle.classList.add('bg-primary', 'text-white');
                    text.classList.remove('text-gray-600');
                    text.classList.add('text-primary');
                } else {
                    circle.classList.remove('bg-primary', 'text-white');
                    circle.classList.add('bg-gray-200', 'text-gray-600');
                    text.classList.remove('text-primary');
                    text.classList.add('text-gray-600');
                }
            });
        }

        function nextStep(currentStep) {
            // Validate current step
            const currentForm = document.querySelector(`#step${currentStep} form`);
            if (currentForm && !validateStep(currentStep)) {
                return;
            }

            // Transfer form data from step 2 to step 3
            if (currentStep === 2) {
                // Get values from step 2
                const hasSkills = document.querySelector('input[name="hasSkills"]:checked')?.value || '';
                const hoursRequired = document.querySelector('input[name="hoursRequired"]')?.value || '';
                const workOnsite = document.querySelector('input[name="workOnsite"]:checked')?.value || '';

                // Create hidden fields in step 3 form to pass these values
                const personalForm = document.getElementById('personalDetailsForm');

                // Create or update hidden fields
                let hasSkillsField = personalForm.querySelector('input[name="hasSkills"]');
                if (!hasSkillsField) {
                    hasSkillsField = document.createElement('input');
                    hasSkillsField.type = 'hidden';
                    hasSkillsField.name = 'hasSkills';
                    personalForm.appendChild(hasSkillsField);
                }
                hasSkillsField.value = hasSkills;

                let hoursField = personalForm.querySelector('input[name="hoursRequired"]');
                if (!hoursField) {
                    hoursField = document.createElement('input');
                    hoursField.type = 'hidden';
                    hoursField.name = 'hoursRequired';
                    personalForm.appendChild(hoursField);
                }
                hoursField.value = hoursRequired;

                let workOnsiteField = personalForm.querySelector('input[name="workOnsite"]');
                if (!workOnsiteField) {
                    workOnsiteField = document.createElement('input');
                    workOnsiteField.type = 'hidden';
                    workOnsiteField.name = 'workOnsite';
                    personalForm.appendChild(workOnsiteField);
                }
                workOnsiteField.value = workOnsite;
            }

            document.querySelector(`#step${currentStep}`).classList.remove('active');
            document.querySelector(`#step${currentStep + 1}`).classList.add('active');
            updateStepIndicator(currentStep + 1);
        }

        function prevStep(currentStep) {
            document.querySelector(`#step${currentStep}`).classList.remove('active');
            document.querySelector(`#step${currentStep - 1}`).classList.add('active');
            updateStepIndicator(currentStep - 1);
        }

        function validateStep(step) {
            const form = document.querySelector(`#step${step} form`);
            if (!form) return true;

            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;

            requiredFields.forEach(field => {
                if (!field.value) {
                    field.classList.add('border-red-500');
                    isValid = false;
                } else {
                    field.classList.remove('border-red-500');
                }
            });

            if (!isValid) {
                alert('Please fill in all required fields');
            }

            return isValid;
        }

        function openApplicationModal(position) {
            // Parse position if it's a string (for backward compatibility)
            if (typeof position === 'string') {
                // Fallback for old format
                document.getElementById('jobTitle').textContent = position;
                document.getElementById('jobPosition').textContent = position;
                document.getElementById('jobShift').textContent = 'To be discussed';
                document.getElementById('jobSchedule').textContent = 'To be discussed';
                document.getElementById('jobLocation').textContent = 'To be discussed';
                document.getElementById('jobType').textContent = 'To be discussed';
                document.getElementById('jobDescription').textContent = 'Job description will be provided during the interview.';
                document.getElementById('jobQualifications').innerHTML = '<li>Qualifications will be discussed during the interview.</li>';
                document.getElementById('hiddenPositionId').value = '';
                new bootstrap.Modal(document.getElementById('applicationModal')).show();
                return;
            }

            // Reset forms
            document.querySelectorAll('form').forEach(form => form.reset());

            // Reset step indicators
            updateStepIndicator(1);

            // Show/hide hours requirement based on position type
            const hoursSection = document.querySelector('#hoursRequirementSection');
            if (hoursSection) {
                const isInternship = position.type.toLowerCase() === 'internship';
                hoursSection.style.display = isInternship ? 'block' : 'none';
                const hoursInput = hoursSection.querySelector('input[name="hoursRequired"]');
                if (hoursInput) {
                    hoursInput.required = isInternship;
                }
            }

            // Set job details from the position object
            document.getElementById('jobTitle').textContent = position.title;
            document.getElementById('jobPosition').textContent = position.title;
            document.getElementById('jobShift').textContent = position.shift;
            document.getElementById('jobSchedule').textContent = position.schedule;
            document.getElementById('jobLocation').textContent = position.location;
            document.getElementById('jobType').textContent = position.type.charAt(0).toUpperCase() + position.type.slice(1);
            document.getElementById('jobDescription').textContent = position.description;

            // Set qualifications
            const qualificationsList = document.getElementById('jobQualifications');
            if (position.qualifications && position.qualifications.length > 0) {
                qualificationsList.innerHTML = position.qualifications.map(q => `<li class="flex items-start">
            <svg class="h-5 w-5 text-primary mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            ${q}
        </li>`).join('');
            } else {
                qualificationsList.innerHTML = '<li>Qualifications will be discussed during the interview.</li>';
            }

            // Set hidden position ID for form submission
            var hiddenPositionId = document.getElementById('hiddenPositionId');
            if (hiddenPositionId) hiddenPositionId.value = position.id;

            new bootstrap.Modal(document.getElementById('applicationModal')).show();
        }

        // Form submission handling
        document.getElementById('personalDetailsForm').addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent default form submission briefly

            // Show loading state
            const submitButton = this.querySelector('button[type="submit"]');
            const originalText = submitButton.innerHTML;
            submitButton.disabled = true;
            submitButton.innerHTML = `
        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        Submitting...
    `;

            // Get job title for the success message
            const jobTitle = document.getElementById('jobTitle').textContent;

            // Show success message immediately
            const modal = document.getElementById('applicationModal');
            const modalTitle = modal.querySelector('.modal-title');
            const modalBody = modal.querySelector('.modal-body');

            // Save original content to restore if there's an error
            const originalTitle = modalTitle.innerHTML;
            const originalBody = modalBody.innerHTML;

            // Update modal with success message
            modalTitle.textContent = `Apply for ${jobTitle}`;
            modalBody.innerHTML = `
        <div class="text-center py-8">
            <svg class="mx-auto h-16 w-16 text-green-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <h3 class="text-2xl font-bold text-gray-900 mb-2">Application Submitted!</h3>
            <p class="text-gray-600 mb-6">Thank you for applying. We will review your application and get back to you soon.</p>
            <button type="button" id="closeSuccessBtn" class="btn-modern btn-primary" data-bs-dismiss="modal">Close</button>
        </div>
    `;

            // Submit the form in the background
            const formData = new FormData(this);

            fetch('careers.php', {
                    method: 'POST',
                    body: formData
                })
                .catch(error => {
                    console.error('Error submitting form:', error);
                    // In case of error, restore original modal content
                    modalTitle.innerHTML = originalTitle;
                    modalBody.innerHTML = originalBody;
                    alert('There was an error submitting your application. Please try again.');
                });
        });

        // File upload handling
        document.querySelectorAll('.file-upload').forEach(upload => {
            const input = upload.querySelector('input[type="file"]');
            const dropZone = upload;

            dropZone.addEventListener('click', () => input.click());

            dropZone.addEventListener('dragover', (e) => {
                e.preventDefault();
                dropZone.classList.add('border-primary', 'bg-blue-50');
            });

            dropZone.addEventListener('dragleave', () => {
                dropZone.classList.remove('border-primary', 'bg-blue-50');
            });

            dropZone.addEventListener('drop', (e) => {
                e.preventDefault();
                dropZone.classList.remove('border-primary', 'bg-blue-50');
                input.files = e.dataTransfer.files;
                updateFileName(input);
            });

            input.addEventListener('change', () => {
                updateFileName(input);
            });
        });

        function updateFileName(input) {
            const fileName = input.files[0]?.name;
            if (fileName) {
                const dropZone = input.closest('.file-upload');
                const text = dropZone.querySelector('p');
                text.textContent = fileName;
            }
        }

        // Gallery functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Gallery filter functionality
            const filterBtns = document.querySelectorAll('.gallery-filter-btn');
            const galleryItems = document.querySelectorAll('.gallery-item');

            filterBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const filter = this.dataset.filter;

                    // Update active button
                    filterBtns.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');

                    // Filter gallery items
                    galleryItems.forEach(item => {
                        const categories = item.dataset.category.split(' ');

                        if (filter === 'all' || categories.includes(filter)) {
                            item.classList.remove('hidden');
                        } else {
                            item.classList.add('hidden');
                        }
                    });
                });
            });

            // Load more functionality
            const loadMoreBtn = document.getElementById('loadMoreBtn');
            if (loadMoreBtn) {
                loadMoreBtn.addEventListener('click', function() {
                    const originalText = this.innerHTML;
                    this.innerHTML = '<div class="gallery-loading"></div> Loading...';
                    this.disabled = true;

                    // Simulate loading more items (you can replace this with actual AJAX call)
                    setTimeout(() => {
                        this.innerHTML = originalText;
                        this.disabled = false;
                        // Add notification
                        const notification = document.createElement('div');
                        notification.className = 'alert alert-info mt-4';
                        notification.textContent = 'More media will be loaded from your database. Connect to your media management system.';
                        this.parentNode.insertBefore(notification, this);
                        setTimeout(() => notification.remove(), 5000);
                    }, 2000);
                });
            }
        });

        // Gallery modal functionality
        function openGalleryModal(type, src, title, description) {
            const modal = document.getElementById('galleryModal');
            const modalTitle = document.getElementById('galleryModalTitle');
            const modalContent = document.getElementById('galleryModalContent');
            const modalDescription = document.getElementById('galleryModalDescription');

            modalTitle.textContent = title;
            modalDescription.textContent = description;

            // Clear previous content
            modalContent.innerHTML = '';

            if (type === 'image') {
                const img = document.createElement('img');
                img.src = src;
                img.alt = title;
                img.style.maxWidth = '100%';
                img.style.maxHeight = '70vh';
                img.style.objectFit = 'contain';
                modalContent.appendChild(img);
            } else if (type === 'video') {
                const video = document.createElement('video');
                video.src = src;
                video.controls = true;
                video.style.maxWidth = '100%';
                video.style.maxHeight = '70vh';
                video.style.objectFit = 'contain';
                modalContent.appendChild(video);
            }

            new bootstrap.Modal(modal).show();
        }

        // Testimonial modal functionality
        function openTestimonialModal(type, name, position, testimonial) {
            const modal = document.getElementById('galleryModal');
            const modalTitle = document.getElementById('galleryModalTitle');
            const modalContent = document.getElementById('galleryModalContent');
            const modalDescription = document.getElementById('galleryModalDescription');

            modalTitle.innerHTML = `<i class="fas fa-quote-left mr-2"></i>${name}`;
            modalDescription.textContent = testimonial;

            // Clear previous content
            modalContent.innerHTML = '';

            // Create testimonial content
            const testimonialContent = document.createElement('div');
            testimonialContent.className = 'testimonial-modal-content text-center py-8 px-6';

            if (type === 'video') {
                testimonialContent.innerHTML = `
            <div class="mb-6">
                <div class="testimonial-video-placeholder bg-gray-200 rounded-lg p-12 mb-6">
                    <i class="fas fa-video text-6xl text-gray-400 mb-4"></i>
                    <p class="text-gray-600">Video testimonial will be loaded here</p>
                    <button class="btn-modern btn-primary mt-4">
                        <i class="fas fa-play mr-2"></i>Play Video
                    </button>
                </div>
            </div>
            <div class="testimonial-info">
                <h3 class="text-2xl font-bold text-gray-800 mb-2">${name}</h3>
                <p class="text-lg text-primary mb-4">${position}</p>
                <div class="testimonial-quote bg-blue-50 p-6 rounded-lg border-l-4 border-primary">
                    <i class="fas fa-quote-left text-primary text-2xl mb-3"></i>
                    <p class="text-gray-700 text-lg italic leading-relaxed">"${testimonial}"</p>
                </div>
            </div>
        `;
            } else {
                testimonialContent.innerHTML = `
            <div class="testimonial-info max-w-3xl mx-auto">
                <div class="mb-6">
                    <div class="w-24 h-24 bg-primary rounded-full flex items-center justify-center text-white text-3xl font-bold mx-auto mb-4">
                        ${name.charAt(0)}
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-2">${name}</h3>
                    <p class="text-lg text-primary mb-6">${position}</p>
                </div>
                <div class="testimonial-quote bg-blue-50 p-8 rounded-lg border-l-4 border-primary">
                    <i class="fas fa-quote-left text-primary text-3xl mb-4"></i>
                    <p class="text-gray-700 text-xl italic leading-relaxed">"${testimonial}"</p>
                </div>
                <div class="mt-6 flex justify-center">
                    <div class="flex text-yellow-400">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                </div>
            </div>
        `;
            }

            modalContent.appendChild(testimonialContent);
            new bootstrap.Modal(modal).show();
        }

        // Close modal when clicking outside content
        document.getElementById('galleryModal').addEventListener('click', function(e) {
            if (e.target === this) {
                bootstrap.Modal.getInstance(this).hide();
            }
        });

        // Keyboard navigation for gallery modal
        document.addEventListener('keydown', function(e) {
            const modal = document.getElementById('galleryModal');
            if (bootstrap.Modal.getInstance(modal) && bootstrap.Modal.getInstance(modal)._isShown) {
                if (e.key === 'Escape') {
                    bootstrap.Modal.getInstance(modal).hide();
                }
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            // Filter functionality
            const filterBtns = document.querySelectorAll('.gallery-filter-btn');
            const galleryItems = document.querySelectorAll('.gallery-item');
            filterBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    filterBtns.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    const filter = this.dataset.filter;
                    galleryItems.forEach(item => {
                        if (filter === 'all' || item.dataset.batch === filter) {
                            item.style.display = '';
                        } else {
                            item.style.display = 'none';
                        }
                    });
                });
            });
            // GLightbox for zoom
            GLightbox({
                selector: '.glightbox'
            });

            const loadMoreBtn = document.getElementById('loadMoreGalleryBtn');
            const backBtn = document.getElementById('backGalleryBtn');
            const ojtgalleryItems = document.querySelectorAll('#ojtGallery .gallery-item');
            let itemsToShow = typeof max_initial !== 'undefined' ? max_initial : 6; // fallback value
            const increment = typeof max_initial !== 'undefined' ? max_initial : 6;

            loadMoreBtn.addEventListener('click', function() {
                let shown = 0;
                galleryItems.forEach((item, idx) => {
                    if (item.style.display !== 'none' && item.classList.contains('hidden') && shown < increment) {
                        item.classList.remove('hidden');
                        shown++;
                    }
                });
                backBtn.style.display = '';
                loadMoreBtn.style.display = 'none';
            });

            backBtn.addEventListener('click', function() {
                let count = 0;
                galleryItems.forEach((item, idx) => {
                    if (item.style.display !== 'none') {
                        if (count >= itemsToShow) {
                            item.classList.add('hidden');
                        }
                        count++;
                    }
                });
                backBtn.style.display = 'none';
                loadMoreBtn.style.display = '';
            });

            // Hide Load More if not needed
            const initiallyHidden = Array.from(galleryItems).some(item => item.classList.contains('hidden'));
            if (!initiallyHidden) {
                loadMoreBtn.style.display = 'none';
            }
        });

        // 1. Exit modal with Escape key
        document.addEventListener('keydown', function(e) {
            const applicationModal = document.getElementById('applicationModal');
            if (e.key === 'Escape' && applicationModal && applicationModal.classList.contains('show')) {
                // Bootstrap modal: use .modal('hide') if using jQuery, or dispatch click on close button
                const closeBtn = applicationModal.querySelector('[data-bs-dismiss="modal"]');
                if (closeBtn) closeBtn.click();
            }
        });

        // 2. Open application modal by clicking the whole job card
        document.querySelectorAll('.job-card').forEach(card => {
            card.addEventListener('click', function(e) {
                if (e.target.closest('.view-details') || e.target.tagName === 'BUTTON' || e.target.tagName === 'A') return;
                const viewBtn = card.querySelector('.view-details');
                if (viewBtn) viewBtn.click();
            });
        });
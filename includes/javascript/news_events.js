document.addEventListener('DOMContentLoaded', function() {
    // FAQ data
    const faqs = [{
            q: 'What is sustainability?',
            a: 'Sustainability means meeting our own needs without compromising the ability of future generations to meet theirs.'
        },
        {
            q: 'What are your sustainability initiatives?',
            a: 'We focus on energy efficiency, waste reduction, and community engagement as part of our sustainability initiatives.'
        },
        {
            q: 'How can I participate in your sustainability programs?',
            a: 'You can participate by joining our events, volunteering, or contacting us through the website.'
        },
        {
            q: 'Do you have any certifications?',
            a: 'Yes, we are ISO 9001 and ISO 14001 certified for quality and environmental management.'
        },
        {
            q: 'Who can I contact for more info?',
            a: 'Please use our contact form or email us for more information about our sustainability efforts.'
        },
    ];

    // Render quick FAQ buttons
    function renderQuickQuestions() {
        const container = document.getElementById('faq-quick-questions');
        if (container) {
            container.innerHTML = '';
            faqs.forEach((faq) => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'bg-white border border-gray-300 text-gray-700 px-2 py-1 rounded-lg text-xs hover:bg-blue-100 transition';
                btn.textContent = faq.q;
                btn.onclick = () => sendUserMessage(faq.q);
                container.appendChild(btn);
            });
        }
    }

    // Show/hide chat box
    const chatBox = document.getElementById('chatbot-box');
    const openBtn = document.getElementById('open-chatbot');
    const closeBtn = document.getElementById('close-chatbot');
    const messages = document.getElementById('chatbot-messages');

    function showChatBox() {
        if (chatBox) {
            chatBox.classList.remove('opacity-0', 'pointer-events-none', 'scale-95');
            chatBox.classList.add('opacity-100', 'scale-100');
            if (openBtn) openBtn.classList.add('hidden');
            chatBox.style.pointerEvents = 'auto';
        }
    }

    function hideChatBox() {
        if (chatBox) {
            chatBox.classList.remove('opacity-100', 'scale-100');
            chatBox.classList.add('opacity-0', 'pointer-events-none', 'scale-95');
            setTimeout(() => {
                chatBox.style.pointerEvents = 'none';
                if (openBtn) openBtn.classList.remove('hidden');
            }, 300);
        }
    }

    if (openBtn) openBtn.addEventListener('click', e => {
        e.preventDefault();
        showChatBox();
    });
    if (closeBtn) closeBtn.addEventListener('click', hideChatBox);

    // Append messages
    function appendMessage(text, isUser) {
        if (!messages) return;
        const msgDiv = document.createElement('div');
        msgDiv.className = 'flex ' + (isUser ? 'justify-end' : 'items-start');
        const bubble = document.createElement('div');
        bubble.className = (isUser ?
            'bg-blue-500 text-white rounded-lg rounded-br-none' :
            'bg-gray-200 text-gray-800 rounded-lg rounded-bl-none') + ' px-3 py-2 text-sm max-w-[80%]';
        bubble.textContent = text;
        msgDiv.appendChild(bubble);
        messages.appendChild(msgDiv);
        messages.scrollTop = messages.scrollHeight;
    }

    function sendUserMessage(text) {
        appendMessage(text, true);
        setTimeout(() => {
            const answer = faqs.find(faq => faq.q === text)?.a || "Sorry, I don't have an answer for that.";
            appendMessage(answer, false);
        }, 500);
    }

    // Render FAQ buttons on load
    renderQuickQuestions();

    // ===== NEW TAB SWITCHING FUNCTIONALITY =====
    console.log('Initializing tab switching...');
    
    // Wait for DOM to be fully ready
    setTimeout(() => {
        initializeTabSwitching();
    }, 200);

    function initializeTabSwitching() {
        console.log('Setting up tab switching...');
        
        // Get all filter tabs
        const filterTabs = document.querySelectorAll('.filter-tab');
        console.log('Found filter tabs:', filterTabs.length);
        
        if (filterTabs.length === 0) {
            console.error('No filter tabs found!');
            return;
        }

        // Add click event to each tab
        filterTabs.forEach((tab, index) => {
            console.log(`Setting up tab ${index}:`, tab.getAttribute('data-filter'));
            
            tab.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const filterType = this.getAttribute('data-filter');
                console.log('Tab clicked:', filterType);
                
                // Remove active class from all tabs
                filterTabs.forEach(t => {
                    t.classList.remove('active');
                    t.style.backgroundColor = '';
                    t.style.color = '';
                });
                
                // Add active class to clicked tab
                this.classList.add('active');
                this.style.backgroundColor = '#1484c0';
                this.style.color = 'white';
                
                // Switch content
                switchContent(filterType);
            });
        });

        // Initialize with news content
        switchContent('news');
    }

    function switchContent(filterType) {
        console.log('Switching to content type:', filterType);
        
        // Define content sections mapping
        const contentSections = {
            'news': '.news-content',
            'events': '.events-content', 
            'videos': '.videos-content',
            'plant': '.plant-content'
        };
        
        // Hide all content sections
        Object.values(contentSections).forEach(selector => {
            const section = document.querySelector(selector);
            if (section) {
                section.style.display = 'none';
                console.log('Hiding section:', selector);
            }
        });
        
        // Show the selected content section
        const targetSelector = contentSections[filterType];
        if (targetSelector) {
            const targetSection = document.querySelector(targetSelector);
            if (targetSection) {
                // Set appropriate display style
                if (filterType === 'news') {
                    targetSection.style.display = 'block';
                } else {
                    targetSection.style.display = 'grid';
                }
                console.log('Showing section:', targetSelector, 'with display:', targetSection.style.display);
            } else {
                console.error('Target section not found:', targetSelector);
            }
        } else {
            console.error('No selector found for filter type:', filterType);
        }
    }

    // Plant Visit Detail Buttons
    document.querySelectorAll('.plant-detail-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const plantId = this.getAttribute('data-plant-id');
            window.location.href = `plant_visit_details.php?id=${plantId}`;
        });
    });

    // Videos & Promotions Modal Functionality
    const videoModal = document.getElementById('video-modal');
    const videoModalContent = document.getElementById('video-modal-content');
    const closeVideoModal = document.getElementById('close-video-modal');

    // Video detail buttons
    document.querySelectorAll('.video-detail-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const videoId = this.getAttribute('data-video-id');
            loadVideoDetails(videoId);
        });
    });

    function loadVideoDetails(videoId) {
        // Show loading state
        videoModalContent.innerHTML = '<div class="text-center py-8"><i class="fas fa-spinner fa-spin text-2xl text-blue-500"></i><p class="mt-2">Loading...</p></div>';
        videoModal.classList.remove('hidden');

        // Fetch video details via AJAX
        fetch(`get_video_details.php?id=${videoId}`)
            .then(response => response.text())
            .then(html => {
                videoModalContent.innerHTML = html;
            })
            .catch(error => {
                console.error('Error loading video details:', error);
                videoModalContent.innerHTML = '<div class="text-center py-8 text-red-500"><p>Error loading details. Please try again.</p></div>';
            });
    }

    // Close video modal
    if (closeVideoModal) {
        closeVideoModal.addEventListener('click', function() {
            videoModal.classList.add('hidden');
        });
    }

    // Close video modal when clicking outside
    if (videoModal) {
        videoModal.addEventListener('click', function(e) {
            if (e.target === videoModal) {
                videoModal.classList.add('hidden');
            }
        });
    }

    // Intersection Observer for fade-in animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    // Observe all cards and sections
    document.querySelectorAll('.news-card, .slide-in-left, .slide-in-right, .fade-in, .content-item').forEach(el => {
        observer.observe(el);
    });

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

    // Add event delegation for video-detail-btn
    // This ensures buttons work even if loaded dynamically
    document.body.addEventListener('click', function(e) {
        const videoBtn = e.target.closest('.video-detail-btn');
        if (videoBtn) {
            e.preventDefault();
            const videoId = videoBtn.getAttribute('data-video-id');
            if (videoId) loadVideoDetails(videoId);
        }
        const plantBtn = e.target.closest('.plant-detail-btn');
        if (plantBtn) {
            e.preventDefault();
            const plantId = plantBtn.getAttribute('data-plant-id');
            if (plantId) window.location.href = `plant_visit_details.php?id=${plantId}`;
        }
    });
});
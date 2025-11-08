// Wait for DOM to be fully loaded before initializing anything
document.addEventListener('DOMContentLoaded', function() {
    
    // Initialize AOS after DOM is loaded
    if (typeof AOS !== 'undefined') {
        AOS.init({
            once: true,
            duration: 900,
            easing: 'ease-out-cubic'
        });
    }

    // Animated counters
    const counters = document.querySelectorAll('.counter');
    if (counters.length > 0) {
        counters.forEach(function(counter) {
            let targetAttr = counter.getAttribute('data-target');
            if (!targetAttr) return;
            
            let target = parseInt(targetAttr);
            let count = 0;
            let step = Math.ceil(target / 100);

            function update() {
                if (count < target) {
                    count += step;
                    if (count > target) count = target;
                    counter.textContent = count.toLocaleString();
                    requestAnimationFrame(update);
                } else {
                    counter.textContent = target.toLocaleString();
                }
            }
            update();
        });
    }

    // Scroll listener for floating logo
    const logo = document.getElementById('floatingLogo');
    if (logo) {
        window.addEventListener('scroll', function() {
            const footer = document.getElementById('site-footer');
            if (!footer) return;
            
            const footerRect = footer.getBoundingClientRect();
            const windowHeight = window.innerHeight || document.documentElement.clientHeight;
            
            if (footerRect.top < windowHeight && footerRect.bottom > 0) {
                logo.style.opacity = '0';
                logo.style.pointerEvents = 'none';
            } else {
                logo.style.opacity = '0.6';
                logo.style.pointerEvents = 'auto';
            }
        });
    }

});
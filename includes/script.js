document.addEventListener('DOMContentLoaded', function() {
    const navLinks = document.querySelectorAll('nav a, nav button');
    const dropdownMenu = document.querySelector('.dropdown-menu');
    let timeout;

    navLinks.forEach(link => {
        link.addEventListener('click', function(event) {
            event.preventDefault();
            const targetId = this.getAttribute('href') ? this.getAttribute('href').substring(1) : this.closest('.group').querySelector('a').getAttribute('href').substring(1);
            const targetSection = document.getElementById(targetId);

            window.scrollTo({
                top: targetSection.offsetTop - 50,
                behavior: 'smooth'
            });
        });
    });

    document.querySelector('.group').addEventListener('mouseenter', function() {
        clearTimeout(timeout);
        dropdownMenu.style.display = 'block';
    });

    document.querySelector('.group').addEventListener('mouseleave', function() {
        timeout = setTimeout(function() {
            dropdownMenu.style.display = 'none';
        }, 500);
    });

    dropdownMenu.addEventListener('mouseenter', function() {
        clearTimeout(timeout);
        dropdownMenu.style.display = 'block';
    });

    dropdownMenu.addEventListener('mouseleave', function() {
        timeout = setTimeout(function() {
            dropdownMenu.style.display = 'none';
        }, 500);
    });
});
document.addEventListener('DOMContentLoaded', function() {
    const navLinks = document.querySelectorAll('nav a, nav button');
    const dropdownMenu = document.querySelector('.dropdown-menu');
    let timeout;

    navLinks.forEach(link => {
        link.addEventListener('click', function(event) {
            event.preventDefault();
            const targetId = this.getAttribute('href') ? this.getAttribute('href').substring(1) : this.closest('.group').querySelector('a').getAttribute('href').substring(1);
            const targetSection = document.getElementById(targetId);

            window.scrollTo({
                top: targetSection.offsetTop - 50,
                behavior: 'smooth'
            });
        });
    });

    document.querySelector('.group').addEventListener('mouseenter', function() {
        clearTimeout(timeout);
        dropdownMenu.style.display = 'block';
    });

    document.querySelector('.group').addEventListener('mouseleave', function() {
        timeout = setTimeout(function() {
            dropdownMenu.style.display = 'none';
        }, 500);
    });

    dropdownMenu.addEventListener('mouseenter', function() {
        clearTimeout(timeout);
        dropdownMenu.style.display = 'block';
    });

    dropdownMenu.addEventListener('mouseleave', function() {
        timeout = setTimeout(function() {
            dropdownMenu.style.display = 'none';
        }, 500); 
    });
});

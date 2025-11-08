document.addEventListener('DOMContentLoaded', function() {
            // Mobile menu toggle
            const mobileMenuButton = document.getElementById('mobileMenuButton');
            const mobileMenu = document.getElementById('mobileMenu');

            if (mobileMenuButton && mobileMenu) {
                mobileMenuButton.addEventListener('click', function() {
                    mobileMenu.classList.toggle('active');
                });
            }

            // Mobile dropdown menu toggle
            const mobileDropdowns = document.querySelectorAll('#mobileMenu li.relative');
            mobileDropdowns.forEach(dropdown => {
                const link = dropdown.querySelector('a');
                const submenu = dropdown.querySelector('ul');

                if (link && submenu) {
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        submenu.classList.toggle('hidden');
                    });
                }
            });

            // Close mobile menu when clicking outside
            document.addEventListener('click', function(e) {
                if (mobileMenu && mobileMenuButton && !mobileMenu.contains(e.target) && e.target !== mobileMenuButton) {
                    mobileMenu.classList.remove('active');
                }
            });

            // Position dropdown functionality
            const positionSelect = document.getElementById('position');
            const positionOther = document.getElementById('position_other');

            if (positionSelect && positionOther) {
                positionSelect.addEventListener('change', function() {
                    if (this.value === 'Others') {
                        positionOther.style.display = 'block';
                        positionOther.required = true;
                    } else {
                        positionOther.style.display = 'none';
                        positionOther.required = false;
                        positionOther.value = '';
                    }
                });
            }

            // Subject dropdown functionality
            const subjectSelect = document.getElementById('subject');
            const subjectOther = document.getElementById('subject_other');

            if (subjectSelect && subjectOther) {
                subjectSelect.addEventListener('change', function() {
                    if (this.value === 'other') {
                        subjectOther.style.display = 'block';
                        subjectOther.required = true;
                    } else {
                        subjectOther.style.display = 'none';
                        subjectOther.required = false;
                        subjectOther.value = '';
                    }
                });
            }
        });
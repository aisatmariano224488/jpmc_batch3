function showProcess(type) {
            const sections = ['plastic', 'rubber'];
            sections.forEach(section => {
                const el = document.getElementById(`${section}-section`);
                if (section === type) {
                    el.classList.remove('opacity-0', 'pointer-events-none');
                    el.classList.add('opacity-100');
                    el.style.zIndex = '10';
                } else {
                    el.classList.remove('opacity-100');
                    el.classList.add('opacity-0');
                    el.style.zIndex = '0';
                }
            });

            // Button style toggle
            sections.forEach(btn => {
                const el = document.getElementById(`btn-${btn}`);
                el.classList.remove('bg-blue-600', 'text-white');
                el.classList.add('bg-white', 'text-gray-700');
            });

            const activeBtn = document.getElementById(`btn-${type}`);
            activeBtn.classList.remove('bg-white', 'text-gray-700');
            activeBtn.classList.add('bg-blue-600', 'text-white');
        }

        // Default
        window.addEventListener('DOMContentLoaded', () => showProcess('plastic'));

        function openImageModal(src) {
            document.getElementById('modalImage').src = src;
            document.getElementById('imageModal').classList.remove('hidden');
        }

        document.getElementById('closeModal').addEventListener('click', function() {
            document.getElementById('imageModal').classList.add('hidden');
            document.getElementById('modalImage').src = '';
        });

        // Close when clicking outside the image
        document.getElementById('imageModal').addEventListener('click', function(e) {
            if (e.target.id === 'imageModal') {
                this.classList.add('hidden');
                document.getElementById('modalImage').src = '';
            }
        });
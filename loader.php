<!-- <div id="preloader">
    <video src="assets/video/logo_animation.mp4" autoplay muted playsinline></video>
    <button id="skip-button">Skip</button>
</div> -->

<style>
    <?php include 'includes/css/loader.css'; ?>
</style>

<!-- Privacy Modal Backdrop and Popup -->
<!-- Privacy Modal Backdrop and Popup -->
<div id="privacy-modal-backdrop"
    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[10000] hidden">
    <div id="privacy-popup" class="bg-white max-w-lg w-[90%] p-6 rounded-lg shadow-lg text-gray-800">
        <h2 class="text-xl font-semibold mb-3">Privacy Notice</h2>
        <p class="text-sm mb-2">
            James Polymers Manufacturing Corporation values your privacy. In compliance with the
            <strong>Data Privacy Act of 2012 (Republic Act No. 10173)</strong> and other relevant privacy laws, we are
            committed to protecting the personal information you share with us.
        </p>
        <p class="text-sm mb-2">
            This website may collect limited information such as cookies, browsing data, and usage statistics to help
            improve user experience, personalize content, and analyze website traffic.
        </p>
        <p class="text-sm mb-2">
            By clicking "I Agree", you consent to the collection and use of your data as outlined in our
            <a href="privacy-policy.php" class="text-blue-600 underline">Privacy Policy</a>.
        </p>
        <p class="text-sm mb-4">
            You have the right to access, correct, or withdraw your personal information at any time as provided by law.
        </p>
        <button id="agreeBtn"
            class="bg-blue-600 text-white text-sm px-5 py-2 rounded-full hover:bg-blue-700 transition">
            I Agree
        </button>
    </div>
</div>


<!-- Reset Privacy Button -->
<!-- <button id="reset-privacy-btn">Reset Privacy Popup</button> -->

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const preloader = document.getElementById('preloader');
        const video = preloader.querySelector('video');
        const skipButton = document.getElementById('skip-button');
        const modalBackdrop = document.getElementById('privacy-modal-backdrop');
        const agreeBtn = document.getElementById('agreeBtn');
        const resetBtn = document.getElementById('reset-privacy-btn');

        let preloaderFinished = false;

        function showPrivacyModal() {
            if (!localStorage.getItem('privacyAccepted')) {
                modalBackdrop.classList.add('show');
            }
        }

        function hidePreloader() {
            if (preloaderFinished) return;
            preloaderFinished = true;

            document.body.classList.remove('loading');
            document.body.classList.add('loaded');

            setTimeout(showPrivacyModal, 500);
        }

        video.addEventListener('ended', hidePreloader);

        skipButton.addEventListener('click', () => {
            video.pause();
            hidePreloader();
        });

        setTimeout(() => {
            if (!preloaderFinished) {
                hidePreloader();
            }
        }, 7000);

        video.addEventListener('error', () => {
            console.warn('Video failed to load.');
            hidePreloader();
        });

        agreeBtn.addEventListener('click', () => {
            localStorage.setItem('privacyAccepted', 'true');
            modalBackdrop.classList.remove('show');
        });

        resetBtn.addEventListener('click', () => {
            localStorage.removeItem('privacyAccepted');
            location.reload();
        });
    });
</script>
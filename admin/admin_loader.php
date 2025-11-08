<div id="preloader">
    <video src="../assets/video/logo_animation.mp4" autoplay muted playsinline></video>
    <button id="skip-button">Skip</button>
</div>

<style>
    body.loading {
        overflow: hidden;
    }

    #preloader {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: #ffffff;
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        opacity: 1;
        visibility: visible;
        pointer-events: all;
        transition: opacity 0.5s ease, visibility 0.5s ease;
        overflow: hidden;
    }

    #preloader video {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    #skip-button {
        position: absolute;
        bottom: 20px;
        right: 20px;
        background-color: rgba(0, 0, 0, 0.6);
        color: #fff;
        border: none;
        padding: 10px 16px;
        border-radius: 5px;
        cursor: pointer;
        z-index: 10000;
    }

    #skip-button:hover {
        background-color: rgba(0, 0, 0, 0.8);
    }

    body.loaded #preloader {
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
    }

    /* Modal Backdrop */
    #privacy-modal-backdrop {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 10000;
        justify-content: center;
        align-items: center;
    }

    #privacy-modal-backdrop.show {
        display: flex;
    }

    /* Modal Box */
    #privacy-popup {
        background-color: #fff;
        padding: 20px 30px;
        border-radius: 8px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        max-width: 500px;
        width: 90%;
        text-align: center;
        animation: fadeIn 0.3s ease;
    }

    #privacy-popup p {
        margin-bottom: 16px;
        color: #333;
        font-size: 14px;
    }

    #agreeBtn {
        background-color: #2563eb;
        color: white;
        padding: 10px 20px;
        border-radius: 6px;
        border: none;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    #agreeBtn:hover {
        background-color: #1d4ed8;
    }

    /* Simple fade in for modal */
    @keyframes fadeIn {
        from { opacity: 0; transform: scale(0.95); }
        to { opacity: 1; transform: scale(1); }
    }

    /* Reset Button Styles */
    #reset-privacy-btn {
        position: fixed;
        bottom: 20px;
        left: 20px;
        background-color: #e3342f;
        color: #fff;
        padding: 8px 12px;
        border-radius: 5px;
        font-size: 12px;
        cursor: pointer;
        z-index: 10001;
        box-shadow: 0 2px 6px rgba(0,0,0,0.2);
    }

    #reset-privacy-btn:hover {
        background-color: #cc1f1a;
    }
</style>

<!-- Privacy Modal Backdrop and Popup -->
<!-- Privacy Modal Backdrop and Popup -->



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

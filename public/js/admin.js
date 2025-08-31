document.addEventListener('DOMContentLoaded', function() {
    const showBannerCheckbox = document.getElementById('home_show_banner');
    const bannerCardBody = showBannerCheckbox.closest('.card').querySelector('.card-body');

    function toggleBannerFields() {
        if (showBannerCheckbox.checked) {
            bannerCardBody.style.display = 'block';
        } else {
            bannerCardBody.style.display = 'none';
        }
    }

    // Initial state
    toggleBannerFields();

    // Listen for changes
    showBannerCheckbox.addEventListener('change', toggleBannerFields);
});

// @dev GustavoChaconDeveloper - github.com/GustavoChaconDeveloper
// ===== APPLICATION PROCESS PAGE JAVASCRIPT =====

document.addEventListener('DOMContentLoaded', function () {

    // ── Mobile: empurra header abaixo do banner fixo ────────────────
    function adjustMobileLayout() {
        var banner = document.querySelector('.top-banner');
        var header = document.querySelector('.header');

        if (!banner || !header) return;

        if (window.innerWidth <= 992) {
            var bannerHeight = banner.offsetHeight;
            header.style.top = bannerHeight + 'px';
            document.body.style.paddingTop = (bannerHeight + header.offsetHeight) + 'px';
        } else {
            header.style.top = '';
            document.body.style.paddingTop = '';
        }
    }

    adjustMobileLayout();
    window.addEventListener('resize', adjustMobileLayout);

});

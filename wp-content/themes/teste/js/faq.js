// @dev GustavoChaconDeveloper - github.com/GustavoChaconDeveloper
// ===== FAQ PAGE JAVASCRIPT =====

document.addEventListener('DOMContentLoaded', function () {

    // ── Accordion ──────────────────────────────────────────────────
    const faqQuestions = document.querySelectorAll('.faq-question');

    faqQuestions.forEach(function (question) {
        question.addEventListener('click', function (e) {
            e.preventDefault();

            const faqItem = this.closest('.faq-item');
            const isActive = faqItem.classList.contains('active');

            document.querySelectorAll('.faq-item').forEach(function (item) {
                item.classList.remove('active');
            });

            if (!isActive) {
                faqItem.classList.add('active');
            }
        });
    });

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


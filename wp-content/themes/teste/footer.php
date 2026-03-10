<?php get_template_part('questionary-widget'); ?>
    <!-- Footer -->
    <footer class="footer">
        <div class="container-fluid">
            <div class="footer-content">
                <!-- Footer Coluna 1 - Editável via Widgets -->
                <div class="footer-column">
                    <?php if (is_active_sidebar('footer-1')) : ?>
                        <?php dynamic_sidebar('footer-1'); ?>
                    <?php else : ?>
                        <!-- Conteúdo padrão se não houver widgets -->
                        <div class="footer-logo">
                            <img src="<?php echo esc_url(get_template_directory_uri() . '/logopoker.png'); ?>" alt="POKER III" class="logo-image">
                        </div>
                        <div class="social-icons">
                            <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                            <a href="#" class="social-link"><i class="fab fa-discord"></i></a>
                        </div>
                        <div class="subscribe-box">
                            <input type="email" id="footerNewsletterEmail" placeholder="Subscribe" class="subscribe-input">
                            <button id="footerNewsletterBtn" class="subscribe-btn"><i class="far fa-envelope"></i></button>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Footer Coluna 2 - Editável via Widgets -->
                <div class="footer-column">
                    <?php if (is_active_sidebar('footer-2')) : ?>
                        <?php dynamic_sidebar('footer-2'); ?>
                    <?php else : ?>
                        <h3 class="footer-title">Membership</h3>
                        <ul class="footer-links">
                            <li><a href="<?php echo esc_url(site_url('/about/')); ?>">How it works</a></li>
                            <li><a href="<?php echo esc_url(site_url('/plans/')); ?>">Membership plans</a></li>
                            <li><a href="<?php echo esc_url(site_url('/sponsorchip/')); ?>">Sponsorship</a></li>
                        </ul>
                    <?php endif; ?>
                </div>

                <!-- Footer Coluna 3 - Editável via Widgets -->
                <div class="footer-column">
                    <?php if (is_active_sidebar('footer-3')) : ?>
                        <?php dynamic_sidebar('footer-3'); ?>
                    <?php else : ?>
                        <h3 class="footer-title">Resources</h3>
                        <ul class="footer-links">
                            <li><a href="#" target="_blank" rel="noopener noreferrer">Discord community</a></li>
                            <li><a href="<?php echo esc_url(site_url('/contact/')); ?>">Contact support</a></li>
                            <li><a href="<?php echo esc_url(site_url('/challenges/')); ?>">Challenges</a></li>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>

            <div class="footer-bottom">
                <?php if (is_active_sidebar('footer-copyright')) : ?>
                    <?php dynamic_sidebar('footer-copyright'); ?>
                <?php else : ?>
                    <p class="copyright">
                        <?php echo esc_html(
                            get_theme_mod(
                                'poker111_copyright_text',
                                'POKER III @ 2025. All rights reserved.'
                            )
                        ); ?>
                    </p>
                    <div class="footer-legal">
                        <a href="#">Privacy & Policy</a>
                        <a href="#">Terms & Condition</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </footer>

    <?php wp_footer(); ?>
</body>
</html>

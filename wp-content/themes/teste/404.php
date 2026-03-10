<?php
/**
 * Template Name: 404
 * Description: Página de erro
 */

get_header();
?>

<!-- Main Content -->
<main class="main-content">
    <div class="container">
        <div class="error-404-section">
            <h1 class="error-404-title">404</h1>
            <div class="error-404-badge">Page Not Found</div>
            <p class="error-404-text">
                Looks like this page doesn't exist.
            </p>
            <p class="error-404-subtext">
                But there's plenty to explore on our homepage or in your account.
            </p>
            <div class="error-404-buttons">
                <a href="<?php echo esc_url(home_url('/')); ?>" class="btn-404-home">Home</a>
                <a href="#" class="btn-404-account" onclick="openLoginModal(event)">My Account</a>
            </div>
        </div>
    </div>
</main>

<?php get_footer(); ?>

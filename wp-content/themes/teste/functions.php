<?php
/**
 * Poker111 Theme - Functions
 *
 * @package Poker111
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

# ==========================================================
# CONFIGURAÇÕES PRINCIPAIS DO TEMA
# ==========================================================

function poker111_setup() {

    // Suporte a título dinâmico
    add_theme_support('title-tag');

    // Suporte a imagens destacadas
    add_theme_support('post-thumbnails');

    // Suporte a HTML5
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ));

    // Suporte ao Elementor
    add_theme_support('elementor');

    // Menu principal do header
    register_nav_menus(array(
        'header-primary' => __('Menu Principal do Header', 'poker111'),
    ));

}
add_action('after_setup_theme', 'poker111_setup');


# ==========================================================
# REGISTRAR WIDGETS DO FOOTER
# ==========================================================

function poker111_widgets_init() {

    register_sidebar(array(
        'name'          => 'Footer - Coluna 1 (Logo e Social)',
        'id'            => 'footer-1',
        'description'   => 'Logo, redes sociais e newsletter',
        'before_widget' => '<div class="footer-widget">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="footer-title">',
        'after_title'   => '</h3>',
    ));

    register_sidebar(array(
        'name'          => 'Footer - Coluna 2 (Membership)',
        'id'            => 'footer-2',
        'description'   => 'Links de membership',
        'before_widget' => '<div class="footer-widget">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="footer-title">',
        'after_title'   => '</h3>',
    ));

    register_sidebar(array(
        'name'          => 'Footer - Coluna 3 (Resources)',
        'id'            => 'footer-3',
        'description'   => 'Links de recursos',
        'before_widget' => '<div class="footer-widget">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="footer-title">',
        'after_title'   => '</h3>',
    ));

    register_sidebar(array(
        'name'          => 'Footer - Copyright',
        'id'            => 'footer-copyright',
        'description'   => 'Texto inferior do rodapé',
        'before_widget' => '<div class="footer-widget">',
        'after_widget'  => '</div>',
    ));

}
add_action('widgets_init', 'poker111_widgets_init');



# ==========================================================
# REGISTRAR STYLE.CSS SEM CARREGAR AUTOMATICAMENTE
# ==========================================================

function poker111_register_styles() {

    wp_register_style(
        'poker111-style',
        get_stylesheet_uri(),
        array(),
        '1.0.0'
    );

}
add_action('wp_enqueue_scripts', 'poker111_register_styles', 1);


# ==========================================================
# ENFILEIRAR CSS E JS CORRETAMENTE
# ==========================================================

function poker111_enqueue_scripts() {

    # ===============================
    # FONTES E BIBLIOTECAS GLOBAIS
    # ===============================

    // Google Fonts
    wp_enqueue_style(
        'poker111-google-fonts',
        'https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&display=swap',
        array(),
        null
    );

    // Font Awesome
    wp_enqueue_style(
        'poker111-fontawesome',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
        array(),
        '6.4.0'
    );

    wp_enqueue_script(
        'poker111-navigation-js',
        get_template_directory_uri() . '/js/navigation.js',
        array('jquery'),
        '1.0.0',
        true
    );
    
        // Newsletter Footer (carrega em todas as páginas)
    wp_enqueue_script(
        'poker111-newsletter-js',
        get_template_directory_uri() . '/js/newsletter.js',
        array(),
        '1.0.0',
        true
    );


    # ===============================
    # STYLE PRINCIPAL (exceto Contact)
    # ===============================

    # STYLE PRINCIPAL + HEADER (exceto Contact e Challenges)
# STYLE PRINCIPAL + HEADER (lógica separada)
if (
    !is_page('contact') &&
    !is_page('contato') &&
    !is_page(array('planos', 'planos2', 'planos3'))
) {

    // STYLE.CSS (GLOBAL)
    wp_enqueue_style('poker111-style');

    // HEADER (exceto Challenges e Planos)
    if (
        !is_page('challenges') &&
        !is_page('torneios') &&
        !is_page('home')
    ) {
        wp_enqueue_style(
            'poker111-header-navigation',
            get_template_directory_uri() . '/css/header-navigation.css',
            array(),
            '1.0.0'
        );
    }
}





    # ===============================
    # HOME
    # ===============================

    if (is_front_page() || is_home()) {

    wp_enqueue_style(
        'poker111-home',
        get_template_directory_uri() . '/css/home.css',
        array(),
        '1.0.0'
    );

    wp_enqueue_style(
        'poker111-home2',
        get_template_directory_uri() . '/css/home2.css',
        array(),
        '1.0.0'
    );

    wp_enqueue_style(
        'poker111-modal-alerts',
        get_template_directory_uri() . '/css/modal-alerts.css',
        array(),
        '1.0.0'
    );

    wp_enqueue_style(
        'poker111-modal-login',
        get_template_directory_uri() . '/css/modal-de-login.css',
        array(),
        '1.0.0'
    );

    wp_enqueue_style(
        'poker111-modal-change-password',
        get_template_directory_uri() . '/css/modal-change-password.css',
        array(),
        '1.0.0'
    );

    wp_enqueue_script(
        'poker111-index-js',
        get_template_directory_uri() . '/js/index.js',
        array('jquery'),
        '1.0.0',
        true
    );

    wp_enqueue_script(
        'poker111-login-js',
        get_template_directory_uri() . '/js/login.js',
        array('jquery'),
        '1.0.0',
        true
    );
    
    wp_enqueue_script(
        'poker111-home-js',
        get_template_directory_uri() . '/js/home.js',
        array('jquery'),
        '1.0.0',
        true
    );
}

 # ===============================
    # 404
 # ===============================
if (is_404()) {

    // CSS base (se quiser reaproveitar fontes/cores)
    wp_enqueue_style('poker111-style');

    // Modais (caso tenha login no 404)
    wp_enqueue_style(
        'poker111-styles-paginas',
        get_template_directory_uri() . '/css/styles-paginas.css',
        array(),
        '1.0.0'
    );
    
    wp_enqueue_style(
        'poker111-modal-alerts',
        get_template_directory_uri() . '/css/modal-alerts.css',
        array(),
        '1.0.0'
    );

    wp_enqueue_style(
        'poker111-modal-change-password',
        get_template_directory_uri() . '/css/modal-change-password.css',
        array(),
        '1.0.0'
    );

    wp_enqueue_style(
        'poker111-modal-login',
        get_template_directory_uri() . '/css/modal-de-login.css',
        array(),
        '1.0.0'
    );

    // JS do 404
    wp_enqueue_script(
        'poker111-404-js',
        get_template_directory_uri() . '/js/index.js',
        array('jquery'),
        '1.0.0',
        true
    );

    // JS de login (se tiver botão de login)
    wp_enqueue_script(
        'poker111-login-js',
        get_template_directory_uri() . '/js/login.js',
        array('jquery'),
        '1.0.0',
        true
    );
}



# ===============================
# CONTACT (SEM STYLE.CSS)
# ===============================

if (is_page('contact') || is_page('contato')) {

    wp_enqueue_style(
        'poker111-contact-css',
        get_template_directory_uri() . '/css/contact.css',
        array(),
        '1.0.0'
    );

    // 🔹 MODAIS (LOGIN + ALERTS)
    wp_enqueue_style(
        'poker111-modal-alerts',
        get_template_directory_uri() . '/css/modal-alerts.css',
        array(),
        '1.0.0'
    );

    wp_enqueue_style(
        'poker111-modal-login',
        get_template_directory_uri() . '/css/modal-de-login.css',
        array(),
        '1.0.0'
    );

    wp_enqueue_style(
        'poker111-menu',
        get_template_directory_uri() . '/css/menu.css',
        array(),
        '1.0.0'
    );

    wp_enqueue_style(
        'poker111-modal-change-password',
        get_template_directory_uri() . '/css/modal-change-password.css',
        array(),
        '1.0.0'
    );

    // Scripts
    wp_enqueue_script(
        'poker111-contact-js',
        get_template_directory_uri() . '/js/contact.js',
        array('jquery'),
        '1.0.0',
        true
    );

    // 🔹 LOGIN JS (substitui navigation.js)
    wp_enqueue_script(
        'poker111-login-js',
        get_template_directory_uri() . '/js/login.js',
        array('jquery'),
        '1.0.0',
        true
    );

    wp_enqueue_script(
        'poker111-index',
        get_template_directory_uri() . '/js/index.js',
        array(),
        '1.0.0',
        true
    );
}

# ===============================
# FAQ (SEM STYLE.CSS)
# ===============================

if (is_page('faq')) {

    wp_enqueue_style(
        'poker111-faq-css',
        get_template_directory_uri() . '/css/faq.css',
        array(),
        '1.0.0'
    );

    // 🔹 MODAIS (LOGIN + ALERTS)
    wp_enqueue_style(
        'poker111-modal-alerts',
        get_template_directory_uri() . '/css/modal-alerts.css',
        array(),
        '1.0.0'
    );

    wp_enqueue_style(
        'poker111-modal-login',
        get_template_directory_uri() . '/css/modal-de-login.css',
        array(),
        '1.0.0'
    );

    wp_enqueue_style(
        'poker111-menu',
        get_template_directory_uri() . '/css/menu.css',
        array(),
        '1.0.0'
    );

    wp_enqueue_style(
        'poker111-modal-change-password',
        get_template_directory_uri() . '/css/modal-change-password.css',
        array(),
        '1.0.0'
    );

    // Scripts
    wp_enqueue_script(
        'poker111-faq-js',
        get_template_directory_uri() . '/js/faq.js',
        array('jquery'),
        '1.0.0',
        true
    );

    wp_enqueue_script(
        'poker111-login-js',
        get_template_directory_uri() . '/js/login.js',
        array('jquery'),
        '1.0.0',
        true
    );

    wp_enqueue_script(
        'poker111-index',
        get_template_directory_uri() . '/js/index.js',
        array(),
        '1.0.0',
        true
    );
}


# ===============================
# MENTORSHIP
# ===============================

if (is_page('mentor-ship')) {

    wp_enqueue_style(
        'poker111-mentorship-css',
        get_template_directory_uri() . '/css/mentorship.css',
        array(),
        '1.0.0'
    );

    wp_enqueue_style(
        'poker111-modal-alerts',
        get_template_directory_uri() . '/css/modal-alerts.css',
        array(),
        '1.0.0'
    );

    wp_enqueue_style(
        'poker111-modal-login',
        get_template_directory_uri() . '/css/modal-de-login.css',
        array(),
        '1.0.0'
    );

    wp_enqueue_style(
        'poker111-menu',
        get_template_directory_uri() . '/css/menu.css',
        array(),
        '1.0.0'
    );

    wp_enqueue_style(
        'poker111-modal-change-password',
        get_template_directory_uri() . '/css/modal-change-password.css',
        array(),
        '1.0.0'
    );

    wp_enqueue_script(
        'poker111-login-js',
        get_template_directory_uri() . '/js/login.js',
        array('jquery'),
        '1.0.0',
        true
    );

    wp_enqueue_script(
        'poker111-index',
        get_template_directory_uri() . '/js/index.js',
        array(),
        '1.0.0',
        true
    );
}

# ===============================
# TRAINING
# ===============================

if (is_page('training')) {

    wp_enqueue_style(
        'poker111-training-css',
        get_template_directory_uri() . '/css/training.css',
        array(),
        '1.0.0'
    );

    wp_enqueue_style(
        'poker111-modal-alerts',
        get_template_directory_uri() . '/css/modal-alerts.css',
        array(),
        '1.0.0'
    );

    wp_enqueue_style(
        'poker111-modal-login',
        get_template_directory_uri() . '/css/modal-de-login.css',
        array(),
        '1.0.0'
    );

    wp_enqueue_style(
        'poker111-menu',
        get_template_directory_uri() . '/css/menu.css',
        array(),
        '1.0.0'
    );

    wp_enqueue_style(
        'poker111-modal-change-password',
        get_template_directory_uri() . '/css/modal-change-password.css',
        array(),
        '1.0.0'
    );

    wp_enqueue_script(
        'poker111-login-js',
        get_template_directory_uri() . '/js/login.js',
        array('jquery'),
        '1.0.0',
        true
    );

    wp_enqueue_script(
        'poker111-index',
        get_template_directory_uri() . '/js/index.js',
        array(),
        '1.0.0',
        true
    );
}

# ===============================
# BLOG
# ===============================

if (is_page('blog')) {

    wp_enqueue_style(
        'poker111-blog-css',
        get_template_directory_uri() . '/css/blog.css',
        array(),
        '1.0.0'
    );

    wp_enqueue_style(
        'poker111-modal-alerts',
        get_template_directory_uri() . '/css/modal-alerts.css',
        array(),
        '1.0.0'
    );

    wp_enqueue_style(
        'poker111-modal-login',
        get_template_directory_uri() . '/css/modal-de-login.css',
        array(),
        '1.0.0'
    );

    wp_enqueue_style(
        'poker111-menu',
        get_template_directory_uri() . '/css/menu.css',
        array(),
        '1.0.0'
    );

    wp_enqueue_style(
        'poker111-modal-change-password',
        get_template_directory_uri() . '/css/modal-change-password.css',
        array(),
        '1.0.0'
    );

    wp_enqueue_script(
        'poker111-login-js',
        get_template_directory_uri() . '/js/login.js',
        array('jquery'),
        '1.0.0',
        true
    );
    
    wp_enqueue_script(
        'poker111-blog-js',
        get_template_directory_uri() . '/js/blog.js',
        array('jquery'),
        '1.0.0',
        true
    );

    wp_enqueue_script(
        'poker111-index',
        get_template_directory_uri() . '/js/index.js',
        array(),
        '1.0.0',
        true
    );
}

# ===============================
# ACADEMY
# ===============================

if (is_page('academy')) {

    wp_enqueue_style(
        'poker111-academy-css',
        get_template_directory_uri() . '/css/academy.css',
        array(),
        '1.0.0'
    );

    wp_enqueue_style(
        'poker111-modal-alerts',
        get_template_directory_uri() . '/css/modal-alerts.css',
        array(),
        '1.0.0'
    );

    wp_enqueue_style(
        'poker111-modal-login',
        get_template_directory_uri() . '/css/modal-de-login.css',
        array(),
        '1.0.0'
    );

    wp_enqueue_style(
        'poker111-menu',
        get_template_directory_uri() . '/css/menu.css',
        array(),
        '1.0.0'
    );

    wp_enqueue_style(
        'poker111-modal-change-password',
        get_template_directory_uri() . '/css/modal-change-password.css',
        array(),
        '1.0.0'
    );

    wp_enqueue_script(
        'poker111-login-js',
        get_template_directory_uri() . '/js/login.js',
        array('jquery'),
        '1.0.0',
        true
    );

    wp_enqueue_script(
        'poker111-academy-js',
        get_template_directory_uri() . '/js/academy.js',
        array(),
        '1.0.0',
        true
    );

    wp_enqueue_script(
        'poker111-index',
        get_template_directory_uri() . '/js/index.js',
        array(),
        '1.0.0',
        true
    );
}

# ===============================
# BLOG POST
# ===============================

if (is_page('blog-post')) {

    wp_enqueue_style(
        'poker111-blog-post-css',
        get_template_directory_uri() . '/css/blog-post.css',
        array(),
        '1.0.0'
    );

    wp_enqueue_style(
        'poker111-modal-alerts',
        get_template_directory_uri() . '/css/modal-alerts.css',
        array(),
        '1.0.0'
    );

    wp_enqueue_style(
        'poker111-modal-login',
        get_template_directory_uri() . '/css/modal-de-login.css',
        array(),
        '1.0.0'
    );

    wp_enqueue_style(
        'poker111-menu',
        get_template_directory_uri() . '/css/menu.css',
        array(),
        '1.0.0'
    );

    wp_enqueue_style(
        'poker111-modal-change-password',
        get_template_directory_uri() . '/css/modal-change-password.css',
        array(),
        '1.0.0'
    );

    wp_enqueue_script(
        'poker111-login-js',
        get_template_directory_uri() . '/js/login.js',
        array('jquery'),
        '1.0.0',
        true
    );

    wp_enqueue_script(
        'poker111-index',
        get_template_directory_uri() . '/js/index.js',
        array(),
        '1.0.0',
        true
    );
}

# ===============================
# APPLICATION PROCESS (SEM STYLE.CSS)
# ===============================

if (is_page('application-process')) {

    wp_enqueue_style(
        'poker111-application-process-css',
        get_template_directory_uri() . '/css/application-process.css',
        array(),
        '1.0.0'
    );

    wp_enqueue_style(
        'poker111-modal-alerts',
        get_template_directory_uri() . '/css/modal-alerts.css',
        array(),
        '1.0.0'
    );

    wp_enqueue_style(
        'poker111-modal-login',
        get_template_directory_uri() . '/css/modal-de-login.css',
        array(),
        '1.0.0'
    );

    wp_enqueue_style(
        'poker111-menu',
        get_template_directory_uri() . '/css/menu.css',
        array(),
        '1.0.0'
    );

    wp_enqueue_style(
        'poker111-modal-change-password',
        get_template_directory_uri() . '/css/modal-change-password.css',
        array(),
        '1.0.0'
    );

    wp_enqueue_script(
        'poker111-application-process-js',
        get_template_directory_uri() . '/js/application-process.js',
        array(),
        '1.0.0',
        true
    );

    wp_enqueue_script(
        'poker111-login-js',
        get_template_directory_uri() . '/js/login.js',
        array('jquery'),
        '1.0.0',
        true
    );

    wp_enqueue_script(
        'poker111-index',
        get_template_directory_uri() . '/js/index.js',
        array(),
        '1.0.0',
        true
    );
}

if (is_page('sponsor-chip')) {

    wp_enqueue_style(
        'poker111-sponsorchip-css',
        get_template_directory_uri() . '/css/sponsorchip.css',
        array(),
        '1.0.0'
    );

    wp_enqueue_style(
        'poker111-modal-alerts',
        get_template_directory_uri() . '/css/modal-alerts.css',
        array(),
        '1.0.0'
    );

    wp_enqueue_style(
        'poker111-modal-login',
        get_template_directory_uri() . '/css/modal-de-login.css',
        array(),
        '1.0.0'
    );

    wp_enqueue_style(
        'poker111-menu',
        get_template_directory_uri() . '/css/menu.css',
        array(),
        '1.0.0'
    );

    wp_enqueue_style(
        'poker111-modal-change-password',
        get_template_directory_uri() . '/css/modal-change-password.css',
        array(),
        '1.0.0'
    );

    wp_enqueue_script(
        'poker111-sponsorchip-js',
        get_template_directory_uri() . '/js/sponsorchip.js',
        array(),
        '1.0.0',
        true
    );

    wp_enqueue_script(
        'poker111-login-js',
        get_template_directory_uri() . '/js/login.js',
        array('jquery'),
        '1.0.0',
        true
    );

    wp_enqueue_script(
        'poker111-index',
        get_template_directory_uri() . '/js/index.js',
        array(),
        '1.0.0',
        true
    );
}


if (is_page('scouting')) {

    wp_enqueue_style(
        'poker111-scouting-css',
        get_template_directory_uri() . '/css/scouting.css',
        array(),
        '1.0.0'
    );

    wp_enqueue_style(
        'poker111-modal-alerts',
        get_template_directory_uri() . '/css/modal-alerts.css',
        array(),
        '1.0.0'
    );

    wp_enqueue_style(
        'poker111-modal-login',
        get_template_directory_uri() . '/css/modal-de-login.css',
        array(),
        '1.0.0'
    );

    wp_enqueue_style(
        'poker111-menu',
        get_template_directory_uri() . '/css/menu.css',
        array(),
        '1.0.0'
    );

    wp_enqueue_style(
        'poker111-modal-change-password',
        get_template_directory_uri() . '/css/modal-change-password.css',
        array(),
        '1.0.0'
    );

    wp_enqueue_script(
        'poker111-login-js',
        get_template_directory_uri() . '/js/login.js',
        array('jquery'),
        '1.0.0',
        true
    );

    wp_enqueue_script(
        'poker111-index',
        get_template_directory_uri() . '/js/index.js',
        array(),
        '1.0.0',
        true
    );
}

# ==========================================================
# REMOVER STYLE.CSS E HEADER NA PÁGINA LIVE
# ==========================================================

add_action('wp_enqueue_scripts', function () {
    if (is_page('live')) {

        // Remove style.css
        wp_dequeue_style('poker111-style');
        wp_deregister_style('poker111-style');

        // Remove header-navigation.css
        wp_dequeue_style('poker111-header-navigation');
        wp_deregister_style('poker111-header-navigation');
    }
}, 999);


# ===============================
# LIVE (SEM STYLE.CSS)
# ===============================

if (is_page(array('live', 'live-2'))) {

    // CSS da Live (reaproveitando contact)
    wp_enqueue_style(
        'poker111-live-css',
        get_template_directory_uri() . '/css/contact.css',
        array(),
        '1.0.0'
    );

    // 🔹 MODAIS (LOGIN + ALERTS)
    wp_enqueue_style(
        'poker111-live-modal-alerts',
        get_template_directory_uri() . '/css/modal-alerts.css',
        array(),
        '1.0.0'
    );

    wp_enqueue_style(
        'poker111-live-modal-login',
        get_template_directory_uri() . '/css/modal-de-login.css',
        array(),
        '1.0.0'
    );

    wp_enqueue_style(
        'poker111-modal-change-password',
        get_template_directory_uri() . '/css/modal-change-password.css',
        array(),
        '1.0.0'
    );

    // Menu (se usar na live)
    wp_enqueue_style(
        'poker111-live-menu',
        get_template_directory_uri() . '/css/menu.css',
        array(),
        '1.0.0'
    );

    // JS necessários
    wp_enqueue_script(
        'poker111-live-js',
        get_template_directory_uri() . '/js/contact.js',
        array('jquery'),
        '1.0.0',
        true
    );

    wp_enqueue_script(
        'poker111-live-login-js',
        get_template_directory_uri() . '/js/login.js',
        array('jquery'),
        '1.0.0',
        true
    );

    wp_enqueue_script(
        'poker111-live-index-js',
        get_template_directory_uri() . '/js/index.js',
        array(),
        '1.0.0',
        true
    );
}

# ===============================
# ABOUT (SEM STYLE.CSS)
# ===============================

if (is_page(array('about', 'sobre'))) {

    // CSS da About
    wp_enqueue_style(
        'poker111-about-css',
        get_template_directory_uri() . '/css/about.css',
        array(),
        '1.0.0'
    );
    
        // CSS da About
    wp_enqueue_style(
        'poker111-about2-css',
        get_template_directory_uri() . '/css/about2.css',
        array(),
        '1.0.0'
    );

    // 🔹 MODAIS (LOGIN + ALERTS)
    wp_enqueue_style(
        'poker111-about-modal-alerts',
        get_template_directory_uri() . '/css/modal-alerts.css',
        array(),
        '1.0.0'
    );

    wp_enqueue_style(
        'poker111-about-modal-login',
        get_template_directory_uri() . '/css/modal-de-login.css',
        array(),
        '1.0.0'
    );

    wp_enqueue_style(
        'poker111-modal-change-password',
        get_template_directory_uri() . '/css/modal-change-password.css',
        array(),
        '1.0.0'
    );

    // JS da About
    wp_enqueue_script(
        'poker111-about-js',
        get_template_directory_uri() . '/js/home.js',
        array('jquery'),
        '1.0.0',
        true
    );

    wp_enqueue_script(
        'poker111-about-login-js',
        get_template_directory_uri() . '/js/login.js',
        array('jquery'),
        '1.0.0',
        true
    );

    wp_enqueue_script(
        'poker111-about-index-js',
        get_template_directory_uri() . '/js/index.js',
        array(),
        '1.0.0',
        true
    );
}



    
    if (is_page('challenges') || is_page('torneios')) {

    // Desafios CSS
    wp_enqueue_style(
        'poker111-desafios',
        get_template_directory_uri() . '/css/desafios.css',
        array(),
        '1.0.0'
    );

    // Affiliation CSS
    wp_enqueue_style(
        'poker111-affiliation',
        get_template_directory_uri() . '/css/affiliation.css',
        array(),
        '1.0.0'
    );

    // 🔹 MODAIS (LOGIN + ALERTS)
    wp_enqueue_style(
        'poker111-modal-alerts',
        get_template_directory_uri() . '/css/modal-alerts.css',
        array(),
        '1.0.0'
    );

    wp_enqueue_style(
        'poker111-modal-login',
        get_template_directory_uri() . '/css/modal-de-login.css',
        array(),
        '1.0.0'
    );

    wp_enqueue_style(
        'poker111-modal-change-password',
        get_template_directory_uri() . '/css/modal-change-password.css',
        array(),
        '1.0.0'
    );

    // 🔹 LOGIN JS (AGORA EM CHALLENGES)
    wp_enqueue_script(
        'poker111-login-js',
        get_template_directory_uri() . '/js/login.js',
        array('jquery'),
        '1.0.0',
        true
    );

    // Countdown JS
    wp_enqueue_script(
        'poker111-countdown-desafios',
        get_template_directory_uri() . '/js/countdown.js',
        array('jquery'),
        '1.0.0',
        true
    );

    // Desafios JS
    wp_enqueue_script(
        'poker111-desafios-page',
        get_template_directory_uri() . '/js/desafios.js',
        array('jquery'),
        '1.0.0',
        true
    );
}

    
    


    # ===============================
    # LANDING PAGE
    # ===============================

    if (is_page('landing-page')) {

        wp_enqueue_style(
            'poker111-landing-css',
            get_template_directory_uri() . '/css/home.css',
            array(),
            '1.0.0'
        );

        wp_enqueue_script(
            'poker111-home-js',
            get_template_directory_uri() . '/js/home.js',
            array('jquery'),
            '1.0.0',
            true
        );
    }


    # ===============================
    # DESAFIOS
    # ===============================

    if (is_page(array('desafios', 'desafios2', 'desafios3', 'desafios4', 'desafios5'))) {

        wp_enqueue_style(
            'poker111-desafios-css',
            get_template_directory_uri() . '/css/desafios.css',
            array(),
            '1.0.0'
        );

        wp_enqueue_script(
            'poker111-desafios-js',
            get_template_directory_uri() . '/js/desafios.js',
            array('jquery'),
            '1.0.0',
            true
        );
    }


    # ===============================
    # PLANOS
    # ===============================

    if (is_page(array('planos', 'planos2', 'planos3'))) {
        
        wp_enqueue_style(
            'poker111-style-plano-css',
            get_template_directory_uri() . '/css/styles-paginas.css',
            array(),
            '1.0.0'
        );

        wp_enqueue_style(
            'poker111-planos-css',
            get_template_directory_uri() . '/css/plano2.css',
            array(),
            '1.0.0'
        );
        wp_enqueue_style(
            'poker111-modal-css',
            get_template_directory_uri() . '/css/modal-alerts.css',
            array(),
            '1.0.0'
        );
        
        wp_enqueue_style(
            'poker111-modal-alert-css',
            get_template_directory_uri() . '/css/modal-de-login.css',
            array(),
            '1.0.0'
        );

    wp_enqueue_style(
        'poker111-modal-change-password',
        get_template_directory_uri() . '/css/modal-change-password.css',
        array(),
        '1.0.0'
    );

        wp_enqueue_script(
            'poker111-planos-js',
            get_template_directory_uri() . '/js/plano2.js',
            array('jquery'),
            '1.0.0',
            true
        );
        
        wp_enqueue_script(
            'poker111-planos-toggles-js',
            get_template_directory_uri() . '/js/planos-toggle.js',
            array('jquery'),
            '1.0.0',
            true
        );
        
        wp_enqueue_script(
            'poker111-login-js',
            get_template_directory_uri() . '/js/login.js',
            array('jquery'),
            '1.0.0',
            true
        );
        wp_enqueue_script(
            'poker111-index-js',
            get_template_directory_uri() . '/js/index.js',
            array('jquery'),
            '1.0.0',
            true
        );
    }

    # ===============================
    # UPGRADE
    # ===============================

    if (is_page('upgrade')) {
        
        wp_enqueue_style(
            'poker111-upgrade-css',
            get_template_directory_uri() . '/css/upgrade.css',
            array(),
            '1.0.0'
        );
        
        wp_enqueue_style(
            'poker111-menud-css',
            get_template_directory_uri() . '/css/menu.css',
            array(),
            '1.0.0'
        );
        
        wp_enqueue_style(
            'poker111-modal-alerts-upgrade-css',
            get_template_directory_uri() . '/css/modal-alerts.css',
            array(),
            '1.0.0'
        );

        wp_enqueue_style(
            'poker111-modal-login-upgrade-css',
            get_template_directory_uri() . '/css/modal-de-login.css',
            array(),
            '1.0.0'
        );
        
                wp_enqueue_script(
            'poker111-index-upgrade-js',
            get_template_directory_uri() . '/js/index.js',
            array('jquery'),
            '1.0.0',
            true
        );

        wp_enqueue_script(
            'poker111-upgrade-js',
            get_template_directory_uri() . '/js/upgrade.js',
            array('jquery'),
            '1.0.0',
            true
        );
        
        wp_enqueue_script(
            'poker111-login-upgrade-js',
            get_template_directory_uri() . '/js/login.js',
            array('jquery'),
            '1.0.0',
            true
        );
        
    }

    # ===============================
    # CHECKOUT
    # ===============================

    if (is_page('checkout')) {
        
        wp_enqueue_style(
            'poker111-modal-alerts-css',
            get_template_directory_uri() . '/css/modal-alerts.css',
            array(),
            '1.0.0'
        );

        wp_enqueue_style(
            'poker111-modal-login-css',
            get_template_directory_uri() . '/css/modal-de-login.css',
            array(),
            '1.0.0'
        );

    wp_enqueue_style(
        'poker111-modal-change-password',
        get_template_directory_uri() . '/css/modal-change-password.css',
        array(),
        '1.0.0'
    );

        wp_enqueue_style(
            'poker111-menu-css',
            get_template_directory_uri() . '/css/menu.css',
            array(),
            '1.0.0'
        );
    }


}
add_action('wp_enqueue_scripts', 'poker111_enqueue_scripts');


# ==========================================================
# PERMITIR SVG E WEBP
# ==========================================================

function poker111_mime_types($mimes) {
    $mimes['svg']  = 'image/svg+xml';
    $mimes['webp'] = 'image/webp';
    return $mimes;
}
add_filter('upload_mimes', 'poker111_mime_types');


# ==========================================================
# BODY CLASS CUSTOMIZADA
# ==========================================================

function poker111_body_classes($classes) {

    if (is_page_template('page-landing.php')) {
        $classes[] = 'landing-page';
    }

    return $classes;
}
add_filter('body_class', 'poker111_body_classes');


# ==========================================================
# CUSTOMIZER - COPYRIGHT
# ==========================================================

function poker111_customize_register($wp_customize) {

    $wp_customize->add_section('poker111_footer_section', array(
        'title'    => 'Configurações do Footer',
        'priority' => 30,
    ));

    $wp_customize->add_setting('poker111_copyright_text', array(
        'default' => 'POKER111 © 2025. All rights reserved.',
    ));

    $wp_customize->add_control('poker111_copyright_text', array(
        'label'   => 'Texto do Copyright',
        'section' => 'poker111_footer_section',
        'type'    => 'text',
    ));

}
add_action('customize_register', 'poker111_customize_register');


# ==========================================================
# HELPERS
# ==========================================================

function poker111_get_image_url($path) {
    return get_template_directory_uri() . '/imagens/' . $path;
}

function poker111_is_admin_preview() {
    return is_admin() || is_customize_preview();
}


# ==========================================================
# WALKER PERSONALIZADO DO MENU DO HEADER
# ==========================================================

if (!class_exists('Poker111_Header_Menu_Walker')) {
    class Poker111_Header_Menu_Walker extends Walker_Nav_Menu {

        private $current_dropdown_id = null;

        public function start_lvl(&$output, $depth = 0, $args = null) {
            $indent = str_repeat("\t", $depth);
            $is_root_dropdown = ($depth === 0);
            $class = $is_root_dropdown ? 'nav-dropdown-menu' : 'nav-dropdown-submenu';
            $id_attribute = '';

            if ($is_root_dropdown && $this->current_dropdown_id) {
                $id_attribute = ' id="' . esc_attr($this->current_dropdown_id) . '"';
            }

            $output .= "\n{$indent}<div class=\"{$class}\"{$id_attribute}>\n";
        }

        public function end_lvl(&$output, $depth = 0, $args = null) {
            $indent = str_repeat("\t", $depth);
            $output .= "{$indent}</div>\n";

            if ($depth === 0) {
                $this->current_dropdown_id = null;
            }
        }

        public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0) {
            $has_children = in_array('menu-item-has-children', $item->classes ?? array(), true);
            $title = apply_filters('the_title', $item->title, $item->ID);
            $url = !empty($item->url) ? $item->url : '#';
            $target = $item->target ? ' target="' . esc_attr($item->target) . '"' : '';
            $rel = $item->xfn ? ' rel="' . esc_attr($item->xfn) . '"' : '';

            if ($depth === 0 && $has_children) {
                $dropdown_id = 'navDropdown-' . $item->ID;
                $this->current_dropdown_id = $dropdown_id;

                $output .= '<div class="nav-dropdown">';
                $output .= sprintf(
                    '<a class="nav-link" href="%1$s"%2$s%3$s onclick="toggleNavDropdown(event, \'%4$s\')">%5$s <i class="fas fa-chevron-down"></i></a>',
                    esc_url($url),
                    $target,
                    $rel,
                    esc_attr($dropdown_id),
                    esc_html($title)
                );
            } elseif ($depth === 0) {
                $output .= '<a class="nav-link" href="' . esc_url($url) . '"' . $target . $rel . '>' . esc_html($title) . '</a>';
                $output .= "\n";
            } else {
                $output .= '<a class="nav-dropdown-item" href="' . esc_url($url) . '"' . $target . $rel . '>' . esc_html($title) . '</a>';
                $output .= "\n";
            }
        }

        public function end_el(&$output, $item, $depth = 0, $args = null) {
            $has_children = in_array('menu-item-has-children', $item->classes ?? array(), true);

            if ($depth === 0 && $has_children) {
                $output .= "</div>\n";
            }
        }
    }
}

function poker111_menu_fallback() {
    if (!current_user_can('edit_theme_options')) {
        return;
    }

    echo '<a class="nav-link" href="' . esc_url(admin_url('nav-menus.php')) . '">' . esc_html__('Configure o menu principal', 'poker111') . '</a>';
}

add_action('wp_enqueue_scripts', function () {

    if (is_404()) {
        wp_dequeue_style('poker111-style');
        wp_deregister_style('poker111-style');
    }

}, 999);

add_action('wp_enqueue_scripts', 'poker111_remover_style_css', 100);
function poker111_remover_style_css() {

    // Remove APENAS o style.css do tema
    wp_dequeue_style('poker111-style-css');
    wp_deregister_style('poker111-style-css');
}



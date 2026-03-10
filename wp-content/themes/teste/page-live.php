<?php
/**
 * Template Name: Live Poker
 * Description: Página de poker ao vivo com iframe da plataforma
 */

get_header();
?>

<style>
    /* Estilos específicos da página Live */
    .live-page {
        padding: 60px 0;
    }
    .live-section {
        display: flex;
        justify-content: center;
        align-items: center;
        width: 100%;
        min-height: 800px;
    }
    .live-iframe {
        width: 100%;
        height: 800px;
        border: none;
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    }
    
    /* Responsivo */
    @media (max-width: 768px) {
        .live-page {
            padding: 30px 0;
        }
        .live-section {
            min-height: 500px;
        }
        .live-iframe {
            height: 500px;
        }
    }
    
    @media (max-width: 480px) {
        .live-section {
            min-height: 400px;
        }
        .live-iframe {
            height: 400px;
            border-radius: 4px;
        }
    }
</style>

<!-- Main Content -->
<main class="main-content live-page">
    <div class="container">
        <!-- Live Poker Section -->
        <div class="live-section">
            <?php
            // URL do iframe pode ser configurada via Custom Fields ou ACF
            $iframe_url = get_post_meta(get_the_ID(), 'live_iframe_url', true);
            if (empty($iframe_url)) {
                $iframe_url = 'https://play.poker111.com/d/?sit-and-go';
            }
            ?>
            <iframe src="<?php echo esc_url($iframe_url); ?>" class="live-iframe" title="Poker Live Platform" allowfullscreen></iframe>
        </div>
    </div>
</main>

<!-- FAQ Section -->
<section class="faq-section">
    <div class="container">
        <h2 class="faq-title"><?php echo esc_html__('Frequently Asked Questions', 'poker-theme'); ?></h2>
        <div class="faq-list">
            <?php
            // FAQs podem ser customizadas via painel do WordPress
            $faqs = [
                [
                    'question' => __('Can I use the platform for training?', 'poker-theme'),
                    'answer' => __('Yes, our platform offers comprehensive training features to help you improve your poker skills.', 'poker-theme')
                ],
                [
                    'question' => __('Do training sessions cost credits or tickets?', 'poker-theme'),
                    'answer' => __('Training sessions are included with your membership and do not require additional credits or tickets.', 'poker-theme')
                ],
                [
                    'question' => __('Can I leave a challenge after joining?', 'poker-theme'),
                    'answer' => __('Once you\'ve joined a challenge, you commit to completing it. Please review the challenge rules before joining.', 'poker-theme')
                ],
                [
                    'question' => __('What happens if I lose connection mid-challenge?', 'poker-theme'),
                    'answer' => __('If you lose connection, the system will hold your position for a limited time allowing you to reconnect and continue.', 'poker-theme')
                ],
                [
                    'question' => __('Are training results private?', 'poker-theme'),
                    'answer' => __('Yes, all training results and statistics are completely private and only visible to you in your personal dashboard.', 'poker-theme')
                ]
            ];
            
            foreach ($faqs as $faq): ?>
                <div class="faq-item">
                    <button class="faq-question">
                        <span><?php echo esc_html($faq['question']); ?></span>
                        <i class="fas fa-plus"></i>
                    </button>
                    <div class="faq-answer">
                        <p><?php echo esc_html($faq['answer']); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php get_footer(); ?>

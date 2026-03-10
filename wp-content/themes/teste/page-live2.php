<?php
/**
 * Template Name: Live Poker 2
 * Description: Página de poker ao vivo com modal de derrota
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

    /* Loss Modal Styles */
    .loss-modal {
        display: none;
        position: fixed;
        z-index: 10000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        animation: fadeIn 0.3s ease-out;
    }

    .loss-modal.show {
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .loss-modal-content {
        width: 520px;
        max-width: 90%;
        max-height: 90vh;
        overflow-y: auto;
        padding: 30px 40px;
        border-radius: 8px;
        background: rgba(0, 17, 52, 0.55);
        backdrop-filter: blur(30px);
        -webkit-backdrop-filter: blur(30px);
        box-shadow: 0 0 40px rgba(0, 51, 153, 0.5), inset 0 0 0 1px rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.08);
        position: relative;
        animation: modalSlideIn 0.3s ease-out;
        text-align: center;
    }

    .loss-modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
    }

    .loss-modal-close {
        background: transparent;
        border: none;
        color: rgba(255, 255, 255, 0.6);
        font-size: 22px;
        cursor: pointer;
        transition: color 0.3s ease;
        padding: 0;
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .loss-modal-close:hover {
        color: #ffffff;
    }

    .loss-modal-logo {
        font-size: 26px;
        font-weight: 700;
        color: #ffffff;
        font-family: 'Montserrat', sans-serif;
        text-align: left;
    }

    .loss-modal-logo span {
        color: #ff3b3b;
    }

    .loss-quote-box {
        background: rgba(30, 58, 95, 0.6);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        padding: 25px 30px;
        margin-bottom: 30px;
    }

    .loss-quote {
        font-family: 'Montserrat', sans-serif;
        font-size: 17px;
        font-style: italic;
        color: rgba(255, 255, 255, 0.95);
        margin-bottom: 15px;
        line-height: 1.5;
        font-weight: 400;
    }

    .loss-author {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }

    .loss-author-icon {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        object-fit: cover;
    }

    .loss-author-name {
        font-family: 'Montserrat', sans-serif;
        font-size: 15px;
        font-weight: 500;
        color: rgba(255, 255, 255, 0.9);
    }

    .loss-badge {
        width: 70px;
        height: 70px;
        background: linear-gradient(135deg, #ff6b35, #ff8c42);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        font-size: 32px;
        color: #ffffff;
        box-shadow: 0 4px 20px rgba(255, 107, 53, 0.4);
    }

    .loss-title {
        font-family: 'Montserrat', sans-serif;
        font-size: 20px;
        font-weight: 600;
        color: #ffffff;
        margin-bottom: 12px;
    }

    .loss-message {
        font-family: 'Montserrat', sans-serif;
        font-size: 13px;
        color: rgba(255, 255, 255, 0.7);
        line-height: 1.6;
        margin-bottom: 18px;
    }

    .loss-tickets {
        font-family: 'Montserrat', sans-serif;
        font-size: 14px;
        color: #ffffff;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .loss-tickets-icon {
        width: 28px;
        height: 28px;
        object-fit: contain;
    }

    .loss-modal-actions {
        display: flex;
        gap: 12px;
        margin-top: 25px;
    }

    .btn-loss-close {
        flex: 1;
        background: transparent;
        border: 1px solid rgba(255, 255, 255, 0.3);
        color: #ffffff;
        padding: 12px 18px;
        border-radius: 30px;
        font-family: 'Montserrat', sans-serif;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-loss-close:hover {
        background: rgba(255, 255, 255, 0.05);
        border-color: rgba(255, 255, 255, 0.5);
    }

    .btn-loss-play {
        flex: 1;
        background: #1e3a5f;
        border: 1px solid rgba(255, 255, 255, 0.3);
        color: #ffffff;
        padding: 12px 18px;
        border-radius: 30px;
        font-family: 'Montserrat', sans-serif;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .btn-loss-play:hover {
        background: #25456f;
        border-color: rgba(255, 255, 255, 0.5);
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes modalSlideIn {
        from {
            opacity: 0;
            transform: translateY(-30px) scale(0.95);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
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
        .loss-modal-content {
            padding: 20px 25px;
        }
        .loss-quote {
            font-size: 15px;
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
        .loss-modal-actions {
            flex-direction: column;
        }
    }
</style>

<!-- Loss Modal -->
<div id="lossModal" class="loss-modal">
    <div class="loss-modal-content">
        <div class="loss-modal-header">
            <div class="loss-modal-logo">
                POKER<span>111</span>
            </div>
            <button class="loss-modal-close" onclick="closeLossModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="loss-quote-box">
            <p class="loss-quote"><?php echo esc_html__("The key to winning in poker is learning how to lose", 'poker-theme'); ?></p>

            <div class="loss-author">
                <img src="<?php echo get_template_directory_uri(); ?>/imagens/224fbd1ab6354bcb75cc42c3c2f6d21d7fb4462e.png" alt="Doyle Brunson" class="loss-author-icon">
                <span class="loss-author-name">Doyle Brunson</span>
            </div>
        </div>

        <h3 class="loss-title"><?php echo esc_html__('You lost, It happens.', 'poker-theme'); ?></h3>

        <p class="loss-message">
            <?php echo esc_html__('Losses happen. Momentum is a choice.', 'poker-theme'); ?><br>
            <?php echo esc_html__('Hit play and take it back.', 'poker-theme'); ?>
        </p>

        <div class="loss-tickets">
            <?php 
            $tickets_left = 4; // Pode ser dinâmico via banco de dados
            printf(esc_html__('You have %d', 'poker-theme'), $tickets_left);
            ?>
            <img src="<?php echo get_template_directory_uri(); ?>/imagens/ticket.png" alt="Ticket" class="loss-tickets-icon">
            <?php echo esc_html__('left', 'poker-theme'); ?>
        </div>

        <div class="loss-modal-actions">
            <button class="btn-loss-close" onclick="closeLossModal()"><?php echo esc_html__('Close', 'poker-theme'); ?></button>
            <button class="btn-loss-play" onclick="playAgain()">
                <?php echo esc_html__('Use 1', 'poker-theme'); ?>
                <img src="<?php echo get_template_directory_uri(); ?>/imagens/ticket.png" alt="Ticket" class="loss-tickets-icon" style="width: 24px; height: 24px;">
                <?php echo esc_html__('to play', 'poker-theme'); ?>
            </button>
        </div>
    </div>
</div>

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

<script>
// Abrir modal automaticamente ao carregar a página
window.addEventListener('load', function() {
    setTimeout(function() {
        document.getElementById('lossModal').classList.add('show');
    }, 500);
});

// Fechar modal
function closeLossModal() {
    document.getElementById('lossModal').classList.remove('show');
}

// Função para jogar novamente
function playAgain() {
    closeLossModal();
    // Aqui você pode adicionar a lógica para iniciar um novo jogo
    alert('Starting new game with 1 ticket!');
}

// Fechar modal ao clicar fora dele
document.getElementById('lossModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeLossModal();
    }
});
</script>

<?php get_footer(); ?>

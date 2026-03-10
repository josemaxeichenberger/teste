<?php
/**
 * Template Name: Sponsorship
 * Descrição: Página de Patrocínio - Poker111
 */

get_header();
?>

<!-- Seção Hero - fundo escuro -->
<section class="ap-section sp-hero-section">
    <div class="ap-container">

        <!-- Título e Subtítulo -->
        <div class="sp-hero">
            <h1 class="sp-title">
                <span class="sp-title-highlight">Focus on your game,</span> not finances.
            </h1>
            <div class="sp-perks">
                <span class="sp-perk">Your bankroll stays safe.</span>
                <i class="fas fa-trophy sp-perk-icon"></i>
                <span class="sp-perk">We fund your tournaments.</span>
                <i class="fas fa-sack-dollar sp-perk-icon"></i>
                <span class="sp-perk">You keep 50% of winnings.</span>
                <i class="fas fa-face-smile sp-perk-icon"></i>
                <span class="sp-perk">Zero risk.</span>
            </div>
        </div>

    </div>
</section>

<!-- Seção Comparação - fundo branco separado -->
<section class="sp-compare-section">
    <div class="sp-compare-inner">

        <!-- Comparação: Old Way vs Poker111 Way -->
        <div class="sp-compare">

            <!-- Card Esquerdo: The Old Way -->
            <div class="sp-compare-card sp-card-old">
                <p class="sp-card-label sp-label-old">THE OLD WAY</p>
                <h3 class="sp-card-title">Playing Alone</h3>
                <ul class="sp-card-list">
                    <li class="sp-list-item sp-item-bad"><i class="fas fa-times"></i> Limited tournament access</li>
                    <li class="sp-list-item sp-item-bad"><i class="fas fa-times"></i> No support or coaching</li>
                    <li class="sp-list-item sp-item-bad"><i class="fas fa-times"></i> Pay from your pocket</li>
                    <li class="sp-list-item sp-item-bad"><i class="fas fa-times"></i> Risk your bankroll</li>
                </ul>
                <div class="sp-card-chips sp-chips-left">
                    <img src="<?php echo esc_url(get_template_directory_uri() . '/imagens/sponsorpoker3.png'); ?>" alt="chips" class="sp-chips-img">
                </div>
            </div>

            <!-- Seta Central -->
            <div class="sp-compare-arrow">
                <i class="fas fa-arrow-right"></i>
            </div>

            <!-- Card Direito: The Poker111 Way -->
            <div class="sp-compare-card sp-card-new">
                <p class="sp-card-label sp-label-new">THE POKER111 WAY</p>
                <h3 class="sp-card-title sp-card-title-new">Backed &amp; Supported</h3>
                <ul class="sp-card-list">
                    <li class="sp-list-item sp-item-good"><i class="fas fa-check"></i> Elite coaching &amp; community</li>
                    <li class="sp-list-item sp-item-good"><i class="fas fa-check"></i> Global tournament access</li>
                    <li class="sp-list-item sp-item-good"><i class="fas fa-check"></i> We cover all costs</li>
                    <li class="sp-list-item sp-item-good"><i class="fas fa-check"></i> Zero financial risk</li>
                </ul>
                <div class="sp-card-chips sp-chips-right">
                    <img src="<?php echo esc_url(get_template_directory_uri() . '/imagens/sponsorpoker1.png'); ?>" alt="chips" class="sp-chips-img">
                </div>
            </div>

        </div>

    </div>
</section>

<!-- Seção How Sponsorship Works - fundo escuro -->
<section class="ap-section sp-cta-section">
    <div class="ap-container">

        <!-- Título da seção -->
        <div class="sp-how-heading">
            <h2 class="sp-how-title">How Sponsorship Works</h2>
            <p class="sp-how-subtitle">Four simple steps from club member to sponsored professional</p>
        </div>

        <!-- Passos -->
        <div class="sp-how-steps">

            <!-- Passo 1 -->
            <div class="sp-how-step">
                <div class="sp-how-chip">
                    <img src="<?php echo esc_url(get_template_directory_uri() . '/imagens/moedasazuis.png'); ?>" alt="Step 1">
                    <span class="sp-step-number">1</span>
                </div>
                <h3 class="sp-how-step-title">Join Club</h3>
                <p class="sp-how-step-desc">Choose your membership tier starting at $9.99/month</p>
                <a href="<?php echo esc_url(site_url('/planos/')); ?>" class="sp-how-link">View Pricing +</a>
            </div>

            <!-- Passo 2 -->
            <div class="sp-how-step">
                <div class="sp-how-chip">
                    <img src="<?php echo esc_url(get_template_directory_uri() . '/imagens/moedasazuis.png'); ?>" alt="Step 2">
                    <span class="sp-step-number">2</span>
                </div>
                <h3 class="sp-how-step-title">Play Challenges</h3>
                <p class="sp-how-step-desc">Compete in weekly challenges to prove your skills</p>
                <a href="<?php echo esc_url(site_url('/desafios/')); ?>" class="sp-how-link">See Challenges +</a>
            </div>

            <!-- Passo 3 -->
            <div class="sp-how-step">
                <div class="sp-how-chip">
                    <img src="<?php echo esc_url(get_template_directory_uri() . '/imagens/moedasazuis.png'); ?>" alt="Step 3">
                    <span class="sp-step-number">3</span>
                </div>
                <h3 class="sp-how-step-title">Win Challenge</h3>
                <p class="sp-how-step-desc">Victory earns you a spot on our sponsored team</p>
                <a href="#" class="sp-how-link">Learn More +</a>
            </div>

            <!-- Passo 4 -->
            <div class="sp-how-step">
                <div class="sp-how-chip">
                    <img src="<?php echo esc_url(get_template_directory_uri() . '/imagens/moedasazuis.png'); ?>" alt="Step 4">
                    <span class="sp-step-number">4</span>
                </div>
                <h3 class="sp-how-step-title">Get Sponsored</h3>
                <p class="sp-how-step-desc">50/50 profit split, zero risk, full support</p>
                <a href="#" class="sp-how-link">Read Contract +</a>
            </div>

        </div>

    </div>
</section>

<!-- Seção The Deal - Simple Math -->
<section class="sp-deal-section">
    <div class="ap-container">
        
        <!-- Título da seção -->
        <div class="sp-deal-heading">
            <h2 class="sp-deal-title">The Deal - Simple Math</h2>
            <p class="sp-deal-subtitle">This is how it works for our sponsored players</p>
        </div>

        <!-- Tabela -->
        <div class="sp-deal-table-wrapper">
            <table class="sp-deal-table">
                <thead>
                    <tr>
                        <th class="sp-table-header sp-header-scenario">Scenario</th>
                        <th class="sp-table-header sp-header-win">You Win</th>
                        <th class="sp-table-header sp-header-lose">You Lose</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="sp-table-cell sp-cell-label">Tournament Buy-in (covered by us)</td>
                        <td class="sp-table-cell sp-cell-value">$1,000</td>
                        <td class="sp-table-cell sp-cell-value">$1,000</td>
                    </tr>
                    <tr>
                        <td class="sp-table-cell sp-cell-label">Your Risk</td>
                        <td class="sp-table-cell sp-cell-value">$0</td>
                        <td class="sp-table-cell sp-cell-value">$0</td>
                    </tr>
                    <tr>
                        <td class="sp-table-cell sp-cell-label">Tournament Winnings</td>
                        <td class="sp-table-cell sp-cell-value sp-value-highlight">$20,000</td>
                        <td class="sp-table-cell sp-cell-value">$0</td>
                    </tr>
                    <tr class="sp-table-row-final">
                        <td class="sp-table-cell sp-cell-label sp-label-bold">Your Share (50%)</td>
                        <td class="sp-table-cell sp-cell-value sp-value-final">$9,500</td>
                        <td class="sp-table-cell sp-cell-value sp-value-final">$0</td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>
</section>

<!-- Seção Sponsorship Benefits -->
<section class="sp-benefits-section">
    <div class="ap-container">
        
        <!-- Título da seção -->
        <div class="sp-benefits-heading">
            <h2 class="sp-benefits-title">Sponsorship Benefits</h2>
            <p class="sp-benefits-subtitle">Everything you need to succeed as a professional poker player</p>
        </div>

        <!-- Grid de Benefícios -->
        <div class="sp-benefits-grid">
            
            <!-- Benefício 1: ZERO FINANCIAL RISK -->
            <div class="sp-benefit-card">
                <div class="sp-benefit-icon">
                    <img src="<?php echo get_template_directory_uri(); ?>/imagens/cadeado.png" alt="Zero Financial Risk" class="benefit-icon-img">
                </div>
                <h3 class="sp-benefit-title">ZERO FINANCIAL RISK</h3>
                <p class="sp-benefit-desc">We cover 100% of tournament buy-ins, travel, and accommodation. Your bankroll stays safe while you compete.</p>
            </div>

            <!-- Benefício 2: 50/50 PROFIT SPLIT -->
            <div class="sp-benefit-card">
                <div class="sp-benefit-icon">
                    <img src="<?php echo get_template_directory_uri(); ?>/imagens/apertodemao.png" alt="50/50 Profit Split" class="benefit-icon-img">
                </div>
                <h3 class="sp-benefit-title">50/50 PROFIT SPLIT</h3>
                <p class="sp-benefit-desc">Win $20K? Keep $10K. Lose? We cover it all. No caps on earnings, no risk to you.</p>
            </div>

            <!-- Benefício 3: ELITE SUPPORT -->
            <div class="sp-benefit-card">
                <div class="sp-benefit-icon">
                    <img src="<?php echo get_template_directory_uri(); ?>/imagens/explicacao.png" alt="Elite Support" class="benefit-icon-img">
                </div>
                <h3 class="sp-benefit-title">ELITE SUPPORT</h3>
                <p class="sp-benefit-desc">Access to professional coaches, GTO solvers, strategy sessions, and a global community of sponsored players.</p>
            </div>

            <!-- Benefício 4: CLEAR PATH TO PRO -->
            <div class="sp-benefit-card">
                <div class="sp-benefit-icon">
                    <img src="<?php echo get_template_directory_uri(); ?>/imagens/chapeu.png" alt="Clear Path to Pro" class="benefit-icon-img">
                </div>
                <h3 class="sp-benefit-title">CLEAR PATH TO PRO</h3>
                <p class="sp-benefit-desc">Performance-based tier progression with transparent objectives. Your growth is our priority.</p>
            </div>

        </div>

    </div>
</section>

<!-- Seção Two Paths to Professional Sponsorship -->
<section class="sp-paths-section">
    <div class="ap-container">
        
        <!-- Título da seção -->
        <h2 class="sp-paths-title">Two paths to professional sponsorship</h2>

        <!-- Cards de Paths -->
        <div class="sp-paths-cards">
            
            <!-- Card 1: Our Challenges -->
            <div class="sp-path-card sp-path-left">
                <div class="sp-path-inner">
                    <h3 class="sp-path-card-title">Our Challenges</h3>
                    <p class="sp-path-card-desc">Win our weekly challenges and earn your sponsorship. Open to all club members who prove their skills.</p>
                    <a href="<?php echo esc_url(site_url('/desafios/')); ?>" class="sp-path-link">View Current Challenges →</a>
                </div>
            </div>

            <!-- Card 2: Committee Review -->
            <div class="sp-path-card sp-path-right">
                <div class="sp-path-inner">
                    <h3 class="sp-path-card-title">Committee Review</h3>
                    <p class="sp-path-card-desc">Exceptional players with strong resumes may receive direct sponsorship offers from our professional committee.</p>
                    <a href="<?php echo esc_url(site_url('/profile/')); ?>" class="sp-path-link">Complete Your Profile →</a>
                </div>
            </div>

        </div>

    </div>
</section>

<!-- Seção Featured Players -->
<section class="featured-players-section">
    <div class="featured-players-container">
        <h2 class="featured-players-title">Real players, real results, real sponsorships</h2>
        
        <div class="players-carousel">
            <button class="carousel-btn carousel-btn-prev" onclick="moveCarousel(-1)">
                <i class="fas fa-chevron-left"></i>
            </button>
            
            <div class="carousel-wrapper">
                <div class="carousel-track" id="carouselTrack">
                    <div class="player-card">
                        <img src="<?php echo get_template_directory_uri(); ?>/imagens/players/Tahles.webp" alt="Player 1">
                        <div class="player-overlay">
                            <h3 class="player-name">Thales Moreli</h3>
                            <p class="player-quote">"I joined the Poker111 team and won R$415,950 at the BSOP Winter Millions. Now my dream is real"</p>
                        </div>
                    </div>
                    
                    <div class="player-card">
                        <img src="<?php echo get_template_directory_uri(); ?>/imagens/players/Carol.webp" alt="Player 2">
                        <div class="player-overlay">
                            <h3 class="player-name">Carol Martins</h3>
                            <p class="player-quote">"We set targets, tracked hands, and measured edges. The first milestone felt unreal - then it became the new normal"</p>
                        </div>
                    </div>
                    
                    <div class="player-card">
                        <img src="<?php echo get_template_directory_uri(); ?>/imagens/players/LucasMeira.webp" alt="Player 3">
                        <div class="player-overlay">
                            <h3 class="player-name">Lucas Meira</h3>
                            <p class="player-quote">"They flew me to EPT Barcelona. Poker111 made it real"</p>
                        </div>
                    </div>
                    
                    <div class="player-card">
                        <img src="<?php echo get_template_directory_uri(); ?>/imagens/players/Luiz.webp" alt="Player 4">
                        <div class="player-overlay">
                            <h3 class="player-name">Luiz Alberto</h3>
                            <p class="player-quote">"Focused, fearless, and risk-free I achieved the results that pushed my poker journey to the next level"</p>
                        </div>
                    </div>
                    
                    <div class="player-card">
                        <img src="<?php echo get_template_directory_uri(); ?>/imagens/players/AnaCarolina.webp" alt="Player 5">
                        <div class="player-overlay">
                            <h3 class="player-name">Ana Carolina Teixeira</h3>
                            <p class="player-quote">"If poker is more than a hobby, this is the bridge. The support is real, the bar is high, and the trajectory is different here"</p>
                        </div>
                    </div>
                    
                    <div class="player-card">
                        <img src="<?php echo get_template_directory_uri(); ?>/imagens/players/Jefferson.webp" alt="Player 6">
                        <div class="player-overlay">
                            <h3 class="player-name">Jefferson de Lima</h3>
                            <p class="player-quote">"Grinding alone capped my potential. With a team, I gained a plan, accountability, and belief. It stopped feeling like a dream and started feeling like a path"</p>
                        </div>
                    </div>
                    
                    <div class="player-card">
                        <img src="<?php echo get_template_directory_uri(); ?>/imagens/players/Daniele.webp" alt="Player 7">
                        <div class="player-overlay">
                            <h3 class="player-name">Daniele Feitosa</h3>
                            <p class="player-quote">"I fell in love with the vision and the team - and Poker111 made it real. They flew me to EPT Barcelona! Thank you, Poker111. I couldn't have done this without you"</p>
                        </div>
                    </div>
                    
                    <div class="player-card">
                        <img src="<?php echo get_template_directory_uri(); ?>/imagens/players/Raphael.webp" alt="Player 8">
                        <div class="player-overlay">
                            <h3 class="player-name">Raphael Garcia</h3>
                            <p class="player-quote">"We set targets, tracked hands, and measured edges. The first milestone felt unreal - then it became the new normal"</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <button class="carousel-btn carousel-btn-next" onclick="moveCarousel(1)">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
    </div>
</section>

<script>
let currentIndex = 0;
const track = document.getElementById('carouselTrack');
const cards = document.querySelectorAll('.player-card');
const totalCards = cards.length;

function getCardsPerView() {
    if (window.innerWidth <= 480) return 1;
    if (window.innerWidth <= 768) return 2;
    if (window.innerWidth <= 1200) return 3;
    return 4;
}

function moveCarousel(direction) {
    const cardsPerView = getCardsPerView();
    const maxIndex = totalCards - cardsPerView;
    
    currentIndex += direction;
    
    if (currentIndex < 0) currentIndex = 0;
    if (currentIndex > maxIndex) currentIndex = maxIndex;
    
    const cardWidth = cards[0].offsetWidth;
    const gap = 20;
    const offset = currentIndex * (cardWidth + gap);
    
    track.style.transform = `translateX(-${offset}px)`;
}

window.addEventListener('resize', () => {
    currentIndex = 0;
    track.style.transform = 'translateX(0)';
});
</script>

<!-- Seção FAQ -->
<section class="faq-section">
    <div class="faq-container">
        <h2 class="faq-title">Frequently Asked Questions</h2>
        
        <div class="faq-list">
            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>What's the difference in support for the different club members?</span>
                    <button class="faq-toggle">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
                <div class="faq-answer">
                    <p>Each membership tier offers tailored support levels. Basic members get access to our educational content library, Silver members receive weekly group coaching sessions, Gold members get personalized 1-on-1 strategy reviews, and Platinum members enjoy priority support with direct access to professional coaches and tournament selection guidance.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>I need to update sensitive personal data like my name, date of birth, or address. How do I do this?</span>
                    <button class="faq-toggle">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
                <div class="faq-answer">
                    <p>For security reasons, updates to sensitive personal information must be requested through our support team. Contact us via email at support@poker111.com with your registered email address and verification documents. Our team will process your request within 24-48 hours.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>Where can I find info about my Poker Club membership or challenge entries?</span>
                    <button class="faq-toggle">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
                <div class="faq-answer">
                    <p>All your membership details and challenge history are available in your personal dashboard. Log in to your account and navigate to "My Profile" to view your current membership tier, active challenges, past results, and upcoming opportunities.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>How can I apply to become a sponsored player?</span>
                    <button class="faq-toggle">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
                <div class="faq-answer">
                    <p>There are two paths to sponsorship: 1) Win one of our weekly challenges to earn automatic sponsorship consideration, or 2) Submit your poker resume and results through your profile for committee review. Our professional committee evaluates applications monthly and reaches out to exceptional players directly.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>I won a challenge or was qualified for membership, what's next?</span>
                    <button class="faq-toggle">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
                <div class="faq-answer">
                    <p>Congratulations! Our team will contact you within 48 hours via email to schedule an onboarding call. During this call, we'll discuss your sponsorship terms, tournament schedule, coaching plan, and answer any questions you may have about the program.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>Who is responsible for reviewing applications submitted through the questionnaire?</span>
                    <button class="faq-toggle">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
                <div class="faq-answer">
                    <p>Our professional committee consists of experienced poker professionals, coaches, and team managers who review all sponsorship applications. Each application is evaluated based on playing history, results, tournament performance, and overall potential for growth within our sponsorship program.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>Who do I contact for questions about my contract, tournament selection, or bankroll?</span>
                    <button class="faq-toggle">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
                <div class="faq-answer">
                    <p>Sponsored players have a dedicated team manager assigned to them. For contract questions, reach out to legal@poker111.com. For tournament selection and bankroll matters, contact your team manager directly or email tournaments@poker111.com for immediate assistance.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function toggleFaq(element) {
    const faqItem = element.parentElement;
    const answer = faqItem.querySelector('.faq-answer');
    const toggle = element.querySelector('.faq-toggle i');
    
    // Close all other FAQs
    document.querySelectorAll('.faq-item').forEach(item => {
        if (item !== faqItem) {
            item.classList.remove('active');
            item.querySelector('.faq-answer').style.maxHeight = null;
            item.querySelector('.faq-toggle i').classList.remove('fa-minus');
            item.querySelector('.faq-toggle i').classList.add('fa-plus');
        }
    });
    
    // Toggle current FAQ
    faqItem.classList.toggle('active');
    
    if (faqItem.classList.contains('active')) {
        answer.style.maxHeight = answer.scrollHeight + 'px';
        toggle.classList.remove('fa-plus');
        toggle.classList.add('fa-minus');
    } else {
        answer.style.maxHeight = null;
        toggle.classList.remove('fa-minus');
        toggle.classList.add('fa-plus');
    }
}
</script>

<?php get_footer(); ?>

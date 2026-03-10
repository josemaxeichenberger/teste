<?php
/**
 * Template Name: Mentorship
 * Descrição: Página de Mentorship - Poker111
 */

get_header();
?>

<!-- Seção Hero - fundo escuro -->
<section class="ap-section sp-hero-section">
    <div class="ap-container">

        <!-- Título e Conteúdo -->
        <div class="sp-hero">
            <h1 class="sp-scouting-title">MENTORSHIP</h1>
            
            <div class="sp-scouting-content">
                <p class="sp-scouting-text">Our Poker111 mentorship program connects team players with dedicated and experienced mentors, who will guide and support them in a variety of key areas.</p>
            </div>
            
            <!-- Grid de benefícios do Mentorship -->
            <div class="mentorship-benefits-grid">
                
                <!-- Benefício 1: Arranging tournaments -->
                <div class="mentorship-benefit-item">
                    <div class="mentorship-benefit-icon">
                        <img src="<?php echo get_template_directory_uri(); ?>/imagens/trofeu.png" alt="Arranging tournaments">
                    </div>
                    <p class="mentorship-benefit-text">Arranging tournaments.</p>
                </div>
                
                <!-- Benefício 2: Handling bankrolls -->
                <div class="mentorship-benefit-item">
                    <div class="mentorship-benefit-icon">
                        <img src="<?php echo get_template_directory_uri(); ?>/imagens/handling.png" alt="Handling bankrolls">
                    </div>
                    <p class="mentorship-benefit-text">Handling bankrolls properly.</p>
                </div>
                
                <!-- Benefício 3: Booking hotel -->
                <div class="mentorship-benefit-item">
                    <div class="mentorship-benefit-icon">
                        <img src="<?php echo get_template_directory_uri(); ?>/imagens/predios.png" alt="Booking hotel">
                    </div>
                    <p class="mentorship-benefit-text">Booking hotel accommodations.</p>
                </div>
                
                <!-- Benefício 4: Providing help -->
                <div class="mentorship-benefit-item">
                    <div class="mentorship-benefit-icon">
                        <img src="<?php echo get_template_directory_uri(); ?>/imagens/lampada.png" alt="Providing help">
                    </div>
                    <p class="mentorship-benefit-text">Providing general help.</p>
                </div>
                
            </div>

            <!-- Seção Also Includes the Following -->
            <div class="mentorship-also-section">
                <h2 class="mentorship-also-title">Our mentorship program also includes the following:</h2>
                <div class="mentorship-also-grid">

                    <div class="mentorship-also-card">
                        <h3 class="mentorship-also-card-title">Detailed Feedback</h3>
                        <p class="mentorship-also-card-desc">Mentors provide in-depth analysis of how players make their choices, allowing players to see the reasoning behind each action.</p>
                    </div>

                    <div class="mentorship-also-card">
                        <h3 class="mentorship-also-card-title">Advanced Strategy Tools</h3>
                        <p class="mentorship-also-card-desc">Team members are provided with premium tools such as GTO solvers and exclusive content from major poker academies.</p>
                    </div>

                    <div class="mentorship-also-card">
                        <h3 class="mentorship-also-card-title">Collaborative Growth</h3>
                        <p class="mentorship-also-card-desc">Experienced players mentor new ones, fostering a supportive, collaborative environment. This ensures everyone benefits from the knowledge of those who've achieved success.</p>
                    </div>

                </div>
            </div>

        </div>

    </div>
</section>

<!-- Seção Learning and Skill Development -->
<section class="sp-learning-section">
    <div class="ap-container">

        <div class="sp-learning-content">
            <h2 class="sp-learning-title">Learning and Skill Development</h2>

            <p class="sp-learning-text">At Poker111, we strive to maintain an atmosphere where learning and growth is a top priority. We know from our own experience that through the right education, players will have an increased opportunity to reach their dreams and develop into world-class poker players. Through the right partnerships and a mentorship system, we can give players the ability to be the best player they can be, both on and off the table.</p>

            <p class="sp-learning-text sp-learning-text--bold">By cultivating talent, and making growth an absolute priority, Poker111 is redefining the face of poker and reinventing what it means to be a professional poker player.</p>

            <div class="sp-learning-cta">
                <a href="#" class="sp-learning-btn">BECOME A MEMBER</a>
            </div>
        </div>

    </div>
</section>

<!-- Seção FAQ -->
<section class="faq-section">
    <div class="faq-container">
        <h2 class="faq-title">Frequently Asked Questions</h2>
        <p class="faq-category">Mentorship</p>
        
        <div class="faq-list">
            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>What is the Poker111 Mentorship Program?</span>
                    <button class="faq-toggle">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
                <div class="faq-answer">
                    <p>The Poker111 Mentorship Program connects team players with dedicated and experienced mentors who guide and support them across key areas such as tournament arrangement, bankroll management, accommodation logistics, and overall professional development. Our goal is to accelerate player growth by pairing them with mentors who have firsthand experience at the highest levels of the game.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>What are the most important skills required for success in poker according to Poker111?</span>
                    <button class="faq-toggle">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
                <div class="faq-answer">
                    <p>According to Poker111, the most critical skills for long-term poker success include strategic decision-making, disciplined bankroll management, emotional control under pressure, adaptability to different opponents and formats, and a continuous willingness to learn. Our mentors focus on developing all of these areas in a structured and personalized way.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>Why does Poker111 promote continuous learning for poker players?</span>
                    <button class="faq-toggle">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
                <div class="faq-answer">
                    <p>Poker is an ever-evolving game. New strategies, solvers, and competitive dynamics constantly emerge, meaning players who stop learning quickly fall behind. Poker111 promotes continuous education because we believe that an informed and growing player is a competitive player — and our mentorship program is built around that philosophy of lifelong improvement.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>What resources does Poker111 provide to its players for growth?</span>
                    <button class="faq-toggle">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
                <div class="faq-answer">
                    <p>Poker111 provides team members with access to premium GTO solvers, exclusive content from top poker academies, hand history analysis tools, study groups, personalized coaching sessions, and a collaborative community of experienced players. These resources are designed to give every player — regardless of their current level — the best possible foundation for growth.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>How does the mentorship program support players in their poker careers?</span>
                    <button class="faq-toggle">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
                <div class="faq-answer">
                    <p>Our mentorship program supports players by providing structured guidance from seasoned professionals who have navigated the same challenges. Mentors assist with scheduling tournaments, managing bankrolls responsibly, booking travel and accommodations, and offering in-depth feedback on gameplay. This holistic support allows players to focus entirely on their performance and development.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>What is the importance of collaborative growth in Poker111's program?</span>
                    <button class="faq-toggle">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
                <div class="faq-answer">
                    <p>Collaborative growth is a cornerstone of Poker111's philosophy. Experienced players mentor newcomers, creating an environment where knowledge flows freely across all levels of the team. This culture ensures that every member benefits from the collective wisdom of those who have already achieved success, while also reinforcing the mentors' own understanding through teaching.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>What makes Poker111's approach to player development unique?</span>
                    <button class="faq-toggle">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
                <div class="faq-answer">
                    <p>What sets Poker111 apart is our combination of personalized mentorship, premium analytical tools, collaborative team culture, and financial support. Unlike generic coaching programs, we tailor our approach to each player's strengths and areas for improvement, ensuring that development is both targeted and effective. We don't just invest in players — we invest in their entire journey.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>Are there specific tools provided to players as part of the mentorship program?</span>
                    <button class="faq-toggle">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
                <div class="faq-answer">
                    <p>Yes. Team members enrolled in the Poker111 mentorship program receive access to industry-leading GTO solvers, equity calculators, hand replayers, and exclusive video content produced by professional coaches from major poker academies. These tools are provided to ensure players have every technical resource they need to refine their strategy and stay competitive at the highest levels.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>Are beginners eligible for the Poker111 mentorship program?</span>
                    <button class="faq-toggle">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
                <div class="faq-answer">
                    <p>Yes, beginners with genuine potential and a strong desire to improve are welcome in the Poker111 mentorship program. While we also work with advanced players, we believe that early-stage development is critical — and that building the right habits and mindset from the beginning can make all the difference in a player's long-term career trajectory.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>How does Poker111 define success for its players?</span>
                    <button class="faq-toggle">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
                <div class="faq-answer">
                    <p>Poker111 defines success as a combination of consistent performance improvement, financial sustainability, and personal fulfillment at the table. While tournament results and titles are important milestones, we measure true success by a player's ability to grow, adapt, and perform at their best over time. We want our players to become world-class professionals both on and off the felt.</p>
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

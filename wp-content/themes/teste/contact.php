<?php
/**
 * Template Name: Contact
 * Description: Página de contato Poker111
 */

get_header();
?>

<!-- Autocomplete Detection CSS -->
<style>
@keyframes onAutoFillStart {
    from { opacity: 0.99; }
    to { opacity: 1; }
}
@keyframes onAutoFillCancel {
    from { opacity: 1; }
    to { opacity: 0.99; }
}
input:-webkit-autofill {
    animation-name: onAutoFillStart;
    animation-duration: 0.001s;
}
input:not(:-webkit-autofill) {
    animation-name: onAutoFillCancel;
    animation-duration: 0.001s;
}
</style>

<!-- Top Banner -->
<div class="top-banner">
    <div class="top-banner-content">
        <div class="banner-text">
            <span class="banner-title">Next Poker Challenge -</span>
        </div>
        <div class="banner-countdown">
            <div class="countdown-item">
                <span class="countdown-value" id="days">5</span>
                <span class="countdown-label">Days</span>
            </div>
            <div class="countdown-separator">
                <span class="dot"></span>
                <span class="dot"></span>
            </div>
            <div class="countdown-item">
                <span class="countdown-value" id="hours">20</span>
                <span class="countdown-label">Hrs</span>
            </div>
            <div class="countdown-separator">
                <span class="dot"></span>
                <span class="dot"></span>
            </div>
            <div class="countdown-item">
                <span class="countdown-value" id="minutes">27</span>
                <span class="countdown-label">Min</span>
            </div>
            <div class="countdown-separator">
                <span class="dot"></span>
                <span class="dot"></span>
            </div>
            <div class="countdown-item">
                <span class="countdown-value" id="seconds">56</span>
                <span class="countdown-label">Sec</span>
            </div>
        </div>
        <button class="banner-btn">More Info</button>
    </div>
</div>

<!-- Main Content -->
<main class="main-content contact-page">
    <div class="container">
        <!-- Contact Section -->
        <div class="contact-section">
            <div class="contact-form-wrapper">
                <h1 class="contact-title">Get <em>In Touch</em></h1>
                <p class="contact-subtitle">We're happy to hear from you. Use the form to contact us with any questions, concerns, or feedback.</p>
                
                <form class="contact-form" id="contactForm">
                    <div class="form-row">
                        <div class="form-group">
                            <input type="text" class="form-input" id="contactFirstName" name="firstName" placeholder="First Name *" autocomplete="given-name" required>
                        </div>
                        <div class="form-group">
                            <input type="text" class="form-input" id="contactLastName" name="lastName" placeholder="Last Name *" autocomplete="family-name" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <input type="email" class="form-input" id="contactEmail" name="emailAddress" placeholder="Email Address *" autocomplete="email" required>
                        </div>
                        <div class="form-group">
                            <select class="form-input form-select" id="contactSubject" name="subject" required>
                                <option value="">Subject</option>
                                <option value="player-application">Player applications & committee review</option>
                                <option value="coaching">Coaching, academy & training</option>
                                <option value="account">Account, payments & contracts</option>
                                <option value="technical">Technical or website issue</option>
                                <option value="business">Business & media partnerships</option>
                                <option value="general">General inquiry or feedback</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <textarea class="form-input form-textarea" id="contactMessage" name="message" placeholder="Message" rows="6"></textarea>
                    </div>
                    
                    <div class="form-checkbox-group">
                        <label class="checkbox-label">
                            <input type="checkbox" id="contactTerms" name="termsAgree" required>
                            <span>I agree to the <a href="#">Terms of Use</a></span>
                        </label>
                    </div>
                    
                    <div class="form-checkbox-group">
                        <label class="checkbox-label">
                            <input type="checkbox" id="contactNewsletter" name="newsletter">
                            <span>Subscribe to our Newsletter</span>
                        </label>
                    </div>
                    
                    <button type="submit" class="btn-submit">Submit</button>
                </form>
            </div>
            
            <div class="contact-avatars">
                <!-- Decorative circles -->
                <div class="decorative-circle circle-deco-1"></div>
                <div class="decorative-circle circle-deco-2"></div>
                <div class="decorative-circle circle-deco-3"></div>
                <div class="decorative-circle circle-deco-4"></div>
                <div class="decorative-circle circle-deco-5"></div>
                <div class="decorative-circle circle-deco-6"></div>
                <div class="decorative-circle circle-deco-7"></div>
                <div class="decorative-circle circle-deco-8"></div>
                <div class="decorative-circle circle-deco-9"></div>
                <div class="decorative-circle circle-deco-10"></div>
                <div class="decorative-circle circle-deco-11"></div>
                <div class="decorative-circle circle-deco-12"></div>
                <div class="decorative-circle circle-deco-13"></div>
                <div class="decorative-circle circle-deco-14"></div>
                <div class="decorative-circle circle-deco-15"></div>
                
                <!-- Avatar circles -->
                <?php
                $avatars = [
                    "317a1645b8a25ae97e20496bada2f95a107c2f07.png",
                    "415f578673f868b920c80d95b24fc3c17fd3fff7.png",
                    "4aba288142527268c7be2ae7cc167532197831f7.png",
                    "8a2f44c257b915e486e516fa8c581f2dc887838a.png",
                    "92662c5b77d3c5165595688b2b5330922101c452.png",
                    "9a86d9d0459ea457097923c5a164db423aa54d46.png",
                    "af2c58dc7d4a767c664cd9a8ecee146afa710e4e.png"
                ];
                
                foreach ($avatars as $index => $img): ?>
                    <div class="avatar-circle avatar-<?php echo $index + 1; ?>">
                        <img src="<?php echo get_template_directory_uri(); ?>/imagens/profile/<?php echo $img; ?>" alt="Team Member">
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</main>

<!-- Platforms Section -->
<section class="platforms-section">
    <div class="container">
        <h2 class="platforms-title">Find us on other platforms:</h2>
        <div class="platforms-grid">
            <div class="platform-card">
                <div class="platform-icon">
                    <img src="<?php echo get_template_directory_uri(); ?>/imagens/whatsapp.gif" alt="WhatsApp">
                </div>
                <h3 class="platform-name">WHATSAPP</h3>
                <p class="platform-description">Chat directly with the team for quick,<br>personal support on your phone, anywhere in the world.</p>
            </div>
            <div class="platform-card">
                <div class="platform-icon">
                    <img src="<?php echo get_template_directory_uri(); ?>/imagens/discord.gif" alt="Discord">
                </div>
                <h3 class="platform-name">DISCORD</h3>
                <p class="platform-description">Connect with the community,<br>join study rooms, and talk strategy live by voice or text.</p>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="faq-section">
    <div class="container">
        <h2 class="faq-title">Frequently Asked Questions</h2>
        <div class="faq-list">
            <?php
            $faqs = [
                "What's the difference in support for the different club members?" =>
                    "Different membership tiers receive varying levels of support and benefits tailored to their needs.",
                
                "I need to update sensitive personal data like my name, date of birth, or address. How do I do this?" =>
                    "Please contact our support team through the contact form above with your verification details.",
                
                "Where can I find info about my Poker Club membership or challenge entries?" =>
                    "All membership and challenge information can be found in your member dashboard.",
                
                "How can I apply to become a sponsored player?" =>
                    "Submit your application through our sponsorship program page with your poker achievements and statistics.",
                
                "I won a challenge or was qualified for membership, what's next?" =>
                    "Congratulations! Our team will reach out to you within 48 hours with next steps.",
                
                "Who is responsible for reviewing applications submitted through the questionnaire?" =>
                    "Our dedicated committee reviews all applications to ensure fair and thorough evaluation.",
                
                "Who do I contact for questions about my contract, tournament selection, or bankroll?" =>
                    "Please use the contact form above and select \"Account, payments & contracts\" from the subject dropdown."
            ];
            
            foreach ($faqs as $question => $answer): ?>
                <div class="faq-item">
                    <button class="faq-question">
                        <span><?php echo esc_html($question); ?></span>
                        <i class="fas fa-plus"></i>
                    </button>
                    <div class="faq-answer">
                        <p><?php echo esc_html($answer); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Scripts -->

<?php get_footer(); ?>

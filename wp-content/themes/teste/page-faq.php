<?php
/**
 * Template Name: Faq
 * Description: Página de Faq Poker111
 */

get_header();
?>

<!-- FAQ Section -->
<section class="faq-section">
    <div class="container">
        <h2 class="faq-title"><em>Frequently Asked Questions</em></h2>

        <?php
        $faqs = [
            "What's the difference in support for the different club members?" =>
                "Different membership tiers receive varying levels of support and benefits tailored to their needs.",
            "I need to update sensitive personal data like my name, date of birth, or address. How do I do this?" =>
                "Please contact our support team through our contact page with your verification details.",
            "Where can I find info about my Poker Club membership or challenge entries?" =>
                "All membership and challenge information can be found in your member dashboard.",
            "How can I apply to become a sponsored player?" =>
                "Submit your application through our sponsorship program page with your poker achievements and statistics.",
            "I won a challenge or was qualified for membership, what's next?" =>
                "Congratulations! Our team will reach out to you within 48 hours with next steps.",
            "Who is responsible for reviewing applications submitted through the questionnaire?" =>
                "Our dedicated committee reviews all applications to ensure fair and thorough evaluation.",
            "Who do I contact for questions about my contract, tournament selection, or bankroll?" =>
                "Please reach out to our support team via the contact page for assistance with contracts, tournaments, or bankroll inquiries."
        ];

        $categories = ['Question Category', 'Question Category', 'Question Category'];

        foreach ($categories as $category): ?>
            <p class="faq-category"><?php echo esc_html($category); ?></p>
            <div class="faq-list">
                <?php foreach ($faqs as $question => $answer): ?>
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
        <?php endforeach; ?>

    </div>
</section>

<!-- Scripts -->

<?php get_footer(); ?>

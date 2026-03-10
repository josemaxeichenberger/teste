<?php
/**
 * Template Name: Application Process
 * Description: Página de Application Process - Poker111
 */

get_header();
?>

<!-- Application Process Section -->
<section class="ap-section">
    <div class="ap-container">

        <!-- Hero -->
        <div class="ap-hero">
            <h1 class="ap-title">Application Process</h1>
            <p class="ap-subtitle">Whether you're an aspiring or experienced poker player, Poker111 gives you a chance to join the team and earn a sponsorship through our Team Challenge system or a committee review.</p>
        </div>

        <!-- Key Steps Heading -->
        <h2 class="ap-steps-heading">Becoming a sponsored player involves several key steps:</h2>

        <div class="ap-steps">

            <?php
            $base = get_template_directory_uri() . '/imagens/';
            $steps = [
                ['img' => 'moedapoker.png', 'title' => 'Registration',                                          'desc' => 'Open your Poker111 account and verify your email.'],
                ['img' => 'moedapoker2.png', 'title' => 'Complete Your Profile',                                  'desc' => 'Answer a short questionnaire so we can better understand your poker background. Players with a strong poker resume will be reviewed by our professional committee and may get special personal contract.'],
                ['img' => 'moedapoker3.png', 'title' => 'Join our club and play a Challenge',                     'desc' => 'Our club members gain entry to challenges, a word of benefits for joining our community. Win: Become a Poker111 sponsored team member. Lose: Re-enter and try again.'],
                ['img' => 'moedapoker4.png', 'title' => 'For Experienced Players Who Didn\'t Pass Committee Review', 'desc' => 'Didn\'t pass our committee review? Join our club, become a community member and participate in Challenges to prove your skills and get an opportunity to become a team member and secure a sponsorship contract.'],
            ];
            foreach ($steps as $step) : ?>
            <div class="ap-step">
                <div class="ap-chip">
                    <img src="<?php echo esc_url($base . $step['img']); ?>" alt="<?php echo esc_attr($step['title']); ?>" class="ap-chip-img">
                </div>
                <div class="ap-step-content">
                    <h3 class="ap-step-title"><?php echo esc_html($step['title']); ?></h3>
                    <p class="ap-step-desc"><?php echo esc_html($step['desc']); ?></p>
                </div>
            </div>
            <?php endforeach; ?>

        </div>

        <!-- CTA -->
        <div class="ap-cta">
            <div class="ap-cta-image">
                <img src="<?php echo esc_url(get_template_directory_uri() . '/imagens/cta-player.png'); ?>" alt="Start Your Application">
            </div>
            <div class="ap-cta-body">
                <h2 class="ap-cta-title">Start Your Application</h2>
                <p class="ap-cta-desc">Visit the Challenges Page to learn about more challenges and opportunities for securing a sponsorship contract with Poker111.</p>
                <a href="<?php echo esc_url(site_url('/questionary/')); ?>" class="ap-cta-btn">JOIN THE CLUB</a>
            </div>
        </div>

    </div>
</section>

<?php get_footer(); ?>

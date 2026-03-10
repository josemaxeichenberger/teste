<?php
/**
 * Template Name: About
 * Description: About do Poker111 com todo o conteúdo
 */

// Iniciar sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir helpers necessários
require_once dirname(__FILE__) . '/../../../helpers/TicketManager.php';

// Verificar se o usuário está logado
$isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$userFullName = $isLoggedIn ? $_SESSION['user_name'] : 'User';
// Pegar apenas o primeiro nome
$userName = explode(' ', $userFullName)[0];
$userEmail = $isLoggedIn ? $_SESSION['user_email'] : '';
$userId = $isLoggedIn ? $_SESSION['user_id'] : null;

// Buscar avatar e tickets do usuário do banco de dados
$userAvatar = 'avatar1.webp'; // Padrão
$userTickets = 0;

if ($isLoggedIn && $userId) {
    try {
        require_once dirname(__FILE__) . '/../../../conexao/conexao.php';
        $pdo = getConnection();
        $stmt = $pdo->prepare("SELECT url_avatar FROM usuarios WHERE id = ?");
        $stmt->execute([$userId]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($userData && !empty($userData['url_avatar'])) {
            $userAvatar = $userData['url_avatar'];
        }
        
        // Buscar quantidade de tickets disponíveis
        $userTickets = TicketManager::contarTicketsDisponiveis($userId);
        
    } catch (PDOException $e) {
        error_log('Erro ao buscar avatar do usuário na landing page: ' . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php bloginfo('name'); ?> - Landing Page</title>
    
    <!-- Google Fonts - Montserrat -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,400;0,600;0,700;0,800;1,600;1,800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Header Navigation CSS (estilos unificados mobile/desktop) -->
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/css/header-navigation.css">
    
    <!-- Navigation Dropdown CSS -->
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/css/navigation.css">
    
    <!-- Menu CSS (estilos do avatar e ticket counter) -->
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/css/menu.css">
    
    <!-- About Page CSS -->
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/css/about.css">
    
    <?php wp_head(); ?>
    
    <!-- Google Identity Services -->
    <script src="https://accounts.google.com/gsi/client"></script>
    
    <?php
    // Incluir conexao.php para ter acesso ao GOOGLE_CLIENT_ID
    require_once dirname(__FILE__) . '/../../../conexao/conexao.php';
    ?>
    
    <script>
    // Configuração do Google OAuth
    const GOOGLE_CLIENT_ID = '<?php echo defined("GOOGLE_CLIENT_ID") ? GOOGLE_CLIENT_ID : ""; ?>';
    console.log('🔑 Google Client ID configurado:', GOOGLE_CLIENT_ID ? 'SIM' : 'NÃO');
    </script>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<!-- Header -->
<header class="header">
    <div class="container">
        <div class="header-content">
            <div class="logo">
                <?php if (has_custom_logo()) : ?>
                    <?php the_custom_logo(); ?>
                <?php else : ?>
                    <img src="<?php echo esc_url(get_template_directory_uri() . '/logopoker.png'); ?>" alt="<?php bloginfo('name'); ?>" class="logo-image">
                <?php endif; ?>
            </div>
            
            <button class="mobile-menu-toggle" onclick="toggleMobileMenu()">
                <i class="fas fa-bars"></i>
            </button>
            
            <div class="language-selector language-selector-mobile" onclick="toggleLanguageMenu(event)">
                <img src="https://flagcdn.com/w40/us.png" alt="EN" class="flag-icon" id="currentFlagMobile">
                <i class="fas fa-chevron-down"></i>
                <div class="language-dropdown" id="languageDropdownMobile">
                    <div class="language-option" onclick="changeLanguage('en', 'us', event)">
                        <img src="https://flagcdn.com/w40/us.png" alt="English" class="flag-icon">
                        <span>English</span>
                    </div>
                    <div class="language-option" onclick="changeLanguage('pt', 'br', event)">
                        <img src="https://flagcdn.com/w40/br.png" alt="Português" class="flag-icon">
                        <span>Português</span>
                    </div>
                </div>
            </div>
            
            <nav class="nav-menu" id="navMenu">
                <?php
                $poker111_menu_args = array(
                    'theme_location' => 'header-primary',
                    'container'      => false,
                    'items_wrap'     => '%3$s',
                    'fallback_cb'    => 'poker111_menu_fallback',
                    'depth'          => 3,
                );

                if (class_exists('Poker111_Header_Menu_Walker')) {
                    $poker111_menu_args['walker'] = new Poker111_Header_Menu_Walker();
                }

                wp_nav_menu($poker111_menu_args);
                ?>
                
<!-- Mobile User Dropdown (shown when logged in) -->
                <?php if ($isLoggedIn): ?>
                <div class="nav-dropdown mobile-user-nav-dropdown" id="mobileUserNavDropdown">
                    <a href="#" class="nav-link" onclick="toggleNavDropdown(event, 'mobileUserDropdownMenu')">
                        <i class="fas fa-user-circle" style="margin-right: 8px;"></i>
                        HI, <?php echo strtoupper(htmlspecialchars($userName)); ?> 
                        <i class="fas fa-chevron-down"></i>
                    </a>
                    <div class="nav-dropdown-menu" id="mobileUserDropdownMenu">
                        <a href="dashboard.html" class="nav-dropdown-item">Dashboard</a>
                        <a href="#" class="nav-dropdown-item">My Profile</a>
                        <a href="#" class="nav-dropdown-item">Settings</a>
                        <a href="#" class="nav-dropdown-item" onclick="logout(event)">Logout</a>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Mobile Action Buttons (apenas mobile) -->
                <div class="mobile-actions">
                    <!-- Login Button (hidden when logged in) -->
                    <a href="#" class="mobile-btn-login" id="mobileBtnLogin" onclick="openLoginModal(event)" <?php echo $isLoggedIn ? 'style="display: none;"' : ''; ?>>LOG IN</a>
                    <?php if (!$isLoggedIn): ?>
                    <a href="#" class="mobile-btn-get-started">GET STARTED</a>
                    <?php endif; ?>
                </div>
                
                <div class="language-selector" onclick="toggleLanguageMenu(event)">
                    <img src="https://flagcdn.com/w40/us.png" alt="EN" class="flag-icon" id="currentFlag">
                    <i class="fas fa-chevron-down"></i>
                    <div class="language-dropdown" id="languageDropdown">
                        <div class="language-option" onclick="changeLanguage('en', 'us', event)">
                            <img src="https://flagcdn.com/w40/us.png" alt="English" class="flag-icon">
                            <span>English</span>
                        </div>
                        <div class="language-option" onclick="changeLanguage('pt', 'br', event)">
                            <img src="https://flagcdn.com/w40/br.png" alt="Português" class="flag-icon">
                            <span>Português</span>
                        </div>
                    </div>
                </div>
            </nav>
            
            <div class="header-actions">
                <?php if ($isLoggedIn): ?>
                    <!-- Usuário logado: mostrar ticket counter + avatar -->
                    <div class="ticket-counter" title="Your Tickets">
                        <img src="<?php echo get_template_directory_uri(); ?>/imagens/ticket-icon.png" alt="Ticket" class="ticket-icon-img">
                        <span class="ticket-count"><?php echo esc_html($userTickets); ?></span>
                    </div>
                    <div class="user-avatar-dropdown">
                        <div class="user-avatar" onclick="toggleUserAvatarMenu(event)">
                            <img src="<?php echo get_template_directory_uri(); ?>/imagens/avatares/<?php echo esc_attr($userAvatar); ?>" alt="<?php echo esc_attr($userName); ?>">
                            <div class="card-icon">
                                <img src="<?php echo get_template_directory_uri(); ?>/imagens/icon1.png" alt="Card Icon">
                            </div>
                        </div>
                        <div class="user-avatar-menu" id="userAvatarMenu">
                            <div class="user-avatar-header">
                                <img src="<?php echo get_template_directory_uri(); ?>/imagens/avatares/<?php echo esc_attr($userAvatar); ?>" alt="Avatar" class="menu-avatar">
                                <div class="menu-user-info">
                                    <span class="menu-user-name"><?php echo esc_html($userName); ?></span>
                                </div>
                            </div>
                            <div class="user-avatar-menu-items">
                                <a href="<?php echo esc_url(site_url('/challenges/?tab=settings')); ?>" class="user-avatar-menu-item">
                                    <i class="fas fa-user"></i> My Profile
                                </a>
                                <a href="<?php echo esc_url(site_url('/challenges/')); ?>" class="user-avatar-menu-item">
                                    <i class="fas fa-trophy"></i> Challenges
                                </a>
                                <a href="<?php echo esc_url(site_url('/challenges/?tab=settings')); ?>" class="user-avatar-menu-item">
                                    <i class="fas fa-cog"></i> Settings
                                </a>
                                <hr class="menu-divider">
                                <a href="#" class="user-avatar-menu-item logout-item" onclick="logout(event)">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </a>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Usuário não logado: mostrar botão login -->
                    <a href="#" class="btn-login" onclick="openLoginModal(event)">LOG IN</a>
                <?php endif; ?>
                <?php if (!$isLoggedIn): ?>
                <a href="#" class="btn-get-started">GET STARTED</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-content">
            <span class="hero-tag">ABOUT POKER111</span>
            <h1 class="hero-title">Re-inventing Professional Poker</h1>
            <p class="hero-subtitle">We're building the global platform where poker talent is discovered, developed, and elevated into lasting professional careers.</p>
            <button class="btn-hero" onclick="openLoginModal()">START YOUR JOURNEY</button>
        </div>
    </section>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <!-- Our Story Section -->
            <section class="our-story-section">
                <div class="story-header">
                    <span class="section-tag">OUR STORY</span>
                    <h2 class="section-title">Born from a Simple Truth</h2>
                </div>
                
                <div class="story-content">
                    <div class="story-text-left">
                        <div class="red-line"></div>
                        <p>We saw it everywhere: talented players with the skills to compete at the highest levels, held back by one barrier—the bankroll.</p>
                    </div>
                    
                    <div class="story-image">
                        <img src="<?php echo get_template_directory_uri(); ?>/imagens/about/6f444460-ee5c-48bd-9026-0ca14fdcb59d.png" alt="Poker Cards">
                    </div>
                </div>
                
                <div class="story-bottom">
                    <p class="story-text-right">
                        The dream of playing professionally - felt impossible for most. Not because they lacked the discipline, the strategy, or the mindset. 
                        <em>But because they didn't have the financial backing to take the shot.</em>
                    </p>
                    
                    <div class="story-quote-box">
                        <p>"What if we could remove the financial risk and let pure skill rise to the top?"</p>
                    </div>
                </div>
                
                <div class="story-answer">
                    <div class="story-cards">
                        <img src="<?php echo get_template_directory_uri(); ?>/imagens/about/teste.png" alt="Poker Cards" class="cards-image">
                    </div>
                    <div class="story-answer-text">
                        <p>That question sparked Poker111. We set out to bridge the gap in the poker world - to identify players with real potential and give them the resources, support, and opportunities to turn their passion into a profession.</p>
                    </div>
                </div>
            </section>

            <!-- Movement Section -->
            <section class="movement-section">
                <div class="movement-container">
                    <div class="movement-content">
                        <p class="movement-text">
                            Today, we're more than a platform.<br>
                            <strong>We're a movement</strong>. A global network committed<br>
                            to rewriting the rules of how poker talent is<br>
                            <strong>discovered, supported, and celebrated</strong>.
                        </p>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <!-- Our Mission Section -->
    <section class="mission-section">
        <div class="mission-container">
            <span class="mission-tag">OUR MISSION</span>
            <h2 class="mission-title">Bridging the Gap in Competitive Poker</h2>
            
            <p class="mission-text-bold">Our mission is to make professional poker accessible to anyone with the skill and dedication to succeed, regardless of their financial situation.</p>
            
            <p class="mission-text">We identify talented players worldwide, provide them with sponsorships, mentorship, and training, and guide them through every stage of their career from their first tournament to the world stage.</p>
            
            <button class="btn-mission" onclick="openLoginModal()">Read Our Full Vision →</button>
        </div>
    </section>

    <!-- How We Work Section -->
    <section class="how-we-work-section">
        <div class="how-we-work-container">
            <span class="how-we-work-tag">HOW WE WORK</span>
            <h2 class="how-we-work-title">A True Partnership</h2>
            
            <p class="how-we-work-description">We don't just sponsor players—we build partnerships. From the moment you join, you're a professional member of our team with full support to compete and thrive at the highest levels.</p>
            
            <div class="partnership-grid">
                <div class="partnership-item">
                    <div class="partnership-icon">
                        <i class="fas fa-suitcase"></i>
                    </div>
                    <h3 class="partnership-title">Full tournament funding & travel coverage</h3>
                </div>
                
                <div class="partnership-item">
                    <div class="partnership-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <h3 class="partnership-title">Professional coaching & strategy training</h3>
                </div>
                
                <div class="partnership-item">
                    <div class="partnership-icon">
                        <i class="fas fa-globe"></i>
                    </div>
                    <h3 class="partnership-title">Access to global poker events & circuits</h3>
                </div>
                
                <div class="partnership-item">
                    <div class="partnership-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3 class="partnership-title">Career guidance & development planning</h3>
                </div>
            </div>
            
            <button class="btn-apply" onclick="openLoginModal()">Apply for Sponsorship →</button>
        </div>
    </section>

    <!-- Services Grid Section -->
    <section class="services-grid-section">
        <div class="services-grid-container">
            <div class="services-grid">
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3 class="service-title">SCOUTING</h3>
                    <p class="service-description">We actively identify players across online and live environments who demonstrate the skills, discipline, and competitive mindset to thrive at the highest levels. Our goal: find future champions, wherever they are.</p>
                    <a href="#" class="service-link">Learn More →</a>
                </div>
                
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <h3 class="service-title">SPONSORING</h3>
                    <p class="service-description">Complete financial backing for tournament buy-ins, travel, accommodation, and all expenses. We remove the financial burden so you can focus entirely on performance and results.</p>
                    <a href="#" class="service-link">Learn More →</a>
                </div>
                
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-chalkboard-user"></i>
                    </div>
                    <h3 class="service-title">MENTORING</h3>
                    <p class="service-description">Join a rapidly growing global community where experienced players share strategies, guidance, and insider knowledge. Collaboration is encouraged. Every member learns, grows, and succeeds together.</p>
                    <a href="#" class="service-link">Learn More →</a>
                </div>
                
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <h3 class="service-title">TRAINING</h3>
                    <p class="service-description">Access to top poker academies, experienced coaches, and elite training programs. Master advanced strategy, refine your mental game, and learn how to navigate the professional circuit like a veteran.</p>
                    <a href="#" class="service-link">Learn More →</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Three Pillars Section -->
    <section class="pillars-section">
        <div class="pillars-container">
            <h2 class="pillars-title">The <span class="pillars-number">3</span> pillars that guide us</h2>
            
            <div class="pillars-grid">
                <div class="pillar-card">
                    <h3 class="pillar-title"><span class="pillar-number">1</span>DREAM</h3>
                    <p class="pillar-text">Every talented player deserves a shot at their dream, regardless of their bankroll. We eliminate financial barriers so skill can shine and ambition can take flight.</p>
                </div>
                
                <div class="pillar-card">
                    <h3 class="pillar-title pillar-title-blue"><span class="pillar-number pillar-number-blue">1</span>TEAM</h3>
                    <p class="pillar-text">You're never alone. From coaches to fellow players, you're part of a global community that supports you, challenges you, and celebrates every win with you.</p>
                </div>
                
                <div class="pillar-card">
                    <h3 class="pillar-title"><span class="pillar-number">1</span>FUTURE</h3>
                    <p class="pillar-text">We're building the next generation of poker champions. Your success is our success. We invest in you because we believe in your potential and your future.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <!-- Final CTA Section -->
            <section class="final-cta-section">
                <div class="final-cta-container">
                    <div class="final-cta-content">
                        <div class="final-cta-text">
                            <div class="final-cta-logo">POKER<span class="logo-red">111</span></div>
                            <h2 class="final-cta-title">Your Poker Career<br>Starts <span class="cta-highlight">Today</span></h2>
                            <p class="final-cta-subtitle">Join thousands of members already on their path to professional poker sponsorship</p>
                            <button class="btn-final-cta" onclick="openLoginModal()">START YOUR JOURNEY</button>
                        </div>
                        <div class="final-cta-avatars">
                            <div class="avatar-circle avatar-1">
                                <img src="<?php echo get_template_directory_uri(); ?>/imagens/profile/317a1645b8a25ae97e20496bada2f95a107c2f07.png" alt="Player">
                            </div>
                            <div class="avatar-circle avatar-2">
                                <img src="<?php echo get_template_directory_uri(); ?>/imagens/profile/415f578673f868b920c80d95b24fc3c17fd3fff7.png" alt="Player">
                            </div>
                            <div class="avatar-circle avatar-3">
                                <img src="<?php echo get_template_directory_uri(); ?>/imagens/profile/4aba288142527268c7be2ae7cc167532197831f7.png" alt="Player">
                            </div>
                            <div class="avatar-circle avatar-4">
                                <img src="<?php echo get_template_directory_uri(); ?>/imagens/profile/8a2f44c257b915e486e516fa8c581f2dc887838a.png" alt="Player">
                            </div>
                            <div class="avatar-circle avatar-5">
                                <img src="<?php echo get_template_directory_uri(); ?>/imagens/profile/92662c5b77d3c5165595688b2b5330922101c452.png" alt="Player">
                            </div>
                            <div class="avatar-circle avatar-6">
                                <img src="<?php echo get_template_directory_uri(); ?>/imagens/profile/9a86d9d0459ea457097923c5a164db423aa54d46.png" alt="Player">
                            </div>
                            <div class="avatar-circle avatar-7">
                                <img src="<?php echo get_template_directory_uri(); ?>/imagens/profile/af2c58dc7d4a767c664cd9a8ecee146afa710e4e.png" alt="Player">
                            </div>
                            <div class="avatar-circle avatar-8">
                                <img src="<?php echo get_template_directory_uri(); ?>/imagens/profile/317a1645b8a25ae97e20496bada2f95a107c2f07.png" alt="Player">
                            </div>
                            <div class="decoration-circle deco-1"></div>
                            <div class="decoration-circle deco-2"></div>
                            <div class="decoration-circle deco-3"></div>
                            <div class="decoration-circle deco-4"></div>
                            <div class="decoration-circle deco-5"></div>
                            <div class="decoration-circle" style="width: 100px; height: 100px; top: 2%; left: 2%; background: rgba(41, 98, 255, 0.15);"></div>
                            <div class="decoration-circle" style="width: 80px; height: 80px; top: 12%; left: 48%; background: rgba(41, 98, 255, 0.18);"></div>
                            <div class="decoration-circle" style="width: 120px; height: 120px; top: 45%; left: 15%; background: rgba(41, 98, 255, 0.12);"></div>
                            <div class="decoration-circle" style="width: 90px; height: 90px; bottom: 5%; left: 20%; background: rgba(41, 98, 255, 0.2);"></div>
                            <div class="decoration-circle" style="width: 110px; height: 110px; bottom: 35%; right: 35%; background: rgba(41, 98, 255, 0.15);"></div>
                            <div class="decoration-circle" style="width: 130px; height: 130px; top: 18%; right: 22%; background: rgba(41, 98, 255, 0.18);"></div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <!-- Login Modal -->
    <div id="loginModal" class="modal">
        <div class="modal-content">
            <div class="modal-top-bar">
                <img src="<?php echo get_template_directory_uri(); ?>/logopoker.png" alt="POKER III" class="modal-logo">
                <button class="modal-close" onclick="closeLoginModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="modal-header">
                <h2 class="modal-title" id="modalTitle">Welcome</h2>
                <p class="modal-subtitle" id="modalSubtitle">Fill in the fields to continue registration</p>
            </div>
            
            <div class="modal-tabs">
                <button class="tab-btn active" id="loginTab" onclick="switchTab('login')">Login</button>
                <button class="tab-btn" id="signupTab" onclick="switchTab('signup')">Sign Up</button>
            </div>
            
            <form class="modal-form" id="loginForm">
                <div class="form-group">
                    <input type="email" class="form-input" id="loginEmail" placeholder="Email" required oninput="checkFormFilled('login')">
                </div>
                
                <div class="form-group password-group">
                    <input type="password" class="form-input" id="loginPassword" placeholder="Password" required oninput="checkFormFilled('login')">
                    <button type="button" class="toggle-password" onclick="togglePassword('loginPassword')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                
                <a href="#" class="forgot-password" onclick="showForgotPassword(event)">Forgot Your Password?</a>
                
                <button type="submit" class="btn-continue" id="loginContinue" disabled>Continue</button>
                
                <div class="divider">
                    <span>Or</span>
                </div>
                
                <button type="button" class="btn-google" id="googleLoginBtn" onclick="handleGoogleLogin()">
                    <i class="fab fa-google"></i> Continue with Google
                </button>
            </form>
            
            <form class="modal-form" id="signupForm" style="display: none;">
                <div class="form-group">
                    <input type="email" class="form-input" id="signupEmail" placeholder="Email" required>
                </div>
                
                <div class="form-group password-group password-group-signup">
                    <input type="password" class="form-input" id="signupPassword" placeholder="Password" required oninput="validatePassword(this.value);">
                    <button type="button" class="toggle-password" onclick="togglePassword('signupPassword')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                
                <div class="password-requirements" id="passwordRequirements">
                    <p class="requirements-title">Your password must contain at least</p>
                    <div class="requirement" id="uppercaseReq">
                        <i class="fas fa-times"></i>
                        <span>1 uppercase letter</span>
                    </div>
                    <div class="requirement" id="lengthReq">
                        <i class="fas fa-times"></i>
                        <span>8 characters</span>
                    </div>
                </div>
                
                <button type="submit" class="btn-continue" id="signupContinue">Continue</button>
                
                <div class="divider">
                    <span>Or</span>
                </div>
                
                <button type="button" class="btn-google" id="googleSignupBtn" onclick="handleGoogleLogin()">
                    <i class="fab fa-google"></i> Continue with Google
                </button>
                
                <p class="terms-text">By creating an account you agree to domain.com's <a href="#">Terms of Use</a> and <a href="#">Privacy Policy</a></p>
            </form>
            
            <form class="modal-form" id="forgotPasswordForm" style="display: none;">
                <!-- Mensagem de Status -->
                <div id="forgotPasswordAlert" class="alert-message" style="display: none;"></div>
                
                <div class="form-group">
                    <input type="email" class="form-input" id="forgotEmail" placeholder="Enter your email" required>
                </div>
                
                <a href="#" class="forgot-password" id="backToLogin" onclick="backToLogin(event)">Back to Login</a>
                
                <button type="submit" class="btn-continue" id="sendEmailBtn">Send Email</button>
            </form>
            
            <form class="modal-form" id="signupDetailsForm" style="display: none;">
                <div class="form-group">
                    <input type="text" class="form-input" id="firstName" placeholder="First Name" required>
                </div>
                
                <div class="form-group">
                    <input type="text" class="form-input" id="lastName" placeholder="Last Name" required>
                </div>
                
                <div class="form-group phone-group">
                <select class="country-code-select" id="countryCode" onchange="updatePhonePlaceholder()">
                    <option value="+1">🇺🇸 +1 USA/Canada</option>
                    <option value="+7">🇷🇺 +7 Russia</option>
                    <option value="+27">🇿🇦 +27 South Africa</option>
                    <option value="+33">🇫🇷 +33 France</option>
                    <option value="+34">🇪🇸 +34 Spain</option>
                    <option value="+39">🇮🇹 +39 Italy</option>
                    <option value="+44">🇬🇧 +44 UK</option>
                    <option value="+49">🇩🇪 +49 Germany</option>
                    <option value="+51">🇵🇪 +51 Peru</option>
                    <option value="+52">🇲🇽 +52 Mexico</option>
                    <option value="+54">🇦🇷 +54 Argentina</option>
                    <option value="+55" selected>🇧🇷 +55 Brazil</option>
                    <option value="+56">🇨🇱 +56 Chile</option>
                    <option value="+57">🇨🇴 +57 Colombia</option>
                    <option value="+61">🇦🇺 +61 Australia</option>
                    <option value="+81">🇯🇵 +81 Japan</option>
                    <option value="+86">🇨🇳 +86 China</option>
                    <option value="+91">🇮🇳 +91 India</option>
                    <option value="+351">🇵🇹 +351 Portugal</option>
                    <option value="+380">🇺🇦 +380 Ukraine</option>
                </select>
                <input type="tel" class="form-input phone-input" id="phoneNumber" placeholder="(11) 94993-1617" maxlength="16" required oninput="formatPhoneNumber()">
            </div>
                
                <a href="#" class="forgot-password" id="backToSignup" onclick="backToSignup(event)">Back</a>
                
                <button type="submit" class="btn-continue" id="detailsContinue">Continue</button>
            </form>
            
            <!-- Modal de escolha de método de verificação -->
            <div class="modal-form" id="verificationMethodForm" style="display: none;">
                <h3 class="method-title" id="verificationMethodTitle">Choose Verification Method</h3>
                <p class="method-subtitle" id="verificationMethodSubtitle">How would you like to receive your verification code?</p>
                
                <div class="verification-methods">
                    <label class="verification-option" for="methodSMS">
                        <input type="radio" name="verificationMethod" id="methodSMS" value="sms" checked>
                        <div class="option-content">
                            <i class="fas fa-mobile-alt"></i>
                            <div class="option-text">
                                <span class="option-title">SMS</span>
                                <span class="option-desc">Receive code via text message</span>
                            </div>
                        </div>
                    </label>
                    
                    <label class="verification-option" for="methodEmail">
                        <input type="radio" name="verificationMethod" id="methodEmail" value="email">
                        <div class="option-content">
                            <i class="fas fa-envelope"></i>
                            <div class="option-text">
                                <span class="option-title">Email</span>
                                <span class="option-desc">Receive code in your inbox</span>
                            </div>
                        </div>
                    </label>
                </div>
                
                <button type="button" class="btn-continue" id="confirmMethodBtn" onclick="confirmVerificationMethod()">Continue</button>
                
                <a href="#" class="forgot-password" id="backToDetails" onclick="backToPersonalDetails(event)">Back</a>
            </div>
            
            <form class="modal-form" id="phoneVerificationForm" style="display: none;">
                <p class="verification-phone" id="verificationPhone">+357 999 99 99</p>
                
                <p class="verification-instruction" id="verificationInstruction">
                    Please enter the code below to confirm your phone number. Make sure to keep this window open while you check your phone. The code may take up to 10 minutes to arrive.
                </p>
                
                <div class="form-group">
                    <input type="text" class="form-input code-input" id="verificationCode" placeholder="— — — —" maxlength="4" pattern="[0-9]{4}" required oninput="formatCodeInput()">
                </div>
                
                <button type="button" class="btn-resend" id="resendBtn" disabled>Resend in 118 sec...</button>
                
                <a href="#" class="forgot-password" id="changePhoneNumber" onclick="changePhoneNumber(event)">Change phone number</a>
            </form>
            
            <div class="modal-form" id="almostDoneScreen" style="display: none;">
                <p class="almost-done-text">
                    <span id="almostDoneSubtitle1">Thanks for confirming your email! You're almost there - just a quick</span>
                    <span id="almostDoneSubtitle2">step left to unlock all the awesome benefits we offer.</span>
                </p>
                
                <p class="almost-done-help" id="almostDoneHelp">Help us out by taking a short screening questionnaire.</p>
                
                <div class="almost-done-buttons">
                    <button type="button" class="btn-ask-later" id="askLaterBtn" onclick="askMeLater()">Ask me later</button>
                    <button type="button" class="btn-lets-go" id="letsGoBtn" onclick="letsGo()">Let's go</button>
                </div>
            </div>
        </div>
    </div>

<!-- Passar dados PHP para JavaScript -->
<script>
// Dados do usuário da sessão PHP
window.phpUserData = {
    isLoggedIn: <?php echo json_encode($isLoggedIn); ?>,
    userName: <?php echo json_encode($userName); ?>,
    userEmail: <?php echo json_encode($userEmail); ?>,
    userId: <?php echo json_encode($userId); ?>
};
</script>

<!-- Navigation JS (funções do avatar dropdown e logout) -->
<script src="<?php echo get_template_directory_uri(); ?>/js/navigation.js"></script>

<?php get_footer(); ?>
</body>
</html>

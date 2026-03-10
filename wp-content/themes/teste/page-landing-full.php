<?php
/**
 * Template Name: Landing Page Completa
 * Description: Landing page do Poker111 com todo o conteúdo
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
            <h1 class="hero-title">
                <span class="hero-number">1</span> DREAM 
                <span class="hero-number">1</span> TEAM 
                <span class="hero-number">1</span> FUTURE
            </h1>
            <p class="hero-subtitle">Join the next generation of elite Brazilian poker players.</p>
            <button class="btn-hero" onclick="openLoginModal()">START YOUR JOURNEY</button>
        </div>
    </section>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <div class="features-section">
                <div class="feature-card">
                    <div class="feature-icon">
                        <img src="<?php echo get_template_directory_uri(); ?>/imagens/1dream.gif" alt="Dream Icon" style="width: 60px; height: 60px;">
                    </div>
                    <h2 class="feature-title"><span class="feature-number">1</span><span class="hero-text">DREAM</span></h2>
                    <p class="feature-text">Every great player begins with a vision. At Poker111, we turn that vision into action, providing guidance to help you start strong and grow quickly as a professional player.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <img src="<?php echo get_template_directory_uri(); ?>/imagens/1team.gif" alt="Team Icon" style="width: 60px; height: 60px;">
                    </div>
                    <h2 class="feature-title"><span class="feature-number">1</span>TEAM</h2>
                    <p class="feature-text">At Poker111, success is a team effort. Join champions with world-class coaching and a community that drives you forward. Together, we grow, rise, and win.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <img src="<?php echo get_template_directory_uri(); ?>/imagens/1future.gif" alt="Future Icon" style="width: 60px; height: 60px;">
                    </div>
                    <h2 class="feature-title"><span class="feature-number">1</span>FUTURE</h2>
                    <p class="feature-text">Build a sustainable poker career with no financial risk. One Future helps you turn your passion for poker into a reliable income that supports you and your family.</p>
                </div>
            </div>

            <!-- Empowerment Section -->
            <section class="empowerment-section">
                <div class="empowerment-photos-left">
                    <div class="empowerment-photo">
                        <img src="<?php echo get_template_directory_uri(); ?>/imagens/poker-photo1.jpg" alt="Poker Player">
                    </div>
                    <div class="empowerment-photo">
                        <img src="<?php echo get_template_directory_uri(); ?>/imagens/poker-photo2.png" alt="Poker Player">
                    </div>
                </div>

                <div class="empowerment-photos-right">
                    <div class="empowerment-photo">
                        <img src="<?php echo get_template_directory_uri(); ?>/imagens/poker-photo4.png" alt="Poker Player">
                    </div>
                    <div class="empowerment-photo">
                        <img src="<?php echo get_template_directory_uri(); ?>/imagens/poker-photo3.png" alt="Poker Player">
                    </div>
                </div>

                <div class="empowerment-container">
                    <h2 class="empowerment-title">We empower talent, accelerate growth, and create real opportunities.</h2>
                    <p class="empowerment-subtitle">This is who we are. This is the new era of poker.</p>
                    <button class="btn-empowerment" onclick="openLoginModal()">START YOUR JOURNEY</button>
                </div>
            </section>

            <!-- Champions Section -->
            <section class="champions-section">
                <div class="champions-container">
                    <h2 class="champions-title">A Home For Brazil's Next <span class="highlight">Poker Champions</span></h2>
                    
                    <div class="champions-top-row">
                        <div class="champions-box">
                            <p class="champions-box-title">OUR MISSION</p>
                            <p class="champions-box-text">Poker111 aims to break down barriers for Brazil players making it into the big leagues with <strong>financial support, top-notch coaching, and a community</strong> that believes in every player's potential.</p>
                        </div>
                        
                        <div class="champions-box-image">
                            <img src="<?php echo get_template_directory_uri(); ?>/imagens/img3.jpg" alt="Poker Hands">
                        </div>
                        
                        <div class="champions-box">
                            <p class="champions-box-subtitle">1 DREAM 1 TEAM 1 FUTURE</p>
                            <p class="champions-box-text">More than a team, we are a <strong>home for those who live and breathe poker</strong> - a place where dream, unity, and future walk side by side.</p>
                        </div>
                    </div>
                    
                    <div class="champions-bottom-row">
                        <div class="champions-bottom-image">
                            <img src="<?php echo get_template_directory_uri(); ?>/imagens/img2.jpg" alt="Tournament Stage">
                        </div>
                        
                        <div class="champions-box">
                            <p class="champions-box-title">TOURNAMENT SPONSORSHIPS</p>
                            <p class="champions-box-text">We sponsor <strong>big-live for live and online tournaments</strong>, giving you opportunities to grow, learn, and compete, grow, and reach their goals without financial limitations holding them back.</p>
                        </div>
                        
                        <div class="champions-bottom-image">
                            <img src="<?php echo get_template_directory_uri(); ?>/imagens/img1.png" alt="Poker Player">
                        </div>
                    </div>
                </div>
            </section>

            <!-- Stats Section -->
            <section class="stats-section">
                <div class="stats-container">
                    <div class="stat-item">
                        <h3 class="stat-number">1,000+</h3>
                        <p class="stat-label">Club Members</p>
                    </div>
                    <div class="stat-item">
                        <h3 class="stat-number">R$415K</h3>
                        <p class="stat-label">Biggest Win</p>
                    </div>
                    <div class="stat-item">
                        <h3 class="stat-number">15+</h3>
                        <p class="stat-label">Sponsored Players</p>
                    </div>
                    <div class="stat-item">
                        <h3 class="stat-number">100+</h3>
                        <p class="stat-label">Academy Lessons</p>
                    </div>
                </div>
            </section>

            <!-- Where We Send You Section -->
            <section class="tournaments-section">
                <div class="tournaments-content">
                    <div class="tournaments-left">
                        <h2 class="tournaments-title">Where We Send You</h2>
                        <p class="tournaments-subtitle">From online grinder to global tournament player</p>
                        <button class="btn-tournament" onclick="openLoginModal()">START YOUR JOURNEY</button>
                    </div>
                    
                    <div class="tournaments-right">
                        <div class="tournament-card">
                            <div class="tournament-card-image">
                                <img src="<?php echo get_template_directory_uri(); ?>/imagens/efd7b12362760f6e054f87620d47088dcdc1f12e.png" alt="BSOP Winter Millions">
                                <div class="tournament-icon">
                                    <img src="<?php echo get_template_directory_uri(); ?>/imagens/a85e03996c342b1312a2e7bdd7c3d4f8ca70fcc4.png" alt="BSOP">
                                </div>
                            </div>
                            <div class="tournament-card-content">
                                <h3 class="tournament-card-title">BSOP Winter Millions</h3>
                                <p class="tournament-card-text">Brazil's biggest poker championship with players from 44 countries</p>
                            </div>
                        </div>
                        
                        <div class="tournament-card">
                            <div class="tournament-card-image">
                                <img src="<?php echo get_template_directory_uri(); ?>/imagens/5c117bd7cd2b9479c98093e82f3e7dd07a3fd4b8.png" alt="KSOP sao paulo">
                                <div class="tournament-icon">
                                    <img src="<?php echo get_template_directory_uri(); ?>/imagens/872cc801c9664db9d91897b3dc12c98dd0081197.png" alt="KSOP">
                                </div>
                            </div>
                            <div class="tournament-card-content">
                                <h3 class="tournament-card-title">KSOP sao paulo</h3>
                                <p class="tournament-card-text">The dream destination - World Series of Poker</p>
                            </div>
                        </div>
                        
                        <div class="tournament-card">
                            <div class="tournament-card-image">
                                <img src="<?php echo get_template_directory_uri(); ?>/imagens/7c698de8bf573c89356d000c7bad492d464cf684.png" alt="EPT Tournaments">
                                <div class="tournament-icon ept-icon">
                                    <img src="<?php echo get_template_directory_uri(); ?>/imagens/88731d5afdbf479deee56d5f78d08778f3aaea72.png" alt="EPT">
                                </div>
                            </div>
                            <div class="tournament-card-content">
                                <h3 class="tournament-card-title">EPT Tournaments</h3>
                                <p class="tournament-card-text">Compete with the best in Europe's poker capital</p>
                            </div>
                        </div>
                        
                        <div class="tournament-card">
                            <div class="tournament-card-image">
                                <img src="<?php echo get_template_directory_uri(); ?>/imagens/5c117bd7cd2b9479c98093e82f3e7dd07a3fd4b8.png" alt="KSOP sao paulo">
                                <div class="tournament-icon">
                                    <img src="<?php echo get_template_directory_uri(); ?>/imagens/872cc801c9664db9d91897b3dc12c98dd0081197.png" alt="KSOP">
                                </div>
                            </div>
                            <div class="tournament-card-content">
                                <h3 class="tournament-card-title">KSOP sao paulo</h3>
                                <p class="tournament-card-text">The dream destination - World Series of Poker</p>
                            </div>
                        </div>
                        
                        <div class="tournament-card">
                            <div class="tournament-card-image">
                                <img src="<?php echo get_template_directory_uri(); ?>/imagens/7c698de8bf573c89356d000c7bad492d464cf684.png" alt="EPT Tournaments">
                                <div class="tournament-icon ept-icon">
                                    <img src="<?php echo get_template_directory_uri(); ?>/imagens/88731d5afdbf479deee56d5f78d08778f3aaea72.png" alt="EPT">
                                </div>
                            </div>
                            <div class="tournament-card-content">
                                <h3 class="tournament-card-title">EPT Tournaments</h3>
                                <p class="tournament-card-text">Compete with the best in Europe's poker capital</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- What Makes Us Unique Section -->
            <section class="unique-section">
                <div class="unique-container">
                    <div class="unique-header">
                        <h2 class="unique-title">What Makes Us Unique</h2>
                        <p class="unique-description">Joining Poker111 lets you turn dreams into reality with support from those who believe in you. We create ideal conditions for new players to grow and shine in poker, providing structure and real opportunities for talent to thrive.</p>
                    </div>
                    
                    <div class="unique-grid">
                        <div class="unique-card">
                            <div class="unique-icon">
                                <i class="fas fa-hand-holding-usd"></i>
                            </div>
                            <h3 class="unique-card-title">FINANCIAL BACKING</h3>
                            <p class="unique-card-text">Poker111 invests in players' futures by covering buy-ins and expenses, letting them focus on studying and competing without financial worries.</p>
                        </div>
                        
                        <div class="unique-card">
                            <div class="unique-icon">
                                <i class="fas fa-chalkboard-teacher"></i>
                            </div>
                            <h3 class="unique-card-title">WORLD-CLASS COACHING</h3>
                            <p class="unique-card-text">Poker111 players receive mentoring from experienced pros who refine their technical and mental skills for global poker challenges.</p>
                        </div>
                        
                        <div class="unique-card">
                            <div class="unique-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <h3 class="unique-card-title">TEAM SUPPORT</h3>
                            <p class="unique-card-text">Joining Poker111 means being part of a supportive community. Our team offers hand reviews, strategy, and support, ensuring players feel connected.</p>
                        </div>
                        
                        <div class="unique-card">
                            <div class="unique-icon">
                                <i class="fas fa-file-contract"></i>
                            </div>
                            <h3 class="unique-card-title">GUARANTEED CONTRACTS</h3>
                            <p class="unique-card-text">Players benefit from clear contracts that ensure transparency. Poker111 values trust and professionalism, providing stability for growth.</p>
                        </div>
                        
                        <div class="unique-card">
                            <div class="unique-icon">
                                <i class="fas fa-trophy"></i>
                            </div>
                            <h3 class="unique-card-title">LIVE TOURNAMENTS</h3>
                            <p class="unique-card-text">Poker111 assists players in live events with logistical and financial support, giving them opportunities to compete and gain visibility.</p>
                        </div>
                        
                        <div class="unique-card">
                            <div class="unique-icon">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <h3 class="unique-card-title">ZERO RISK</h3>
                            <p class="unique-card-text">At Poker111, players face no risk as the company covers all investments, allowing them to focus on learning and career building.</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Our Featured Players Section -->
            <section class="featured-players-section">
                <div class="featured-players-container">
                    <h2 class="featured-players-title">Our Featured Players</h2>
                    
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
                                        <p class="player-quote">I fell in love with the vision and the team - and Poker111 made it real. They flew me to EPT Barcelona! Thank you, Poker111. I couldn't have done this without you"</p>
                                    </div>
                                </div>
                                <div class="player-card">
                                    <img src="<?php echo get_template_directory_uri(); ?>/imagens/players/Raphael.webp" alt="Player 7">
                                    <div class="player-overlay">
                                        <h3 class="player-name">Raphael Garcia </h3>
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

            <!-- FAQ Section -->
            <section class="faq-section">
                <div class="faq-container">
                    <h2 class="faq-title">Questions? We've Got Answers</h2>
                    
                    <div class="faq-list">
                        <div class="faq-item">
                            <div class="faq-question" onclick="toggleFaq(this)">
                                <span>How can I turn my passion for poker into a legitimate, successful, long-term career?</span>
                                <button class="faq-toggle">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                            <div class="faq-answer">
                                <p>We provide you with a blueprint for a sustainable professional poker career through strategic partnerships, full financial backing, elite coaching, and a structured path to success. In return, we require your professional commitment, brand presence, and participation in the results.</p>
                            </div>
                        </div>

                        <div class="faq-item">
                            <div class="faq-question" onclick="toggleFaq(this)">
                                <span>What is the fastest way to get scouted and start your professional sponsorship journey?</span>
                                <button class="faq-toggle">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                            <div class="faq-answer">
                                <p>No details.</p>
                            </div>
                        </div>

                        <div class="faq-item">
                            <div class="faq-question" onclick="toggleFaq(this)">
                                <span>Can I really play high-stakes tournaments like the BSOP, KSOP, or EPT without risking my own money?</span>
                                <button class="faq-toggle">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                            <div class="faq-answer">
                                <p>No details.</p>
                            </div>
                        </div>

                        <div class="faq-item">
                            <div class="faq-question" onclick="toggleFaq(this)">
                                <span>What sets Poker111's training apart from just studying and grinding on my own?</span>
                                <button class="faq-toggle">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                            <div class="faq-answer">
                                <p>No details.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

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

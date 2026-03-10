<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <?php
    // Incluir conexao.php para ter acesso ao GOOGLE_CLIENT_ID
    require_once dirname(__FILE__) . '/../../../conexao/conexao.php';
    require_once dirname(__FILE__) . '/../../../helpers/TicketManager.php';
    ?>
    
    <?php wp_head(); ?>
    
    <!-- Google Identity Services - Carregado após wp_head -->
    <script src="https://accounts.google.com/gsi/client"></script>
    
    <script>
    // Configuração do Google OAuth
    const GOOGLE_CLIENT_ID = '<?php echo defined("GOOGLE_CLIENT_ID") ? GOOGLE_CLIENT_ID : ""; ?>';
    console.log('🔑 Google Client ID configurado:', GOOGLE_CLIENT_ID ? 'SIM' : 'NÃO');
    </script>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<?php
// Iniciar sessão se não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar se o usuário está logado
$isLoggedIn = isset($_SESSION['logged_in']) && ($_SESSION['logged_in'] === true || $_SESSION['logged_in'] == 1);
$userFullName = $isLoggedIn && isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'User';
$userName = explode(' ', $userFullName)[0]; // Pegar apenas o primeiro nome
$userEmail = $isLoggedIn && isset($_SESSION['user_email']) ? $_SESSION['user_email'] : '';
$userId = $isLoggedIn && isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Buscar avatar e tickets do usuário do banco de dados
$userAvatar = 'avatar1.webp'; // Padrão
$userTickets = 0;

if ($isLoggedIn && $userId) {
    try {
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
        error_log('Erro ao buscar avatar do usuário no header: ' . $e->getMessage());
    }
}
?>

<!-- Login Modal (Global) -->
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
                <input type="email" class="form-input" id="loginEmail" placeholder="|Email" required oninput="checkFormFilled('login')">
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
                <input type="email" class="form-input" id="signupEmail" placeholder="|Email" required>
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
            <div class="form-group">
                <input type="email" class="form-input" id="forgotEmail" placeholder="Enter your email" required>
            </div>
            
            <!-- Área de alerta para mensagens de sucesso/erro -->
            <div id="forgotPasswordAlert" class="alert-message" style="display: none;"></div>
            
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

<?php
// ─── Top Banner condicional ───────────────────────────────────────────────────
// Adicione aqui os slugs/IDs das páginas onde o banner deve aparecer.
$top_banner_pages = ['faq', 'application-process', 'sponsorchip', 'sponsor-chip', 'scouting', 'mentor-ship', 'training', 'blog', 'contact'];
if (is_page($top_banner_pages)) : ?>
<!-- Top Banner -->
<div class="top-banner">
    <div class="top-banner-content">
        <div class="banner-text">
            <span class="banner-title">Next Poker Challenge </span>
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
<?php endif; ?>

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

                <!-- Mobile User Dropdown (shown when logged in) -->
                <?php if ($isLoggedIn): ?>
                <div class="nav-dropdown mobile-user-nav-dropdown" id="mobileUserNavDropdown">
                    <a href="#" class="nav-link" onclick="toggleNavDropdown(event, 'mobileUserDropdownMenu')">
                        <i class="fas fa-user-circle" style="margin-right: 8px;"></i>
                        HI, <?php echo strtoupper(htmlspecialchars($userName)); ?> 
                        <i class="fas fa-chevron-down"></i>
                    </a>
                    <div class="nav-dropdown-menu" id="mobileUserDropdownMenu">
                        <a href="<?php echo esc_url(site_url('/challenges/?tab=settings')); ?>" class="nav-dropdown-item">My Profile</a>
                        <a href="<?php echo esc_url(site_url('/challenges/')); ?>" class="nav-dropdown-item">Challenges</a>
                        <a href="#" class="nav-dropdown-item" onclick="openChangePasswordModal(event)">Alterar Senha</a>
                        <a href="#" class="nav-dropdown-item" onclick="logout(event)">Logout</a>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Mobile Action Buttons (apenas mobile) -->
                <div class="mobile-actions">
                    <!-- Login Button (hidden when logged in) -->
                    <a href="#" class="mobile-btn-login" id="mobileBtnLogin" onclick="openLoginModal(event)" <?php echo $isLoggedIn ? 'style="display: none;"' : ''; ?>>LOG IN</a>
                    <?php if (!$isLoggedIn): ?>
                    <a href="<?php echo esc_url(site_url('/plans/')); ?>" class="mobile-btn-get-started">GET STARTED</a>
                    <?php endif; ?>
                </div>
            </nav>

            <!-- Desktop Actions -->
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
                                <a href="#" class="user-avatar-menu-item" onclick="openChangePasswordModal(event)">
                                    <i class="fas fa-key"></i> Alterar Senha
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
                <a href="<?php echo esc_url(site_url('/plans/')); ?>" class="btn-get-started">GET STARTED</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>

<!-- Change Password Modal -->
<div id="changePasswordModal" class="modal change-password-modal">
    <div class="modal-content change-password-content">
        <div class="modal-top-bar">
            <img src="<?php echo get_template_directory_uri(); ?>/logopoker.png" alt="POKER III" class="modal-logo">
            <button class="modal-close" onclick="closeChangePasswordModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="modal-header">
            <div class="password-icon">
                <i class="fas fa-lock"></i>
            </div>
            <h2 class="modal-title">Alterar Senha</h2>
            <p class="modal-subtitle-password">Digite sua senha atual e escolha uma nova senha segura.</p>
        </div>
        
        <form id="changePasswordForm" class="password-form" onsubmit="handleChangePassword(event)">
            <div class="password-field-group">
                <label for="currentPassword" class="password-label">Senha Atual</label>
                <div class="password-input-wrapper">
                    <input type="password" id="currentPassword" name="currentPassword" class="password-input" placeholder="Digite sua senha atual" required>
                    <button type="button" class="toggle-password-visibility" onclick="togglePasswordVisibility('currentPassword')">
                        <i class="far fa-eye"></i>
                    </button>
                </div>
            </div>
            
            <div class="password-field-group">
                <label for="newPassword" class="password-label">Nova Senha</label>
                <div class="password-input-wrapper">
                    <input type="password" id="newPassword" name="newPassword" class="password-input" placeholder="Digite sua nova senha" required minlength="8">
                    <button type="button" class="toggle-password-visibility" onclick="togglePasswordVisibility('newPassword')">
                        <i class="far fa-eye"></i>
                    </button>
                </div>
                <div class="password-strength" id="passwordStrength">
                    <div class="strength-bar">
                        <div class="strength-bar-fill" id="strengthBarFill"></div>
                    </div>
                    <span class="strength-text" id="strengthText"></span>
                </div>
            </div>
            
            <div class="password-field-group">
                <label for="confirmPassword" class="password-label">Confirmar Nova Senha</label>
                <div class="password-input-wrapper">
                    <input type="password" id="confirmPassword" name="confirmPassword" class="password-input" placeholder="Digite novamente a nova senha" required minlength="8">
                    <button type="button" class="toggle-password-visibility" onclick="togglePasswordVisibility('confirmPassword')">
                        <i class="far fa-eye"></i>
                    </button>
                </div>
                <div class="password-match-indicator" id="passwordMatchIndicator"></div>
            </div>
            
            <div class="password-requirements">
                <p class="requirements-title">Sua senha deve conter:</p>
                <ul class="requirements-list">
                    <li id="req-length"><i class="fas fa-circle"></i> Mínimo de 8 caracteres</li>
                    <li id="req-uppercase"><i class="fas fa-circle"></i> Pelo menos uma letra maiúscula</li>
                    <li id="req-lowercase"><i class="fas fa-circle"></i> Pelo menos uma letra minúscula</li>
                    <li id="req-number"><i class="fas fa-circle"></i> Pelo menos um número</li>
                </ul>
            </div>
            
            <div class="password-modal-actions">
                <button type="button" class="btn-cancel-password" onclick="closeChangePasswordModal()">Cancelar</button>
                <button type="submit" class="btn-save-password">Alterar Senha</button>
            </div>
        </form>
    </div>
</div>



<script>
// ============================================================================
// MODAL DE ALTERAÇÃO DE SENHA
// ============================================================================

// Função para mostrar alerta no modal de senha
function showPasswordAlert(message, type = 'success') {
    // Remover alertas anteriores
    const existingAlert = document.querySelector('.password-form .modal-alert');
    if (existingAlert) {
        existingAlert.remove();
    }
    
    // Criar novo alerta
    const alert = document.createElement('div');
    alert.className = `modal-alert alert-${type}`;
    
    const icon = document.createElement('i');
    icon.className = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle';
    
    const textSpan = document.createElement('span');
    textSpan.textContent = message;
    
    alert.appendChild(icon);
    alert.appendChild(textSpan);
    
    // Inserir no início do formulário
    const form = document.querySelector('.password-form');
    form.insertBefore(alert, form.firstChild);
    
    // Remover após 5 segundos
    setTimeout(() => {
        alert.remove();
    }, 5000);
}

// Função para abrir modal de alteração de senha
function openChangePasswordModal(event) {
    if (event) event.preventDefault();
    const modal = document.getElementById('changePasswordModal');
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    
    // Fechar menu dropdown se estiver aberto
    const userMenu = document.getElementById('userAvatarMenu');
    if (userMenu) {
        userMenu.classList.remove('active');
    }
    
    // Fechar menu mobile se estiver aberto
    const mobileMenu = document.getElementById('navMenu');
    if (mobileMenu && mobileMenu.classList.contains('active')) {
        mobileMenu.classList.remove('active');
    }
}

// Função para fechar modal de alteração de senha
function closeChangePasswordModal() {
    const modal = document.getElementById('changePasswordModal');
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
    
    // Limpar formulário
    document.getElementById('changePasswordForm').reset();
    const strengthContainer = document.getElementById('passwordStrength');
    if (strengthContainer) {
        strengthContainer.style.display = 'none';
    }
    document.getElementById('passwordMatchIndicator').textContent = '';
    
    // Resetar requisitos
    const requirements = ['req-length', 'req-uppercase', 'req-lowercase', 'req-number'];
    requirements.forEach(req => {
        const el = document.getElementById(req);
        if (el) {
            el.classList.remove('met');
        }
    });
}

// Função para alternar visibilidade da senha
function togglePasswordVisibility(fieldId) {
    const field = document.getElementById(fieldId);
    const button = field.parentElement.querySelector('.toggle-password-visibility');
    const icon = button.querySelector('i');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Função para verificar força da senha
function checkPasswordStrength(password) {
    let strength = 0;
    const requirements = {
        length: password.length >= 8,
        uppercase: /[A-Z]/.test(password),
        lowercase: /[a-z]/.test(password),
        number: /[0-9]/.test(password),
        special: /[!@#$%^&*(),.?":{}|<>]/.test(password)
    };
    
    // Atualizar visual dos requisitos
    const reqLength = document.getElementById('req-length');
    const reqUppercase = document.getElementById('req-uppercase');
    const reqLowercase = document.getElementById('req-lowercase');
    const reqNumber = document.getElementById('req-number');
    
    if (reqLength) reqLength.classList.toggle('met', requirements.length);
    if (reqUppercase) reqUppercase.classList.toggle('met', requirements.uppercase);
    if (reqLowercase) reqLowercase.classList.toggle('met', requirements.lowercase);
    if (reqNumber) reqNumber.classList.toggle('met', requirements.number);
    
    // Calcular força
    if (requirements.length) strength++;
    if (requirements.uppercase) strength++;
    if (requirements.lowercase) strength++;
    if (requirements.number) strength++;
    if (requirements.special) strength++;
    
    return { strength, requirements };
}

// Função para atualizar indicador de força da senha
function updatePasswordStrength(password) {
    const { strength } = checkPasswordStrength(password);
    const strengthBar = document.getElementById('strengthBarFill');
    const strengthText = document.getElementById('strengthText');
    const strengthContainer = document.getElementById('passwordStrength');
    
    if (password.length === 0) {
        strengthContainer.style.display = 'none';
        return;
    }
    
    strengthContainer.style.display = 'block';
    
    const percentage = (strength / 5) * 100;
    strengthBar.style.width = percentage + '%';
    
    // Remover todas as classes
    strengthBar.className = 'strength-bar-fill';
    
    if (strength <= 2) {
        strengthBar.classList.add('weak');
        strengthText.textContent = 'Senha fraca';
        strengthText.style.color = '#ff4444';
    } else if (strength === 3) {
        strengthBar.classList.add('medium');
        strengthText.textContent = 'Senha média';
        strengthText.style.color = '#FFA726';
    } else {
        strengthBar.classList.add('strong');
        strengthText.textContent = 'Senha forte';
        strengthText.style.color = '#4CAF50';
    }
}

// Função para verificar se as senhas coincidem
function checkPasswordMatch() {
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    const indicator = document.getElementById('passwordMatchIndicator');
    
    if (confirmPassword.length === 0) {
        indicator.textContent = '';
        return;
    }
    
    if (newPassword === confirmPassword) {
        indicator.textContent = '✓ As senhas coincidem';
        indicator.style.color = '#4CAF50';
    } else {
        indicator.textContent = '✗ As senhas não coincidem';
        indicator.style.color = '#ff4444';
    }
}

// Função para processar alteração de senha
function handleChangePassword(event) {
    event.preventDefault();
    
    const currentPassword = document.getElementById('currentPassword').value;
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    
    // Validar se as senhas coincidem
    if (newPassword !== confirmPassword) {
        showPasswordAlert('As senhas não coincidem. Por favor, verifique.', 'error');
        return;
    }
    
    // Validar requisitos da senha
    const { requirements } = checkPasswordStrength(newPassword);
    if (!requirements.length || !requirements.uppercase || !requirements.lowercase || !requirements.number) {
        showPasswordAlert('A nova senha não atende aos requisitos de segurança.', 'error');
        return;
    }
    
    // Fazer chamada AJAX para o backend
    const apiBaseUrl = '<?php echo esc_url(site_url("/api/")); ?>';
    
    fetch(apiBaseUrl + 'change-password.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            currentPassword: currentPassword,
            newPassword: newPassword
        })
    })
    .then(response => {
        // Verificar se a resposta é JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            throw new Error('Resposta não é JSON. Verifique o arquivo PHP.');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showPasswordAlert('Senha alterada com sucesso!', 'success');
            // Fechar modal após 2 segundos
            setTimeout(() => {
                closeChangePasswordModal();
            }, 2000);
        } else {
            // Mostrar mensagem de erro detalhada
            let errorMsg = data.message || 'Erro desconhecido';
            if (data.debug) {
                console.error('Debug:', data.debug);
                console.error('Error Type:', data.error_type);
            }
            showPasswordAlert(errorMsg, 'error');
        }
    })
    .catch(error => {
        console.error('Erro completo:', error);
        showPasswordAlert('Erro ao processar solicitação. Verifique o console.', 'error');
    });
}

// Event listeners para o modal de senha
document.addEventListener('DOMContentLoaded', function() {
    // Verificar força da senha em tempo real
    const newPasswordInput = document.getElementById('newPassword');
    if (newPasswordInput) {
        newPasswordInput.addEventListener('input', function() {
            updatePasswordStrength(this.value);
            checkPasswordMatch();
        });
    }
    
    // Verificar se as senhas coincidem
    const confirmPasswordInput = document.getElementById('confirmPassword');
    if (confirmPasswordInput) {
        confirmPasswordInput.addEventListener('input', checkPasswordMatch);
    }
    
    // Fechar modal ao clicar fora
    const changePasswordModal = document.getElementById('changePasswordModal');
    if (changePasswordModal) {
        window.addEventListener('click', function(event) {
            if (event.target === changePasswordModal) {
                closeChangePasswordModal();
            }
        });
    }
});
</script>

<?php
/**
 * Template Name: Desafios
 * Description: Página de desafios e conta do usuário
 */

// Iniciar sessão se não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================================================
// IMPORTAR CONEXÃO DO ARQUIVO conexao.php
// ============================================================================
$conexao_encontrada = false;

// Lista de caminhos possíveis onde o arquivo conexao.php pode estar
$caminhos_conexao = [
    __DIR__ . '/../conexao/conexao.php',                          // Relativo ao tema (local)
    __DIR__ . '/../../conexao/conexao.php',                       // Dois níveis acima
    $_SERVER['DOCUMENT_ROOT'] . '/conexao/conexao.php',           // Raiz do site
    $_SERVER['DOCUMENT_ROOT'] . '/Poker5/conexao/conexao.php',    // Pasta Poker5 local
    '/home/poker/public_html/conexao/conexao.php',                // Servidor produção
    dirname(ABSPATH) . '/conexao/conexao.php',                    // Fora do WordPress
    ABSPATH . '../conexao/conexao.php',                           // Um nível acima do WP
    ABSPATH . 'conexao/conexao.php',                              // Dentro do WordPress
];

foreach ($caminhos_conexao as $caminho) {
    if (file_exists($caminho)) {
        require_once $caminho;
        $conexao_encontrada = true;
        break;
    }
}

// Verificar se usuário está logado e buscar dados
$usuario_logado = false;
$usuario_dados = null;

// Verificar sessão - aceitar tanto boolean true quanto string '1' ou inteiro 1
$is_logged_in = isset($_SESSION['logged_in']) && ($_SESSION['logged_in'] === true || $_SESSION['logged_in'] == 1 || $_SESSION['logged_in'] === '1');
$has_user_id = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);

if ($is_logged_in && $has_user_id && $conexao_encontrada) {
    try {
        // Usar a função de conexão do arquivo conexao.php
        $pdo = getConnection();
        
        // Buscar dados do usuário
        $stmt = $pdo->prepare("
            SELECT 
                id,
                email,
                primeiro_nome,
                sobrenome,
                nickname,
                url_avatar,
                telefone,
                codigo_pais,
                tipo_conta,
                status,
                plano_inicio,
                plano_termino
            FROM usuarios
            WHERE id = ?
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $usuario_dados = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($usuario_dados) {
            $usuario_logado = true;
            
            // Se não tiver nickname, gerar um automático
            if (empty($usuario_dados['nickname'])) {
                $usuario_dados['nickname'] = $usuario_dados['primeiro_nome'] . substr((string)$usuario_dados['id'], -3);
            }
            
            // Se não tiver avatar, usar o padrão
            if (empty($usuario_dados['url_avatar'])) {
                $usuario_dados['url_avatar'] = 'avatar1.webp';
            }
        }
        
    } catch (PDOException $e) {
        error_log('Erro ao buscar dados do usuário: ' . $e->getMessage());
        $usuario_logado = false;
    }
}

// ============================================================================
// FUNÇÃO PARA FORMATAR DATAS EM PORTUGUÊS
// ============================================================================
function formatarDataPTBR($data, $mostrarHoje = true) {
    if (empty($data)) {
        return '';
    }
    
    $timestamp = strtotime($data);
    if ($timestamp === false) {
        return '';
    }
    
    // Se a data é EXATAMENTE hoje e $mostrarHoje é true, mostrar "Hoje"
    if ($mostrarHoje && date('Y-m-d', $timestamp) == date('Y-m-d')) {
        return 'Hoje';
    }
    
    $meses = [
        1 => 'janeiro', 2 => 'fevereiro', 3 => 'março', 4 => 'abril',
        5 => 'maio', 6 => 'junho', 7 => 'julho', 8 => 'agosto',
        9 => 'setembro', 10 => 'outubro', 11 => 'novembro', 12 => 'dezembro'
    ];
    
    $dia = date('j', $timestamp);
    $mes = $meses[(int)date('n', $timestamp)];
    $ano = date('Y', $timestamp);
    
    return "$dia de $mes de $ano";
}

// ============================================================================
// CAPTURAR PARÂMETRO DA URL PARA TABS
// ============================================================================
$tab_ativa = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'challenges';
$tabs_validas = ['challenges', 'billing', 'affiliation', 'settings'];
if (!in_array($tab_ativa, $tabs_validas)) {
    $tab_ativa = 'challenges';
}

get_header();
?>

<!-- Passar variáveis para JavaScript -->
<script>
    var tabAtiva = '<?php echo esc_js($tab_ativa); ?>';
    var usuarioLogado = <?php echo $usuario_logado ? 'true' : 'false'; ?>;
    var apiBaseUrl = '<?php echo esc_url(home_url('/')); ?>api/';
    var themeUrl = '<?php echo get_template_directory_uri(); ?>';
    var avatarPath = '<?php echo get_template_directory_uri(); ?>/imagens/avatares/';
    
    // ============================================================================
    // FORMATAÇÃO DE TELEFONE NO MODAL - DEVE ESTAR NO ESCOPO GLOBAL
    // ============================================================================
    function formatPhoneNumberModal() {
        const phoneInput = document.getElementById('phoneInputModal');
        if (!phoneInput) return;
        
        const countryCode = document.getElementById('phoneCountryCode').value;
        let value = phoneInput.value.replace(/\D/g, ''); // Remove tudo que não é dígito
        
        // Limitar número de dígitos por país
        const maxDigits = {
            '+1': 10,   '+7': 10,   '+27': 9,   '+33': 9,   '+34': 9,
            '+39': 10,  '+44': 10,  '+49': 11,  '+51': 9,   '+52': 10,
            '+54': 10,  '+55': 11,  '+56': 9,   '+57': 10,  '+61': 9,
            '+81': 10,  '+86': 11,  '+91': 10,  '+351': 9,  '+380': 9
        };
        
        if (maxDigits[countryCode] && value.length > maxDigits[countryCode]) {
            value = value.substring(0, maxDigits[countryCode]);
        }
        
        let formattedValue = '';
        
        switch(countryCode) {
            case '+1': // EUA/Canadá - (555) 123-4567
                formattedValue = value.replace(/(\d{0,3})(\d{0,3})(\d{0,4})/, function(match, p1, p2, p3) {
                    let result = '';
                    if (p1) result += '(' + p1 + ')';
                    if (p2) result += ' ' + p2;
                    if (p3) result += '-' + p3;
                    return result;
                });
                break;
                
            case '+7': // Rússia - 495 123-45-67
                formattedValue = value.replace(/(\d{0,3})(\d{0,3})(\d{0,2})(\d{0,2})/, function(match, p1, p2, p3, p4) {
                    let result = p1;
                    if (p2) result += ' ' + p2;
                    if (p3) result += '-' + p3;
                    if (p4) result += '-' + p4;
                    return result;
                });
                break;
                
            case '+27': case '+33': case '+34': case '+51': case '+56':
                // África do Sul, França, Espanha, Peru, Chile
                formattedValue = value.replace(/(\d{1,2})(\d{0,3})(\d{0,4})/, function(match, p1, p2, p3) {
                    let result = p1;
                    if (p2) result += ' ' + p2;
                    if (p3) result += ' ' + p3;
                    return result;
                });
                break;
                
            case '+39': case '+49': // Itália, Alemanha
                formattedValue = value.replace(/(\d{0,2})(\d{0,8})/, function(match, p1, p2) {
                    let result = p1;
                    if (p2) result += ' ' + p2;
                    return result;
                });
                break;
                
            case '+44': case '+52': case '+54': case '+57': case '+91':
                // Reino Unido, México, Argentina, Colômbia, Índia
                formattedValue = value.replace(/(\d{0,2})(\d{0,4})(\d{0,4})/, function(match, p1, p2, p3) {
                    let result = p1;
                    if (p2) result += ' ' + p2;
                    if (p3) result += ' ' + p3;
                    return result;
                });
                break;
                
            case '+55': // Brasil
                if (value.length <= 10) {
                    // Formato: (11) 4993-1617 (fixo)
                    formattedValue = value.replace(/(\d{2})(\d{0,4})(\d{0,4})/, function(match, p1, p2, p3) {
                        let result = '';
                        if (p1) result += '(' + p1 + ')';
                        if (p2) result += ' ' + p2;
                        if (p3) result += '-' + p3;
                        return result;
                    });
                } else {
                    // Formato: (11) 94993-1617 (celular com 11 dígitos)
                    formattedValue = value.replace(/(\d{2})(\d{5})(\d{0,4})/, '($1) $2-$3');
                }
                break;
                
            case '+61': // Austrália - 2 1234 5678
                formattedValue = value.replace(/(\d{1})(\d{0,4})(\d{0,4})/, function(match, p1, p2, p3) {
                    let result = p1;
                    if (p2) result += ' ' + p2;
                    if (p3) result += ' ' + p3;
                    return result;
                });
                break;
                
            case '+81': // Japão - 3 1234 5678
                formattedValue = value.replace(/(\d{1,2})(\d{0,4})(\d{0,4})/, function(match, p1, p2, p3) {
                    let result = p1;
                    if (p2) result += ' ' + p2;
                    if (p3) result += ' ' + p3;
                    return result;
                });
                break;
                
            case '+86': // China - 10 1234 5678
                formattedValue = value.replace(/(\d{0,2})(\d{0,4})(\d{0,4})/, function(match, p1, p2, p3) {
                    let result = p1;
                    if (p2) result += ' ' + p2;
                    if (p3) result += ' ' + p3;
                    return result;
                });
                break;
                
            case '+351': // Portugal - 21 123 4567
                formattedValue = value.replace(/(\d{0,2})(\d{0,3})(\d{0,4})/, function(match, p1, p2, p3) {
                    let result = p1;
                    if (p2) result += ' ' + p2;
                    if (p3) result += ' ' + p3;
                    return result;
                });
                break;
                
            case '+380': // Ucrânia - 66 777 88 99
                formattedValue = value.replace(/(\d{0,2})(\d{0,3})(\d{0,2})(\d{0,2})/, function(match, p1, p2, p3, p4) {
                    let result = p1;
                    if (p2) result += ' ' + p2;
                    if (p3) result += ' ' + p3;
                    if (p4) result += ' ' + p4;
                    return result;
                });
                break;
                
            default:
                formattedValue = value;
        }
        
        phoneInput.value = formattedValue.trim();
    }
    
    // Atualizar placeholder quando mudar o código do país
    function updatePhonePlaceholderModal() {
        const phoneInput = document.getElementById('phoneInputModal');
        if (!phoneInput) return;
        
        const countryCode = document.getElementById('phoneCountryCode').value;
        
        const placeholders = {
            '+1': '(555) 123-4567',
            '+7': '495 123-45-67',
            '+27': '21 123 4567',
            '+33': '1 23 45 67 89',
            '+34': '612 34 56 78',
            '+39': '06 1234 5678',
            '+44': '20 1234 5678',
            '+49': '30 12345678',
            '+51': '1 234 5678',
            '+52': '55 1234 5678',
            '+54': '11 1234 5678',
            '+55': '(11) 94993-1617',
            '+56': '2 1234 5678',
            '+57': '1 234 5678',
            '+61': '2 1234 5678',
            '+81': '3 1234 5678',
            '+86': '10 1234 5678',
            '+91': '22 1234 5678',
            '+351': '21 123 4567',
            '+380': '44 123 45 67'
        };
        
        phoneInput.placeholder = placeholders[countryCode] || '123456789';
        phoneInput.value = ''; // Limpar ao trocar país
    }
</script>

<!-- Main Content -->
<main class="main-content challenges-page">
    <div class="container">
        <!-- Challenge Section -->
        <div class="challenge-section-cards">
            <div class="challenge-card-text">
                <h2 class="challenge-card-title">Next Poker Challenge</h2>
                <div class="countdown-timer">
                    <div class="countdown-item">
                        <span class="countdown-value" id="days">5</span>
                        <span class="countdown-label">Days</span>
                    </div>
                    <span class="countdown-separator">:</span>
                    <div class="countdown-item">
                        <span class="countdown-value" id="hours">20</span>
                        <span class="countdown-label">Hrs</span>
                    </div>
                    <span class="countdown-separator">:</span>
                    <div class="countdown-item">
                        <span class="countdown-value" id="minutes">27</span>
                        <span class="countdown-label">Min</span>
                    </div>
                    <span class="countdown-separator">:</span>
                    <div class="countdown-item">
                        <span class="countdown-value" id="seconds">56</span>
                        <span class="countdown-label">Sec</span>
                    </div>
                </div>
                <button class="btn-register-challenge">Take me there</button>
            </div>
            <div class="challenge-banner-image">
                <img src="<?php echo get_template_directory_uri(); ?>/imagens/banner2.png" alt="Tournament Details" class="tournament-banner">
            </div>
        </div>

        <!-- My Account Section -->
        <?php if ($usuario_logado): ?>
        <div class="my-account-section">
            <div class="account-header">
                <h2 class="account-title">My Account</h2>
                <div class="membership-info">
                    <?php
                    // Definir informações do plano baseado no tipo de conta
                    // Valores aceitos: free, silver, gold, diamond
                    $tipo_conta = isset($usuario_dados['tipo_conta']) ? strtolower(trim($usuario_dados['tipo_conta'])) : 'free';
                    
                    // Se o valor não for válido, usar free como padrão
                    if (!in_array($tipo_conta, ['free', 'silver', 'gold', 'diamond'])) {
                        $tipo_conta = 'free';
                    }
                    
                    // Configurações dos planos para o header
                    $icone_plano = ''; // Sem ícone para plano gratuito
                    $nome_plano = 'Free Plan';
                    $data_renovacao = '';
                    $mostrar_upgrade = true;
                    $texto_botao = 'View Plans';
                    
                    // Formatar datas do plano
                    $data_inicio_fmt = '';
                    $data_termino_fmt = '';
                    
                    if (!empty($usuario_dados['plano_inicio'])) {
                        $data_inicio_fmt = formatarDataPTBR($usuario_dados['plano_inicio'], false);
                    }
                    
                    if (!empty($usuario_dados['plano_termino'])) {
                        $data_termino_fmt = formatarDataPTBR($usuario_dados['plano_termino'], true);
                    }
                    
                    switch ($tipo_conta) {
                        case 'silver':
                            $icone_plano = get_template_directory_uri() . '/imagens/planos/prata.png';
                            $nome_plano = 'Silver membership';
                            if ($data_termino_fmt) {
                                $data_renovacao = 'Renova em ' . $data_termino_fmt;
                            } else {
                                $data_renovacao = 'Renews on Nov 30, 2025';
                            }
                            $texto_botao = 'Upgrade plan';
                            break;
                        case 'gold':
                            $icone_plano = get_template_directory_uri() . '/imagens/planos/dourada.png';
                            $nome_plano = 'Gold membership';
                            if ($data_termino_fmt) {
                                $data_renovacao = 'Renova em ' . $data_termino_fmt;
                            } else {
                                $data_renovacao = 'Renews on Nov 30, 2025';
                            }
                            $texto_botao = 'Upgrade plan';
                            break;
                        case 'diamond':
                            $icone_plano = get_template_directory_uri() . '/imagens/planos/diamante.png';
                            $nome_plano = 'Diamond membership';
                            if ($data_termino_fmt) {
                                $data_renovacao = 'Renova em ' . $data_termino_fmt;
                            } else {
                                $data_renovacao = 'Renews on Nov 30, 2025';
                            }
                            $mostrar_upgrade = false;
                            break;
                        case 'free':
                        default:
                            $data_renovacao = 'Subscribe to get premium features';
                            break;
                    }
                    ?>
                    <?php if (!empty($icone_plano)): ?>
                        <img src="<?php echo $icone_plano; ?>" alt="Membership Icon" class="membership-icon">
                    <?php endif; ?>
                    <div class="membership-text">
                        <span class="membership-type"><?php echo $nome_plano; ?></span>
                        <span class="membership-date"><?php echo $data_renovacao; ?></span>
                    </div>
                    <?php if ($mostrar_upgrade): ?>
                        <button class="btn-upgrade" onclick="window.location.href='<?php echo site_url('/planos/'); ?>'"><?php echo $texto_botao; ?></button>
                    <?php endif; ?>
                </div>
            </div>

            <div class="account-tabs">
                <button class="account-tab <?php echo ($tab_ativa === 'challenges') ? 'active' : ''; ?>" onclick="switchAccountTab('challenges')">Challenges</button>
                <button class="account-tab <?php echo ($tab_ativa === 'billing') ? 'active' : ''; ?>" onclick="switchAccountTab('billing')">Billing</button>
                <button class="account-tab <?php echo ($tab_ativa === 'affiliation') ? 'active' : ''; ?>" onclick="switchAccountTab('affiliation')">Affiliation</button>
                <button class="account-tab <?php echo ($tab_ativa === 'settings') ? 'active' : ''; ?>" onclick="switchAccountTab('settings')">Settings</button>
            </div>

            <!-- Challenges Tab Content -->
            <div class="tab-content <?php echo ($tab_ativa === 'challenges') ? 'active' : ''; ?>" id="challengesTab">
                <div class="challenge-history">
                <h3 class="history-title">Challenge History</h3>
                <?php
                // TODO: Buscar histórico de desafios do banco de dados
                // Deixar vazio por padrão para mostrar o empty state
                $history_items = [];
                
                // Exemplo de histórico (descomentar para testar com dados)
                /*
                $history_items = [
                    ['date' => 'Sep 28 2025', 'description' => 'Second table entry ticket', 'has_button' => true],
                    ['date' => 'Sep 27 2025', 'description' => 'Free challenge entry for new registrants.', 'has_button' => false],
                    ['date' => 'Sep 26 2025', 'description' => 'Free challenge entry for new registrants.', 'has_button' => false],
                    ['date' => 'Sep 25 2025', 'description' => 'Free challenge entry for new registrants.', 'has_button' => false],
                ];
                */
                
                if (empty($history_items)): ?>
                    <!-- Empty State -->
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <h4 class="empty-title">Ainda não há desafios.</h4>
                        <p class="empty-description">Pronto para provar o seu valor? Participe do seu primeiro desafio e comece a sua escalada rumo ao topo.</p>
                        <button class="btn-take-me-there" onclick="window.location.href='#'">Leve-me lá</button>
                    </div>
                <?php else: ?>
                    <!-- History List -->
                    <div class="history-list">
                        <?php foreach ($history_items as $item): ?>
                            <div class="history-item">
                                <span class="history-date"><?php echo esc_html($item['date']); ?></span>
                                <span class="history-description"><?php echo esc_html($item['description']); ?></span>
                                <?php if ($item['has_button']): ?>
                                    <button class="btn-go"><i class="fas fa-arrow-right"></i></button>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="pagination">
                        <button class="page-btn"><i class="fas fa-chevron-left"></i></button>
                        <button class="page-btn active">1</button>
                        <button class="page-btn">2</button>
                        <span class="page-dots">...</span>
                        <button class="page-btn">6</button>
                        <button class="page-btn">7</button>
                        <button class="page-btn"><i class="fas fa-chevron-right"></i></button>
                    </div>
                <?php endif; ?>
            </div>
            </div>

            <!-- Affiliation Tab Content -->
            <div class="tab-content <?php echo ($tab_ativa === 'affiliation') ? 'active' : ''; ?>" id="affiliationTab">
                <!-- Stats Section -->
                <div class="affiliation-stats-top">
                    <div class="stat-card-top">
                        <div class="stat-number-large">12</div>
                        <div class="stat-label-large">Total<br>Referrals</div>
                    </div>
                    <div class="stat-card-top">
                        <div class="stat-number-large">+10</div>
                        <div class="stat-label-large">Earned<br>Credits</div>
                    </div>
                    <div class="stat-card-top">
                        <div class="stat-number-large">2</div>
                        <div class="stat-label-large">Pending<br>Credits</div>
                    </div>
                </div>

                <!-- Referral Link Section -->
                <div class="affiliation-link-section">
                    <div class="affiliation-unified-container">
                        <div class="referral-link-row">
                            <input type="text" class="referral-link-input" id="referralLink" value="https://preview---poker111-ascent-2d25c15e.base44.app/refister??ref=GROSANNA06847483G23" readonly>
                            <button class="btn-copy-link" onclick="copyReferralLink()">
                                <i class="far fa-copy"></i>
                            </button>
                        </div>
                        
                        <div class="share-container">
                            <button class="btn-share">Share</button>
                            <div class="share-social-icons-inline">
                                <i class="fab fa-facebook-f"></i>
                                <i class="fab fa-x-twitter"></i>
                                <i class="fab fa-instagram"></i>
                                <i class="fab fa-linkedin-in"></i>
                                <i class="fab fa-discord"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- How it Works Section -->
                <div class="how-it-works-section">
                    <h3 class="section-title">How it Works</h3>
                    <div class="steps-container">
                        <div class="work-step">
                            <div class="step-icon-circle">
                                <i class="fas fa-link"></i>
                            </div>
                            <div class="step-content">
                                <div class="step-number">Step 1</div>
                                <div class="step-description">Share your referral link with others</div>
                            </div>
                        </div>
                        
                        <div class="step-arrow">
                            <i class="fas fa-arrow-right"></i>
                        </div>
                        
                        <div class="work-step">
                            <div class="step-icon-circle">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <div class="step-content">
                                <div class="step-number">Step 2</div>
                                <div class="step-description">They register using link & create an account</div>
                            </div>
                        </div>
                        
                        <div class="step-arrow">
                            <i class="fas fa-arrow-right"></i>
                        </div>
                        
                        <div class="work-step">
                            <div class="step-icon-circle">
                                <i class="fas fa-coins"></i>
                            </div>
                            <div class="step-content">
                                <div class="step-number">Step 3</div>
                                <div class="step-description">Earn 2 credits for each successful referral</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Referral History Section -->
                <div class="referral-history-section">
                    <h3 class="section-title">Referral History</h3>
                    <p class="section-description">Description</p>
                    
                    <div class="referral-empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <h4 class="empty-state-title">No Referrals Yet</h4>
                        <p class="empty-state-description">Start sharing your link and earn 2 credits for every friend who joins.<br>It's that simple.</p>
                        <button class="btn-copy-my-link" onclick="copyReferralLink()">
                            Copy My Link
                            <i class="far fa-copy"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Settings Tab Content -->
            <div class="tab-content <?php echo ($tab_ativa === 'settings') ? 'active' : ''; ?>" id="settingsTab">
                <!-- Personal Information -->
                <div class="settings-section">
                    <h3 class="settings-title">Personal Information</h3>
                    <div class="personal-info-content">
                        <div class="profile-avatar-section">
                            <div class="avatar-frame">
                                <div class="profile-avatar-large">
                                    <div class="avatar-background"></div>
                                    <?php if ($usuario_logado && $usuario_dados): ?>
                                        <img src="<?php echo get_template_directory_uri(); ?>/imagens/avatares/<?php echo esc_attr($usuario_dados['url_avatar']); ?>" alt="Profile Avatar" class="avatar-img" id="profileAvatarImg">
                                    <?php else: ?>
                                        <img src="<?php echo get_template_directory_uri(); ?>/imagens/avatares/avatar1.webp" alt="Profile Avatar" class="avatar-img" id="profileAvatarImg">
                                    <?php endif; ?>
                                </div>
                                <button class="edit-avatar-btn" onclick="openAvatarModal()"><i class="fas fa-pencil-alt"></i></button>
                                <div class="avatar-badge">
                                    <img src="<?php echo get_template_directory_uri(); ?>/imagens/icon2.png" alt="Badge" class="badge-icon">
                                </div>
                            </div>
                        </div>
                        
                        <div class="info-fields-grid">
                            <div class="info-field">
                                <div class="field-header">
                                    <label class="field-label">Nickname</label>
                                    <button class="edit-field-btn" onclick="openNicknameModal()"><i class="fas fa-pencil-alt"></i></button>
                                </div>
                                <?php if ($usuario_logado && $usuario_dados): ?>
                                    <span class="field-value" id="nicknameDisplay"><?php echo esc_html($usuario_dados['nickname']); ?></span>
                                <?php else: ?>
                                    <span class="field-value" id="nicknameDisplay">Guest</span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="info-field">
                                <div class="field-header">
                                    <label class="field-label">Phone</label>
                                    <button class="edit-field-btn" onclick="openPhoneModal()"><i class="fas fa-pencil-alt"></i></button>
                                </div>
                                <?php if ($usuario_logado && $usuario_dados): ?>
                                    <span class="field-value" id="phoneDisplay"><?php echo esc_html($usuario_dados['codigo_pais'] . ' ' . $usuario_dados['telefone']); ?></span>
                                <?php else: ?>
                                    <span class="field-value" id="phoneDisplay">Not available</span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="info-field">
                                <div class="field-header">
                                    <label class="field-label">Full Name</label>
                                    <button class="btn-contact-edit" id="contactEditFullName" onclick="openContactModal('name')">Contact Us To Edit</button>
                                </div>
                                <?php if ($usuario_logado && $usuario_dados): ?>
                                    <span class="field-value" id="fullNameDisplay"><?php echo esc_html($usuario_dados['primeiro_nome'] . ' ' . $usuario_dados['sobrenome']); ?></span>
                                <?php else: ?>
                                    <span class="field-value" id="fullNameDisplay">Guest User</span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="info-field">
                                <div class="field-header">
                                    <label class="field-label">E-mail</label>
                                    <button class="btn-contact-edit" id="contactEditEmail" onclick="openContactModal('email')">Contact Us To Edit</button>
                                </div>
                                <?php if ($usuario_logado && $usuario_dados): ?>
                                    <span class="field-value" id="emailDisplay"><?php echo esc_html($usuario_dados['email']); ?></span>
                                <?php else: ?>
                                    <span class="field-value" id="emailDisplay">Not available</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Subscription -->
                <div class="settings-section">
                    <h3 class="settings-title">Subscription</h3>
                    <div class="subscription-content">
                        <?php
                        // Definir informações do plano baseado no tipo de conta
                        // Valores aceitos: free, silver, gold, diamond
                        $tipo_conta = isset($usuario_dados['tipo_conta']) ? strtolower(trim($usuario_dados['tipo_conta'])) : 'free';
                        
                        // Se o valor não for válido, usar free como padrão
                        if (!in_array($tipo_conta, ['free', 'silver', 'gold', 'diamond'])) {
                            $tipo_conta = 'free';
                        }
                        
                        // Formatar período do plano
                        $periodo_plano = '';
                        if (!empty($usuario_dados['plano_inicio']) && !empty($usuario_dados['plano_termino'])) {
                            $data_inicio = formatarDataPTBR($usuario_dados['plano_inicio'], false);
                            $data_termino = formatarDataPTBR($usuario_dados['plano_termino'], true);
                            $periodo_plano = $data_inicio . ' - ' . $data_termino;
                        }
                        
                        // Configurações dos planos
                        $planos_config = [
                            'silver' => [
                                'nome' => 'SILVER POKER MEMBERSHIP',
                                'icone' => get_template_directory_uri() . '/imagens/planos/prata.png',
                                'descricao' => 'This is your active subscription plan',
                                'data' => $periodo_plano, // Só mostra se houver datas no banco
                                'mostrar_cancelar' => true,
                                'mostrar_upgrade' => true
                            ],
                            'gold' => [
                                'nome' => 'GOLD POKER MEMBERSHIP',
                                'icone' => get_template_directory_uri() . '/imagens/planos/dourada.png',
                                'descricao' => 'This is your active subscription plan',
                                'data' => $periodo_plano, // Só mostra se houver datas no banco
                                'mostrar_cancelar' => true,
                                'mostrar_upgrade' => true
                            ],
                            'diamond' => [
                                'nome' => 'DIAMOND POKER MEMBERSHIP',
                                'icone' => get_template_directory_uri() . '/imagens/planos/diamante.png',
                                'descricao' => 'This is your active subscription plan',
                                'data' => $periodo_plano, // Só mostra se houver datas no banco
                                'mostrar_cancelar' => true,
                                'mostrar_upgrade' => false
                            ],
                            'free' => [
                                'nome' => 'FREE PLAN',
                                'icone' => '', // Sem ícone para plano gratuito
                                'descricao' => 'You are currently on the free plan. Subscribe to unlock premium features and challenge entry tickets!',
                                'data' => '',
                                'mostrar_cancelar' => false,
                                'mostrar_upgrade' => true
                            ]
                        ];
                        
                        // Se o tipo de conta não estiver na lista, usar free
                        if (!isset($planos_config[$tipo_conta])) {
                            $tipo_conta = 'free';
                        }
                        
                        $plano_atual = $planos_config[$tipo_conta];
                        ?>
                        <div class="subscription-info-box">
                            <?php if (!empty($plano_atual['icone'])): ?>
                                <img src="<?php echo $plano_atual['icone']; ?>" alt="<?php echo $plano_atual['nome']; ?>" class="subscription-icon">
                            <?php endif; ?>
                            <div class="subscription-details">
                                <h4 class="subscription-name"><?php echo $plano_atual['nome']; ?></h4>
                                <p class="subscription-description"><?php echo $plano_atual['descricao']; ?></p>
                                <?php if (!empty($plano_atual['data'])): ?>
                                    <p class="subscription-date"><?php echo $plano_atual['data']; ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="subscription-actions">
                            <?php if ($plano_atual['mostrar_cancelar']): ?>
                                <button class="btn-cancel-subscription">Cancel Subscription</button>
                            <?php endif; ?>
                            <?php if ($plano_atual['mostrar_upgrade']): ?>
                                <button class="btn-upgrade-subscription" onclick="window.location.href='<?php echo site_url('/planos/'); ?>'">
                                    <?php echo ($tipo_conta === 'free') ? 'View Plans' : 'Upgrade Plan'; ?>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Payment Methods -->
                <div class="settings-section">
                    <h3 class="settings-title">Payment Methods</h3>
                    <div class="payment-methods-content">
                        <div class="payment-card-info">
                            <div class="card-brand">
                                <i class="fab fa-cc-visa"></i>
                            </div>
                            <div class="card-details">
                                <p class="card-number">Visa •••• 4253</p>
                                <p class="card-expiry">Expires 12/2026</p>
                            </div>
                        </div>
                        <div class="payment-methods-actions">
                            <button class="payment-method-icon"><i class="fab fa-cc-paypal"></i></button>
                            <button class="payment-method-icon"><i class="fab fa-cc-mastercard"></i></button>
                            <button class="btn-change-payment">Change Payment Method</button>
                        </div>
                    </div>
                </div>

                <!-- Delete Account -->
                <div class="settings-section">
                    <button class="btn-delete-account" onclick="openDeleteAccountModal()">Delete Account</button>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <!-- Seção para usuários não logados -->
    <div class="my-account-section not-logged-placeholder" style="display: none;">
        <!-- Esta seção fica oculta, o modal será exibido -->
    </div>
    <?php endif; ?>
</main>

<!-- Modal de Usuário Não Logado -->
<div id="notLoggedModal" class="modal not-logged-modal">
    <div class="modal-content">
        <div class="modal-top-bar">
            <img src="<?php echo get_template_directory_uri(); ?>/logopoker.png" alt="POKER III" class="modal-logo">
        </div>
        
        <div class="modal-header">
            <div class="not-logged-icon">
                <i class="fas fa-user-lock"></i>
            </div>
            <h2 class="modal-title" id="notLoggedTitle">Oops! You're not logged in</h2>
            <p class="modal-subtitle" id="notLoggedSubtitle">We noticed you're not logged in. Please log in to access your account and all features.</p>
        </div>
        
        <div class="not-logged-actions">
            <button class="btn-login-now" onclick="openLoginFromNotLogged()">
                <i class="fas fa-sign-in-alt"></i>
                <span id="notLoggedLoginBtn">Log In</span>
            </button>
            <p class="not-logged-signup-text">
                <span id="notLoggedNoAccount">Don't have an account?</span>
                <a href="#" onclick="openSignupFromNotLogged()" id="notLoggedSignupLink">Sign Up</a>
            </p>
        </div>
    </div>
</div>

<!-- Avatar Modal -->
<div id="avatarModal" class="modal avatar-modal">
    <div class="modal-content avatar-modal-content">
        <div class="modal-top-bar">
            <img src="<?php echo get_template_directory_uri(); ?>/logopoker.png" alt="POKER III" class="modal-logo">
            <button class="modal-close" onclick="closeAvatarModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="modal-header">
            <h2 class="modal-title">Change Avatar</h2>
            <p class="modal-subtitle">It will be visible for other users</p>
        </div>
        
        <div class="avatar-grid">
            <?php for ($i = 1; $i <= 12; $i++): ?>
                <div class="avatar-option" onclick="selectAvatar('avatar<?php echo $i; ?>.webp')">
                    <img src="<?php echo get_template_directory_uri(); ?>/imagens/avatares/avatar<?php echo $i; ?>.webp" alt="Avatar <?php echo $i; ?>">
                </div>
            <?php endfor; ?>
        </div>
        
        <div class="avatar-modal-actions">
            <button class="btn-close-modal" onclick="closeAvatarModal()">Close</button>
            <button class="btn-save-avatar" onclick="saveAvatar()">Save changes</button>
        </div>
    </div>
</div>

<!-- Nickname Modal -->
<div id="nicknameModal" class="modal nickname-modal">
    <div class="modal-content nickname-modal-content">
        <div class="modal-top-bar">
            <img src="<?php echo get_template_directory_uri(); ?>/logopoker.png" alt="POKER III" class="modal-logo">
            <button class="modal-close" onclick="closeNicknameModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="modal-header">
            <h2 class="modal-title">Change Nickname</h2>
            <p class="modal-subtitle">It will be visible for other users</p>
        </div>
        
        <div class="nickname-input-container">
            <input type="text" id="nicknameInput" class="nickname-input" placeholder="Jane111" maxlength="20">
        </div>
        
        <div class="nickname-modal-actions">
            <button class="btn-close-modal" onclick="closeNicknameModal()">Close</button>
            <button class="btn-save-nickname" onclick="saveNickname()">Save changes</button>
        </div>
    </div>
</div>

<!-- Phone Modal -->
<div id="phoneModal" class="modal phone-modal">
    <div class="modal-content phone-modal-content">
        <div class="modal-top-bar">
            <img src="<?php echo get_template_directory_uri(); ?>/logopoker.png" alt="POKER III" class="modal-logo">
            <button class="modal-close" onclick="closePhoneModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="phone-progress-bar">
            <div class="progress-line" id="progressLine"></div>
            <div class="progress-step" id="progressStep">1 / 2</div>
        </div>
        
        <!-- Step 1: Enter Phone Number -->
        <div id="phoneStep1" class="phone-step">
            <div class="modal-header">
                <h2 class="modal-title">Change Phone Number</h2>
                <p class="modal-subtitle-phone">This is the phone number we'll use<br>for authentication and account recovery.</p>
            </div>
            
            <div class="phone-input-group">
                <select class="phone-country-select" id="phoneCountryCode" onchange="updatePhonePlaceholderModal()">
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
                <input type="tel" id="phoneInputModal" class="phone-input-modal" placeholder="(11) 94993-1617" maxlength="16" oninput="formatPhoneNumberModal()">
            </div>
            
            <div class="phone-modal-actions">
                <button class="btn-close-modal" onclick="closePhoneModal()">Close</button>
                <button class="btn-continue-phone" onclick="goToVerification()">Continue</button>
            </div>
        </div>
        
        <!-- Step 2: Verification Code -->
        <div id="phoneStep2" class="phone-step" style="display: none;">
            <div class="modal-header">
                <h2 class="modal-title">Phone Number Verification</h2>
                <p class="modal-subtitle-phone">We texted you a four-digit code to</p>
                <p class="verification-phone-display" id="verificationPhoneDisplay">+357 99 99 999.</p>
                <p class="modal-subtitle-phone verification-instruction">Please enter the code below to confirm your<br>phone number. Make sure to keep this<br>window open while you check your phone.<br>The code may take up to 10 minutes to arrive.</p>
            </div>
            
            <div class="verification-code-container">
                <input type="text" id="verificationCodeInput" class="verification-code-input" placeholder="○ ○ ○ ○" maxlength="4" pattern="[0-9]*">
            </div>
            
            <div class="verification-actions">
                <button class="btn-change-phone" onclick="backToPhoneInput()">Change phone number</button>
                <button class="btn-resend-code" id="resendBtnModal" disabled>Resend in <span id="resendTimer">40</span> sec...</button>
            </div>
        </div>
    </div>
</div>

<!-- Contact Us Modal -->
<div id="contactModal" class="modal contact-modal" data-mode="profile-change">
    <div class="modal-content contact-modal-content">
        <div class="modal-top-bar">
            <img src="<?php echo get_template_directory_uri(); ?>/logopoker.png" alt="POKER III" class="modal-logo">
            <button class="modal-close" onclick="closeContactModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="modal-header">
            <h2 class="modal-title">Contact Us</h2>
            <p class="modal-subtitle">We'd love to hear from you!</p>
            <p class="modal-subtitle-contact">Envie-nos uma solicitação formal para análise e processamento da alteração.</p>
        </div>
        
        <div class="contact-form-container">
            <div class="contact-current-wrapper">
                <p class="contact-current-label" id="contactCurrentLabel"></p>
                <p class="contact-current-value" id="contactCurrentValue"></p>
            </div>
            <div class="message-input-wrapper">
                <textarea id="contactMessage" class="contact-message-input" placeholder="Add your message" maxlength="500" rows="5"></textarea>
                <div class="message-actions">
                    <button class="btn-send-message" onclick="sendContactMessage()">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                    <span class="char-count"><span id="charCount">0</span>/500</span>
                </div>
            </div>

            <p class="contact-divider">Or reach out via:</p>
            
            <div class="contact-social-icons">
                <a href="#" class="contact-social-btn discord" title="Discord">
                    <i class="fab fa-discord"></i>
                </a>
                <a href="#" class="contact-social-btn telegram" title="Telegram">
                    <i class="fab fa-telegram-plane"></i>
                </a>
                <a href="#" class="contact-social-btn whatsapp" title="WhatsApp">
                    <i class="fab fa-whatsapp"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Delete Account Modal -->
<div id="deleteAccountModal" class="modal delete-account-modal">
    <div class="modal-content delete-account-modal-content">
        <div class="modal-top-bar">
            <img src="<?php echo get_template_directory_uri(); ?>/logopoker.png" alt="POKER III" class="modal-logo">
            <button class="modal-close" onclick="closeDeleteAccountModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="modal-header">
            <div class="delete-account-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h2 class="modal-title">Delete Account</h2>
            <p class="modal-subtitle">We're sorry to see you go!</p>
            <p class="delete-account-warning">
Your account will remain deactivated for 7 days before permanent deletion. 
If you wish to reactivate it during this period, you must contact our support team to request reactivation.
</p>
        </div>
        
        <div class="delete-account-form">
            <label class="delete-account-label">Why are you leaving? (select one or more reasons)</label>
            
            <div class="delete-reasons-list">
                <label class="reason-checkbox">
                    <input type="checkbox" name="deleteReason" value="not_using">
                    <span class="reason-text">I'm not using it anymore</span>
                </label>
                
                <label class="reason-checkbox">
                    <input type="checkbox" name="deleteReason" value="too_expensive">
                    <span class="reason-text">It's too expensive</span>
                </label>
                
                <label class="reason-checkbox">
                    <input type="checkbox" name="deleteReason" value="found_alternative">
                    <span class="reason-text">I found a better alternative</span>
                </label>
                
                <label class="reason-checkbox">
                    <input type="checkbox" name="deleteReason" value="privacy_concerns">
                    <span class="reason-text">Privacy concerns</span>
                </label>
                
                <label class="reason-checkbox">
                    <input type="checkbox" name="deleteReason" value="technical_issues">
                    <span class="reason-text">Technical issues</span>
                </label>
                
                <label class="reason-checkbox">
                    <input type="checkbox" name="deleteReason" value="lack_features">
                    <span class="reason-text">Lack of features I need</span>
                </label>
                
                <label class="reason-checkbox">
                    <input type="checkbox" name="deleteReason" value="other">
                    <span class="reason-text">Other</span>
                </label>
            </div>
            
            <div class="delete-additional-feedback">
                <label class="delete-account-label">Additional feedback (optional)</label>
                <textarea id="deleteFeedback" class="delete-feedback-input" placeholder="Tell us more about your experience..." maxlength="500" rows="4"></textarea>
                <span class="char-count-delete"><span id="deleteCharCount">0</span>/500</span>
            </div>
            
            <div class="delete-confirmation-checkbox">
                <label class="confirm-checkbox">
                    <input type="checkbox" id="confirmDelete">
                    <span class="confirm-text">I understand that my account will be deactivated for 7 days and I will receive a confirmation email.</span>
                </label>
            </div>
        </div>
        
        <div class="delete-account-actions">
            <button class="btn-cancel-delete" onclick="closeDeleteAccountModal()">Cancel</button>
            <button class="btn-confirm-delete" id="btnConfirmDelete" onclick="confirmDeleteAccount()" disabled>Delete Account</button>
        </div>
    </div>
</div>

<?php get_footer(); ?>

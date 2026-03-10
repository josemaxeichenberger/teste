<?php
/**
 * Template Name: Upgrade
 * Description: Página de upgrade/checkout de planos
 */

// Iniciar sessão se não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================================================
// IMPORTAR CONEXÃO DO ARQUIVO conexao.php
// ============================================================================
$conexao_encontrada = false;

$caminhos_conexao = [
    __DIR__ . '/../conexao/conexao.php',
    __DIR__ . '/../../conexao/conexao.php',
    $_SERVER['DOCUMENT_ROOT'] . '/conexao/conexao.php',
    $_SERVER['DOCUMENT_ROOT'] . '/Poker5/conexao/conexao.php',
    '/home/poker/public_html/conexao/conexao.php',
    dirname(ABSPATH) . '/conexao/conexao.php',
    ABSPATH . '../conexao/conexao.php',
    ABSPATH . 'conexao/conexao.php',
];

foreach ($caminhos_conexao as $caminho) {
    if (file_exists($caminho)) {
        require_once $caminho;
        $conexao_encontrada = true;
        break;
    }
}

// Verificar se usuário está logado (mesma lógica do header.php)
$usuario_logado = isset($_SESSION['logged_in']) && ($_SESSION['logged_in'] === true || $_SESSION['logged_in'] == 1);
$usuario_dados = null;

// Buscar dados do usuário do banco se estiver logado
if ($usuario_logado && isset($_SESSION['user_id']) && $conexao_encontrada) {
    try {
        $pdo = getConnection();
        
        $stmt = $pdo->prepare("SELECT id, nome_completo, email, avatar FROM usuarios WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $usuario_dados = $stmt->fetch(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        error_log("Erro ao buscar dados do usuário: " . $e->getMessage());
    }
}

// Se não conseguir buscar dados do banco, usar dados da sessão
if ($usuario_logado && !$usuario_dados) {
    $usuario_dados = [
        'id' => $_SESSION['user_id'] ?? 0,
        'nome_completo' => $_SESSION['user_name'] ?? 'User',
        'email' => $_SESSION['user_email'] ?? '',
        'avatar' => $_SESSION['user_avatar'] ?? 'avatar1.webp'
    ];
}

// Capturar parâmetros da URL
$plan = isset($_GET['plan']) ? strtolower(trim($_GET['plan'])) : 'silver';
$period = isset($_GET['period']) ? strtolower(trim($_GET['period'])) : 'monthly';
$price = isset($_GET['price']) ? floatval($_GET['price']) : 39.00;

// Validar plano
$valid_plans = ['silver', 'gold', 'diamond'];
if (!in_array($plan, $valid_plans)) {
    $plan = 'silver';
}

// Validar período
if (!in_array($period, ['monthly', 'yearly'])) {
    $period = 'monthly';
}

// Definir dados dos planos
$plans_data = [
    'silver' => [
        'name' => 'Silver',
        'name_pt' => 'Prata',
        'icon' => 'prata.png',
        'color' => '#C0C0C0',
        'monthly_price' => 39.00,
        'yearly_price' => 468.00,
        'features' => [
            'en' => [
                '2 free Challenge entry tickets a month',
                'Exclusive coaching videos',
                'Access our training materials',
                'Library Podcasts galore',
                'Weekly Analysis Calls',
                'Private email support',
                'Ability to chat with subscribers'
            ],
            'pt' => [
                '2 tickets gratuitos de desafios por mês',
                'Vídeos exclusivos de coaching',
                'Acesso aos materiais de treinamento',
                'Biblioteca de podcasts',
                'Chamadas de análise semanais',
                'Suporte por email privado',
                'Capacidade de conversar com assinantes'
            ]
        ]
    ],
    'gold' => [
        'name' => 'Gold',
        'name_pt' => 'Ouro',
        'icon' => 'dourada.png',
        'color' => '#FFD700',
        'monthly_price' => 49.00,
        'yearly_price' => 588.00,
        'features' => [
            'en' => [
                '4 +Immediate entry tickets a month',
                'Exclusive coaching videos',
                'Advanced training materials',
                'Library Podcasts galore',
                'Private live group coaching',
                'Unlimited email support',
                'Monthly analysis (hand) review',
                'Priority email support',
                'Access to weekly Q&A Sessions',
                'Game Plan Concept Course'
            ],
            'pt' => [
                '4 tickets imediatos de entrada por mês',
                'Vídeos exclusivos de coaching',
                'Materiais de treinamento avançados',
                'Biblioteca de podcasts',
                'Coaching ao vivo em grupo',
                'Suporte por email ilimitado',
                'Revisão mensal de análise',
                'Suporte por email prioritário',
                'Acesso às sessões semanais de perguntas',
                'Curso de conceitos de plano de jogo'
            ]
        ]
    ],
    'diamond' => [
        'name' => 'Diamond',
        'name_pt' => 'Diamante',
        'icon' => 'diamante.png',
        'color' => '#B9F2FF',
        'monthly_price' => 69.00,
        'yearly_price' => 828.00,
        'features' => [
            'en' => [
                '6 +Immediate entry tickets a month',
                'Exclusive coaching videos',
                'Premium Training Materials',
                'Library Podcasts galore ALL ACCESS',
                'Private live group coaching',
                'Unlimited email support',
                'Weekly analysis (hand) reviews',
                'Priority email support',
                'Access to weekly Q&A Sessions',
                '1 on 1 hour Monthly Strategy session',
                'Discord community access',
                'Game Plan Concept Course Anytime'
            ],
            'pt' => [
                '6 tickets imediatos de entrada por mês',
                'Vídeos exclusivos de coaching',
                'Materiais de treinamento premium',
                'Acesso total à biblioteca de podcasts',
                'Coaching ao vivo em grupo',
                'Suporte por email ilimitado',
                'Revisões semanais de análise',
                'Suporte por email prioritário',
                'Acesso às sessões semanais de perguntas',
                'Sessão de estratégia mensal 1 a 1 de 1 hora',
                'Acesso à comunidade Discord',
                'Curso de conceitos de plano de jogo a qualquer momento'
            ]
        ]
    ]
];

$current_plan = $plans_data[$plan];
$plan_price = $period === 'yearly' ? $current_plan['yearly_price'] : $current_plan['monthly_price'];
// Preço exibido sempre como mensal equivalente
$display_price = $current_plan['monthly_price'];

get_header();
?>

<link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/css/upgrade.css">

<!-- Main Content -->
<main class="main-content upgrade-page">
    <div class="container">
        <div class="upgrade-container">
            <!-- Left Side: Plan Summary -->
            <div class="plan-summary">
                <h2 class="summary-title" data-en="Order Summary" data-pt="Resumo do Pedido">Order Summary</h2>
                
                <div class="plan-info">
                    <div class="plan-badge">
                        <img src="<?php echo get_template_directory_uri(); ?>/imagens/planos/<?php echo $current_plan['icon']; ?>" alt="<?php echo $current_plan['name']; ?>">
                    </div>
                    <div class="plan-details">
                        <h3 class="plan-name-display">
                            <span data-en="<?php echo strtoupper($current_plan['name']); ?>" data-pt="<?php echo strtoupper($current_plan['name_pt']); ?>">
                                <?php echo strtoupper($current_plan['name']); ?>
                            </span>
                            <span class="plan-period" data-en="<?php echo ucfirst($period); ?>" data-pt="<?php echo $period === 'monthly' ? 'Mensal' : 'Anual'; ?>">
                                <?php echo ucfirst($period); ?>
                            </span>
                        </h3>
                        <div class="plan-price-display">
                            <span class="currency">R$</span>
                            <span class="amount"><?php echo number_format($display_price, 0); ?></span>
                            <span class="period-label">/m</span>
                        </div>
                    </div>
                </div>

                <div class="features-summary">
                    <h4 data-en="What's Included:" data-pt="O que está incluído:">What's Included:</h4>
                    <ul class="features-list">
                        <?php foreach ($current_plan['features']['en'] as $index => $feature): ?>
                            <li>
                                <i class="fas fa-check-circle"></i>
                                <span data-en="<?php echo htmlspecialchars($feature); ?>" data-pt="<?php echo htmlspecialchars($current_plan['features']['pt'][$index]); ?>">
                                    <?php echo htmlspecialchars($feature); ?>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <?php if ($period === 'yearly'): ?>
                <div class="savings-notice">
                    <i class="fas fa-tag"></i>
                    <span data-en="You save up to 20% on yearly plan!" data-pt="Você economiza até 20% no plano anual!">
                        You save up to 20% on yearly plan!
                    </span>
                </div>
                <?php endif; ?>
            </div>

            <!-- Right Side: Payment Form -->
            <div class="payment-section">
                <h2 class="payment-title" data-en="Payment Details" data-pt="Detalhes do Pagamento">Payment Details</h2>

                <?php if (!$usuario_logado): ?>
                <div class="login-required">
                    <i class="fas fa-lock"></i>
                    <h3 data-en="Login Required" data-pt="Login Necessário">Login Required</h3>
                    <p data-en="Please log in to continue with your purchase." data-pt="Por favor, faça login para continuar com sua compra.">
                        Please log in to continue with your purchase.
                    </p>
                    <button class="btn-login-required" onclick="openLoginModal(event)" data-en="Log In" data-pt="Entrar">Log In</button>
                </div>
                <?php else: ?>
                
                <form id="paymentForm" class="payment-form">
                    <!-- User Info -->
                    <div class="form-section">
                        <h3 data-en="Account Information" data-pt="Informações da Conta">Account Information</h3>
                        <div class="user-info-display">
                            <div class="info-row">
                                <span class="info-label" data-en="Name:" data-pt="Nome:">Name:</span>
                                <span class="info-value"><?php echo htmlspecialchars($usuario_dados['nome_completo']); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label" data-en="Email:" data-pt="Email:">Email:</span>
                                <span class="info-value"><?php echo htmlspecialchars($usuario_dados['email']); ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="form-section">
                        <h3 data-en="Payment Method" data-pt="Método de Pagamento">Payment Method</h3>
                        <div class="payment-methods-toggle">
                            <button type="button" class="payment-toggle-btn active" data-method="pix">
                                <i class="fas fa-qrcode"></i>
                                <span>PIX</span>
                            </button>
                            <button type="button" class="payment-toggle-btn" data-method="credit_card">
                                <i class="fas fa-credit-card"></i>
                                <span data-en="Credit Card" data-pt="Cartão de Crédito">Credit Card</span>
                            </button>
                        </div>
                    </div>

                    <!-- PIX Form (Default) -->
                    <div id="pixForm" class="form-section payment-details">
                        <div class="pix-info">
                            <i class="fas fa-info-circle"></i>
                            <p data-en="After confirming, you will receive a QR Code to complete the payment via PIX." data-pt="Após confirmar, você receberá um QR Code para completar o pagamento via PIX.">
                                After confirming, you will receive a QR Code to complete the payment via PIX.
                            </p>
                        </div>
                    </div>

                    <!-- Credit Card Form (Hidden by default) -->
                    <div id="creditCardForm" class="form-section payment-details" style="display: none;">
                        <div class="form-group">
                            <label data-en="Card Number" data-pt="Número do Cartão">Card Number</label>
                            <input type="text" id="cardNumber" maxlength="19" placeholder="1234 5678 9012 3456" required>
                            <div class="card-icons">
                                <i class="fab fa-cc-visa"></i>
                                <i class="fab fa-cc-mastercard"></i>
                                <i class="fab fa-cc-amex"></i>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label data-en="Expiry Date" data-pt="Data de Validade">Expiry Date</label>
                                <input type="text" id="expiryDate" maxlength="7" placeholder="MM/YYYY" required>
                            </div>
                            <div class="form-group">
                                <label>CVV</label>
                                <input type="text" id="cvv" maxlength="4" placeholder="123" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label data-en="Cardholder Name" data-pt="Nome no Cartão">Cardholder Name</label>
                            <input type="text" id="cardholderName" placeholder="JOHN DOE" required>
                        </div>
                    </div>

                    <!-- Terms -->
                    <div class="form-section">
                        <label class="checkbox-label">
                            <input type="checkbox" id="termsAgree" required>
                            <span data-en="I agree to the Terms of Service and Privacy Policy" data-pt="Concordo com os Termos de Serviço e Política de Privacidade">
                                I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>
                            </span>
                        </label>
                    </div>

                    <!-- Total and Submit -->
                    <div class="form-total">
                        <div class="total-row">
                            <span data-en="Total:" data-pt="Total:">Total:</span>
                            <span class="total-amount">R$ <?php echo number_format($plan_price, 2, ',', '.'); ?></span>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit-payment">
                        <i class="fas fa-lock"></i>
                        <span data-en="Complete Purchase" data-pt="Finalizar Compra">Complete Purchase</span>
                    </button>

                    <p class="secure-notice">
                        <i class="fas fa-shield-alt"></i>
                        <span data-en="Secure payment processing" data-pt="Processamento de pagamento seguro">Secure payment processing</span>
                    </p>
                </form>

                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<script src="<?php echo get_template_directory_uri(); ?>/js/upgrade.js"></script>
<script>
// Passar dados do PHP para JavaScript
const upgradeData = {
    plan: '<?php echo $plan; ?>',
    period: '<?php echo $period; ?>',
    price: <?php echo $plan_price; ?>,
    userId: <?php echo $usuario_logado ? $usuario_dados['id'] : 'null'; ?>,
    isLoggedIn: <?php echo $usuario_logado ? 'true' : 'false'; ?>
};
</script>

<?php get_footer(); ?>

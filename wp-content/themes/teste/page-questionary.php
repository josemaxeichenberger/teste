<?php
/**
 * Template Name: Questionário
 * Description: Página de questionário de triagem para usuários
 */

// Iniciar sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar se o usuário está logado
$isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$userFullName = $isLoggedIn ? $_SESSION['user_name'] : 'User';
$userName = explode(' ', $userFullName)[0];
$userEmail = $isLoggedIn ? $_SESSION['user_email'] : '';
$userId = $isLoggedIn ? $_SESSION['user_id'] : null;

// Redirecionar se não estiver logado
if (!$isLoggedIn) {
    wp_redirect(home_url());
    exit;
}

// Carregar perguntas do questionário
$perguntas = [];
$totalPerguntas = 0;

try {
    error_log("=== INICIANDO CARREGAMENTO DO QUESTIONÁRIO ===");
    
    // Criar conexão PDO direta com o banco do questionário
    // (evita conflitos de constantes com WordPress)
    $db_config = [
        'host' => 'localhost',
        'dbname' => 'poker_111',
        'user' => 'poker_111',
        'pass' => 'v=NX$UjtOAx2*RU.',
        'charset' => 'utf8mb4'
    ];
    
    error_log("Conectando ao banco: " . $db_config['dbname']);
    
    $dsn = "mysql:host={$db_config['host']};dbname={$db_config['dbname']};charset={$db_config['charset']}";
    
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$db_config['charset']}"
    ];
    
    $conn = new PDO($dsn, $db_config['user'], $db_config['pass'], $options);
    error_log("✅ Conexão estabelecida com sucesso");
    
    // Buscar todas as perguntas ativas
    $sql = "SELECT 
                id,
                texto_pergunta,
                ordem_pergunta,
                tipo_pergunta
            FROM questionario_perguntas
            WHERE esta_ativa = 1
            ORDER BY ordem_pergunta ASC";
    
    error_log("Executando query de perguntas...");
    $stmt = $conn->query($sql);
    $perguntas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    error_log("✅ Perguntas encontradas: " . count($perguntas));
    
    // Para cada pergunta, buscar suas opções
    foreach ($perguntas as &$pergunta) {
        $sqlOpcoes = "SELECT 
                        id,
                        texto_opcao,
                        ordem_opcao
                      FROM questionario_opcoes
                      WHERE pergunta_id = :pergunta_id
                      ORDER BY ordem_opcao ASC";
        
        $stmtOpcoes = $conn->prepare($sqlOpcoes);
        $stmtOpcoes->execute(['pergunta_id' => $pergunta['id']]);
        $pergunta['opcoes'] = $stmtOpcoes->fetchAll(PDO::FETCH_ASSOC);
        error_log("Opções para pergunta " . $pergunta['id'] . ": " . count($pergunta['opcoes']));
    }
    
    $totalPerguntas = count($perguntas);
    error_log("✅ Total de perguntas carregadas: " . $totalPerguntas);
    error_log("=== CARREGAMENTO CONCLUÍDO COM SUCESSO ===");
    
} catch (PDOException $e) {
    $erro_msg = "Erro de conexão com banco de dados: " . $e->getMessage();
    error_log("❌ " . $erro_msg);
    error_log("Stack trace: " . $e->getTraceAsString());
    
    wp_die(
        '<h1>Erro ao carregar questionário</h1>' .
        '<p><strong>Detalhes do erro:</strong></p>' .
        '<div style="background: #f5f5f5; padding: 15px; border-left: 4px solid #dc3545; margin: 20px 0;">' .
        htmlspecialchars($erro_msg) .
        '</div>' .
        '<p><strong>Configuração do Banco:</strong></p>' .
        '<ul>' .
        '<li><strong>Host:</strong> localhost</li>' .
        '<li><strong>Database:</strong> poker_111</li>' .
        '<li><strong>Usuário:</strong> poker_111</li>' .
        '</ul>' .
        '<p><a href="' . home_url() . '" style="color: #0073aa; text-decoration: none;">← Voltar para a página inicial</a></p>'
    );
} catch (Exception $e) {
    $erro_msg = $e->getMessage();
    error_log("❌ ERRO ao carregar perguntas: " . $erro_msg);
    error_log("Stack trace: " . $e->getTraceAsString());
    
    wp_die(
        '<h1>Erro ao carregar questionário</h1>' .
        '<p><strong>Detalhes do erro:</strong></p>' .
        '<div style="background: #f5f5f5; padding: 15px; border-left: 4px solid #dc3545; margin: 20px 0;">' .
        htmlspecialchars($erro_msg) .
        '</div>' .
        '<p><a href="' . home_url() . '" style="color: #0073aa; text-decoration: none;">← Voltar para a página inicial</a></p>'
    );
}
?>

<!-- Questionary Modal CSS -->
<link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/css/questionario-styles.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- Questionnaire Modal -->
<div id="questionnaireModal" class="questionnaire-modal">
    <div class="questionnaire-modal-content">
        <div class="questionnaire-modal-header">
            <button class="modal-back-btn" id="modalBackBtn" onclick="previousQuestion()">
                <i class="fas fa-arrow-left"></i>
            </button>
            <div class="progress-bar-container">
                <div class="progress-segments" id="progressSegments">
                    <?php for ($i = 0; $i < $totalPerguntas; $i++): ?>
                        <div class="progress-segment <?= $i === 0 ? 'active' : '' ?>"></div>
                    <?php endfor; ?>
                </div>
                <span class="question-counter" id="questionCounter">1/<?= $totalPerguntas ?></span>
            </div>
            <button class="modal-close-btn" onclick="showCancelSurveyModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="questionnaire-modal-body">
            <h2 class="modal-question-text" id="modalQuestionText">
                <!-- Pergunta será carregada via JavaScript -->
            </h2>
            
            <div class="modal-answer-options" id="modalAnswerOptions">
                <!-- Opções serão inseridas via JavaScript -->
            </div>
            
            <button class="btn-modal-next" id="btnModalNext" onclick="nextQuestion()" disabled>Próximo</button>
        </div>
    </div>
</div>

<!-- Cancel Survey Confirmation Modal -->
<div id="cancelSurveyModal" class="questionnaire-modal" style="z-index: 10001;">
    <div class="cancel-survey-content">
        <div class="cancel-modal-header">
            <img src="<?php echo get_template_directory_uri(); ?>/logopoker.png" alt="POKER III" class="cancel-modal-logo">
            <button class="cancel-modal-close-btn" onclick="hideCancelSurveyModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <h2 class="cancel-modal-title">Cancelar Questionário?</h2>
        
        <p class="cancel-modal-text">Você realmente deseja finalizar o questionário?</p>
        
        <p class="cancel-modal-subtext">
            Salvaremos todos os dados fornecidos para que você possa<br>
            continuar facilmente a qualquer momento de onde parou.
        </p>
        
        <div class="cancel-modal-buttons">
            <button class="btn-cancel-yes" onclick="confirmCancelSurvey()">Sim, Cancelar</button>
            <button class="btn-cancel-no" onclick="hideCancelSurveyModal()">Não, voltar</button>
        </div>
    </div>
</div>

<!-- Modal de Notificação Customizado -->
<div id="customNotificationModal" class="questionnaire-modal" style="z-index: 10002;">
    <div class="cancel-survey-content" style="max-width: 500px;">
        <div class="cancel-modal-header">
            <img src="<?php echo get_template_directory_uri(); ?>/logopoker.png" alt="POKER III" class="cancel-modal-logo">
        </div>
        
        <div id="notificationIcon" style="text-align: center; margin: 20px 0;">
            <i class="fas fa-clipboard-check" style="font-size: 48px; color: #00d4ff;"></i>
        </div>
        
        <h2 class="cancel-modal-title" id="notificationTitle">Questionário</h2>
        
        <p class="cancel-modal-text" id="notificationMessage">
            Hey! Podemos dar continuidade com o questionário?<br>
            Será uma pequena triagem para conhecer melhor você.
        </p>
        
        <div class="cancel-modal-buttons" id="notificationButtons">
            <button class="btn-cancel-yes" onclick="handleNotificationAction('confirm')">Sim, vamos lá!</button>
            <button class="btn-cancel-no" onclick="handleNotificationAction('cancel')">Agora não</button>
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

// Dados das perguntas carregadas do PHP
const questions = <?= json_encode($perguntas, JSON_UNESCAPED_UNICODE) ?>;
const totalQuestions = <?= $totalPerguntas ?>;

console.log('Perguntas carregadas:', questions);

let currentQuestion = 0;
let selectedAnswer = null;
let userAnswers = []; // Armazenar respostas do usuário

// Garantir userId da sessão PHP
function getOrCreateUserId() {
    let userId = window.phpUserData.userId;
    
    if (!userId) {
        console.error('❌ Usuário não está logado');
        return null;
    }
    
    // Colocar no sessionStorage também para compatibilidade
    sessionStorage.setItem('userId', userId);
    return userId;
}

// Inicializar userId
getOrCreateUserId();

// ============================================================================
// MODAL DE NOTIFICAÇÃO CUSTOMIZADO
// ============================================================================
let notificationCallback = null;

function showCustomNotification(options, callback) {
    const modal = document.getElementById('customNotificationModal');
    const icon = document.getElementById('notificationIcon');
    const title = document.getElementById('notificationTitle');
    const message = document.getElementById('notificationMessage');
    const buttons = document.getElementById('notificationButtons');
    
    // Configurar ícone
    if (options.icon) {
        icon.innerHTML = `<i class="${options.icon}" style="font-size: 48px; color: ${options.iconColor || '#00d4ff'};"></i>`;
    }
    
    // Configurar título
    title.textContent = options.title || 'Notificação';
    
    // Configurar mensagem
    message.innerHTML = options.message || '';
    
    // Configurar botões
    if (options.buttons) {
        buttons.innerHTML = '';
        options.buttons.forEach(btn => {
            const button = document.createElement('button');
            button.className = btn.type === 'primary' ? 'btn-cancel-yes' : 'btn-cancel-no';
            button.textContent = btn.text;
            button.onclick = () => handleNotificationAction(btn.action);
            buttons.appendChild(button);
        });
    } else {
        // Botão padrão de OK
        buttons.innerHTML = '<button class="btn-cancel-yes" onclick="closeCustomNotification()">OK</button>';
    }
    
    // Salvar callback
    notificationCallback = callback || options.callback || null;
    
    // Mostrar modal
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeCustomNotification() {
    const modal = document.getElementById('customNotificationModal');
    modal.classList.remove('active');
    document.body.style.overflow = '';
    notificationCallback = null;
}

function handleNotificationAction(action) {
    console.log('🔔 handleNotificationAction chamado com:', action);
    
    if (notificationCallback) {
        notificationCallback(action);
    } else {
        closeCustomNotification();
    }
}

function openQuestionnaireModal() {
    document.getElementById('questionnaireModal').classList.add('active');
    document.body.style.overflow = 'hidden';
    loadQuestion(0);
}

function closeQuestionnaireModal() {
    document.getElementById('questionnaireModal').classList.remove('active');
    document.body.style.overflow = '';
    currentQuestion = 0;
    selectedAnswer = null;
}

function showCancelSurveyModal() {
    document.getElementById('cancelSurveyModal').classList.add('active');
}

function hideCancelSurveyModal() {
    document.getElementById('cancelSurveyModal').classList.remove('active');
}

function confirmCancelSurvey() {
    const userId = getOrCreateUserId();
    
    if (userId) {
        fetch('/api/postpone-questionnaire.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                userId: userId,
                minutos: 15
            })
        }).then(response => response.json())
          .then(result => {
              console.log('⏰ Questionário adiado:', result);
          })
          .catch(error => {
              console.error('Erro ao adiar:', error);
          });
    }
    
    hideCancelSurveyModal();
    closeQuestionnaireModal();
    
    // Redirecionar para a página inicial
    window.location.href = '<?php echo home_url(); ?>';
}

function loadQuestion(index) {
    currentQuestion = index;
    const question = questions[index];
    
    console.log('Carregando pergunta:', index, question);
    
    // Atualizar contador
    document.getElementById('questionCounter').textContent = `${index + 1}/${totalQuestions}`;
    
    // Atualizar segmentos de progresso
    const segments = document.querySelectorAll('.progress-segment');
    segments.forEach((segment, i) => {
        segment.classList.remove('active', 'completed');
        if (i < index) {
            segment.classList.add('completed');
        } else if (i === index) {
            segment.classList.add('active');
        }
    });
    
    // Atualizar pergunta
    document.getElementById('modalQuestionText').textContent = question.texto_pergunta;
    
    // Atualizar opções
    const optionsContainer = document.getElementById('modalAnswerOptions');
    optionsContainer.innerHTML = '';
    
    question.opcoes.forEach((opcao, i) => {
        const optionDiv = document.createElement('div');
        optionDiv.className = 'modal-answer-option';
        optionDiv.innerHTML = `
            <input type="radio" name="answer" id="option${i}" value="${opcao.id}" data-text="${opcao.texto_opcao}">
            <label for="option${i}">${opcao.texto_opcao}</label>
            <i class="fas fa-check option-check"></i>
        `;
        optionDiv.onclick = function() {
            selectAnswer(i, opcao.id);
        };
        optionsContainer.appendChild(optionDiv);
    });
    
    // Mostrar/esconder botão de voltar
    const backBtn = document.getElementById('modalBackBtn');
    if (index === 0) {
        backBtn.style.visibility = 'hidden';
    } else {
        backBtn.style.visibility = 'visible';
    }
    
    // Resetar seleção
    selectedAnswer = null;
    document.getElementById('btnModalNext').disabled = true;
}

function selectAnswer(index, opcaoId) {
    // Remover seleção anterior
    document.querySelectorAll('.modal-answer-option').forEach(opt => {
        opt.classList.remove('selected');
    });
    
    // Adicionar nova seleção
    const options = document.querySelectorAll('.modal-answer-option');
    options[index].classList.add('selected');
    options[index].querySelector('input').checked = true;
    
    selectedAnswer = {
        perguntaId: questions[currentQuestion].id,
        opcaoId: opcaoId,
        textoOpcao: options[index].querySelector('label').textContent
    };
    
    console.log('Resposta selecionada:', selectedAnswer);
    
    // Salvar imediatamente no array de respostas
    userAnswers[currentQuestion] = selectedAnswer;
    
    // Salvar no banco de dados imediatamente
    saveProgressToDatabase();
    
    document.getElementById('btnModalNext').disabled = false;
}

function nextQuestion() {
    if (selectedAnswer === null) return;
    
    // Salvar resposta
    userAnswers[currentQuestion] = selectedAnswer;
    
    console.log('Respostas até agora:', userAnswers);
    
    // Salvar progresso no banco de dados sempre
    saveProgressToDatabase();
    
    if (currentQuestion < totalQuestions - 1) {
        loadQuestion(currentQuestion + 1);
    } else {
        // Última pergunta - finalizar e salvar no banco
        finalizarQuestionario();
    }
}

function previousQuestion() {
    if (currentQuestion > 0) {
        loadQuestion(currentQuestion - 1);
    }
}

async function saveProgressToDatabase() {
    const userId = getOrCreateUserId();
    
    if (!userId) {
        console.log('Erro: userId não encontrado');
        return;
    }
    
    console.log('💾 Salvando progresso no banco... userId:', userId, 'pergunta:', currentQuestion + 1);
    
    try {
        const response = await fetch('/api/save-progress.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                userId: userId,
                perguntaAtual: currentQuestion + 1,
                totalPerguntas: totalQuestions,
                respostas: userAnswers
            })
        });
        
        const result = await response.json();
        console.log('📊 Resultado do salvamento:', result);
        
    } catch (error) {
        console.error('❌ Erro ao salvar progresso:', error);
    }
}

async function finalizarQuestionario() {
    console.log('✅ Finalizando questionário com respostas:', userAnswers);
    
    const userId = getOrCreateUserId();
    
    if (!userId) {
        showCustomNotification({
            icon: 'fas fa-exclamation-triangle',
            iconColor: '#ffc107',
            title: 'Erro de Identificação',
            message: 'Não foi possível identificar sua sessão.<br><br>Por favor, faça login novamente.',
            buttons: [
                { text: 'Entendi', type: 'primary', action: 'ok' }
            ]
        });
        return;
    }
    
    console.log('📤 Enviando respostas finais para o banco...');
    
    try {
        const response = await fetch('/api/save-answers.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                userId: userId,
                respostas: userAnswers
            })
        });
        
        const result = await response.json();
        console.log('📊 Resultado final:', result);
        
        if (result.success) {
            closeQuestionnaireModal();
            
            showCustomNotification({
                icon: 'fas fa-trophy',
                iconColor: '#ffd700',
                title: 'Questionário Concluído!',
                message: '🎉 Parabéns! Você completou sua triagem com sucesso.<br><br>Obrigado pelas suas respostas!',
                buttons: [
                    { text: 'Ir para Dashboard', type: 'primary', action: 'dashboard' }
                ]
            }, (action) => {
                if (action === 'dashboard') {
                    window.location.href = '<?php echo home_url('/dashboard'); ?>';
                }
            });
        } else {
            showCustomNotification({
                icon: 'fas fa-times-circle',
                iconColor: '#dc3545',
                title: 'Erro ao Salvar',
                message: `Não foi possível salvar suas respostas.<br><br><small>${result.message}</small>`,
                buttons: [
                    { text: 'Tentar Novamente', type: 'primary', action: 'retry' },
                    { text: 'Cancelar', type: 'secondary', action: 'cancel' }
                ]
            }, (action) => {
                if (action === 'retry') {
                    finalizarQuestionario();
                }
            });
        }
        
    } catch (error) {
        console.error('❌ Erro ao salvar respostas:', error);
        showCustomNotification({
            icon: 'fas fa-exclamation-circle',
            iconColor: '#dc3545',
            title: 'Erro de Conexão',
            message: 'Não foi possível conectar ao servidor.<br><br>Verifique sua conexão e tente novamente.',
            buttons: [
                { text: 'Tentar Novamente', type: 'primary', action: 'retry' },
                { text: 'Cancelar', type: 'secondary', action: 'cancel' }
            ]
        }, (action) => {
            if (action === 'retry') {
                finalizarQuestionario();
            }
        });
    }
}

// Verificar progresso salvo ao carregar a página
window.addEventListener('DOMContentLoaded', async () => {
    const userId = getOrCreateUserId();
    
    if (!userId) {
        console.log('❌ Usuário não está logado');
        return;
    }
    
    try {
        const response = await fetch(`/api/get-progress.php?userId=${userId}`);
        const result = await response.json();
        
        console.log('📊 Progresso encontrado:', result);
        
        // Verificar se está adiado
        if (result.success && result.data.estaAdiado) {
            console.log('⏰ Questionário adiado até:', result.data.adiadoAte);
            console.log('⏳ Tempo restante:', result.data.tempoRestante, 'segundos');
            return; // Não mostrar modal se ainda está adiado
        }
        
        if (result.success && result.data.temProgresso && !result.data.estaCompleto) {
            const progresso = result.data.progresso;
            const respostas = result.data.respostas;
            
            // Reconstruir array de respostas
            respostas.forEach((resp, index) => {
                userAnswers[index] = {
                    perguntaId: resp.pergunta_id,
                    opcaoId: resp.opcao_id,
                    textoOpcao: resp.texto_opcao
                };
            });
            
            console.log('📝 Progresso carregado. Você pode continuar de onde parou.');
            
            // Mostrar modal de notificação perguntando se quer continuar
            setTimeout(() => {
                showCustomNotification({
                    icon: 'fas fa-clipboard-check',
                    iconColor: '#00d4ff',
                    title: 'Hey! Podemos dar Continuidade?',
                    message: `Você já respondeu <strong>${respostas.length} pergunta(s)</strong>.<br>Deseja continuar de onde parou?`,
                    buttons: [
                        { text: 'Sim, vamos lá!', type: 'primary', action: 'continue' },
                        { text: 'Mais tarde', type: 'secondary', action: 'later' }
                    ]
                }, async (action) => {
                    closeCustomNotification();
                    
                    if (action === 'continue') {
                        setTimeout(() => {
                            openQuestionnaireModal();
                            loadQuestion(progresso.pergunta_atual - 1 || 0);
                        }, 300);
                    } else if (action === 'later') {
                        // Salvar adiamento no banco antes de redirecionar
                        try {
                            await fetch('/api/postpone-questionnaire.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ userId: userId, minutos: 15 })
                            });
                            console.log('⏰ Questionário adiado por 15 minutos');
                        } catch (error) {
                            console.error('Erro ao adiar:', error);
                        }
                        window.location.href = '<?php echo home_url(); ?>';
                    }
                });
            }, 500);
        } else {
            // Se não tem progresso, mostrar modal de boas-vindas
            setTimeout(() => {
                showCustomNotification({
                    icon: 'fas fa-clipboard-check',
                    iconColor: '#00d4ff',
                    title: 'Questionário',
                    message: 'Hey! Podemos dar continuidade com o questionário?<br>Será uma pequena triagem para conhecer melhor você.',
                    buttons: [
                        { text: 'Sim, vamos lá!', type: 'primary', action: 'start' },
                        { text: 'Agora não', type: 'secondary', action: 'later' }
                    ]
                }, async (action) => {
                    closeCustomNotification();
                    
                    if (action === 'start') {
                        setTimeout(() => {
                            openQuestionnaireModal();
                        }, 300);
                    } else if (action === 'later') {
                        // Salvar adiamento no banco antes de redirecionar
                        try {
                            await fetch('/api/postpone-questionnaire.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ userId: userId, minutos: 15 })
                            });
                            console.log('⏰ Questionário adiado por 15 minutos');
                        } catch (error) {
                            console.error('Erro ao adiar:', error);
                        }
                        window.location.href = '<?php echo home_url(); ?>';
                    }
                });
            }, 500);
        }
        
    } catch (error) {
        console.error('❌ Erro ao verificar progresso:', error);
        
        // Mesmo com erro, mostrar modal de boas-vindas
        setTimeout(() => {
            showCustomNotification({
                icon: 'fas fa-clipboard-check',
                iconColor: '#00d4ff',
                title: 'Questionário',
                message: 'Hey! Podemos dar continuidade com o questionário?<br>Será uma pequena triagem para conhecer melhor você.',
                buttons: [
                    { text: 'Sim, vamos lá!', type: 'primary', action: 'start' },
                    { text: 'Agora não', type: 'secondary', action: 'later' }
                ]
            }, async (action) => {
                closeCustomNotification();
                
                if (action === 'start') {
                    setTimeout(() => {
                        openQuestionnaireModal();
                    }, 300);
                } else if (action === 'later') {
                    // Salvar adiamento no banco antes de redirecionar
                    try {
                        await fetch('/api/postpone-questionnaire.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ userId: userId, minutos: 15 })
                        });
                        console.log('⏰ Questionário adiado por 15 minutos');
                    } catch (error) {
                        console.error('Erro ao adiar:', error);
                    }
                    window.location.href = '<?php echo home_url(); ?>';
                }
            });
        }, 500);
    }
});
</script>

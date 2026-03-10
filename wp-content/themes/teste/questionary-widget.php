<?php
/**
 * Questionário Widget
 * Componente reutilizável para exibir modal de questionário em qualquer página
 * 
 * Uso: <?php get_template_part('questionary-widget'); ?>
 */

// Verificar se usuário está logado
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$userId = $isLoggedIn ? $_SESSION['user_id'] : null;

// Só mostrar para usuários logados
if (!$isLoggedIn || !$userId) {
    return;
}

// Carregar perguntas do banco de dados
$perguntas = [];
$totalPerguntas = 0;

try {
    $db_config = [
        'host' => 'localhost',
        'dbname' => 'poker_111',
        'user' => 'poker_111',
        'pass' => 'v=NX$UjtOAx2*RU.',
        'charset' => 'utf8mb4'
    ];
    
    $dsn = "mysql:host={$db_config['host']};dbname={$db_config['dbname']};charset={$db_config['charset']}";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    $conn = new PDO($dsn, $db_config['user'], $db_config['pass'], $options);
    
    // Buscar perguntas ativas (incluindo campos de track)
    $sql = "SELECT id, texto_pergunta, ordem_pergunta, tipo_pergunta, track_requerido, pergunta_logica
            FROM questionario_perguntas
            WHERE esta_ativa = 1
            ORDER BY pergunta_logica ASC, track_requerido ASC";
    
    $stmt = $conn->query($sql);
    $perguntas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Buscar opções para cada pergunta
    foreach ($perguntas as &$pergunta) {
        $sqlOpcoes = "SELECT id, texto_opcao, ordem_opcao, tag, define_track
                      FROM questionario_opcoes
                      WHERE pergunta_id = :pergunta_id
                      ORDER BY ordem_opcao ASC";
        
        $stmtOpcoes = $conn->prepare($sqlOpcoes);
        $stmtOpcoes->execute(['pergunta_id' => $pergunta['id']]);
        $pergunta['opcoes'] = $stmtOpcoes->fetchAll(PDO::FETCH_ASSOC);
    }
    
    $totalPerguntas = count($perguntas);
    
} catch (Exception $e) {
    error_log("❌ Erro ao carregar questionário widget: " . $e->getMessage());
    return; // Se der erro, não mostrar o widget
}

// Se não tem perguntas, não mostrar
if ($totalPerguntas === 0) {
    return;
}
?>

<!-- CSS do Questionário -->
<link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/css/questionario-styles.css">

<!-- Modal de Questionário -->
<div id="questionnaireModal" class="questionnaire-modal">
    <div class="questionnaire-modal-content">
        <div class="questionnaire-modal-header">
            <button class="modal-back-btn" id="modalBackBtn" onclick="questionaryWidget.previousQuestion()">
                <i class="fas fa-arrow-left"></i>
            </button>
            <div class="progress-bar-container">
                <div class="progress-segments" id="progressSegments">
                    <?php for ($i = 0; $i < 8; $i++): ?>
                        <div class="progress-segment <?= $i === 0 ? 'active' : '' ?>"></div>
                    <?php endfor; ?>
                </div>
                <span class="question-counter" id="questionCounter">1/8</span>
            </div>
            <button class="modal-close-btn" onclick="questionaryWidget.showCancelModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="questionnaire-modal-body">
            <h2 class="modal-question-text" id="modalQuestionText"></h2>
            
            <div class="modal-answer-options" id="modalAnswerOptions"></div>
            
            <button class="btn-modal-next" id="btnModalNext" onclick="questionaryWidget.nextQuestion()" disabled>Próximo</button>
        </div>
    </div>
</div>

<!-- Modal de Cancelamento -->
<div id="cancelSurveyModal" class="questionnaire-modal" style="z-index: 10001;">
    <div class="cancel-survey-content">
        <div class="cancel-modal-header">
            <img src="<?php echo get_template_directory_uri(); ?>/logopoker.png" alt="POKER III" class="cancel-modal-logo">
            <button class="cancel-modal-close-btn" onclick="questionaryWidget.hideCancelModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <h2 class="cancel-modal-title">Cancel Survey?</h2>
        <p class="cancel-modal-text">Do you really want to finish the survey?</p>
        <p class="cancel-modal-subtext">
            We will save all the data you provided so you can easily 
continue at any time from where you left off.
        </p>
        
        <div class="cancel-modal-buttons">
            <button class="btn-cancel-yes" onclick="questionaryWidget.confirmCancel()">Yes, Cancel</button>
            <button class="btn-cancel-no" onclick="questionaryWidget.hideCancelModal()">No, go back</button>
        </div>
    </div>
</div>

<!-- Modal de Notificação -->
<div id="customNotificationModal" class="questionnaire-modal" style="z-index: 10002;">
    <div class="cancel-survey-content" style="max-width: 500px;">
        <div class="cancel-modal-header">
            <img src="<?php echo get_template_directory_uri(); ?>/logopoker.png" alt="POKER III" class="cancel-modal-logo">
        </div>
        
        <div id="notificationIcon" style="text-align: center; margin: 20px 0;">
            <i class="fas fa-clipboard-check" style="font-size: 48px; color: #00d4ff;"></i>
        </div>
        
        <h2 class="cancel-modal-title" id="notificationTitle">Questionário</h2>
        <p class="cancel-modal-text" id="notificationMessage"></p>
        
        <div class="cancel-modal-buttons" id="notificationButtons"></div>
    </div>
</div>

<!-- JavaScript do Questionário -->
<script>
(function() {
    'use strict';
    
    // Namespace para evitar conflitos
    window.questionaryWidget = {
        allQuestions: <?= json_encode($perguntas, JSON_UNESCAPED_UNICODE) ?>,
        questions: [],
        totalQuestions: 0,
        currentQuestion: 0,
        currentLogicQuestion: 0,
        selectedAnswer: null,
        userAnswers: [],
        userId: <?= json_encode($userId) ?>,
        currentTrack: null,
        notificationCallback: null,
        
        init: function() {
            // console.log('🎯 Iniciando questionário com tracks');
            this.checkProgress();
        },
        
        filterQuestionsByTrack: function() {
            this.questions = this.allQuestions.filter(q => {
                if (!q.track_requerido) return true;
                return q.track_requerido === this.currentTrack;
            });
            this.totalQuestions = this.questions.length;
            // console.log(`📊 Track: ${this.currentTrack || 'INICIAL'} - Perguntas: ${this.totalQuestions}`);
        },
        
        updateTrack: function(opcao) {
            // console.log('🔍 updateTrack chamado:', opcao);
            
            if (opcao && opcao.define_track) {
                const oldTrack = this.currentTrack;
                this.currentTrack = opcao.define_track;
                // console.log(`🔄 Track: ${oldTrack || 'INICIAL'} → ${this.currentTrack}`);
                this.filterQuestionsByTrack();
            } else {
                // console.log('⚠️ Opção sem define_track:', opcao);
            }
        },
        
        async checkProgress() {
            if (!this.userId) return;
            
            try {
                const response = await fetch(`/api/get-progress.php?userId=${this.userId}`);
                const result = await response.json();
                
                // console.log('📊 Widget - Progresso:', result);
                
                // Verificar se está adiado
                if (result.success && result.data.estaAdiado) {
                    // console.log('⏰ Widget - Questionário adiado até:', result.data.adiadoAte);
                    return;
                }
                
                // Se já completou, não mostrar
                if (result.success && result.data.estaCompleto) {
                    // console.log('✅ Widget - Questionário já completo');
                    return;
                }
                
                // Se tem progresso, perguntar se quer continuar
                if (result.success && result.data.temProgresso) {
                    const respostas = result.data.respostas;
                    const progresso = result.data.progresso;
                    
                    // Reconstruir respostas
                    respostas.forEach((resp, index) => {
                        this.userAnswers[index] = {
                            perguntaId: resp.pergunta_id,
                            opcaoId: resp.opcao_id,
                            textoOpcao: resp.texto_opcao
                        };
                    });
                    
                    // Recuperar track atual do progresso
                    if (progresso.track_atual) {
                        this.currentTrack = progresso.track_atual;
                        // console.log(`🔄 Track recuperado: ${this.currentTrack}`);
                    }
                    
                    setTimeout(() => {
                        this.showNotification({
                            icon: 'fas fa-clipboard-check',
                            iconColor: '#00d4ff',
                            title: 'Hey! Can we continue?',
                            message: `You have already answered <strong>${respostas.length} question(s)</strong>.<br>Would you like to continue from where you left off?`,
                            buttons: [
                                { text: 'Yes, let’s go!', type: 'primary', action: 'continue' },
                                { text: 'Later', type: 'secondary', action: 'later' }
                            ]
                        }, async (action) => {
                            this.closeNotification();
                            
                            if (action === 'continue') {
                                setTimeout(() => {
                                    // Filtrar perguntas pelo track recuperado
                                    this.filterQuestionsByTrack();
                                    
                                    // Abrir modal
                                    document.getElementById('questionnaireModal').classList.add('active');
                                    document.body.style.overflow = 'hidden';
                                    
                                    // Buscar índice da próxima pergunta lógica
                                    const nextLogicQuestion = progresso.pergunta_atual || 1;
                                    const questionIndex = this.questions.findIndex(q => q.pergunta_logica === nextLogicQuestion);
                                    
                                    if (questionIndex !== -1) {
                                        this.loadQuestion(questionIndex);
                                    } else {
                                        // Se não encontrou, começar do início
                                        this.loadQuestion(0);
                                    }
                                }, 300);
                            } else if (action === 'later') {
                                await this.postpone();
                            }
                        });
                    }, 500);
                } else {
                    // Primeira vez - mostrar convite
                    setTimeout(() => {
                        this.showNotification({
                            icon: 'fas fa-clipboard-check',
                            iconColor: '#00d4ff',
                            title: 'Questionário',
                            message: 'Hey! Can we continue with the questionnaire?<br>It will be a short screening to get to know you better.',
                            buttons: [
                                { text: 'Yes, let’s go!', type: 'primary', action: 'start' },
                                { text: 'Not now', type: 'secondary', action: 'later' }
                            ]
                        }, async (action) => {
                            this.closeNotification();
                            
                            if (action === 'start') {
                                setTimeout(() => this.openModal(), 300);
                            } else if (action === 'later') {
                                await this.postpone();
                            }
                        });
                    }, 500);
                }
            } catch (error) {
                console.error('❌ Widget - Erro ao verificar progresso:', error);
            }
        },
        
        showNotification(options, callback) {
            const modal = document.getElementById('customNotificationModal');
            const icon = document.getElementById('notificationIcon');
            const title = document.getElementById('notificationTitle');
            const message = document.getElementById('notificationMessage');
            const buttons = document.getElementById('notificationButtons');
            
            icon.innerHTML = `<i class="${options.icon}" style="font-size: 48px; color: ${options.iconColor || '#00d4ff'};"></i>`;
            title.textContent = options.title || 'Notificação';
            message.innerHTML = options.message || '';
            
            buttons.innerHTML = '';
            options.buttons.forEach(btn => {
                const button = document.createElement('button');
                button.className = btn.type === 'primary' ? 'btn-cancel-yes' : 'btn-cancel-no';
                button.textContent = btn.text;
                button.onclick = () => this.handleNotificationAction(btn.action);
                buttons.appendChild(button);
            });
            
            this.notificationCallback = callback;
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        },
        
        closeNotification() {
            document.getElementById('customNotificationModal').classList.remove('active');
            document.body.style.overflow = '';
            this.notificationCallback = null;
        },
        
        handleNotificationAction(action) {
            if (this.notificationCallback) {
                this.notificationCallback(action);
            }
        },
        
        openModal() {
            // Limpar respostas anteriores ao começar novo questionário
            this.userAnswers = [];
            this.currentTrack = null;
            
            this.filterQuestionsByTrack();
            document.getElementById('questionnaireModal').classList.add('active');
            document.body.style.overflow = 'hidden';
            this.loadQuestion(0);
        },
        
        closeModal() {
            document.getElementById('questionnaireModal').classList.remove('active');
            document.body.style.overflow = '';
        },
        
        showCancelModal() {
            document.getElementById('cancelSurveyModal').classList.add('active');
        },
        
        hideCancelModal() {
            document.getElementById('cancelSurveyModal').classList.remove('active');
        },
        
        async confirmCancel() {
            await this.postpone();
            this.hideCancelModal();
            this.closeModal();
            window.location.href = '<?php echo home_url(); ?>';
        },
        
        async postpone() {
            try {
                await fetch('/api/postpone-questionnaire.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ userId: this.userId, minutos: 15 })
                });
                // console.log('⏰ Widget - Questionário adiado por 15 minutos');
            } catch (error) {
                console.error('❌ Widget - Erro ao adiar:', error);
            }
        },
        
        loadQuestion(index) {
            this.currentQuestion = index;
            const question = this.questions[index];
            
            if (!question) {
                console.error(`❌ Pergunta no índice ${index} não existe. Total: ${this.questions.length}`);
                return;
            }
            
            this.currentLogicQuestion = question.pergunta_logica;
            
            // console.log(`📝 Q${question.pergunta_logica} (ID: ${question.id}, Track: ${question.track_requerido || 'SEMPRE'})`);
            
            const logicIndex = question.pergunta_logica;
            document.getElementById('questionCounter').textContent = `${logicIndex}/8`;
            
            const segments = document.querySelectorAll('.progress-segment');
            segments.forEach((segment, i) => {
                segment.classList.remove('active', 'completed');
                if (i < logicIndex - 1) segment.classList.add('completed');
                else if (i === logicIndex - 1) segment.classList.add('active');
            });
            
            document.getElementById('modalQuestionText').textContent = question.texto_pergunta;
            
            const optionsContainer = document.getElementById('modalAnswerOptions');
            optionsContainer.innerHTML = '';
            
            question.opcoes.forEach((opcao, i) => {
                const optionDiv = document.createElement('div');
                optionDiv.className = 'modal-answer-option';
                optionDiv.innerHTML = `
                    <input type="radio" name="answer" id="option${i}" value="${opcao.id}">
                    <label for="option${i}">${opcao.texto_opcao}</label>
                    <i class="fas fa-check option-check"></i>
                `;
                optionDiv.onclick = () => this.selectAnswer(i, opcao.id);
                optionsContainer.appendChild(optionDiv);
            });
            
            document.getElementById('modalBackBtn').style.visibility = index === 0 ? 'hidden' : 'visible';
            this.selectedAnswer = null;
            document.getElementById('btnModalNext').disabled = true;
        },
        
        selectAnswer(index, opcaoId) {
            document.querySelectorAll('.modal-answer-option').forEach(opt => {
                opt.classList.remove('selected');
            });
            
            const options = document.querySelectorAll('.modal-answer-option');
            options[index].classList.add('selected');
            options[index].querySelector('input').checked = true;
            
            const question = this.questions[this.currentQuestion];
            
            if (!question) {
                console.error(`❌ Pergunta atual não existe (índice: ${this.currentQuestion})`);
                return;
            }
            
            const opcao = question.opcoes.find(o => o.id == opcaoId);
            
            this.selectedAnswer = {
                perguntaId: question.id,
                perguntaLogica: question.pergunta_logica,
                opcaoId: opcaoId,
                textoOpcao: options[index].querySelector('label').textContent,
                tag: opcao.tag,
                defineTrack: opcao.define_track
            };
            
            // Atualizar track se a opção define um
            this.updateTrack(opcao);
            
            // Remover resposta anterior para esta pergunta (se existir)
            const existingIndex = this.userAnswers.findIndex(a => a.perguntaId === question.id);
            if (existingIndex !== -1) {
                this.userAnswers.splice(existingIndex, 1);
            }
            
            // Adicionar nova resposta
            this.userAnswers.push(this.selectedAnswer);
            this.saveProgress();
            
            document.getElementById('btnModalNext').disabled = false;
        },
        
        nextQuestion() {
            if (!this.selectedAnswer) return;
            
            this.saveProgress();
            
            const isLastQuestion = this.currentLogicQuestion >= 8;
            
            if (isLastQuestion) {
                // console.log('✅ Última pergunta - finalizando');
                this.finalize();
            } else {
                this.filterQuestionsByTrack();
                const nextLogicQuestion = this.currentLogicQuestion + 1;
                const nextQuestionIndex = this.questions.findIndex(q => q.pergunta_logica === nextLogicQuestion);
                
                if (nextQuestionIndex !== -1) {
                    this.loadQuestion(nextQuestionIndex);
                } else {
                    console.error('❌ Próxima pergunta não encontrada');
                    this.finalize();
                }
            }
        },
        
        previousQuestion() {
            if (this.currentQuestion > 0) {
                this.loadQuestion(this.currentQuestion - 1);
            }
        },
        
        async saveProgress() {
            try {
                const payload = {
                    userId: this.userId,
                    perguntaAtual: this.currentLogicQuestion,
                    totalPerguntas: 8,
                    trackAtual: this.currentTrack,
                    respostas: this.userAnswers
                };
                
                // console.log('📤 Enviando progresso:', JSON.stringify(payload));
                
                await fetch('/api/save-progress.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                // console.log(`💾 Progresso salvo - Q${this.currentLogicQuestion}/8 (Track: ${this.currentTrack || 'INICIAL'})`);
            } catch (error) {
                console.error('❌ Widget - Erro ao salvar:', error);
            }
        },
        
        async finalize() {
            try {
                const payload = {
                    userId: this.userId,
                    respostas: this.userAnswers
                };
                
                // console.log('📤 Finalizando questionário:', JSON.stringify(payload));
                
                const response = await fetch('/api/save-answers.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                
                const result = await response.json();
                // console.log('📥 Resposta save-answers:', JSON.stringify(result));
                
                if (result.success) {
                    this.closeModal();
                    this.showNotification({
                        icon: 'fas fa-trophy',
                        iconColor: '#ffd700',
                        title: 'Questionnaire Completed!',
                        message: '🎉 Congratulations! You have successfully completed your screening. Thank you for your responses!',
                        buttons: [
                            { text: 'To close', type: 'primary', action: 'close' }
                        ]
                    }, () => {
                        this.closeNotification();
                    });
                }
            } catch (error) {
                console.error('❌ Widget - Erro ao finalizar:', error);
            }
        }
    };
    
    // Inicializar quando DOM carregar
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => questionaryWidget.init());
    } else {
        questionaryWidget.init();
    }
})();
</script>

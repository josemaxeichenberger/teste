// Dados das perguntas carregadas do PHP
        const questions = <?= json_encode($perguntas, JSON_UNESCAPED_UNICODE) ?>;
        const totalQuestions = <?= $totalPerguntas ?>;
        
        console.log('Perguntas carregadas:', questions);
        
        let currentQuestion = 0;
        let selectedAnswer = null;
        let userAnswers = []; // Armazenar respostas do usuário
        
        // MODO TESTE: Criar userId temporário se não existir
        function getOrCreateTestUserId() {
            let userId = sessionStorage.getItem('userId');
            
            if (!userId) {
                // Verificar se já existe um userId de teste no localStorage
                userId = localStorage.getItem('testUserId');
                
                if (!userId) {
                    // Criar novo userId de teste (usar ID 1 que normalmente existe na tabela usuarios)
                    userId = '1'; // ID do primeiro usuário cadastrado
                    localStorage.setItem('testUserId', userId);
                    console.log('🧪 MODO TESTE: Usando userId:', userId);
                }
                
                // Colocar no sessionStorage também
                sessionStorage.setItem('userId', userId);
            }
            
            return userId;
        }
        
        // Inicializar userId de teste
        getOrCreateTestUserId();
        
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
            console.log('📞 Callback disponível:', !!notificationCallback);
            
            // NÃO fechar aqui - deixar o callback decidir quando fechar
            if (notificationCallback) {
                notificationCallback(action);
            } else {
                console.warn('⚠️ Nenhum callback definido para esta notificação');
                closeCustomNotification();
            }
        }
        // ============================================================================
        // FIM - MODAL DE NOTIFICAÇÃO CUSTOMIZADO
        // ============================================================================
        
        function toggleMobileMenu() {
            const navMenu = document.getElementById('navMenu');
            navMenu.classList.toggle('active');
        }
        
        function toggleLanguageMenu() {
            const dropdown = document.getElementById('languageDropdown');
            dropdown.classList.toggle('active');
        }
        
        function changeLanguage(lang, country, event) {
            event.stopPropagation();
            
            const currentFlag = document.getElementById('currentFlag');
            currentFlag.src = `https://flagcdn.com/w40/${country}.png`;
            currentFlag.alt = lang.toUpperCase();
            
            localStorage.setItem('selectedLanguage', lang);
            localStorage.setItem('selectedCountry', country);
            
            document.getElementById('languageDropdown').classList.remove('active');
            document.documentElement.lang = lang;
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
            // Adiar questionário por 15 minutos no banco
            const userId = sessionStorage.getItem('userId');
            
            if (userId) {
                fetch('api/postpone-questionnaire.php', {
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
            
            showCustomNotification({
                icon: 'fas fa-clock',
                iconColor: '#00d4ff',
                title: 'Progresso Salvo!',
                message: 'Não se preocupe, suas respostas foram salvas com segurança.',
                buttons: [
                    { text: 'Entendi', type: 'primary', action: 'ok' }
                ]
            });
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
            const userId = sessionStorage.getItem('userId');
            
            if (!userId) {
                console.log('Erro: userId não encontrado');
                return;
            }
            
            console.log('💾 Salvando progresso no banco... userId:', userId, 'pergunta:', currentQuestion + 1);
            
            try {
                const response = await fetch('api/save-progress.php', {
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
            
            // Pegar userId do sessionStorage
            const userId = sessionStorage.getItem('userId');
            
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
            
            // Enviar respostas para o servidor
            try {
                const response = await fetch('api/save-answers.php', {
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
                            { text: 'Ótimo!', type: 'primary', action: 'ok' }
                        ]
                    });
                    
                    // Redirecionar para dashboard ou landing page
                    // window.location.href = 'dashboard.html';
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
                            submitAnswers();
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
                        submitAnswers();
                    }
                });
            }
        }
        
        // Carregar idioma salvo e abrir modal automaticamente
        window.addEventListener('DOMContentLoaded', async () => {
            const savedLang = localStorage.getItem('selectedLanguage');
            const savedCountry = localStorage.getItem('selectedCountry');
            
            if (savedLang && savedCountry) {
                const currentFlag = document.getElementById('currentFlag');
                if (currentFlag) {
                    currentFlag.src = `https://flagcdn.com/w40/${savedCountry}.png`;
                    currentFlag.alt = savedLang.toUpperCase();
                }
                document.documentElement.lang = savedLang;
            }
            
            // Garantir que temos um userId (real ou teste)
            const userId = getOrCreateTestUserId();
            console.log('🔑 userId atual:', userId);
            
            // Verificar progresso salvo no banco de dados
            try {
                const response = await fetch(`api/get-progress.php?userId=${userId}`);
                
                // Verificar se a resposta é JSON válido
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    const text = await response.text();
                    console.error('❌ Resposta não é JSON:', text);
                    throw new Error('Resposta inválida da API: ' + text.substring(0, 200));
                }
                
                const result = await response.json();
                
                console.log('📊 Progresso encontrado:', result);
                
                // VERIFICAR SE ESTÁ ADIADO (15 MINUTOS)
                if (result.data.estaAdiado) {
                    const minutosRestantes = Math.ceil(result.data.tempoRestante / 60);
                    console.log(`⏰ Questionário adiado por mais ${minutosRestantes} minutos`);
                    
                    // Não mostrar nada - apenas retornar
                    return;
                }
                
                if (result.success && result.data.temProgresso) {
                    const progresso = result.data.progresso;
                    const respostas = result.data.respostas;
                    
                    // Se já está completo, não mostrar nada
                    if (result.data.estaCompleto) {
                        console.log('✅ Questionário já foi completado anteriormente');
                        return;
                    }
                    
                    // Se tem progresso parcial
                    if (respostas.length > 0) {
                        showCustomNotification({
                            icon: 'fas fa-clipboard-check',
                            iconColor: '#00d4ff',
                            title: 'Hey! Podemos dar Continuidade?',
                            message: `Será uma pequena triagem para conhecer melhor você.<br><br>Você já respondeu <strong>${respostas.length} pergunta(s)</strong>. Deseja continuar de onde parou?`,
                            buttons: [
                                { text: 'Sim, vamos lá!', type: 'primary', action: 'continue' },
                                { text: 'Mais tarde', type: 'secondary', action: 'postpone' }
                            ]
                        }, (action) => {
                            console.log('🚀 CALLBACK EXECUTADO - INÍCIO');
                            console.log('🎯 Ação selecionada:', action);
                            
                            try {
                                // Fechar modal de notificação primeiro
                                console.log('🔒 Fechando modal...');
                                closeCustomNotification();
                                console.log('✅ Modal fechado');
                                
                                if (action === 'continue') {
                                    console.log('▶️ Continuando do progresso...');
                                    // Reconstruir array de respostas
                                    respostas.forEach((resp, index) => {
                                        userAnswers[index] = {
                                            perguntaId: resp.pergunta_id,
                                            opcaoId: resp.opcao_id,
                                            textoOpcao: resp.texto_opcao
                                        };
                                    });
                                    
                                    console.log('📝 Respostas reconstruídas:', userAnswers);
                                    console.log('🎯 Abrindo na pergunta:', progresso.pergunta_atual - 1 || 0);
                                    
                                    setTimeout(() => {
                                        console.log('⏰ setTimeout executado');
                                        openQuestionnaireModal();
                                        console.log('🚪 Modal aberto');
                                        loadQuestion(progresso.pergunta_atual - 1 || 0);
                                        console.log('📄 Pergunta carregada');
                                }, 300);
                            } else if (action === 'postpone') {
                                console.log('⏰ Adiando questionário...');
                                // Adiar por 15 minutos
                                fetch('api/postpone-questionnaire.php', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify({
                                        userId: userId,
                                        minutos: 15
                                    })
                                }).then(response => response.json())
                                  .then(result => {
                                      console.log('✅ Questionário adiado:', result);
                                      showCustomNotification({
                                          icon: 'fas fa-clock',
                                          iconColor: '#00d4ff',
                                          title: 'Progresso Salvo!',
                                          message: 'Não se preocupe, suas respostas foram salvas com segurança.',
                                          buttons: [
                                              { text: 'Entendi', type: 'primary', action: 'ok' }
                                          ]
                                      });
                                  })
                                  .catch(error => {
                                      console.error('❌ Erro ao adiar:', error);
                                  });
                            }
                            } catch (error) {
                                console.error('❌ ERRO NO CALLBACK:', error);
                            }
                        });
                        return;
                    }
                }
            } catch (error) {
                console.error('❌ Erro ao verificar progresso:', error);
            }
            
            // Abrir modal após pequeno delay
            setTimeout(() => {
                openQuestionnaireModal();
            }, 300);
        });
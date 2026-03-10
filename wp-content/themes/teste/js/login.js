// @dev GustavoChaconDeveloper - github.com/GustavoChaconDeveloper
// Script de Modal de Login e Validações
// Depende do objeto 'translations' definido no index.js

// ============================================================================
// INÍCIO - CONFIGURAÇÃO DE VARIÁVEIS GLOBAIS
// ============================================================================
// Variável global para o timer de reenvio de código
let resendTimer;
// Flag para controlar bloqueio do código
let isCodeBlocked = false;
// Flag para controle de login Google
let isGoogleLogin = false;
// Dados do usuário Google
let googleUserData = null;
// Contador de tentativas de inicialização do Google
let googleInitAttempts = 0;
const MAX_GOOGLE_INIT_ATTEMPTS = 20; // Máximo 10 segundos (20 x 500ms)

const API_BASE_URL = `${window.location.origin}/api`;
// ============================================================================
// FIM - CONFIGURAÇÃO DE VARIÁVEIS GLOBAIS
// ============================================================================

// ============================================================================
// INÍCIO - GOOGLE OAUTH FUNCTIONS
// ============================================================================

/**
 * Inicializar Google Identity Services
 */
function initializeGoogleAuth() {
    googleInitAttempts++;
    
    if (typeof google === 'undefined' || !google.accounts) {
        if (googleInitAttempts < MAX_GOOGLE_INIT_ATTEMPTS) {
            console.warn(`Google Identity Services ainda não carregado. Tentativa ${googleInitAttempts}/${MAX_GOOGLE_INIT_ATTEMPTS}...`);
            setTimeout(initializeGoogleAuth, 500);
        } else {
            console.error('❌ Google Identity Services não pôde ser carregado após várias tentativas.');
            console.error('Verifique se o script https://accounts.google.com/gsi/client está sendo carregado.');
        }
        return;
    }
    
    try {
        google.accounts.id.initialize({
            client_id: GOOGLE_CLIENT_ID,
            callback: handleGoogleCredentialResponse,
            auto_select: false,
            cancel_on_tap_outside: true,
            use_fedcm_for_prompt: false // Desabilitar FedCM para usar popup tradicional
        });
        console.log('✅ Google Identity Services inicializado com sucesso!');
    } catch (error) {
        console.error('❌ Erro ao inicializar Google Auth:', error);
    }
}

/**
 * Abrir popup de login do Google
 */
function handleGoogleLogin() {
    const savedLang = localStorage.getItem('selectedLanguage') || 'en';
    
    if (typeof google === 'undefined' || !google.accounts) {
        showMessage(
            savedLang === 'pt' ? 'Aguarde, carregando Google...' : 'Please wait, loading Google...',
            'info'
        );
        setTimeout(handleGoogleLogin, 1000);
        return;
    }
    
    // Sempre mostrar o botão renderizado do Google (mais confiável)
    renderGoogleButtonInModal();
}

/**
 * Renderizar botão do Google dentro do modal
 */
function renderGoogleButtonInModal() {
    const savedLang = localStorage.getItem('selectedLanguage') || 'en';
    
    // Verificar se já existe um container
    let googleModal = document.getElementById('googleButtonModal');
    
    if (!googleModal) {
        // Criar overlay escuro
        const overlay = document.createElement('div');
        overlay.id = 'googleModalOverlay';
        overlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            z-index: 10000;
        `;
        overlay.onclick = closeGoogleModal;
        document.body.appendChild(overlay);
        
        // Criar modal para o botão do Google
        googleModal = document.createElement('div');
        googleModal.id = 'googleButtonModal';
        googleModal.style.cssText = `
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 30px 40px;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.3);
            z-index: 10001;
            text-align: center;
            min-width: 320px;
        `;
        
        googleModal.innerHTML = `
            <div style="margin-bottom: 20px;">
                <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" 
                     alt="Google" style="width: 48px; height: 48px; margin-bottom: 10px;">
                <h3 style="margin: 0; color: #333; font-size: 18px; font-weight: 600;">
                    ${savedLang === 'pt' ? 'Entrar com Google' : 'Sign in with Google'}
                </h3>
                <p style="margin: 8px 0 0 0; color: #666; font-size: 14px;">
                    ${savedLang === 'pt' ? 'Clique no botão abaixo para continuar' : 'Click the button below to continue'}
                </p>
            </div>
            <div id="googleButtonContainer" style="display: flex; justify-content: center; margin-bottom: 20px;"></div>
            <button onclick="closeGoogleModal()" style="
                padding: 10px 30px;
                border: 1px solid #ddd;
                background: #f8f8f8;
                border-radius: 8px;
                cursor: pointer;
                font-size: 14px;
                color: #666;
                transition: background 0.2s;
            " onmouseover="this.style.background='#eee'" onmouseout="this.style.background='#f8f8f8'">
                ${savedLang === 'pt' ? 'Cancelar' : 'Cancel'}
            </button>
        `;
        
        document.body.appendChild(googleModal);
        
        // Renderizar o botão oficial do Google
        google.accounts.id.renderButton(
            document.getElementById('googleButtonContainer'),
            { 
                type: 'standard',
                theme: 'outline', 
                size: 'large',
                text: 'continue_with',
                shape: 'rectangular',
                logo_alignment: 'left',
                width: 280
            }
        );
        
        console.log('✅ Botão do Google renderizado no modal');
    }
}

/**
 * Fechar modal do Google
 */
function closeGoogleModal() {
    const googleModal = document.getElementById('googleButtonModal');
    const overlay = document.getElementById('googleModalOverlay');
    
    if (googleModal) googleModal.remove();
    if (overlay) overlay.remove();
}

/**
 * Fallback: Renderizar botão do Google (deprecated - usar renderGoogleButtonInModal)
 */
function renderGoogleButtonFallback() {
    renderGoogleButtonInModal();
}

/**
 * Callback quando Google retorna credenciais
 */
async function handleGoogleCredentialResponse(response) {
    const savedLang = localStorage.getItem('selectedLanguage') || 'en';
    const t = translations[savedLang] || translations.en;
    
    console.log('📦 Google credential recebida');
    
    if (!response.credential) {
        showMessage(
            savedLang === 'pt' ? 'Erro ao autenticar com Google.' : 'Error authenticating with Google.',
            'error'
        );
        return;
    }
    
    // Mostrar loading
    const loginBtn = document.getElementById('googleLoginBtn');
    const signupBtn = document.getElementById('googleSignupBtn');
    
    if (loginBtn) {
        loginBtn.disabled = true;
        loginBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' + (savedLang === 'pt' ? 'Verificando...' : 'Verifying...');
    }
    if (signupBtn) {
        signupBtn.disabled = true;
        signupBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' + (savedLang === 'pt' ? 'Verificando...' : 'Verifying...');
    }
    
    try {
        // Enviar token para nossa API
        const apiResponse = await fetch(`${API_BASE_URL}/google-auth.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                credential: response.credential
            })
        });
        
        const result = await apiResponse.json();
        console.log('📊 Resposta da API Google Auth:', result);
        
        if (result.success) {
            closeGoogleModal();
            if (result.isNewUser) {
                // ========================================
                // NOVO USUÁRIO - IR PARA PERSONAL DETAILS
                // ========================================
                console.log('🆕 Novo usuário Google - indo para Personal Details');
                
                // Salvar dados do Google
                isGoogleLogin = true;
                googleUserData = result.googleData;
                
                // Salvar no sessionStorage para persistência
                sessionStorage.setItem('isGoogleLogin', 'true');
                sessionStorage.setItem('googleUserData', JSON.stringify(result.googleData));
                sessionStorage.setItem('signupEmail', result.googleData.email);
                
                // Preencher e ir para Personal Details
                showPersonalDetailsForGoogle(result.googleData);
                
            } else {
                // ========================================
                // USUÁRIO EXISTENTE - VERIFICAR SE PRECISA VERIFICAÇÃO
                // ========================================
                if (result.requiresVerification) {
                    // Precisa verificar telefone
                    console.log('📱 Usuário existe mas precisa verificar telefone');
                    
                    sessionStorage.setItem('userId', result.userId);
                    
                    // Salvar email do usuário
                    if (result.email) {
                        sessionStorage.setItem('signupEmail', result.email);
                    }
                    
                    // Salvar dados do usuário para a tela de verificação
                    if (result.userData) {
                        sessionStorage.setItem('signupData', JSON.stringify({
                            firstName: result.userData.firstName || '',
                            lastName: result.userData.lastName || '',
                            phone: result.phone || '',
                            countryCode: result.userData.countryCode || '',
                            phoneDigits: (result.phone || '').replace(/\D/g, ''),
                            email: result.userData.email || result.email || ''
                        }));
                    }
                    
                    // Mostrar tela de escolha de método (SMS ou Email) PRIMEIRO
                    showVerificationMethodChoice(savedLang);
                    
                    // Depois mostrar mensagem
                    showMessage(
                        savedLang === 'pt' 
                            ? 'Escolha como deseja receber seu código de verificação.' 
                            : 'Choose how you want to receive your verification code.',
                        'info'
                    );
                    
                } else {
                    // Login completo!
                    console.log('✅ Login Google bem-sucedido!');
                    
                    sessionStorage.setItem('userId', result.data.userId);
                    sessionStorage.setItem('userName', result.data.name);
                    sessionStorage.setItem('userEmail', result.data.email);
                    
                    showMessage(
                        savedLang === 'pt' ? 'Login realizado com sucesso!' : 'Login successful!',
                        'success'
                    );
                    
                    // Fechar modal e recarregar página
                    setTimeout(() => {
                        closeLoginModal();
                        window.location.reload();
                    }, 1500);
                }
            }
        } else {
    closeGoogleModal();
    showMessage(result.message || 'Erro ao autenticar com Google.', 'error');
}
        
    } catch (error) {
        console.error('❌ Erro na autenticação Google:', error);
        showMessage(
            savedLang === 'pt' ? 'Erro ao conectar com o servidor.' : 'Error connecting to server.',
            'error'
        );
    } finally {
        // Restaurar botões
        if (loginBtn) {
            loginBtn.disabled = false;
            loginBtn.innerHTML = '<i class="fab fa-google"></i> Continue with Google';
        }
        if (signupBtn) {
            signupBtn.disabled = false;
            signupBtn.innerHTML = '<i class="fab fa-google"></i> Continue with Google';
        }
    }
}

/**
 * Mostrar Personal Details com dados do Google preenchidos
 */
function showPersonalDetailsForGoogle(googleData) {
    const savedLang = localStorage.getItem('selectedLanguage') || 'en';
    const t = translations[savedLang] || translations.en;
    
    // Esconder outros formulários
    document.querySelector('.modal-tabs').style.display = 'none';
    document.getElementById('loginForm').style.display = 'none';
    document.getElementById('signupForm').style.display = 'none';
    document.getElementById('forgotPasswordForm').style.display = 'none';
    
    // Mostrar formulário de detalhes pessoais
    document.getElementById('signupDetailsForm').style.display = 'flex';
    
    // Atualizar título
    const modalTitle = document.getElementById('modalTitle');
    const modalSubtitle = document.getElementById('modalSubtitle');
    
    modalTitle.textContent = t.modalPersonalDetails || 'Personal Details';
    modalSubtitle.textContent = t.modalPersonalSubtitle || 'Complete your profile to continue';
    
    // Preencher campos com dados do Google
    const firstNameInput = document.getElementById('firstName');
    const lastNameInput = document.getElementById('lastName');
    
    if (googleData.firstName) {
        firstNameInput.value = googleData.firstName;
    }
    if (googleData.lastName) {
        lastNameInput.value = googleData.lastName;
    }
    
    // O email já está salvo no sessionStorage
    // Só falta o telefone!
    
    console.log('📝 Personal Details preenchido com dados do Google:', googleData);
}

/**
 * Processar registro de usuário Google
 * Chamado após preencher Personal Details
 */
async function processGoogleRegistration(firstName, lastName, phone, countryCode, verificationMethod) {
    const savedLang = localStorage.getItem('selectedLanguage') || 'en';
    
    // Recuperar dados do Google
    const googleData = JSON.parse(sessionStorage.getItem('googleUserData'));
    
    if (!googleData || !googleData.email) {
        showMessage(
            savedLang === 'pt' ? 'Dados do Google não encontrados. Tente novamente.' : 'Google data not found. Please try again.',
            'error'
        );
        return null;
    }
    
    try {
        const response = await fetch(`${API_BASE_URL}/register-google.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                email: googleData.email,
                firstName: firstName,
                lastName: lastName,
                phone: phone,
                countryCode: countryCode,
                picture: googleData.picture || '',
                googleId: googleData.googleId || '',
                verificationMethod: verificationMethod,
                language: savedLang
            })
        });
        
        const result = await response.json();
        console.log('📊 Resultado do registro Google:', result);
        
        return result;
        
    } catch (error) {
        console.error('❌ Erro no registro Google:', error);
        return {
            success: false,
            message: savedLang === 'pt' ? 'Erro ao processar registro.' : 'Error processing registration.'
        };
    }
}

// Inicializar Google Auth quando a página carregar
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeGoogleAuth);
} else {
    initializeGoogleAuth();
}

// ============================================================================
// FIM - GOOGLE OAUTH FUNCTIONS
// ============================================================================

// ============================================================================
// INÍCIO - VERIFICAÇÃO DE SESSÃO PHP
// ============================================================================
/**
 * Inicializar estado de login a partir dos dados PHP
 * Esta função é executada automaticamente quando a página carrega
 */
function initializeSessionFromPHP() {
    // Verificar se os dados PHP existem
    if (typeof window.phpUserData !== 'undefined' && window.phpUserData.isLoggedIn) {
        // Usuário está logado via PHP session
        sessionStorage.setItem('userId', window.phpUserData.userId);
        sessionStorage.setItem('userName', window.phpUserData.userName);
        sessionStorage.setItem('userEmail', window.phpUserData.userEmail);
        
        console.log('Sessão PHP detectada - usuário logado:', window.phpUserData.userName);
        
        // TODO: Atualizar UI se necessário
        // updateLoginUI(window.phpUserData.userId, window.phpUserData.userName);
    }
}

// Executar ao carregar a página
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeSessionFromPHP);
} else {
    initializeSessionFromPHP();
}
// ============================================================================
// FIM - VERIFICAÇÃO DE SESSÃO PHP
// ============================================================================

// ============================================================================
// INÍCIO - FUNÇÕES DE CONTROLE DO MODAL (ABRIR/FECHAR/RESETAR)
// ============================================================================
// Funções do Modal de Login
function openLoginModal(event) {
    if (event) event.preventDefault();
    console.log('openLoginModal chamado'); // Debug
    
    const modal = document.getElementById('loginModal');
    console.log('Modal encontrado:', modal); // Debug
    
    resetModal();
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
    
    console.log('Modal classes:', modal.classList); // Debug
}

function closeLoginModal() {
    document.getElementById('loginModal').classList.remove('active');
    document.body.style.overflow = '';
    
    // Limpar timer de reenvio
    if (resendTimer) {
        clearInterval(resendTimer);
    }
    
    // Resetar modal após fechar
    setTimeout(() => {
        resetModal();
    }, 300);
}

// Resetar modal para o estado inicial
function resetModal() {
    // Limpar timer se existir
    if (resendTimer) {
        clearInterval(resendTimer);
    }
    
    // Resetar flag de bloqueio
    isCodeBlocked = false;
    
    // Resetar flags de login Google
    isGoogleLogin = false;
    googleUserData = null;
    
    // Limpar sessionStorage de dados de signup anteriores
    sessionStorage.removeItem('userId');
    sessionStorage.removeItem('signupEmail');
    sessionStorage.removeItem('signupPassword');
    sessionStorage.removeItem('signupData');
    sessionStorage.removeItem('verificationMethod');
    sessionStorage.removeItem('verificationCode');
    
    // Limpar dados do Google
    sessionStorage.removeItem('isGoogleLogin');
    sessionStorage.removeItem('googleUserData');
    sessionStorage.removeItem('googleSignupReady');
    
    // Mostrar tabs
    document.querySelector('.modal-tabs').style.display = 'flex';
    
    // Esconder todos os formulários
    document.getElementById('loginForm').style.display = 'none';
    document.getElementById('signupForm').style.display = 'none';
    document.getElementById('forgotPasswordForm').style.display = 'none';
    document.getElementById('signupDetailsForm').style.display = 'none';
    document.getElementById('phoneVerificationForm').style.display = 'none';
    document.getElementById('almostDoneScreen').style.display = 'none';
    
    // Ocultar formulário de escolha de método de verificação
    const verificationMethodForm = document.getElementById('verificationMethodForm');
    if (verificationMethodForm) {
        verificationMethodForm.style.display = 'none';
    }
    
    // Remover todas as mensagens de alerta
    const existingAlerts = document.querySelectorAll('.modal-alert');
    existingAlerts.forEach(alert => alert.remove());
    
    // Remover mensagem de bloqueio se existir
    const blockedMessage = document.getElementById('blockedCodeMessage');
    if (blockedMessage) {
        blockedMessage.remove();
    }
    
    // Mostrar formulário de login
    document.getElementById('loginForm').style.display = 'flex';
    
    // Ativar tab de login
    document.getElementById('loginTab').classList.add('active');
    document.getElementById('signupTab').classList.remove('active');
    
    // Restaurar título e subtítulo
    const savedLang = localStorage.getItem('selectedLanguage') || 'en';
    const t = translations[savedLang] || translations.en;
    document.getElementById('modalTitle').textContent = t.modalWelcome;
    document.getElementById('modalSubtitle').textContent = t.modalSubtitle;
    
    // Limpar campos
    document.getElementById('loginEmail').value = '';
    document.getElementById('loginPassword').value = '';
    document.getElementById('signupEmail').value = '';
    document.getElementById('signupPassword').value = '';
    document.getElementById('firstName').value = '';
    document.getElementById('lastName').value = '';
    document.getElementById('phoneNumber').value = '';
    document.getElementById('verificationCode').value = '';
    document.getElementById('forgotEmail').value = '';
    
    // Resetar botão de login
    document.getElementById('loginContinue').disabled = true;
    
    // Esconder requisitos de senha
    const requirements = document.getElementById('passwordRequirements');
    if (requirements) {
        requirements.classList.remove('show');
    }
}
// ============================================================================
// FIM - FUNÇÕES DE CONTROLE DO MODAL (ABRIR/FECHAR/RESETAR)
// ============================================================================

// ============================================================================
// INÍCIO - FUNÇÕES DE NAVEGAÇÃO ENTRE TELAS (LOGIN/SIGNUP/FORGOT)
// ============================================================================
// Trocar entre tabs de Login e Sign Up
function switchTab(tab) {
    const tabs = document.querySelectorAll('.tab-btn');
    tabs.forEach(t => t.classList.remove('active'));
    if (event && event.target) {
        event.target.classList.add('active');
    }
    
    const loginTab = document.getElementById('loginTab');
    const signupTab = document.getElementById('signupTab');
    const loginForm = document.getElementById('loginForm');
    const signupForm = document.getElementById('signupForm');
    const modalTitle = document.getElementById('modalTitle');
    const modalSubtitle = document.getElementById('modalSubtitle');
    
    // Garantir que as tabs estejam visíveis
    document.querySelector('.modal-tabs').style.display = 'flex';
    document.getElementById('forgotPasswordForm').style.display = 'none';
    document.getElementById('signupDetailsForm').style.display = 'none';
    
    loginTab.classList.remove('active');
    signupTab.classList.remove('active');
    
    const savedLang = localStorage.getItem('selectedLanguage') || 'en';
    const t = translations[savedLang] || translations.en;
    
    if (tab === 'login') {
        loginTab.classList.add('active');
        loginForm.style.display = 'flex';
        signupForm.style.display = 'none';
        modalTitle.textContent = t.modalWelcome;
        modalSubtitle.textContent = t.modalSubtitle;
        
        // Esconder requisitos de senha ao voltar para login
        const requirements = document.getElementById('passwordRequirements');
        if (requirements) {
            requirements.classList.remove('show');
        }
    } else {
        signupTab.classList.add('active');
        loginForm.style.display = 'none';
        signupForm.style.display = 'flex';
        modalTitle.textContent = t.modalSignUp;
        modalSubtitle.textContent = t.modalSubtitle;
        
        // Mostrar requisitos de senha ao abrir Sign Up
        setTimeout(() => {
            showPasswordRequirements();
        }, 50);
    }
}

function showForgotPassword(event) {
    event.preventDefault();
    
    const savedLang = localStorage.getItem('selectedLanguage') || 'en';
    const t = translations[savedLang] || translations.en;
    
    // Esconder tabs e formulários
    document.querySelector('.modal-tabs').style.display = 'none';
    document.getElementById('loginForm').style.display = 'none';
    document.getElementById('signupForm').style.display = 'none';
    
    // Mostrar formulário de recuperação
    document.getElementById('forgotPasswordForm').style.display = 'flex';
    
    // Atualizar título e subtítulo
    const modalTitle = document.getElementById('modalTitle');
    const modalSubtitle = document.getElementById('modalSubtitle');
    
    modalTitle.textContent = t.modalForgotTitle;
    modalSubtitle.innerHTML = t.modalForgotSubtitle1 + '<br>' + t.modalForgotSubtitle2;
    
    // Atualizar placeholder e botão
    document.getElementById('forgotEmail').placeholder = t.modalEnterEmail;
    document.getElementById('sendEmailBtn').textContent = t.modalSendEmail;
    document.getElementById('backToLogin').textContent = t.modalBackToLogin;
}

function backToLogin(event) {
    event.preventDefault();
    switchTab('login');
}

function backToSignup(event) {
    event.preventDefault();
    
    const savedLang = localStorage.getItem('selectedLanguage') || 'en';
    const t = translations[savedLang] || translations.en;
    
    // Esconder formulário de detalhes pessoais
    document.getElementById('signupDetailsForm').style.display = 'none';
    
    // Mostrar tabs e formulário de signup
    document.querySelector('.modal-tabs').style.display = 'flex';
    document.getElementById('signupForm').style.display = 'flex';
    
    // Restaurar título e subtítulo do signup
    const modalTitle = document.getElementById('modalTitle');
    const modalSubtitle = document.getElementById('modalSubtitle');
    
    modalTitle.textContent = t.modalSignUp;
    modalSubtitle.textContent = t.modalSubtitle;
}
// ============================================================================
// FIM - FUNÇÕES DE NAVEGAÇÃO ENTRE TELAS (LOGIN/SIGNUP/FORGOT)
// ============================================================================

// ============================================================================
// INÍCIO - VALIDAÇÕES DE FORMULÁRIO
// ============================================================================
function checkFormFilled(formType) {
    if (formType === 'login') {
        const email = document.getElementById('loginEmail').value;
        const password = document.getElementById('loginPassword').value;
        const btn = document.getElementById('loginContinue');
        
        if (email && password) {
            btn.disabled = false;
        } else {
            btn.disabled = true;
        }
    }
    // Para signup, o botão fica sempre habilitado
    // A validação será feita pelo atributo required do HTML
}

function showPasswordRequirements() {
    const requirements = document.getElementById('passwordRequirements');
    if (requirements) {
        requirements.classList.add('show');
    }
}

function hidePasswordRequirements() {
    const requirements = document.getElementById('passwordRequirements');
    const passwordField = document.getElementById('signupPassword');
    if (requirements && passwordField) {
        // Só esconde se o campo estiver vazio
        if (passwordField.value.length === 0) {
            setTimeout(() => {
                requirements.classList.remove('show');
            }, 200);
        }
    }
}

function validatePassword(password) {
    const uppercaseReq = document.getElementById('uppercaseReq');
    const lengthReq = document.getElementById('lengthReq');
    
    // Validar letra maiúscula
    if (/[A-Z]/.test(password)) {
        uppercaseReq.classList.add('valid');
        uppercaseReq.querySelector('i').className = 'fas fa-check';
    } else {
        uppercaseReq.classList.remove('valid');
        uppercaseReq.querySelector('i').className = 'fas fa-times';
    }
    
    // Validar 8 caracteres
    if (password.length >= 8) {
        lengthReq.classList.add('valid');
        lengthReq.querySelector('i').className = 'fas fa-check';
    } else {
        lengthReq.classList.remove('valid');
        lengthReq.querySelector('i').className = 'fas fa-times';
    }
}

function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const icon = event.target.closest('button').querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
// ============================================================================
// FIM - VALIDAÇÕES DE FORMULÁRIO
// ============================================================================

// ============================================================================
// INÍCIO - FORMATAÇÃO DE TELEFONE
// ============================================================================
// Formatação de telefone baseada no código do país
function formatPhoneNumber() {
    const phoneInput = document.getElementById('phoneNumber');
    const countryCode = document.getElementById('countryCode').value;
    let value = phoneInput.value.replace(/\D/g, ''); // Remove tudo que não é dígito
    
    // Limitar número de dígitos por país
        const maxDigits = {
        '+1': 10,   // EUA/Canadá
        '+7': 10,   // Rússia
        '+27': 9,   // África do Sul
        '+33': 9,   // França
        '+34': 9,   // Espanha
        '+39': 10,  // Itália
        '+44': 10,  // Reino Unido
        '+49': 11,  // Alemanha
        '+51': 9,   // Peru
        '+52': 10,  // México
        '+54': 10,  // Argentina
        '+55': 11,  // Brasil
        '+56': 9,   // Chile
        '+57': 10,  // Colômbia
        '+61': 9,   // Austrália
        '+81': 10,  // Japão
        '+86': 11,  // China
        '+91': 10,  // Índia
        '+351': 9,  // Portugal
        '+380': 9   // Ucrânia
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
            
        case '+27': // África do Sul - 21 123 4567
        case '+33': // França - 1 23 45 67 89
        case '+34': // Espanha - 612 34 56 78
        case '+51': // Peru - 1 234 5678
        case '+56': // Chile - 2 2123 4567
            formattedValue = value.replace(/(\d{1,2})(\d{0,3})(\d{0,4})/, function(match, p1, p2, p3) {
                let result = p1;
                if (p2) result += ' ' + p2;
                if (p3) result += ' ' + p3;
                return result;
            });
            break;
            
        case '+39': // Itália - 02 1234 5678
        case '+49': // Alemanha - 30 12345678
            formattedValue = value.replace(/(\d{0,2})(\d{0,8})/, function(match, p1, p2) {
                let result = p1;
                if (p2) result += ' ' + p2;
                return result;
            });
            break;
            
        case '+44': // Reino Unido - 20 7123 4567
            formattedValue = value.replace(/(\d{0,2})(\d{0,4})(\d{0,4})/, function(match, p1, p2, p3) {
                let result = p1;
                if (p2) result += ' ' + p2;
                if (p3) result += ' ' + p3;
                return result;
            });
            break;
            
        case '+52': // México - 55 1234 5678
        case '+54': // Argentina - 11 1234-5678
        case '+57': // Colômbia - 1 234 5678
        case '+91': // Índia - 20 1234 5678
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
function updatePhonePlaceholder() {
    const phoneInput = document.getElementById('phoneNumber');
    const countryCode = document.getElementById('countryCode').value;
    
    // Limpar campo ao mudar país
    phoneInput.value = '';
    
        const placeholders = {
        '+1': '(555) 123-4567',
        '+7': '495 123-45-67',
        '+27': '21 123 4567',
        '+33': '1 23 45 67 89',
        '+34': '612 34 56 78',
        '+39': '02 1234 5678',
        '+44': '20 7123 4567',
        '+49': '30 12345678',
        '+51': '1 234 5678',
        '+52': '55 1234 5678',
        '+54': '11 1234 5678',
        '+55': '(11) 94993-1617',
        '+56': '2 2123 4567',
        '+57': '1 234 5678',
        '+61': '2 1234 5678',
        '+81': '3 1234 5678',
        '+86': '10 1234 5678',
        '+91': '20 1234 5678',
        '+351': '21 123 4567',
        '+380': '66 777 88 99'
    };
    
    phoneInput.placeholder = placeholders[countryCode] || '';
    
    // Atualizar maxlength baseado no país
        const maxLengths = {
        '+1': 14,   // (555) 123-4567
        '+7': 15,   // 495 123-45-67
        '+27': 12,  // 21 123 4567
        '+33': 13,  // 1 23 45 67 89
        '+34': 12,  // 612 34 56 78
        '+39': 13,  // 02 1234 5678
        '+44': 13,  // 20 7123 4567
        '+49': 13,  // 30 12345678
        '+51': 11,  // 1 234 5678
        '+52': 13,  // 55 1234 5678
        '+54': 13,  // 11 1234 5678
        '+55': 16,  // (11) 94993-1617
        '+56': 12,  // 2 2123 4567
        '+57': 11,  // 1 234 5678
        '+61': 11,  // 2 1234 5678
        '+81': 12,  // 3 1234 5678
        '+86': 13,  // 10 1234 5678
        '+91': 13,  // 20 1234 5678
        '+351': 12, // 21 123 4567
        '+380': 14  // 66 777 88 99
    };
    
    phoneInput.maxLength = maxLengths[countryCode] || 20;
}
// ============================================================================
// FIM - FORMATAÇÃO DE TELEFONE
// ============================================================================

// ============================================================================
// INÍCIO - VERIFICAÇÃO DE TELEFONE (SMS)
// ============================================================================

// Mostrar modal de escolha de método de verificação
function showVerificationMethodChoice(lang) {
    const t = translations[lang] || translations.en;
    
    // Esconder TODOS os formulários
    document.getElementById('signupDetailsForm').style.display = 'none';
    document.getElementById('loginForm').style.display = 'none';
    document.getElementById('signupForm').style.display = 'none';
    document.getElementById('forgotPasswordForm').style.display = 'none';
    document.querySelector('.modal-tabs').style.display = 'none';
    
    // Mostrar formulário de escolha de método
    document.getElementById('verificationMethodForm').style.display = 'flex';
    
    // Atualizar título e subtítulo do MODAL PRINCIPAL
    const modalTitle = document.getElementById('modalTitle');
    const modalSubtitle = document.getElementById('modalSubtitle');
    
    modalTitle.textContent = t.modalVerificationMethodTitle || 'Verification Method';
    modalSubtitle.textContent = t.modalVerificationMethodSubtitle || 'Choose how you want to receive your verification code';
    
    // ESCONDER título interno para não duplicar
    const methodTitle = document.getElementById('verificationMethodTitle');
    const methodSubtitle = document.getElementById('verificationMethodSubtitle');
    
    if (methodTitle) methodTitle.style.display = 'none';
    if (methodSubtitle) methodSubtitle.style.display = 'none';
    
    // Atualizar descrições dos métodos com dados reais
    const signupData = JSON.parse(sessionStorage.getItem('signupData') || '{}');
    const userEmail = sessionStorage.getItem('signupEmail') || sessionStorage.getItem('userEmail') || '';
    const googleData = JSON.parse(sessionStorage.getItem('googleUserData') || '{}');
    const phone = signupData.phone || '';
    const email = googleData.email || userEmail || '';
    
    // Atualizar descrição do SMS com telefone
    const smsDesc = document.querySelector('#methodSMS + .option-content .option-desc, label[for="methodSMS"] .option-desc');
    if (smsDesc && phone) {
        smsDesc.textContent = phone;
    }
    
    // Atualizar descrição do Email com email
    const emailDesc = document.querySelector('#methodEmail + .option-content .option-desc, label[for="methodEmail"] .option-desc');
    if (emailDesc && email) {
        emailDesc.textContent = email;
    }
}

// Voltar para detalhes pessoais
function backToPersonalDetails(event) {
    event.preventDefault();
    
    const savedLang = localStorage.getItem('selectedLanguage') || 'en';
    const t = translations[savedLang];
    
    // Esconder modal de escolha
    document.getElementById('verificationMethodForm').style.display = 'none';
    
    // Mostrar formulário de detalhes
    document.getElementById('signupDetailsForm').style.display = 'flex';
    
    // Restaurar título e subtítulo
    const modalTitle = document.getElementById('modalTitle');
    const modalSubtitle = document.getElementById('modalSubtitle');
    
    modalTitle.textContent = t.modalPersonalDetails;
    modalSubtitle.textContent = t.modalPersonalSubtitle;
}

// Confirmar método de verificação escolhido
async function confirmVerificationMethod() {
    const savedLang = localStorage.getItem('selectedLanguage') || 'en';
    
    // Pegar método selecionado
    const selectedMethod = document.querySelector('input[name="verificationMethod"]:checked');
    
    if (!selectedMethod) {
        showMessage(
            savedLang === 'pt' ? 'Selecione um método de verificação.' : 'Please select a verification method.',
            'error'
        );
        return;
    }
    
    const method = selectedMethod.value;
    
    // Chamar função existente
    await chooseVerificationMethod(method);
}

// Escolher método de verificação (SMS ou Email)
async function chooseVerificationMethod(method) {
    const savedLang = localStorage.getItem('selectedLanguage') || 'en';
    const t = translations[savedLang];
    
    // Recuperar dados salvos
    const signupData = JSON.parse(sessionStorage.getItem('signupData'));
    const email = sessionStorage.getItem('signupEmail');
    const password = sessionStorage.getItem('signupPassword');
    const existingUserId = sessionStorage.getItem('userId');
    
    // Verificar se é registro via Google
    const isGoogleSignup = sessionStorage.getItem('isGoogleLogin') === 'true';
    const googleUserData = isGoogleSignup ? JSON.parse(sessionStorage.getItem('googleUserData')) : null;
    
    // Salvar método escolhido
    sessionStorage.setItem('verificationMethod', method);
    
    // Mostrar loading no botão Continue
    const confirmBtn = document.getElementById('confirmMethodBtn');
    const originalText = confirmBtn.textContent;
    confirmBtn.disabled = true;
    confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' + (savedLang === 'pt' ? 'Enviando...' : 'Sending...');
    
    let result;
    
    // Se já existe userId, usar APENAS reenvio (não tentar registro)
    if (existingUserId) {
        try {
            const response = await fetch(`${API_BASE_URL}/resend-code.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ 
                    userId: existingUserId,
                    verificationMethod: method
                })
            });
            
            result = await response.json();
            
            if (result.success) {
                // Salvar novo código
                sessionStorage.setItem('verificationCode', result.data.verificationCode);
            }
            // Se falhou (bloqueado ou erro), apenas mostrar a mensagem
            // NÃO tentar fazer registro novamente
            
        } catch (error) {
            console.error('Erro ao reenviar código:', error);
            result = {
                success: false,
                message: savedLang === 'pt' ? 'Erro ao enviar código. Tente novamente.' : 'Error sending code. Try again.'
            };
        }
    } else if (isGoogleSignup && googleUserData) {
        // ========================================
        // REGISTRO VIA GOOGLE
        // ========================================
        console.log('🔵 Processando registro via Google...');
        
        try {
            const response = await fetch(`${API_BASE_URL}/register-google.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    email: googleUserData.email,
                    firstName: signupData.firstName,
                    lastName: signupData.lastName,
                    phone: signupData.phoneDigits,
                    countryCode: signupData.countryCode,
                    picture: googleUserData.picture || '',
                    googleId: googleUserData.googleId || '',
                    verificationMethod: method,
                    language: savedLang
                })
            });
            
            result = await response.json();
            console.log('📊 Resultado do registro Google:', result);
            
            if (result.success) {
                // Salvar userId para verificação
                sessionStorage.setItem('userId', result.data.userId);
                sessionStorage.setItem('verificationCode', result.data.verificationCode);
            }
            
        } catch (error) {
            console.error('❌ Erro no registro Google:', error);
            result = {
                success: false,
                message: savedLang === 'pt' ? 'Erro ao processar registro.' : 'Error processing registration.'
            };
        }
    } else {
        // ========================================
        // REGISTRO NORMAL (com email e senha)
        // ========================================
        const userData = {
            email: email,
            password: password,
            firstName: signupData.firstName,
            lastName: signupData.lastName,
            phone: signupData.phoneDigits,
            countryCode: signupData.countryCode,
            language: savedLang,
            verificationMethod: method
        };
        
        // Chamar API de registro
        result = await registerUser(userData);
        
        if (result.success) {
            // Salvar userId para verificação
            sessionStorage.setItem('userId', result.data.userId);
            sessionStorage.setItem('verificationCode', result.data.verificationCode);
        }
    }
    
    // Remover loading
    confirmBtn.disabled = false;
    confirmBtn.textContent = originalText;
    
    if (result && result.success) {
        // Esconder modal de escolha
        document.getElementById('verificationMethodForm').style.display = 'none';
        
        // Mostrar tela de verificação apropriada
        if (method === 'sms') {
            showPhoneVerification(signupData.phone, savedLang);
        } else if (method === 'email') {
            showEmailVerification(email, savedLang);
        }
    } else {
        showMessage(result.message, 'error');
    }
}

// Mostrar tela de verificação de email
function showEmailVerification(email, lang) {
    const t = translations[lang] || translations.en;
    
    // Mostrar formulário de verificação
    document.getElementById('phoneVerificationForm').style.display = 'flex';
    
    // Atualizar título e subtítulo
    const modalTitle = document.getElementById('modalTitle');
    const modalSubtitle = document.getElementById('modalSubtitle');
    
    modalTitle.textContent = t.modalEmailVerification;
    modalSubtitle.innerHTML = lang === 'pt' 
        ? 'Enviamos um código de 4 dígitos para seu email' 
        : 'We sent a 4-digit code to your email';
    
    // Atualizar email exibido
    document.getElementById('verificationPhone').textContent = email;
    
    // Atualizar instrução
    document.getElementById('verificationInstruction').textContent = 
        lang === 'pt' ? 'Digite o código de verificação' : 'Enter verification code';
    
    // Atualizar link para mudar método
    document.getElementById('changePhoneNumber').textContent = 
        lang === 'pt' ? 'Alterar método de verificação' : 'Change verification method';
    
    // Iniciar countdown do botão de reenviar
    startResendTimer(lang);
}

// Mostrar tela de verificação de telefone
function showPhoneVerification(phoneNumber, lang) {
    const t = translations[lang] || translations.en;
    
    // Esconder formulário de detalhes pessoais
    document.getElementById('signupDetailsForm').style.display = 'none';
    
    // Mostrar formulário de verificação
    document.getElementById('phoneVerificationForm').style.display = 'flex';
    
    // Atualizar título e subtítulo
    const modalTitle = document.getElementById('modalTitle');
    const modalSubtitle = document.getElementById('modalSubtitle');
    
    modalTitle.textContent = t.modalPhoneVerification;
    modalSubtitle.innerHTML = t.modalVerificationSubtitle1;
    
    // Atualizar número de telefone
    document.getElementById('verificationPhone').textContent = phoneNumber;
    
    // Atualizar instrução
    document.getElementById('verificationInstruction').textContent = t.modalVerificationInstruction;
    
    // Atualizar link para mudar método
    document.getElementById('changePhoneNumber').textContent = 
        lang === 'pt' ? 'Alterar método de verificação' : 'Change verification method';
    
    // Iniciar countdown do botão de reenviar
    startResendTimer(lang);
}

// Countdown do botão de reenviar
function startResendTimer(lang) {
    const t = translations[lang] || translations.en;
    const resendBtn = document.getElementById('resendBtn');
    let timeLeft = 60; // 1 minuto = 60 segundos
    
    resendBtn.disabled = true;
    
    clearInterval(resendTimer);
    resendTimer = setInterval(() => {
        timeLeft--;
        if (timeLeft > 0) {
            resendBtn.textContent = `${t.modalResendIn} ${timeLeft} ${t.modalSec}`;
        } else {
            clearInterval(resendTimer);
            resendBtn.disabled = false;
            resendBtn.textContent = t.modalResend;
        }
    }, 1000);
    
    // Adicionar evento de clique no botão de reenvio
    resendBtn.onclick = async function() {
        if (!resendBtn.disabled) {
            await resendVerificationCode();
        }
    };
}

/**
 * Reenviar código de verificação
 */
async function resendVerificationCode() {
    const savedLang = localStorage.getItem('selectedLanguage') || 'en';
    const userId = sessionStorage.getItem('userId');
    const verificationMethod = sessionStorage.getItem('verificationMethod') || 'sms';
    
    if (!userId) {
        showMessage(
            savedLang === 'pt' ? 'Erro: ID de usuário não encontrado.' : 'Error: User ID not found.',
            'error'
        );
        return;
    }
    
    try {
        const response = await fetch(`${API_BASE_URL}/api/resend-code.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ 
                userId: userId,
                verificationMethod: verificationMethod
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Salvar novo código para teste
            sessionStorage.setItem('verificationCode', result.data.verificationCode);
            
            // DESBLOQUEAR CAMPO DE CÓDIGO
            isCodeBlocked = false;
            const codeInput = document.getElementById('verificationCode');
            codeInput.disabled = false;
            codeInput.style.opacity = '1';
            codeInput.style.cursor = 'text';
            
            // Mostrar campo novamente
            const verificationInputGroup = codeInput.closest('.modal-input-group');
            if (verificationInputGroup) {
                verificationInputGroup.style.display = 'flex';
            }
            
            // Remover mensagem de bloqueio
            const blockedMessage = document.getElementById('blockedCodeMessage');
            if (blockedMessage) {
                blockedMessage.remove();
            }
            
            // Mostrar mensagem de sucesso
            showMessage(result.message, 'success');
            
            // Reiniciar timer
            startResendTimer(savedLang);
            
            // Limpar campo de código
            codeInput.value = '';
            codeInput.focus();
            
        } else {
            showMessage(result.message, 'error');
        }
        
    } catch (error) {
        console.error('Erro ao reenviar código:', error);
        showMessage(
            savedLang === 'pt' ? 'Erro ao reenviar código. Tente novamente.' : 'Error resending code. Try again.',
            'error'
        );
    }
}

// Formatar input do código (apenas números)
function formatCodeInput() {
    const codeInput = document.getElementById('verificationCode');
    
    // Se está bloqueado, limpar qualquer tentativa de digitação
    if (isCodeBlocked) {
        codeInput.value = '';
        return;
    }
    
    let value = codeInput.value.replace(/\D/g, '');
    
    if (value.length > 4) {
        value = value.substring(0, 4);
    }
    
    codeInput.value = value;
    
    // Auto-submit quando completar 4 dígitos
    if (value.length === 4) {
        verifyCode(value);
    }
}

// Verificar código via API
async function verifyCode(code) {
    const savedLang = localStorage.getItem('selectedLanguage') || 'en';
    const userId = sessionStorage.getItem('userId');
    
    // Verificar se está bloqueado
    if (isCodeBlocked) {
        showMessage(
            savedLang === 'pt' ? 'Código bloqueado. Solicite um novo código.' : 'Code blocked. Request a new code.',
            'error'
        );
        return;
    }
    
    if (!userId) {
        showMessage(
            savedLang === 'pt' ? 'Erro: ID de usuário não encontrado.' : 'Error: User ID not found.',
            'error'
        );
        return;
    }
    
    // Desabilitar input durante verificação
    const codeInput = document.getElementById('verificationCode');
    codeInput.disabled = true;
    
    // Chamar API de verificação
    const result = await verifyPhoneCode(userId, code);
    
    if (result.success) {
        // Limpar timer antes de avançar
        if (resendTimer) {
            clearInterval(resendTimer);
        }
        
        // Mostrar mensagem de sucesso
        showMessage(
            savedLang === 'pt' ? 'Telefone verificado com sucesso!' : 'Phone verified successfully!',
            'success'
        );
        
        // Ir para tela Almost Done após 1 segundo
        setTimeout(() => {
            showAlmostDone(savedLang);
        }, 1000);
        
    } else {
        // Verificar se é mensagem de bloqueio
        const isBlocked = result.message.includes('excedeu') || 
                         result.message.includes('exceeded') ||
                         result.message.includes('bloqueado') ||
                         result.message.includes('blocked');
        
        if (isBlocked) {
            // BLOQUEAR CAMPO DE CÓDIGO
            isCodeBlocked = true;
            codeInput.disabled = true;
            codeInput.value = '';
            codeInput.style.opacity = '0.5';
            codeInput.style.cursor = 'not-allowed';
            
            // Ocultar campo completamente
            const verificationInputGroup = codeInput.closest('.modal-input-group');
            if (verificationInputGroup) {
                verificationInputGroup.style.display = 'none';
            }
            
            // Adicionar mensagem visual de bloqueio
            const form = document.getElementById('phoneVerificationForm');
            let blockedMessage = document.getElementById('blockedCodeMessage');
            if (!blockedMessage) {
                blockedMessage = document.createElement('div');
                blockedMessage.id = 'blockedCodeMessage';
                blockedMessage.style.cssText = `
                    background: #fff3cd;
                    border: 2px solid #ffc107;
                    border-radius: 8px;
                    padding: 20px;
                    margin: 20px 0;
                    text-align: center;
                    color: #856404;
                    font-weight: 500;
                    line-height: 1.6;
                `;
                blockedMessage.innerHTML = `
                    <i class="fas fa-exclamation-triangle" style="color: #ffc107; font-size: 32px; display: block; margin-bottom: 10px;"></i>
                    <div style="font-size: 16px; margin-bottom: 8px;">
                        ${savedLang === 'pt' ? 
                            'Código bloqueado por excesso de tentativas.' : 
                            'Code blocked due to too many attempts.'}
                    </div>
                    <div style="font-size: 14px; font-weight: 600;">
                        ${savedLang === 'pt' ? 
                            '⏱️ Aguarde 60 segundos para solicitar um novo código.' : 
                            '⏱️ Wait 60 seconds to request a new code.'}
                    </div>
                `;
                form.insertBefore(blockedMessage, form.firstChild);
            }
        } else {
            // Reabilitar input apenas se NÃO estiver bloqueado
            codeInput.disabled = false;
            codeInput.value = '';
            codeInput.focus();
        }
        
        // Mostrar mensagem de erro
        showMessage(result.message, 'error');
    }
}

// Voltar para alterar método de verificação
function changePhoneNumber(event) {
    event.preventDefault();
    
    const savedLang = localStorage.getItem('selectedLanguage') || 'en';
    const t = translations[savedLang] || translations.en;
    
    // Limpar timer
    clearInterval(resendTimer);
    
    // Esconder formulário de verificação
    document.getElementById('phoneVerificationForm').style.display = 'none';
    document.getElementById('verificationCode').value = '';
    
    // Mostrar formulário de escolha de método
    showVerificationMethodChoice(savedLang);
}
// ============================================================================
// FIM - VERIFICAÇÃO DE TELEFONE (SMS)
// ============================================================================

// ============================================================================
// INÍCIO - TELA ALMOST DONE (FINALIZAÇÃO)
// ============================================================================
// Mostrar tela Almost Done
function showAlmostDone(lang) {
    const t = translations[lang] || translations.en;
    
    // Esconder formulário de verificação
    document.getElementById('phoneVerificationForm').style.display = 'none';
    
    // Esconder tabs
    document.querySelector('.modal-tabs').style.display = 'none';
    
    // Mostrar tela Almost Done
    document.getElementById('almostDoneScreen').style.display = 'flex';
    
    // Atualizar título
    const modalTitle = document.getElementById('modalTitle');
    const modalSubtitle = document.getElementById('modalSubtitle');
    
    modalTitle.textContent = t.modalAlmostDone;
    modalSubtitle.textContent = '';
    
    // Atualizar textos
    document.getElementById('almostDoneSubtitle1').textContent = t.modalAlmostSubtitle1;
    document.getElementById('almostDoneSubtitle2').textContent = t.modalAlmostSubtitle2;
    document.getElementById('almostDoneHelp').textContent = t.modalAlmostHelp;
    
    // Atualizar botões
    document.getElementById('askLaterBtn').textContent = t.modalAskLater;
    document.getElementById('letsGoBtn').textContent = t.modalLetsGo;
}

// Botão Ask me later
function askMeLater() {
    const savedLang = localStorage.getItem('selectedLanguage') || 'en';
    
    // Limpar dados salvos
    sessionStorage.removeItem('signupData');
    
    alert(savedLang === 'pt' ? 'Cadastro concluído! Você pode responder o questionário depois.' : 'Registration completed! You can answer the questionnaire later.');
    
    // Fechar modal
    closeLoginModal();
}

// Botão Let's go
function letsGo() {
    const savedLang = localStorage.getItem('selectedLanguage') || 'en';
    const t = translations[savedLang] || translations.en;
    
    // Limpar dados salvos
    sessionStorage.removeItem('signupData');
    sessionStorage.removeItem('justRegistered');
    
    // Salvar flag para abrir questionário APÓS o login
    sessionStorage.setItem('openQuestionaryAfterLogin', 'true');
    
    // Esconder tela Almost Done
    document.getElementById('almostDoneScreen').style.display = 'none';
    
    // Mostrar tabs e formulário de login
    document.querySelector('.modal-tabs').style.display = 'flex';
    document.getElementById('loginForm').style.display = 'flex';
    
    // Ativar tab de login
    document.getElementById('loginTab').classList.add('active');
    document.getElementById('signupTab').classList.remove('active');
    
    // Restaurar título e subtítulo
    document.getElementById('modalTitle').textContent = t.modalWelcome;
    document.getElementById('modalSubtitle').textContent = t.modalSubtitle;
    
    // Limpar campos de login
    document.getElementById('loginEmail').value = '';
    document.getElementById('loginPassword').value = '';
    
    // Mostrar mensagem informativa
    showMessage(
        savedLang === 'pt' 
            ? 'Para responder o questionário, por favor faça login com suas credenciais.' 
            : 'To answer the questionnaire, please log in with your credentials.',
        'info'
    );
}
// ============================================================================
// FIM - TELA ALMOST DONE (FINALIZAÇÃO)
// ============================================================================

// ============================================================================
// INÍCIO - EVENT LISTENERS (DOMContentLoaded)
// ============================================================================
// ============================================================================
// INÍCIO - FUNÇÕES DE API (INTEGRAÇÃO BACKEND)
// ============================================================================
/**
 * Registrar novo usuário via API
 */
async function registerUser(userData) {
    try {
        const response = await fetch(`${API_BASE_URL}/register.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(userData)
        });
        
        const result = await response.json();
        return result;
        
    } catch (error) {
        console.error('Erro ao registrar:', error);
        return {
            success: false,
            message: 'Erro ao conectar com o servidor. Tente novamente.'
        };
    }
}

/**
 * Verificar código de telefone via API
 */
async function verifyPhoneCode(userId, code) {
    try {
        const response = await fetch(`${API_BASE_URL}/verify-phone.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                userId: userId,
                code: code
            })
        });
        
        const result = await response.json();
        return result;
        
    } catch (error) {
        console.error('Erro ao verificar:', error);
        return {
            success: false,
            message: 'Erro ao conectar com o servidor. Tente novamente.'
        };
    }
}

/**
 * Mostrar mensagem de loading
 */
function showLoading(buttonId, isLoading = true) {
    const button = document.getElementById(buttonId);
    if (!button) return;
    
    if (isLoading) {
        button.disabled = true;
        button.dataset.originalText = button.textContent;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processando...';
    } else {
        button.disabled = false;
        button.textContent = button.dataset.originalText || 'Continue';
    }
}

/**
 * Mostrar mensagem de erro/sucesso
 */
function showMessage(message, type = 'error') {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-error';
    const iconClass = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    
    // Remover alertas anteriores
    const existingAlert = document.querySelector('.modal-alert');
    if (existingAlert) {
        existingAlert.remove();
    }
    
    // Criar novo alerta
    const alert = document.createElement('div');
    alert.className = `modal-alert ${alertClass}`;
    alert.innerHTML = `
        <i class="fas ${iconClass}"></i>
        <span>${message}</span>
    `;
    
    // Inserir no modal
    const modalContent = document.querySelector('.modal-content');
    const modalHeader = document.querySelector('.modal-header');
    modalContent.insertBefore(alert, modalHeader.nextSibling);
    
    // Remover após 5 segundos
    setTimeout(() => {
        alert.remove();
    }, 5000);
}
// ============================================================================
// FIM - FUNÇÕES DE API (INTEGRAÇÃO BACKEND)
// ============================================================================

// Event Listeners para os formulários
document.addEventListener('DOMContentLoaded', function() {
    // Fechar modal ao clicar fora
    document.addEventListener('click', (event) => {
        const modal = document.getElementById('loginModal');
        if (event.target === modal) {
            closeLoginModal();
        }
    });
    
    // Prevenir submit do formulário de login
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            
            const savedLang = localStorage.getItem('selectedLanguage') || 'en';
            const email = document.getElementById('loginEmail').value;
            const password = document.getElementById('loginPassword').value;
            
            // Validar campos
            if (!email || !password) {
                showMessage(
                    savedLang === 'pt' ? 'Por favor, preencha todos os campos.' : 'Please fill all fields.',
                    'error'
                );
                return;
            }
            
            // Mostrar loading
const loginButton = loginForm.querySelector('button[type="submit"]');
const originalText = loginButton.textContent;

loginButton.disabled = true;
loginButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' + 
    (savedLang === 'pt' ? 'Entrando...' : 'Logging in...');

try {
    console.log('Enviando requisição de login...');
    const response = await fetch(`${API_BASE_URL}/login.php`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            email: email,
            password: password
        })
    });

                
                console.log('Response status:', response.status);
                console.log('Response ok:', response.ok);
                
                const result = await response.json();
                console.log('Result:', result);
                
                if (result.success) {
                    console.log('Login bem-sucedido!');
                    showMessage(result.message, 'success');
                    
                    // Salvar dados do usuário no sessionStorage
                    if (result.data) {
                        sessionStorage.setItem('userId', result.data.userId);
                        sessionStorage.setItem('userName', result.data.name);
                        sessionStorage.setItem('userEmail', result.data.email);
                        sessionStorage.setItem('accountType', result.data.accountType);
                    }
                    
                    // Recarregar a página para atualizar com a sessão PHP
                    setTimeout(() => {
                        window.location.reload();
                    }, 800);
                    
                } else {
                    // Verificar se precisa de verificação
                    if (result.requiresVerification) {
                        showMessage(result.message, 'warning');
                        
                        // Salvar dados do usuário para verificação
                        sessionStorage.setItem('userId', result.userId);
                        
                        // Salvar dados do usuário para exibir na tela de verificação
                        if (result.userData) {
                            const fullName = `${result.userData.firstName || ''} ${result.userData.lastName || ''}`.trim();
                            const phone = result.phone || '';
                            
                            sessionStorage.setItem('signupData', JSON.stringify({
                                firstName: result.userData.firstName || '',
                                lastName: result.userData.lastName || '',
                                phone: phone,
                                countryCode: result.userData.countryCode || '',
                                phoneDigits: phone.replace(/\D/g, '')
                            }));
                        }
                        
                        // Esconder formulário de login
                        document.getElementById('loginForm').style.display = 'none';
                        document.querySelector('.modal-tabs').style.display = 'none';
                        
                        // Mostrar tela de escolha de método de verificação
                        setTimeout(() => {
                            showVerificationMethodChoice(savedLang);
                        }, 1000);
                    } else {
                        showMessage(result.message, 'error');
                    }
                }
                
            } catch (error) {
                console.error('Erro no login:', error);
                showMessage(
                    savedLang === 'pt' ? 'Erro ao fazer login. Tente novamente.' : 'Error logging in. Try again.',
                    'error'
                );
            } finally {
                // Remover loading
                loginButton.disabled = false;
                loginButton.textContent = originalText;
            }
        });
    }
    
    // Handler para formulário de signup - ir para Personal Details
    const signupForm = document.getElementById('signupForm');
    if (signupForm) {
        signupForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            
            const savedLang = localStorage.getItem('selectedLanguage') || 'en';
            const t = translations[savedLang] || translations.en;
            
            // Validar e-mail e senha antes de avançar
            const email = document.getElementById('signupEmail').value;
            const password = document.getElementById('signupPassword').value;
            
            // Validar formato de e-mail
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                showMessage(
                    savedLang === 'pt' ? 'Por favor, insira um e-mail válido.' : 'Please enter a valid email.',
                    'error'
                );
                return;
            }
            
            // Validar requisitos de senha
            const hasUppercase = /[A-Z]/.test(password);
            const hasMinLength = password.length >= 8;
            
            if (!hasUppercase || !hasMinLength) {
                showMessage(
                    savedLang === 'pt' ? 'A senha deve conter pelo menos 1 letra maiúscula e 8 caracteres.' : 'Password must contain at least 1 uppercase letter and 8 characters.',
                    'error'
                );
                return;
            }
            
            // VERIFICAR SE EMAIL JÁ EXISTE NO BANCO
            const continueBtn = document.getElementById('signupContinue');
            continueBtn.disabled = true;
            continueBtn.textContent = savedLang === 'pt' ? 'Verificando...' : 'Checking...';
            
            try {
                const response = await fetch(`${API_BASE_URL}/check-email.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ email: email })
                });
                
                const result = await response.json();
                
                if (result.exists) {
                    // Email já está cadastrado
                    showMessage(
                        savedLang === 'pt' ? 'Este email já está cadastrado. Faça login ou use outro email.' : 'This email is already registered. Please login or use another email.',
                        'error'
                    );
                    continueBtn.disabled = false;
                    continueBtn.textContent = 'Continue';
                    return;
                }
                
                // Email disponível, continuar para Personal Details
                continueBtn.disabled = false;
                continueBtn.textContent = 'Continue';
                
            } catch (error) {
                console.error('Erro ao verificar email:', error);
                showMessage(
                    savedLang === 'pt' ? 'Erro ao verificar email. Tente novamente.' : 'Error checking email. Try again.',
                    'error'
                );
                continueBtn.disabled = false;
                continueBtn.textContent = 'Continue';
                return;
            }
            
            // Salvar email e senha no sessionStorage para usar depois
            sessionStorage.setItem('signupEmail', email);
            sessionStorage.setItem('signupPassword', password);
            
            // Esconder tabs e formulário de signup
            document.querySelector('.modal-tabs').style.display = 'none';
            document.getElementById('signupForm').style.display = 'none';
            
            // Mostrar formulário de detalhes pessoais
            document.getElementById('signupDetailsForm').style.display = 'flex';
            
            // Atualizar título e subtítulo
            const modalTitle = document.getElementById('modalTitle');
            const modalSubtitle = document.getElementById('modalSubtitle');
            
            modalTitle.textContent = t.modalPersonalDetails;
            modalSubtitle.textContent = t.modalPersonalSubtitle;
            
            // Atualizar placeholders
            document.getElementById('firstName').placeholder = t.modalFirstName;
            document.getElementById('lastName').placeholder = t.modalLastName;
            document.getElementById('phoneNumber').placeholder = '66 777 88 99';
            document.getElementById('detailsContinue').textContent = t.modalContinue;
            document.getElementById('backToSignup').textContent = t.modalBack;
        });
    }
    
    // Handler para formulário de recuperação de senha
    const forgotPasswordForm = document.getElementById('forgotPasswordForm');
    if (forgotPasswordForm) {
        forgotPasswordForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            
            const savedLang = localStorage.getItem('selectedLanguage') || 'en';
            const email = document.getElementById('forgotEmail').value.trim();
            const submitBtn = document.getElementById('sendEmailBtn');
            const alertBox = document.getElementById('forgotPasswordAlert');
            
            // Validar email
            if (!email || !email.includes('@')) {
                showForgotPasswordAlert(
                    savedLang === 'pt' ? 'Por favor, digite um email válido.' : 'Please enter a valid email.',
                    'error'
                );
                return;
            }
            
            // Desabilitar botão e mostrar loading
            submitBtn.disabled = true;
            submitBtn.textContent = savedLang === 'pt' ? 'Enviando...' : 'Sending...';
            
            try {
                const response = await fetch(`${API_BASE_URL}/forgot-password.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ email })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showForgotPasswordAlert(
                        savedLang === 'pt' 
                            ? '✅ Email enviado! Verifique sua caixa de entrada e spam.' 
                            : '✅ Email sent! Check your inbox and spam folder.',
                        'success'
                    );
                    
                    // Limpar campo de email
                    document.getElementById('forgotEmail').value = '';
                    
                    // Voltar para login após 5 segundos
                    setTimeout(() => {
                        switchTab('login');
                        alertBox.style.display = 'none';
                    }, 5000);
                } else {
                    showForgotPasswordAlert(
                        savedLang === 'pt' 
                            ? '❌ ' + (data.message || 'Erro ao enviar email. Tente novamente.')
                            : '❌ ' + (data.message || 'Error sending email. Please try again.'),
                        'error'
                    );
                }
            } catch (error) {
                console.error('Erro ao solicitar recuperação de senha:', error);
                showForgotPasswordAlert(
                    savedLang === 'pt' 
                        ? '❌ Erro ao processar solicitação. Tente novamente.' 
                        : '❌ Error processing request. Please try again.',
                    'error'
                );
            } finally {
                // Reabilitar botão
                submitBtn.disabled = false;
                submitBtn.textContent = savedLang === 'pt' ? 'Enviar Email' : 'Send Email';
            }
        });
    }
    
    // Função auxiliar para mostrar alertas no formulário de recuperação
    function showForgotPasswordAlert(message, type) {
        const alertBox = document.getElementById('forgotPasswordAlert');
        if (!alertBox) return;
        
        alertBox.textContent = message;
        alertBox.className = 'alert-message ' + type;
        alertBox.style.display = 'block';
        
        // Auto-esconder alertas de erro após 8 segundos
        if (type === 'error') {
            setTimeout(() => {
                alertBox.style.display = 'none';
            }, 8000);
        }
    }
    
    // Handler para formulário de detalhes pessoais
    const signupDetailsForm = document.getElementById('signupDetailsForm');
    if (signupDetailsForm) {
        signupDetailsForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            
            const savedLang = localStorage.getItem('selectedLanguage') || 'en';
            
            const firstName = document.getElementById('firstName').value;
            const lastName = document.getElementById('lastName').value;
            const countryCode = document.getElementById('countryCode').value;
            const phone = document.getElementById('phoneNumber').value;
            
            // Validar número de telefone
            const digitsOnly = phone.replace(/\D/g, '');
            const validLengths = {
                '+55': [10, 11], // Brasil: 10 (fixo) ou 11 (celular)
                '+1': [10], // EUA/Canadá
                '+44': [10], // Reino Unido
                '+380': [9], // Ucrânia
                '+351': [9] // Portugal
            };
            
            const expectedLengths = validLengths[countryCode];
            if (!expectedLengths || !expectedLengths.includes(digitsOnly.length)) {
                const errorMessages = {
                    '+55': savedLang === 'pt' ? 'Telefone inválido. Digite 10 dígitos (fixo) ou 11 dígitos (celular).' : 'Invalid phone. Enter 10 digits (landline) or 11 digits (mobile).',
                    '+1': savedLang === 'pt' ? 'Telefone inválido. Digite 10 dígitos.' : 'Invalid phone. Enter 10 digits.',
                    '+44': savedLang === 'pt' ? 'Telefone inválido. Digite 10 dígitos.' : 'Invalid phone. Enter 10 digits.',
                    '+380': savedLang === 'pt' ? 'Telefone inválido. Digite 9 dígitos.' : 'Invalid phone. Enter 9 digits.',
                    '+351': savedLang === 'pt' ? 'Telefone inválido. Digite 9 dígitos.' : 'Invalid phone. Enter 9 digits.'
                };
                showMessage(errorMessages[countryCode], 'error');
                return;
            }
            
            // Mostrar loading
            showLoading('detailsContinue', true);
            
            // Verificar se o telefone já está cadastrado
            try {
                const response = await fetch(`${API_BASE_URL}/check-phone.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        phone: digitsOnly,
                        countryCode: countryCode
                    })
                });
                
                const result = await response.json();
                
                // Remover loading
                showLoading('detailsContinue', false);
                
                if (result.exists) {
                    // Telefone já cadastrado
                    showMessage(
                        savedLang === 'pt' 
                            ? 'Este número de telefone já está cadastrado.' 
                            : 'This phone number is already registered.',
                        'error'
                    );
                    return;
                }
                
                // Telefone disponível, salvar dados e continuar
                const fullPhone = countryCode + ' ' + phone;
                sessionStorage.setItem('signupData', JSON.stringify({ 
                    firstName, 
                    lastName, 
                    phone: fullPhone,
                    countryCode: countryCode,
                    phoneDigits: digitsOnly
                }));
                
                // Verificar se é login via Google
                const isGoogleSignup = sessionStorage.getItem('isGoogleLogin') === 'true';
                
                if (isGoogleSignup) {
                    console.log('📱 Registro via Google - salvando dados para verificação');
                    // Salvar dados adicionais para processamento posterior
                    sessionStorage.setItem('googleSignupReady', 'true');
                }
                
                // Mostrar modal de escolha de método de verificação
                showVerificationMethodChoice(savedLang);
                
            } catch (error) {
                console.error('Erro ao verificar telefone:', error);
                showLoading('detailsContinue', false);
                showMessage(
                    savedLang === 'pt' 
                        ? 'Erro ao verificar telefone. Tente novamente.' 
                        : 'Error checking phone. Please try again.',
                    'error'
                );
            }
        });
    }
});

// ============================================================================
// USER PROFILE MANAGEMENT
// ============================================================================

/**
 * Check if user is logged in and update UI
 */
function checkLoginStatus() {
    const userId = sessionStorage.getItem('userId');
    const userName = sessionStorage.getItem('userName');
    
    const btnLogin = document.getElementById('btnLogin');
    const mobileBtnLogin = document.getElementById('mobileBtnLogin');
    const userProfileDropdown = document.getElementById('userProfileDropdown');
    const mobileUserNavDropdown = document.getElementById('mobileUserNavDropdown');
    
    if (userId && userName) {
        // User is logged in
        if (btnLogin) btnLogin.style.display = 'none';
        if (mobileBtnLogin) mobileBtnLogin.style.display = 'none';
        
        if (userProfileDropdown) {
            userProfileDropdown.style.display = 'block';
            const userNameElement = document.getElementById('userName');
            if (userNameElement) {
                // Mostrar "Hi" + primeiro nome
                const firstName = userName.split(' ')[0];
                userNameElement.textContent = `Hi, ${firstName}`;
            }
        }
        
        if (mobileUserNavDropdown) {
            mobileUserNavDropdown.style.display = 'block';
        }
    } else {
        // User is NOT logged in
        if (btnLogin) btnLogin.style.display = 'block';
        if (mobileBtnLogin) mobileBtnLogin.style.display = 'block';
        if (userProfileDropdown) userProfileDropdown.style.display = 'none';
        if (mobileUserNavDropdown) mobileUserNavDropdown.style.display = 'none';
    }
}

/**
 * Toggle user dropdown menu
 */
function toggleUserMenu(event) {
    event.preventDefault();
    event.stopPropagation();
    
    const userProfileBtn = document.querySelector('.user-profile-btn');
    const userDropdownMenu = document.getElementById('userDropdownMenu');
    
    if (userProfileBtn && userDropdownMenu) {
        userProfileBtn.classList.toggle('active');
        userDropdownMenu.classList.toggle('active');
    }
}

/**
 * Logout user
 */
async function logout(event) {
    if (event) {
        event.preventDefault();
    }
    
    try {
        // Call logout API
        await fetch(`${API_BASE_URL}/logout.php`, {
            method: 'POST'
        });
    } catch (error) {
        console.error('Erro ao fazer logout:', error);
    } finally {
        // Clear session storage
        sessionStorage.clear();
        
        // Redirect to landing page PHP
        window.location.href = 'index.php';
    }
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    // Desktop dropdown
    const userProfileDropdown = document.getElementById('userProfileDropdown');
    const userDropdownMenu = document.getElementById('userDropdownMenu');
    const userProfileBtn = document.querySelector('.user-profile-btn');
    
    if (userDropdownMenu && userProfileBtn && userProfileDropdown) {
        if (!userProfileDropdown.contains(event.target)) {
            userProfileBtn.classList.remove('active');
            userDropdownMenu.classList.remove('active');
        }
    }
});

// Check login status on page load
document.addEventListener('DOMContentLoaded', function() {
    checkLoginStatus();
});
// ============================================================================
// FIM - EVENT LISTENERS (DOMContentLoaded)
// ============================================================================

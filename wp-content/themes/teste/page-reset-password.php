<?php
/**
 * Template Name: Reset Password Page
 * Description: Página para redefinir senha do usuário
 */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir Senha - Poker111</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    /* Reset Password Styles - Glassmorphism */
    .reset-password-wrapper {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px 20px;
        background: linear-gradient(135deg, #00133D 0%, #012164 50%, #001b4d 100%);
    }

    .reset-password-container {
        width: 480px;
        max-width: 100%;
        padding: 32px 36px;
        border-radius: 8px;
        background: rgba(0, 17, 52, 0.55);
        backdrop-filter: blur(30px);
        -webkit-backdrop-filter: blur(30px);
        box-shadow: 0 0 40px rgba(0, 51, 153, 0.5), inset 0 0 0 1px rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.08);
        position: relative;
        animation: modalSlideIn 0.3s ease-out;
    }

    @keyframes modalSlideIn {
        from {
            opacity: 0;
            transform: translateY(-30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .reset-logo {
        text-align: center;
        margin-bottom: 30px;
    }

    .reset-logo img {
        height: 28px;
        width: auto;
        margin-bottom: 12px;
    }

    .reset-logo p {
        font-size: 13px;
        color: rgba(255, 255, 255, 0.7);
        margin: 0;
        font-weight: 400;
    }

    .reset-status-message {
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        display: none;
        animation: fadeIn 0.3s ease-in;
        font-size: 14px;
        line-height: 1.5;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .reset-status-message.success {
        background: rgba(40, 167, 69, 0.2);
        border: 1px solid rgba(40, 167, 69, 0.4);
        color: #5cff5c;
    }

    .reset-status-message.error {
        background: rgba(220, 53, 69, 0.2);
        border: 1px solid rgba(220, 53, 69, 0.4);
        color: #ff6b7a;
    }

    .reset-status-message.warning {
        background: rgba(255, 193, 7, 0.2);
        border: 1px solid rgba(255, 193, 7, 0.4);
        color: #ffd966;
    }

    .reset-status-message.info {
        background: rgba(23, 162, 184, 0.2);
        border: 1px solid rgba(23, 162, 184, 0.4);
        color: #5ddbff;
    }

    .reset-form-group {
        margin-bottom: 20px;
    }

    .reset-label {
        display: block;
        font-size: 14px;
        font-weight: 600;
        color: #ffffff;
        margin-bottom: 8px;
    }

    .reset-input-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }

    .reset-input {
        width: 100%;
        padding: 15px 45px 15px 20px;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 10px;
        color: #ffffff;
        font-size: 14px;
        transition: all 0.3s;
        font-family: inherit;
    }

    .reset-input:focus {
        outline: none;
        border-color: rgba(255, 255, 255, 0.5);
        background: rgba(255, 255, 255, 0.08);
    }

    .reset-input::placeholder {
        color: rgba(255, 255, 255, 0.5);
    }

    .reset-toggle-password {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        background: transparent;
        border: none;
        color: rgba(255, 255, 255, 0.6);
        cursor: pointer;
        font-size: 16px;
        padding: 5px;
        transition: color 0.3s;
    }

    .reset-toggle-password:hover {
        color: #ffffff;
    }

    .password-requirements {
        margin-top: 8px;
        display: block;
    }

    .requirement {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 6px;
        font-size: 12px;
    }

    .requirement::before {
        content: '○';
        color: #E53935;
        font-size: 14px;
        width: 14px;
        font-weight: bold;
    }

    .requirement.valid::before {
        content: '✓';
        color: #00B04C;
    }

    .requirement span {
        color: rgba(255, 255, 255, 0.9);
    }

    .reset-btn {
        width: 100%;
        padding: 13px;
        background: rgba(70, 80, 100, 0.5);
        border: none;
        border-radius: 8px;
        color: rgba(255, 255, 255, 0.5);
        font-size: 16px;
        font-weight: 600;
        cursor: not-allowed;
        transition: all 0.3s;
        margin-top: 10px;
    }

    .reset-btn:not(:disabled) {
        background: linear-gradient(135deg, #ff3366 0%, #ff1a4d 100%);
        color: #ffffff;
        cursor: pointer;
    }

    .reset-btn:not(:disabled):hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 30px rgba(255, 51, 102, 0.4);
    }

    .reset-loading {
        display: none;
        text-align: center;
        padding: 20px;
        color: rgba(255, 255, 255, 0.9);
    }

    .reset-spinner {
        border: 3px solid rgba(255, 255, 255, 0.1);
        border-top: 3px solid #ff3366;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        animation: spin 1s linear infinite;
        margin: 0 auto 10px;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .reset-back-to-login {
        text-align: center;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid rgba(255, 255, 255, 0.15);
    }

    .reset-back-to-login a {
        color: #ff3366;
        text-decoration: none;
        font-weight: 600;
        font-size: 14px;
        transition: all 0.3s;
    }

    .reset-back-to-login a:hover {
        color: #ff6b90;
        text-decoration: underline;
    }

    #validatingToken {
        display: none;
    }

    #resetPasswordForm {
        display: none;
    }

    @media (max-width: 480px) {
        .reset-password-container {
            padding: 24px 20px;
        }
    }
</style>

<div class="reset-password-wrapper">
    <div class="reset-password-container">
        <div class="reset-logo">
            <img src="<?php echo get_template_directory_uri(); ?>/logopoker.png" alt="Poker111">
            <p>Redefinir Senha</p>
        </div>

        <!-- Mensagem de Status -->
        <div id="statusMessage" class="reset-status-message"></div>

        <!-- Loading de Validação -->
        <div id="validatingToken" class="reset-loading">
            <div class="reset-spinner"></div>
            <p>Validando seu link...</p>
        </div>

        <!-- Formulário de Reset -->
        <form id="resetPasswordForm">
            <div class="reset-form-group">
                <label for="newPassword" class="reset-label">Nova Senha</label>
                <div class="reset-input-wrapper">
                    <input 
                        type="password" 
                        id="newPassword" 
                        class="reset-input"
                        placeholder="Digite sua nova senha"
                        required
                    >
                    <button type="button" class="reset-toggle-password" onclick="togglePasswordVisibility('newPassword')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <div class="password-requirements">
                    <div class="requirement" id="req-length">
                        <span>Mínimo de 8 caracteres</span>
                    </div>
                    <div class="requirement" id="req-uppercase">
                        <span>Pelo menos uma letra maiúscula</span>
                    </div>
                    <div class="requirement" id="req-lowercase">
                        <span>Pelo menos uma letra minúscula</span>
                    </div>
                    <div class="requirement" id="req-number">
                        <span>Pelo menos um número</span>
                    </div>
                </div>
            </div>

            <div class="reset-form-group">
                <label for="confirmPassword" class="reset-label">Confirmar Nova Senha</label>
                <div class="reset-input-wrapper">
                    <input 
                        type="password" 
                        id="confirmPassword" 
                        class="reset-input"
                        placeholder="Digite novamente sua nova senha"
                        required
                    >
                    <button type="button" class="reset-toggle-password" onclick="togglePasswordVisibility('confirmPassword')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="reset-btn" id="submitBtn">
                REDEFINIR SENHA
            </button>

            <div class="reset-back-to-login">
                <a href="<?php echo home_url('/'); ?>">← Voltar para Login</a>
            </div>
        </form>

        <!-- Loading de Submissão -->
        <div id="submittingReset" class="reset-loading">
            <div class="reset-spinner"></div>
            <p>Redefinindo sua senha...</p>
        </div>
    </div>
</div>

<script>
    const API_BASE = '/api';
    let currentToken = null;

    // Obter token da URL
    function getTokenFromUrl() {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('token');
    }

    // Mostrar mensagem de status
    function showMessage(message, type = 'info') {
        const statusMessage = document.getElementById('statusMessage');
        statusMessage.textContent = message;
        statusMessage.className = `reset-status-message ${type}`;
        statusMessage.style.display = 'block';
    }

    // Esconder mensagem de status
    function hideMessage() {
        document.getElementById('statusMessage').style.display = 'none';
    }

    // Toggle visibilidade da senha
    function togglePasswordVisibility(inputId) {
        const input = document.getElementById(inputId);
        const button = input.nextElementSibling;
        const icon = button.querySelector('i');
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'fas fa-eye-slash';
        } else {
            input.type = 'password';
            icon.className = 'fas fa-eye';
        }
    }

    // Validar requisitos da senha
    function validatePasswordRequirements() {
        const password = document.getElementById('newPassword').value;
        const confirmPassword = document.getElementById('confirmPassword').value;
        
        // Mínimo 8 caracteres
        const hasLength = password.length >= 8;
        document.getElementById('req-length').classList.toggle('valid', hasLength);
        
        // Letra maiúscula
        const hasUppercase = /[A-Z]/.test(password);
        document.getElementById('req-uppercase').classList.toggle('valid', hasUppercase);
        
        // Letra minúscula
        const hasLowercase = /[a-z]/.test(password);
        document.getElementById('req-lowercase').classList.toggle('valid', hasLowercase);
        
        // Número
        const hasNumber = /[0-9]/.test(password);
        document.getElementById('req-number').classList.toggle('valid', hasNumber);
        
        // Verificar se senhas coincidem
        const passwordsMatch = password === confirmPassword && confirmPassword.length > 0;
        
        // Habilitar/desabilitar botão
        const allValid = hasLength && hasUppercase && hasLowercase && hasNumber && passwordsMatch;
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = !allValid;
        
        return allValid;
    }

    // Validar token ao carregar página
    async function validateToken() {
        currentToken = getTokenFromUrl();
        
        if (!currentToken) {
            showMessage('Link inválido. Token não encontrado.', 'error');
            document.getElementById('validatingToken').style.display = 'none';
            return;
        }

        document.getElementById('validatingToken').style.display = 'block';

        try {
            const response = await fetch(`${API_BASE}/reset-password.php?token=${currentToken}`);
            const data = await response.json();

            document.getElementById('validatingToken').style.display = 'none';

            if (data.success && data.valid) {
                // Token válido - mostrar formulário
                document.getElementById('resetPasswordForm').style.display = 'block';
                showMessage(`Olá, ${data.data.firstName}! Digite sua nova senha abaixo.`, 'success');
            } else if (data.expired) {
                // Token expirado
                showMessage('⏱️ Este link expirou. Por favor, solicite uma nova recuperação de senha.', 'warning');
            } else {
                // Token inválido
                showMessage(data.message || 'Link inválido ou já utilizado.', 'error');
            }
        } catch (error) {
            document.getElementById('validatingToken').style.display = 'none';
            showMessage('Erro ao validar link. Tente novamente.', 'error');
            console.error('Erro:', error);
        }
    }

    // Submeter formulário de reset
    document.getElementById('resetPasswordForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        hideMessage();

        const newPassword = document.getElementById('newPassword').value;
        const confirmPassword = document.getElementById('confirmPassword').value;

        // Validar requisitos da senha
        if (!validatePasswordRequirements()) {
            showMessage('A senha não atende aos requisitos mínimos.', 'error');
            return;
        }

        // Verificar se senhas coincidem
        if (newPassword !== confirmPassword) {
            showMessage('As senhas não coincidem.', 'error');
            return;
        }

        // Mostrar loading
        document.getElementById('resetPasswordForm').style.display = 'none';
        document.getElementById('submittingReset').style.display = 'block';

        try {
            const response = await fetch(`${API_BASE}/reset-password.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    token: currentToken,
                    newPassword: newPassword,
                    confirmPassword: confirmPassword
                })
            });

            const data = await response.json();

            document.getElementById('submittingReset').style.display = 'none';

            if (data.success) {
                showMessage('✅ ' + data.message, 'success');
                
                // Redirecionar para login após 3 segundos
                setTimeout(() => {
                    window.location.href = '<?php echo home_url('/'); ?>';
                }, 3000);
            } else if (data.expired) {
                showMessage('⏱️ Este link expirou. Solicite uma nova recuperação.', 'warning');
            } else {
                showMessage(data.message || 'Erro ao redefinir senha.', 'error');
                document.getElementById('resetPasswordForm').style.display = 'block';
            }
        } catch (error) {
            document.getElementById('submittingReset').style.display = 'none';
            document.getElementById('resetPasswordForm').style.display = 'block';
            showMessage('Erro ao processar solicitação. Tente novamente.', 'error');
            console.error('Erro:', error);
        }
    });

    // Validar senha em tempo real
    document.getElementById('newPassword').addEventListener('input', validatePasswordRequirements);
    document.getElementById('confirmPassword').addEventListener('input', validatePasswordRequirements);

    // Iniciar validação do token ao carregar
    window.addEventListener('DOMContentLoaded', validateToken);
</script>
</body>
</html>

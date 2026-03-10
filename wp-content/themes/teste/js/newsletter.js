// @dev GustavoChaconDeveloper - github.com/GustavoChaconDeveloper
// ===== NEWSLETTER FOOTER JAVASCRIPT =====

document.addEventListener('DOMContentLoaded', function() {
    console.log('Newsletter footer initialized');
    
    const emailInput = document.getElementById('footerNewsletterEmail');
    const subscribeBtn = document.getElementById('footerNewsletterBtn');
    
    if (!emailInput || !subscribeBtn) {
        console.log('Newsletter elements not found');
        return;
    }
    
    // Handle button click
    subscribeBtn.addEventListener('click', function(e) {
        e.preventDefault();
        handleNewsletterSubscribe();
    });
    
    // Handle Enter key in input
    emailInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            handleNewsletterSubscribe();
        }
    });
    
    async function handleNewsletterSubscribe() {
        const email = emailInput.value.trim();
        
        console.log('Newsletter subscribe attempt:', email);
        
        // Validate email
        if (!email) {
            showNewsletterAlert('Por favor, insira seu email.', 'error');
            emailInput.focus();
            return;
        }
        
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            showNewsletterAlert('Por favor, insira um email válido.', 'error');
            emailInput.focus();
            return;
        }
        
        // Disable button during request
        const originalIcon = subscribeBtn.innerHTML;
        subscribeBtn.disabled = true;
        subscribeBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        subscribeBtn.style.opacity = '0.6';
        
        try {
            const apiUrl = window.location.origin + '/api/newsletter.php';
            console.log('Sending to:', apiUrl);
            
            const response = await fetch(apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ email: email })
            });
            
            console.log('Response status:', response.status);
            
            const result = await response.json();
            console.log('Response data:', result);
            
            if (result.success) {
                console.log('✅ Newsletter subscription successful');
                showNewsletterAlert(result.message, 'success');
                emailInput.value = '';
            } else {
                console.error('❌ Newsletter subscription failed:', result.message);
                showNewsletterAlert(result.message, 'error');
            }
            
        } catch (error) {
            console.error('❌ Newsletter exception:', error);
            showNewsletterAlert('Erro de conexão. Tente novamente.', 'error');
        } finally {
            // Re-enable button
            subscribeBtn.disabled = false;
            subscribeBtn.innerHTML = originalIcon;
            subscribeBtn.style.opacity = '1';
        }
    }
    
    function showNewsletterAlert(message, type = 'info') {
        // Remove existing alerts
        const existingAlert = document.querySelector('.newsletter-alert');
        if (existingAlert) {
            existingAlert.remove();
        }
        
        // Create alert element
        const alert = document.createElement('div');
        alert.className = `newsletter-alert newsletter-alert-${type}`;
        
        const icon = type === 'success' ? '✓' : type === 'error' ? '✕' : 'ℹ';
        const bgColor = type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#3b82f6';
        
        alert.innerHTML = `
            <div style="
                position: fixed;
                top: 20px;
                right: 20px;
                background: ${bgColor};
                color: white;
                padding: 16px 24px;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.2);
                z-index: 9999;
                max-width: 400px;
                animation: slideInRight 0.3s ease-out;
                display: flex;
                align-items: center;
                gap: 12px;
            ">
                <span style="font-size: 20px; font-weight: bold;">${icon}</span>
                <span style="flex: 1;">${message}</span>
                <button onclick="this.parentElement.parentElement.remove()" style="
                    background: transparent;
                    border: none;
                    color: white;
                    font-size: 20px;
                    cursor: pointer;
                    padding: 0;
                    width: 24px;
                    height: 24px;
                    line-height: 1;
                ">×</button>
            </div>
        `;
        
        // Add CSS animation
        if (!document.getElementById('newsletter-alert-styles')) {
            const style = document.createElement('style');
            style.id = 'newsletter-alert-styles';
            style.textContent = `
                @keyframes slideInRight {
                    from {
                        transform: translateX(100%);
                        opacity: 0;
                    }
                    to {
                        transform: translateX(0);
                        opacity: 1;
                    }
                }
                @keyframes slideOutRight {
                    from {
                        transform: translateX(0);
                        opacity: 1;
                    }
                    to {
                        transform: translateX(100%);
                        opacity: 0;
                    }
                }
            `;
            document.head.appendChild(style);
        }
        
        // Add to page
        document.body.appendChild(alert);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (alert.parentElement) {
                alert.querySelector('div').style.animation = 'slideOutRight 0.3s ease-out';
                setTimeout(() => alert.remove(), 300);
            }
        }, 5000);
    }
});

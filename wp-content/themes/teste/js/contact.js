// @dev GustavoChaconDeveloper - github.com/GustavoChaconDeveloper
// ===== CONTACT PAGE JAVASCRIPT =====
// Navigation functions are handled by navigation.js

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('Contact page loaded');
    
    // Load saved language (from navigation.js)
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
    
    // FAQ Accordion functionality
    console.log('Initializing FAQ');
    const faqQuestions = document.querySelectorAll('.faq-question');
    console.log('FAQ questions found:', faqQuestions.length);
    
    faqQuestions.forEach((question, index) => {
        question.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('FAQ question clicked:', index);
            
            const faqItem = this.closest('.faq-item');
            const isActive = faqItem.classList.contains('active');
            
            // Close all other items
            document.querySelectorAll('.faq-item').forEach(item => {
                item.classList.remove('active');
            });
            
            // Open clicked item if it wasn't active
            if (!isActive) {
                faqItem.classList.add('active');
                console.log('FAQ opened:', index);
            } else {
                console.log('FAQ closed:', index);
            }
        });
    });
    
    // Contact Form Submission
    const contactForm = document.getElementById('contactForm');
    
    if (contactForm) {
        contactForm.addEventListener('submit', async function(event) {
            event.preventDefault();
            
            console.log('=== FORM SUBMIT START ===');
            
            // Get all inputs using unique contact form IDs
            const firstNameInput = document.getElementById('contactFirstName');
            const lastNameInput = document.getElementById('contactLastName');
            const emailInput = document.getElementById('contactEmail');
            const subjectInput = document.getElementById('contactSubject');
            const messageInput = document.getElementById('contactMessage');
            const termsInput = document.getElementById('contactTerms');
            const newsletterInput = document.getElementById('contactNewsletter');
            
            // Read values DIRECTLY - no tricks, no manipulation
            let firstName = firstNameInput.value.trim();
            let lastName = lastNameInput.value.trim();
            let email = emailInput.value.trim();
            const subject = subjectInput.value;
            let message = messageInput.value.trim();
            const termsAgree = termsInput.checked;
            const newsletter = newsletterInput.checked;
            
            console.log('🔍 RAW VALUES (before any processing):', {
                firstName: `"${firstName}"`,
                lastName: `"${lastName}"`,
                email: `"${email}"`,
                subject: `"${subject}"`,
                message: `"${message}"`,
                firstNameInputValue: `"${firstNameInput.value}"`,
                lastNameInputValue: `"${lastNameInput.value}"`
            });
            
            // Try reading via FormData (browser's native form data collection)
            const formDataAPI = new FormData(contactForm);
            console.log('🔍 FormData API values:', {
                firstName: formDataAPI.get('firstName'),
                lastName: formDataAPI.get('lastName'),
                emailAddress: formDataAPI.get('emailAddress')
            });
            
            // If JavaScript can't read but FormData can, use FormData
            if ((!firstName || !lastName) && (formDataAPI.get('firstName') || formDataAPI.get('lastName'))) {
                console.log('⚠️ Using FormData fallback');
                firstName = formDataAPI.get('firstName') || '';
                lastName = formDataAPI.get('lastName') || '';
                email = formDataAPI.get('emailAddress') || email;
            }
            
            console.log('Form values:', {
                firstName: firstName,
                lastName: lastName,
                email: email,
                subject: subject,
                message: message,
                termsAgree: termsAgree,
                newsletter: newsletter
            });
            
            // Validate
            if (!firstName || !lastName || !email || !subject) {
                console.error('Validation failed - missing fields');
                showAlert('Please fill in all required fields.', 'error');
                return;
            }
            
            if (!termsAgree) {
                console.error('Validation failed - terms not agreed');
                showAlert('Please agree to the Terms of Use.', 'error');
                return;
            }
            
            // Validate email format
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                console.error('Validation failed - invalid email');
                showAlert('Please enter a valid email address.', 'error');
                return;
            }
            
            // Create form data object
            const formData = {
                firstName,
                lastName,
                email,
                subject,
                message,
                termsAgree,
                newsletter
            };
            
            console.log('✅ Validation passed. Sending data:', formData);
            
            // Disable submit button
            const submitBtn = contactForm.querySelector('.btn-submit');
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Sending...';
            submitBtn.style.opacity = '0.6';
            submitBtn.style.cursor = 'not-allowed';
            
            try {
                // Get API URL
                const apiUrl = window.location.origin + '/api/contact.php';
                console.log('API URL:', apiUrl);
                
                // Send to API
                const response = await fetch(apiUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });
                
                console.log('Response status:', response.status);
                console.log('Response ok:', response.ok);
                
                const result = await response.json();
                console.log('Response data:', result);
                
                if (result.success) {
                    console.log('✅ SUCCESS:', result.message);
                    showAlert(result.message || 'Thank you for contacting us! We will get back to you soon.', 'success');
                    contactForm.reset();
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                } else {
                    console.error('❌ ERROR:', result.message);
                    showAlert(result.message || 'An error occurred. Please try again.', 'error');
                }
                
            } catch (error) {
                console.error('❌ EXCEPTION:', error);
                showAlert('Connection error. Please check your internet and try again.', 'error');
            } finally {
                // Re-enable submit button
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
                submitBtn.style.opacity = '1';
                submitBtn.style.cursor = 'pointer';
                console.log('=== FORM SUBMIT END ===');
            }
        });
    }
});

// Show custom alert message
function showAlert(message, type = 'info') {
    // Remove existing alerts
    const existingAlert = document.querySelector('.custom-alert');
    if (existingAlert) {
        existingAlert.remove();
    }
    
    // Create alert element
    const alert = document.createElement('div');
    alert.className = `custom-alert custom-alert-${type}`;
    alert.innerHTML = `
        <div class="alert-content">
            <span class="alert-icon">${type === 'success' ? '✓' : type === 'error' ? '✕' : 'ℹ'}</span>
            <span class="alert-message">${message}</span>
            <button class="alert-close" onclick="this.parentElement.parentElement.remove()">×</button>
        </div>
    `;
    
    // Add to page
    document.body.insertBefore(alert, document.body.firstChild);
    
    // Auto remove after 8 seconds
    setTimeout(() => {
        if (alert.parentElement) {
            alert.style.animation = 'slideOut 0.3s ease-out';
            setTimeout(() => alert.remove(), 300);
        }
    }, 8000);
}

// Note: Navigation menu and language functions are in navigation.js

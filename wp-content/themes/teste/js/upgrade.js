// Payment method toggle
document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ Upgrade.js loaded');
    
    const paymentToggles = document.querySelectorAll('.payment-toggle-btn');
    const creditCardForm = document.getElementById('creditCardForm');
    const pixForm = document.getElementById('pixForm');
    
    console.log('Payment toggles found:', paymentToggles.length);
    console.log('Credit card form:', creditCardForm ? 'Found' : 'NOT FOUND');
    console.log('PIX form:', pixForm ? 'Found' : 'NOT FOUND');
    
    if (!paymentToggles.length || !creditCardForm || !pixForm) {
        console.error('❌ Required elements not found!');
        return;
    }
    
    paymentToggles.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Toggle clicked:', this.getAttribute('data-method'));
            
            // Remove active from all buttons
            paymentToggles.forEach(b => b.classList.remove('active'));
            
            // Add active to clicked button
            this.classList.add('active');
            
            // Show/hide payment forms
            const method = this.getAttribute('data-method');
            
            if (method === 'pix') {
                creditCardForm.style.display = 'none';
                pixForm.style.display = 'block';
                console.log('Showing PIX form');
            } else if (method === 'credit_card') {
                creditCardForm.style.display = 'block';
                pixForm.style.display = 'none';
                console.log('Showing Credit Card form');
            }
        });
    });
    
    // Card number formatting
    const cardNumberInput = document.getElementById('cardNumber');
    if (cardNumberInput) {
        cardNumberInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s/g, '');
            let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
            e.target.value = formattedValue;
        });
    }
    
    // Expiry date formatting
    const expiryDateInput = document.getElementById('expiryDate');
    if (expiryDateInput) {
        expiryDateInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 6);
            }
            e.target.value = value;
        });
    }
    
    // CVV formatting (only numbers)
    const cvvInput = document.getElementById('cvv');
    if (cvvInput) {
        cvvInput.addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/\D/g, '');
        });
    }
    
    // Form submission
    const paymentForm = document.getElementById('paymentForm');
    if (paymentForm) {
        paymentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const termsAgree = document.getElementById('termsAgree').checked;
            
            if (!termsAgree) {
                alert('Please agree to the Terms of Service and Privacy Policy');
                return;
            }
            
            // Get active payment method
            const activeMethod = document.querySelector('.payment-toggle-btn.active');
            const paymentMethod = activeMethod ? activeMethod.getAttribute('data-method') : 'pix';
            
            // Prepare form data
            const formData = {
                plan: upgradeData.plan,
                period: upgradeData.period,
                price: upgradeData.price,
                userId: upgradeData.userId,
                paymentMethod: paymentMethod
            };
            
            if (paymentMethod === 'credit_card') {
                formData.cardNumber = document.getElementById('cardNumber').value;
                formData.expiryDate = document.getElementById('expiryDate').value;
                formData.cvv = document.getElementById('cvv').value;
                formData.cardholderName = document.getElementById('cardholderName').value;
            }
            
            console.log('Processing payment:', formData);
            
            // Show loading state
            const submitBtn = this.querySelector('.btn-submit-payment');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Processing...</span>';
            submitBtn.disabled = true;
            
            // Simulate API call (replace with actual payment processing)
            setTimeout(() => {
                alert('Payment processed successfully! (This is a demo)');
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 2000);
        });
    }
});

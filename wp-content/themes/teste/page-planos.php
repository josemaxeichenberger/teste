<?php
/**
 * Template Name: Planos
 * Description: Página de planos de assinatura Poker
 */

get_header();
?>

<!-- Main Content -->
<main class="main-content plans-page">
    <div class="container">
        <!-- Membership Plans Section -->
        <div class="plans-section">
            <h1 class="plans-title">Poker Membership Plans</h1>
            <p class="plans-subtitle">Choose one of our subscription plans to unlock your full potential and just weekly challenges.</p>
            
            <!-- Toggle Yearly/Monthly -->
            <div style="text-align: center;">
                <div class="plan-toggle">
                    <button class="toggle-btn active" id="yearlyBtn">Yearly</button>
                    <button class="toggle-btn" id="monthlyBtn">Monthly</button>
                </div>
            </div>
            <p class="save-notice" id="saveNotice">Save up to 20% on yearly plans</p>
            
            <!-- Plans Cards -->
            <div class="plans-grid">
                <!-- Silver Plan - MONTHLY -->
                <div class="plan-card" data-period="monthly" style="display: none;">
                    <div class="plan-top-section">
                        <div class="plan-header">
                            <h3 class="plan-name">SILVER</h3>
                            <div class="plan-price">
                                <span class="currency">R$</span>
                                <span class="amount">39</span>
                                <span class="period">/mo</span>
                            </div>
                            <p class="plan-savings"><span class="save-amount">No commitment</span> <span class="save-period">Cancel anytime</span></p>
                        </div>
                        <div class="plan-icon">
                            <img src="<?php echo get_template_directory_uri(); ?>/imagens/planos/prata.png" alt="Silver Badge">
                        </div>
                    </div>
                    <ul class="plan-features">
                        <li><i class="fas fa-check"></i> 1 free challenge entry ticket <strong>a month</strong></li>
                        <li><i class="fas fa-check"></i> Basic Academy access</li>
                        <li><i class="fas fa-check"></i> Discord access</li>
                        <li><i class="fas fa-check"></i> Basic analysis tools</li>
                        <li><i class="fas fa-check"></i> Email support</li>
                        <li><i class="fas fa-check"></i> Monthly group coaching session</li>
                    </ul>
                    <button class="btn-plan" data-plan="silver" data-period="monthly" data-price="39.00" onclick="goToUpgrade(this)">Start Silver Plan</button>
                </div>
                
               <!-- Silver Plan - YEARLY -->
<div class="plan-card" data-period="yearly">
    <div class="plan-top-section">
        <div class="plan-header">
            <h3 class="plan-name">SILVER</h3>
            <div class="plan-price">
                <span class="currency">R$</span>
                <span class="amount">39</span>
                <span class="period">/m</span>
            </div>
            <p class="plan-savings">
                You Save <span class="save-amount">R$120</span> 
                <span class="save-period">Billed annually</span>
            </p>
            <p class="plan-description">
                For aspiring players testing the waters
            </p>
        </div>

        <div class="plan-icon">
            <img src="<?php echo get_template_directory_uri(); ?>/imagens/planos/prata.png" alt="Silver Badge">
        </div>
    </div>

    <ul class="plan-features">
    <li>
        <i class="fas fa-check"></i> 
        <strong class="save-amount">
            1 free challenge entry ticket a month
        </strong>
    </li>
    <li><i class="fas fa-check"></i> Basic Academy access</li>
    <li><i class="fas fa-check"></i> Discord access</li>
    <li><i class="fas fa-check"></i> Basic analysis tools</li>
    <li><i class="fas fa-check"></i> Email support</li>
    <li><i class="fas fa-check"></i> Monthly group coaching session</li>
</ul>

    <button 
        class="btn-plan" 
        data-plan="silver" 
        data-period="yearly" 
        data-price="468.00" 
        onclick="goToUpgrade(this)">
        Start Silver Plan
    </button>
</div>
                
                <!-- Gold Plan (Popular) - MONTHLY -->
                <div class="plan-card popular" data-period="monthly" style="display: none;">
                    <div class="popular-badge">MOST POPULAR</div>
                    <div class="plan-top-section">
                        <div class="plan-header">
                            <h3 class="plan-name gold">GOLD</h3>
                            <div class="plan-price">
                                <span class="currency">R$</span>
                                <span class="amount">49</span>
                                <span class="period">/mo</span>
                            </div>
                            <p class="plan-savings"><span class="save-amount">No commitment</span> <span class="save-period">Cancel anytime</span></p>
                        </div>
                        <div class="plan-icon">
                            <img src="<?php echo get_template_directory_uri(); ?>/imagens/planos/dourada.png" alt="Gold Badge">
                        </div>
                    </div>
                    <ul class="plan-features">
                        <li><i class="fas fa-check"></i> 2 challenge entry tickets <strong>a month</strong></li>
                        <li><i class="fas fa-check"></i> Everything in Silver</li>
                        <li><i class="fas fa-check"></i> Advanced Academy courses</li>
                        <li><i class="fas fa-check"></i> 1-hour monthly personal coaching</li>
                        <li><i class="fas fa-check"></i> Advanced analysis tools</li>
                        <li><i class="fas fa-check"></i> Gold Discord community</li>
                        <li><i class="fas fa-check"></i> Hand history review</li>
                        <li><i class="fas fa-check"></i> Priority email support</li>
                        <li><i class="fas fa-check"></i> Discounts at the best online academies in Brazil</li>
                    </ul>
                    <button class="btn-plan gold" data-plan="gold" data-period="monthly" data-price="49.00" onclick="goToUpgrade(this)">Start Gold Plan</button>
                </div>
                
                <!-- Gold Plan (Popular) - YEARLY -->
<div class="plan-card popular" data-period="yearly">
    <div class="popular-badge">MOST POPULAR</div>

    <div class="plan-top-section">
        <div class="plan-header">
            <h3 class="plan-name gold">GOLD</h3>

            <div class="plan-price">
                <span class="currency">R$</span>
                <span class="amount">49</span>
                <span class="period">/m</span>
            </div>

            <p class="plan-savings">
                You Save <span class="save-amount">R$240</span> 
                <span class="save-period">Billed annually</span>
            </p>

            <p class="plan-description">
                For improving grinders building a bankroll
            </p>
        </div>

        <div class="plan-icon">
            <img src="<?php echo get_template_directory_uri(); ?>/imagens/planos/dourada.png" alt="Gold Badge">
        </div>
    </div>

    <ul class="plan-features">
        <li>
            <i class="fas fa-check"></i> 
            <strong class="save-amount">
                2 challenge entry tickets a month
            </strong>
        </li>
        <li><i class="fas fa-check"></i> Everything in Silver</li>
        <li><i class="fas fa-check"></i> Advanced Academy courses</li>
        <li><i class="fas fa-check"></i> 1-hour monthly personal coaching</li>
        <li><i class="fas fa-check"></i> Advanced analysis tools</li>
        <li><i class="fas fa-check"></i> Gold Discord community</li>
        <li><i class="fas fa-check"></i> Hand history review</li>
        <li><i class="fas fa-check"></i> Priority email support</li>
        <li><i class="fas fa-check"></i> Discounts at the best online academies in Brazil</li>
    </ul>

    <button 
        class="btn-plan gold" 
        data-plan="gold" 
        data-period="yearly" 
        data-price="588.00" 
        onclick="goToUpgrade(this)">
        Start Gold Plan
    </button>
</div>
                
                <!-- Diamond Plan - MONTHLY -->
                <div class="plan-card" data-period="monthly" style="display: none;">
                    <div class="plan-top-section">
                        <div class="plan-header">
                            <h3 class="plan-name diamond">DIAMOND</h3>
                            <div class="plan-price">
                                <span class="currency">R$</span>
                                <span class="amount">69</span>
                                <span class="period">/mo</span>
                            </div>
                            <p class="plan-savings"><span class="save-amount">No commitment</span> <span class="save-period">Cancel anytime</span></p>
                        </div>
                        <div class="plan-icon">
                            <img src="<?php echo get_template_directory_uri(); ?>/imagens/planos/diamante.png" alt="Diamond Badge">
                        </div>
                    </div>
                    <ul class="plan-features">
                        <li><i class="fas fa-check"></i> 4 challenge entry tickets <strong>a month</strong></li>
                        <li><i class="fas fa-check"></i> Everything in Gold</li>
                        <li><i class="fas fa-check"></i> Video game analysis</li>
                        <li><i class="fas fa-check"></i> Priority support</li>
                        <li><i class="fas fa-check"></i> Exclusive Diamond Discord community</li>
                        <li><i class="fas fa-check"></i> Live tournament backing opportunities</li>
                    </ul>
                    <button class="btn-plan diamond" data-plan="diamond" data-period="monthly" data-price="69.00" onclick="goToUpgrade(this)">Start Diamond Plan</button>
                </div>
                
               <!-- Diamond Plan - YEARLY -->
<div class="plan-card" data-period="yearly">
    <div class="plan-top-section">
        <div class="plan-header">
            <h3 class="plan-name diamond">DIAMOND</h3>
            <div class="plan-price">
                <span class="currency">R$</span>
                <span class="amount">69</span>
                <span class="period">/m</span>
            </div>
            <p class="plan-savings">
                You Save <span class="save-amount">R$360</span> 
                <span class="save-period">Billed annually</span>
            </p>
            <p class="plan-description">
                For serious players ready to go pro
            </p>
        </div>
        <div class="plan-icon">
            <img src="<?php echo get_template_directory_uri(); ?>/imagens/planos/diamante.png" alt="Diamond Badge">
        </div>
    </div>

    <ul class="plan-features">
        <li>
            <i class="fas fa-check"></i> 
            <strong class="save-amount">
                4 challenge entry tickets a month
            </strong>
        </li>
        <li><i class="fas fa-check"></i> Everything in Gold</li>
        <li><i class="fas fa-check"></i> Video game analysis</li>
        <li><i class="fas fa-check"></i> Priority support</li>
        <li><i class="fas fa-check"></i> Exclusive Diamond Discord community</li>
        <li><i class="fas fa-check"></i> Live tournament backing opportunities</li>
    </ul>

    <button class="btn-plan diamond" 
        data-plan="diamond" 
        data-period="yearly" 
        data-price="828.00" 
        onclick="goToUpgrade(this)">
        Start Diamond Plan
    </button>
</div>
            </div>
            
            <!-- One Time Entry Tickets -->
            <div class="entry-tickets">
                <div class="ticket-icon">
                    <img src="<?php echo get_template_directory_uri(); ?>/imagens/ticket.png" alt="Ticket Icon">
                </div>
                <div class="ticket-info">
                    <h3>ONE TIME <br><strong>ENTRY TICKETS</strong></h3>
                    <p class="ticket-price">R$<strong>25</strong> <span>/ Ticket</span></p>
                </div>
                <div class="ticket-description">
                    <p>Get single entry tickets and jump right back whenever you want!</p>
                </div>
                <div class="ticket-quantity">
                    <button class="btn-quantity-down" onclick="decreaseQuantity()">
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <input type="text" class="quantity-display" value="1" readonly>
                    <button class="btn-quantity-up" onclick="increaseQuantity()">
                        <i class="fas fa-chevron-up"></i>
                    </button>
                </div>
                <button class="btn-purchase" onclick="handlePurchaseTickets(event)">Purchase Tickets for R$25</button>
            </div>

            <!-- Plans Comparison Table -->
            <div class="plans-comparison">
                <h2 class="comparison-title">Chart comparison for the plans</h2>
                <p class="comparison-subtitle">Short description</p>
                
                <div class="comparison-table">
                    <div class="table-header">
                        <div class="header-cell benefits-col">Benefits</div>
                        <div class="header-cell"><img src="<?php echo get_template_directory_uri(); ?>/imagens/planos/prata.png" alt="Silver"> Silver</div>
                        <div class="header-cell"><img src="<?php echo get_template_directory_uri(); ?>/imagens/planos/dourada.png" alt="Gold"> Gold</div>
                        <div class="header-cell"><img src="<?php echo get_template_directory_uri(); ?>/imagens/planos/diamante.png" alt="Diamond"> Diamond</div>
                    </div>
                    
                    <div class="table-row">
                        <div class="cell benefits-col">Challenge entry tickets per month</div>
                        <div class="cell">1</div>
                        <div class="cell">2</div>
                        <div class="cell">4</div>
                    </div>
                    
                    <div class="table-row">
                        <div class="cell benefits-col">Basic Academy access</div>
                        <div class="cell"><i class="fas fa-check"></i></div>
                        <div class="cell"><i class="fas fa-check"></i></div>
                        <div class="cell"><i class="fas fa-check"></i></div>
                    </div>
                    
                    <div class="table-row">
                        <div class="cell benefits-col">Discord access</div>
                        <div class="cell"><i class="fas fa-check"></i></div>
                        <div class="cell"><i class="fas fa-check"></i></div>
                        <div class="cell"><i class="fas fa-check"></i></div>
                    </div>
                    
                    <div class="table-row">
                        <div class="cell benefits-col">Basic analysis tools</div>
                        <div class="cell"><i class="fas fa-check"></i></div>
                        <div class="cell"><i class="fas fa-check"></i></div>
                        <div class="cell"><i class="fas fa-check"></i></div>
                    </div>
                    
                    <div class="table-row">
                        <div class="cell benefits-col">Email support</div>
                        <div class="cell"><i class="fas fa-check"></i></div>
                        <div class="cell"><i class="fas fa-check"></i></div>
                        <div class="cell"><i class="fas fa-check"></i></div>
                    </div>
                    
                    <div class="table-row">
                        <div class="cell benefits-col">Monthly group coaching session</div>
                        <div class="cell"><i class="fas fa-check"></i></div>
                        <div class="cell"><i class="fas fa-check"></i></div>
                        <div class="cell"><i class="fas fa-check"></i></div>
                    </div>
                    
                    <div class="table-row">
                        <div class="cell benefits-col">Advanced Academy courses</div>
                        <div class="cell"><i class="fas fa-times"></i></div>
                        <div class="cell"><i class="fas fa-check"></i></div>
                        <div class="cell"><i class="fas fa-check"></i></div>
                    </div>
                    
                    <div class="table-row">
                        <div class="cell benefits-col">1-hour monthly personal coaching</div>
                        <div class="cell"><i class="fas fa-times"></i></div>
                        <div class="cell"><i class="fas fa-check"></i></div>
                        <div class="cell"><i class="fas fa-check"></i></div>
                    </div>
                    
                    <div class="table-row">
                        <div class="cell benefits-col">Advanced analysis tools</div>
                        <div class="cell"><i class="fas fa-times"></i></div>
                        <div class="cell"><i class="fas fa-check"></i></div>
                        <div class="cell"><i class="fas fa-check"></i></div>
                    </div>
                    
                    <div class="table-row">
                        <div class="cell benefits-col">Gold Discord community</div>
                        <div class="cell"><i class="fas fa-times"></i></div>
                        <div class="cell"><i class="fas fa-check"></i></div>
                        <div class="cell"><i class="fas fa-check"></i></div>
                    </div>
                    
                    <div class="table-row">
                        <div class="cell benefits-col">Hand history review</div>
                        <div class="cell"><i class="fas fa-times"></i></div>
                        <div class="cell"><i class="fas fa-check"></i></div>
                        <div class="cell"><i class="fas fa-check"></i></div>
                    </div>
                    
                    <div class="table-row">
                        <div class="cell benefits-col">Priority email support</div>
                        <div class="cell"><i class="fas fa-times"></i></div>
                        <div class="cell"><i class="fas fa-check"></i></div>
                        <div class="cell"><i class="fas fa-check"></i></div>
                    </div>
                    
                    <div class="table-row">
                        <div class="cell benefits-col">Discounts at the best online academies in Brazil</div>
                        <div class="cell"><i class="fas fa-times"></i></div>
                        <div class="cell"><i class="fas fa-check"></i></div>
                        <div class="cell"><i class="fas fa-check"></i></div>
                    </div>
                    
                    <div class="table-row">
                        <div class="cell benefits-col">Video game analysis</div>
                        <div class="cell"><i class="fas fa-times"></i></div>
                        <div class="cell"><i class="fas fa-times"></i></div>
                        <div class="cell"><i class="fas fa-check"></i></div>
                    </div>
                    
                    <div class="table-row">
                        <div class="cell benefits-col">Priority support</div>
                        <div class="cell"><i class="fas fa-times"></i></div>
                        <div class="cell"><i class="fas fa-times"></i></div>
                        <div class="cell"><i class="fas fa-check"></i></div>
                    </div>
                    
                    <div class="table-row">
                        <div class="cell benefits-col">Exclusive Gold Discord community.</div>
                        <div class="cell"><i class="fas fa-times"></i></div>
                        <div class="cell"><i class="fas fa-times"></i></div>
                        <div class="cell"><i class="fas fa-check"></i></div>
                    </div>
                    
                    <div class="table-row">
                        <div class="cell benefits-col">Live tournament backing opportunities</div>
                        <div class="cell"><i class="fas fa-times"></i></div>
                        <div class="cell"><i class="fas fa-times"></i></div>
                        <div class="cell"><i class="fas fa-check"></i></div>
                    </div>
                </div>
            </div>

            <!-- FAQ Section -->
            <div class="faq-section">
                <h2 class="faq-title">Frequently Asked Questions</h2>
                <p class="faq-subtitle">Poker membership</p>
                
                <div class="faq-container">
                    <div class="faq-item">
                        <button class="faq-question" onclick="toggleFAQ(this)">
                            <span>Can I change my plan anytime?</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="faq-answer">
                            <p>Yes, you can upgrade or downgrade your plan at any time. Changes will take effect in your next billing cycle.</p>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <button class="faq-question" onclick="toggleFAQ(this)">
                            <span>What happens to unused challenges?</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="faq-answer">
                            <p>Unused challenges expire at the end of each month. They don't roll over, so make sure to use them!</p>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <button class="faq-question" onclick="toggleFAQ(this)">
                            <span>Is there a free trial?</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="faq-answer">
                            <p>We offer a 1 month free trial for all of our monthly plans. And a 3-months free trial for all of our yearly plans.</p>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <button class="faq-question" onclick="toggleFAQ(this)">
                            <span>Do I still get sponsorship opportunities?</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="faq-answer">
                            <p>Absolutely! Win challenges to prove your skills and qualify for our professional sponsorship contracts.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Not Logged In Modal -->
<div id="notLoggedInModal" class="modal">
    <div class="modal-content" style="max-width: 400px;">
        <div class="modal-top-bar">
            <img src="<?php echo get_template_directory_uri(); ?>/logopoker.png" alt="POKER III" class="modal-logo">
            <button class="modal-close" onclick="closeNotLoggedInModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="modal-header">
            <h2 class="modal-title">Authentication Required</h2>
            <p class="modal-subtitle">You need to be logged in to purchase tickets</p>
        </div>
        
        <div style="padding: 30px 20px; text-align: center;">
            <i class="fas fa-lock" style="font-size: 48px; color: #ff3366; margin-bottom: 20px; display: block;"></i>
            <p style="font-size: 16px; color: #ffffff; margin-bottom: 30px; line-height: 1.6;">
                To purchase tickets, please log in or create an account first.
            </p>
            <button class="btn-continue" onclick="openLoginAndCloseNotLoggedIn(event)" style="width: 100%; margin-bottom: 10px;">
                Click here to Login
            </button>
            <button class="btn-close-modal" onclick="closeNotLoggedInModal()" style="width: 100%;">
                Cancel
            </button>
        </div>
    </div>
</div>

<!-- Login Modal -->
<div id="loginModal" class="modal">
    <div class="modal-content">
        <div class="modal-top-bar">
            <img src="<?php echo get_template_directory_uri(); ?>/logopoker.png" alt="POKER III" class="modal-logo">
            <button class="modal-close" onclick="closeLoginModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="modal-header">
            <h2 class="modal-title" id="modalTitle">Welcome</h2>
            <p class="modal-subtitle" id="modalSubtitle">Fill in the fields to continue registration</p>
        </div>
        
        <div class="modal-tabs">
            <button class="tab-btn active" id="loginTab" onclick="switchTab('login')">Login</button>
            <button class="tab-btn" id="signupTab" onclick="switchTab('signup')">Sign Up</button>
        </div>
        
        <form class="modal-form" id="loginForm">
            <div class="form-group">
                <input type="email" class="form-input" id="loginEmail" placeholder="|Email" required oninput="checkFormFilled('login')">
            </div>
            
            <div class="form-group password-group">
                <input type="password" class="form-input" id="loginPassword" placeholder="Password" required oninput="checkFormFilled('login')">
                <button type="button" class="toggle-password" onclick="togglePassword('loginPassword')">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
            
            <a href="#" class="forgot-password" onclick="showForgotPassword(event)">Forgot Your Password?</a>
            
            <button type="submit" class="btn-continue" id="loginContinue" disabled>Continue</button>
            
            <div class="divider">
                <span>Or</span>
            </div>
            
            <button type="button" class="btn-google">
                <i class="fab fa-google"></i> Continue with Google
            </button>
        </form>
        
        <form class="modal-form" id="signupForm" style="display: none;">
            <div class="form-group">
                <input type="email" class="form-input" id="signupEmail" placeholder="|Email" required>
            </div>
            
            <div class="form-group password-group password-group-signup">
                <input type="password" class="form-input" id="signupPassword" placeholder="Password" required oninput="validatePassword(this.value);">
                <button type="button" class="toggle-password" onclick="togglePassword('signupPassword')">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
            
            <div class="password-requirements" id="passwordRequirements">
                <p class="requirements-title">Your password must contain at least</p>
                <div class="requirement" id="uppercaseReq">
                    <i class="fas fa-times"></i>
                    <span>1 uppercase letter</span>
                </div>
                <div class="requirement" id="lengthReq">
                    <i class="fas fa-times"></i>
                    <span>8 characters</span>
                </div>
            </div>
            
            <button type="submit" class="btn-continue" id="signupContinue">Continue</button>
            
            <div class="divider">
                <span>Or</span>
            </div>
            
            <button type="button" class="btn-google">
                <i class="fab fa-google"></i> Continue with Google
            </button>
            
            <p class="terms-text">By creating an account you agree to domain.com's <a href="#">Terms of Use</a> and <a href="#">Privacy Policy</a></p>
        </form>
        
        <form class="modal-form" id="forgotPasswordForm" style="display: none;">
            <div class="form-group">
                <input type="email" class="form-input" id="forgotEmail" placeholder="Enter your email" required>
            </div>
            
            <a href="#" class="forgot-password" id="backToLogin" onclick="backToLogin(event)">Back to Login</a>
            
            <button type="submit" class="btn-continue" id="sendEmailBtn">Send Email</button>
        </form>
        
        <form class="modal-form" id="signupDetailsForm" style="display: none;">
            <div class="form-group">
                <input type="text" class="form-input" id="firstName" placeholder="First Name" required>
            </div>
            
            <div class="form-group">
                <input type="text" class="form-input" id="lastName" placeholder="Last Name" required>
            </div>
            
            <div class="form-group phone-group">
                <select class="country-code-select" id="countryCode" onchange="updatePhonePlaceholder()">
                    <option value="+380">+380</option>
                    <option value="+1">+1</option>
                    <option value="+44">+44</option>
                    <option value="+55" selected>+55</option>
                    <option value="+351">+351</option>
                </select>
                <input type="tel" class="form-input phone-input" id="phoneNumber" placeholder="(11) 94993-1617" maxlength="16" required oninput="formatPhoneNumber()">
            </div>
            
            <a href="#" class="forgot-password" id="backToSignup" onclick="backToSignup(event)">Back</a>
            
            <button type="submit" class="btn-continue" id="detailsContinue">Continue</button>
        </form>
        
        <form class="modal-form" id="phoneVerificationForm" style="display: none;">
            <p class="verification-phone" id="verificationPhone">+357 999 99 99</p>
            
            <p class="verification-instruction" id="verificationInstruction">
                Please enter the code below to confirm your phone number. Make sure to keep this window open while you check your phone. The code may take up to 10 minutes to arrive.
            </p>
            
            <div class="form-group">
                <input type="text" class="form-input code-input" id="verificationCode" placeholder="— — — —" maxlength="4" pattern="[0-9]{4}" required oninput="formatCodeInput()">
            </div>
            
            <button type="button" class="btn-resend" id="resendBtn" disabled>Resend in 118 sec...</button>
            
            <a href="#" class="forgot-password" id="changePhoneNumber" onclick="changePhoneNumber(event)">Change phone number</a>
        </form>
        
        <div class="modal-form" id="almostDoneScreen" style="display: none;">
            <p class="almost-done-text">
                <span id="almostDoneSubtitle1">Thanks for confirming your email! You're almost there - just a quick</span>
                <span id="almostDoneSubtitle2">step left to unlock all the awesome benefits we offer.</span>
            </p>
            
            <p class="almost-done-help" id="almostDoneHelp">Help us out by taking a short screening questionnaire.</p>
            
            <div class="almost-done-buttons">
                <button type="button" class="btn-ask-later" id="askLaterBtn" onclick="askMeLater()">Ask me later</button>
                <button type="button" class="btn-lets-go" id="letsGoBtn" onclick="letsGo()">Let's go</button>
            </div>
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
            <?php for ($i = 1; $i <= 12; $i++) : ?>
            <div class="avatar-option" onclick="selectAvatar('avatar<?php echo $i; ?>.png')">
                <img src="<?php echo get_template_directory_uri(); ?>/imagens/avatares/avatar<?php echo $i; ?>.png" alt="Avatar <?php echo $i; ?>">
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
                <select class="phone-country-select" id="phoneCountryCode">
                    <option value="+380">+380</option>
                    <option value="+1" selected>+1</option>
                    <option value="+44">+44</option>
                    <option value="+55">+55</option>
                    <option value="+351">+351</option>
                </select>
                <input type="tel" id="phoneInputModal" class="phone-input-modal" placeholder="11 222 33 44" maxlength="15">
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
                <button class="btn-resend-code" id="resendBtn2" disabled>Resend in <span id="resendTimer">40</span> sec...</button>
            </div>
        </div>
    </div>
</div>

<!-- Contact Us Modal -->
<div id="contactModal" class="modal contact-modal">
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
            <p class="modal-subtitle-contact">Use the form to contact us with any questions, concerns, or feedback.</p>
        </div>
        
        <div class="contact-form-container">
            <div class="message-input-wrapper">
                <textarea id="contactMessage" class="contact-message-input" placeholder="Add your message" maxlength="500" rows="5"></textarea>
                <div class="message-actions">
                    <button class="btn-send-message" onclick="sendContactMessage()">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                    <span class="char-count"><span id="charCount">0</span>/0</span>
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

<?php 
// Iniciar sessão ANTES de get_footer
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$isUserLoggedIn = isset($_SESSION['logged_in']) && ($_SESSION['logged_in'] === true || $_SESSION['logged_in'] == 1);
$sessionUserId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
$sessionUserEmail = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : '';
?>

<?php get_footer(); ?>
<script src="<?php echo get_template_directory_uri(); ?>/js/planos-toggle.js"></script>

<script>
// Debug de sessão
console.log('=== DEBUG SESSION ===');
console.log('User ID:', <?php echo $sessionUserId; ?>);
console.log('User Email:', '<?php echo $sessionUserEmail; ?>');
console.log('Logged In:', <?php echo $isUserLoggedIn ? 'true' : 'false'; ?>);
console.log('Session Data:', <?php echo json_encode($_SESSION); ?>);

const userIsLoggedIn = <?php echo $isUserLoggedIn ? 'true' : 'false'; ?>;

function isUserLoggedIn() {
    return userIsLoggedIn;
}

// Redirect to Upgrade page with plan parameters
function goToUpgrade(button) {
    console.log('🎯 Plan button clicked!');
    
    const plan = button.getAttribute('data-plan');
    const period = button.getAttribute('data-period');
    const price = button.getAttribute('data-price');
    
    console.log('Plan:', plan);
    console.log('Period:', period);
    console.log('Price:', price);
    
    // Construir URL de upgrade com parâmetros
    const upgradeUrl = '<?php echo get_site_url(); ?>/upgrade/?plan=' + plan + '&period=' + period + '&price=' + price;
    console.log('Redirecting to:', upgradeUrl);
    
    window.location.href = upgradeUrl;
}

// Handle Purchase Tickets
function handlePurchaseTickets(event) {
    if (event) event.preventDefault();
    
    console.log('🎫 Purchase button clicked!');
    console.log('User is logged in?', userIsLoggedIn);
    
    if (!userIsLoggedIn) {
        console.log('❌ Not logged in - showing modal');
        document.getElementById('notLoggedInModal').style.display = 'flex';
    } else {
        console.log('✅ Logged in - redirecting to checkout');
        
        // Gerar token e pegar quantidade (do plano2.js)
        const qty = typeof ticketQuantity !== 'undefined' ? ticketQuantity : 1;
        const token = typeof generateToken === 'function' ? generateToken() : Math.random().toString(16).substring(2, 18);
        
        const checkoutUrl = '<?php echo get_site_url(); ?>/checkout/?qty=' + qty + '&token=' + token;
        console.log('Redirecting to:', checkoutUrl);
        
        window.location.href = checkoutUrl;
    }
}

// Fechar modal de não logado
function closeNotLoggedInModal() {
    document.getElementById('notLoggedInModal').style.display = 'none';
}

// Abrir login e fechar o modal de não logado
function openLoginAndCloseNotLoggedIn(event) {
    if (event) event.preventDefault();
    closeNotLoggedInModal();
    if (typeof openLoginModal === 'function') {
        openLoginModal();
    }
}

// Fechar modal quando clicar fora
window.addEventListener('click', function(event) {
    const notLoggedInModal = document.getElementById('notLoggedInModal');
    if (event.target === notLoggedInModal) {
        notLoggedInModal.style.display = 'none';
    }
});
</script>

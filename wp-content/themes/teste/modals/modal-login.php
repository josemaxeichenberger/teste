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
            
            <!-- Modal de escolha de método de verificação -->
            <div class="modal-form" id="verificationMethodForm" style="display: none;">
                <h3 class="method-title" id="verificationMethodTitle">Choose Verification Method</h3>
                <p class="method-subtitle" id="verificationMethodSubtitle">How would you like to receive your verification code?</p>
                
                <div class="verification-methods">
                    <label class="verification-option" for="methodSMS">
                        <input type="radio" name="verificationMethod" id="methodSMS" value="sms" checked>
                        <div class="option-content">
                            <i class="fas fa-mobile-alt"></i>
                            <div class="option-text">
                                <span class="option-title">SMS</span>
                                <span class="option-desc">Receive code via text message</span>
                            </div>
                        </div>
                    </label>
                    
                    <label class="verification-option" for="methodEmail">
                        <input type="radio" name="verificationMethod" id="methodEmail" value="email">
                        <div class="option-content">
                            <i class="fas fa-envelope"></i>
                            <div class="option-text">
                                <span class="option-title">Email</span>
                                <span class="option-desc">Receive code in your inbox</span>
                            </div>
                        </div>
                    </label>
                </div>
                
                <button type="button" class="btn-continue" id="confirmMethodBtn" onclick="confirmVerificationMethod()">Continue</button>
                
                <a href="#" class="forgot-password" id="backToDetails" onclick="backToPersonalDetails(event)">Back</a>
            </div>
            
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
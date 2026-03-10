<?php
/**
 * Template Name: Checkout
 * Description: Página de checkout para compra de tickets
 */

// Iniciar sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar se o usuário está logado
$isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;

// Se não estiver logado, redirecionar para planos
if (!$isLoggedIn) {
    wp_redirect(site_url('/planos/'));
    exit;
}

// Validar token de checkout
$token = isset($_GET['token']) ? $_GET['token'] : '';
if (!preg_match('/^[a-f0-9]{16}$/', $token)) {
    // Token inválido ou ausente, redirecionar para planos
    wp_redirect(site_url('/planos/'));
    exit;
}

// Obter dados do usuário
$userEmail = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : '';
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Capturar quantidade da URL
$quantity = isset($_GET['qty']) ? intval($_GET['qty']) : 1;
if ($quantity < 1) $quantity = 1;

// Salvar token na sessão para rastreamento do pedido
$_SESSION['checkout_token'] = $token;

// Calcular valores
$pricePerTicket = 25;
$subtotal = $quantity * $pricePerTicket;
$tax = 0; // 0% de taxa
$total = $subtotal + $tax;

// Identificador de conta EFI Pay — encontre em: Painel EFI > API > Introdução > Identificador de conta
$payeeCode = 'f89854a5b4f7a1065c6be6650d3f674d';

get_header();
?>

<style>
.checkout-page {
    min-height: 100vh;
    background: #00133D;
    padding: 80px 30px;
}

.checkout-container {
    max-width: 1300px;
    margin: 0 auto;
    background: rgba(0, 19, 61, 0.3);
    border: 1px solid rgba(107, 155, 221, 0.1);
    border-radius: 24px;
    padding: 50px 40px;
    backdrop-filter: blur(10px);
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
}

.checkout-header-section {
    text-align: center;
    margin-bottom: 50px;
}

.checkout-header-section h1 {
    font-size: 36px;
    font-weight: 700;
    color: #ffffff;
    margin-bottom: 10px;
    letter-spacing: -0.5px;
}

.checkout-header-section p {
    font-size: 16px;
    color: rgba(255, 255, 255, 0.65);
    letter-spacing: 0.3px;
}

.checkout-grid {
    display: grid;
    grid-template-columns: 1fr 450px;
    gap: 40px;
    align-items: start;
}

/* Order Summary */
.order-summary-card {
    background: linear-gradient(145deg, rgba(59, 111, 204, 0.04), rgba(46, 90, 170, 0.04));
    border: 1px solid rgba(107, 155, 221, 0.15);
    border-radius: 20px;
    padding: 40px;
    backdrop-filter: blur(15px);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
}

.summary-header {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.summary-icon {
    width: 56px;
    height: 56px;
    background: linear-gradient(135deg, rgba(59, 111, 204, 0.15), rgba(46, 90, 170, 0.15));
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 12px rgba(59, 111, 204, 0.1);
}

.summary-icon i {
    font-size: 24px;
    color: #6B9BDD;
}

.summary-title {
    font-size: 22px;
    font-weight: 700;
    color: #ffffff;
    margin: 0;
}

.order-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.item-details h4 {
    font-size: 18px;
    font-weight: 600;
    color: #ffffff;
    margin: 0 0 8px 0;
}

.item-details p {
    font-size: 14px;
    color: rgba(255, 255, 255, 0.5);
    margin: 0;
}

.item-price {
    font-size: 20px;
    font-weight: 700;
    color: #ffffff;
}

.order-totals {
    margin-top: 25px;
    padding-top: 20px;
    border-top: 2px solid rgba(255, 255, 255, 0.1);
}

.total-row {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
}

.total-label {
    font-size: 15px;
    color: rgba(255, 255, 255, 0.6);
}

.total-value {
    font-size: 16px;
    font-weight: 600;
    color: #ffffff;
}

.total-row.final {
    padding: 20px 0 0 0;
    margin-top: 15px;
    border-top: 2px solid rgba(107, 155, 221, 0.2);
    background: linear-gradient(90deg, rgba(59, 111, 204, 0.03), rgba(46, 90, 170, 0.03));
    padding: 20px 15px 0 15px;
    margin-left: -15px;
    margin-right: -15px;
    border-radius: 8px;
}

.total-row.final .total-label {
    font-size: 20px;
    font-weight: 700;
    color: #ffffff;
}

.total-row.final .total-value {
    font-size: 26px;
    font-weight: 700;
    color: #6B9BDD;
}

/* Quantity Controls */
.quantity-controls {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-top: 8px;
}

.quantity-controls span {
    font-size: 13px;
    color: rgba(255, 255, 255, 0.5);
}

.quantity-controls .qty-controls {
    display: flex;
    align-items: center;
    gap: 12px;
    background: rgba(0, 19, 61, 0.5);
    border: 1px solid rgba(107, 155, 221, 0.2);
    border-radius: 10px;
    padding: 8px 12px;
}

.quantity-controls button {
    width: 32px;
    height: 32px;
    border: none;
    background: linear-gradient(135deg, #3B6FCC, #2E5AAA);
    color: #ffffff;
    border-radius: 8px;
    font-size: 18px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.quantity-controls button:hover {
    background: linear-gradient(135deg, #4A7FDD, #3D6ABB);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(59, 111, 204, 0.3);
}

.quantity-controls button:active {
    transform: translateY(0);
}

.quantity-controls button:disabled {
    opacity: 0.4;
    cursor: not-allowed;
    transform: none;
}

.quantity-controls .qty-display {
    font-size: 18px;
    font-weight: 700;
    color: #ffffff;
    min-width: 30px;
    text-align: center;
}

/* Payment Methods */
.payment-methods-card {
    background: linear-gradient(145deg, rgba(59, 111, 204, 0.04), rgba(46, 90, 170, 0.04));
    border: 1px solid rgba(107, 155, 221, 0.15);
    border-radius: 20px;
    padding: 40px;
    backdrop-filter: blur(15px);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
}

.payment-header h2 {
    font-size: 24px;
    font-weight: 700;
    color: #ffffff;
    margin: 0 0 10px 0;
}

.payment-header p {
    font-size: 14px;
    color: rgba(255, 255, 255, 0.55);
    margin: 0 0 30px 0;
}

.payment-options {
    display: flex;
    flex-direction: column;
    gap: 15px;
    margin-bottom: 35px;
}

.payment-option {
    background: rgba(0, 19, 61, 0.4);
    border: 2px solid rgba(255, 255, 255, 0.08);
    border-radius: 14px;
    padding: 22px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 15px;
    backdrop-filter: blur(5px);
}

.payment-option:hover {
    border-color: rgba(107, 155, 221, 0.4);
    background: rgba(59, 111, 204, 0.08);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(59, 111, 204, 0.12);
}

.payment-option.selected {
    border-color: #6B9BDD;
    background: linear-gradient(135deg, rgba(107, 155, 221, 0.12), rgba(59, 111, 204, 0.1));
    box-shadow: 0 4px 20px rgba(59, 111, 204, 0.15);
}

.payment-option input[type="radio"] {
    appearance: none;
    width: 22px;
    height: 22px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-radius: 50%;
    cursor: pointer;
    position: relative;
    flex-shrink: 0;
}

.payment-option input[type="radio"]:checked {
    border-color: #6B9BDD;
}

.payment-option input[type="radio"]:checked::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 12px;
    height: 12px;
    background: #6B9BDD;
    border-radius: 50%;
}

.payment-icon {
    width: 54px;
    height: 54px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, rgba(59, 111, 204, 0.12), rgba(46, 90, 170, 0.12));
    border-radius: 12px;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(59, 111, 204, 0.08);
}

.payment-icon i {
    font-size: 24px;
    color: #6B9BDD;
}

.payment-info h3 {
    font-size: 17px;
    font-weight: 600;
    color: #ffffff;
    margin: 0 0 5px 0;
}

.payment-info p {
    font-size: 13px;
    color: rgba(255, 255, 255, 0.5);
    margin: 0;
}

.payment-recommended {
    background: linear-gradient(135deg, rgba(34, 197, 94, 0.15), rgba(16, 185, 129, 0.15));
    color: #5FD88A;
    font-size: 11px;
    font-weight: 700;
    padding: 6px 14px;
    border-radius: 20px;
    margin-left: auto;
    text-transform: uppercase;
    border: 1px solid rgba(34, 197, 94, 0.25);
    box-shadow: 0 2px 8px rgba(34, 197, 94, 0.1);
}

.checkout-actions {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.btn-complete-purchase {
    background: linear-gradient(135deg, #3B6FCC 0%, #2E5AAA 100%);
    border: none;
    border-radius: 14px;
    color: #ffffff;
    font-size: 18px;
    font-weight: 700;
    padding: 20px 32px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    box-shadow: 0 6px 20px rgba(59, 111, 204, 0.25);
}

.btn-complete-purchase:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 30px rgba(59, 111, 204, 0.35);
    background: linear-gradient(135deg, #2E5AAA 0%, #3B6FCC 100%);
}

.btn-complete-purchase:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none;
}

.btn-back {
    background: transparent;
    border: 2px solid rgba(255, 255, 255, 0.15);
    border-radius: 14px;
    color: rgba(255, 255, 255, 0.85);
    font-size: 15px;
    font-weight: 600;
    padding: 16px 24px;
    cursor: pointer;
    transition: all 0.3s ease;
    text-align: center;
}

.btn-back:hover {
    border-color: rgba(255, 255, 255, 0.35);
    background: rgba(255, 255, 255, 0.05);
    transform: translateY(-2px);
    color: #ffffff;
}

.secure-badge {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    margin-top: 25px;
    padding-top: 25px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.secure-badge i {
    color: #5FD88A;
    font-size: 18px;
}

.secure-badge span {
    font-size: 14px;
    color: rgba(255, 255, 255, 0.6);
}

/* ============================================================================
   CREDIT CARD FORM
   ============================================================================ */

.checkout-cc-form {
    margin-bottom: 25px;
    display: flex;
    flex-direction: column;
    gap: 18px;
    padding: 25px;
    background: rgba(0, 19, 61, 0.4);
    border: 1px solid rgba(107, 155, 221, 0.2);
    border-radius: 14px;
    animation: ccFormSlideDown 0.3s ease;
}

@keyframes ccFormSlideDown {
    from { opacity: 0; transform: translateY(-10px); }
    to   { opacity: 1; transform: translateY(0); }
}

.cc-form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
    position: relative;
}

.cc-form-group label {
    font-size: 13px;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.75);
    letter-spacing: 0.3px;
}

.cc-input-wrap {
    position: relative;
}

.cc-form-group input,
.cc-input-wrap input {
    width: 100%;
    background: rgba(0, 19, 61, 0.6);
    border: 1px solid rgba(107, 155, 221, 0.2);
    border-radius: 10px;
    padding: 13px 16px;
    color: #ffffff;
    font-size: 15px;
    transition: all 0.3s ease;
    box-sizing: border-box;
}

.cc-form-group input:focus,
.cc-input-wrap input:focus {
    outline: none;
    border-color: #6B9BDD;
    background: rgba(59, 111, 204, 0.1);
    box-shadow: 0 0 0 3px rgba(107, 155, 221, 0.1);
}

.cc-form-group input::placeholder,
.cc-input-wrap input::placeholder {
    color: rgba(255, 255, 255, 0.25);
}

.cc-card-icons {
    position: absolute;
    right: 14px;
    top: 50%;
    transform: translateY(-50%);
    display: flex;
    gap: 6px;
    pointer-events: none;
}

.cc-card-icons i {
    font-size: 22px;
    color: rgba(255, 255, 255, 0.3);
}

.cc-input-wrap input {
    padding-right: 100px;
}

.cc-form-row {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 15px;
}

@media (max-width: 480px) {
    .cc-form-row {
        grid-template-columns: 1fr;
    }
}

/* Seção título dentro do formulário CC */
.cc-section-title {
    font-size: 12px;
    font-weight: 700;
    color: rgba(255,255,255,0.5);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 14px 0 6px;
    border-top: 1px solid rgba(255,255,255,0.07);
    display: flex;
    align-items: center;
    gap: 7px;
    margin: 0;
}
.cc-section-title:first-child {
    border-top: none;
    padding-top: 0;
}
.cc-section-title i {
    font-size: 11px;
    color: #6B9BDD;
}

/* Select de parcelas */
.cc-select {
    width: 100%;
    background: rgba(0,19,61,0.6);
    border: 1px solid rgba(107,155,221,0.2);
    border-radius: 10px;
    padding: 13px 40px 13px 16px;
    color: #ffffff;
    font-size: 15px;
    cursor: pointer;
    transition: border-color 0.3s;
    appearance: none;
    -webkit-appearance: none;
    background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='rgba(255,255,255,0.4)' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
}
.cc-select:focus {
    outline: none;
    border-color: #6B9BDD;
    box-shadow: 0 0 0 3px rgba(107,155,221,0.1);
}
.cc-select option { background: #001344; color: #fff; }

/* CEP wrap */
.cc-cep-wrap {
    display: flex;
    gap: 8px;
}
.cc-cep-wrap input { flex: 1; }
.cc-cep-btn {
    flex-shrink: 0;
    width: 46px;
    height: 46px;
    background: linear-gradient(135deg, #3B6FCC, #2E5AAA);
    border: none;
    border-radius: 10px;
    color: #fff;
    font-size: 14px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.2s;
    margin-top: auto;
}
.cc-cep-btn:hover { background: linear-gradient(135deg, #4A7FDD, #3D6ABB); }
.cc-cep-btn:disabled { opacity: 0.6; cursor: not-allowed; }

/* Campos de endereço */
.cc-address-section {
    display: flex;
    flex-direction: column;
    gap: 14px;
    animation: ccFormSlideDown 0.3s ease;
}
.cc-address-section .cc-form-row {
    grid-template-columns: 3fr 1fr;
}

/* Modal de processamento de cartão */
.card-modal-content {
    width: 480px;
    max-width: 93%;
    text-align: left;
    padding: 36px 36px 30px;
}

/* Responsive */
@media (max-width: 640px) {
    .card-modal-content { padding: 28px 20px 24px; }
}

/* Responsive */
@media (max-width: 968px) {
    .checkout-page {
        padding: 60px 20px;
    }
    
    .checkout-container {
        padding: 40px 30px;
    }
    
    .checkout-grid {
        grid-template-columns: 1fr;
        gap: 30px;
    }
    
    .order-summary-card {
        order: 2;
    }
    
    .payment-methods-card {
        order: 1;
    }
}

@media (max-width: 640px) {
    .checkout-page {
        padding: 40px 15px;
    }
    
    .checkout-container {
        padding: 30px 20px;
        border-radius: 16px;
    }
    
    .checkout-header-section {
        margin-bottom: 35px;
    }
    
    .checkout-header-section h1 {
        font-size: 28px;
    }
    
    .payment-methods-card,
    .order-summary-card {
        padding: 25px 20px;
    }
    
    .checkout-grid {
        gap: 25px;
    }
    
    .quantity-controls {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }
    
    .quantity-controls button {
        width: 28px;
        height: 28px;
        font-size: 16px;
    }
    
    .quantity-controls .qty-display {
        font-size: 16px;
        min-width: 25px;
    }
}

/* Modal de Aviso */
.payment-modal {
    display: none;
    position: fixed;
    z-index: 10000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.4);
    backdrop-filter: blur(8px);
    align-items: center;
    justify-content: center;
}

.payment-modal.active {
    display: flex;
}

.payment-modal-content {
    width: 500px;
    max-width: 90%;
    padding: 40px;
    border-radius: 8px;
    background: rgba(0, 17, 52, 0.55);
    backdrop-filter: blur(30px);
    -webkit-backdrop-filter: blur(30px);
    box-shadow: 0 0 40px rgba(0, 51, 153, 0.5), inset 0 0 0 1px rgba(255, 255, 255, 0.12);
    border: 1px solid rgba(255, 255, 255, 0.08);
    position: relative;
    text-align: center;
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

.payment-modal-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 25px;
    background: linear-gradient(135deg, rgba(251, 191, 36, 0.15), rgba(245, 158, 11, 0.15));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 8px 24px rgba(251, 191, 36, 0.2);
}

.payment-modal-icon i {
    font-size: 40px;
    color: #FCD34D;
}

.payment-modal-title {
    font-size: 24px;
    font-weight: 700;
    color: #ffffff;
    margin: 0 0 15px 0;
}

.payment-modal-message {
    font-size: 16px;
    color: rgba(255, 255, 255, 0.7);
    line-height: 1.6;
    margin: 0 0 30px 0;
}

.payment-modal-close {
    position: absolute;
    top: 20px;
    right: 20px;
    background: transparent;
    border: none;
    color: rgba(255, 255, 255, 0.6);
    font-size: 24px;
    cursor: pointer;
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    transition: all 0.3s ease;
}

.payment-modal-close:hover {
    background: rgba(255, 255, 255, 0.1);
    color: #ffffff;
}

.payment-modal-btn {
    background: linear-gradient(135deg, #3B6FCC 0%, #2E5AAA 100%);
    border: none;
    border-radius: 8px;
    color: #ffffff;
    font-size: 16px;
    font-weight: 600;
    padding: 14px 32px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 16px rgba(59, 111, 204, 0.3);
}

.payment-modal-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(59, 111, 204, 0.4);
}

@media (max-width: 640px) {
    .payment-modal-content {
        padding: 30px 24px;
    }
    
    .payment-modal-icon {
        width: 70px;
        height: 70px;
    }
    
    .payment-modal-icon i {
        font-size: 32px;
    }
    
    .payment-modal-title {
        font-size: 20px;
    }
    
    .payment-modal-message {
        font-size: 14px;
    }
}

/* ============================================================
   MODAL PIX
   ============================================================ */
.pix-modal-content {
    width: 520px;
    max-width: 93%;
    text-align: left;
    padding: 36px 36px 30px;
}

.pix-state { display: block; }

.pix-loading-wrap {
    text-align: center;
    padding: 30px 0 20px;
}
.pix-loading-wrap .fa-spinner {
    font-size: 44px;
    color: #6B9BDD;
    margin-bottom: 18px;
    display: block;
}
.pix-loading-wrap h2 {
    font-size: 20px;
    font-weight: 700;
    color: #fff;
    margin: 0 0 8px;
}
.pix-loading-wrap p {
    font-size: 14px;
    color: rgba(255,255,255,0.5);
    margin: 0;
}

.pix-ready-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 20px;
    padding-bottom: 18px;
    border-bottom: 1px solid rgba(255,255,255,0.08);
}
.pix-ready-icon {
    width: 42px;
    height: 42px;
    background: rgba(34,197,94,0.13);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.pix-ready-icon i {
    font-size: 20px;
    color: #5FD88A;
}
.pix-ready-header h2 {
    font-size: 19px;
    font-weight: 700;
    color: #fff;
    margin: 0 0 3px;
}
.pix-ready-header p {
    font-size: 12px;
    color: rgba(255,255,255,0.45);
    margin: 0;
}

.pix-info-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: rgba(0,19,61,0.6);
    border: 1px solid rgba(107,155,221,0.2);
    border-radius: 12px;
    padding: 14px 20px;
    margin-bottom: 18px;
}
.pix-info-block {}
.pix-info-label {
    font-size: 11px;
    font-weight: 600;
    color: rgba(255,255,255,0.45);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 4px;
    display: flex;
    align-items: center;
    gap: 5px;
}
.pix-info-value {
    font-size: 20px;
    font-weight: 700;
    color: #ffffff;
}
.pix-info-value.countdown {
    color: #FCD34D;
    font-variant-numeric: tabular-nums;
}
.pix-info-value.countdown.expiring {
    color: #f87171;
    animation: pix-blink 1s infinite;
}
@keyframes pix-blink { 0%,100%{opacity:1} 50%{opacity:0.5} }

.pix-qr-wrap {
    display: flex;
    justify-content: center;
    margin-bottom: 18px;
}
.pix-qr-wrap img {
    width: 190px;
    height: 190px;
    border: 3px solid rgba(107,155,221,0.25);
    border-radius: 14px;
    background: #fff;
    padding: 6px;
    box-sizing: border-box;
}

.pix-copiacola-label {
    font-size: 12px;
    font-weight: 600;
    color: rgba(255,255,255,0.5);
    text-transform: uppercase;
    letter-spacing: 0.4px;
    margin-bottom: 8px;
}
.pix-copiacola-box {
    display: flex;
    align-items: center;
    gap: 10px;
    background: rgba(0,19,61,0.55);
    border: 1px solid rgba(107,155,221,0.2);
    border-radius: 10px;
    padding: 11px 14px;
    margin-bottom: 18px;
}
.pix-copiacola-code {
    flex: 1;
    font-size: 11px;
    color: rgba(255,255,255,0.65);
    word-break: break-all;
    line-height: 1.45;
    font-family: monospace;
}
.pix-copy-btn {
    flex-shrink: 0;
    background: linear-gradient(135deg, #3B6FCC, #2E5AAA);
    border: none;
    border-radius: 8px;
    color: #fff;
    font-size: 12px;
    font-weight: 700;
    padding: 8px 13px;
    cursor: pointer;
    white-space: nowrap;
    transition: all 0.2s;
}
.pix-copy-btn:hover { background: linear-gradient(135deg, #4A7FDD, #3D6ABB); }
.pix-copy-btn.copied { background: linear-gradient(135deg, #10b981, #059669); }

.pix-waiting {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 13px;
    color: rgba(255,255,255,0.55);
    margin-top: 4px;
}
.pix-spin {
    width: 15px;
    height: 15px;
    border: 2px solid rgba(255,255,255,0.15);
    border-top-color: #5FD88A;
    border-radius: 50%;
    animation: pix-spin-anim 0.8s linear infinite;
    flex-shrink: 0;
}
@keyframes pix-spin-anim { to { transform: rotate(360deg); } }

.pix-success-wrap {
    text-align: center;
    padding: 20px 0;
}
.pix-success-wrap .pix-check-icon {
    font-size: 62px;
    margin-bottom: 18px;
    display: block;
}
.pix-success-wrap h2 {
    font-size: 24px;
    font-weight: 700;
    color: #5FD88A;
    margin: 0 0 10px;
}
.pix-success-wrap p {
    font-size: 15px;
    color: rgba(255,255,255,0.65);
    margin: 0 0 6px;
    line-height: 1.5;
}
.pix-success-sub {
    font-size: 13px;
    color: rgba(255,255,255,0.35);
    margin-bottom: 28px !important;
}

.pix-error-wrap {
    text-align: center;
    padding: 20px 0;
}
.pix-error-wrap i.fa-times-circle {
    font-size: 52px;
    color: #f87171;
    margin-bottom: 18px;
    display: block;
}
.pix-error-wrap h2 {
    font-size: 20px;
    font-weight: 700;
    color: #fff;
    margin: 0 0 12px;
}
.pix-error-wrap p {
    font-size: 14px;
    color: rgba(255,255,255,0.6);
    margin: 0 0 28px;
    line-height: 1.5;
}
</style>

<main class="main-content checkout-page">
    <div class="checkout-container">
        <div class="checkout-header-section">
            <h1>Complete Purchase</h1>
            <p>Complete your purchase quickly and securely</p>
        </div>
        
        <div class="checkout-grid">
            <!-- Payment Methods -->
            <div class="payment-methods-card">
                <div class="payment-header">
                    <h2>Payment Method</h2>
                    <p>Choose how you want to pay for your tickets</p>
                </div>
                
                <div class="payment-options">
                    <!-- PIX -->
                    <label class="payment-option selected">
                        <input type="radio" name="payment" value="pix" checked onchange="selectPaymentMethod(this)">
                        <div class="payment-icon">
                            <i class="fas fa-qrcode"></i>
                        </div>
                        <div class="payment-info">
                            <h3>PIX</h3>
                            <p>Instant approval via QR Code</p>
                        </div>
                        <span class="payment-recommended">Recommended</span>
                    </label>
                    
                    <!-- Cartão de Crédito -->
                    <label class="payment-option">
                        <input type="radio" name="payment" value="credit_card" onchange="selectPaymentMethod(this)">
                        <div class="payment-icon">
                            <i class="fas fa-credit-card"></i>
                        </div>
                        <div class="payment-info">
                            <h3>Credit Card</h3>
                            <p>Installment available</p>
                        </div>
                    </label>
                </div>
                
                <!-- Credit Card Form (Hidden by default) -->
                <div id="checkoutCreditCardForm" class="checkout-cc-form" style="display:none;">

                    <!-- DADOS DO CARTÃO -->
                    <div class="cc-section-title">
                        <i class="fas fa-credit-card"></i> Dados do Cartão
                    </div>
                    <div class="cc-form-group">
                        <label>Número do Cartão</label>
                        <div class="cc-input-wrap">
                            <input type="text" id="cc-cardNumber" maxlength="19" placeholder="1234 5678 9012 3456" autocomplete="cc-number">
                            <div class="cc-card-icons" id="ccBrandIcons">
                                <i class="fab fa-cc-visa" id="cc-brand-visa"></i>
                                <i class="fab fa-cc-mastercard" id="cc-brand-mastercard"></i>
                                <i class="fab fa-cc-amex" id="cc-brand-amex"></i>
                                <i class="fab fa-cc-elo" id="cc-brand-elo" style="display:none;"></i>
                            </div>
                        </div>
                    </div>
                    <div class="cc-form-row">
                        <div class="cc-form-group">
                            <label>Validade</label>
                            <input type="text" id="cc-expiryDate" maxlength="7" placeholder="MM/AAAA" autocomplete="cc-exp">
                        </div>
                        <div class="cc-form-group">
                            <label>CVV</label>
                            <input type="text" id="cc-cvv" maxlength="4" placeholder="123" autocomplete="cc-csc">
                        </div>
                    </div>
                    <div class="cc-form-group">
                        <label>Nome no Cartão</label>
                        <input type="text" id="cc-cardholderName" placeholder="NOME COMO NO CARTÃO" autocomplete="cc-name">
                    </div>

                    <!-- PARCELAMENTO -->
                    <div class="cc-section-title">
                        <i class="fas fa-list-ol"></i> Parcelamento
                    </div>
                    <div class="cc-form-group">
                        <label>Parcelas</label>
                        <select id="cc-installments" class="cc-select">
                            <option value="1">1× de R$<?php echo number_format($total, 2, ',', '.'); ?> sem juros</option>
                        </select>
                    </div>

                    <!-- DADOS PESSOAIS -->
                    <div class="cc-section-title">
                        <i class="fas fa-user"></i> Dados do Titular
                    </div>
                    <div class="cc-form-row">
                        <div class="cc-form-group">
                            <label>CPF</label>
                            <input type="text" id="cc-cpf" maxlength="14" placeholder="000.000.000-00">
                        </div>
                        <div class="cc-form-group">
                            <label>Telefone</label>
                            <input type="text" id="cc-phone" maxlength="15" placeholder="(11) 99999-9999">
                        </div>
                    </div>
                    <div class="cc-form-group">
                        <label>Data de Nascimento</label>
                        <input type="text" id="cc-birth" maxlength="10" placeholder="DD/MM/AAAA">
                    </div>

                    <!-- ENDEREÇO DE COBRANÇA -->
                    <div class="cc-section-title">
                        <i class="fas fa-map-marker-alt"></i> Endereço de Cobrança
                    </div>
                    <div class="cc-form-group">
                        <label>CEP</label>
                        <div class="cc-cep-wrap">
                            <input type="text" id="cc-zipcode" maxlength="9" placeholder="00000-000">
                            <button type="button" class="cc-cep-btn" id="ccCepBtn" onclick="lookupCEP()" title="Buscar CEP">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    <div id="ccAddressFields" class="cc-address-section" style="display:none;">
                        <div class="cc-form-row">
                            <div class="cc-form-group" style="grid-column:span 1;">
                                <label>Rua</label>
                                <input type="text" id="cc-street" placeholder="Nome da rua">
                            </div>
                            <div class="cc-form-group">
                                <label>Número</label>
                                <input type="text" id="cc-addr-number" placeholder="123">
                            </div>
                        </div>
                        <div class="cc-form-group">
                            <label>Bairro</label>
                            <input type="text" id="cc-neighborhood" placeholder="Bairro">
                        </div>
                        <div class="cc-form-row">
                            <div class="cc-form-group">
                                <label>Cidade</label>
                                <input type="text" id="cc-city" placeholder="Cidade">
                            </div>
                            <div class="cc-form-group">
                                <label>UF</label>
                                <input type="text" id="cc-state" maxlength="2" placeholder="SP" style="text-transform:uppercase;">
                            </div>
                        </div>
                    </div>

                </div>

                <div class="checkout-actions">
                    <button class="btn-complete-purchase" onclick="completePurchase()">
                        <i class="fas fa-lock"></i>
                        <span>Complete Purchase - R$<?php echo number_format($total, 2, ',', '.'); ?></span>
                    </button>
                    <button class="btn-back" onclick="window.location.href='<?php echo site_url('/planos/'); ?>'">
                        Back to Plans
                    </button>
                </div>
                
                <div class="secure-badge">
                    <i class="fas fa-shield-alt"></i>
                    <span>Payment 100% secure and encrypted</span>
                </div>
            </div>
            
            <!-- Order Summary -->
            <div class="order-summary-card">
                <div class="summary-header">
                    <div class="summary-icon">
                        <i class="fas fa-ticket-alt"></i>
                    </div>
                    <h3 class="summary-title">Order Summary</h3>
                </div>
                
                <div class="order-item">
                    <div class="item-details">
                        <h4>Entry Tickets</h4>
                        <p id="ticket-description"><?php echo $quantity; ?> ticket<?php echo $quantity > 1 ? 's' : ''; ?> × R$<?php echo number_format($pricePerTicket, 2, ',', '.'); ?></p>
                        <div class="quantity-controls">
                            <span>Quantity:</span>
                            <div class="qty-controls">
                                <button type="button" id="decreaseQty" onclick="updateQuantity(-1)" <?php echo $quantity <= 1 ? 'disabled' : ''; ?>>−</button>
                                <span class="qty-display" id="qtyDisplay"><?php echo $quantity; ?></span>
                                <button type="button" id="increaseQty" onclick="updateQuantity(1)">+</button>
                            </div>
                        </div>
                    </div>
                    <div class="item-price" id="subtotal-price">
                        R$<?php echo number_format($subtotal, 2, ',', '.'); ?>
                    </div>
                </div>
                
                <div class="order-totals">
                    <div class="total-row">
                        <span class="total-label">Subtotal</span>
                        <span class="total-value" id="subtotal-value">R$<?php echo number_format($subtotal, 2, ',', '.'); ?></span>
                    </div>
                    <div class="total-row">
                        <span class="total-label">Fees</span>
                        <span class="total-value" id="tax-value">R$<?php echo number_format($tax, 2, ',', '.'); ?></span>
                    </div>
                    <div class="total-row final">
                        <span class="total-label">Total</span>
                        <span class="total-value" id="total-value">R$<?php echo number_format($total, 2, ',', '.'); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Modal Processamento Cartão -->
<div id="cardModal" class="payment-modal">
    <div class="payment-modal-content card-modal-content">
        <button class="payment-modal-close" onclick="closeCardModal()">
            <i class="fas fa-times"></i>
        </button>

        <!-- Estado: Processando -->
        <div id="cardStateLoading" class="pix-state">
            <div class="pix-loading-wrap">
                <i class="fas fa-spinner fa-spin"></i>
                <h2>Processando Pagamento</h2>
                <p>Por favor, aguarde um momento...</p>
            </div>
        </div>

        <!-- Estado: Sucesso -->
        <div id="cardStateSuccess" class="pix-state" style="display:none;">
            <div class="pix-success-wrap">
                <span class="pix-check-icon">&#9989;</span>
                <h2>Pagamento Aprovado!</h2>
                <p>Seu pagamento foi processado com sucesso.</p>
                <p class="pix-success-sub" id="cardSuccessInfo"></p>
                <button class="payment-modal-btn" onclick="window.location.href='<?php echo site_url(); ?>'">
                    Ir para o Início
                </button>
            </div>
        </div>

        <!-- Estado: Erro -->
        <div id="cardStateError" class="pix-state" style="display:none;">
            <div class="pix-error-wrap">
                <i class="fas fa-times-circle"></i>
                <h2>Erro no Pagamento</h2>
                <p id="cardErrorMsg">Ocorreu um erro. Tente novamente.</p>
                <button class="payment-modal-btn" onclick="closeCardModal()">Tentar Novamente</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal PIX -->
<div id="pixModal" class="payment-modal">
    <div class="payment-modal-content pix-modal-content">
        <button class="payment-modal-close" onclick="closePixModal()">
            <i class="fas fa-times"></i>
        </button>

        <!-- Estado: Carregando -->
        <div id="pixStateLoading" class="pix-state">
            <div class="pix-loading-wrap">
                <i class="fas fa-spinner fa-spin"></i>
                <h2>Generating PIX Charge</h2>
                <p>Please wait a moment...</p>
            </div>
        </div>

        <!-- Estado: QR Code pronto -->
        <div id="pixStateReady" class="pix-state" style="display:none;">
            <div class="pix-ready-header">
                <div class="pix-ready-icon"><i class="fas fa-qrcode"></i></div>
                <div>
                    <h2>PIX Payment</h2>
                    <p>Scan the QR Code or use Copy &amp; Paste</p>
                </div>
            </div>

            <!-- Valor e timer -->
            <div class="pix-info-bar">
                <div class="pix-info-block">
                    <div class="pix-info-label">Amount</div>
                    <div class="pix-info-value" id="pixAmount">R$0,00</div>
                </div>
                <div class="pix-info-block" style="text-align:right;">
                    <div class="pix-info-label"><i class="fas fa-clock"></i> Expires in</div>
                    <div class="pix-info-value countdown" id="pixCountdown">60:00</div>
                </div>
            </div>

            <!-- QR Code -->
            <div class="pix-qr-wrap">
                <img id="pixQrCodeImg" src="" alt="QR Code PIX">
            </div>

            <!-- Copia e Cola -->
            <div class="pix-copiacola-label">PIX Copy &amp; Paste</div>
            <div class="pix-copiacola-box">
                <span id="pixCopiaColaText" class="pix-copiacola-code"></span>
                <button class="pix-copy-btn" id="pixCopyBtn" onclick="copyPixCode()">
                    <i class="fas fa-copy"></i> Copy
                </button>
            </div>

            <!-- Aguardando pagamento -->
            <div class="pix-waiting" id="pixWaiting">
                <div class="pix-spin"></div>
                <span>Waiting for payment...</span>
            </div>
        </div>

        <!-- Estado: Sucesso -->
        <div id="pixStateSuccess" class="pix-state" style="display:none;">
            <div class="pix-success-wrap">
                <span class="pix-check-icon">&#9989;</span>
                <h2>Payment Confirmed!</h2>
                <p>Your PIX payment was received successfully.</p>
                <p class="pix-success-sub" id="pixSuccessInfo"></p>
                <button class="payment-modal-btn" onclick="window.location.href='<?php echo site_url(); ?>'">
                    Go to Home
                </button>
            </div>
        </div>

        <!-- Estado: Erro -->
        <div id="pixStateError" class="pix-state" style="display:none;">
            <div class="pix-error-wrap">
                <i class="fas fa-times-circle"></i>
                <h2>Payment Error</h2>
                <p id="pixErrorMsg">An error occurred. Please try again.</p>
                <button class="payment-modal-btn" onclick="closePixModal()">Try Again</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/gh/efipay/js-payment-token-efi/dist/payment-token-efi-umd.min.js"></script>
<script>
// Constantes
const TICKET_PRICE = <?php echo $pricePerTicket; ?>;
const TAX_RATE = 0; // 0% de taxa

// Dados do pedido
const orderData = {
    quantity: <?php echo $quantity; ?>,
    total: <?php echo $total; ?>,
    userId: <?php echo $userId ? $userId : 'null'; ?>,
    paymentMethod: 'pix'
};

// Atualizar quantidade de tickets
function updateQuantity(change) {
    const newQuantity = orderData.quantity + change;
    
    // Validar mínimo de 1 ticket
    if (newQuantity < 1) return;
    
    // Atualizar quantidade
    orderData.quantity = newQuantity;
    
    // Calcular novos valores
    const subtotal = orderData.quantity * TICKET_PRICE;
    const tax = subtotal * TAX_RATE;
    const total = subtotal + tax;
    orderData.total = total;
    
    // Atualizar interface
    document.getElementById('qtyDisplay').textContent = orderData.quantity;
    document.getElementById('ticket-description').innerHTML = 
        orderData.quantity + ' ticket' + (orderData.quantity > 1 ? 's' : '') + 
        ' × R$' + TICKET_PRICE.toFixed(2).replace('.', ',');
    
    document.getElementById('subtotal-price').textContent = 
        'R$' + subtotal.toFixed(2).replace('.', ',');
    document.getElementById('subtotal-value').textContent = 
        'R$' + subtotal.toFixed(2).replace('.', ',');
    document.getElementById('tax-value').textContent = 
        'R$' + tax.toFixed(2).replace('.', ',');
    document.getElementById('total-value').textContent = 
        'R$' + total.toFixed(2).replace('.', ',');
    
    // Atualizar botão de finalizar compra
    const btn = document.querySelector('.btn-complete-purchase span');
    if (btn) {
        btn.textContent = 'Complete Purchase - R$' + total.toFixed(2).replace('.', ',');
    }
    
    // Desabilitar botão de diminuir se for 1
    document.getElementById('decreaseQty').disabled = (orderData.quantity <= 1);

    updateInstallmentOptions(total);

    console.log('Quantidade atualizada:', orderData.quantity, 'Total:', total);
}

// Selecionar método de pagamento
function selectPaymentMethod(radio) {
    document.querySelectorAll('.payment-option').forEach(option => {
        option.classList.remove('selected');
    });
    
    radio.closest('.payment-option').classList.add('selected');
    orderData.paymentMethod = radio.value;

    const ccForm = document.getElementById('checkoutCreditCardForm');
    if (radio.value === 'credit_card') {
        ccForm.style.display = 'flex';
    } else {
        ccForm.style.display = 'none';
    }
}

// Máscaras do formulário de cartão
document.addEventListener('DOMContentLoaded', function () {
    // Número do cartão — grupos de 4 + detecção de bandeira
    var cardNumberInput = document.getElementById('cc-cardNumber');
    if (cardNumberInput) {
        cardNumberInput.addEventListener('input', function (e) {
            var value = e.target.value.replace(/\D/g, '');
            var formatted = value.match(/.{1,4}/g);
            e.target.value = formatted ? formatted.join(' ') : value;
            var brand = detectCardBrand(e.target.value);
            highlightCardBrand(brand);
            updateInstallmentOptions(orderData.total);
        });
    }

    // Validade — MM/AAAA
    var expiryInput = document.getElementById('cc-expiryDate');
    if (expiryInput) {
        expiryInput.addEventListener('input', function (e) {
            var value = e.target.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 6);
            }
            e.target.value = value;
        });
    }

    // CVV — somente números
    var cvvInput = document.getElementById('cc-cvv');
    if (cvvInput) {
        cvvInput.addEventListener('input', function (e) {
            e.target.value = e.target.value.replace(/\D/g, '');
        });
    }

    // CPF — 000.000.000-00
    var cpfInput = document.getElementById('cc-cpf');
    if (cpfInput) {
        cpfInput.addEventListener('input', function (e) {
            var v = e.target.value.replace(/\D/g, '');
            v = v.replace(/(\d{3})(\d)/, '$1.$2');
            v = v.replace(/(\d{3})(\d)/, '$1.$2');
            v = v.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            e.target.value = v;
        });
    }

    // Telefone — (DDD) + 8/9 dígitos
    var phoneInput = document.getElementById('cc-phone');
    if (phoneInput) {
        phoneInput.addEventListener('input', function (e) {
            var v = e.target.value.replace(/\D/g, '');
            if (v.length <= 10) {
                v = v.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
            } else {
                v = v.replace(/(\d{2})(\d{5})(\d{0,4})/, '($1) $2-$3');
            }
            e.target.value = v;
        });
    }

    // Data de nascimento — DD/MM/AAAA
    var birthInput = document.getElementById('cc-birth');
    if (birthInput) {
        birthInput.addEventListener('input', function (e) {
            var v = e.target.value.replace(/\D/g, '');
            if (v.length > 4) v = v.substring(0,2) + '/' + v.substring(2,4) + '/' + v.substring(4,8);
            else if (v.length > 2) v = v.substring(0,2) + '/' + v.substring(2);
            e.target.value = v;
        });
    }

    // CEP — 00000-000
    var cepInput = document.getElementById('cc-zipcode');
    if (cepInput) {
        cepInput.addEventListener('input', function (e) {
            var v = e.target.value.replace(/\D/g, '');
            if (v.length > 5) v = v.substring(0,5) + '-' + v.substring(5,8);
            e.target.value = v;
        });
        cepInput.addEventListener('blur', function (e) {
            if (e.target.value.replace(/\D/g,'').length === 8) lookupCEP();
        });
    }

    // UF em maiúsculo
    var stateInput = document.getElementById('cc-state');
    if (stateInput) {
        stateInput.addEventListener('input', function(e) {
            e.target.value = e.target.value.toUpperCase().replace(/[^A-Z]/g,'');
        });
    }
});


// Abrir modal (legado — mantém compat)
function openPaymentModal() {}
function closePaymentModal() {}

// Fechar modais ao clicar fora
window.addEventListener('click', function(event) {
    if (event.target === document.getElementById('cardModal')) {
        closeCardModal();
    }
    if (event.target === document.getElementById('pixModal')) {
        closePixModal();
    }
});

// Toast notification
function showCheckoutToast(message, type) {
    type = type || 'info';

    var existing = document.querySelector('.checkout-toast');
    if (existing) existing.remove();

    var icons   = { success: '✓', error: '✕', info: 'ℹ', warning: '⚠' };
    var colors  = { success: '#10b981', error: '#ef4444', info: '#3b82f6', warning: '#f59e0b' };
    var icon    = icons[type]  || icons.info;
    var bg      = colors[type] || colors.info;

    if (!document.getElementById('checkout-toast-styles')) {
        var s = document.createElement('style');
        s.id = 'checkout-toast-styles';
        s.textContent = [
            '@keyframes checkoutToastIn{from{transform:translateX(110%);opacity:0}to{transform:translateX(0);opacity:1}}',
            '@keyframes checkoutToastOut{from{transform:translateX(0);opacity:1}to{transform:translateX(110%);opacity:0}}'
        ].join('');
        document.head.appendChild(s);
    }

    var wrap = document.createElement('div');
    wrap.className = 'checkout-toast';
    wrap.style.cssText = [
        'position:fixed', 'top:24px', 'right:24px', 'z-index:99999',
        'background:' + bg, 'color:#fff',
        'padding:14px 20px', 'border-radius:10px',
        'box-shadow:0 6px 20px rgba(0,0,0,.35)',
        'display:flex', 'align-items:center', 'gap:12px',
        'max-width:380px', 'min-width:240px',
        'font-size:15px', 'font-weight:500', 'line-height:1.4',
        'animation:checkoutToastIn .3s ease-out'
    ].join(';');

    wrap.innerHTML = '<span style="font-size:18px;font-weight:700;flex-shrink:0">' + icon + '</span>'
        + '<span style="flex:1">' + message + '</span>'
        + '<button onclick="this.parentElement.remove()" style="'
        +   'background:transparent;border:none;color:rgba(255,255,255,.8);'
        +   'font-size:22px;cursor:pointer;padding:0;line-height:1;flex-shrink:0'
        + '">×</button>';

    document.body.appendChild(wrap);

    setTimeout(function () {
        if (!wrap.parentElement) return;
        wrap.style.animation = 'checkoutToastOut .3s ease-in forwards';
        setTimeout(function () { wrap.remove(); }, 300);
    }, 5000);
}

// Completar compra
function completePurchase() {
    if (orderData.paymentMethod === 'credit_card') {
        var cardNumber = document.getElementById('cc-cardNumber').value.replace(/\s/g, '');
        var expiry    = document.getElementById('cc-expiryDate').value;
        var cvv       = document.getElementById('cc-cvv').value;
        var holder    = document.getElementById('cc-cardholderName').value.trim();

        if (!cardNumber || cardNumber.length < 13) {
            showCheckoutToast('Please enter a valid card number.', 'error');
            document.getElementById('cc-cardNumber').focus();
            return;
        }
        if (!expiry || expiry.length < 7) {
            showCheckoutToast('Please enter a valid expiry date (MM/YYYY).', 'error');
            document.getElementById('cc-expiryDate').focus();
            return;
        }
        if (!cvv || cvv.length < 3) {
            showCheckoutToast('Please enter a valid CVV.', 'error');
            document.getElementById('cc-cvv').focus();
            return;
        }
        if (!holder) {
            showCheckoutToast('Please enter the cardholder name.', 'error');
            document.getElementById('cc-cardholderName').focus();
            return;
        }
        // Cartão — iniciar pagamento real
        initCardPayment();
        return;
    }

    // PIX
    initPixPayment();
}

/* ============================================================
   PIX FUNCTIONS
   ============================================================ */
var _pixCountdownInterval = null;
var _pixPollingInterval   = null;

function openPixModal() {
    document.getElementById('pixModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closePixModal() {
    document.getElementById('pixModal').classList.remove('active');
    document.body.style.overflow = '';
    if (_pixCountdownInterval) { clearInterval(_pixCountdownInterval); _pixCountdownInterval = null; }
    if (_pixPollingInterval)   { clearInterval(_pixPollingInterval);   _pixPollingInterval   = null; }
}

function showPixState(state) {
    ['pixStateLoading', 'pixStateReady', 'pixStateSuccess', 'pixStateError'].forEach(function(id) {
        document.getElementById(id).style.display = 'none';
    });
    document.getElementById(state).style.display = 'block';
}

function copyPixCode() {
    var text = document.getElementById('pixCopiaColaText').innerText;
    navigator.clipboard.writeText(text).then(function() {
        var btn = document.getElementById('pixCopyBtn');
        btn.classList.add('copied');
        btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
        setTimeout(function() {
            btn.classList.remove('copied');
            btn.innerHTML = '<i class="fas fa-copy"></i> Copy';
        }, 3000);
    });
}

function startPixCountdown(seconds) {
    if (_pixCountdownInterval) clearInterval(_pixCountdownInterval);
    var remaining = seconds;
    var el = document.getElementById('pixCountdown');

    function tick() {
        var m = Math.floor(remaining / 60);
        var s = remaining % 60;
        el.textContent = String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
        if (remaining <= 300) el.classList.add('expiring');
        if (remaining <= 0) {
            clearInterval(_pixCountdownInterval);
            clearInterval(_pixPollingInterval);
            el.textContent = '00:00';
            document.getElementById('pixWaiting').innerHTML =
                '<i class="fas fa-exclamation-triangle" style="color:#FCD34D;margin-right:8px;"></i>' +
                '<span>PIX expired. Close and try again.</span>';
            return;
        }
        remaining--;
    }
    tick();
    _pixCountdownInterval = setInterval(tick, 1000);
}

function startPixPolling(txid) {
    if (_pixPollingInterval) clearInterval(_pixPollingInterval);
    var attempts    = 0;
    var maxAttempts = 120; // 6 minutos a cada 3s

    _pixPollingInterval = setInterval(function() {
        attempts++;
        if (attempts > maxAttempts) {
            clearInterval(_pixPollingInterval);
            return;
        }
        fetch('<?php echo site_url(); ?>/api/consultar_status.php?txid=' + encodeURIComponent(txid))
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.status === 'CONCLUIDA') {
                    clearInterval(_pixPollingInterval);
                    clearInterval(_pixCountdownInterval);
                    document.getElementById('pixSuccessInfo').textContent =
                        orderData.quantity + ' ticket' + (orderData.quantity > 1 ? 's' : '') +
                        ' added to your account.';
                    showPixState('pixStateSuccess');
                }
            })
            .catch(function() {});
    }, 3000);
}

async function initPixPayment() {
    var btn = document.querySelector('.btn-complete-purchase');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Processing...</span>';

    openPixModal();
    showPixState('pixStateLoading');

    try {
        var formData = new FormData();
        formData.append('qty',   orderData.quantity);
        formData.append('token', '<?php echo $token; ?>');

        var response = await fetch('<?php echo site_url(); ?>/api/criar-pix.php', {
            method: 'POST',
            body: formData
        });

        var data = await response.json();

        if (!data.success) {
            throw new Error(data.error || 'Failed to generate PIX charge.');
        }

        // Preenche modal
        document.getElementById('pixAmount').textContent      = 'R$' + data.valor;
        document.getElementById('pixQrCodeImg').src           = data.qrcode_img;
        document.getElementById('pixCopiaColaText').textContent = data.pix_copia_cola;

        showPixState('pixStateReady');
        startPixCountdown(data.expiracao);
        startPixPolling(data.txid);

    } catch (err) {
        document.getElementById('pixErrorMsg').textContent = err.message || 'An error occurred.';
        showPixState('pixStateError');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-lock"></i> <span>Complete Purchase - R$' +
            orderData.total.toFixed(2).replace('.', ',') + '</span>';
    }
}

/* ============================================================
   CARTÃO DE CRÉDITO — funções
   ============================================================ */

// Identificador de conta EFI Pay
var EFI_PAYEE_CODE = '<?php echo addslashes($payeeCode); ?>';

// Detectar bandeira pelo número do cartão
function detectCardBrand(number) {
    number = number.replace(/\s/g, '');
    if (/^4/.test(number))                                           return 'visa';
    if (/^(5[1-5]|2(2[2-9]|[3-6]|7[01]))/.test(number))           return 'mastercard';
    if (/^3[47]/.test(number))                                       return 'amex';
    if (/^(636368|438935|504175|451416|636297|5067|4576|4011)/.test(number)) return 'elo';
    if (/^3(0[0-5]|[68])/.test(number))                             return 'diners';
    return 'unknown';
}

// Destacar ícone da bandeira
function highlightCardBrand(brand) {
    ['visa','mastercard','amex'].forEach(function(b) {
        var el = document.getElementById('cc-brand-' + b);
        if (el) el.style.color = (b === brand) ? '#ffffff' : 'rgba(255,255,255,0.25)';
    });
}

// Atualizar opções de parcelamento
function updateInstallmentOptions(total) {
    var sel = document.getElementById('cc-installments');
    if (!sel) return;
    sel.innerHTML = '';
    for (var i = 1; i <= 12; i++) {
        var value = (total / i).toFixed(2).replace('.', ',');
        var opt   = document.createElement('option');
        opt.value = i;
        opt.textContent = i + '\u00d7 de R$' + value + (i === 1 ? ' sem juros' : '');
        sel.appendChild(opt);
    }
}

// Buscar endereço pelo CEP (ViaCEP)
async function lookupCEP() {
    var cep = document.getElementById('cc-zipcode').value.replace(/\D/g, '');
    if (cep.length !== 8) {
        showCheckoutToast('Informe um CEP válido com 8 dígitos.', 'error');
        return;
    }
    var btn = document.getElementById('ccCepBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    try {
        var resp = await fetch('https://viacep.com.br/ws/' + encodeURIComponent(cep) + '/json/');
        var data = await resp.json();
        if (data.erro) {
            showCheckoutToast('CEP não encontrado. Preencha o endereço manualmente.', 'warning');
            document.getElementById('ccAddressFields').style.display = 'flex';
            return;
        }
        document.getElementById('cc-street').value       = data.logradouro || '';
        document.getElementById('cc-neighborhood').value = data.bairro     || '';
        document.getElementById('cc-city').value         = data.localidade || '';
        document.getElementById('cc-state').value        = data.uf         || '';
        document.getElementById('ccAddressFields').style.display = 'flex';
        document.getElementById('cc-addr-number').focus();
    } catch (e) {
        showCheckoutToast('Erro ao buscar CEP. Preencha o endereço manualmente.', 'warning');
        document.getElementById('ccAddressFields').style.display = 'flex';
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-search"></i>';
    }
}

// Funções do modal de cartão
function openCardModal() {
    document.getElementById('cardModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeCardModal() {
    document.getElementById('cardModal').classList.remove('active');
    document.body.style.overflow = '';
}

function showCardState(state) {
    ['cardStateLoading', 'cardStateSuccess', 'cardStateError'].forEach(function(id) {
        document.getElementById(id).style.display = 'none';
    });
    document.getElementById(state).style.display = 'block';
}

// Processar pagamento com cartão
async function initCardPayment() {
    // Coletar dados do formulário
    var cardNumber = document.getElementById('cc-cardNumber').value.replace(/\s/g, '');
    var expiry     = document.getElementById('cc-expiryDate').value;
    var cvv        = document.getElementById('cc-cvv').value;
    var holder     = document.getElementById('cc-cardholderName').value.trim();
    var installments  = parseInt(document.getElementById('cc-installments').value) || 1;
    var cpf        = document.getElementById('cc-cpf').value.replace(/\D/g, '');
    var phone      = document.getElementById('cc-phone').value.replace(/\D/g, '');
    var birth      = document.getElementById('cc-birth').value;          // DD/MM/AAAA
    var zipcode    = document.getElementById('cc-zipcode').value.replace(/\D/g, '');
    var street     = document.getElementById('cc-street').value.trim();
    var addrNum    = document.getElementById('cc-addr-number').value.trim();
    var neigh      = document.getElementById('cc-neighborhood').value.trim();
    var city       = document.getElementById('cc-city').value.trim();
    var state      = document.getElementById('cc-state').value.trim().toUpperCase();

    // Validações rápidas no front-end
    if (cardNumber.length < 13) {
        showCheckoutToast('Número do cartão inválido.', 'error');
        document.getElementById('cc-cardNumber').focus(); return;
    }
    if (expiry.length < 7) {
        showCheckoutToast('Validade inválida (MM/AAAA).', 'error');
        document.getElementById('cc-expiryDate').focus(); return;
    }
    if (cvv.length < 3) {
        showCheckoutToast('CVV inválido.', 'error');
        document.getElementById('cc-cvv').focus(); return;
    }
    if (!holder) {
        showCheckoutToast('Informe o nome como aparece no cartão.', 'error');
        document.getElementById('cc-cardholderName').focus(); return;
    }
    if (cpf.length !== 11) {
        showCheckoutToast('CPF inválido — informe 11 dígitos.', 'error');
        document.getElementById('cc-cpf').focus(); return;
    }
    if (phone.length < 10) {
        showCheckoutToast('Telefone inválido.', 'error');
        document.getElementById('cc-phone').focus(); return;
    }
    if (birth.length < 10) {
        showCheckoutToast('Data de nascimento inválida (DD/MM/AAAA).', 'error');
        document.getElementById('cc-birth').focus(); return;
    }
    if (zipcode.length !== 8) {
        showCheckoutToast('CEP inválido.', 'error');
        document.getElementById('cc-zipcode').focus(); return;
    }
    if (!street || !addrNum || !neigh || !city || state.length !== 2) {
        showCheckoutToast('Preencha o endereço de cobrança completo.', 'error');
        document.getElementById('ccAddressFields').style.display = 'flex'; return;
    }

    // Converter data: DD/MM/AAAA → AAAA-MM-DD
    var bp = birth.split('/');
    var birthISO = (bp.length === 3) ? bp[2] + '-' + bp[1].padStart(2,'0') + '-' + bp[0].padStart(2,'0') : '';

    // Parsear validade
    var ep  = expiry.split('/');
    var expMonth = ep[0] || '';
    var expYear  = ep[1] || '';

    var brand = detectCardBrand(cardNumber);

    var btn = document.querySelector('.btn-complete-purchase');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Processando...</span>';

    openCardModal();
    showCardState('cardStateLoading');

    try {
        // Verificar se biblioteca EFI Pay foi carregada
        if (typeof EfiPay === 'undefined') {
            throw new Error('Biblioteca de tokenização não carregada. Recarregue a página e tente novamente.');
        }

        // Tokenizar dados do cartão via EFI Pay JS
        var tokenResult = await EfiPay.CreditCard
            .setAccount(EFI_PAYEE_CODE)
            .setEnvironment('production')
            .setCreditCardData({
                brand:           brand,
                number:          cardNumber,
                cvv:             cvv,
                expirationMonth: expMonth,
                expirationYear:  expYear,
                reuse:           false
            })
            .getPaymentToken();

        var payment_token = tokenResult.payment_token;

        // Enviar ao backend
        var formData = new FormData();
        formData.append('token',         '<?php echo $token; ?>');
        formData.append('payment_token', payment_token);
        formData.append('qty',           orderData.quantity);
        formData.append('installments',  installments);
        formData.append('name',          holder);
        formData.append('cpf',           cpf);
        formData.append('phone',         phone);
        formData.append('birth',         birthISO);
        formData.append('zipcode',       zipcode);
        formData.append('street',        street);
        formData.append('number',        addrNum);
        formData.append('neighborhood',  neigh);
        formData.append('city',          city);
        formData.append('state',         state);

        var response = await fetch('<?php echo site_url(); ?>/api/cobrar-cartao.php', {
            method: 'POST',
            body:   formData
        });

        var data = await response.json();

        if (!data.success) {
            throw new Error(data.error || 'Pagamento não aprovado.');
        }

        document.getElementById('cardSuccessInfo').textContent =
            data.qty + ' ticket' + (data.qty > 1 ? 's' : '') +
            ' adicionado' + (data.qty > 1 ? 's' : '') + ' à sua conta.';
        showCardState('cardStateSuccess');

    } catch (err) {
        document.getElementById('cardErrorMsg').textContent =
            err.message || 'Ocorreu um erro ao processar o pagamento.';
        showCardState('cardStateError');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-lock"></i> <span>Complete Purchase - R$' +
            orderData.total.toFixed(2).replace('.', ',') + '</span>';
    }
}
</script>

<?php get_footer(); ?>


// Funções para controlar quantidade de tickets (escopo global)
let ticketQuantity = 1;
const ticketPrice = 25;

function increaseQuantity() {
    ticketQuantity++;
    updateTicketDisplay();
}

function decreaseQuantity() {
    if (ticketQuantity > 1) {
        ticketQuantity--;
        updateTicketDisplay();
    }
}

function updateTicketDisplay() {
    const quantityDisplay = document.querySelector('.quantity-display');
    const purchaseBtn = document.querySelector('.btn-purchase');
    
    if (quantityDisplay) {
        quantityDisplay.value = ticketQuantity;
    }
    
    if (purchaseBtn) {
        const totalPrice = ticketQuantity * ticketPrice;
        purchaseBtn.textContent = `Purchase Tickets for R$${totalPrice}`;
    }
}

// Gerar token hexadecimal de 16 caracteres
function generateToken() {
    const chars = '0123456789abcdef';
    let token = '';
    for (let i = 0; i < 16; i++) {
        token += chars[Math.floor(Math.random() * chars.length)];
    }
    return token;
}

// Função para redirecionar ao checkout
function proceedToCheckout() {
    // Gerar token único para o pedido
    const orderToken = generateToken();
    
    // Criar URL com parâmetros de quantidade e token
    const checkoutUrl = `/checkout/?qty=${ticketQuantity}&token=${orderToken}`;
    
    console.log('Token gerado:', orderToken);
    console.log('Quantidade:', ticketQuantity);
    console.log('Redirecionando para:', checkoutUrl);
    
    // Redirecionar para página de checkout
    window.location.href = checkoutUrl;
}

// Listener removido - usar onclick="handlePurchaseTickets(event)" no HTML em vez disso
// O handlePurchaseTickets() no page-planos.php cuida da autenticação

// Função para toggle de FAQ
function toggleFAQ(button) {
    const faqItem = button.parentElement;
    const isActive = faqItem.classList.contains('active');
    
    // Fechar todos os outros itens
    document.querySelectorAll('.faq-item').forEach(item => {
        item.classList.remove('active');
    });
    
    // Abrir o item clicado se não estava ativo
    if (!isActive) {
        faqItem.classList.add('active');
    }
}

// Funções para o modal de confirmação de plano
let selectedPlan = '';

function openPlanModal(planName) {
    selectedPlan = planName;
    const modal = document.getElementById('planModal');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function closePlanModal() {
    const modal = document.getElementById('planModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
    selectedPlan = '';
}

function confirmPlanChange() {
    console.log('Plan confirmed:', selectedPlan);
    // Aqui você pode adicionar a lógica para processar a mudança de plano
    // Por exemplo, redirecionar para página de pagamento ou fazer uma requisição API
    
    closePlanModal();
    
    // Exemplo: mostrar mensagem de sucesso
    alert(`You have successfully selected the ${selectedPlan} plan!`);
}

// Fechar modal ao clicar fora dele
window.addEventListener('click', function(event) {
    const modal = document.getElementById('planModal');
    if (event.target === modal) {
        closePlanModal();
    }
});

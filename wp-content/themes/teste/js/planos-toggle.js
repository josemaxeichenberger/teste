/**
 * Planos Toggle - Monthly/Yearly Functionality
 * Apenas alterna estados quando clica nos botões
 * Todos os dados estão no HTML (page-planos.php)
 */

let currentPeriod = 'yearly';

document.addEventListener('DOMContentLoaded', function() {
    initToggleButtons();
    showPeriod('yearly'); // Mostrar Yearly por padrão
});

function initToggleButtons() {
    const yearlyBtn = document.getElementById('yearlyBtn');
    const monthlyBtn = document.getElementById('monthlyBtn');
    
    if (yearlyBtn) {
        yearlyBtn.addEventListener('click', function() {
            setPeriod('yearly');
        });
    }
    
    if (monthlyBtn) {
        monthlyBtn.addEventListener('click', function() {
            setPeriod('monthly');
        });
    }
}

function setPeriod(period) {
    currentPeriod = period;
    
    // Atualizar botões ativos
    const yearlyBtn = document.getElementById('yearlyBtn');
    const monthlyBtn = document.getElementById('monthlyBtn');
    
    if (yearlyBtn && monthlyBtn) {
        if (period === 'yearly') {
            yearlyBtn.classList.add('active');
            monthlyBtn.classList.remove('active');
        } else {
            monthlyBtn.classList.add('active');
            yearlyBtn.classList.remove('active');
        }
    }
    
    // Mostrar/esconder planos baseado no período
    showPeriod(period);
}

function showPeriod(period) {
    // Selecionar todos os planos
    const allCards = document.querySelectorAll('[data-period]');
    
    allCards.forEach(card => {
        if (card.getAttribute('data-period') === period) {
            card.style.display = 'block';
            card.style.animation = 'fadeInCard 0.3s ease-out';
        } else {
            card.style.display = 'none';
        }
    });
}

// Adicionar animação CSS
if (!document.getElementById('periodToggleAnimation')) {
    const style = document.createElement('style');
    style.id = 'periodToggleAnimation';
    style.textContent = `
        @keyframes fadeInCard {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        [data-period] {
            transition: all 0.3s ease-out;
        }
    `;
    document.head.appendChild(style);
}

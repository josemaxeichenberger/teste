// @dev GustavoChaconDeveloper - github.com/GustavoChaconDeveloper
// ===== NAVIGATION & LANGUAGE SYSTEM =====

// ===== AJUSTE DINÂMICO: empurra header para baixo do top-banner =====
function adjustHeaderForBanner() {
    const banner  = document.querySelector('.top-banner');
    const header  = document.querySelector('.header');
    const navMenu = document.getElementById('navMenu');

    if (!banner || !header) return; // sem banner na página, não faz nada

    function update() {
        if (window.innerWidth <= 992) {
            // 1) posiciona o header abaixo do banner
            header.style.top = banner.offsetHeight + 'px';

            // 2) aguarda o reflow para ler a posição real do bottom do header
            requestAnimationFrame(function () {
                const headerBottom = Math.round(header.getBoundingClientRect().bottom);

                document.body.style.paddingTop = headerBottom + 'px';

                if (navMenu) {
                    navMenu.style.top       = headerBottom + 'px';
                    navMenu.style.maxHeight = 'calc(100vh - ' + headerBottom + 'px)';
                }
            });
        } else {
            // desktop: limpa os estilos inline, CSS assume o controle
            header.style.top = '';
            document.body.style.paddingTop = '';
            if (navMenu) {
                navMenu.style.top       = '';
                navMenu.style.maxHeight = '';
            }
        }
    }

    update();
    window.addEventListener('resize', update);
}

document.addEventListener('DOMContentLoaded', adjustHeaderForBanner);

// Mobile Menu Toggle
function toggleMobileMenu() {
    const navMenu = document.getElementById('navMenu');
    const menuIcon = document.querySelector('.mobile-menu-toggle i');
    
    navMenu.classList.toggle('active');
    
    // Alternar entre ícone de menu (bars) e X (times)
    if (navMenu.classList.contains('active')) {
        menuIcon.classList.remove('fa-bars');
        menuIcon.classList.add('fa-times');
    } else {
        menuIcon.classList.remove('fa-times');
        menuIcon.classList.add('fa-bars');
    }
}

// Language Selector Toggle (Desktop e Mobile)
function toggleLanguageMenu(event) {
    if (event) event.stopPropagation();
    
    // Detectar qual dropdown usar baseado no viewport
    const isMobile = window.innerWidth <= 768;
    const dropdownId = isMobile ? 'languageDropdownMobile' : 'languageDropdown';
    const dropdown = document.getElementById(dropdownId);
    
    if (dropdown) {
        dropdown.classList.toggle('active');
    }
}

// Toggle Navigation Dropdown (ABOUT, TEAM, etc)
function toggleNavDropdown(event, dropdownId) {
    event.preventDefault();
    event.stopPropagation();
    
    const dropdown = document.getElementById(dropdownId);
    const allDropdowns = document.querySelectorAll('.nav-dropdown-menu');
    
    // Fechar outros dropdowns
    allDropdowns.forEach(menu => {
        if (menu.id !== dropdownId) {
            menu.classList.remove('active');
        }
    });
    
    // Toggle o dropdown clicado
    dropdown.classList.toggle('active');
}

// Change Language
function changeLanguage(lang, country, event) {
    event.stopPropagation();
    
    // Atualizar bandeiras (desktop e mobile)
    const currentFlag = document.getElementById('currentFlag');
    const currentFlagMobile = document.getElementById('currentFlagMobile');
    
    if (currentFlag) {
        currentFlag.src = `https://flagcdn.com/w40/${country}.png`;
        currentFlag.alt = lang.toUpperCase();
    }
    
    if (currentFlagMobile) {
        currentFlagMobile.src = `https://flagcdn.com/w40/${country}.png`;
        currentFlagMobile.alt = lang.toUpperCase();
    }
    
    // Salvar preferência no localStorage
    localStorage.setItem('selectedLanguage', lang);
    localStorage.setItem('selectedCountry', country);
    
    // Fechar ambos os dropdowns (desktop e mobile)
    const dropdownDesktop = document.getElementById('languageDropdown');
    const dropdownMobile = document.getElementById('languageDropdownMobile');
    if (dropdownDesktop) dropdownDesktop.classList.remove('active');
    if (dropdownMobile) dropdownMobile.classList.remove('active');
    
    // Atualizar atributo lang do HTML
    document.documentElement.lang = lang;
    
    // Atualizar conteúdo da página (se houver função de tradução)
    if (typeof updatePageContent === 'function') {
        updatePageContent(lang);
    }
}

// Fechar dropdowns ao clicar fora
document.addEventListener('click', (event) => {
    if (!event.target.closest('.nav-dropdown')) {
        document.querySelectorAll('.nav-dropdown-menu').forEach(menu => {
            menu.classList.remove('active');
        });
    }
});

// Carregar idioma salvo ao carregar a página
window.addEventListener('DOMContentLoaded', () => {
    const savedLang = localStorage.getItem('selectedLanguage');
    const savedCountry = localStorage.getItem('selectedCountry');
    
    if (savedLang && savedCountry) {
        const currentFlag = document.getElementById('currentFlag');
        const currentFlagMobile = document.getElementById('currentFlagMobile');
        
        if (currentFlag) {
            currentFlag.src = `https://flagcdn.com/w40/${savedCountry}.png`;
            currentFlag.alt = savedLang.toUpperCase();
        }
        if (currentFlagMobile) {
            currentFlagMobile.src = `https://flagcdn.com/w40/${savedCountry}.png`;
            currentFlagMobile.alt = savedLang.toUpperCase();
        }
        document.documentElement.lang = savedLang;
    }
});

// Fechar menu mobile apenas em links sem dropdown
document.querySelectorAll('.nav-link').forEach(link => {
    link.addEventListener('click', (e) => {
        // Não fechar se for um link com dropdown
        const parentDropdown = link.closest('.nav-dropdown');
        if (!parentDropdown) {
            // Só fecha o menu se não for um dropdown
            document.getElementById('navMenu').classList.remove('active');
        }
    });
});

// Fechar menu mobile ao clicar em itens do dropdown
document.querySelectorAll('.nav-dropdown-item').forEach(item => {
    item.addEventListener('click', () => {
        document.getElementById('navMenu').classList.remove('active');
    });
});

// Fechar dropdowns ao clicar fora
document.addEventListener('click', (event) => {
    if (!event.target.closest('.language-selector')) {
        const dropdownDesktop = document.getElementById('languageDropdown');
        const dropdownMobile = document.getElementById('languageDropdownMobile');
        if (dropdownDesktop) dropdownDesktop.classList.remove('active');
        if (dropdownMobile) dropdownMobile.classList.remove('active');
    }
});


// ==========================================
// FUNÇÕES DO AVATAR DROPDOWN
// ==========================================

// Toggle do menu dropdown do avatar
function toggleUserAvatarMenu(event) {
    event.preventDefault();
    event.stopPropagation();
    
    const menu = document.getElementById('userAvatarMenu');
    if (menu) {
        menu.classList.toggle('active');
    }
}

// Fechar menu ao clicar fora
document.addEventListener('click', function(event) {
    const avatarMenu = document.getElementById('userAvatarMenu');
    const avatarDropdown = document.querySelector('.user-avatar-dropdown');
    
    if (avatarMenu && avatarDropdown && !avatarDropdown.contains(event.target)) {
        avatarMenu.classList.remove('active');
    }
});

// Função de logout
function logout(event) {
    if (event) {
        event.preventDefault();
    }
    
    // Fazer requisição para o endpoint de logout
    fetch('/api/logout.php', {
        method: 'POST',
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        // Redirecionar para a página inicial após logout
        window.location.href = '/';
    })
    .catch(error => {
        console.error('Erro ao fazer logout:', error);
        // Mesmo com erro, redirecionar
        window.location.href = '/';
    });
}

// Fechar dropdown com tecla ESC
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const avatarMenu = document.getElementById('userAvatarMenu');
        if (avatarMenu) {
            avatarMenu.classList.remove('active');
        }
    }
});

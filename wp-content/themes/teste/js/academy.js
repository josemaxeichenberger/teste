// @dev GustavoChaconDeveloper - github.com/GustavoChaconDeveloper
// ===== ACADEMY PAGE ===== //

// ── Open Video Modal ──────────────────────────────────────────────
function openVideoModal(card) {
    const videoId    = card.dataset.videoId;
    const title      = card.dataset.title;
    const desc       = card.dataset.description;
    const category   = card.querySelector('.academy-card-category')
                           ? card.querySelector('.academy-card-category').textContent.trim()
                           : '';

    const overlay  = document.getElementById('academyModal');
    const iframe   = document.getElementById('academyVideoFrame');
    const modalTitle    = document.getElementById('modalTitle');
    const modalDesc     = document.getElementById('modalDesc');
    const modalCategory = document.getElementById('modalCategory');

    if (!overlay || !iframe) return;

    // Preenche dados do modal
    if (modalTitle)    modalTitle.textContent    = title    || '';
    if (modalDesc)     modalDesc.textContent     = desc     || '';
    if (modalCategory) modalCategory.textContent = category || '';

    // Carrega o iframe com autoplay
    iframe.src = 'https://www.youtube.com/embed/' + videoId + '?autoplay=1&rel=0&modestbranding=1';

    // Abre o modal
    overlay.classList.add('active');

    // Trava scroll do body (compatível iOS Safari)
    document.body.dataset.scrollY = String(window.scrollY || window.pageYOffset);
    document.body.style.top       = '-' + document.body.dataset.scrollY + 'px';
    document.body.classList.add('menu-open');
}

// ── Close Video Modal ─────────────────────────────────────────────
function closeVideoModal(event, force) {
    // Se veio de click: só fecha se clicou fora do modal interno
    if (!force && event) {
        const modal = document.querySelector('.academy-modal');
        if (modal && modal.contains(event.target)) return;
    }

    const overlay = document.getElementById('academyModal');
    const iframe  = document.getElementById('academyVideoFrame');

    if (!overlay) return;

    overlay.classList.remove('active');

    // Para o vídeo limpando o src
    if (iframe) iframe.src = '';

    // Restaura scroll do body
    const scrollY = parseInt(document.body.dataset.scrollY || '0', 10);
    document.body.classList.remove('menu-open');
    document.body.style.top = '';
    window.scrollTo(0, scrollY);
}

// ── Fechar com ESC ─────────────────────────────────────────────────
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        closeVideoModal(null, true);
    }
});

// ── Filter Buttons ─────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    const filterBtns = document.querySelectorAll('.academy-filter-btn');
    const cards      = document.querySelectorAll('.academy-card');
    const emptyState = document.getElementById('academyEmpty');

    filterBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            // Atualiza botão ativo
            filterBtns.forEach(function (b) { b.classList.remove('active'); });
            btn.classList.add('active');

            const filter = btn.dataset.filter;
            let visible  = 0;

            cards.forEach(function (card) {
                if (filter === 'all' || card.dataset.category === filter) {
                    card.classList.remove('hidden');
                    visible++;
                } else {
                    card.classList.add('hidden');
                }
            });

            // Empty state
            if (emptyState) {
                if (visible === 0) {
                    emptyState.classList.add('visible');
                } else {
                    emptyState.classList.remove('visible');
                }
            }
        });
    });

    // ── Login Gate ────────────────────────────────────────────────
    if (typeof usuarioLogado !== 'undefined' && !usuarioLogado) {
        setTimeout(function () { openNotLoggedModal(); }, 500);
    }
});

// ── Not-Logged Modal ──────────────────────────────────────────────
function openNotLoggedModal() {
    var modal = document.getElementById('notLoggedModal');
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
        updateNotLoggedModalTexts();
    }
}

function closeNotLoggedModal() {
    var modal = document.getElementById('notLoggedModal');
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
}

function updateNotLoggedModalTexts() {
    var savedLang = localStorage.getItem('selectedLanguage') || 'en';
    var texts = {
        en: {
            title:     "Oops! You're not logged in",
            subtitle:  "Please log in to access the Academy and all features.",
            loginBtn:  "Log In",
            noAccount: "Don't have an account?",
            signupLink:"Sign Up"
        },
        pt: {
            title:     "Ops! Você não está logado",
            subtitle:  "Por favor, faça login para acessar a Academy e todos os recursos.",
            loginBtn:  "Entrar",
            noAccount: "Não tem uma conta?",
            signupLink:"Cadastre-se"
        }
    };
    var t = texts[savedLang] || texts['en'];
    var el = function(id) { return document.getElementById(id); };
    if (el('notLoggedTitle'))    el('notLoggedTitle').textContent    = t.title;
    if (el('notLoggedSubtitle')) el('notLoggedSubtitle').textContent = t.subtitle;
    if (el('notLoggedLoginBtn')) el('notLoggedLoginBtn').textContent = t.loginBtn;
    if (el('notLoggedNoAccount'))el('notLoggedNoAccount').textContent= t.noAccount;
    if (el('notLoggedSignupLink'))el('notLoggedSignupLink').textContent = t.signupLink;
}

function openLoginFromNotLogged() {
    // Esconde o gate temporariamente para o modal de login ficar visível
    var gateModal = document.getElementById('notLoggedModal');
    if (gateModal) gateModal.classList.remove('active');

    // Abre o modal de login
    if (typeof openLoginModal === 'function') { openLoginModal(); }

    // Observa quando o modal de login fechar; se ainda não logado, reexibe o gate
    var loginModal = document.getElementById('loginModal');
    if (loginModal) {
        var observer = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                if (!loginModal.classList.contains('active')) {
                    observer.disconnect();
                    setTimeout(function () {
                        // Reexibe o gate apenas se ainda não logado
                        if (typeof usuarioLogado !== 'undefined' && !usuarioLogado) {
                            openNotLoggedModal();
                        }
                    }, 250);
                }
            });
        });
        observer.observe(loginModal, { attributes: true, attributeFilter: ['class'] });
    }
}

function openSignupFromNotLogged() {
    // Mesmo padrão — esconde gate, abre signup, reexibe se ainda não logado
    var gateModal = document.getElementById('notLoggedModal');
    if (gateModal) gateModal.classList.remove('active');

    if (typeof openSignupModal === 'function') {
        openSignupModal();
    } else if (typeof openLoginModal === 'function') {
        openLoginModal();
        if (typeof switchTab === 'function') { setTimeout(function () { switchTab('signup'); }, 50); }
    }

    var loginModal = document.getElementById('loginModal');
    if (loginModal) {
        var obs = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                if (!loginModal.classList.contains('active')) {
                    obs.disconnect();
                    setTimeout(function () {
                        if (typeof usuarioLogado !== 'undefined' && !usuarioLogado) {
                            openNotLoggedModal();
                        }
                    }, 250);
                }
            });
        });
        obs.observe(loginModal, { attributes: true, attributeFilter: ['class'] });
    }
}

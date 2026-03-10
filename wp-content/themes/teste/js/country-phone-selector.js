// Sistema de seleção de país com bandeiras e formatação automática de telefone
// Dados dos países com código ISO, DDI e formato de telefone

const countries = [
    // América do Norte
    { code: '+1', iso: 'us', name: 'USA/Canada', maxDigits: 10, format: '(###) ###-####', placeholder: '(555) 123-4567' },
    { code: '+52', iso: 'mx', name: 'Mexico', maxDigits: 10, format: '## #### ####', placeholder: '55 1234 5678' },
    
    // América do Sul
    { code: '+54', iso: 'ar', name: 'Argentina', maxDigits: 10, format: '## #### ####', placeholder: '11 1234 5678' },
    { code: '+55', iso: 'br', name: 'Brazil', maxDigits: 11, format: '(##) #####-####', placeholder: '(11) 94993-1617' },
    { code: '+56', iso: 'cl', name: 'Chile', maxDigits: 9, format: '# #### ####', placeholder: '2 1234 5678' },
    { code: '+57', iso: 'co', name: 'Colombia', maxDigits: 10, format: '### ### ####', placeholder: '300 123 4567' },
    { code: '+51', iso: 'pe', name: 'Peru', maxDigits: 9, format: '### ### ###', placeholder: '987 654 321' },
    { code: '+58', iso: 've', name: 'Venezuela', maxDigits: 10, format: '### ### ####', placeholder: '412 123 4567' },
    { code: '+593', iso: 'ec', name: 'Ecuador', maxDigits: 9, format: '## ### ####', placeholder: '99 123 4567' },
    { code: '+595', iso: 'py', name: 'Paraguay', maxDigits: 9, format: '### ### ###', placeholder: '981 123 456' },
    { code: '+598', iso: 'uy', name: 'Uruguay', maxDigits: 8, format: '#### ####', placeholder: '9123 4567' },
    
    // Europa
    { code: '+32', iso: 'be', name: 'Belgium', maxDigits: 9, format: '### ## ## ##', placeholder: '470 12 34 56' },
    { code: '+41', iso: 'ch', name: 'Switzerland', maxDigits: 9, format: '## ### ## ##', placeholder: '78 123 45 67' },
    { code: '+49', iso: 'de', name: 'Germany', maxDigits: 11, format: '### ########', placeholder: '151 12345678' },
    { code: '+34', iso: 'es', name: 'Spain', maxDigits: 9, format: '### ### ###', placeholder: '612 345 678' },
    { code: '+33', iso: 'fr', name: 'France', maxDigits: 9, format: '# ## ## ## ##', placeholder: '6 12 34 56 78' },
    { code: '+44', iso: 'gb', name: 'United Kingdom', maxDigits: 10, format: '## #### ####', placeholder: '20 7123 4567' },
    { code: '+39', iso: 'it', name: 'Italy', maxDigits: 10, format: '### ### ####', placeholder: '312 345 6789' },
    { code: '+31', iso: 'nl', name: 'Netherlands', maxDigits: 9, format: '## #### ####', placeholder: '6 1234 5678' },
    { code: '+48', iso: 'pl', name: 'Poland', maxDigits: 9, format: '### ### ###', placeholder: '501 234 567' },
    { code: '+351', iso: 'pt', name: 'Portugal', maxDigits: 9, format: '### ### ###', placeholder: '912 345 678' },
    { code: '+7', iso: 'ru', name: 'Russia', maxDigits: 10, format: '### ###-##-##', placeholder: '912 345-67-89' },
    { code: '+380', iso: 'ua', name: 'Ukraine', maxDigits: 9, format: '## ### ## ##', placeholder: '50 123 45 67' },
    { code: '+43', iso: 'at', name: 'Austria', maxDigits: 10, format: '### #######', placeholder: '664 1234567' },
    { code: '+45', iso: 'dk', name: 'Denmark', maxDigits: 8, format: '## ## ## ##', placeholder: '20 12 34 56' },
    { code: '+46', iso: 'se', name: 'Sweden', maxDigits: 9, format: '## ### ## ##', placeholder: '70 123 45 67' },
    { code: '+47', iso: 'no', name: 'Norway', maxDigits: 8, format: '### ## ###', placeholder: '406 12 345' },
    { code: '+358', iso: 'fi', name: 'Finland', maxDigits: 10, format: '## ### ####', placeholder: '40 123 4567' },
    { code: '+353', iso: 'ie', name: 'Ireland', maxDigits: 9, format: '## ### ####', placeholder: '85 123 4567' },
    
    // Ásia
    { code: '+86', iso: 'cn', name: 'China', maxDigits: 11, format: '### #### ####', placeholder: '138 0013 8000' },
    { code: '+91', iso: 'in', name: 'India', maxDigits: 10, format: '##### #####', placeholder: '81234 56789' },
    { code: '+81', iso: 'jp', name: 'Japan', maxDigits: 10, format: '##-####-####', placeholder: '90-1234-5678' },
    { code: '+82', iso: 'kr', name: 'South Korea', maxDigits: 10, format: '##-####-####', placeholder: '10-1234-5678' },
    { code: '+65', iso: 'sg', name: 'Singapore', maxDigits: 8, format: '#### ####', placeholder: '8123 4567' },
    { code: '+60', iso: 'my', name: 'Malaysia', maxDigits: 10, format: '##-### ####', placeholder: '12-345 6789' },
    { code: '+66', iso: 'th', name: 'Thailand', maxDigits: 9, format: '## ### ####', placeholder: '81 234 5678' },
    { code: '+84', iso: 'vn', name: 'Vietnam', maxDigits: 10, format: '### ### ####', placeholder: '912 345 678' },
    { code: '+63', iso: 'ph', name: 'Philippines', maxDigits: 10, format: '### ### ####', placeholder: '917 123 4567' },
    { code: '+62', iso: 'id', name: 'Indonesia', maxDigits: 11, format: '###-###-####', placeholder: '812-345-6789' },
    { code: '+92', iso: 'pk', name: 'Pakistan', maxDigits: 10, format: '### #######', placeholder: '300 1234567' },
    { code: '+880', iso: 'bd', name: 'Bangladesh', maxDigits: 10, format: '#### ######', placeholder: '1712 345678' },
    
    // Oriente Médio
    { code: '+971', iso: 'ae', name: 'United Arab Emirates', maxDigits: 9, format: '## ### ####', placeholder: '50 123 4567' },
    { code: '+966', iso: 'sa', name: 'Saudi Arabia', maxDigits: 9, format: '## ### ####', placeholder: '50 123 4567' },
    { code: '+972', iso: 'il', name: 'Israel', maxDigits: 9, format: '##-###-####', placeholder: '50-123-4567' },
    { code: '+90', iso: 'tr', name: 'Turkey', maxDigits: 10, format: '### ### ####', placeholder: '532 123 4567' },
    { code: '+20', iso: 'eg', name: 'Egypt', maxDigits: 10, format: '### ### ####', placeholder: '100 123 4567' },
    
    // Oceania
    { code: '+61', iso: 'au', name: 'Australia', maxDigits: 9, format: '### ### ###', placeholder: '412 345 678' },
    { code: '+64', iso: 'nz', name: 'New Zealand', maxDigits: 9, format: '## ### ####', placeholder: '21 123 4567' },
    
    // África
    { code: '+27', iso: 'za', name: 'South Africa', maxDigits: 9, format: '## ### ####', placeholder: '82 123 4567' },
    { code: '+234', iso: 'ng', name: 'Nigeria', maxDigits: 10, format: '### ### ####', placeholder: '802 123 4567' },
    { code: '+254', iso: 'ke', name: 'Kenya', maxDigits: 9, format: '### ######', placeholder: '712 345678' }
];

// Variável global para país selecionado
window.selectedCountry = countries.find(c => c.iso === 'br') || countries[0]; // Brasil como padrão
let selectedCountry = window.selectedCountry; // Referência local para compatibilidade

// Inicializar seletor customizado
function initCustomCountrySelector() {
    const phoneGroups = document.querySelectorAll('.phone-group');
    
    phoneGroups.forEach(phoneGroup => {
        // Verificar se já foi inicializado
        if (phoneGroup.querySelector('.custom-country-select')) {
            return;
        }
        
        const originalSelect = phoneGroup.querySelector('#countryCode');
        if (!originalSelect) return;
        
        // Criar estrutura do custom select
        const customSelect = document.createElement('div');
        customSelect.className = 'custom-country-select';
        customSelect.innerHTML = `
            <div class="custom-country-select-trigger">
                <div class="country-select-value">
                    <img src="https://flagcdn.com/w40/${selectedCountry.iso}.png" 
                         alt="${selectedCountry.name}" 
                         class="country-flag-icon">
                    <span class="country-code-text">${selectedCountry.code}</span>
                </div>
                <i class="fas fa-chevron-down country-select-arrow"></i>
            </div>
            <div class="custom-country-dropdown">
                <div class="country-search">
                    <input type="text" placeholder="Search country..." class="country-search-input">
                </div>
                <div class="country-options-list">
                    ${countries.map(country => `
                        <div class="country-option ${country.code === selectedCountry.code ? 'selected' : ''}" 
                             data-code="${country.code}" 
                             data-iso="${country.iso}">
                            <img src="https://flagcdn.com/w40/${country.iso}.png" 
                                 alt="${country.name}" 
                                 class="country-option-flag">
                            <div class="country-option-info">
                                <div class="country-option-name">${country.name}</div>
                                <div class="country-option-code">${country.code}</div>
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
        
        // Inserir antes do select original
        phoneGroup.insertBefore(customSelect, originalSelect);
        
        // Atualizar valor do select original
        originalSelect.value = selectedCountry.code;
        
        // Event listeners
        const trigger = customSelect.querySelector('.custom-country-select-trigger');
        const dropdown = customSelect.querySelector('.custom-country-dropdown');
        const searchInput = customSelect.querySelector('.country-search-input');
        const options = customSelect.querySelectorAll('.country-option');
        
        // Toggle dropdown
        trigger.addEventListener('click', (e) => {
            e.stopPropagation();
            const isActive = dropdown.classList.contains('active');
            
            // Fechar todos os dropdowns abertos
            document.querySelectorAll('.custom-country-dropdown.active').forEach(d => {
                d.classList.remove('active');
                d.closest('.custom-country-select').querySelector('.custom-country-select-trigger').classList.remove('active');
            });
            
            if (!isActive) {
                dropdown.classList.add('active');
                trigger.classList.add('active');
                setTimeout(() => searchInput.focus(), 100);
            }
        });
        
        // Selecionar país
        options.forEach(option => {
            option.addEventListener('click', () => {
                const code = option.dataset.code;
                const iso = option.dataset.iso;
                const country = countries.find(c => c.code === code);
                
                if (country) {
                    // Atualizar país selecionado globalmente
                    selectedCountry = country;
                    window.selectedCountry = country;
                    
                    // Atualizar UI
                    customSelect.querySelector('.country-flag-icon').src = `https://flagcdn.com/w40/${iso}.png`;
                    customSelect.querySelector('.country-code-text').textContent = code;
                    
                    // Atualizar select original
                    originalSelect.value = code;
                    
                    // Marcar como selecionado
                    options.forEach(opt => opt.classList.remove('selected'));
                    option.classList.add('selected');
                    
                    // Fechar dropdown
                    dropdown.classList.remove('active');
                    trigger.classList.remove('active');
                    
                    // Atualizar placeholder do telefone
                    const phoneInput = phoneGroup.querySelector('#phoneNumber');
                    if (phoneInput) {
                        phoneInput.value = '';
                        phoneInput.placeholder = country.placeholder;
                        phoneInput.maxLength = country.format.length;
                    }
                    
                    console.log('País selecionado:', country.name, code);
                }
            });
        });
        
        // Busca de país
        searchInput.addEventListener('input', (e) => {
            const searchTerm = e.target.value.toLowerCase();
            
            options.forEach(option => {
                const countryName = option.querySelector('.country-option-name').textContent.toLowerCase();
                const countryCode = option.querySelector('.country-option-code').textContent.toLowerCase();
                
                if (countryName.includes(searchTerm) || countryCode.includes(searchTerm)) {
                    option.style.display = 'flex';
                } else {
                    option.style.display = 'none';
                }
            });
        });
        
        // Prevenir que o search feche o dropdown
        searchInput.addEventListener('click', (e) => {
            e.stopPropagation();
        });
    });
    
    // Fechar dropdown ao clicar fora
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.custom-country-select')) {
            document.querySelectorAll('.custom-country-dropdown.active').forEach(dropdown => {
                dropdown.classList.remove('active');
                dropdown.closest('.custom-country-select').querySelector('.custom-country-select-trigger').classList.remove('active');
            });
        }
    });
}

// Formatação de telefone melhorada - EXPOSTA GLOBALMENTE
window.formatPhoneNumber = function() {
    const phoneInput = document.getElementById('phoneNumber');
    if (!phoneInput) return;
    
    const country = window.selectedCountry;
    if (!country) return;
    
    let value = phoneInput.value.replace(/\D/g, ''); // Remove tudo que não é dígito
    
    // Limitar número de dígitos
    if (value.length > country.maxDigits) {
        value = value.substring(0, country.maxDigits);
    }
    
    // Aplicar formatação
    let formattedValue = '';
    let valueIndex = 0;
    
    for (let i = 0; i < country.format.length && valueIndex < value.length; i++) {
        if (country.format[i] === '#') {
            formattedValue += value[valueIndex];
            valueIndex++;
        } else {
            // É um caractere de formatação (espaço, parênteses, hífen, etc)
            if (valueIndex > 0) {
                formattedValue += country.format[i];
            }
        }
    }
    
    phoneInput.value = formattedValue.trim();
}

// Atualizar placeholder - EXPOSTA GLOBALMENTE
window.updatePhonePlaceholder = function() {
    const phoneInput = document.getElementById('phoneNumber');
    if (!phoneInput) return;
    
    const country = window.selectedCountry;
    if (!country) return;
    
    phoneInput.value = '';
    phoneInput.placeholder = country.placeholder;
    phoneInput.maxLength = country.format.length;
}

// Inicializar quando o DOM estiver pronto
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCustomCountrySelector);
} else {
    initCustomCountrySelector();
}

// Re-inicializar quando o modal for aberto
const observer = new MutationObserver((mutations) => {
    mutations.forEach((mutation) => {
        if (mutation.target.classList && mutation.target.classList.contains('modal')) {
            if (mutation.target.classList.contains('active')) {
                setTimeout(initCustomCountrySelector, 100);
            }
        }
    });
});

// Observar mudanças no modal
const modals = document.querySelectorAll('.modal');
modals.forEach(modal => {
    observer.observe(modal, { attributes: true, attributeFilter: ['class'] });
});

console.log('✅ Country phone selector initialized with', countries.length, 'countries');
console.log('📱 formatPhoneNumber() available globally:', typeof window.formatPhoneNumber === 'function');
console.log('🌍 Selected country:', window.selectedCountry?.name, window.selectedCountry?.code);

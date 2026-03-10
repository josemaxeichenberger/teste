// Sticky Header on Scroll
        window.addEventListener('scroll', function() {
            const header = document.querySelector('.header');
            if (window.scrollY > 100) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });

        // Scroll Animation Observer
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach((entry, index) => {
                if (entry.isIntersecting) {
                    setTimeout(() => {
                        entry.target.classList.add('animate-in');
                    }, index * 150);
                } else {
                    // Remove animation class when element leaves viewport
                    entry.target.classList.remove('animate-in');
                }
            });
        }, observerOptions);

        // Observe all animated elements
        document.addEventListener('DOMContentLoaded', function() {
            const animatedElements = document.querySelectorAll('.feature-card, .champions-box, .champions-box-image, .champions-bottom-image');
            animatedElements.forEach(el => observer.observe(el));
        });

        // Smooth Scroll for Anchor Links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const href = this.getAttribute('href');
                if (href !== '#' && document.querySelector(href)) {
                    e.preventDefault();
                    document.querySelector(href).scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Button Ripple Effect
        document.querySelectorAll('.btn-hero, .btn-empowerment, .btn-get-started').forEach(button => {
            button.addEventListener('click', function(e) {
                const ripple = document.createElement('span');
                const rect = this.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const x = e.clientX - rect.left - size / 2;
                const y = e.clientY - rect.top - size / 2;
                
                ripple.style.width = ripple.style.height = size + 'px';
                ripple.style.left = x + 'px';
                ripple.style.top = y + 'px';
                ripple.classList.add('ripple');
                
                this.appendChild(ripple);
                
                setTimeout(() => ripple.remove(), 600);
            });
        });

        // Add ripple CSS dynamically
        const style = document.createElement('style');
        style.textContent = `
            .btn-hero, .btn-empowerment, .btn-get-started {
                position: relative;
                overflow: hidden;
            }
            .ripple {
                position: absolute;
                border-radius: 50%;
                background: rgba(255, 255, 255, 0.5);
                transform: scale(0);
                animation: ripple-animation 0.6s ease-out;
                pointer-events: none;
            }
            @keyframes ripple-animation {
                to {
                    transform: scale(4);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);

        // Counter Animation for Stats
        function animateCounter(element, target, duration = 2000) {
            const start = 0;
            const increment = target / (duration / 16);
            let current = start;
            
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    element.textContent = formatNumber(target);
                    clearInterval(timer);
                } else {
                    element.textContent = formatNumber(Math.floor(current));
                }
            }, 16);
        }

        function formatNumber(num) {
            if (num >= 1000) {
                return (num / 1000).toFixed(1).replace('.0', '') + 'K';
            }
            return num.toString();
        }

        // Intersection Observer for animations
        const animationObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                    
                    // Animate stat numbers
                    if (entry.target.classList.contains('stat-item')) {
                        const numberElement = entry.target.querySelector('.stat-number');
                        const text = numberElement.textContent;
                        
                        // Extract number from text
                        let targetNumber = 0;
                        if (text.includes('415K')) targetNumber = 415;
                        else if (text.includes('1,000+')) targetNumber = 1000;
                        else if (text.includes('15+')) targetNumber = 15;
                        else if (text.includes('100+')) targetNumber = 100;
                        
                        if (targetNumber > 0) {
                            numberElement.textContent = '0';
                            setTimeout(() => {
                                if (text.includes('K')) {
                                    animateCounter(numberElement, targetNumber, 2000);
                                    setTimeout(() => {
                                        numberElement.textContent = text;
                                    }, 2100);
                                } else if (text.includes('+')) {
                                    animateCounter(numberElement, targetNumber, 1500);
                                    setTimeout(() => {
                                        numberElement.textContent = targetNumber + '+';
                                    }, 1600);
                                }
                            }, 200);
                        }
                    }
                    
                    animationObserver.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.2,
            rootMargin: '0px 0px -100px 0px'
        });

        // Observe all animated elements
        document.addEventListener('DOMContentLoaded', function() {
            // Stats animation
            document.querySelectorAll('.stat-item').forEach(el => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(30px)';
                el.style.transition = 'all 0.6s ease';
                animationObserver.observe(el);
            });

            // Tournament cards
            document.querySelectorAll('.tournament-card').forEach((el, index) => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(30px)';
                el.style.transition = `all 0.5s ease ${index * 0.1}s`;
                animationObserver.observe(el);
            });

            // Unique cards
            document.querySelectorAll('.unique-card').forEach((el, index) => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(30px)';
                el.style.transition = `all 0.5s ease ${index * 0.1}s`;
                animationObserver.observe(el);
            });

            // Player cards
            document.querySelectorAll('.player-card').forEach((el, index) => {
                el.style.opacity = '0';
                el.style.transform = 'scale(0.9)';
                el.style.transition = `all 0.4s ease ${index * 0.05}s`;
                animationObserver.observe(el);
            });

            // FAQ items
            document.querySelectorAll('.faq-item').forEach((el, index) => {
                el.style.opacity = '0';
                el.style.transform = 'translateX(-30px)';
                el.style.transition = `all 0.5s ease ${index * 0.1}s`;
                animationObserver.observe(el);
            });

            // Final CTA section
            const ctaText = document.querySelector('.final-cta-text');
            const ctaAvatars = document.querySelector('.final-cta-avatars');
            
            if (ctaText) {
                ctaText.style.opacity = '0';
                ctaText.style.transform = 'translateX(-50px)';
                ctaText.style.transition = 'all 0.8s ease';
                animationObserver.observe(ctaText);
            }
            
            if (ctaAvatars) {
                ctaAvatars.style.opacity = '0';
                ctaAvatars.style.transform = 'translateX(50px)';
                ctaAvatars.style.transition = 'all 0.8s ease 0.2s';
                animationObserver.observe(ctaAvatars);
            }

            // Add animate-in class styles
            const animationStyles = document.createElement('style');
            animationStyles.textContent = `
                .stat-item.animate-in,
                .tournament-card.animate-in,
                .unique-card.animate-in,
                .player-card.animate-in,
                .faq-item.animate-in,
                .final-cta-text.animate-in,
                .final-cta-avatars.animate-in {
                    opacity: 1 !important;
                    transform: translateY(0) translateX(0) scale(1) !important;
                }
            `;
            document.head.appendChild(animationStyles);
        });

        // Players Carousel
        let currentCarouselIndex = 0;
        const carouselTrack = document.getElementById('carouselTrack');
        
        function moveCarousel(direction) {
            if (!carouselTrack) return;
            
            const cards = carouselTrack.querySelectorAll('.player-card');
            const cardWidth = cards[0].offsetWidth;
            const gap = 20;
            const totalCards = cards.length;
            const visibleCards = window.innerWidth <= 480 ? 1 : window.innerWidth <= 768 ? 2 : window.innerWidth <= 1200 ? 3 : 4;
            const maxIndex = totalCards - visibleCards;
            
            currentCarouselIndex += direction;
            
            // Loop infinito: se voltar do início, vai para o final
            if (currentCarouselIndex < 0) {
                currentCarouselIndex = maxIndex;
            } 
            // Loop infinito: se avançar do final, volta para o início
            else if (currentCarouselIndex > maxIndex) {
                currentCarouselIndex = 0;
            }
            
            const offset = currentCarouselIndex * (cardWidth + gap);
            carouselTrack.style.transform = `translateX(-${offset}px)`;
        }

        // FAQ Toggle
        function toggleFaq(element) {
            const faqItem = element.parentElement;
            const isActive = faqItem.classList.contains('active');
            
            // Close all FAQ items
            document.querySelectorAll('.faq-item').forEach(item => {
                item.classList.remove('active');
                const icon = item.querySelector('.faq-toggle i');
                if (icon) {
                    icon.className = 'fas fa-plus';
                }
            });
            
            // If the clicked item wasn't active, open it
            if (!isActive) {
                faqItem.classList.add('active');
                const icon = element.querySelector('.faq-toggle i');
                if (icon) {
                    icon.className = 'fas fa-times';
                }
            }
        }
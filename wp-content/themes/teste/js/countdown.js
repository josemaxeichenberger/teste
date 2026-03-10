// @dev GustavoChaconDeveloper - github.com/GustavoChaconDeveloper
// Countdown Timer with Professional Effects
class CountdownTimer {
    constructor(targetDate) {
        this.targetDate = new Date(targetDate).getTime();
        this.elements = {
            days: document.getElementById('days'),
            hours: document.getElementById('hours'),
            minutes: document.getElementById('minutes'),
            seconds: document.getElementById('seconds')
        };
        this.previousValues = {
            days: null,
            hours: null,
            minutes: null,
            seconds: null
        };
        this.init();
    }

    init() {
        this.updateCountdown();
        setInterval(() => this.updateCountdown(), 1000);
    }

    updateCountdown() {
        const now = new Date().getTime();
        const distance = this.targetDate - now;

        if (distance < 0) {
            this.displayExpired();
            return;
        }

        const time = {
            days: Math.floor(distance / (1000 * 60 * 60 * 24)),
            hours: Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)),
            minutes: Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60)),
            seconds: Math.floor((distance % (1000 * 60)) / 1000)
        };

        // Update each element with animation
        for (let unit in time) {
            this.updateElement(unit, time[unit]);
        }
    }

    updateElement(unit, value) {
        const formattedValue = value.toString().padStart(2, '0');
        
        if (this.elements[unit] && this.previousValues[unit] !== formattedValue) {
            this.animateChange(this.elements[unit], formattedValue);
            this.previousValues[unit] = formattedValue;
        }
    }

    animateChange(element, newValue) {
        // Update value without animation
        element.textContent = newValue;
    }

    displayExpired() {
        Object.values(this.elements).forEach(element => {
            if (element) element.textContent = '00';
        });
    }

    // Update target date dynamically
    setNewDate(newDate) {
        this.targetDate = new Date(newDate).getTime();
    }
}

// Initialize countdown when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    // Set target date - exemplo: 7 dias a partir de agora
    const futureDate = new Date();
    futureDate.setDate(futureDate.getDate() + 7);
    futureDate.setHours(20, 0, 0, 0); // 8:00 PM
    
    // Create countdown instance
    const countdown = new CountdownTimer(futureDate);
    
    // Make it globally accessible if needed
    window.pokerCountdown = countdown;
});

// Additional CSS animations (inject into document)
const style = document.createElement('style');
style.textContent = `
    .countdown-value {
        display: inline-block;
    }

    .countdown-item {
        transition: transform 0.3s ease;
    }

    .countdown-item:hover {
        transform: translateY(-2px);
    }

    /* Glow effect on countdown values */
    .countdown-value {
        text-shadow: 0 0 10px rgba(255, 255, 255, 0.3);
    }
`;
document.head.appendChild(style);

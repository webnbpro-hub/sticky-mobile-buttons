// Анимация нажатия на кнопки
document.addEventListener('DOMContentLoaded', function() {
    var buttons = document.querySelectorAll('.sticky-button');
    
    buttons.forEach(function(button) {
        // Для сенсорных устройств
        button.addEventListener('touchstart', function() {
            this.style.transform = 'scale(0.95)';
            this.style.backgroundColor = '#f5f5f5';
        });
        
        button.addEventListener('touchend', function() {
            this.style.transform = '';
            this.style.backgroundColor = '';
        });
        
        button.addEventListener('touchcancel', function() {
            this.style.transform = '';
            this.style.backgroundColor = '';
        });
        
        // Для десктопов (если нужно тестировать на ПК)
        button.addEventListener('mousedown', function() {
            this.style.transform = 'scale(0.95)';
            this.style.backgroundColor = '#f5f5f5';
        });
        
        button.addEventListener('mouseup', function() {
            this.style.transform = '';
            this.style.backgroundColor = '';
        });
        
        button.addEventListener('mouseleave', function() {
            this.style.transform = '';
            this.style.backgroundColor = '';
        });
    });
});
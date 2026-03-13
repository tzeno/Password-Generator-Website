class PasswordGenerator {
    constructor() {
        this.elements = {
            password: document.getElementById('password'),
            generateBtn: document.getElementById('generateBtn'),
            copyBtn: document.getElementById('copyBtn'),
            refreshBtn: document.getElementById('refreshBtn'),
            length: document.getElementById('length'),
            lengthValue: document.getElementById('lengthValue'),
            uppercase: document.getElementById('uppercase'),
            lowercase: document.getElementById('lowercase'),
            numbers: document.getElementById('numbers'),
            symbols: document.getElementById('symbols'),
            excludeSimilar: document.getElementById('excludeSimilar'),
            strengthBar: document.getElementById('strengthBar'),
            strengthText: document.getElementById('strengthText'),
            historyList: document.getElementById('historyList'),
            toggleAdvanced: document.getElementById('toggleAdvanced'),
            advancedPanel: document.getElementById('advancedPanel'),
            generatorType: document.querySelectorAll('input[name="generatorType"]'),
            wordCount: document.getElementById('wordCount'),
            separator: document.getElementById('separator'),
            passphraseControls: document.getElementById('passphraseControls')
        };
        
        this.init();
    }
    
    init() {
        this.elements.generateBtn.addEventListener('click', () => this.generate());
        this.elements.refreshBtn.addEventListener('click', () => this.generate());
        this.elements.copyBtn.addEventListener('click', () => this.copyToClipboard());
        this.elements.length.addEventListener('input', () => this.updateLength());
        this.elements.toggleAdvanced.addEventListener('click', () => this.toggleAdvanced());
        
        this.elements.generatorType.forEach(radio => {
            radio.addEventListener('change', () => this.toggleGeneratorType());
        });
        
        this.generate();
    }
    
    async generate() {
        this.elements.password.value = 'Generating...';
        this.elements.password.disabled = true;
        
        const options = {
            length: parseInt(this.elements.length.value, 10),
            uppercase: this.elements.uppercase.checked,
            lowercase: this.elements.lowercase.checked,
            numbers: this.elements.numbers.checked,
            symbols: this.elements.symbols.checked,
            excludeSimilar: this.elements.excludeSimilar.checked,
            generatorType: document.querySelector('input[name="generatorType"]:checked').value,
            wordCount: parseInt(this.elements.wordCount?.value || 4, 10),
            separator: this.elements.separator?.value || '-'
        };
        
        try {
            const response = await fetch('api/generate.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(options)
            });
            
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            
            const data = await response.json();
            
            if (data.success) {
                this.elements.password.value = data.password;
                this.updateStrength(data.strength);
                this.updateHistory(data.history);
                
                // Simple animation
                this.elements.password.style.transform = 'scale(1.02)';
                setTimeout(() => {
                    this.elements.password.style.transform = 'scale(1)';
                }, 100);
            } else {
                this.elements.password.value = 'Error generating password';
            }
        } catch (error) {
            console.error('Generation failed:', error);
            this.elements.password.value = 'Error - try again';
        } finally {
            this.elements.password.disabled = false;
        }
    }
    
    updateStrength(strength) {
        this.elements.strengthBar.style.width = strength.score + '%';
        this.elements.strengthBar.className = `bar ${strength.class}`;
        this.elements.strengthText.textContent = strength.level;
    }
    
    async copyToClipboard() {
        const password = this.elements.password.value;
        if (!password || password === 'Generating...' || password.startsWith('Error')) return;
        
        try {
            await navigator.clipboard.writeText(password);
            
            // Visual feedback
            this.elements.copyBtn.style.transform = 'scale(1.2)';
            this.elements.copyBtn.style.backgroundColor = '#10b981';
            
            setTimeout(() => {
                this.elements.copyBtn.style.transform = 'scale(1)';
                this.elements.copyBtn.style.backgroundColor = '';
            }, 200);
        } catch (err) {
            alert('Failed to copy to clipboard');
        }
    }
    
    updateLength() {
        const value = this.elements.length.value;
        this.elements.lengthValue.textContent = value;
    }
    
    toggleAdvanced() {
        this.elements.advancedPanel.classList.toggle('hidden');
        this.elements.toggleAdvanced.textContent = 
            this.elements.advancedPanel.classList.contains('hidden') 
                ? '⚡ Show Advanced Options' 
                : '⚡ Hide Advanced Options';
    }
    
    toggleGeneratorType() {
        const isPassphrase = document.querySelector('input[name="generatorType"]:checked').value === 'passphrase';
        this.elements.passphraseControls.style.display = isPassphrase ? 'block' : 'none';
    }
    
    updateHistory(history) {
        if (!this.elements.historyList) return;
        
        // Escape HTML to prevent XSS
        const escapeHtml = (text) => {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        };
        
        this.elements.historyList.innerHTML = history.map(pwd => {
            const safePwd = escapeHtml(pwd);
            const jsSafePwd = JSON.stringify(pwd); // For onclick
            return `
                <div class="history-item">
                    <code>${safePwd}</code>
                    <button class="small-copy" onclick="copyToClipboard(${jsSafePwd})" title="Copy">
                        📋
                    </button>
                </div>
            `;
        }).join('');
    }
}

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    new PasswordGenerator();
});

// Global copy function for history (must be secure)
window.copyToClipboard = async (text) => {
    try {
        await navigator.clipboard.writeText(text);
        alert('Copied to clipboard!');
    } catch (err) {
        alert('Failed to copy');
    }
};
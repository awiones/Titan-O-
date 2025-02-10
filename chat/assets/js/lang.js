class LanguageManager {
    constructor() {
        this.currentLang = localStorage.getItem('language') || 'en';
        this.translations = {};
        this.init();
    }

    async init() {
        await this.loadTranslations(this.currentLang);
        this.applyTranslations();
    }

    async loadTranslations(lang) {
        try {
            const response = await fetch(`/d:/website/titano/chat/assets/langs/${lang}.json`);
            this.translations = await response.json();
        } catch (error) {
            console.error('Error loading translations:', error);
            // Fallback to English if translation file fails to load
            if (lang !== 'en') {
                await this.loadTranslations('en');
            }
        }
    }

    async setLanguage(lang) {
        this.currentLang = lang;
        localStorage.setItem('language', lang);
        await this.loadTranslations(lang);
        this.applyTranslations();
        
        // Dispatch event for other components to react
        window.dispatchEvent(new CustomEvent('languagechange', { detail: { language: lang } }));
    }

    translate(key) {
        return this.translations[key] || key;
    }

    applyTranslations() {
        document.querySelectorAll('[data-i18n]').forEach(element => {
            const key = element.getAttribute('data-i18n');
            element.textContent = this.translate(key);
        });

        // Update placeholders
        document.querySelectorAll('[data-i18n-placeholder]').forEach(element => {
            const key = element.getAttribute('data-i18n-placeholder');
            element.placeholder = this.translate(key);
        });
    }
}

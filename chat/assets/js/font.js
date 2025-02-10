class FontManager {
    constructor() {
        this.fontSizes = {
            small: {
                '--base-font-size': '14px',
                '--message-font-size': '0.9rem',
                '--input-font-size': '0.9rem',
                '--title-font-size': '1.2rem',
                '--button-font-size': '0.9rem'
            },
            medium: {
                '--base-font-size': '16px',
                '--message-font-size': '1rem',
                '--input-font-size': '1rem',
                '--title-font-size': '1.5rem',
                '--button-font-size': '1rem'
            },
            large: {
                '--base-font-size': '18px',
                '--message-font-size': '1.1rem',
                '--input-font-size': '1.1rem',
                '--title-font-size': '1.8rem',
                '--button-font-size': '1.1rem'
            }
        };

        this.init();
    }

    init() {
        const savedSize = localStorage.getItem('fontSize') || 'medium';
        this.setFontSize(savedSize);
    }

    setFontSize(size) {
        if (this.fontSizes[size]) {
            localStorage.setItem('fontSize', size);
            this.applyFontSize(this.fontSizes[size]);
            
            // Update any font size selectors in the UI
            const fontSelector = document.querySelector('select[name="font_size"]');
            if (fontSelector) {
                fontSelector.value = size;
            }

            // Dispatch event for other components to react
            window.dispatchEvent(new CustomEvent('fontsizechange', { detail: { size } }));
        }
    }

    applyFontSize(sizes) {
        for (const [property, value] of Object.entries(sizes)) {
            document.documentElement.style.setProperty(property, value);
        }
    }

    getCurrentSize() {
        return localStorage.getItem('fontSize') || 'medium';
    }
}

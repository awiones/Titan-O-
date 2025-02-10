class ThemeManager {
    constructor() {
        this.darkTheme = {
            '--background-color': '#1a1a1a',
            '--text-color': '#ffffff',
            '--input-border': '#333333',
            '--input-background': '#2d2d2d',
            '--button-background': '#4a4a4a',
            '--button-text': '#ffffff',
            '--hover-background': '#3d3d3d'
        };

        this.lightTheme = {
            '--background-color': '#ffffff',
            '--text-color': '#000000',
            '--input-border': '#000000',
            '--input-background': '#ffffff',
            '--button-background': '#000000',
            '--button-text': '#ffffff',
            '--hover-background': '#333333'
        };

        this.init();
    }

    init() {
        // Check for saved theme preference
        const savedTheme = localStorage.getItem('theme') || 'system';
        this.setTheme(savedTheme);

        // Watch for system theme changes
        if (window.matchMedia) {
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
                if (localStorage.getItem('theme') === 'system') {
                    this.applyThemeColors(e.matches ? this.darkTheme : this.lightTheme);
                }
            });
        }
    }

    setTheme(theme) {
        localStorage.setItem('theme', theme);
        
        switch (theme) {
            case 'dark':
                this.applyThemeColors(this.darkTheme);
                break;
            case 'light':
                this.applyThemeColors(this.lightTheme);
                break;
            case 'system':
                if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                    this.applyThemeColors(this.darkTheme);
                } else {
                    this.applyThemeColors(this.lightTheme);
                }
                break;
        }

        // Update any theme selectors in the UI
        const themeSelector = document.querySelector('select[name="theme"]');
        if (themeSelector) {
            themeSelector.value = theme;
        }

        // Dispatch event for other components to react
        window.dispatchEvent(new CustomEvent('themechange', { detail: { theme } }));
    }

    applyThemeColors(colors) {
        for (const [property, value] of Object.entries(colors)) {
            document.documentElement.style.setProperty(property, value);
        }
    }

    getCurrentTheme() {
        return localStorage.getItem('theme') || 'system';
    }

    isDarkMode() {
        const theme = this.getCurrentTheme();
        if (theme === 'dark') return true;
        if (theme === 'light') return false;
        return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    }
}

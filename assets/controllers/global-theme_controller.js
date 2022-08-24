import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = [ 'themeToggle' ];

    connect() {
        const toggle = this.themeToggleTarget;
        toggle.onclick = this.themeToggle.bind(this);
        this.getCurrentTheme();
        this.setTheme();
    }

    getCurrentTheme() {
        this.currentTheme = localStorage.getItem('theme');
        if (!this.currentTheme) {
            this.currentTheme = "light";
        }
        // Ensure our theme is either dark/light.
        if ((this.currentTheme !== "light") && (this.currentTheme !== "dark")) {
            this.currentTheme = "light";
        }
        return this.currentTheme;
    }

    toggleText() {
        const toggle = this.themeToggleTarget;
        const theme = this.getCurrentTheme();
        if (theme === "light") {
            toggle.innerHTML = "Dark Mode";
        }  else {
            toggle.innerHTML = "Light Mode";
        }
    }

    themeToggle(event) {
        if (this.currentTheme === "light") {
            this.currentTheme = "dark";
        } else {
            this.currentTheme = "light";
        }

        localStorage.setItem('theme', this.currentTheme);
        this.setTheme();
    }

    setTheme() {
        if (this.currentTheme === "light") {
            document.querySelector('html').style.filter = "invert(0%)";
        } else {
            document.querySelector('html').style.filter = "invert(100%)";
        }
        this.toggleText();
    }
}

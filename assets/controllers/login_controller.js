import { Controller } from '@hotwired/stimulus';
import { Modal, Spinner } from 'bootstrap';
import { Notification } from './notification.js';
import { Theme } from './theme.js';

export default class extends Controller {
    static targets = [ 'form', 'capsWarning' ];
    static values = {
        token: String,
    }

    connect() {

        let inputEmail = document.querySelector('#inputEmail');
        let inputPassword = document.querySelector('#inputPassword');

        this.detectCapsLock(inputEmail);
        this.detectCapsLock(inputPassword);

        let form = this.formTarget;
        form.addEventListener('submit', (event) => {
            // In order to display a loading spinner on authentication,
            // We have to append the form to the document body, then
            // hide it. This is some sort of JavaScript DOM magick.
            let theme = new Theme();
            theme.displayLoadingSpinner();
            document.body.appendChild(form);
            form.style.display = "none";
        });
    }

    detectCapsLock(input) {
        input.addEventListener('keydown', (event) => {
            let p = this.capsWarningTarget;
            if (event.getModifierState("CapsLock")) {
                p.textContent = "Caps Lock On";
            } else {
                p.textContent = "";
            }
        });
    }
}

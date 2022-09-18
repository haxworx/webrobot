import { Controller } from '@hotwired/stimulus';
import { Modal, Spinner } from 'bootstrap';
import { Notification } from './notification.js';

export default class extends Controller {
    static targets = ['modal', 'spinner', 'confirm', 'button'];
    static values = {
        token: String,
    }

    confirm(event) {
        event.preventDefault();
        let confirmText = this.confirmTarget;
        document.addEventListener('keyup', (event) => {
            if (confirmText.value === "yes i understand completely") {
                this.buttonTarget.classList.remove("disabled");
            } else {
                this.buttonTarget.classList.add("disabled");
            }
        });
        this.modal = new Modal(this.modalTarget);
        confirmText.value = "";
        this.buttonTarget.classList.add("disabled");
        this.modal.show();
    }
    remove(event) {
        event.preventDefault();

        this.removeAccount();
        this.spinner = this.spinnerTarget;
        if(this.spinner !== undefined) {
            this.spinner.classList.remove('visually-hidden');
        }
        this.modal.hide();
    }

    removeAccount() {
        let token = this.tokenValue;
        
        fetch('/delete/account', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'token=' + token,
        })
        .then(response => {
            if (!response.ok) {
                let notification = new Notification("A network error occurred", true);
                notification.show();
            }
            window.location.href = '/';
        })
        .catch((error) => {
            let notification = new Notification("An error has occurred.", true)
            console.error('Error:', error);
        });
    }
}

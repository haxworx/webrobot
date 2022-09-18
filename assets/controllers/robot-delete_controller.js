import { Controller } from '@hotwired/stimulus';
import { Modal, Spinner } from 'bootstrap';

export default class extends Controller {
    static targets = ['modal', 'spinner', 'confirm', 'button'];
    static values = {
        token: String,
        botId: Number,
    }

    confirm(event) {
        event.preventDefault();
        let confirmText = this.confirmTarget;
        document.addEventListener('keyup', (event) => {
            if (confirmText.value === "delete me") {
                console.log(this.buttonTarget);
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

        this.removeCrawler();
        this.spinner = this.spinnerTarget;
        if(this.spinner !== undefined) {
            this.spinner.classList.remove('visually-hidden');
        }
        this.modal.hide();
    }

    removeCrawler() {
        let botId = this.botIdValue;
        let token = this.tokenValue;

        let save_button = document.getElementById('robot_schedule_save')
        if (save_button) {
            save_button.disabled = true;
        }
        let delete_button = document.getElementById('robot_schedule_delete');
        if (delete_button) {
            delete_button.disabled = true;
        }

        fetch('/robot/schedule/remove/' + botId, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'botId=' + botId + '&token=' + token,
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response');
            }
            window.location.href = '/';
        })
        .catch((error) => {
            console.error('Error:', error);
        });
    }
}

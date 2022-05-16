import { Controller } from '@hotwired/stimulus';
import { Modal, Spinner } from 'bootstrap';

export default class extends Controller {
    static targets = ['modal', 'spinner', 'btn_save', 'btn_delete'];
    static values = {
        token: String,
        botId: Number,
    }
    confirm(event) {
        event.preventDefault();
        this.modal = new Modal(this.modalTarget);
        this.modal.show();
    }
    remove(event) {
        event.preventDefault();

        this.removeCrawler();
        this.spinner = this.spinnerTarget;
        this.spinner.classList.remove('visually-hidden');
        this.modal.hide();
    }

    removeCrawler() {
        let botId = this.botIdValue;
        let token = this.tokenValue;

        let save_button = document.getElementById('robot_schedule_save')
        save_button.disabled = true;
        let delete_button = document.getElementById('robot_schedule_delete');
        delete_button.disabled = true;

        let xhr = new XMLHttpRequest();
        xhr.open('POST', '/robot/schedule/remove/' + botId, true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        xhr.onreadystatechange = function() {
            if ((xhr.readyState == XMLHttpRequest.DONE) && (xhr.status === 200)) {
                window.location.href='/';
            }
        }
        xhr.send('botId=' + botId + '&token=' + token);
    }
}

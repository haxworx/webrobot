import { Controller } from '@hotwired/stimulus';
import { Modal } from 'bootstrap';

export default class extends Controller {
    static targets = [ 'modal' ];

    connect() {
        let accepted = localStorage.getItem('legal');
        if ((!accepted) || (accepted !== 'accepted')) { 
            this.modal = new Modal(this.modalTarget);
            this.modal.show();
        }
    }

    accept() {
        localStorage.setItem('legal', 'accepted');
        this.modal.hide();
    }

    reject() {
        window.location.href="about:blank";
    }
}

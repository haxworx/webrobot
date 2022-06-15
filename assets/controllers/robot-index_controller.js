import { Controller } from '@hotwired/stimulus';
import { Modal, Spinner } from 'bootstrap';
import { updateCrawlers } from './local.js';

export default class extends Controller {
    static targets = [];
    static values = {
    };

    updateState() {
        updateCrawlers();
    }

    connect() {
        this.updateState();
        this.interval = setInterval(this.updateState.bind(this), 5000); 
    }
}

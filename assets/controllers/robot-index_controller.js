import { Controller } from '@hotwired/stimulus';
import { Modal, Spinner } from 'bootstrap';
import { Crawlers } from './crawlers.js';

export default class extends Controller {
    static targets = [];
    static values = {
    };

    updateState() {
        let crawlers = new Crawlers();
        crawlers.update();
    }

    connect() {
        this.updateState();
        this.interval = setInterval(this.updateState.bind(this), 5000); 
    }
}

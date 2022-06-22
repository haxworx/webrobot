import { Controller } from '@hotwired/stimulus';
import { Modal, Spinner } from 'bootstrap';
import { Crawlers } from './crawlers.js';
import { Notification } from './notification.js';

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
        this.interval = setInterval(this.updateState, 5000);

        // Notification test.
        return;
        let notification = new Notification('Test', true);
        for (let i = 0; i < 10; i++) {
            notification.show();
        }
    }
}

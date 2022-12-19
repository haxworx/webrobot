import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['pre'];
    static values = {
        botId: Number,
    }

    connect() {
    }

    show(event) {
        const pre = this.preTarget;
        const recordId = event.params['id'];
        const botId = this.botIdValue || event.params['botId'];

        fetch('/robot/records/download/' + botId + '/record/' + recordId, {
            method: 'GET',
        })
        .then(response => response.text())
        .then(data => {
            pre.textContent = data;
        })
        .catch((error) => {
            if (this.interval) {
                clearInterval(this.interval);
                this.interval = null;
            }
            console.error('Error:', error);
            let notification = new Notification("There was a network error.", true);
            notification.show();
        });
    }

    download (event) {
        const recordId = event.params.id;
        const botId = this.botIdValue || event.params['botId'];
        window.location.href = '/robot/records/download/' + botId + '/record/' + recordId;
    }
}

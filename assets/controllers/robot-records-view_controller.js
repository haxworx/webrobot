import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['frame'];
    static values = {
        botId: Number,
    }

    connect() {
        const frame = this.frameTarget;
    }

    show(event) {
        const frame = this.frameTarget;
        const recordId = event.params['id'];
        const botId = this.botIdValue || event.params['botId'];
        frame.src = '/robot/records/show/' + botId + '/record/' + recordId;
    }

    download (event) {
        const recordId = event.params.id;
        const botId = this.botIdValue || event.params['botId'];
        window.location.href = '/robot/records/download/' + botId + '/record/' + recordId;
    }
}

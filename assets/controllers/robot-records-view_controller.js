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
        const botId = this.botIdValue;
        frame.src = '/robot/records/view/' + botId + '/record/' + recordId;
    }
}

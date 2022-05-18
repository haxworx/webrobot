import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['botId', 'dates' ];
    static values = {
        botId: Number,
        baseUrl: String,
    }
    connect() {
        let searchParams = new URLSearchParams(window.location.search);
        const addressField = this.botIdTarget;
        addressField.addEventListener('change', (event) => {
            if (event.target.value) {
                searchParams.set('botId', event.target.value);
                searchParams.delete('scanDate');
                window.location.search = searchParams.toString();
            }
        });

        let logDates = null;
        if (this.hasDatesTarget) {
            logDates = this.datesTarget;
        }

        if (!logDates) return;

        logDates.addEventListener('change', (event) => {
            if (event.target.value) {
                window.location = this.baseUrlValue + '/' + searchParams.get('botId') + '/date/' + event.target.value;
            }
        });
    }
}

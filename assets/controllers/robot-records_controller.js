import { Controller } from '@hotwired/stimulus';
import { clearSelectElements, getRobots, getLaunches } from './local.js';

export default class extends Controller {
    static targets = ['botId', 'launches', 'launchesDiv' ];
    static values = {
        botId: Number,
        baseUrl: String,
    }
    
    connect() {
        const addressField = this.botIdTarget;
        const launchesField = this.launchesTarget;
        const launchesDiv = this.launchesDivTarget;

        getRobots(addressField);

        addressField.addEventListener('change', (event) => {
            if (event.target.value) {
                this.botIdValue = event.target.value;
                clearSelectElements(launchesField);
                getLaunches(launchesField, event.target.value);
                if (launchesDiv.classList.contains('visually-hidden')) {
                    launchesDiv.classList.remove('visually-hidden');
                }
            }
        });

        launchesField.addEventListener('change', (event) => {
            if ((event.target.value) && (this.botIdValue)) {
                window.location = this.baseUrlValue + '/' + this.botIdValue + '/launch/' + event.target.value + '/offset/' + 0;
            }
        });
    }
}

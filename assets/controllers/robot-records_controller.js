import { Controller } from '@hotwired/stimulus';
import { Crawlers } from './crawlers.js';
import { Theme } from './theme.js';

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

        let crawlers = new Crawlers(addressField);
        crawlers.getRobots();

        addressField.addEventListener('change', (event) => {
            if (event.target.value) {
                this.botIdValue = event.target.value;
                let crawlers = new Crawlers(launchesField);
                crawlers.clearSelectElements();
                crawlers.getLaunches(this.botIdValue);
                if (launchesDiv.classList.contains('visually-hidden')) {
                    launchesDiv.classList.remove('visually-hidden');
                }
            }
        });

        launchesField.addEventListener('change', (event) => {
            if ((event.target.value) && (this.botIdValue)) {
                // Show a spinner.
                let theme = new Theme();
                theme.displayLoadingSpinner();
                window.location = this.baseUrlValue + '/' + this.botIdValue + '/launch/' + event.target.value + '/offset/' + 0;
            }
        });
    }
}

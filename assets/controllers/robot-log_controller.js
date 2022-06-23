import { Controller } from '@hotwired/stimulus';
import { Crawlers } from './crawlers.js';
import { Notification } from './notification.js';

export default class extends Controller {
    static targets = ['botId', 'launches', 'launchesDiv', 'panel', 'token', 'spinner'];
    static values = {
        botId: Number,
        baseUrl: String,
        launchId: String,
        token: String,
    }

    connect() {
        const addressField = this.botIdTarget;
        const launchesField = this.launchesTarget;
        const launchesDiv = this.launchesDivTarget;
        const panel = this.panelTarget;

        let crawlers = new Crawlers(addressField);
        crawlers.getRobots();

        addressField.addEventListener('change', (event) => {
            if (event.target.value) {
                panel.innerHTML = "";
                this.botIdValue = event.target.value;
                let crawlers = new Crawlers(launchesField);
                crawlers.getLaunches(this.botIdValue);
                launchesDiv.classList.remove('visually-hidden');
            }
        });

        launchesField.addEventListener('change', (event) => {
            if ((event.target.value) && (this.botIdValue)) {
                this.launchIdValue = event.target.value;
                panel.classList.remove('visually-hidden');
                panel.innerHTML = "";
                this.getLog();
            }
        });
    }

    getLog() {
        if (this.interval) {
            clearInterval(this.interval);
        }
        this.dataTime = null;
        this.postObj = {
            bot_id: this.botIdValue,
            last_id: 0,
            launch_id: this.launchIdValue,
            token: this.tokenValue,
        };
        this.postData = JSON.stringify(this.postObj);
        this.downloadLog();
        this.interval = setInterval(this.downloadLog.bind(this), 5000);
    }

    downloadLog() {
        fetch('/robot/log/more', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json; charset=UTF-8',
            },
            body: JSON.stringify(this.postObj),
        })
        .then(response => response.json())
        .then(data => {
            this.postObj = data;
            let logPanel = this.panelTarget;
            let spinner = this.spinnerTarget;
            if (this.postObj.logs) {
                logPanel.innerHTML = logPanel.innerHTML + this.postObj.logs;
                logPanel.scrollTop = logPanel.scrollHeight;
                delete(this.postObj.logs);
                this.postData = JSON.stringify(this.postObj);
                this.dataTime = Math.floor(Date.now() / 1000);
                spinner.classList.remove('visually-hidden');

            }
            if ((this.dataTime) && (((Date.now() / 1000) - this.dataTime) >= 5.0)) {
                spinner.classList.add('visually-hidden');
            }
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
}

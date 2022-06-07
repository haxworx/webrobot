import { Controller } from '@hotwired/stimulus';
import { clearSelectElements, getRobots, getLaunches } from './local.js';

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

        getRobots(addressField);

        addressField.addEventListener('change', (event) => {
            if (event.target.value) {
                panel.innerHTML = "";
                this.botIdValue = event.target.value;
                getLaunches(launchesField, event.target.value);
                if (launchesDiv.classList.contains('visually-hidden')) {
                    launchesDiv.classList.remove('visually-hidden');
                }
            }
        });

        launchesField.addEventListener('change', (event) => {
            if ((event.target.value) && (this.botIdValue)) {
                this.launchIdValue = event.target.value;
                if (panel.classList.contains('visually-hidden')) {
                    panel.classList.remove('visually-hidden');
                }
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
        var self = this;
        let xhr = new XMLHttpRequest();
        xhr.open('POST', '/robot/log/more', true);
        xhr.setRequestHeader('Content-Type', 'application/json; charset=UTF-8');
        xhr.send(JSON.stringify(self.postObj));

        let logPanel = self.panelTarget;
        let spinner = self.spinnerTarget;
        xhr.onload = function() {
            if (xhr.status === 200) {
                self.postObj = JSON.parse(xhr.response);
                if (self.postObj.logs) {
                    logPanel.innerHTML = logPanel.innerHTML + self.postObj.logs;
                    logPanel.scrollTop = logPanel.scrollHeight;
                    delete(self.postObj.logs);
                    self.postData = JSON.stringify(self.postObj);
                    self.dataTime = Math.floor(Date.now() / 1000);
                    if (spinner.classList.contains('visually-hidden')) {
                        spinner.classList.remove('visually-hidden');
                    }
                }
                if ((self.dataTime) && (((Date.now() / 1000) - self.dataTime) >= 5.0)) {
                    if (!spinner.classList.contains('visually-hidden')) {
                        spinner.classList.add('visually-hidden');
                    }
                }
            } else {
                if (self.interval) {
                    clearInterval(self.interval);
                    self.interval = null;
                }
            }
        }
    }
}

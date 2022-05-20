import { Controller } from '@hotwired/stimulus';

function clearSelectElements(selectElement) {
    for (let i = selectElement.options.length - 1; i >= 0; i--) {
        if (selectElement[i].value != "") {
            selectElement.remove(i);
        } else {
            selectElement[i].selected = 'selected';
        }
    }
}

export default class extends Controller {
    static targets = ['botId', 'dates', 'datesDiv', 'panel', 'token', 'spinner'];
    static values = {
        botId: Number,
        baseUrl: String,
        scanDate: String,
        token: String,
    }

    getRobots(addressField) {
        let xhr = new XMLHttpRequest();
        xhr.open('GET', '/robot/query/all', true);
        xhr.setRequestHeader('Content-Type', 'application/json; charset=UTF-8');
        xhr.send();

        xhr.onload = function() {
            if (xhr.status === 200) {
                let robots = JSON.parse(xhr.response);
                robots.forEach(function (item, index) {
                    let option = document.createElement('option');
                    option.text = item['address'];
                    option.value = item['botId'];
                    addressField.appendChild(option);
                });
            }
        }
    }

    getDates(datesField, botId) {
        let xhr = new XMLHttpRequest();
        xhr.open('GET', '/robot/query/dates/'+ botId, true);
        xhr.setRequestHeader('Content-Type', 'application/json; charset=UTF=8');
        xhr.send();

        xhr.onload = function() {
            clearSelectElements(datesField);
            if (xhr.status === 200) {
                let dates = JSON.parse(xhr.response);
                dates.forEach(function (item, index) {
                    let option = document.createElement('option');
                    option.text = item;
                    option.value = item;
                    datesField.appendChild(option);
                });
            }
        }
    }

    connect() {
        const addressField = this.botIdTarget;
        const datesField = this.datesTarget;
        const datesDiv = this.datesDivTarget;
        const panel = this.panelTarget;

        this.getRobots(addressField);

        addressField.addEventListener('change', (event) => {
            if (event.target.value) {
                panel.innerHTML = "";
                this.botIdValue = event.target.value;
                this.getDates(datesField, event.target.value);
                if (datesDiv.classList.contains('visually-hidden')) {
                    datesDiv.classList.remove('visually-hidden');
                }
            }
        });

        datesField.addEventListener('change', (event) => {
            if ((event.target.value) && (this.botIdValue)) {
                this.scanDateValue = event.target.value;
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
            scan_date: this.scanDateValue,
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
                if (self.postObj['logs']) {
                    logPanel.innerHTML = logPanel.innerHTML + self.postObj['logs'];
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
            }
        }
    }
}

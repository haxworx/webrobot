import { Notification } from './notification.js';

export class Crawlers {
    constructor(field) {
        this.field = field;
    }

    clearSelectElements() {
        let selectElement = this.field;
        let length = selectElement.options.length;
        for (let i = length -1; i >= 0; i--) {
            if (selectElement.options[i].value != "") {
                selectElement.remove(i);
            }
        }
    }

    fuzzyTime(input) {
        let out = "n/a";
        if (input === null) return out;
        let date = new Date(input).valueOf() / 1000;
        let now = new Date().valueOf() / 1000;
        let secs = Math.floor(now) - Math.floor(date);
        if (secs < 3600) {
            let mins = Math.floor(secs / 60);
            out = mins + " minute" + (mins != 1 ? 's' : '') + ' ago';
        } else if ((secs > 3600) && (secs < 86400)) {
            let hours = Math.floor(secs / 3600);
            out = hours + " hour" + (hours != 1 ? 's' : '') + ' ago';
        } else {
            let days = Math.floor(secs / 86400);
            out = days + " day" + (days != 1 ? 's' : '') + ' ago';
        }
        return out;
    }

    timeFormat(input) {
        let out = "n/a";
        if (input === null) return out;

        let date = new Date(input);

        return date.toLocaleTimeString();
    }

    update() {
        let self = this;
        fetch('/robot/query/all', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json; charset=UTF-8',
            },
        })
        .then(response => response.json())
        .then(crawlers => {
            crawlers.every(crawler => {
                let address = document.querySelector('#bot' + crawler.botId + 'Address');
                let agent = document.querySelector('#bot' + crawler.botId + 'Agent');
                let start = document.querySelector('#bot' + crawler.botId + 'Start');
                let finish = document.querySelector('#bot' + crawler.botId + 'Finish');
                let state = document.querySelector('#bot' + crawler.botId + 'State');

                if ((!address) || (!agent) || (!start) || (!finish) || (!state)) {
                    self.contentError();
                    return false;
                }

                address.innerHTML = crawler.address;
                agent.innerHTML = crawler.agent;
                start.innerHTML = self.timeFormat(crawler.startTime);
                finish.innerHTML = self.fuzzyTime(crawler.endTime);

                if (crawler.IsRunning) {
                    state.innerHTML = "yes";
                } else {
                    state.innerHTML = "no";
                }
                return true;
            });
        })
        .catch((error) => {
            self.networkError(error);
        });
    }

    networkError(error) {
        let notification = new Notification('There was a network error.', true);
        notification.show();
        console.log('Error:', error);
    }

    contentError() {
        let notification = new Notification('There was a content error.', true);
        notification.show();
    }

    getRobots() {
        let self = this;
        fetch('/robot/query/all', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json; charset=UTF-8',
            },
        })
        .then(response => response.json())
        .then(data => {
            data.forEach(function (item, index) {
                let option = document.createElement('option');
                option.text = item.address;
                option.value = item.botId;
                self.field.appendChild(option);
            });
        })
        .catch((error) => {
            self.networkError(error);
        });
    }

    dateFormat(input) {
        if (input === null) return "n/a";
        let date = new Date(input);
        return date.toLocaleString();
    }

    getLaunches(botId) {
        let self = this;
        fetch('/robot/query/launches/' + botId, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json; charset=UTF-8',
            },
        })
        .then(response => response.json())
        .then(data => {
            data.forEach(function (item, index) {
                let option = document.createElement('option');
                option.text = self.dateFormat(item.startTime)+ ' to ' + self.dateFormat(item.endTime);
                option.value = item.id;
                self.field.appendChild(option);
            });
        })
        .catch((error) => {
            self.networkError(error);
        });
    }
}


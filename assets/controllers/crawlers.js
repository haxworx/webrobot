
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
            crawlers.forEach(function(crawler, index) {
                let address = document.getElementById('bot'+crawler.botId+'Address');
                let agent = document.getElementById('bot'+crawler.botId+'Agent');
                let start = document.getElementById('bot'+crawler.botId+'Start');
                let finish = document.getElementById('bot'+crawler.botId+'Finish');
                let state = document.getElementById('bot'+crawler.botId+'State');

                address.innerHTML = crawler.address;
                agent.innerHTML = crawler.agent;
                start.innerHTML = self.timeFormat(crawler.startTime);
                finish.innerHTML = self.fuzzyTime(crawler.endTime);

                if (crawler.IsRunning) {
                    state.innerHTML = "yes";
                } else {
                    state.innerHTML = "no";
                }
            });
        })
        .catch((error) => {
            console.error('Error:', error);
        });
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
            console.error('Error:', error);
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
            console.error('Error:', error);
        });
    }
}


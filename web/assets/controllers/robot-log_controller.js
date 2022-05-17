import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['crawl', 'dates', 'panel'];
    static values = {
        botId: Number,
        token: String,
        scanDate: String,
        lastId: Number
    }
    connect() {
        const addressField = this.crawlTarget;
        let logDates = null;
        if (this.hasDatesTarget) {
            logDates = this.datesTarget;
        }

        let searchParams = new URLSearchParams(window.location.search);
        addressField.addEventListener('change', (event) => {
            if (event.target.value) {
                searchParams.set('botId', event.target.value);
                searchParams.delete('scanDate');
                window.location.search = searchParams.toString();
            }
        });

        if (!logDates) return;

        logDates.addEventListener('change', (event) => {
            if (event.target.value) {
                searchParams.set('scanDate', event.target.value);
                window.location.search = searchParams.toString();
            }
        });
        if (this.hasPanelTarget) {
            this.update(this.botIdValue, this.lastIdValue, this.scanDateValue, this.tokenValue);
        }
    }

    update(botId, lastId, scanDate, token) {
        let postObj = {
            bot_id: botId,
            last_id: lastId,
            scan_date: scanDate,
            token: token,
        }

        let logPanel = this.panelTarget;
        logPanel.scrollTop = logPanel.scrollHeight;

        let postData = JSON.stringify(postObj);

        let interval = setInterval(function() {
            let xhr = new XMLHttpRequest();
            xhr.open('POST', '/robot/log/more', true);
            xhr.setRequestHeader('Content-Type', 'application/json; charset=UTF-8');
            xhr.send(postData);

            xhr.onload = function() {
                if (xhr.status === 200) {
                    postObj = JSON.parse(xhr.response)
                    if (postObj['logs']) {
                        postData = JSON.stringify(postObj);
                        logPanel.innerHTML = logPanel.innerHTML + postObj['logs'];
                        logPanel.scrollTop = logPanel.scrollHeight;
                        // Show spinner if we have "live" log data.
                        let spinner = document.getElementById('spinner');
                        spinner.classList.remove('visually-hidden');
                    }
                }
            }
        }, 5000);
    }
}

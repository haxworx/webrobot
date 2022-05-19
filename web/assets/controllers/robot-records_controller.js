import { Controller } from '@hotwired/stimulus';

function removeOptions(selectElement) {
    let length = selectElement.options.length - 1;

    for (let i = length; i >= length; i--) {
        if (selectElement[i].value !== '') {
            selectElement.remove(i);
        }
    }
}

export default class extends Controller {
    static targets = ['botId', 'dates', 'datesDiv' ];
    static values = {
        botId: Number,
        baseUrl: String,
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
        let searchParams = new URLSearchParams(window.location.search);
        const addressField = this.botIdTarget;
        const datesField = this.datesTarget;
        const datesDiv = this.datesDivTarget;

        this.getRobots(addressField);

        addressField.addEventListener('click', (event) => {
            if (event.target.value) {
                this.botIdValue = event.target.value;
                removeOptions(datesField);
                this.getDates(datesField, event.target.value);
                if (datesDiv.classList.contains('visually-hidden')) {
                    datesDiv.classList.remove('visually-hidden');
                }
            }
        });

        datesField.addEventListener('click', (event) => {
            if ((event.target.value) && (this.botIdValue)) {
                window.location = this.baseUrlValue + '/' + this.botIdValue + '/date/' + event.target.value;
            }
        });
    }
}

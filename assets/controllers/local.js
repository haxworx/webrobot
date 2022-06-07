
export function clearSelectElements(selectElement) {
    let length = selectElement.options.length;
    for (let i = length -1; i >= 0; i--) {
        if (selectElement.options[i].value != "") {
            selectElement.remove(i);
        }
    }
}

export function getRobots(addressField) {
let xhr = new XMLHttpRequest();
    xhr.open('GET', '/robot/query/all', true);
    xhr.setRequestHeader('Content-Type', 'application/json; charset=UTF-8');
    xhr.send();

    xhr.onload = function() {
        if (xhr.status === 200) {
            let robots = JSON.parse(xhr.response);
            robots.forEach(function (item, index) {
                let option = document.createElement('option');
                option.text = item.address;
                option.value = item.botId;
                addressField.appendChild(option);
            });
        }
    }
}

export function getLaunches(launchesField, botId) {
    let xhr = new XMLHttpRequest();
    xhr.open('GET', '/robot/query/launches/'+ botId, true);
    xhr.setRequestHeader('Content-Type', 'application/json; charset=UTF=8');
    xhr.send();

    xhr.onload = function() {
        clearSelectElements(launchesField);
        if (xhr.status === 200) {
            let dates = JSON.parse(xhr.response);
            dates.forEach(function (item, index) {
                let option = document.createElement('option');
                let date = new Date(item.startTime);
                option.text = item.startTime + ' to ' + item.endTime;
                option.value = item.id;
                launchesField.appendChild(option);
            });
        }
    }
}


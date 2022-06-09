
export function clearSelectElements(selectElement) {
    let length = selectElement.options.length;
    for (let i = length -1; i >= 0; i--) {
        if (selectElement.options[i].value != "") {
            selectElement.remove(i);
        }
    }
}

export function getRobots(addressField) {
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
            addressField.appendChild(option);
        });
    })
    .catch((error) => {
        console.error('Error:', error);
    });
}

function dateFormat(input) {
    if (input === null) return "n/a";
    let date = new Date(input);
    return date.toLocaleString();
}

export function getLaunches(launchesField, botId) {
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
            option.text = dateFormat(item.startTime)+ ' to ' + dateFormat(item.endTime);
            option.value = item.id;
            launchesField.appendChild(option);
        });
    })
    .catch((error) => {
        console.error('Error:', error);
    });
}



function logsUpdate(botId, lastId, scanDate) {
    let postObj = {
        bot_id: botId,
        last_id: lastId,
        scan_date: scanDate,
    }

    let logPanel = document.getElementById('log-panel');

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
                }
            }
        }
    }, 5000);
}

window.onload = function() {
    const addressField = document.querySelector('#robot_log_crawl');
    const logDates = document.querySelector('#robot_log_dates');

    let searchParams = new URLSearchParams(window.location.search);
    addressField.addEventListener('change', (event) => {
        if (event.target.value) {
            searchParams.set('botId', event.target.value);
            searchParams.delete('scanDate');
            window.location.search = searchParams.toString();
        }
    });

    // Date selected only rendered when a valid botId is.

    if (!logDates) return;

    logDates.addEventListener('change', (event) => {
        if (event.target.value) {
            searchParams.set('scanDate', event.target.value);
            window.location.search = searchParams.toString();
        }
    });

    let botId = searchParams.get('botId');
    let scanDate = searchParams.get('scanDate');
    let logPanel = document.getElementById('log-panel');

    if ((!botId) || (!scanDate)) return;

    logPanel.scrollTop = logPanel.scrollHeight;
}

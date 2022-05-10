
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

    logDates.addEventListener('change', (event) => {
        if (event.target.value) {
            searchParams.set('scanDate', event.target.value);
            window.location.search = searchParams.toString();
        }
    });

}

let weekdays = [ 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' ];

window.onload = function() {
    let radio_weekly = document.getElementById('weekly');
    let radio_daily = document.getElementById('daily');
    let address = document.getElementById('address');
    let agent = document.getElementById('agent');
    let time = document.getElementById('time');
    let delay = document.getElementById('delay');
    let retry_max = document.getElementById('retry_max');
    let ignore_query = document.getElementById('ignore_query');
    let import_sitemaps = document.getElementById('import_sitemaps');
    let content = document.getElementsByName('content_types[]');

    for (let i = 0; i < weekdays.length; i++) {
        let weekday = document.getElementById(weekdays[i]);
        weekday.onclick = radio_weekday_clicked;
    }

    for (let i = 0; i < content.length; i++) {
        content[i].onclick = reset_error;
    }

    radio_weekly.onclick = radio_weekly_clicked;
    radio_daily.onclick = radio_daily_clicked;
    address.onclick = reset_error;
    agent.onclick = reset_error;
    time.onclick = reset_error;
    delay.onclick = reset_error;
    retry_max.onclick = reset_error;
    ignore_query.onclick = reset_error;
    import_sitemaps.onclick = reset_error;
}

function reset_error() {
   let p = document.getElementById('create_error');
   p.style.display = "none";
}

function display_error(message) {
    let p = document.getElementById('create_error');
    p.style.display = "block";
    p.innerHTML = message;
}

function validate_address(address) {
    let ok = true;
    let url = address.value.toLowerCase();
    let val = url.search(/^(http|https):\/\/(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z0-9][a-z0-9-]{0,61}[a-z0-9]$/);
    if (val == -1) {
        ok = false;
        display_error("Please enter a valid address.");
    }
    return ok;
}

function validate_agent(agent) {
    let ok = true;
    let user_agent = agent.value;
    let val = user_agent.search(/^[A-Za-z0-9\._\/]+\/\d+\.\d+$/);
    if (val == -1) {
        ok = false;
        display_error("Please enter a valid user agent.");
    }
    return ok;
}

function validate_time(time) {
    if (time.value === "") {
        display_error("Please enter a valid time.");
        return false;
    }
    return true;
}

function create_validate(form) {
    let address = form.address;
    let agent = form.agent;
    let time = form.time;
    let weekly = form.weekly;
    let daily = form.daily;
    let content = document.getElementsByName('content_types[]');

    if (!validate_address(address)) {
        return false;
    }

    if (!validate_agent(agent)) {
        return false;
    }

    if (!validate_time(time)) {
        return false;
    }

    let haveContent = false;
    for (let i = 0; i < content.length; i++) {
        if (content[i].checked) {
            haveContent = true;
            break;
        }
    }
    if (!haveContent) {
        display_error("Please select at least one content type.");
        return false;
    }

    if (daily.checked == true) {
        return true;
    }

    let weekdays = document.getElementsByName('weekly');
    let haveWeekday = false;
    for (let i = 0; i < weekdays.length; i++) {
        if (weekdays[i].checked) {
            haveWeekday = true;
            break;
        }
    }
    if (!haveWeekday) {
        display_error("Please select a week day.");
    }
    return haveWeekday;
}

function radio_weekly_clicked() {
    reset_error();
    let radio_monday = document.getElementById('monday');
    radio_monday.checked = true;
}

function radio_daily_clicked() {
    reset_error();
    for (let i = 0; i < weekdays.length; i++) {
        let weekday = document.getElementById(weekdays[i]);
        weekday.checked = false;
    }
}

function radio_weekday_clicked() {
   reset_error();
   let weekly = document.getElementById('weekly');
   let daily = document.getElementById('daily');
   daily.checked = false;
   weekly.checked = true;
}

window.onload = function() {
    var radio_weekly = document.getElementById('weekly');
    var radio_daily = document.getElementById('daily');

    radio_weekly.onclick = radio_weekly_clicked;
    radio_daily.onclick = radio_daily_clicked;
}

function validate_address(address) {
    if (address.value === "") {
    return false;
    }
    return true;
}

function validate_agent(agent) {
    if (agent.value === "") {
        return false;
    }
    return true;
}

function validate_time(time) {
    if (time.value === "") {
    return false;
    }
    return true;
}

function create_validate(form) {
    var address = form.address;
    var agent = form.agent;
    var time = form.time;
    var weekly = form.weekly;
    var daily = form.daily;

    if (!validate_address(address)) {
        return false;
    }

    if (!validate_time(time)) {
        return false;
    }

    if (!validate_agent(time)) {
        return false;
    }

    if (daily.checked == true) {
        return true;
    }

    var weekdays = document.getElementsByName('weekly');
    var haveWeekday = false;
    for (var i = 0; i < weekdays.length; i++) {
        if (weekdays[i].checked) {
            haveWeekday = true;
            break;
        }
    }
    return haveWeekday;
}

function radio_weekly_clicked() {
    var weekly_block = document.getElementById('weekly_block');
    weekly_block.style.display = "block";
}

function radio_daily_clicked() {
    var weekly_block = document.getElementById('weekly_block');
    weekly_block.style.display = "none";
}

window.onload = function() {
    var radio_weekly = document.getElementById('weekly');
    var radio_daily = document.getElementById('daily');
    var address = document.getElementById('address');
    var agent = document.getElementById('agent');
    var time = document.getElementById('time');

    radio_daily.checked = true;
    radio_weekly.onclick = radio_weekly_clicked;
    radio_daily.onclick = radio_daily_clicked;
    address.onclick = reset_error;
    agent.onclick = reset_error;
    time.onclick = reset_error;
}

function reset_error() {
   var p = document.getElementById('create_error');
   p.style.display = "none";
}

function display_error(message) {
    var p = document.getElementById('create_error');
    p.style.display = "block";
    p.innerHTML = message;
}

function validate_address(address) {
    if (address.value === "") {
	display_error("Please enter a valid address.");
        return false;
    }
    return true;
}

function validate_agent(agent) {
    if (agent.value === "") {
	display_error("Please enter a valid user agent.");
        return false;
    }
    return true;
}

function validate_time(time) {
    if (time.value === "") {
	display_error("Please enter a valid time.");
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

    if (!validate_agent(agent)) {
        return false;
    }

    if (!validate_time(time)) {
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
    if (!haveWeekday) {
        display_error("Please select a week day.");
    }
    return haveWeekday;
}

function radio_weekly_clicked() {
    var weekly_block = document.getElementById('weekly_block');
    reset_error();
    weekly_block.style.display = "block";
}

function radio_daily_clicked() {
    var weekly_block = document.getElementById('weekly_block');
    reset_error();
    weekly_block.style.display = "none";
}

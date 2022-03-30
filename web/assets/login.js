function reset_error() {
   let p = document.getElementById('login_error');
   p.style.display = "none";
}

function display_error(message) {
    let p = document.getElementById('login_error');
    p.style.display = "block";
    p.innerHTML = message;
}

function validate_username(username) {
    if (username.value === "") {
        display_error("Please enter a user name.");
        return false;
    }
    return true;
}

function validate_password(password) {
    if (password.value === "") {
        display_error("Please enter a password.");
        return false;
    }
    return true;
}

function login_validate(form) {
    let username = form.username;
    let password = form.password;

    username.onclick = reset_error;
    password.onclick = reset_error;

    if (!validate_username(username)) {
        return false;
    }

    if (!validate_password(password)) {
        return false;
    }
    return true;
}


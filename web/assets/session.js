
let timeout = 1000 * 60 * 10;

setTimeout(sessionRefresh, timeout);

function sessionRefresh() {
    const xmlhttp = new XMLHttpRequest();
    xmlhttp.open("GET", "/session/");
    xmlhttp.send();
    setTimeout(sessionRefresh, timeout);
}

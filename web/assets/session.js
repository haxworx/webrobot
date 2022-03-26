
let timeout = 1000 * 60 * 15;

setTimeout(sessionRefresh, timeout);

function sessionRefresh() {
    const xmlhttp = new XMLHttpRequest();
    xmlhttp.open("GET", "/session.php");
    xmlhttp.send();
    setTimeout(sessionRefresh, timeout);
}

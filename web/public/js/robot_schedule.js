var myModal = document.getElementById('myModal')
var myInput = document.getElementById('myInput')

myModal.addEventListener('shown.bs.modal', function () {
  myInput.focus()
})

// Show spinner, send POST request, redirect on success.
function removeCrawler(botId, token) {
    let spinner = document.getElementById('spinner');
    spinner.classList.remove('visually-hidden');
    let save_button = document.getElementById('robot_schedule_save')
    save_button.disabled = true;
    let delete_button = document.getElementById('robot_schedule_delete');
    delete_button.disabled = true;

    let xhr = new XMLHttpRequest();
    xhr.open('POST', '/robot/schedule/remove/' + botId, true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    xhr.onreadystatechange = function() {
        if ((xhr.readyState == XMLHttpRequest.DONE) && (xhr.status === 200)) {
            window.location.href='/';
        }
    }
    xhr.send('botId=' + botId + '&token=' + token);
}

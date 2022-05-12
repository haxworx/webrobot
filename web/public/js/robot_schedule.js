var myModal = document.getElementById('myModal')
var myInput = document.getElementById('myInput')

myModal.addEventListener('shown.bs.modal', function () {
  myInput.focus()
})

// Show spinner then redirect to delete.
function removeCrawler(deletePath) {
    let spinner = document.getElementById('spinner');
    spinner.classList.remove('visually-hidden');
    window.location.href=deletePath;
}

const NOTIFICATION_URL = "/flashes";

export class Notification {
    constructor(message){
        this.messageText = message;
        this.message = {
            message: this.messageText,
        }
    }

    // Use Javascript to append Bootstrap 5 alert to our main content.
    show() {
        let container = document.querySelector('#main');
        let div = document.createElement('div');
        div.classList.add("alert", "alert-info", "alter-dismissible", "fade", "show");
        div.innerHTML = this.messageText;
        let button = document.createElement('button');
        button.setAttribute('data-bs-dismiss', 'alert');
        button.setAttribute('aria-label', 'Close');
        button.classList.add("btn-close");
        div.prepend(button);
        container.prepend(div);
    }

    getUrl() {
        return NOTIFICATION_URL;
    }

    // Send a POST request to use server-side generated notification.
    send() {
        let self = this;
        fetch(this.getUrl(), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json; charset=UTF-8',
            },
            body: JSON.stringify(this.message),
        })
        .then(response => {
            if (!response.ok) {
                throw "Invalid response";
            }
        })
        .catch((error) => {
            console.error('Error:', error);
        });
    }
}

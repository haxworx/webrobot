const NOTIFICATION_URL = "/flashes";

export class Notification {
    constructor(message, warning = false){
        this.warning = warning;
        this.messageText = message;
        this.message = {
            message: this.messageText,
        }
    }

    // Use Javascript to append Bootstrap 5 alert to our main content.
    show() {
        let container = document.querySelector('#main');
        let div = document.createElement('div');
        div.classList.add("alert", "alter-dismissible", "d-flex", 'align-items-center', "fade", "show");
        if (!this.warning) {
            div.classList.add('alert-info');
        } else {
            div.classList.add('alert-warning');
        }
        div.innerHTML = this.messageText;
        let button = document.createElement('button');
        button.setAttribute('data-bs-dismiss', 'alert');
        button.setAttribute('aria-label', 'Close');
        button.classList.add("btn-close", "text-right");
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

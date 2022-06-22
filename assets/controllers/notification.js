const NOTIFICATION_URL = "/flashes";

export class Notification {
    constructor(message){
        this.message = {
            message: message,
        }
    }

    getUrl() {
        return NOTIFICATION_URL;
    }

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

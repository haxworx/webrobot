import { Controller } from '@hotwired/stimulus';
import { Modal, Spinner } from 'bootstrap';
import { Notification } from './notification.js';

export default class extends Controller {
    static values = {
        token: String,
    }

    generate(event) {
        event.preventDefault();
        let token = this.tokenValue;
        let o = {
            token: this.tokenValue,
        };

        fetch('/api/key/regenerate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(o),
        })
        .then(response => response.json())
        .then(data => {
            let apiKey = data['api-key'];
            let div = document.querySelector('#api_token');
            div.textContent = apiKey;
        })
        .catch((error) => {
            let notification = new Notification("An error has occurred.", true)
            console.error('Error:', error);
        });
    }
}

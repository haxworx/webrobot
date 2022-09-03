export class Theme {
    constructor() {

    }

    displayLoadingSpinner() {
        let main = document.querySelector('#main');
        main.innerHTML = "";
        main.classList.add("text-center");
        let div = document.createElement("div");
        div.classList.add("spinner-border");
        div.classList.add("text-primary");
        div.classList.add("text-center");
        div.setAttribute("role", "status");
        main.appendChild(div);
    }
}

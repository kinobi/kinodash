export default class Item extends HTMLElement {
    constructor() {
        super();
        this._payload = null;
        this.root = this.attachShadow({mode: 'open'});
    }

    select() {
        const container = this.root.querySelector('div');
        container.style.backgroundColor = '#3273dc';
        container.style.color = '#ffffff';
    }

    reset() {
        const container = this.root.querySelector('div');
        container.style.backgroundColor = '#ffffff';
        container.style.color = '#4a4a4a';
    }

    run() {
        console.log(this._payload);
    }

    set payload(data) {
        this._payload = data;
        this.root.innerHTML = `
            <style>
            div {
                text-align: left;
                padding: .5em 2em .5em 3em;
            }
            </style>
            <div>
               ${this._payload.title}
            </div>
        `
    }
}

customElements.define('kinodash-launcher-item', Item);

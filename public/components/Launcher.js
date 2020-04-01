import './Launcher/Item.js';

export default class Launcher extends HTMLElement {
    constructor() {
        super();

        this.innerHTML = `
            <div class="field" style="width: 75%; margin: 0 auto;">
                <p class="control has-icons-left">
                    <input id="launcher-input" class="input is-medium" type="text" placeholder="" style="background-color: #ffffffaa;" autofocus autocomplete="off">
                    <span class="icon is-small is-left">
                        <i class="fas fa-search"></i>
                    </span>
                </p>
                <div class="dropdown" id="launcher-list" style="width: 100%;">
                    <div class="dropdown-menu" style="width: 100%;">
                        <div class="dropdown-content"></div>
                    </div>
                </div>
            </div>
        `;

        this.input = this.querySelector('#launcher-input');
        this.list = this.querySelector('#launcher-list');
        this.content = this.list.querySelector('.dropdown-content');

        this.contentVisible = false;
        this.currentFocus = -1;
    }

    hide() {
        this.contentVisible = false;
        this.list.classList.remove('is-active');
        this.content.innerHTML = '';
        this.currentFocus = -1;
    }

    show() {
        this.contentVisible = true;
        this.list.classList.add('is-active');
    }

    connectedCallback() {
        this.addEventListener('input', this.search);
        this.addEventListener('keydown', this.launch);
    }

    launch(e) {
        if (e.key === 'Escape') {
            this.input.value = '';
            this.hide();
            return;
        }

        if (e.key === 'Enter') {
            const value = this.input.value;

            // Bookmark Command mode run
            if (value[0] === ':') {
                const parts = value.split(' ', 3);
                const action = parts[0].substring(1);

                if (!action) {
                    return;
                }

                axios.post(`/bookmark/${action}`, {
                    url: parts[1] || null,
                    tags: parts[2] || null
                }).then(function (response) {
                    console.log(response);
                }).catch(function (error) {
                    console.log(error);
                });
            }

            if (this.contentVisible && this.currentFocus > -1) {
                const items = this.content.getElementsByTagName('kinodash-launcher-item');
                items[this.currentFocus].run();
            }

            return;
        }

        if (!this.contentVisible) {
            return;
        }

        if (e.key === 'ArrowUp') {
            this.currentFocus--;
            this.select();
            return;
        }

        if (e.key === 'ArrowDown') {
            this.currentFocus++;
            this.select();
            return;
        }

        console.log('LAUNCH => CURRENT=' + this.currentFocus + ' / KEY=' + e.key);
    }

    select() {
        const items = this.content.getElementsByTagName('kinodash-launcher-item');
        Array.from(items).forEach(item => {
            item.reset();
        });

        if (this.currentFocus < 0) {
            this.currentFocus = (items.length - 1);
        } else if (this.currentFocus >= items.length) {
            this.currentFocus = 0;
        }

        items[this.currentFocus].select();
    }

    search(e) {
        this.hide();
        this.autocomplete(e)
            .then(bookmarks => {
                if (!bookmarks || bookmarks.length === 0) {
                    return;
                }

                let i = 0;
                const separator = document.createElement('hr');
                separator.classList.add("dropdown-divider");
                bookmarks.forEach(bookmark => {
                    const item = document.createElement('kinodash-launcher-item');
                    item.payload = bookmark;

                    if (i % 2 === 1) {
                        this.content.appendChild(separator);
                    }
                    this.content.appendChild(item);
                    i++;
                });

                this.show();
            })
            .catch(err => console.log(err));
    }

    async autocomplete(input) {
        const value = this.input.value;
        this.hide();

        // Module Mode
        if (value[0] === '/') {
            console.log('MODULE ACTION');
            return;
        }

        // No value or Bookmark Command Mode
        if (!value || value[0] === ':') {
            return;
        }

        // Bookmark Search Mode
        const res = await axios.get('/bookmark/search', {
            params: {
                query: value
            }
        });

        return res.data.results || null;
    }
}

customElements.define('kinodash-launcher', Launcher);

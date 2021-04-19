import services from './Services'
import API from "./API";

class Session {
    /**
     * Construct Session
     * @return {Session}
     */
    constructor(adminController) {

        this.instanciated = false;
        this.bpm = 6;
        this.interactDebounce = 1000;
        this.adminController = adminController;
        this.alsoHere = [];

        adminController
            .onRefreshUi((e, domElement) => {

                if (!this.instanciated) {
                    this.instanciated = true;
                    this.init();
                }

                $('.js-admin-session--also-here:not(.js-admin-session--also-here--processed)')
                    .addClass('js-admin-session--also-here--processed')
                    .each((index, element) => {
                        this.alsoHere.push(element);
                    });
            });

        document
            .addEventListener('mousemove', () => {
                this.interact();
            });

        document
            .addEventListener('keyup', () => {
                this.interact();
            });

        document
            .addEventListener('click', () => {
                this.interact();
            });

        return this;
    }

    // --------------------------------------------------------------------------

    init() {
        this.adminController.log('Setting up heartbeat');
        this.pulse();
        setInterval(() => {
            this.pulse();
        }, (60 / this.bpm) * 1000);
    }

    // --------------------------------------------------------------------------

    async pulse() {
        let res = await services.apiRequest({
            method: 'put',
            url: API.session.heartbeat
        });

        this.alsoHere.map((el) => {

            el.innerHTML = '';

            let list = document.createElement('ul');
            let item = document.createElement('li');

            item.innerHTML = el.getAttribute('data-prefix') || 'Also on this page:';
            list.appendChild(item);

            res.data.data.map((here) => {

                let item = document.createElement('li');
                let spanName = document.createElement('span');
                let spanLastSeen = document.createElement('span');

                spanName.classList.add('name');
                spanName.innerHTML = here.user.name;

                spanLastSeen.classList.add('last-seen');
                spanLastSeen.innerHTML = ` (last seen ${here.user.last_seen_relative})`;

                item.appendChild(spanName);
                item.appendChild(spanLastSeen);

                list.appendChild(item);
            });

            el.appendChild(list);
        });
    }

    // --------------------------------------------------------------------------

    interact() {

        clearTimeout(this.interactTimeout);
        this.interactTimeout = setTimeout(() => {
            let res = services.apiRequest({
                method: 'put',
                url: API.session.interact
            });
        }, this.interactDebounce);
    }
}

export default Session

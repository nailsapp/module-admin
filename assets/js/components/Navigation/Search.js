class Search {

    /**
     * Constructs Search
     * @param {Navigation} mainController The main navigation controller
     * @param {DOMElement} element The DOMElement to bind to
     */
    constructor(mainController, element) {
        this.mainController = mainController;
        this.element = element;

        this.init();
    }

    // --------------------------------------------------------------------------

    /**
     * Initlaises the sidebar search
     * @returns {Search}
     */
    init() {
        this.element
            .addEventListener('keyup', (e) => {
                clearTimeout(this.debounce);
                this.debounce = setTimeout(() => {
                    let keywords = this.normaliseSearchString(e.srcElement.value);
                    if (this.keywords !== keywords) {
                        this.keywords = keywords;
                        this.search(keywords);
                    }
                }, 250);
            });

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Performs a search
     * @param {string} keywords The search keywords
     * @returns {Search}
     */
    search(keywords) {
        if (keywords.length) {
            this.filter(keywords);
        } else {
            this.reset();
        }

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Filters the sidebar sections by the given keywords
     * @param {string} keywords
     * @returns {Search}
     */
    filter(keywords) {
        this.mainController.adminController.log(`Filtering nav sections by: ${keywords}`);
        this.mainController
            .disableAnimation()
            .disableSorting();

        this.hideToggles();

        this.mainController
            .sections
            .forEach((section) => {

                section.instance.box.container
                    .querySelectorAll('li')
                    .forEach((item) => {

                        let regex = new RegExp(keywords, 'gi');
                        let link = item.querySelector('a');
                        let text = this.normaliseSearchString(
                            item.innerText + link.dataset['search-terms']
                        );

                        if (regex.test(text)) {
                            item.classList.remove('hidden');
                        } else {
                            item.classList.add('hidden');
                        }
                    });

                //  If there are no visible options, hide the box
                let visible = section.instance.box.container
                    .querySelectorAll('li:not(.hidden)')

                if (visible.length) {

                    section.instance.box.element.classList.remove('hidden');

                    //  Size the box to accommodate visible options
                    let height = section.instance.box.container.querySelector('ul').offsetHeight;
                    section.instance.box.container.style.height = `${height}px`;

                } else {
                    section.instance.box.element.classList.add('hidden');
                }
            });

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Resets the sidebar to how it was prior to searching
     * @returns {Search}
     */
    reset() {

        this.mainController.adminController.log('Resetting...');

        this.mainController
            .sections
            .forEach((section) => {

                section.instance.box.element
                    .classList.remove('hidden');

                section.instance.box.container
                    .querySelectorAll('li')
                    .forEach((item) => {
                        item.classList.remove('hidden');
                    });

                if (section.instance.isOpen()) {
                    section.instance.open();
                } else {
                    section.instance.close();
                }
            });

        this.showToggles();

        this.mainController
            .enableAnimation()
            .enableSorting();

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Normalises the search term
     * @param searchString
     * @returns {string}
     */
    normaliseSearchString(searchString) {
        return searchString.replace(/[^a-zA-Z0-9]/g, '');
    };

    // --------------------------------------------------------------------------

    /**
     * Hides all the section tiggles
     * @returns {Search}
     */
    hideToggles() {
        this.mainController
            .sections
            .forEach((section) => {
                section.instance.hideToggles();
            });

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Shows all the section tiggles
     * @returns {Search}
     */
    showToggles() {
        this.mainController
            .sections
            .forEach((section) => {
                section.instance.showToggles();
            });

        return this;
    }
}

export default Search;

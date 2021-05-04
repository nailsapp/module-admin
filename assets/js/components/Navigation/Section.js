class Section {
    /**
     * Constructs Section
     * @param {Navigation} mainController The main Navigation controller
     * @param {DOMElement} element The DOMElement to bind to
     */
    constructor(mainController, element) {
        this.mainController = mainController;
        this.element = element;
        this.box = new Box(
            mainController,
            this.element.querySelector('.box'),
            this.element.dataset['initial-state']
        )
    }

    // --------------------------------------------------------------------------

    /**
     * Opens the section
     * @returns {Section}
     */
    open() {
        this.box.open();
        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Closes the section
     * @returns {Section}
     */
    close() {
        this.box.close();
        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Whether the section is open
     * @returns {boolean}
     */
    isOpen() {
        return this.box.state === 'open';
    }

    // --------------------------------------------------------------------------

    /**
     * Hide the section's toggles
     * @returns {Section}
     */
    hideToggles() {
        this.element
            .querySelector('.toggle')
            .classList.add('hidden');

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * show the section's toggles
     * @returns {Section}
     */
    showToggles() {
        this.element
            .querySelector('.toggle')
            .classList.remove('hidden');

        return this;
    }
}

class Box {

    /**
     * Construct Box
     * @param {Navigation} mainController The main Navigation controller
     * @param {DOMElement} element The DOMElement to bind to
     * @param {string} initialState The initial state of the section
     */
    constructor(mainController, element, initialState) {
        this.mainController = mainController;
        this.element = element;
        this.container = element.querySelector('.box-container');
        this.toggle = element.querySelector('.toggle');
        this.state = initialState || 'closed';
        this.originalHeight = `${this.container.offsetHeight}px`;

        if (this.state === 'open') {
            this.open();
        } else {
            this.close();
        }

        this.toggle.addEventListener('click', (e) => {
            e.preventDefault();
            this.toggleState();
        });
    }

    // --------------------------------------------------------------------------

    /**
     * Opens the section
     * @returns {Box}
     */
    open() {
        this.state = 'open';
        this.container.style.height = this.originalHeight;
        this.element.classList.add('open');
        this.element.classList.remove('closed');

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Closes the section
     * @returns {Box}
     */
    close() {
        this.state = 'closed';
        this.container.style.height = 0;
        this.element.classList.add('closed');
        this.element.classList.remove('open');

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * toggles the state of the section
     * @returns {Box}
     */
    toggleState() {
        if (this.state === 'open') {
            this.close();
        } else {
            this.open();
        }
        this.mainController.saveState();

        return this;
    }
}

export default Section;

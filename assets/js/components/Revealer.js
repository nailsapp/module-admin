/* export Revealer */

/* globals $, jQuery */
class Revealer {

    /**
     * Construct Revealer.
     * @return {Revealer}
     */
    constructor(adminController) {

        this.groups = {};
        this.adminController = adminController;

        this.adminController
            .onRefreshUi((e, domElement) => {
                this.init(domElement);
            })
            .onDestroyUi((e, domElement) => {
                this.destroy(domElement);
            });

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Inits Revealer
     * @param {HTMLElement} domElement
     * @returns {Revealer}
     */
    init(domElement) {

        $('[data-revealer]:not(.processed):not([data-reveal-on])', domElement)
            .filter(':input')
            .filter('input[type=checkbox], select')
            .addClass('processed')
            .each((index, element) => {

                let group = $(element)
                    .data('revealer');

                if (typeof this.groups[group] !== 'undefined') {
                    this.adminController.warn(`Duplicate group "${group}"`);
                    return;
                }

                this.groups[group] = new Group(this.adminController, group, element);
                this.groups[group].findNewElements(domElement);
            });

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Destroys groups whose controller falls within domElement
     * @param domElement
     */
    destroy(domElement) {
        for (let key in this.groups) {
            if (this.groups.hasOwnProperty(key)) {
                if (domElement === null || $.contains(domElement, this.groups[key].$control)) {
                    this.groups[key].destroy();
                    delete this.groups[key];
                }
            }
        }
    }
}

/**
 * Represents a revealer group
 */
class Group {

    /**
     * Construct Group.
     * @param group {String} The string this group represents
     * @param control {HTMLElement} The DOM element responsible for controlling this group
     */
    constructor(adminController, group, control) {

        this.adminController = adminController;
        this.group = group;
        this.$control = $(control);
        this.elements = [];

        this.$control
            .on('change', () => {
                this.toggle(
                    this.getControlValue()
                );
                this.adminController.refreshUi();
            });
    }

    // --------------------------------------------------------------------------

    /**
     * Looks for new elements to add to the group
     * @param domElement {HTMLElement} The DOM element to restrict the search to
     * @returns {Group}
     */
    findNewElements(domElement) {

        $(`[data-revealer="${this.group}"]:not(.processed)`, domElement)
            .filter(':not(input[type=checkbox], select)')
            .addClass('processed')
            .each((index, element) => {
                this
                    .elements
                    .push(
                        new Element(element)
                    )
            });

        this.$control.trigger('change');

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the current value of the control
     * @returns {String|Boolean}
     */
    getControlValue() {
        let value;
        if (this.$control.is('select')) {
            value = this.$control.val();
        } else {
            value = this.$control.is(':checked');
        }

        return value;
    }

    // --------------------------------------------------------------------------

    /**
     * Shows/hides the elements based on whether they should be shown or not
     * @param value {String|Boolean} The value to test
     */
    toggle(value) {
        this.adminController.log(`Toggling elements for value ${value}`);
        for (let i = 0; i < this.elements.length; i++) {
            if (this.elements[i].isShown(value)) {
                this.elements[i].show();
            } else {
                this.elements[i].hide();
            }
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Destroys the group
     */
    destroy() {
        this.$control.removeClass('processed');
        for (let i = 0; i < this.elements.length; i++) {
            this.elements[i].destroy();
        }
    }
}

/**
 * Represents a single element
 */
class Element {

    /**
     * Construct Element.
     * @param element {HTMLElement} The DOM element
     */
    constructor(element) {
        this.$element = $(element);
        this.value = this.$element.data('reveal-on');
    }

    // --------------------------------------------------------------------------

    /**
     * Determines whether the element should be shown for the supplied value
     * @param value {String|Boolean} The value to test
     * @returns {boolean}
     */
    isShown(value) {

        /**
         * This adds support for true/false properties which maybe have been cast as 1/0
         */
        if (typeof value === 'boolean') {
            return (value && this.value === 1) || (!value && this.value === 0);
        } else {
            return this.value === value;
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Shows the element
     * @returns {Element}
     */
    show() {
        this.$element.show();
        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Hides the element
     * @returns {Element}
     */
    hide() {
        this.$element.hide();
        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Handles destruction
     */
    destroy() {
        this.$element.removeClass('processed');
        this.show();
    }
}

export default Revealer;

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

        let exclude = [
            '.revealer--processed',
            '[data-reveal-on]',
            '[data-reveal-not-on]',
        ];

        let selector = '[data-revealer]';
        for (let i = 0; i < exclude.length; i++) {
            selector += `:not(${exclude[i]})`;
        }

        $(selector, domElement)
            .filter(':input')
            .filter('input[type=checkbox], select, input[data-api]')
            .addClass('revealer--processed')
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
            });
    }

    // --------------------------------------------------------------------------

    /**
     * Looks for new elements to add to the group
     * @param domElement {HTMLElement} The DOM element to restrict the search to
     * @returns {Group}
     */
    findNewElements(domElement) {

        $(`[data-revealer="${this.group}"]:not(.revealer--processed)`, domElement)
            .filter(':not(input[type=checkbox], select)')
            .addClass('revealer--processed')
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
        if (this.$control.is('[type=checkbox]')) {
            value = this.$control.is(':checked');
        } else {
            value = this.$control.val();
        }

        return value;
    }

    // --------------------------------------------------------------------------

    /**
     * Shows/hides the elements based on whether they should be shown or not
     * @param value {String|Boolean} The value to test
     */
    toggle(value) {
        this.adminController.log('Toggling elements for value', value);
        for (let i = 0; i < this.elements.length; i++) {
            if (this.elements[i].isShown(value)) {
                this.elements[i].show();
            } else {
                this.elements[i].hide();
            }
        }
        this.adminController.refreshUi();
    }

    // --------------------------------------------------------------------------

    /**
     * Destroys the group
     */
    destroy() {
        this.$control.removeClass('revealer--processed');
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
        this.delimiter = this.$element.data('reveal-delimiter') || ',';

        this.values = element.hasAttribute('data-reveal-on')
            ? element.getAttribute('data-reveal-on')
            : null;

        if (this.values) {
            this.values = this.values.split(this.delimiter)
        }

        this.bangValues = element.hasAttribute('data-reveal-not-on')
            ? element.getAttribute('data-reveal-not-on')
            : null;

        if (this.bangValues) {
            this.bangValues = this.bangValues.split(this.delimiter)
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Determines whether the element should be shown for the supplied value
     * @param value {String|Boolean} The value to test
     * @returns {boolean}
     */
    isShown(value) {

        let showOn = this.testValue(this.values, value, false);
        let showNotOn = this.testValue(this.bangValues, value, true);

        return showOn || showNotOn;
    }

    // --------------------------------------------------------------------------

    /**
     * Performs the test of the supplied value against the stored values
     * @param values
     * @param testString
     * @returns {null|boolean}
     */
    testValue(values, testString, negativeTest) {

        if (values !== null) {

            if (testString.length && !values.length) {
                return true;
            }

            for (let i = 0; i < values.length; i++) {

                if (typeof testString === "boolean") {
                    if (
                        (!negativeTest && testString && [true, 1, "true", "1"].indexOf(values[i]) > -1) ||
                        (!negativeTest && !testString && [false, 0, "false", "0"].indexOf(values[i]) > -1) ||
                        (negativeTest && testString && [true, 1, "true", "1"].indexOf(values[i]) === -1) ||
                        (negativeTest && !testString && [false, 0, "false", "0"].indexOf(values[i]) === -1)
                    ) {
                        return true;
                    }

                } else if (!negativeTest && values[i] == value) {
                    return true;

                } else if (negativeTest && values[i] != value) {
                    return true;
                }
            }
        }

        return null;
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
        this.$element.removeClass('revealer--processed');
        this.show();
    }
}

export default Revealer;

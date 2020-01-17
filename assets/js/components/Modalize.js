/* export Modalize */

/* globals $, jQuery */
class Modalize {

    /**
     * Construct Modalize
     * @return {Modalize}
     */
    constructor(adminController) {

        $(document)
            .on('admin:js-admin-modalize', (e, selector, options) => {
                Modalize.log('Initiating new modalize buttons');
                this.init(selector, options);
            });

        adminController
            .onRefreshUi(() => {
                $(document)
                    .trigger('admin:js-admin-modalize', ['.js-admin-modalize:not(.modalized)']);
            });

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Inits Modalize
     * @returns {Modalize}
     */
    init(selector, options) {
        options = options || {};
        $(selector)
            .each((index, element) => {
                $(element)
                    .add('modalized')
                    .data(
                        'modalize',
                        new ModalizeInstance(
                            element,
                            options
                        )
                    );
            });
        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Write a log to the console
     * @param  {String} message The message to log
     * @param  {mixed}  payload Any additional data to display in the console
     * @return {void}
     */
    static log(message, payload) {
        if (typeof (console.log) === 'function') {
            if (payload !== undefined) {
                console.log('Modalize:', message, payload);
            } else {
                console.log('Modalize:', message);
            }
        }
    };

    // --------------------------------------------------------------------------

    /**
     * Write a warning to the console
     * @param  {String} message The message to warn
     * @param  {mixed}  payload Any additional data to display in the console
     * @return {void}
     */
    static warn(message, payload) {
        if (typeof (console.warn) === 'function') {
            if (payload !== undefined) {
                console.warn('Modalize:', message, payload);
            } else {
                console.warn('Modalize:', message);
            }
        }
    };
}

class ModalizeInstance {

    /**
     * Construct ModalizeInstance
     *
     * @param {DOMElement} element
     * @param {Object} options
     */
    constructor(element, options) {

        this.$trigger = $(element);

        //  Do not double init
        if (this.$trigger.data('modalize') instanceof ModalizeInstance) {
            return;
        }

        this.target_id = this.coalesce(this.$trigger.data('target-id'), options.target_id);
        this.title = this.coalesce(this.$trigger.data('title'), options.title);
        this.width = this.coalesce(this.$trigger.data('width'), options.width);
        this.max_height = this.coalesce(this.$trigger.data('max-height'), options.max_height);

        this.target = document.getElementById(this.target_id);

        if (!this.target) {
            Modalize.warn('"' + this.target_id + '" is not a valid ID');
            return;
        }

        $(this.target).wrapInner('<div></div>');

        this.$trigger
            .on('click', () => {
                this.open();
                return false;
            });
    }

    // --------------------------------------------------------------------------

    open() {
        Modalize.log('Opening modal');

        //  Remove the content from the DOM and place it in the modal
        let content = $(this.target).find('> div').clone();

        this.$modal = $('<div>')
            .html(content)
            .dialog({
                modal: true,
                dialogClass: 'no-close',
                closeOnEscape: false,
                draggable: false,
                title: this.title,
                width: this.width,
                maxHeight: this.max_height,
                position: {
                    my: 'center',
                    at: 'center',
                    of: window
                },
                buttons: {
                    'OK': () => {
                        this.close(true)
                    },
                    'Cancel': () => {
                        this.close(false)
                    }
                }
            });
    }

    // --------------------------------------------------------------------------


    close(applyChanges) {
        Modalize.log('Closing modal');
        if (applyChanges) {
            Modalize.log('Applying changes');
            let content = this.$modal.detach();
            $(this.target)
                .find('> div')
                .empty()
                .append($(content).find('> div'));
        }

        this.$modal.dialog('close');
    }

    // --------------------------------------------------------------------------

    /**
     * Implements null coalesce operator type functionality
     * hat-tip: https://stackoverflow.com/a/22265471/789224
     * @return {null|any}
     */
    coalesce() {
        var len = arguments.length;
        for (let i = 0; i < len; i++) {
            if (arguments[i] !== null && arguments[i] !== undefined) {
                return arguments[i];
            }
        }
        return null;
    }
}

export default Modalize;

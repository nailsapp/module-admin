/* export Modalize */

/* globals $, jQuery */
class Modalize {

    /**
     * Construct Modalize
     * @return {Modalize}
     */
    constructor(adminController) {

        $(document)
            .on('admin:js-admin-modalize', (e, selector, options, domElement) => {
                Modalize.log('Initiating new modalize buttons');
                this.init(selector, options, domElement);
            });

        this.adminController = adminController;
        this.adminController
            .onRefreshUi((e, domElement) => {
                $(document)
                    .trigger(
                        'admin:js-admin-modalize',
                        [
                            '.js-admin-modalize:not(.modalized)',
                            {},
                            domElement
                        ]
                    );
            });

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Inits Modalize
     * @returns {Modalize}
     */
    init(selector, options, domElement) {
        options = options || {};
        $(selector, domElement)
            .each((index, element) => {
                $(element)
                    .add('modalized')
                    .data(
                        'modalize',
                        new ModalizeInstance(
                            this.adminController,
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
     * @return {void}
     */
    static log() {
        if (typeof (console.log) === 'function') {
            console.log("\x1b[33m[Modalize]\x1b[0m", ...arguments);
        }
    };

    // --------------------------------------------------------------------------

    /**
     * Write a warning to the console
     * @return {void}
     */
    static warn() {
        if (typeof (console.warn) === 'function') {
            console.warn("\x1b[33m[Modalize]\x1b[0m", ...arguments);
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
    constructor(adminController, element, options) {

        this.adminController = adminController;
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

        $(this.target)
            .wrapInner('<div></div>')
            .hide();

        this.$trigger
            .on('click', () => {
                this.open();
                return false;
            });
    }

    // --------------------------------------------------------------------------

    open() {

        Modalize.log('Opening modal');

        //  Kill UI (we'll rebuild after)
        this.adminController
            .destroyUi(this.target);

        //  Remove the content from the DOM and place it in the modal
        let content = $(this.target).find('> div').detach();
        Modalize.log(content);

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
                open: () => {
                    this.adminController
                        .refreshUi(this.$modal);
                },
                buttons: {
                    'OK': () => {
                        this.close()
                    }
                }
            });
    }

    // --------------------------------------------------------------------------


    close(applyChanges) {

        Modalize.log('Closing modal');

        this.adminController
            .instances['nails/module-admin']
            .Wysiwyg
            .destroy(this.$modal);

        let content = this.$modal.find('> div').detach();

        $(this.target)
            .append(content);

        this.$modal.dialog('close').remove();
        this.$modal = null;
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

/* export Wysiwyg */

/* globals $, jQuery */
class Wysiwyg {

    /**
     * Construct Wysiwyg
     * @return {Wysiwyg}
     */
    constructor(adminController) {

        this.ready = false;
        this.config = {
            basic: window.NAILS.URL + 'js/ckeditor.config.basic.min.js',
            default: window.NAILS.URL + 'js/ckeditor.config.default.min.js',
        };

        adminController
            .onRefreshUi((e, domElement) => {
                this.init(domElement);
            })
            .onDestroyUi((e, domElement) => {
                this.destroy(domElement);
            });
        ;

        $.ajax({
            'url': window.SITE_URL + 'api/admin/ckeditor/configs',
        })
            .done((response) => {
                this.config.basic = response.data.basic;
                this.config.default = response.data.default;
                this.ready = true;
                this.init();
            })
            .fail((response) => {
                Wysiwyg.warn('Failed to fetch configs. Falling back to default configuration');
                this.ready = true;
                this.init();
            });

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Inits Wysiwyg
     * @param {HTMLElement} domElement
     * @returns {Wysiwyg}
     */
    init(domElement) {
        if (this.ready) {
            $('textarea.wysiwyg:not(.wysiwyged)', domElement)
                .each((index, element) => {
                    $(element)
                        .data(
                            'instance',
                            new WysiwygInstance(
                                $(element),
                                this.config
                            )
                        );
                });
        }

        return this;
    }

    // --------------------------------------------------------------------------


    /**
     * Destroys Wysiwyg instances contained within the given DOM element
     * @param domElement
     * @returns {Wysiwyg}
     */
    destroy(domElement) {
        $('textarea.wysiwyg.wysiwyged', domElement)
            .each((index, element) => {
                $(element)
                    .data('instance')
                    .destroy();
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
            console.log('WYSIWYG:', ...arguments);
        }
    };

    // --------------------------------------------------------------------------

    /**
     * Write a warning to the console
     * @return {void}
     */
    static warn(message, payload) {
        if (typeof (console.warn) === 'function') {
            console.warn('WYSIWYG:', ...arguments);
        }
    };
}

class WysiwygInstance {

    /**
     * Construct WysiwygInstance
     * @param container
     */
    constructor(container, config) {

        this.container = container;
        this.config = config;

        Wysiwyg.log('Constructing', this.container);
        this.container
            .addClass('wysiwyged')
            .ckeditor({
                customConfig: this.container.hasClass('wysiwyg-basic')
                    ? this.config.default
                    : this.config.basic
            });
    }

    // --------------------------------------------------------------------------

    /**
     * Destroys the instance
     */
    destroy() {
        Wysiwyg.log('Destroying', this.container);
        this.container
            .removeClass('wysiwyged')
            .ckeditor(function() {
                this.destroy();
            })
            .data('instance', null);
    }
}

export default Wysiwyg;

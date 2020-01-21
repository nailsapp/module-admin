/* export Wysiwyg */

/* globals $, jQuery */
class Wysiwyg {

    /**
     * Construct Wysiwyg
     * @return {Wysiwyg}
     */
    constructor(adminController) {

        this.ready = false;
        this.adminController = adminController;
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
                this.adminController.log('Ready');
                this.init(document);
            })
            .fail((response) => {
                this.adminController.warn('Failed to fetch configs. Falling back to default configuration');
                this.ready = true;
                this.adminController.log('Ready');
                this.init(document);
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
            this.adminController.log('Initiating new WYSIWYG', domElement);
            $('textarea.wysiwyg:not(.wysiwyged)', domElement)
                .each((index, element) => {
                    $(element)
                        .data(
                            'instance',
                            new WysiwygInstance(
                                this.adminController,
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
}

class WysiwygInstance {

    /**
     * Construct WysiwygInstance
     * @param container
     */
    constructor(adminController, container, config) {

        this.adminController = adminController;
        this.container = container;
        this.config = config;

        this.adminController.log('Constructing', this.container);
        this.container
            .addClass('wysiwyged')
            .ckeditor({
                customConfig: this.container.hasClass('wysiwyg-basic')
                    ? this.config.basic
                    : this.config.default
            });
    }

    // --------------------------------------------------------------------------

    /**
     * Destroys the instance
     */
    destroy() {
        this.adminController.log('Destroying', this.container);
        this.container
            .removeClass('wysiwyged')
            .ckeditor(function() {
                this.destroy();
            })
            .data('instance', null);
    }
}

export default Wysiwyg;

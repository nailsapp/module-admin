/* export Toggles */

/* globals $, jQuery */
class Toggles {

    /**
     * Construct Toggles
     * @return {Toggles}
     */
    constructor(adminController) {

        adminController
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
     * Inits Toggles
     * @param {HTMLElement} domElement
     * @returns {Toggles}
     */
    init(domElement) {
        $('.form-bool:not(.toggled)', domElement)
            .each(function() {
                $(this)
                    .data(
                        'instance',
                        new ToggleInstance($(this))
                    );
            });

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Destroys Toggle instances contained within the given DOM element
     * @param domElement
     * @returns {Wysiwyg}
     */
    destroy(domElement) {
        $('.form-bool.toggled', domElement)
            .each((index, element) => {
                $(element)
                    .data('instance')
                    .destroy();
            });
        return this;
    }
}

class ToggleInstance {

    /**
     * Construct ToggleInstance
     * @param container
     */
    constructor(container) {

        this.container = container;
        this.checkbox = this.container.next('input[type=checkbox]');
        this.is_readonly = this.checkbox.hasClass('readonly');
        this.width = this.checkbox.data('toggle-width') || '100px';
        this.height = this.checkbox.data('toggle-height') || '30px';
        this.text_on = this.checkbox.data('text-on') || 'ON';
        this.text_off = this.checkbox.data('text-off') || 'OFF';

        this.checkbox.hide();
        this.container
            .addClass('toggled')
            .css({
                'width': this.width,
                'height': this.height,
                'text-align': 'center'
            })
            .toggles({
                'checkbox': this.checkbox,
                'click': !this.is_readonly,
                'drag': !this.is_readonly,
                'clicker': this.checkbox,
                'on': this.checkbox.is(':checked'),
                'text': {
                    'on': this.text_on,
                    'off': this.text_off
                }
            })
            .on('toggle', (e, value) => {
                //  Proxy the toggle event to the checkbox as well
                this.checkbox.trigger('toggle', [value]).trigger('change');
            });
    }

    // --------------------------------------------------------------------------

    destroy() {
        this.container
            .empty()
            .removeClass('toggled')
            .removeAttr('style');
        this.checkbox
            .show();
    }
}

export default Toggles;

/* export Toggles */

/* globals $, jQuery */
class Toggles {

    /**
     * Construct Toggles
     * @return {Toggles}
     */
    constructor(adminController) {

        adminController
            .onRefreshUi(() => {
                this.init();
            });

        return this;
    }

    /**
     * Inits Toggles
     * @returns {Toggles}
     */
    init() {
        $('.form-bool:not(.toggled)')
            .each(function() {
                $(this)
                    .data(
                        'instance',
                        new ToggleInstance($(this))
                    );
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
                this.checkbox.trigger('toggle', [value]);
            });
    }
}

export default Toggles;

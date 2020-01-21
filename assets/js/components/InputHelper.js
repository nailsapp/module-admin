/* export InputHelper */

/* globals $, jQuery */
class InputHelper {

    /**
     * Construct InputHelper
     * @return {InputHelper}
     */
    constructor(adminController) {

        this.helpers = [];
        adminController
            .onRefreshUi((e, domElement) => {
                this.findNewHelpers(domElement);
                this.positionHelpers()
            });

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Finds new helpers
     * @param {HTMLElement} domElement
     * @returns {InputHelper}
     */
    findNewHelpers(domElement) {

        $('input[data-helper]:not(.input-helper--processed', domElement)
            .addClass('input-helper--processed')
            .each((index, element) => {

                let $input = $(element);
                let text = $input.data('helper');
                let $helper = $('<div>')
                    .html(text)
                    .insertAfter($input);

                this.helpers.push({
                    helper: $helper,
                    input: $input,
                })
            });

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Positions existing helpers
     */
    positionHelpers() {
        for (let i = 0; i < this.helpers.length; i++) {

            let item = this.helpers[i];
            if (item.input.is(':visible')) {
                let height = `${item.input.outerHeight()}px`;
                item
                    .helper
                    .css({
                        'position': 'absolute',
                        'background': 'rgba(0, 0, 0, 0.07)',
                        'border': '1px solid transparent',
                        'border-right': '1px solid rgba(0, 0, 0, 0.1)',
                        'transform': 'translateY(-100%)',
                        'height': height,
                        'lineHeight': height,
                        'padding': '0 0.5rem'
                    });
                item
                    .input
                    .css({
                        'paddingLeft': item.helper.outerWidth() + 7
                    })
            }
        }
    }
}

export default InputHelper;

/* export Stripes */

/* globals $, jQuery */
class Stripes {

    /**
     * Construct Stripes
     * @return {Stripes}
     */
    constructor(adminController) {

        adminController
            .onRefreshUi((e, domElement) => {
                this.init(domElement);
            });

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Inits Stripes
     * @param {HTMLElement} domElement
     * @returns {Stripes}
     */
    init(domElement) {

        $('fieldset,.fieldset', domElement)
            .each(function() {
                $('div.field', this).removeClass('odd even');
                $('div.field:visible:odd', this).addClass('odd');
                $('div.field:visible:even', this).addClass('even');
            });

        return this;
    }
}

export default Stripes;

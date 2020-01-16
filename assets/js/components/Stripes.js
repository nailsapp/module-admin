/* export Stripes */

/* globals $, jQuery */
class Stripes {

    /**
     * Construct Stripes
     * @return {Stripes}
     */
    constructor(adminController) {

        adminController
            .onRefreshUi(() => {
                this.init();
            });

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Inits Stripes
     * @returns {Stripes}
     */
    init() {

        $('fieldset,.fieldset')
            .each(function() {
                $('div.field', this).removeClass('odd even');
                $('div.field:visible:odd', this).addClass('odd');
                $('div.field:visible:even', this).addClass('even');
            });

        return this;
    }
}

export default Stripes;

/* export Select */

/* globals $, jQuery */
class Select {

    /**
     * Construct Select
     * @return {Select}
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
     * Inits Select
     * @param {HTMLElement} domElement
     * @returns {Select}
     */
    init(domElement) {

        //  @todo (Pablo - 2020-01-21) - Target all selects?
        $('select.select2:not(.select2-offscreen):not(.select2--processed)', domElement)
            .addClass('select2--processed')
            .select2();

        return this;
    }
}

export default Select;

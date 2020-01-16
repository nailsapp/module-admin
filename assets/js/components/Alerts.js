/* export Alerts */

/* globals $, jQuery */
class Alerts {

    /**
     * Construct Alerts
     * @return {Alerts}
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
     * Inits Alerts
     * @returns {Alerts}
     */
    init() {

        $('.alert__close:not(.alert__close--initiated)')
            .addClass('alert__close--initiated')
            .on('click', function() {
                $(this)
                    .closest('.alert')
                    .remove();
            });

        return this;
    }
}

export default Alerts;

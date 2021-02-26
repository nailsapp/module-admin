/* export DashboardWidgets */

/* globals $, jQuery */
class DashboardWidgets {

    /**
     * Construct DashboardWidgets
     * @return {DashboardWidgets}
     */
    constructor(adminController) {

        this.adminController = adminController;
        this.adminController
            .onRefreshUi((e, domElement) => {
                this.init(this.adminController);
            });

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Inits DashboardWidgets
     * @returns {DashboardWidgets}
     */
    init() {

        $('.dashboard-widgets:not(.dashboard-widgets--initialised)')
            .addClass('dashboard-widgets--initialised')
            .each((index, element) => {
                //  @todo (Pablo 26/02/2021) - Set up the draggables, adding etc
            })
        return this;
    }
}

export default DashboardWidgets;

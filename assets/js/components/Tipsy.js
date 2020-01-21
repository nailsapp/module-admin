/* export Tipsy */

/* globals $, jQuery */
class Tipsy {

    /**
     * Construct Tipsy
     * @return {Tipsy}
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
     * Inits Tipsy
     * @param {HTMLElement} domElement
     * @returns {Tipsy}
     */
    init(domElement) {
        /**
         * Once tipsy'd, add drunk class - so it's not called twice should this
         * method be called again. Tipsy... drunk... geddit?
         */

        $('*[rel=tipsy]:not(.drunk)', domElement)
            .addClass('drunk')
            .tipsy({opacity: 0.85});

        $('*[rel=tipsy-html]:not(.drunk)', domElement)
            .addClass('drunk')
            .tipsy({opacity: 0.85, html: true});

        $('*[rel=tipsy-right]:not(.drunk)', domElement)
            .addClass('drunk')
            .tipsy({opacity: 0.85, gravity: 'w'});

        $('*[rel=tipsy-left]:not(.drunk)', domElement)
            .addClass('drunk')
            .tipsy({opacity: 0.85, gravity: 'e'});

        $('*[rel=tipsy-top]:not(.drunk)', domElement)
            .addClass('drunk')
            .tipsy({opacity: 0.85, gravity: 's'});

        $('*[rel=tipsy-bottom]:not(.drunk)', domElement)
            .addClass('drunk')
            .tipsy({opacity: 0.85, gravity: 'n'});

        return this;
    }
}

export default Tipsy;

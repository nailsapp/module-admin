/* export DisabledElements */

/* globals $, jQuery */
class DisabledElements {

    /**
     * Construct DisabledElements
     * @return {DisabledElements}
     */
    constructor() {

        $(document, '.btn-disabled')
            .on('click', () => {
                return false;
            });

        return this;
    }
}

export default DisabledElements;

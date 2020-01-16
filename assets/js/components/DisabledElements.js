/* export DisabledElements */

/* globals $, jQuery */
class DisabledElements {

    /**
     * Construct DisabledElements
     * @return {DisabledElements}
     */
    constructor() {

        $(document)
            .on('click', '.btn-disabled', () => {
                return false;
            });

        return this;
    }
}

export default DisabledElements;

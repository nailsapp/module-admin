/* export DisabledElements */

/* globals $, jQuery */
class DisabledElements {

    /**
     * Construct DisabledElements
     * @return {DisabledElements}
     */
    constructor() {
        $('.btn-disabled')
            .on('click', (e) => {
                return false;
            })
        return this;
    }
}

export default DisabledElements;

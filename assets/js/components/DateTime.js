/* export DateTime */

/* globals $, jQuery */
class DateTime {

    /**
     * Construct DateTime
     * @return {DateTime}
     */
    constructor(adminController) {

        this.adminController = adminController;
        this.processing = false;
        this.checkAgain = [];

        this.adminController
            .onRefreshUi((e, domElement) => {
                this.init(domElement);
            });

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Inits uninitiated elements
     * @param {HTMLElement} domElement
     * @returns {DateTime}
     */
    init(domElement) {
        this.adminController.log('Initiating new date/time inputs');
        let classes = [
            'input.date:not(.datetime--processed)',
            'input.datetime:not(.datetime--processed)',
            'input.time:not(.datetime--processed)',
        ];
        let $items = $(classes.join(','), domElement)
            .addClass('datetime--processed');

        this.adminController.log(`Found ${$items.length} unprocessed items`);

        $items
            .each((index, element) => {
                if (element.classList.contains('date')) {
                    this.initDate(element);
                } else if (element.classList.contains('datetime')) {
                    this.initDateTime(element);
                } else if (element.classList.contains('time')) {
                    this.initTime(element);
                }
            });
    }

    // --------------------------------------------------------------------------

    /**
     * Initiatres Date inputs
     * @param element {HTMLElement}
     */
    initDate(element) {

        let $element = $(element);
        let dateFormat = $element.data('datepicker-dateformat') || 'yy-mm-dd';
        let yearRange = $element.data('datepicker-yearrange') || 'c-100:c+10';

        //  Instanciate datepicker
        $element
            .datepicker({
                'dateFormat': dateFormat,
                'changeMonth': true,
                'changeYear': true,
                'yearRange': yearRange
            });
    }

    // --------------------------------------------------------------------------

    /**
     * Initiatres DateTime inputs
     * @param element {HTMLElement}
     */
    initDateTime(element) {

        let $element = $(element);
        let dateFormat = $element.data('datepicker-dateformat') || 'yy-mm-dd';
        let timeFormat = $element.data('datepicker-timeformat') || 'HH:mm:ss';
        let yearRange = $element.data('datepicker-yearrange') || 'c-100:c+10';

        $element
            .datetimepicker({
                'dateFormat': dateFormat,
                'timeFormat': timeFormat,
                'changeMonth': true,
                'changeYear': true,
                'yearRange': yearRange
            });
    }

    // --------------------------------------------------------------------------

    /**
     * Initiatres Time inputs
     * @param element {HTMLElement}
     */
    initTime(element) {

        let $element = $(element);
        let timeFormat = $element.data('datepicker-timeformat') || 'HH:mm';

        $element
            .datetimepicker({
                'timeOnly': true,
                'timeFormat': timeFormat
            });
    }
}

export default DateTime;

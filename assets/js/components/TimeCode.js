/* export TimeCode */

class TimeCode {

    /**
     * Construct TimeCode
     * @param adminController The admin controller
     * @returns {TimeCode}
     */
    constructor(adminController) {

        this.adminController = adminController;
        this.instances = [];
        this.adminController
            .onRefreshUi((e, domElement) => {
                this.init(domElement);
            });

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Inits TimeCode
     * @param {HTMLElement} domElement
     * @returns {TimeCode}
     */
    init(domElement) {

        let $items = $('.js-timecode:not(.js-timecode--processed)')
            .addClass('js-timecode--processed');

        if ($items.length) {
            this.adminController.log(`Found ${$items.length} new timecode inputs`);
            $items
                .each((index, element) => {

                    this.instances.push(
                        new TimeCodeInstance(
                            this.adminController,
                            $(element)
                        )
                    )
                });
        }
    }
}

class TimeCodeInstance {
    /**
     * Constructs TimemCodeInstance
     *
     * @param adminController The admin controller
     * @param $element The element to bind to
     */
    constructor(adminController, $element) {

        this.adminController = adminController;
        this.$timecode = $element;
        this.$seconds = $element.next('.js-timecode-input');
        this.$error = this.$seconds.next('.js-timecode-error');

        this.$timecode
            .on('change', () => {
                this.hideError();
            })
            .on('blur', (e) => {
                try {
                    this.setSeconds(
                        this.convertTimecodeToSeconds(
                            e.currentTarget.value
                        )
                    );
                } catch (e) {
                    this.showError(e.message);
                }
            });

        this.$seconds
            .on('change', () => {
                this.hideError();
            })
            .on('blur', (e) => {
                this.setTimeCode(
                    this.convertSecondsToTimecode(
                        e.currentTarget.value
                    )
                );
            });

        this.$timecode
            .val(
                this.convertSecondsToTimecode(
                    this.$seconds.val()
                )
            );
    }

    // --------------------------------------------------------------------------

    /**
     * Sets the value of the timecode input
     * @param {String} timecode The value to set
     */
    setTimeCode(timecode) {
        this.$timecode.val(timecode || '00:00:00')
    }

    // --------------------------------------------------------------------------

    /**
     * Sets the value of the seconds input
     * @param {Number} seconds The vaue to set
     */
    setSeconds(seconds) {
        this.$seconds.val(seconds || 0)
    }

    // --------------------------------------------------------------------------

    /**
     * Shows the error block
     * @param message The message to show
     */
    showError(message) {
        this.adminController.log(message);
        this.$error.text(message).addClass('alert alert-danger');
    }

    // --------------------------------------------------------------------------

    /**
     * Hides the error block
     */
    hideError() {
        this.$error.text('').removeClass('alert alert-danger');
    }

    // --------------------------------------------------------------------------

    /**
     * Converts a timecode string to seconds
     * @param {String} timecode The timecode to convert
     * @returns {number}
     */
    convertTimecodeToSeconds(timecode) {

        if (!/^\d{2,}:[0-5]\d:[0-5]\d$/.test(timecode)) {
            throw Error('Invalid timecode (expected format: hh:mm:ss)');
        }

        let bits = timecode.split(':')
        let hh = parseInt(bits[0]);
        let mm = parseInt(bits[1]);
        let ss = parseInt(bits[2]);

        return (hh * 60 * 60) + (mm * 60) + ss;
    }

    // --------------------------------------------------------------------------

    /**
     * Converts seconds to a timecode string
     * @param {Number} seconds The seconds to convert
     * @returns {string}
     */
    convertSecondsToTimecode(seconds) {

        let ss = 0;
        let mm = 0;
        let hh = 0;

        ss = seconds % 60;

        mm = (seconds - ss) / 60;

        if (mm > 59) {
            let mmTemp = mm % 60;
            hh = Math.floor(mm / 60);
            mm = mmTemp;
        }

        return [
            this.pad(hh),
            this.pad(mm),
            this.pad(ss)
        ].join(':');
    }

    pad(number) {
        return number < 10 ? '0' + number : number.toString();
    }
}

export default TimeCode;

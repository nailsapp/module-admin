/* export ScrolltoFirstError */

/* globals $, jQuery */
class ScrolltoFirstError {

    /**
     * Construct ScrolltoFirstError
     * @return {ScrolltoFirstError}
     */
    constructor(adminController) {

        let $inline, $system, $target;

        $inline = $('div.field.error:visible');

        if ($inline.length) {
            $target = $($inline.get(0));
        } else {
            $system = $('div.system-alert.error:visible');
            $target = $($system.get(0));
        }

        if ($target.length) {

            //  Giving the browser a slight chance to work out sizes etc
            setTimeout(() => {
                $.scrollTo(
                    $target,
                    'fast',
                    {
                        axis: 'y',
                        offset: {
                            top: -60
                        }
                    }
                );
            }, 750);
        }

        return this;
    }
}

export default ScrolltoFirstError;

let _nails_admin = function() {
    /**
     * Avoid scope issues in callbacks and anonymous functions by referring to `this` as `base`
     * @type {Object}
     */
    var base = this;

    // --------------------------------------------------------------------------

    /**
     * Construct the class
     * @return {Void}
     */
    base.__construct = function() {
        //  @todo (Pablo - 2019-11-27) - Rewrite all of these as admin plugins
        base.initSearchBoxes();
        base.initMobileMenu();
        base.initNiceTime();

        // --------------------------------------------------------------------------

        $(window).on('resize', function() {
            base.initMatchHeights();
        }).trigger('resize');

    };

    // --------------------------------------------------------------------------

    base.localStorage = {
        'enabled': (function() {
            var uid = new Date().toString();
            var storage;
            var result;
            try {

                (storage = window.localStorage).setItem(uid, uid);
                result = storage.getItem(uid) === uid;
                storage.removeItem(uid);
                return true;

            } catch (exception) {
                return false;
            }

        }()),
        'set': function(key, value) {

            if (this.enabled) {

                value = JSON.stringify(value);
                return window.localStorage.setItem(key, value);

            } else {

                return false;
            }
        },
        'get': function(key) {

            if (this.enabled) {

                var value = window.localStorage.getItem(key);
                return JSON.parse(value);

            } else {

                return false;
            }
        },
        'remove': function(key) {

            if (this.enabled) {

                return window.localStorage.removeItem(key);

            } else {

                return false;
            }
        }
    };

    // --------------------------------------------------------------------------

    /**
     * Initialise the admin search & sort boxes
     * @return {Void}
     */
    base.initSearchBoxes = function() {
        var timeout;
        //  Bind submit to select changes
        $('div.search select, div.search input[type=checkbox]').on('change', function() {
            var $form = $(this).closest('form');
            clearTimeout(timeout);
            timeout = setTimeout(function() {
                $form.submit();
            }, 500);
        });

        // --------------------------------------------------------------------------

        //  Show mask when submitting form
        $('div.search form').on('submit', function() {

            $(this).closest('div.search').find('div.mask').show();
        });

        // --------------------------------------------------------------------------

        //  Filter Checkboxes
        $('div.search .filterOption input[type=checkbox]').on('change', function() {

            if ($(this).is(':checked')) {

                $(this).closest('.filterOption').addClass('checked');

            } else {

                $(this).closest('.filterOption').removeClass('checked');
            }
        });

        //  Initial styles
        $('div.search .filterOption input[type=checkbox]').each(function() {

            if ($(this).is(':checked')) {

                $(this).closest('.filterOption').addClass('checked');

            } else {

                $(this).closest('.filterOption').removeClass('checked');
            }
        });
    };

    // --------------------------------------------------------------------------

    /**
     * Initialise the mobile menu
     * @return {Void}
     */
    base.initMobileMenu = function() {
        $('#mobileMenuBurger').on('click', function() {

            var maxHeight = $(window).height() - 150;

            $('#mobileMenu').toggleClass('open');

            if ($('#mobileMenu').hasClass('open')) {

                $('#mobileMenu').css('max-height', maxHeight + 'px');

            } else {

                $('#mobileMenu').css('max-height', '0px');
            }

            return false;
        });
    };

    // --------------------------------------------------------------------------

    /**
     * Initialise nicetime elements
     * @return {Void}
     */
    base.initNiceTime = function() {
        var _elems = $('.nice-time:not(.nice-timed)'); // Fetch just new objects

        //  Fetch objects which can be nice-timed
        _elems.each(function() {

            //  Setup variables
            var _src = $(this).text();

            //  Check format
            var _regex = /^\d\d\d\d-\d\d?-\d\d?(\d\d?\:\d\d?\:\d\d?)?$/;

            if (_regex.test(_src)) {

                //  Parse into various bits
                var _basic = _src.split(' ');

                if (!_basic[1]) {

                    _basic[1] = '00:00:00';
                }

                if (_basic[0]) {

                    var _date = _basic[0].split('-');
                    var _time = _basic[1].split(':');

                    var _Y = _date[0];
                    var _M = _date[1];
                    var _D = _date[2];
                    var _h = _time[0];
                    var _m = _time[1];
                    var _s = _time[2];

                    //  Attempt to parse the time
                    var _date_obj = new Date(_Y, _M, _D, _h, _m, _s);

                    if (!isNaN(_date_obj.getTime())) {

                        /**
                         * Date was parsed successfully, stick it as the attribute.
                         * Add .nice-timed to it so it's not picked up as a new object
                         */

                        $(this).addClass('nice-timed');
                        $(this).attr('data-time', _src);
                        $(this).attr('data-year', _Y);
                        $(this).attr('data-month', _M);
                        $(this).attr('data-day', _D);
                        $(this).attr('data-hour', _h);
                        $(this).attr('data-minute', _m);
                        $(this).attr('data-second', _s);
                        $(this).attr('title', _src);
                    }
                }
            }
        });

        // --------------------------------------------------------------------------

        //  Nice time-ify everything
        $('.nice-timed').each(function() {
            //  Pick up date form object
            var _Y = $(this).attr('data-year');
            var _M = $(this).attr('data-month') - 1; //  Because the date object does months from 0
            var _D = $(this).attr('data-day');
            var _h = $(this).attr('data-hour');
            var _m = $(this).attr('data-minute');
            var _s = $(this).attr('data-second');

            var _date = new Date(_Y, _M, _D, _h, _m, _s);
            var _now = new Date();
            var _relative = '';

            // --------------------------------------------------------------------------

            //  Do whatever it is we need to do to get relative time
            var _diff = Math.ceil((_now.getTime() - _date.getTime()) / 1000);

            if (_diff >= 0 && _diff < 10) {

                //  Has just happened so for a few seconds show plain ol' English
                _relative = 'a moment ago';

            } else if (_diff >= 10) {

                //  Target time is in the past
                _relative = base.initNiceTimeCalc(_diff) + ' ago';

            } else if (_diff < 0) {

                //  Target time is in the future
                _relative = base.initNiceTimeCalc(_diff) + ' from now';
            }

            // --------------------------------------------------------------------------

            //  Set the new relative time
            if (_relative === '1 day ago') {

                _relative = 'yesterday';

            } else if (_relative === '1 day from now') {

                _relative = 'tomorrow';
            }

            if ($(this).data('capitalise') === true) {

                _relative = _relative.charAt(0).toUpperCase() + _relative.slice(1);
            }

            $(this).text(_relative);
        });
    };

    // --------------------------------------------------------------------------

    /**
     * Perform the niceTime calculation
     * @param  {Number} diff The difference between then and now
     * @return {String}
     */
    base.initNiceTimeCalc = function(diff) {
        var _value = 0;
        var _term = '';

        //  Constants
        var _second = 1;
        var _minute = _second * 60;
        var _hour = _minute * 60;
        var _day = _hour * 24;
        var _week = _day * 7;
        var _month = _day * 30;
        var _year = _day * 365;

        //  Always dealing with positive values
        if (diff < 0) {

            diff = diff * -1;
        }

        //  Seconds
        if (diff < _minute) {

            //  Seconds
            _value = diff;
            _term = 'second';

        } else if (diff < _hour) {

            //  Minutes
            _value = Math.floor(diff / 60);
            _term = 'minute';

        } else if (diff < _day) {

            //  Hours
            _value = Math.floor(diff / 60 / 60);
            _term = 'hour';

        } else if (diff < _week) {

            //  Days
            _value = Math.floor(diff / 60 / 60 / 24);
            _term = 'day';

        } else if (diff < _month) {

            //  Weeks
            _value = Math.floor(diff / 60 / 60 / 24 / 7);
            _term = 'week';

        } else if (diff < _year) {

            //  Months
            _value = Math.floor(diff / 60 / 60 / 24 / 30);
            _term = 'month';

        } else {

            //  Years
            _value = Math.floor(diff / 60 / 60 / 24 / 365);
            _term = 'year';
        }

        // --------------------------------------------------------------------------

        var _suffix = _value === 1 ? '' : 's';

        return _value + ' ' + _term + _suffix;
    };

    // --------------------------------------------------------------------------

    /**
     * Write an error to the console
     * @param  {string} output The error to write
     * @return {Void}
     */
    base.error = function(output) {
        if (window.console && window.ENVIRONMENT !== 'PRODUCTION') {

            console.error(output);
        }
    };

    // --------------------------------------------------------------------------

    /**
     * Formats a number into a human friendly String
     * Adapted from: https://github.com/kvz/phpjs/blob/master/functions/strings/number_format.js
     * @param  {Number} number        The number to format
     * @param  {Number} decimals      The number of decimal points to show
     * @param  {String} dec_point     The string to use as the decimal separator
     * @param  {String} thousands_sep The string to use for the thousands separator
     * @return {String}
     */
    base.numberFormat = function(number, decimals, dec_point, thousands_sep) {

        number = (number + '').replace(/[^0-9+\-Ee.]/g, '');

        var n = !isFinite(+number) ? 0 : +number;
        var prec = !isFinite(+decimals) ? 0 : Math.abs(decimals);
        var sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep;
        var dec = (typeof dec_point === 'undefined') ? '.' : dec_point;
        var s = '';
        var toFixedFix = function(n, prec) {
            var k = Math.pow(10, prec);
            return '' + (Math.round(n * k) / k)
                .toFixed(prec);
        };

        // Fix for IE parseFloat(0.55).toFixed(0) = 0;
        s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');

        if (s[0].length > 3) {
            s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
        }

        if ((s[1] || '').length < prec) {

            s[1] = s[1] || '';
            s[1] += new Array(prec - s[1].length + 1).join('0');
        }

        return s.join(dec);
    };

    // --------------------------------------------------------------------------

    base.initMatchHeights = function() {
        var heights, elements, group;

        heights = {};
        elements = $('.match-height');

        //  Calculate the max height
        elements.each(function() {

            //  Reset the height
            $(this).height('');

            group = $(this).data('height-group') || 'default';

            if (heights[group] === undefined) {
                heights[group] = 0;
            }

            if ($(this).height() > heights[group]) {
                heights[group] = $(this).height();
            }
        });

        //  Set the computed max height
        elements.each(function() {

            group = $(this).data('height-group') || 'default';

            $(this).height(heights[group]);
        });
    };

    // --------------------------------------------------------------------------

    return base.__construct();
};

window._nails_admin = new _nails_admin();

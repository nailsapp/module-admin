/* exported _ADMIN_DYNAMIC_TABLE */
/* globals Mustache */
var _ADMIN_DYNAMIC_TABLE;
_ADMIN_DYNAMIC_TABLE = function() {

    /**
     * Avoid scope issues in callbacks and anonymous functions by referring to `this` as `base`
     * @type {_ADMIN_DYNAMIC_TABLE}
     */
    var base = this;

    // --------------------------------------------------------------------------

    /**
     * Construct _ADMIN_DYNAMIC_TABLE
     * @return {_ADMIN_DYNAMIC_TABLE} A reference to this class
     */
    base.__construct = function() {
        $('.js-admin-dynamic-table')
            .each(function(index, element) {
                base.init($(element));
            });
        return base;
    };

    // --------------------------------------------------------------------------

    /**
     * Initialise dynamic tables
     * @param  {jQuery} $table The table DOM element
     * @return {_ADMIN_DYNAMIC_TABLE} A reference to this class
     */
    base.init = function($table) {

        var $body = $table.find('.js-admin-dynamic-table__template');
        var data = $table.data('data') || [];

        $table.data('template', $body.html());
        $table.data('index', 0);
        $body.empty();

        base.bindEvents($table, $body);

        for (var i = 0, j = data.length; i < j; i++) {
            base.add($table, $body, data[i]);
        }

        return base;
    };

    // --------------------------------------------------------------------------

    /**
     * Bind events to the table
     * @param  {jQuery} $table The table DOM element
     * @param  {jQuery} $body  The body DOM element
     * @return {_ADMIN_DYNAMIC_TABLE} A reference to this class
     */
    base.bindEvents = function($table, $body) {
        $('.js-admin-dynamic-table__add', $table)
            .on('click', function() {
                base.add($table, $body);
                return false;
            });

        $table
            .on('click', '.js-admin-dynamic-table__remove', function(e) {
                base.remove($(e.currentTarget).closest('tr'));
                return false;
            });

        return base;
    };

    // --------------------------------------------------------------------------

    /**
     * Adds a new row
     * @param  {jQuery} $table The table DOM element
     * @param  {jQuery} $body  The body DOM element
     * @param {Object}  data   Any data to use to populate the row
     * @return {_ADMIN_DYNAMIC_TABLE} A reference to this class
     */
    base.add = function($table, $body, data) {
        data = data || {};
        data.index = $table.data('index');
        $body.append(
            Mustache.render($table.data('template'), data)
        );
        $table.data('index', data.index + 1);
        return base;
    };

    // --------------------------------------------------------------------------

    /**
     * Remove a row
     * @param  {jquery} $row The row DOM element
     * @return {_ADMIN_DYNAMIC_TABLE} A reference to this class
     */
    base.remove = function($row) {
        $row.remove();
        return base;
    };

    // --------------------------------------------------------------------------

    return base.__construct();
}();

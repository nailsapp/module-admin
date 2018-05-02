/* export DynamicTable */

/* globals Mustache, $, jQuery */
class DynamicTable {

    /**
     * Construct DynamicTable
     * @return {DynamicTable}
     */
    constructor() {

        $('.js-admin-dynamic-table')
            .each((index, element) => {
                this.init($(element));
            });
        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Initialise a dynamic table
     * @param {jQuery} $table The table DOM element
     * @return {DynamicTable}
     */
    init($table) {

        let $body = $table.find('.js-admin-dynamic-table__template');
        let data = $table.data('data') || [];

        $table.data('template', $body.html());
        $table.data('index', 0);
        $body.empty();

        this.bindEvents($table, $body);

        for (let i = 0, j = data.length; i < j; i++) {
            this.add($table, $body, data[i]);
        }

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Bind events
     * @param {jQuery} $table The table DOM element
     * @param {jQuery} $body The body DOM element
     * @return {DynamicTable}
     */
    bindEvents($table, $body) {
        $('.js-admin-dynamic-table__add', $table)
            .on('click', () => {
                this.add($table, $body);
                return false;
            });

        $table
            .on('click', '.js-admin-dynamic-table__remove', (e) => {
                this.remove($(e.currentTarget).closest('tr'));
                return false;
            });

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Add a new row
     * @param {jQuery} $table The table DOM element
     * @param {jQuery} $body The body DOM element
     * @param {Object} data Data to render the row with
     * @return {DynamicTable}
     */
    add($table, $body, data) {
        data = data || {};
        data.index = $table.data('index');
        $body.append(
            Mustache.render($table.data('template'), data)
        );
        $table.data('index', data.index + 1);

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Remove a row
     * @param {jQuery} $row The row DOM element
     * @return {DynamicTable}
     */
    remove($row) {
        $row.remove();
        return this;
    }
}

export default DynamicTable;

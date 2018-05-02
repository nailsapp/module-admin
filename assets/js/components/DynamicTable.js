/* export DynamicTable */
/* globals Mustache */
class DynamicTable {
    constructor() {

        $('.js-admin-dynamic-table')
            .each((index, element) => {
                this.init($(element));
            });
        return this;
    }

    // --------------------------------------------------------------------------

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

    remove($row) {
        $row.remove();
        return this;
    }
}

export default DynamicTable;

/* global Mustache */
let _admin_logs_site = function() {
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
        base.fetchLogs();
    };

    // --------------------------------------------------------------------------

    /**
     * Fetches logs from the server
     * @return {Void}
     */
    base.fetchLogs = function() {
        $.ajax({
            'url': window.SITE_URL + 'api/admin/logs/site',
        })
            .done(function(response) {
                base.fetchLogsOk(response.data);
            })
            .fail(function(response) {

                var data;

                try {
                    data = JSON.parse(response.responseText);
                } catch (e) {
                    data = {
                        'status': 500,
                        'error': 'An unknown error occurred.'
                    };
                }
                base.fetchLogsFail(data);
            });
    };

    // --------------------------------------------------------------------------

    /**
     * Called when fetchLogs is successful
     * @param  {Object} logs Data returned by the server
     * @return {Void}
     */
    base.fetchLogsOk = function(logs) {
        var tpl, html;

        $('#logEntries').empty();
        $('#pleaseNote').remove();

        if (logs.length > 0) {

            tpl = $('#templateLogRow').html();

            for (var i = 0; i < logs.length; i++) {

                html = Mustache.render(tpl, logs[i]);
                $('#logEntries').append(html);
            }

        } else {

            html = $('#templateNoLogFiles').html();
            $('#logEntries').html(html);
        }
    };

    // --------------------------------------------------------------------------

    /**
     * Called when fetchLogs is unsuccessful
     * @param  {Object} data Data from the server
     * @return {Void}
     */
    base.fetchLogsFail = function(data) {
        $('<div>')
            .html('<p>' + data.error + '</p>')
            .dialog(
                {
                    title: 'An error occurred',
                    resizable: false,
                    draggable: false,
                    modal: true,
                    dialogClass: 'no-close',
                    buttons:
                        {
                            OK: function() {
                                $(this).dialog('close');
                            }
                        }
                })
            .show();
    };

    // --------------------------------------------------------------------------

    return base.__construct();
}();

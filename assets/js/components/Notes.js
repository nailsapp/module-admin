/* export Notes */

/* globals $, jQuery */

class Notes {
    /**
     * Construct Notes
     * @return {Notes}
     */
    constructor() {
        $('.js-admin-notes')
            .on('click', (e) => {

                let $btn = $(e.currentTarget);
                let $modal = $('<div>')
                    .dialog({
                        modal: true,
                        title: $btn.data('modal-title') || 'Notes',
                        width: $btn.data('modal-width') || 500,
                        minHeight: $btn.data('modal-height') || 500
                    });

                this.load(
                    $modal,
                    $btn.data('model-name'),
                    $btn.data('model-provider'),
                    $btn.data('id')
                );

                return false;
            });

        return this;
    };

    // --------------------------------------------------------------------------

    /**
     * Load notes from the server
     * @param {jQuery} $modal The modal object
     * @param {String} model_name The model name
     * @param {String} model_provider The model provider
     * @param {Number} id The item's ID
     */
    load($modal, model_name, model_provider, id) {

        $modal.html('<p>Loading...</p>');

        $.ajax({
                'url': window.SITE_URL + 'api/admin/notes',
                'data': {
                    'model_name': model_name,
                    'model_provider': model_provider,
                    'id': id
                }
            })
            .done((response) => {

                let $ul = $('<ul>').addClass('admin-notes');

                if (response.data.length) {
                    for (let i = 0, j = response.data.length; i < j; i++) {
                        $ul.append(
                            Notes.renderMessageItem(
                                response.data[i].message,
                                response.data[i].user,
                                response.data[i].date
                            )
                        );
                    }
                } else {
                    $ul.append($('<li>').html('<p class"admin-notes__empty">No notes recorded for this item</p>'));
                }

                let $formItem = $('<li>');
                let $textarea = $('<textarea>');
                let $btn = $('<button>')
                    .addClass('btn btn-block btn-primary')
                    .text('Add Note')
                    .on('click', () => {
                        $.ajax({
                                'url': window.SITE_URL + 'api/admin/notes',
                                'method': 'POST',
                                'data': {
                                    'model_name': model_name,
                                    'model_provider': model_provider,
                                    'id': id,
                                    'message': $textarea.val()
                                }
                            })
                            .done((response) => {
                                $textarea.val('');
                                $formItem
                                    .before(
                                        Notes.renderMessageItem(
                                            response.data.message,
                                            response.data.user,
                                            response.data.date
                                        )
                                    );

                            })
                            .error((response) => {
                                Notes.showError(response.responseText);
                            });
                    });

                $ul.append(
                    $formItem
                        .append($textarea)
                        .append($btn)
                );

                $modal.html($ul);
            })
            .error((response) => {
                Notes.showError(response.responseText);
            });
    };

    // --------------------------------------------------------------------------

    /**
     * Compiles the message ite
     * @param {String} message The message string
     * @param {Object} user The user object
     * @param {String} date The date string
     * @return {jQuery}
     */
    static renderMessageItem(message, user, date) {
        let $message = $('<div>').addClass('admin-notes__note__message').html(message);
        let $meta = $('<div>').addClass('admin-notes__note__meta');
        let $user = $('<span>').addClass('admin-notes__note__user').html(user.first_name + ' ' + user.last_name);
        let $date = $('<span>').addClass('admin-notes__note__date').html(date);
        return $('<li>')
            .addClass('admin-notes__note')
            .append($message)
            .append(
                $meta
                    .append($user)
                    .append($date)
            );
    };

    // --------------------------------------------------------------------------

    /**
     * Renders an error
     * @param {String} responseText The response text from the server
     */
    static showError(responseText) {
        let data;
        try {
            data = JSON.parse(responseText);

        } catch (e) {
            data = {'error': 'An unknown error occurred'};
        }

        //  @todo (Pablo - 2018-08-01) - Nicer errors
        alert(data.error);
    }
}

export default Notes;

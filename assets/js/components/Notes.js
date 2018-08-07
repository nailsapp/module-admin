/* export Notes */

/* globals $, jQuery */

class Notes {
    /**
     * Construct Notes
     * @return {Notes}
     */
    constructor() {
        $('.js-admin-notes')
            .each((index, e) => {

                let $btn = $(e.currentTarget);
                let $counter = $('<span>').addClass('admin-notes__counter');
                let modelName = $btn.data('model-name');
                let modelProvider = $btn.data('model-provider');
                let itemId = $btn.data('id');
                let title = $btn.data('modal-title') || 'Notes';
                let width = $btn.data('modal-width') || 500;
                let maxHeight = $btn.data('modal-max-height') || 750;

                $btn
                    .append($counter)
                    .on('click', () => {

                        let $modal = $('<div>')
                            .dialog({
                                modal: true,
                                title: title,
                                width: width,
                                maxHeight: maxHeight
                            });

                        this.load(
                            $modal,
                            modelName,
                            modelProvider,
                            itemId
                        );

                        return false;
                    });

                this.countNotes(modelName, modelProvider, itemId)
                    .done((count) => {
                        $counter.val(count);
                    });
            });

        return this;
    };

    // --------------------------------------------------------------------------

    /**
     * Load notes from the server
     * @param {jQuery} $modal The modal object
     * @param {String} modelName The model name
     * @param {String} modelProvider The model provider
     * @param {Number} itemId The item's ID
     */
    load($modal, modelName, modelProvider, itemId) {

        $modal.html($('<p>').text('Loading...'));

        base.loadNotes(modelName, modelProvider, itemId)
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
                    $ul.append(
                        $('<li>')
                            .addClass('admin-notes__empty')
                            .append($('<p>').text('No notes recorded for this item'))
                    );
                }

                let $formItem = $('<li>');
                let $textarea = $('<textarea>');
                let $btn = $('<button>')
                    .addClass('btn btn-block btn-primary')
                    .text('Add Note')
                    .on('click', () => {

                        this.saveNote(modelName, modelProvider, itemId, $textarea.val())
                            .done((response) => {
                                $textarea.val('');
                                $('.admin-notes__empty', $modal).remove();
                                $formItem
                                    .before(
                                        Notes.renderMessageItem(
                                            response.data.message,
                                            response.data.user,
                                            response.data.date
                                        )
                                    );

                                $modal
                                    .dialog('option', 'position', 'center');
                            });
                    });

                $ul.append(
                    $formItem
                        .append($textarea)
                        .append($btn)
                );

                $modal.html($ul);
            });
    };

    // --------------------------------------------------------------------------

    /**
     * Loads notes from the server
     * @param {String} modelName The model name
     * @param {String} modelProvider The model provider
     * @param {Number} itemId The ID of the item
     * @return {jQuery.Deferred}
     */
    countNotes(modelName, modelProvider, itemId) {
        let $deferred = new $.Deferred();
        $.ajax({
                'url': window.SITE_URL + 'api/admin/notes/count',
                'data': {
                    'model_name': modelName,
                    'model_provider': modelProvider,
                    'id': itemId
                }
            })
            .done((response) => {
                $deferred.resolve(response.data);
            })
            .fail((response) => {
                Notes.showError(response.responseText);
                $deferred.reject(response.responseText);
            });

        return $deferred.promise();
    }

    // --------------------------------------------------------------------------

    /**
     * Loads notes from the server
     * @param {String} modelName The model name
     * @param {String} modelProvider The model provider
     * @param {Number} itemId The ID of the item
     * @return {jQuery.Deferred}
     */
    loadNotes(modelName, modelProvider, itemId) {
        let $deferred = new $.Deferred();
        $.ajax({
                'url': window.SITE_URL + 'api/admin/notes',
                'data': {
                    'model_name': modelName,
                    'model_provider': modelProvider,
                    'id': itemId
                }
            })
            .done((response) => {
                $deferred.resolve(response);
            })
            .fail((response) => {
                Notes.showError(response.responseText);
                $deferred.reject(response.responseText);
            });

        return $deferred.promise();
    }

    // --------------------------------------------------------------------------

    /**
     * Save a new note to the server
     * @param {String} modelName The model name
     * @param {String} modelProvider The model provider
     * @param {Number} itemId The ID of the item
     * @param {String} message The message to save
     * @return {jQuery.Deferred}
     */
    saveNote(modelName, modelProvider, itemId, message) {
        let $deferred = new $.Deferred();
        $.ajax({
                'url': window.SITE_URL + 'api/admin/notes',
                'method': 'POST',
                'data': {
                    'model_name': modelName,
                    'model_provider': modelProvider,
                    'id': itemId,
                    'message': message
                }
            })
            .done((response) => {
                $deferred.resolve(response);
            })
            .fail((response) => {
                Notes.showError(response.responseText);
                $deferred.reject(response.responseText);
            });

        return $deferred.promise();
    }

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
        let $user = $('<span>').addClass('admin-notes__note__meta__user').html(user.first_name + ' ' + user.last_name);
        let $date = $('<span>').addClass('admin-notes__note__meta__date').html(date);
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

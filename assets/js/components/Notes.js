/* export Notes */

/* globals $, jQuery */
class Notes {
    /**
     * Construct Notes
     * @return {Notes}
     */
    constructor() {
        $('.js-admin-notes')
            .each((index, element) => {

                let $btn = $(element);
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
                                maxHeight: maxHeight,
                                position: {
                                    my: 'center',
                                    at: 'center',
                                    of: window
                                }
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
                        this.setCounter(count);
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

        this.loadNotes(modelName, modelProvider, itemId)
            .done((response) => {

                let $ul = $('<ul>').addClass('admin-notes');
                let $empty = $('<li>')
                    .addClass('admin-notes__empty')
                    .append($('<p>').text('No notes recorded for this item'));

                $ul.append($empty);

                if (response.data.length) {
                    $empty.hide();
                    for (let i = 0, j = response.data.length; i < j; i++) {
                        $ul.append(
                            this.renderMessageItem(
                                $modal,
                                response.data[i].id,
                                response.data[i].message,
                                response.data[i].user,
                                response.data[i].date
                            )
                        );
                    }
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
                                $('.admin-notes__empty', $modal).hide();
                                $formItem
                                    .before(
                                        this.renderMessageItem(
                                            $modal,
                                            response.data.id,
                                            response.data.message,
                                            response.data.user,
                                            response.data.date
                                        )
                                    );

                                this.setCounter(('.admin-notes__note', $modal).length)
                                    .centerModal($modal, true);
                            });
                    });

                $ul.append(
                    $formItem
                        .append($textarea)
                        .append($btn)
                );

                $modal.html($ul);
                this.centerModal($modal, true);
            });
    };

    // --------------------------------------------------------------------------

    /**
     * Centers the modal in the screen
     * @param {jQuery} $modal The modal object
     * @param {boolean} scrollToBottom Whether tos croll the view to the bottom
     * @return {Notes}
     */
    centerModal($modal, scrollToBottom) {
        $modal
            .dialog(
                'option',
                'position',
                {
                    my: 'center',
                    at: 'center',
                    of: window
                }
            );

        if (scrollToBottom) {
            $modal
                .animate(
                    {
                        scrollTop: $modal.find('.admin-notes').outerHeight()
                    },
                    200
                );
        }

        return this;
    }

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
                'url': window.SITE_URL + 'api/admin/note/count',
                'data': {
                    'model_name': modelName,
                    'model_provider': modelProvider,
                    'item_id': itemId
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
                'url': window.SITE_URL + 'api/admin/note',
                'data': {
                    'model_name': modelName,
                    'model_provider': modelProvider,
                    'item_id': itemId
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
                'url': window.SITE_URL + 'api/admin/note',
                'method': 'POST',
                'data': {
                    'model_name': modelName,
                    'model_provider': modelProvider,
                    'item_id': itemId,
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
     * Delete a particular note
     * @param {Number} id The ID of the note to delete
     * @return {jQuery.Deferred}
     */
    deleteNote(id) {
        let $deferred = new $.Deferred();
        $.ajax({
                'url': window.SITE_URL + 'api/admin/note/' + id,
                'method': 'DELETE'
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
     * Compiles the message item
     * @param {Number} id The message ID
     * @param {String} message The message string
     * @param {Object} user The user object
     * @param {String} date The date string
     * @return {jQuery}
     */
    renderMessageItem($modal, id, message, user, date) {

        let $li = $('<li>').addClass('admin-notes__note');
        let $message = $('<div>').addClass('admin-notes__note__message').html(message);
        let $delete = $('<button>').addClass('admin-notes__note__delete').html('&times;');
        let $meta = $('<div>').addClass('admin-notes__note__meta');
        let $user = $('<span>').addClass('admin-notes__note__meta__user').html(user.first_name + ' ' + user.last_name);
        let $date = $('<span>').addClass('admin-notes__note__meta__date').html(date);

        $delete
            .on('click', () => {
                this.deleteNote(id)
                    .done(() => {
                        $li.remove();
                        this.centerModal($modal, false);
                        let count = $('.admin-notes__note', $modal).length;
                        this.setCounter(count);
                        if (count === 0) {
                            $('.admin-notes__empty', $modal).show();
                        }
                    });
                return false;
            });

        return $li
            .append($message)
            .append($delete)
            .append(
                $meta
                    .append($user)
                    .append($date)
            );
    };

    // --------------------------------------------------------------------------

    /**
     * Set the counter to a specific value
     * @param {Number} count The value
     * @return {Notes}
     */
    setCounter(count) {
        $('.admin-notes__counter').text(count || '');
        return this;
    }

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

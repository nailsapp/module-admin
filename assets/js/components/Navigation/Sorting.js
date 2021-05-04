class Sorting {

    /**
     * Construct Sorting
     * @param {Navigation} mainController The main Navigation controller
     */
    constructor(mainController) {
        this.mainController = mainController;

        $('.sidebar > ul.modules')
            .sortable({
                axis: 'y',
                placeholder: 'sort-placeholder',
                items: 'li.module.sortable',
                handle: '.handle',
                start: function(e, ui) {
                    ui.placeholder.height(ui.helper.outerHeight());
                    ui.placeholder.append(document.createElement('div'))
                },
                stop: () => {
                    this.mainController.saveState()
                }
            });
    }
}

export default Sorting;

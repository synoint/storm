const $ = require('jquery');
class AnswersHandler {

    constructor() {
        this.container = $("#page-container");
    }

    bindEvents() {
        this.container.on('change', '.exclusive', function (e) {
            $(e.currentTarget.closest('.custom-control.custom-checkbox')).find('.form-check').each(function() {
                const input = $(this).find('input');
                if(input.get(0) !== $(e.currentTarget).get(0)) {
                    input.prop("checked", false);
                }
            });
        });

    }
}

(function () {

    let handler = new AnswersHandler();

    handler.bindEvents();

})();

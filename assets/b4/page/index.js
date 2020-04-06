const $ = require('jquery');
class Page {

    constructor() {
        this.container = $("body");
    }

    bindEvents() {
        this.container.on('change', '.exclusive', function (e) {
            $(e.currentTarget.closest('.custom-control.custom-checkbox')).find('.form-check').each(function() {
                const input = $(this).find('input');
                if(!input.is($(e.currentTarget))) {
                    input.prop("checked", false);
                    input.prop('disabled', !input.prop('disabled'));
                }
            });
        });
    }
}

(function () {

    let handler = new Page();

    handler.bindEvents();

})();

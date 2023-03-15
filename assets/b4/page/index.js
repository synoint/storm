class Page {
    constructor() {
        this.container = $("body");
    }

    bindEvents() {
        const self = this;

        this.container.on('change', '.exclusive', function (e) {
            $(e.currentTarget.closest('.custom-control.custom-checkbox')).find('.form-check').each(function () {
                const input = $(this).find('input');

                if (!input.is($(e.currentTarget))) {
                    input.prop("checked", false);
                    input.prop('disabled', !input.prop('disabled'));
                }
            });
        });

        this.container.on('keyup', '.free-text-input', function (e) {
            const checkbox = $(e.currentTarget.closest('.form-check')).find('.custom-control-input');

            if (checkbox.length > 0) {
                if ($(this).val() !== '') {
                    checkbox.prop("checked", true);
                } else {
                    checkbox.prop("checked", false);
                }

                self.clearSingleOtherOptionFreeText(checkbox);
            }
        });

        this.container.on('change', '.custom-control-input', function (e) {
            const inputTarget = $(e.currentTarget);

            self.clearSingleOtherOptionFreeText(inputTarget);

            if (inputTarget.closest('.custom-checkbox-filled').get(0)) {
                inputTarget.closest('.form-check').find('.free-text-input').val('');
            }
        });

        // line below rewrites history which is loaded when back button is pressed, so form would show already filled answers
        window.history.replaceState(null, null, window.location);
    }

    clearSingleOtherOptionFreeText(input) {
        const formCheck = input.closest('.form-check');

        formCheck.closest('.custom-radio-filled').find('.form-check').each(function () {
            if (formCheck.get(0) !== $(this).get(0)) {
                $(this).find('.free-text-input').val('');
            }
        });
    }
}

(function () {
    let handler = new Page();

    handler.bindEvents();
})();

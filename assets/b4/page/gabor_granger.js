class GaborGranger {

    constructor() {
        this.submitButtonSelector       = ".submit-gabor-answer__button";
        this.priceHolderInputsSelector  = ".price__holder input";
        this.gaborGrangerSelector       = ".gabor-granger__holder";
        this.displayedAnswerSelector    = ".displayed-answer";
        this.answerResultSelector       = ".answer__result";
        this.answerButtonsSelector      = ".answer__buttons";
        this.zeroInputClass             = "zero__input";
    }

    initialize() {
        const self = this;
        const body = $("body");

        body.on('click', this.submitButtonSelector, function (event) {
            self.submitAnswer($(this));
        });
    }

    submitAnswer(el) {

        const nextAnswer = this.getNextAnswer(el);
        this.selectAnswer(el, nextAnswer);

        if(nextAnswer && nextAnswer.hasClass(this.zeroInputClass)){
            this.submitForm(el);
        } else {
            if(nextAnswer && nextAnswer.length > 0){
                this.loadNextAnswer(nextAnswer);
            } else {
                this.submitForm(el);
            }
        }
    }

    loadNextAnswer(nextAnswer) {

        const questionHolder  = nextAnswer.closest(this.gaborGrangerSelector);
        const displayedAnswer = questionHolder.find(this.displayedAnswerSelector);

        displayedAnswer.html(nextAnswer.data("label"));
        displayedAnswer.data("val", nextAnswer.attr("value"));
    }

    submitForm(el) {

        const questionHolder  = el.closest(this.gaborGrangerSelector);

        const lastAnswer             = questionHolder.find(this.priceHolderInputsSelector+":checked");
        const answerResult           = questionHolder.find(this.answerResultSelector);
        const answerButtons          = questionHolder.find(this.answerButtonsSelector);
        const displayedAnswer        = questionHolder.find(this.displayedAnswerSelector);
        const correspondingTextInput = questionHolder.find("#answer"+lastAnswer.attr("value") + "text");

        displayedAnswer.html(lastAnswer.data("label"));
        displayedAnswer.data("val", lastAnswer.attr("value"));

        answerButtons.addClass("d-none");
        answerResult.removeClass("d-none");
    }

    selectAnswer(el, nextAnswer) {

        const questionHolder         = el.closest(this.gaborGrangerSelector);

        const answerId               = questionHolder.find(this.displayedAnswerSelector).data("val");
        const priceHolderInputs      = questionHolder.find(this.priceHolderInputsSelector);
        const correspondingInput     = questionHolder.find("#answer"+answerId);
        const correspondingTextInput = questionHolder.find("#answer"+answerId + "text");

        if(this.agreed(el)){
            priceHolderInputs.prop("checked", false);
            correspondingInput.prop("checked", true);
        }

        correspondingTextInput.val(el.html());

        if(nextAnswer && nextAnswer.hasClass(this.zeroInputClass) && !this.agreed(el)){
            priceHolderInputs.prop("checked", false);
            questionHolder.find("." + this.zeroInputClass).prop("checked", true);
        } else {
            if (!nextAnswer && !questionHolder.find(this.priceHolderInputsSelector + ":checked").length) {
                priceHolderInputs.prop("checked", false);
                correspondingInput.prop("checked", true);
            }
        }
    }

    getNextAnswer(el) {

        const questionHolder     = el.closest(this.gaborGrangerSelector);

        const answerId           = questionHolder.find(this.displayedAnswerSelector).data("val");
        const priceHolderInputs  = questionHolder.find(this.priceHolderInputsSelector);
        const correspondingInput = questionHolder.find("#answer"+answerId);
        const index              = correspondingInput.index();
        let nextPriceInput;

        correspondingInput.data("shown", 1);

        if(this.agreed(el)){
            nextPriceInput = priceHolderInputs.eq(parseInt(index) + 1);
        } else {

            const prevIndex = parseInt(index) - 1;
            if(0 <= prevIndex) {
                nextPriceInput = priceHolderInputs.eq(prevIndex);
            }
        }

        if(nextPriceInput && nextPriceInput.data("shown")){
            nextPriceInput = null;
        }

        return nextPriceInput;
    }

    agreed(el) {
        return el.data("agreed");
    }
}

(function () {
    let gaborGranger = new GaborGranger();
    gaborGranger.initialize();
})();
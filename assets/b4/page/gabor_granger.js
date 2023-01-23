class GaborGranger {

    constructor() {
        this.submitButtonSelector       = ".submit-gabor-answer__button";
        this.priceHolderInputsSelector  = ".price__holder input";
        this.gaborGrangerSelector       = ".gabor-granger__holder";
        this.displayedAnswerSelector    = ".displayed-answer";
        this.answerResultSelector       = ".answer__result";
        this.answerButtonsSelector      = ".answer__buttons";
        this.zeroInputClass             = "zero__input";
        this.biggestAgreedSelector      = ".biggest-agreed";
        this.biggestDeclinedSelector    = ".biggest-declined";
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

        const questionHolder    = el.closest(this.gaborGrangerSelector);
        const priceHolderInputs = questionHolder.find(this.priceHolderInputsSelector);

        const selectedAnswerId = questionHolder.find(this.biggestAgreedSelector).data("answer");
        let selectedAnswer;

        if(!selectedAnswerId){
            // get won't buy answer as answer
            selectedAnswer = questionHolder.find("."+this.zeroInputClass);
        } else {
            selectedAnswer = questionHolder.find(selectedAnswerId);
        }

        priceHolderInputs.prop("checked", false);
        selectedAnswer.prop("checked", true);

        const answerResult           = questionHolder.find(this.answerResultSelector);
        const answerButtons          = questionHolder.find(this.answerButtonsSelector);
        const displayedAnswer        = questionHolder.find(this.displayedAnswerSelector);

        displayedAnswer.html(selectedAnswer.data("label"));
        displayedAnswer.data("val", selectedAnswer.attr("value"));


        answerButtons.addClass("d-none");
        answerResult.removeClass("d-none");

        // submit page
        this.submitPageIfLastInPage(el);

    }

    submitPageIfLastInPage(el) {
        const currentQuestionIndex = el.closest(".question").index();
        const totalQuestions       = el.closest(".questions").find(".question").length;

        if(currentQuestionIndex === (totalQuestions-1)){
            $("#p_next").trigger("click");
        }
    }

    getNextAnswer(el) {

        const questionHolder     = el.closest(this.gaborGrangerSelector);
        const answerId           = questionHolder.find(this.displayedAnswerSelector).data("val");
        const correspondingInput = questionHolder.find("#answer"+answerId);
        const index              = correspondingInput.index();
        let nextPriceInput;

        correspondingInput.data("shown", 1);

        if(this.agreed(el)){

            questionHolder.find(this.biggestAgreedSelector).val(correspondingInput.val());
            questionHolder.find(this.biggestAgreedSelector).data("answer", correspondingInput);

            nextPriceInput = this.getNextRandomBigger(el, index);
        } else {

            nextPriceInput = this.getNextRandomSmaller(el, index);

            questionHolder.find(this.biggestDeclinedSelector).val(correspondingInput.val());
            questionHolder.find(this.biggestDeclinedSelector).data("answer", correspondingInput);
        }

        return nextPriceInput;
    }

    getNextRandomBigger(el, index) {

        const self = this;
        const questionHolder       = el.closest(this.gaborGrangerSelector);
        const priceHolderInputs    = questionHolder.find(this.priceHolderInputsSelector);
        const biggestValue         = questionHolder.find(this.biggestAgreedSelector).val();
        const biggestDeclinedValue = questionHolder.find(this.biggestDeclinedSelector).val();
        let nextAnswers            = [];
        let nextAnswer;

        priceHolderInputs.each(function(){

            if($(this).index() > index && !$(this).data("shown") &&
                biggestValue < $(this).val() &&
                (!biggestDeclinedValue || biggestDeclinedValue > $(this).val()) &&
                !$(this).hasClass(self.zeroInputClass)){
                nextAnswers.push($(this));
            }
        });

        if(nextAnswers.length > 0){
            nextAnswers = this.shuffle(nextAnswers);
            nextAnswer  = nextAnswers[0];
        }

        return nextAnswer;
    }

    getNextRandomSmaller(el, index) {

        const self = this;
        const questionHolder       = el.closest(this.gaborGrangerSelector);
        const priceHolderInputs    = questionHolder.find(this.priceHolderInputsSelector);
        const biggestValue         = questionHolder.find(this.biggestAgreedSelector).val();
        const biggestDeclinedValue = questionHolder.find(this.biggestDeclinedSelector).val();
        let nextAnswers            = [];
        let nextAnswer;

        priceHolderInputs.each(function(){
            if($(this).index() < index && !$(this).data("shown") &&
                biggestValue < $(this).val() &&
                (!biggestDeclinedValue || biggestDeclinedValue > $(this).val()) &&
                !$(this).hasClass(self.zeroInputClass)) {
                nextAnswers.push($(this));
            }
        });

        if(nextAnswers.length > 0){
            nextAnswers = this.shuffle(nextAnswers);
            nextAnswer  = nextAnswers[0];
        }

        return nextAnswer;
    }

    shuffle(array) {
        let currentIndex = array.length,  randomIndex;

        // While there remain elements to shuffle.
        while (currentIndex !== 0) {

            // Pick a remaining element.
            randomIndex = Math.floor(Math.random() * currentIndex);
            currentIndex--;

            // And swap it with the current element.
            [array[currentIndex], array[randomIndex]] = [
                array[randomIndex], array[currentIndex]];
        }

        return array;
    }

    agreed(el) {
        return el.data("agreed");
    }
}

(function () {
    let gaborGranger = new GaborGranger();
    gaborGranger.initialize();
})();
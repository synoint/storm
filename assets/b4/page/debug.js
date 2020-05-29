const $ = require('jquery');
class Debug {

    constructor() {
        this.container = $("#debug__block");
        this.pageContent = $(".page-content");
    }

    initialize() {
        this.pageContent.css("padding-bottom", this.container.height() + "px");
    }
}

(function () {
    let handler = new Debug();
    handler.initialize();
})();

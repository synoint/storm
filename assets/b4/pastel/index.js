// common styles
require("../common/b4.css");
require("../common/style.css");
// application overrides
require("./app.css");

const $ = require("jquery");
// this "modifies" the jquery module: adding behavior to it
// the bootstrap module doesn't export/return anything
require("bootstrap");

// common images
const imagesContext = require.context(
	"./images",
	true,
	/\.(png|jpg|jpeg|gif|ico|svg|webp)$/
);
imagesContext.keys().forEach(imagesContext);

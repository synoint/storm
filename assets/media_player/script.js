import "../../node_modules/plyr/src/sass/plyr.scss";
import Plyr from "plyr";

class MediaPlayer {
	constructor(el) {
		this.player = new Plyr("#media-player");
		this.container = el;
	}

	hideControls() {
		this.container.addEventListener("ready", (event) => {
			const player = event.detail.plyr;
			player.config.hideControls = false;
		});
	}
	toggleControls(val) {
		this.player.toggleControls(val);
	}
}

(function () {
	const container = document.querySelector("#media-player");
	const mediaplayer = new MediaPlayer(container);
	const fullscreen = mediaplayer.container.getAttribute("fullscreen");
	const required = mediaplayer.container.getAttribute("required");

	if (fullscreen === "false") {
		const button = document.querySelector('button[data-plyr="fullscreen"]');
		button.style = "display:none";
	}

	if (required === "true") {
		const button = document.querySelector("#page_next");
		button.setAttribute("disabled", "true");
		mediaplayer.toggleControls(false);
		mediaplayer.hideControls();
		mediaplayer.player.on("ended", (ev) => {
			button.removeAttribute("disabled");
			mediaplayer.toggleControls(true);
		});
	}
})();

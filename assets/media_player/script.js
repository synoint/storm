import "../../node_modules/plyr/src/sass/plyr.scss";
import Plyr from "plyr";

class MediaPlayer {
	constructor(el, required) {

		let options = {};

		if(required) {
			options = {
				listeners: {
					seek: function (e) {
						e.preventDefault()
						return false;
					}
				}
			};
		}

		this.player = new Plyr("#media-player", options);
		this.container = el;
	}
}

(function () {
	const container = document.querySelector("#media-player");
	const required = container.getAttribute("data-required");
	const autoplay = container.getAttribute("data-autoplay");
	const mediaPlayer = new MediaPlayer(container, required);

	if(autoplay === "true"){
		mediaPlayer.player.on('ready', () => {
			mediaPlayer.player.muted = true;
			mediaPlayer.player.play();
		})
	}

	if (required === "true") {
		const button = document.querySelector("#page_next");
		button.setAttribute("disabled", "true");

		mediaPlayer.player.on("ended", (ev) => {
			button.removeAttribute("disabled");
		});
	}
})();

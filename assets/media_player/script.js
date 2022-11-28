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

		this.player = new Plyr(el, options);
		this.container = el;
	}
}

(function () {

	const containers = document.getElementsByClassName('media-player');

	Array.prototype.forEach.call(containers, function(el, index, array) {
		const required = el.getAttribute("data-required");
		const autoplay = el.getAttribute("data-autoplay");
		const mediaPlayer = new MediaPlayer(el, required);

		if(autoplay === "true"){
			mediaPlayer.player.on('ready', () => {
				mediaPlayer.player.muted = true;
				mediaPlayer.player.play();
			})
		}

		if (required === "true") {
			const button = document.querySelector("#p_next");
			button.setAttribute("disabled", "true");

			mediaPlayer.player.on("ended", (ev) => {
				button.removeAttribute("disabled");
			});
		}
	});
	
	const container = document.querySelector("#media-player");

	if(container !== null){

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
			const button = document.querySelector("#p_next");
			button.setAttribute("disabled", "true");

			mediaPlayer.player.on("ended", (ev) => {
				button.removeAttribute("disabled");
			});
		}
	}
})();

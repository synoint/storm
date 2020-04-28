import '../../node_modules/plyr/src/sass/plyr.scss';
import Plyr from 'plyr';

class MediaPlayer {
    constructor() {
        const player = new Plyr('.media-player');
    }
}

(function () {
    let mediaPlayer = new MediaPlayer();
})();
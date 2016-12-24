## Podcast 

This extension adds the ability to easily create publishable podcasts.

Also included is a fancy audio player. You can add this player by using this html element: `<audio src="example.com/!/podcast/episode/1.mp3" preload="none" />` and by including the following files in the controller

*   `S::$css[] = '/includes/extensions/podcast/mediaelement/mediaelementplayer.css';`
*   `S::$js[] = '/includes/extensions/podcast/mediaelement/mediaelement-and-player.min.js';`

You will also need to enable the player
`S::$js_onload[] = "$('video,audio').mediaelementplayer();";`

Now you will have some fancy audio players!

>TODO: incomplete documentation
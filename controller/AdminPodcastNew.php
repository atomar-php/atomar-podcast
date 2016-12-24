<?php

namespace podcast\controller;

use atomic\core\Auth;
use atomic\core\Lightbox;
use podcast\PodcastAPI;

class AdminPodcastNew extends Lightbox {

    function GET($matches = array()) {
        // require authentication
        if (!Auth::has_authentication('create_podcast')) {
            set_error('You do not have permission to create podcasts.');
            $this->dismiss();
        }

        // configure lightbox
        $this->auto_height(true);
        $this->width(500);
        $this->header('New Podcast');


        // render page
        echo $this->render_view('/podcast/views/modal.podcast.new.html');
    }

    function POST($matches = array()) {
        // require authentication
        if (!Auth::has_authentication('create_podcast')) {
            set_error('You do not have permission to create podcasts');
            $this->redirect();
        }

        $podcast = PodcastAPI::create_podcast($_REQUEST);
        if ($podcast) {
            set_success('Podcast successfully created!');
        } else {
            set_error('Unable to create podcast.');
        }
        $this->redirect();
    }

    /**
     * This method will be called before GET, POST, and PUT when the lightbox is returned to e.g. when using lightbox.dismiss_url or lightbox.return_url
     */
    function RETURNED() {

    }
}
<?php

namespace podcast\controller;

use atomic\core\Auth;
use atomic\core\Controller;
use atomic\core\Templator;

class AdminPodcastView extends Controller {
    function GET($matches = array()) {
        Auth::authenticate('administer_podcast');
        Templator::$css[] = '/includes/extensions/podcast/css/style.css';

        $podcast = \R::load('podcast', $matches['id']);

        // preload
        $podcast->with(' AND is_deleted<>\'1\' ORDER BY recorded_at DESC ')->ownPodcastepisodeList;

        if ($podcast->id) {
            // render page
            echo $this->render_view('podcast/views/admin.podcast.view.html', array(
                'podcast' => $podcast
            ));
        } else {
            set_error('Unknown podcast');
            $this->go('/admin/podcast/');
        }
    }

    function POST($matches = array()) {
        $this->go('/admin/podcast/');
    }
}
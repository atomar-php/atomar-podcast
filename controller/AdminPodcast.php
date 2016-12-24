<?php

namespace podcast\controller;

use atomic\core\Auth;
use atomic\core\Controller;
use atomic\core\Templator;

class AdminPodcast extends Controller {
    function GET($matches = array()) {
        Auth::authenticate('administer_podcast');
        Templator::$css[] = Templator::resolve_ext_asset('podcast/css/style.css');
        $podcasts = \R::findAll('podcast');

        // preload
        foreach ($podcasts as $p) {
            $p->with(' AND is_deleted<>\'1\' ')->ownPodcastepisodeList;
        }

        // render page
        echo $this->render_view('podcast/views/admin.podcast.html', array(
            'feeds' => $podcasts
        ));
    }

    function POST($matches = array()) {
        $this->go('/admin/podcast');
    }
}
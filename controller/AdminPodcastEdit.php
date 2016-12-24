<?php

namespace podcast\controller;

use atomic\core\Auth;
use atomic\core\Lightbox;

class AdminPodcastEdit extends Lightbox {

    function GET($matches = array()) {
        // require authentication
        if (!Auth::has_authentication('edit_podcast')) {
            set_error('You do not have permission to edit podcasts.');
            $this->dismiss();
        }

        $p = \R::load('podcast', $matches['id']);
        if ($p->id) {
            // configure lightbox
            $this->auto_height(true);
            $this->width(500);
            $this->header('Edit Podcast <small>' . $p->title . '</small>');

            // render page
            echo $this->render_view('/podcast/views/modal.podcast.edit.html', array(
                'podcast' => $p
            ));
        } else {
            set_error('Unknown podcast');
            $this->redirect();
        }
    }

    function POST($matches = array()) {
        // require authentication
        if (!Auth::has_authentication('edit_podcast')) {
            set_error('You do not have permission to create podcasts');
            $this->redirect();
        }

        $p = \R::load('podcast', $matches['id']);
        if ($p->id) {
            $p->title = $_REQUEST['title'];
            $p->description = $_REQUEST['description'];
            if (store($p)) {
                set_success('The podcast was successfully updated');
            } else {
                set_error('The podcast could not be updated.');
            }
        } else {
            set_error('Unknown podcast');
        }
        $this->redirect();
    }

    /**
     * This method will be called before GET, POST, and PUT when the lightbox is returned to e.g. when using lightbox.dismiss_url or lightbox.return_url
     */
    function RETURNED() {

    }
}
<?php

namespace podcast\controller;

use atomic\core\Auth;
use atomic\core\Lightbox;
use atomic\core\Templator;

class AdminPodcastEpisodeEdit extends Lightbox {

    function GET($matches = array()) {
        // require authentication
        if (!Auth::has_authentication('edit_podcast_episode')) {
            set_error('You do not have permission to edit podcasts');
            $this->redirect('/');
        }

        $p = \R::load('podcastepisode', $matches['id']);
        if ($p->id) {
            $date = $p->recorded_at;
            Templator::$js_onload[] = <<<JAVASCRIPT
$('#date').datetimepicker({
  maskInput:true,
  pickTime:false
});
$('#date').datetimepicker('setDate', new Date('$date'));
$('#date').datetimepicker('update');
JAVASCRIPT;

            // configure lightbox
            $this->auto_height(true);
            $this->width(500);
            $this->header('Edit Episode <small>' . $p->title . '</small>');

            // render page
            echo $this->render_view('/podcast/views/modal.podcast.episode.edit.html', array(
                'episode' => $p
            ));
        } else {
            set_error('Unknown episode');
            $this->redirect();
        }
    }

    function POST($matches = array()) {
        // require authentication
        if (!Auth::has_authentication('edit_podcast_episode')) {
            set_error('You do not have permission to edit podcasts');
            $this->redirect('/');
        }

        $p = \R::load('podcastepisode', $matches['id']);
        if ($p->id) {
            $p->title = $_REQUEST['title'];
            $p->recorded_at = db_date(strtotime($_REQUEST['date']));
            $p->description = $_REQUEST['description'];
            if (store($p)) {
                set_success('The episode has been updated');
            } else {
                set_error('The episode could not be updated');
            }
        } else {
            set_error('Unknown episode');
        }
        $this->redirect();
    }

    /**
     * This method will be called before GET, POST, and PUT when the lightbox is returned to e.g. when using lightbox.dismiss_url or lightbox.return_url
     */
    function RETURNED() {

    }
}
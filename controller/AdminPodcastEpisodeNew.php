<?php

namespace podcast\controller;

use atomic\core\Auth;
use atomic\core\Lightbox;
use atomic\core\Templator;
use file_drop\FileDropAPI;
use podcast\PodcastAPI;

class AdminPodcastEpisodeNew extends Lightbox {

    function GET($matches = array()) {
        // require authentication
        if (!Auth::has_authentication('create_podcast_episode')) {
            set_error('You do not have permission to create podcast episodes');
            $this->dismiss();
        }

        FileDropAPI::deploy('#dropzone', array(
            'maxFileSize' => 100 * 1024 * 1024, // 100MB
            'maxFiles' => 1,
            'targetPath' => 'sermons/',
            'showFilesOnComplete' => true,
            'callbackDone' => 'assignSermon',
            'callbackDelete' => 'deleteSermon'
        ));

        Templator::$js_onload[] = <<<JAVASCRIPT
$('#date').datetimepicker({
  maskInput:true,
  pickTime:false
});
$('#date').datetimepicker('setDate', new Date());
$('#date').datetimepicker('update');
function assignSermon(data) {
  $('#file-id').val(data.id);
  $('button[type=submit]').attr('disabled', false);
  global.lightbox.resize(global.lightbox.width, 540);
}
function deleteSermon(data) {
  $('#file-id').val('');
  $('button[type=submit]').attr('disabled', true);
  global.lightbox.resize(global.lightbox.width, 510);
}
JAVASCRIPT;

        $p = \R::load('podcast', $matches['id']);
        if ($p->id) {
            // configure lightbox
            $this->height(510);
            $this->width(500);
            $this->header('Create Episode');

            // render page
            echo $this->render_view('/podcast/views/modal.podcast.episode.new.html', array(
                'podcast' => $p
            ));
        } else {
            set_error('Unknown podcast');
            $this->redirect();
        }
    }

    function POST($matches = array()) {
        // require authentication
        if (!Auth::has_authentication('create_podcast_episode')) {
            set_error('You do not have permission to create podcast episodes');
            $this->dismiss();
        }

        $p = \R::load('podcast', $matches['id']);
        if ($p->id) {

            // fetch the uploaded file
            $file = \R::load('file', $_REQUEST['file_id']);
            if ($file->id) {
                // create new episode
                $episode = PodcastAPI::create_episode(array(
                    'description' => $_REQUEST['description'],
                    'recorded_at' => db_date(strtotime($_REQUEST['date'])),
                    'file' => $file,
                    'podcast_id' => $p->id
                ));
                if ($episode) {
                    $link = $file->link_to($episode);
                    if (!$link) {
                        set_warning('The file could not be linked to the episode');
                    }
                    set_success('The episode has been successfully created.');
                } else {
                    set_error('The episode could not be created.');
                    unlink($path);
                }
            } else {
                set_error('The file is required');
                $this->dismiss();
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
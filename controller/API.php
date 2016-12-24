<?php

namespace podcast\controller;

use atomic\Atomic;
use atomic\core\ApiController;
use atomic\core\Auth;
use file_drop\FileDropAPI;
use podcast\PodcastAPI;

class API extends ApiController {

    private $matches = array();

    function get_delete_podcast($id) {
        if (!Auth::has_authentication('edit_podcast')) {
            set_error('You do not have permission to delete podcasts');
        } else {
            $p = \R::load('podcast', $id);
            if ($p->id) {
                \R::trash($p);
                set_success('The podcast has been deleted.');
            } else {
                set_error('Unknown podcast');
            }
        }
        $this->go_back();
    }

    function get_refresh_id3($id) {
        if (!Auth::has_authentication('edit_podcast_episode')) {
            set_error('You do not have permission to edit podcast episodes');
        } else {
            $p = \R::load('podcastepisode', $id);
            if ($p->id) {
                $p = PodcastAPI::load_id3_tags($p);
                store($p);
                set_success('The episode has been updated with the ID3 tags.');
            } else {
                set_error('Unknown podcast');
            }
        }
        $this->go_back();
    }

    function get_enable_podcast($id, $enabled='0') {
        if (!Auth::has_authentication('edit_podcast')) {
            set_error('You do not have permission to edit podcasts');
        } else {
            $p = \R::load('podcast', $id);
            if ($p->id) {
                $p->is_enabled = $enabled == '1' ? '1' : '0';
                store($p);
            } else {
                set_error('Unknown podcast');
            }
        }
        $this->go_back();
    }

    function get_enable_episode($id, $enabled='0') {
        if (!Auth::has_authentication('edit_podcast_episode')) {
            set_error('You do not have permission to edit podcast episodes');
        } else {
            $p = \R::load('podcastepisode', $id);
            if ($p->id) {
                $p->is_enabled = $enabled == '1' ? '1' : '0';
                store($p);
                set_success('The episode has been ' . ($p->is_enabled == '1' ? 'en' : 'dis') . 'abled');
            } else {
                set_error('Unknown episode');
            }
        }
        $this->go_back();
    }

    function get_delete_episode($id) {
        if (!Auth::has_authentication('edit_podcast_episode')) {
            set_error('You do not have permission to delete podcast episodes');
        } else {
            $p = \R::load('podcastepisode', $id);
            if ($p->id) {
                if (PodcastAPI::delete_episode($p->id)) {
                    set_success('The episode has been deleted.');
                } else {
                    set_error('The episode could not be deleted');
                }
            } else {
                set_error('Unknown episode');
            }
        }
        $this->go_back();
    }

    function get_feed($id) {
        error_reporting(0);
        $p = \R::load('podcast', $id);

        // preload
        $p->with(' AND is_deleted<>\'1\' AND is_enabled=\'1\' ORDER BY recorded_at DESC ')->ownPodcastepisodeList;

        if ($p->id) {
            header('Content-Type: text/xml');
            echo $this->render_view('podcast/views/feed.html', array(
                'podcast' => $p,
                'site_name' => Atomic::$config['site_name'],
                'owner_name' => Atomic::$config['site_name'],
                'owner_email' => Atomic::$config['email']['contact_email'],
                'site_url' => Atomic::$config['site_url'],
                'channel_description' => variable_get('podcast_chanel_description'),
                'year' => date('Y'),
                'build_date' => date('D, d M Y H:i:s O'),
                'feed_url' => ltrim($_SERVER['REQUEST_URI'], '/')
            ));
        } else {
            header("HTTP/1.1 404 Not Found");
        }
        exit;
    }

    function get_episode() {
        $episode = \R::load('podcastepisode', $this->matches['id']);
        if ($episode->id) {
            $node = reset($episode->sharedFilenodeList);
            if ($node) {
                FileDropAPI::download_file($node->file);
            } else {
                set_error('The audio file is missing');
            }
        } else {
            set_error('unknown episode');
        }
        $this->go_back();
    }

    /**
     * Allows you to perform any additional actions before get requests are processed
     * @param array $matches
     */
    protected function setup_get($matches = array()) {
        $this->matches = $matches;
    }

    /**
     * Allows you to perform any additional actions before post requests are processed
     * @param array $matches
     */
    protected function setup_post($matches = array()) {
        $this->matches = $matches;
    }
}
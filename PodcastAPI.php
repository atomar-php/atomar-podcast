<?php

namespace podcast;

use atomic\core\Logger;
use file_drop\FileDropAPI;

/**
 * This is the internal api class that can be used by third party extensions
 */
class PodcastAPI {
    public static function create_podcast($options) {
        $defaults = array(
            'title' => null,
            'description' => null
        );
        $options = array_merge($defaults, $options);
        // validate fields
        foreach ($options as $key => $value) {
            if (in_array($key, $defaults) && $value == null) {
                return false;
            }
        }

        // create podcast
        $p = \R::dispense('podcast');
        $p->title = $options['title'];
        $p->description = $options['description'];
        $p->is_enabled = '1';

        if (store($p)) {
            return $p;
        } else {
            return false;
        }
    }

    // returns the podcast if it is enabled.
    public static function get_podcast($id) {
        $podcast = \R::load('podcast', $id);
        if ($podcast->is_enabled == '1') {
            return $podcast;
        } else {
            return false;
        }
    }

    /**
     * Finds and returns a podcast episode by it's file id.
     * @param $id the id of the file (not to be confused with the filenode id).
     * @return null|\RedBeanPHP\OODBBean
     */
    public static function get_episode_by_file_id($id) {
        $sql_episode_link = <<<SQL
SELECT
  `podcastepisode_id`
FROM `filenode_podcastepisode` AS `fe`
LEFT JOIN `filenode` AS `f` ON `f`.`id`=`fe`.`filenode_id`
WHERE
  `f`.`file_id`=?
LIMIT 1
SQL;
        $episode_id = \R::getCell($sql_episode_link, array($id));
        if ($episode_id) {
            return \R::load('podcastepisode', $episode_id);
        } else {
            return null;
        }
    }

    //  Returns the podcast episode if it is enabled
    public static function get_episode($id) {
        $episode = \R::load('podcastepisode', $id);
        if ($episode->is_enabled == '1' && $episode->is_deleted == '0') {
            return $episode;
        } else {
            return false;
        }
    }

    public static function delete_episode($id) {
        $episode = \R::load('podcastepisode', $id);
        if ($episode->id) {
            $episode->is_deleted = '1';
            return store($episode);
        } else {
            return false;
        }
    }

    public static function create_episode($options) {
        $defaults = array(
            'description' => '',
            'file' => null,
            'recorded_at' => db_date(),
            'podcast_id' => null
        );
        $options = array_merge($defaults, $options);
        // validate fields
        foreach ($options as $key => $value) {
            if (in_array($key, $defaults) && $value == null) {
                return false;
            }
        }

        $episode = \R::dispense('podcastepisode');
        $episode->description = $options['description'];
        $episode->created_at = db_date();
        $episode->recorded_at = $options['recorded_at'];
        $episode->is_enabled = '1';
        $episode->is_deleted = '0';

        $meta = FileDropAPI::fetch_file_meta($options['file']);
        $episode = self::load_id3_tags($episode, $meta['path']);

        $podcast = \R::load('podcast', $options['podcast_id']);
        $podcast->ownPodcastepisodeList[] = $episode;
        if (store($podcast)) {
            return $episode;
        } else {
            Logger::log_error($podcast->errors());
            return false;
        }
    }

    public static function load_id3_tags($episode, $file_path = '') {
        require_once('getid3/getid3/getid3.php');
        $getID3 = new \getID3();
        $info = $getID3->analyze($file_path);

        if (isset($info['tags']['id3v2']['title'])) {
            $episode->title = $info['tags']['id3v2']['title'][0];
        } else {
            $episode->title = $info['filename'];
        }
        if (isset($info['tags']['id3v2']['genre'])) {
            $episode->genre = $info['tags']['id3v2']['genre'][0];
        }
        if (isset($info['tags']['id3v2']['artist'])) {
            $episode->artist = $info['tags']['id3v2']['artist'][0];
        }
        $episode->length = $info['filesize'];
        $episode->duration = $info['playtime_string'];

        // make sure we get the hour field
        $parts = explode(':', $episode->duration);
        if (count($parts) == 2) {
            $episode->duration = '00:' . $episode->duration;
        }

        return $episode;
    }

    public static function twig_podcast_date($date = false) {
        if ($date === false || $date == '') {
            $time = time();
        } else {
            $time = strtotime($date);
        }
        return date('D, d M Y H:i:s O', $time);
    }
}
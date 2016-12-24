<?php

namespace podcast;

/**
 * Implements hook_permission()
 */
function permission() {
    return array(
        'administer_podcast',
        'access_podcast'
    );
}

/**
 * Implements hook_menu()
 */
function menu() {
    $items['secondary_menu']['/podcast'] = array(
        'link' => l('Podcast', '/admin/podcast'),
        'class' => array(),
        'weight' => 0,
        'access' => 'administer_podcast',
        'menu' => array()
    );
    return $items;
}

/**
 * Implements hook_model()
 */
function model() {
    return array();
}

/**
 * Implements hook_url()
 */
function url() {
    return array(
        '/!/podcast/(?P<api>[a-zA-Z0-9\_-]+)/?(\?.*)?' => 'podcast\controller\API',
        '/!/podcast/(?P<api>[a-zA-Z\_-]+)/(?P<id>[0-9]+)\.[a-zA-Z0-9]+(\?.*)?' => 'podcast\controller\API',
        '/admin/podcast/?(\?.*)?' => 'podcast\controller\AdminPodcast',
        '/admin/podcast/new/?(\?.*)?' => 'podcast\controller\AdminPodcastNew',
        '/admin/podcast/(?P<id>[0-9]+)/?(\?.*)?' => 'podcast\controller\AdminPodcastView',
        '/admin/podcast/(?P<id>[0-9]+)/edit/?(\?.*)?' => 'podcast\controller\AdminPodcastEdit',
        '/admin/podcast/(?P<id>[0-9]+)/new_episode/?(\?.*)?' => 'podcast\controller\AdminPodcastEpisodeNew',
        '/admin/podcast/episode/(?P<id>[0-9]+)/edit/?(\?.*)?' => 'podcast\controller\AdminPodcastEpisodeEdit'
    );
}

/**
 * Implements hook_libraries()
 */
function libraries() {
    return array(
        'PodcastAPI.php'
    );
}

/**
 * Implements hook_cron()
 */
function cron() {
    // execute actions to be performed on cron
}

/**
 * Implements hook_twig_function()
 */
function twig_function() {
    // return an array of key value pairs.
    // key: twig_function_name
    // value: actual_function_name
    // You may use object functions as well
    // e.g. ObjectClass::actual_function_name
    return array(
        'podcast_date' => 'podcast\PodcastAPI::twig_podcast_date'
    );
}

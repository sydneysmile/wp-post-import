<?php

/*
Plugin Name: SydneySmileNews
Plugin URI: http://wordpress.sydneysmile.net/
Description: A plugin to parse the json data from facebook to import the posts
Author: SydneySmile
Version: 1.0
Author URI: http://wordpress.sysneysmile.net/

@todo::provide admin interface to import post data
*/

add_filter('the_title', 'sydneysmilenews_title');

function sydneysmilenews_title($t) {
    return $t.' * ';
}

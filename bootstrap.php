<?php
/*
Plugin Name: WP JSHRINK
Description: JShrink minifier implemented for WordPress. Combines footer scripts equeued with <code>wp_enqueue_script</code> into a single minified js file.
Author: Vic M
Version: 1.1.3
Author URI: http://victor.miletskiy.name
Plugin URI: http://wordpress.org/extend/plugins/wp-jshrink/

  This plugin is released under version 3 of the GPL:
  http://www.opensource.org/licenses/gpl-3.0.html
*/

function wp_jshrink_init()
{
    if(!is_admin() && !defined('DOING_AJAX') && !defined('WP_JSHRINK_DEBUG')) {
        
        require_once 'vendor/autoload.php';
        require_once 'lib/Plugin.class.php';
        require_once 'lib/Helper.class.php';

        global $wp_scripts;

        $wpJshrink = new WpJshrinkPlugin($wp_scripts);
        $wpJshrink->dispatch();
    }
}

add_action('init', 'wp_jshrink_init');

<?php
/*
Plugin Name: WP JSHRINK
Description: JShrink minifier implemented for WordPress. Combines all scripts equeued by <code>wp_enqueue_script</code> into a single minified js file.
Author: Victor M
Version: 0.2.0
Author URI:
Plugin URI:

  This plugin is released under version 3 of the GPL:
  http://www.opensource.org/licenses/gpl-3.0.html
*/

function wp_jshrink_init()
{
    if(!is_admin() && !defined('DOING_AJAX')) {
        
        require_once 'vendor/autoload.php';
        require_once 'lib/Plugin.class.php';
        require_once 'lib/Helper.class.php';

        global $wp_scripts;

        $wpJshrink = new WpJshrinkPlugin($wp_scripts);
        $wpJshrink->dispatch();
    }
}

add_action('init', 'wp_jshrink_init');

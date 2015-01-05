=== WP-JSHRINK ===
Contributors: vic-m
Tags: minify, javascript
Requires at least: 4.0
Tested up to: 4.1
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==
JShrink minifier implemented for WordPress. Combines footer scripts equeued by wp_enqueue_script into a single minified js file. Check the FAQ section for more info about the plugin.

Important note: only footer scripts which are equeued by wp_enqueue_script with the fifth parameter set to 'true' are minified by this plugin.

JShrink is a php class that minifies javascript so that it can be delivered to the client quicker. Check https://github.com/tedious/JShrink for more info.

Works for sure with: Wordpress 4.0+, PHP 5.4+.

== Installation ==
Just install and activate the plugin, and it will seamlessly minify and combine javascript in the footer. No other actions required from your side.

== Frequently Asked Questions ==
= Why would I use it? =
It will help you to reduce number of scripts on your page and hence reduce number of http requests to your server.
= When does it make sense? =
If you have 3 js files enqueued in your footer - you definitely don't need this plugin. If you there are like 20 of them - then probably you do.
= What gets minified? =
Scripts from the current theme, wp-includes & plugins.
= Why only footer scripts? =
To encourage people put their javascript in the footer.
= Does it minify css? =
No. Initially it was a small plugin for personal usage in conjunction with wp-less. Consider using LESS or SCSS to put your css together in a single file or use any other css minifier.
= Does it uglify my javascript? =
No. Only minifies and combines into a single file.
= Grunt, Gulp, xxx and yyy already do this =
This is yet another tool which you may either use or not use, it's up to you :)

== Screenshots ==
1. Before & after, number of requests significantly reduced.

== Changelog ==

= Version 1.1.2 =
* Stable tag

= Version 1.1.1 =
* Unused code like tests removed from library to reduce package size

= Version 1.1 =
* Hardcoded paths removed

= Version 1.0 =
* Very basic API
* Plugin goes public

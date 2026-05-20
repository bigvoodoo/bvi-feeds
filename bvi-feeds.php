<?php

/**
 * Plugin Name:       BVI Feeds
 * Plugin URI:        https://github.com/bigvoodoo/bvi-feeds
 * Author:            Big Voodoo Interactive
 * Author URI:        https://www.bigvoodoo.com
 * Description:       Adds client side social media and RSS widgets.
 * Version:           4.2.1
 * Requires at least: 6.9
 * Tested up to:      7.0
 * Requires PHP:      8.2
 * License:           GPLv3
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * GitHub Update URI: https://github.com/bigvoodoo/bvi-feeds
 * Primary Branch:    main
 * Text Domain:       bvi-feeds
 */

if ( ! function_exists( 'add_action' ) ) {
	echo 'No direct access.';
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	die();
}

require_once( 'sm-rss-feed-widget.php' );
require_once( 'sm-twitter-feed-widget.php' );
require_once( 'sm-facebook-feed-widget.php' );

// add widgets
add_action( 'widgets_init', 'sm_register_widgets' );
function sm_register_widgets() {
	register_widget( 'WP_Widget_SM_RSS_Feed' );
	register_widget( 'WP_Widget_SM_Twitter_Feed' );
	register_widget( 'WP_Widget_SM_Facebook_Feed' );
}

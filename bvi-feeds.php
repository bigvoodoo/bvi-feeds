<?php
/**
 * Plugin Name: BVI Feeds
 * Plugin URI: https://github.com/bigvoodoo/bvi-feeds
 * Description: Simple tool for adding client side social media and RSS widgets.
 * Author: Big Voodoo Interactive
 * Version: 4.1.1
 * Author URI: http://www.bigvoodoo.com
 * GitHub Plugin URI: https://github.com/bigvoodoo/bvi-feeds
 * 
 * @author Christina Gleason <tina@bigvoodoo.com>
 * @author Joey Line
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

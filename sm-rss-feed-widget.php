<?php

if ( ! function_exists( 'add_action' ) ) {
	echo 'No direct access.';
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	die();
}

class WP_Widget_SM_RSS_Feed extends WP_Widget {
	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'sm_rss_feed', // Base ID
			__( 'Social Media RSS Feed', 'bvi-social-media' ), // Name
			array( 'description' => __( 'An RSS Feed Widget loaded via JavaScript', 'bvi-social-media' ), ) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {

		if( ! empty( $instance['url'] ) ) {

			wp_deregister_script( 'moment.js' );
			wp_register_script( 'sm-rss-feed', plugins_url( 'assets/js/sm-rss-feed.js', __FILE__ ), array( 'jquery' ), false, true );
			wp_register_script( 'moment.js', plugins_url( 'assets/js/moment.js', __FILE__ ), array( 'jquery' ), false, true );
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'sm-rss-feed' );
			wp_enqueue_script( 'moment.js' );

			if( strpos( $instance['template'], '{player}' ) !== false ) {
				wp_enqueue_script( 'wp-mediaelement', false, array(), false, true );
			}

			if( $instance['proxy'] ) {
				wp_localize_script( 'sm-rss-feed', 'SM_RSS_Feed', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
			}

			$url = $instance['url'];
			if( strpos( $url, 'http://' ) !== 0 && strpos( $url, 'www.' ) !== 0 && strpos( $url, 'https://' ) !== 0 ) {
				$url = home_url( $url );
			}

			echo '<div class="rss-feed wrapper x" data-widget-id="' . preg_replace('/[^0-9]+/', '', $args['widget_id']) .'" data-href="' . $url . '" data-number-posts="' . $instance['num_posts'] . '" data-proxy="' . ( $instance['proxy'] ? $instance['url'] : '' ) . '" style="display:none;">' . $instance['template'] . '</div>';
		}
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		$url = isset( $instance['url'] ) ? $instance['url'] : '';
		$num_posts = isset( $instance['num_posts'] ) ? $instance['num_posts'] : 3;
		$template = isset( $instance['template'] ) ? $instance['template'] : '';
		$proxy = isset( $instance['proxy'] ) ? (bool) $instance['proxy'] : false;

		?>
		<p>NOTE: this requires either that the feed is a JSON-encoded version of the feed (possibly with the plugin <a href="http://wordpress.org/plugins/feed-json/">Feed JSON</a>), or that proxying be enabled.</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'url' ); ?>"><?php _e( 'Feed URL:' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'url' ); ?>" name="<?php echo $this->get_field_name( 'url' ); ?>" type="text" value="<?php echo esc_attr( $url ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'proxy' ); ?>">
				<input id="<?php echo $this->get_field_id( 'proxy' ); ?>" name="<?php echo $this->get_field_name( 'proxy' ); ?>" type="checkbox" value="1"<?php echo $proxy ? ' checked="checked"' : ''; ?> />
				<?php _e( 'Proxy this feed?' ); ?>
			</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'num_posts' ); ?>"><?php _e( 'Number of Posts:' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'num_posts' ); ?>" name="<?php echo $this->get_field_name( 'num_posts' ); ?>" type="number" value="<?php echo esc_attr( $num_posts ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'template' ); ?>"><?php _e( 'Template:' ); ?></label>
			<textarea class="widefat" id="<?php echo $this->get_field_id( 'template' ); ?>" name="<?php echo $this->get_field_name( 'template' ); ?>" style="height:200px;"><?php echo esc_attr( $template ); ?></textarea>
		</p>
		<span class="description" style="display:block;">
			Possible templating replacements (so far):
			<dl>
				<dt>{index}</dt>
				<dd>The index of the entry, starting at 1</dd>
				<dt>{title}</dt>
				<dd>The title of the entry</dd>
				<dt>{author}</dt>
				<dd>The author of the entry</dd>
				<dt>{timestamp-###}</dt>
				<dd>The timestamp of the entry, formatted according to ### using <a href="http://momentjs.com/docs/#/displaying/format/" target="_blank">moment.js</a> or <a href="http://pubs.opengroup.org/onlinepubs/007908799/xsh/strftime.html" target="_blank">strftime standards</a> (strftime has fewer options but is easier to read, and most existing sites use it).</dd>
				<dt>{link}</dt>
				<dd>The &lt;a&gt; link to the entry</dd>
				<dt>{/link}</dt>
				<dd>The &lt;/a&gt; end of link to the entry</dd>
				<dt>{content}</dt>
				<dd>The full content of the entry</dd>
				<dt>{content-###}</dt>
				<dd>The content of the entry, trimmed to about ### characters (e.g. {content-200})</dd>
				<dt>{duration}</dt>
				<dd>The duration of the track (for podcasts only)</dd>
				<dt>{player}</dt>
				<dd>The audio player (for podcasts only)</dd>
			</dl>
		</span>
		<?php
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {

		$instance = array();
		$instance['url'] = ( ! empty( $new_instance['url'] ) ) ? $new_instance['url'] : '';
		$instance['num_posts'] = ( ! empty( $new_instance['num_posts'] ) ) ? $new_instance['num_posts'] : '';
		$instance['template'] = ( ! empty( $new_instance['template'] ) ) ? $new_instance['template'] : '';
		$instance['proxy'] = ( ! empty( $new_instance['proxy'] ) ) ? (bool) $new_instance['proxy'] : false;

		return $instance;
	}

	public static function ajax_get_feed() {

		$widgets = get_option( 'widget_sm_rss_feed' );
		$url = urldecode( $_GET['url'] );
		$found = false;
		$num_posts = null;

		foreach( $widgets as $id => $widget ) {

			// Has a URL been set for the widget?
			$url_set = isset( $widget['url'] );
			// Does it match the url provided in the request?
			$url_match = $widget['url'] == $url;
			// Does the widget ID match that of the request?
			$widget_id = $id == $_GET['widget_id'];
			// Was the proxy setting set to true?
			$widget_proxy = true == $widget['proxy'];

			// If all of these conditions apply...
			if( $url_set && $url_match && $widget_id && $widget_proxy ) {
				// We found our widget!
				$found = true;
				$num_posts = $widget['num_posts'];
				break;
			}
		}

		if( ! $found ) {
			echo 'Feed not found.';
			header( 'Status: 400 Bad Request' );
			header( 'HTTP/1.1 400 Bad Request' );
			die();
		}

		$rest_host = '';
		$rest_path = '/wp/v2/posts';
		$category_path = '/wp/v2/categories';

		// Does the provided URL begin with any of these things?
		$www = strpos( $url, 'www.' ) === 0;
		$http = strpos( $url, 'http://' ) === 0;
		$https = strpos( $url, 'https://' ) === 0;

		// If it does, external will be set to true.
		$external = $www || $http || $https;

		// If the url is external to the site, set it up as follows
		if ( $external ) {
			$path = wp_parse_url( $url );
			$rest_host = $path['scheme'] . '://';
			$rest_host .= $path['host'];
			$rest_host .= '/wp-json';
		}

		// Does the url begin with the word category?
		$is_category = strpos( $url, 'category' ) === 0;

		// If so, let's grab the category slug from the path
		if ( $is_category ) {
			$path = explode( '/', $url );
			$category = $path[1];
		}

		// Let's get ready to make some internal REST requests!
		$request_uri = $rest_host . $rest_path;
		$category_uri = $rest_host . $category_path;

		global $wp_rest_server;

		// But first, if it's external, let's go grab some JSON there
		if ( $external ) {

			// We'll build the query the old fashioned way
			$query = '?per_page=' . $num_posts . '&_embed';

			// Append it to the request, and go get our response
			$request_uri = $request_uri . $query;
			$request = wp_remote_get( $request_uri );
			$response = wp_remote_retrieve_body( $request );

			// When JSON decoded, it looks just like the internal response!
			$data = json_decode( $response, true );

		} else {

			// Let us begin a new internal rest request
			$request = new WP_REST_Request( 'GET', $request_uri );

			if ( ! $request ) {
				echo 'Could not make REST request.';
				header('Status: 400 Bad Request');
				header('HTTP/1.1 400 Bad Request');
				die();
			}

			// An array for query params
			$query_params = array();
			// Beginning with posts per page
			$query_params['per_page'] = $num_posts;

			// If it's a category, we'll need to go get its ID from the slug
			if ( $is_category ) {

				$categories = new WP_REST_Request( 'GET', $category_uri );
				$categories->set_query_params( [ 'slug' => $category ] );
				$category_response = rest_do_request( $categories );
				$result = $wp_rest_server->response_to_data( $category_response, false );
				$category_id = $result[0]['id'];

				$query_params['categories'] = $category_id;
			}

			// Now we can finish our request!
			$request->set_query_params( $query_params );
			$response = rest_do_request( $request );
			$data = $wp_rest_server->response_to_data( $response, true );
		}

		// Wherever it came from, let's set up our data
		// to be used by jQuery on the front end

		$output = array();
		foreach ( $data as $post ) {
			$output[] = array(
				'title' => $post['title']['rendered'],
				'link' => $post['link'],
				'date' => $post['date'],
				'content' => $post['excerpt']['rendered'],
				'author' => $post['_embedded']['author'][0]['name'],
			);
		}

		// Encode that output like a boss
		$json = wp_json_encode( $output );

		// Throw down some nice headers
		$charset = get_bloginfo( 'charset' );
		header( 'Content-Type: application/x-javascript; charset=' . $charset );
		header( 'Expires: ' . date( DATE_RFC1123, strtotime( '+1 hour' ) ) );
		header( 'Cache-Control: public, must-revalidate, proxy-revalidate' );
		header( 'Pragma: public' );

		// Echo out our encoded data
		echo $_GET['callback'] . '(' . $json . ');';

		// And we're done
		die();
	}
}

add_action( 'wp_ajax_sm_get_rss_feed', array( 'WP_Widget_SM_RSS_Feed', 'ajax_get_feed' ) );
add_action( 'wp_ajax_nopriv_sm_get_rss_feed', array( 'WP_Widget_SM_RSS_Feed', 'ajax_get_feed' ) );

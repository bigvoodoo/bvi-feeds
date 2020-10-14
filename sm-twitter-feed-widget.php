<?php

if ( ! function_exists( 'add_action' ) ) {
	echo 'No direct access.';
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	die();
}

class WP_Widget_SM_Twitter_Feed extends WP_Widget {
	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'sm_twitter_feed', // Base ID
			__( 'Social Media Twitter Feed', 'bvi-social-media' ), // Name
			array( 'description' => __( 'A Twitter Widget loaded via JavaScript', 'bvi-social-media' ), ) // Args
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
		if( !empty( $instance['widget_id'] ) ) {
			wp_deregister_script( 'moment.js' );
			wp_register_script( 'twitter-widgets', '//platform.twitter.com/widgets.js', array(), null, true );
			wp_register_script( 'moment.js', plugins_url( 'assets/js/moment.js', __FILE__ ), array( 'jquery' ), false, true );
			wp_register_script( 'sm-twitter-feed', plugins_url( 'assets/js/sm-twitter-feed.js', __FILE__ ), array( 'jquery', 'moment.js' ), false, true );
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'twitter-widgets' );
			wp_enqueue_script( 'moment.js' );
			wp_enqueue_script( 'sm-twitter-feed' );

			echo '<div class="twitter-feed wrapper x" data-id="' . $instance['widget_id'] . '" data-number-posts="' . $instance['num_posts'] .'" data-include-retweets="' . $instance['include_retweets'] . '" data-use-timeago="' . $instance['use_timeago'] . '" data-shorten-links="' . $instance['shorten_links'] . '" style="display:none;">' . $instance['template'] . '</div>';
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
		$widget_id = isset( $instance['widget_id'] ) ? $instance['widget_id'] : '';
		$num_posts = isset( $instance['num_posts'] ) ? $instance['num_posts'] : 3;
		$template = isset( $instance['template'] ) ? $instance['template'] : '';
		$include_retweets = isset( $instance['include_retweets'] ) ? (bool) $instance['include_retweets'] : false;
		$use_timeago = isset( $instance['use_timeago'] ) ? (bool) $instance['use_timeago'] : false;
		$shorten_links = isset( $instance['shorten_links'] ) ? (bool) $instance['shorten_links'] : false;

		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'widget_id' ); ?>"><?php _e( 'Twitter Widget ID:' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'widget_id' ); ?>" name="<?php echo $this->get_field_name( 'widget_id' ); ?>" type="text" value="<?php echo esc_attr( $widget_id ); ?>" />
			You can get this ID by creating a widget in your account's settings area, copying the code for the widget, then grabbing the number that is displayed in <span style="font-family:monospace">data-widget-id="##################"</span>.
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'num_posts' ); ?>"><?php _e( 'Number of Posts:' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'num_posts' ); ?>" name="<?php echo $this->get_field_name( 'num_posts' ); ?>" type="number" value="<?php echo esc_attr( $num_posts ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'include_retweets' ); ?>">
				<input id="<?php echo $this->get_field_id( 'include_retweets' ); ?>" name="<?php echo $this->get_field_name( 'include_retweets' ); ?>" type="checkbox" value="1"<?php echo $include_retweets ? ' checked="checked"' : ''; ?> />
				<?php _e( 'Include Retweets?' ); ?>
			</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'use_timeago' ); ?>">
				<input id="<?php echo $this->get_field_id( 'use_timeago' ); ?>" name="<?php echo $this->get_field_name( 'use_timeago' ); ?>" type="checkbox" value="1"<?php echo $use_timeago ? ' checked="checked"' : ''; ?> />
				<?php _e( 'Use Timeago Formatting?' ); ?>
			</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'shorten_links' ); ?>">
				<input id="<?php echo $this->get_field_id( 'shorten_links' ); ?>" name="<?php echo $this->get_field_name( 'shorten_links' ); ?>" type="checkbox" value="1"<?php echo $shorten_links ? ' checked="checked"' : ''; ?> />
				<?php _e( 'Shorten Links?' ); ?>
			</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'template' ); ?>"><?php _e( 'Template:' ); ?></label>
			<textarea class="widefat" id="<?php echo $this->get_field_id( 'template' ); ?>" name="<?php echo $this->get_field_name( 'template' ); ?>" style="height:200px;"><?php echo esc_attr( $template ); ?></textarea>
		</p>
		<span class="description" style="display:block;">
			When marking the output of your twitter feed you must include {tweets} to mark the start of your twitter feed and {/tweets} to mark the end.<br />
			Possible templating replacements (so far):<br />
			<strong>Used anywhere:</strong>
			<dl>
				<dt>{user.id}</dt>
				<dd>The id of the Twitter user</dd>
				<dt>{user.screen_name}</dt>
				<dd>The Twitter username being used</dd>
				<dt>{user.name}</dt>
				<dd>The real name of the Twitter user</dd>
				<dt>{user.link}</dt>
				<dd>The &lt;a&gt; link to the user's Twitter page</dd>
				<dt>{/user.link}</dt>
				<dd>The &lt;/a&gt; end of link to the user's Twitter page</dd>
			</dl>
			<strong>Used inside the feed loop:</strong>
			<dl>
				<dt>{tweet.index}</dt>
				<dd>The index of the tweet, starting at 1</dd>
				<dt>{tweet.link}</dt>
				<dd>The &lt;a&gt; link to the tweet</dd>
				<dt>{/tweet.link}</dt>
				<dd>The &lt;/a&gt; end of link to the tweet</dd>
				<dt>{tweet.content}</dt>
				<dd>The tweet content</dd>
				<dt>{tweet.time}</dt>
				<dd>The time of the tweet</dd>
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
		$instance['widget_id'] = ( ! empty( $new_instance['widget_id'] ) ) ? $new_instance['widget_id'] : '';
		$instance['num_posts'] = ( ! empty( $new_instance['num_posts'] ) ) ? $new_instance['num_posts'] : '';
		$instance['template'] = ( ! empty( $new_instance['template'] ) ) ? $new_instance['template'] : '';
		$instance['include_retweets'] = ( ! empty( $new_instance['include_retweets'] ) ) ? (bool) $new_instance['include_retweets'] : false;
		$instance['use_timeago'] = ( ! empty( $new_instance['use_timeago'] ) ) ? (bool) $new_instance['use_timeago'] : false;
		$instance['shorten_links'] = ( ! empty( $new_instance['shorten_links'] ) ) ? (bool) $new_instance['shorten_links'] : false;

		return $instance;
	}
}

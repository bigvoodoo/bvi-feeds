<?php
/**
 * Prevent direct access to file
 * @param none
 * @author Christina Gleason
 *
 */
if(!function_exists('add_action')) {
	echo 'No direct access allowed.';
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	die();
}

class WP_Widget_SM_Facebook_Feed extends WP_Widget {
	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'sm_facebook_feed', // Base ID
			__( 'Social Media Facebook Feed', 'bvi-feeds' ), // Name
			array( 'description' => __( 'A Facebook Widget loaded via JavaScript', 'bvi-feeds' ), ) // Args
		);
	}
	
	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form($instance) {
		// pull existence of widget field data
		$app_id = isset($instance['app_id']) ? $instance['app_id'] : '';
		$app_secret = isset($instance['app_secret']) ? $instance['app_secret'] : '';
		$page_id = isset($instance['page_id']) ? $instance['page_id'] : '';
		$num_posts = isset($instance['num_posts']) ? $instance['num_posts'] : 3;
		$template = isset($instance['template']) ? $instance['template'] : '';

		?>
		<p>
			<label for="<?php echo $this->get_field_id('app_id'); ?>"><?php _e('Facebook App ID:'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('app_id'); ?>" name="<?php echo $this->get_field_name('app_id'); ?>" type="text" value="<?php echo esc_attr($app_id); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('app_secret'); ?>"><?php _e('Facebook App Secret:'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('app_secret'); ?>" name="<?php echo $this->get_field_name('app_secret'); ?>" type="text" value="<?php echo esc_attr($app_secret); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('page_id'); ?>"><?php _e('Facebook Page ID:'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('page_id'); ?>" name="<?php echo $this->get_field_name('page_id'); ?>" type="text" value="<?php echo esc_attr($page_id); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('num_posts'); ?>"><?php _e('Number of Posts:'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('num_posts'); ?>" name="<?php echo $this->get_field_name('num_posts'); ?>" type="number" value="<?php echo esc_attr($num_posts); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('template'); ?>"><?php _e('Template:'); ?></label>
			<textarea class="widefat" id="<?php echo $this->get_field_id('template'); ?>" name="<?php echo $this->get_field_name('template'); ?>" style="height:200px;"><?php echo esc_attr($template); ?></textarea>
		</p>
		<span class="description" style="display:block;">
			<dl>
				<dt>{id}</dt>
				<dd>The id of the post (pageid_postid)</dd>
				<dt>{index}</dt>
				<dd>The index of the post, starting at 1</dd>
				<dt>{title}</dt>
				<dd>The title of the post</dd>
				<dt>{timestamp-###}</dt>
				<dd>The timestamp of the post, formatted according to ### using <a href="http://pubs.opengroup.org/onlinepubs/007908799/xsh/strftime.html" target="_blank">strftime standards</a></dd>
				<dt>{facebook-link}</dt>
				<dd>The &lt;a&gt; link to the Facebook post</dd>
				<dt>{/facebook-link}</dt>
				<dd>The &lt;/a&gt; end of link to the Facebook post</dd>
				<dt>{message}</dt>
				<dd>The message (the text the poster posted) of the post</dd>
				<dt>{name}</dt>
				<dd>The name of the post, such as the title from a link posted to Facebook</dd>
				<dt>{picture}</dt>
				<dd>The thumbnail related to the post</dd>
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
	public function update($new_instance, $old_instance) {
		$instance = array();
		// update the widget fields to their new values, or set them empty if specified
		$instance['app_id'] = (! empty($new_instance['app_id'])) ? $new_instance['app_id'] : '';
		$instance['app_secret'] = (! empty($new_instance['app_secret'])) ? $new_instance['app_secret'] : '';
		$instance['page_id'] = (! empty($new_instance['page_id'])) ? $new_instance['page_id'] : '';
		$instance['num_posts'] = (! empty($new_instance['num_posts'])) ? $new_instance['num_posts'] : '';
		$instance['template'] = (! empty($new_instance['template'])) ? $new_instance['template'] : '';

		return $instance;
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget($args, $instance) {
		if(!empty($instance['app_id'])) {
			$final_template = $this->generate_template($instance);

			echo '<div class="facebook-feed wrapper x">' . $final_template . '</div>';
		}
	}

	/**
	 * Generate template using parameters given for the widget
	 * @param $instance: variable containing all the parameters
	 * @author Christina Gleason
	 *
	 */
	private function generate_template($instance) {
		// pulls the data using the facebook graph api - fields that are returned are listed at the end
		$page_feed = 'https://graph.facebook.com/' . $instance['page_id'] . '/posts?access_token=' . $instance['app_id'] . '|' . $instance['app_secret'] . '&date_format=U&locale=en_US&limit=' . $instance['num_posts'] . '&fields=id,story,type,name,message,full_picture,created_time';
		// make a cURL request using the GET URL defined above
		$curl = curl_init($page_feed);
		// this tells the URL to send us back a response so we know what happened
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		// do the thing! and set the response to a variable
		$data = curl_exec($curl);
		// if there was an error related to the cURL functionality itself, throw an error
		if(curl_errno($curl)){
			$error = curl_error($curl);
			curl_close($curl);
			return 'Error with feed: cURL error: ' . $error;
		}
		// if the response was bad, throw an error
		if($curl === false) {
			return 'Error with feed: cURL execution failed.';
		}
		// close the connection we've made
		curl_close($curl);
		// decode the JSON object returned from the cURL request
		$data = json_decode($data);

		$posts = '';
		$count = 1;
		// go through each post found from the feed
		foreach($data->data as $post){
			// set the individual post id from the response
			$id = explode('_', $post->id);
			$post_id = $id[1];
			// template items to replace with content from feed
			$template = '';
			$post_title = '';
			$post_message = '';
			$post_picture = '';
			// setup link directly to the individual post
			$facebook_link = 'https://www.facebook.com/' . $instance['page_id'] . '/posts/' . $post_id;
			// types of posts we support, currently does not support offer posts
			// check if this could be a video post
			if($post->type == 'video'){
				// check if theres a story or name available to use as a title, otherwise there is none
				if(!empty($post->story)) {
					$post_title = $post->story;
				} else if(!empty($post->name)) {
					$post_title = $post->name;
				} 
				// check if theres a text message along with the video
				if(!empty($post->message)) {
					$post_message .= '<p>' . $post->message . '</p>';
				}

				$post_message .= '<a href="' . $facebook_link . '" target="_blank" rel="noopener noreferrer">Watch the Video on Facebook</a>';
				// check if there's a picture associated with this post
				if(!empty($post->full_picture)) {
					$post_picture = '<img src="' . $post->full_picture . '" alt="' . $post_title . '">';
				}
			// else, this could be a photo post type
			} else if($post->type == 'photo'){
				// check if theres a story or name available to use as a title, otherwise there is none
				if(!empty($post->story)) {
					$post_title = $post->story;
				} else if(!empty($post->name)) {
					$post_title = $post->name;
				}  
				// sometimes photos can get posted without a message
				if(!empty($post->message)) {
					$post_message = '<p>' . $post->message . '</p>';
				}
				// check if there's a picture associated with this post
				if(!empty($post->full_picture)) {
					$post_picture = '<img src="' . $post->full_picture . '" alt="' . $post_title . '">';
				}
			// else, this could be a link post type
			} else if($post->type == 'link'){
				$post_title = $post->name;
				$post_message = '<p>' . $post->message . '</p>';
				// check if there's a picture associated with this post
				if(!empty($post->full_picture)) {
					$post_picture = '<img src="' . $post->full_picture . '" alt="' . $post_title . '">';
				}
			// otherwise this is probably just a status
			} else {
				// check if theres a story or name available to use as a title, otherwise there is none
				if(!empty($post->story)) {
					$post_title = $post->story;
				} else if(!empty($post->name)) {
					$post_title = $post->name;
				} 

				$post_message = '<p>' . $post->message . '</p>';
			}
			// if picture is not empty, get the full resolution picture instead and replace
			$picture_feed = 'https://graph.facebook.com/' . $instance['page_id'] . '/posts?access_token=' . $instance['app_id'] . '|' . $instance['app_secret'] . '&date_format=U&locale=en_US&limit=' . $instance['num_posts'];

			// replace the template bracketed items with their equivalent values from the response
			$template = str_replace(
				array('{id}', '{index}', '{title}', '{facebook-link}', '{/facebook-link}', '{message}', '{picture}'),
				array($post_id, $count, $post_title, '<a href="' . $facebook_link . '" target="_blank" rel="noopener noreferrer">', '</a>', $post_message, $post_picture),
				$instance['template']
			);
			// locate the timestamp declaration if there is one
			preg_match('/\{timestamp-([^\}]+)\}/', $template, $matches);
			// if a timestamp declaration is found, replace it with valid date from the response
			if(!empty($matches[1])) {
				// the match found is the strftime format the user specified
				$time_format = $matches[1];
				// format the timestamp according to what was specified by the user
				$timestamp = strftime($time_format, $post->created_time);
				// go through the template and replace all instances
				// TODO: make this recognize 2 or more timestamp declarations with different formats
				$template = preg_replace('/\{timestamp-([^\}]+)\}/', $timestamp, $template);
			}
			// increments the count of elements found in this response
			$count++;
			// add the template to the posts array to be sent to the feed, in cases of multiple posts
			$posts .= $template;
		}
		// return the formatted template with the values
		return $posts;
	}
}

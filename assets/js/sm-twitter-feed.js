/**
 * Loads a Twitter feed widget in a custom fashion
 * Inspired by: http://jasonmayes.com/projects/twitterApi/
 */
jQuery(function($) {
	$('.twitter-feed').each(function() {
		var container = $(this);
		var id = container.data('id');
		var number_posts = container.data('number-posts');
		var include_retweets = container.data('include-retweets');
		var use_timeago = container.data('use-timeago');
		var shorten_links = container.data('shorten-links');
		var template = container.html();
		// yay for JavaScript regex stupidness!
		// . does not match newlines, and [^] doesn't work in IE < 11
		// [\s\S] means all whitespace or all non-whitespace...so everything
		// http://stackoverflow.com/questions/1979884/how-to-use-javascript-regex-over-multiple-lines
		var post_template = $.trim(template.replace(/[\s\S]*\{tweets\}([\s\S]+)\{\/tweets\}[\s\S]*/, '$1'));
		template = $.trim(template.replace(/\{tweets\}[\s\S]+\{\/tweets\}/, '{tweets}'));
		container.empty();

		// https://cdn.syndication.twimg.com/widgets/timelines/
		// 369839186087383040
		// ?do_not_track=true
		// &expand_media=true
		// &hide_at_replies=true
		// &lang=en
		// &suppress_response_codes=true
		// &callback=?
		var uri = "//cdn.syndication.twimg.com/widgets/timelines/" + id + "?do_not_track=true&lang=en&hide_at_replies=true&suppress_response_codes=true&rnd=" + Math.random() + "&callback=?";

		$.getJSON(uri, null, function(timeline_html) {
			var timeline = $(timeline_html.body);

			var user = {};
			user.name = timeline.find('.timeline-Header').find('a.customisable-highlight').attr('title').replace(' on Twitter', '');
			user.url = timeline.find('.timeline-Header').find('a.customisable-highlight').attr('href');
			user.screen_name = user.url.replace(/.*\//, '');
			user.id = timeline.data('profile-id');

			var tweets = '';

			if(!timeline.find('li.timeline-TweetList-tweet').length) {
				var fakeTweet = $('<li>');
				fakeTweet.addClass('tweet');
				fakeTweet.data('tweet-id', 'fakeTweet');
				fakeTweet.append('<a class="permalink" href="#">');
				fakeTweet.append('<div class="e-entry-content"><div class="e-entry-title">No tweets found...</div></div>');
				fakeTweet.append($('<time>').attr('datetime', new Date().toISOString()));
				fakeTweet.append('<div><div class="reply-action web-intent"></div><div class="retweet-action web-intent"></div><div class="favorite-action web-intent"></div></div>');
				timeline.find('ol').append(fakeTweet)
			}

			var index = 0;
			timeline.find('li.timeline-TweetList-tweet').each(function() {
				if(index >= number_posts) {
					return false;
				}

				if(include_retweets) {
					if($(this).find('.retweet-credit').size()) {
						return true;
					}
				}
				// TODO: attribute retweets

				var tweet = {
					index: index + 1,
					id: $(this).data('tweet-id'),
					url: $(this).find('a.timeline-Tweet-timestamp').attr('href'),
					content: sm_twitter_clean_content($(this).find('.timeline-Tweet-text'), shorten_links).html(),
					time: sm_twitter_format_date(moment($(this).find('time').attr('datetime')), use_timeago)
				};

				var html = post_template
					.replace(/\{tweet\.index\}/gi, tweet.index)
					.replace(/\{tweet\.id\}/gi, tweet.id)
					.replace(/\{tweet\.link\}/gi, '<a href="' + tweet.url + '" target="_blank" rel="noopener noreferrer">')
					.replace(/\{\/tweet\.link\}/gi, '</a>')
					.replace(/\{tweet\.content\}/gi, tweet.content)
					.replace(/\{tweet\.time\}/gi, tweet.time);

				tweets += html;
				index++;
			});

			template = template.replace(/\{tweets\}/i, tweets);

			container.html(template
				.replace(/\{user\.id\}/gi, user.id)
				.replace(/\{user\.screen_name\}/gi, user.screen_name)
				.replace(/\{user\.name\}/gi, user.name)
				.replace(/\{user\.link\}/gi, '<a href="' + user.url + '" target="_blank" rel="noopener noreferrer">')
				.replace(/\{\/user\.link\}/gi, '</a>')
			);

			container.show();

			container.trigger('twitter.loaded');
		});
	});

	var sm_twitter_format_date = function(date, use_timeago) {
		var display = use_timeago ? date.fromNow() : date.toDate().toLocaleString();
		return '<time datetime="' + date.toISOString() + '" title="' + date.format('llll') + '">' + display + '</time>';
	};

	var sm_twitter_clean_content = function(content, shorten_links) {
		if(shorten_links) {
			var link = content.find('a.link.customisable');
			if(link.length) {
				link.replaceWith('<a href="' + link.attr('href') + '" rel="'
					+ link.attr('rel') + '" title="' + link.attr('title')
					+ '" target="' + link.attr('target') + '">'
					+ link.attr('href') + '</a>');
			}
		}
		return content;
	};
});

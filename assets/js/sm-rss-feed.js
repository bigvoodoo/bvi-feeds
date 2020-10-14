/**
 * Loads an RSS feed via jsonp
 */
jQuery(function($) {
	var i = 0;
	var urlPattern = /^https?\:\/\/([^\/?#]+)(?:[\/?#]|$)/i;
	$('.rss-feed').each(function() {
		var container = $(this);
		var widget_id = container.data('widget-id');
		var url = container.data('href');
		var proxy = container.data('proxy');
		var template = container.html();
		container.empty();

		if (proxy) {
			url = SM_RSS_Feed.ajaxurl + '?action=sm_get_rss_feed&url=' + encodeURIComponent(proxy) + '&widget_id=' + encodeURIComponent(widget_id);
		}

		$.ajax({
			url: url,
			cache: true,
			dataType: 'jsonp',
			jsonpCallback: 'rssFeed' + (i++) + 'Callback',
			success: function(entries) {
				$.each(entries, function(index, entry) {
					if (index >= container.data('number-posts')) {
						return false;
					}

					var content = sm_rss_clean_content(entry.content);
					var link = entry.permalink || entry.link;

					var matches = link.match(urlPattern);
					var nofollow = false;
					if (matches && matches[1] != window.location.host) {
						nofollow = true;
					}

					var html = template
						.replace(/\{index\}/g, index + 1)
						.replace(/\{title\}/g, entry.title)
						.replace(/\{author\}/g, entry.author)
						.replace(/\{timestamp-([^\}]+)\}/g, (entry.date ? sm_rss_format_timestamp(entry.date) : ''))
						.replace(/\{link\}/g, '<a href="' + link + '" target="_blank"' + (nofollow ? ' rel="nofollow noopener noreferrer"' : '') + '>')
						.replace(/\{\/link\}/g, '</a>')
						.replace(/\{content\}/g, content)
						.replace(/\{content-([0-9]+)\}/g, sm_rss_shorten_content(content))
						.replace(/\{duration\}/g, entry.duration)
						.replace(/\{player\}/g, '<audio src="' + entry.url + '?source=sm_rss_feed" preload="none"></audio>');
					container.append(html);
				});

				var audio = container.find('audio');
				if (audio.length) {
					audio.mediaelementplayer();
				}

				container.show();

				container.trigger('rss.loaded');
			}
		});
	});

	/**
	 * Formats the timestamp
	 * NOTE: this returns an anonymous function to be used elsewhere, such as in
	 * .replace() above.
	 */
	var sm_rss_format_timestamp = function(date) {
		return function(_ignored, format) {
			return moment(date).strftime(format);
		};
	};

	/**
	 * Shortens the content to the nearest ' ' after length characters
	 * NOTE: this returns an anonymous function to be used elsewhere, such as in
	 * .replace() above.
	 */
	var sm_rss_shorten_content = function(content) {
		return function(_ignored, length) {
			if (content.length < length) {
				return content;
			} else {
				var end = content.indexOf(' ', length);
				if (end == -1) {
					return content.substr(0, length) + '&hellip;';
				} else {
					return content.substr(0, end) + '&hellip;';
				}
			}
		};
	};

	/**
	 * Cleans up HTML comments and tags, as well as newlines.
	 */
	var sm_rss_clean_content = function(content) {
		var tags = /<\/?[a-z][a-z0-9]*\b[^>]*>/gi,
			commentsAndPhpTags = /<!--[\s\S]*?-->|<\?(?:php)?[\s\S]*?\?>/gi,
			newLines = /\n\r/g;
		return content
			.replace(commentsAndPhpTags, '')
			.replace(tags, '')
			.replace(newLines, ' ');
	};
});
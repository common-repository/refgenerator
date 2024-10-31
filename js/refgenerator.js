jQuery(function($){
	$('#' + refgen.post_parent_id)
		.find(refgen.post_content_tag + '.' + refgen.post_content_class)
		.each(function () {
			var i = 1, list = [], result = [];
			$(this).find('a').not('a[href^="'+refgen.blog_url+'"]').each(function() {
				var a = $(this), href = a.attr('href'), lang = '', title = '';
				if (jQuery.inArray(href, result) === -1 && /^(javascript:|mailto:|#)/.test(href) === false) {
					lang = (a.attr("lang")) ? lang = 'lang="' + a.attr("lang") : '';
					title = (a.attr("title")) ?  a.attr("title") : refgen.a_title + i;
					list[list.length] = '<li><a href="' + href + '"' + lang + ' rel="nofollow" target="_blank">' + title + '<\/a><br/><small>' + href + '<\/small><\/li>';
					result[result.length] = href;
					i++;
				}
			});
			if (list.length > 0) {
				list[list.length] = '<\/ol>';
				list[list.length] = '<\/div>';
				$(this).append(['<div class="' + refgen.post_links_class+'">', '<h3>' + refgen.display_title + '<\/h3>', '<ol>'].concat(list).join('\n'));
			}
		});
});

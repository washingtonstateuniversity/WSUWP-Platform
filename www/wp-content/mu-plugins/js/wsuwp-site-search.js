/** Adapted from Kailey Lampert's My Sites Search - https://github.com/trepmal/my-sites-search/ */
(function($){
	jQuery(document).ready( function($) {
		$('.ms-sites-search.hide-if-no-js').show();
		$('.ms-sites-search input').keyup( function( ) {
			var searchValRegex = new RegExp( $(this).val(), 'i');
			var current_menu = $(this ).closest('ul' ).attr('id');
			$( '#' + current_menu + ' > li.menupop').hide().filter(function() {
				return searchValRegex.test( $(this).find('> a').attr("href") ) || searchValRegex.test( $(this).find('> a').text() );
			}).show();
		});
	});
}(jQuery));
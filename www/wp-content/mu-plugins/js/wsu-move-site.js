/**
 * The move site option is output in the footer due to the lack
 * of a matching hook in site-info.php. We use Javascript to
 * reposition the option on the page as it loads.
 */
(function($){
	var move_site = $('#wsu-move-site');
	$('.form-table' ).prepend( move_site );
	move_site.show();
}(jQuery));
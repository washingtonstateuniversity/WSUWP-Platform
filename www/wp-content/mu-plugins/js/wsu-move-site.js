/**
 * The move site option is output in the footer due to the lack
 * of a matching hook in site-info.php. We use Javascript to
 * reposition the option on the page as it loads.
 */
(function($){
	var form_table = $('.form-table');
	var move_site = $('#wsu-move-site');
	var extend_site = $('#wsuwp-extended-site');

	form_table.prepend( extend_site );
	form_table.prepend( move_site );

	extend_site.show();
	move_site.show();
}(jQuery));
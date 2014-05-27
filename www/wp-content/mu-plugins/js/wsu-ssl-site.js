(function($){
	var domain;

	function handle_confirm_click() {
		domain = $(this ).attr('id');

		if ( true === confirm( "Removing " + domain + " from the SSL confirmation list." ) ) {
			confirm_ssl_domain( domain );
		}
	}

	function confirm_ssl_domain( domain ) {
		console.log( domain );
	}

	$('.confirm_ssl' ).on('click', handle_confirm_click );
}(jQuery));
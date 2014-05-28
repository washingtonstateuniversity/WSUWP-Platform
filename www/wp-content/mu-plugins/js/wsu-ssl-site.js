(function($,ajaxurl, window){
	var domain;

	function handle_confirm_click() {
		domain = $(this ).attr('id');

		if ( true === confirm( "Removing " + domain + " from the SSL confirmation list." ) ) {
			confirm_ssl_domain( domain );
		}
	}

	function confirm_ssl_domain( domain ) {
		var ajax_nonce = $('#ssl_ajax_nonce' ).val();
		var data = {
			'action' : 'confirm_ssl',
			'domain' : domain,
			'ajax_nonce' : ajax_nonce
		};
		$.post(ajaxurl,data,handle_confirm_response);
	}

	function handle_confirm_response( response ) {
		response = $.parseJSON( response );
		if ( response.success ) {
			$('#' + domain ).parentsUntil('tr' ).remove();
		}
	}

	$('.confirm_ssl' ).on('click', handle_confirm_click );
}(jQuery, ajaxurl, window));
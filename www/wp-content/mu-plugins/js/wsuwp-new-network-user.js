(function($){
	$('.form-table').find('td').last()
		.html('<p class="description" style="max-width:640px;">Creating a new user on this page does not send any notification email. Please ' +
			'communicate with the new user as appropriate. If you would like a notification to be generated ' +
			'automatically, create the user at an individual site level.</p>');
}(jQuery));
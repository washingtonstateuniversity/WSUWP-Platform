(function($){
	$('.accordion-section label input').focusin( function() { $(this).closest('label').addClass('focused'); } );
	$('.accordion-section label input').focusout( function() { $(this).closest('label').removeClass('focused'); } );

	$('#customize-control-social_spot_one input').attr('placeholder','http://www.facebook.com/wsupullman');
	$('#customize-control-social_spot_two input').attr('placeholder','http://twitter.com/wsupullman');
	$('#customize-control-social_spot_three input').attr('placeholder','http://youtube.com/washingtonstateuniv');
	$('#customize-control-social_spot_four input').attr('placeholder','http://social.wsu.edu');
}(jQuery));
(function($){
	var upload_space = $('#blog_upload_space');
	var upload_space_h3 = upload_space.parents('.form-table' ).prev('h3');
	var upload_space_area = upload_space.parentsUntil('.form-table');
	var menu = $('#menu');
	var menu_h3 = menu.prev('h3');

	upload_space_h3.remove();
	upload_space_area.remove();

	menu_h3.remove();
	menu.remove();
}(jQuery));
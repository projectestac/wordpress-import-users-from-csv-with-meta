jQuery( document ).ready( function( $ ){
	var user_screen = $( '.users-php' );
	var title_action = user_screen.find( '.page-title-action:last' );
	
	import_html = '<a href="' + acui_js_object.import_url + '" class="page-title-action" title="' + acui_js_object.import_title + '">' + acui_js_object.import_label + '</a>';
	export_html = '<a href="' + acui_js_object.export_url + '" class="page-title-action" title="' + acui_js_object.export_title + '">' + acui_js_object.export_label + '</a>';

	title_action.after( import_html );
	title_action.after( export_html );
} )
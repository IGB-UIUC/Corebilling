function escapeRegExp(text) {
  return text.replace(/[-[\]{}()*+?.,\\^$|#\s]/g, '\\$&');
}

function searchCol(table,column,field){
	var $filter = jQuery('#'+field);
	table.column(column).search(escapeRegExp($filter.val()));
	table.draw();
}

function matchCol(table,column,field){
	var $filter = jQuery('#'+field);
	if($filter.val() == ""){
		table.column(column).search("");
	} else {
		table.column(column).search('^'+escapeRegExp($filter.val())+'$',true,false);
	}
	table.draw();
}

$.fn.select2.defaults.set( "theme", "bootstrap" );
$.fn.select2.defaults.set( "width", null );
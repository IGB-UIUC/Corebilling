function searchCol(table,column,field){
	console.log(this);
	var $filter = jQuery('#'+field);
	console.log($filter.val());
	table.column(column).search($filter.val());
	table.draw();
}

$.fn.select2.defaults.set( "theme", "bootstrap" );
$.fn.select2.defaults.set( "width", null );
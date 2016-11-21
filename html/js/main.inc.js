function searchCol(table,column,field){
	var $filter = jQuery('#'+field);
	table.column(column).search($filter.val());
	table.draw();
}
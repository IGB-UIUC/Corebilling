function searchCol(table,column,field){
	console.log(this);
	var $filter = jQuery('#'+field);
	console.log($filter.val());
	table.column(column).search($filter.val());
	table.draw();
}
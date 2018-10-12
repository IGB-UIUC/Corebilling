<?php

/**
 * Class VisualizeData
 * a helper abstract class used to assist in the generating of
 * smart graphs and tables
 */
class VisualizeData {
    /** returns a databale which is sortable, searchable and exportable using the datatables jquery module
     * @param $tableData
     * @param $tableHeaders
     * @param $dataColumns
     * @param $tableNameId
     * @param bool $checkBoxes
     * @return string
     */
    public static function ListSessionsTable($tableData,$tableHeaders,$dataColumns,$tableNameId,$checkBoxes=false,$filter=true)
    {
	    $buttonLocation = ".prependTo($('#".$tableNameId."_heading'))";
	    if($filter){$buttonLocation = ".appendTo($('#".$tableNameId."_filter'))";}
        $tableString =  " <script type=\"text/javascript\" class=\"init\">
        var Rate1;
            $(document).ready( function () {\n
            ".$tableNameId." = $('#".$tableNameId."').DataTable({
                \"dom\": '".($filter?'f':'')."Brtip',
                \"paging\": false,
                \"pageLength\": 50,
                \"pagingType\": \"full_numbers\",
                \"buttons\": {
                	buttons: ['copy','csv','excel','pdf','print'],
                	dom: {container:{className:'btn-group pull-right'}}
                }
                    });\n
			".$tableNameId.".buttons().container()$buttonLocation;
            $(\"#checkAll\").click(function(){
            $('input:checkbox').prop('checked', this.checked);
                });
                } );\n
            </script>";
        $tableString .= "<table id=\"".$tableNameId."\" class=\"table table-condensed\">";
        $tableString .= "<thead><tr>";

        //If checkboxes are checked then add another column
        if($checkBoxes)
        {
           $tableString .= "<th><input type=\"checkbox\" id=\"checkAll\" title=\"Toggle All Checkboxes\" name=\"checkbox\"></th>";
        }

        foreach($tableHeaders as $header)
        {
            $tableString .= "<th>".$header."</th>";
        }
        $tableString .= "</tr>
                           </thead>
                            <tbody>";
        foreach($tableData as $id=>$sessionInfo)
        {
            $tableString .= "<tr>";

            if($checkBoxes)
            {
                $tableString.= "<td><input type=\"checkbox\" class=\"checkbox\" name=\"sessionsCheckbox[]\" value=\"".$sessionInfo['id']."\"> </td>";
            }

            foreach($dataColumns as $columnName)
            {
                $tableString.= "<td>".$sessionInfo[$columnName]."</td>";
            }
            $tableString .= "</tr>";
        }
        $tableString .= "</tbody>";
        $tableString .= "</table>";

        return $tableString;
    }
    
    /** returns a databale which is sortable, searchable and exportable using the datatables jquery module
     * @param $tableData
     * @param $tableHeaders
     * @param $dataColumns
     * @param $tableNameId
     * @param bool $checkBoxes
     * @return string
     */
    public static function ListSessionsTableHiddenCols($tableData,$tableHeaders,$dataColumns,$hiddenColumns,$spreadsheetColumns,$tableNameId,$checkBoxes=false,$filter=true)
    {
	    $buttonLocation = ".prependTo($('#".$tableNameId."_heading'))";
	    if($filter){$buttonLocation = ".appendTo($('#".$tableNameId."_filter'))";}
        $tableString =  " <script type=\"text/javascript\" class=\"init\">
        var Rate1;
            $(document).ready( function () {\n
            ".$tableNameId." = $('#".$tableNameId."').DataTable({
                \"dom\": '".($filter?'f':'')."Brti',
                \"paging\": false,
                \"buttons\": {
                	buttons: ['copy',{
                		extend:'csv',
                		exportOptions: {
	                		columns: '.spreadsheetCol'
	                	}
                	},{
                		extend:'excel',
                		exportOptions: {
	                		columns: '.spreadsheetCol'
	                	}
                	},'pdf','print'],
                	dom: {container:{className:'btn-group pull-right'}}
                }
                    });\n
			".$tableNameId.".buttons().container()$buttonLocation;
            $(\"#checkAll\").click(function(){
            $('input:checkbox').prop('checked', this.checked);
                });
                } );\n
            </script>";
        $tableString .= "<table id=\"".$tableNameId."\" class=\"table table-condensed\">";
        $tableString .= "<thead><tr>";

        //If checkboxes are checked then add another column
        if($checkBoxes)
        {
           $tableString .= "<th><input type=\"checkbox\" id=\"checkAll\" title=\"Toggle All Checkboxes\" name=\"checkbox\"></th>";
        }

        foreach($tableHeaders as $header)
        {
	        $colclass = "";
            if(in_array($header, $hiddenColumns)){
	            $colclass .= "hidden";
            }
            if(in_array($header, $spreadsheetColumns)){
	            $colclass .= " spreadsheetCol";
            }
            $tableString .= "<th class='$colclass'>".$header."</th>";
        }
        $tableString .= "</tr>
                           </thead>
                            <tbody>";
        foreach($tableData as $id=>$sessionInfo)
        {
            $tableString .= "<tr>";

            if($checkBoxes)
            {
                $tableString.= "<td><input type=\"checkbox\" class=\"checkbox\" name=\"sessionsCheckbox[]\" value=\"".$sessionInfo['id']."\"> </td>";
            }

            foreach($dataColumns as $columnName)
            {
	            $colclass = "";
	            if(in_array($columnName, $hiddenColumns)){
		            $colclass .= "hidden";
	            }
                $tableString.= "<td class='$colclass'>".$sessionInfo[$columnName]."</td>";
            }
            $tableString .= "</tr>";
        }
        $tableString .= "</tbody>";
        $tableString .= "</table>";

        return $tableString;
    }

}

?>

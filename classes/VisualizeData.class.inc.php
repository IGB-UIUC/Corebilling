<?php

/**
 * Class VisualizeData
 * a helper abstract class used to assist in the generating of
 * smart graphs and tables
 */
class VisualizeData
{


    /** Returns a pie chart using the highcarts.com library
     * @param $graphData
     * @param $sliceField
     * @param $labelField
     * @param $graphTitle
     * @return string
     */
    public static function GraphDataPie($graphData,$sliceField, $labelField, $graphTitle )
    {
        $graphSeriesData = array();
        foreach($graphData as $id => $sessionInfo)
        {
            $sessionString = "['".$sessionInfo[$labelField]."', ".($sessionInfo[$sliceField])."]";
            $graphSeriesData[] = $sessionString;
        }

        $graphString = "<script type=\"text/javascript\">\n
                $(function () {\n
                    var chart;\n

                    $(document).ready(function () {\n

                        // Build the chart\n
                        $('#".$labelField."').highcharts({\n
                            chart: {\n
                                plotBackgroundColor: null,\n
                                plotBorderWidth: null,\n
                                plotShadow: false\n
                            },\n
                            title: {\n
                                text: '".$graphTitle."'\n
                            },\n
                            tooltip: {\n
                                pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b> <br>Hours: <b>{point.y:.1f} Hrs.</b>'\n
                            },\n
                            plotOptions: {\n
                                pie: {\n
                                    allowPointSelect: true,\n
                                    cursor: 'pointer',\n
                                    dataLabels: {\n
                                        enabled: false\n
                                    },\n
                                    showInLegend: true\n
                                }\n
                            },\n
                            series: [{\n
                                type: 'pie',\n
                                name: 'Usage %',\n
                                data: [\n";
            $graphString .= implode(',',$graphSeriesData);
            $graphString .= "   ]\n
                            }]\n
                        });\n
                    });\n
            \n
                });\n
            </script>\n";
            $graphString .= "<div id=\"".$labelField."\" style=\"min-width: 310px; height: 640px; margin: 0 auto\"></div>";
        return $graphString;
    }

    /** Returns a graph time line using the highcharts.com libraries
     * @param $graphData
     * @param $timeField
     * @param $yField
     * @param $labelField
     * @param $graphTitle
     * @param $yLabel
     * @return string
     */
    public static function GraphTimeLine($graphData,$timeField, $yField,$labelField,$graphTitle, $yLabel)
    {
        $devicesSessionArr = array();
        foreach($graphData as $id => $sessionInfo)
        {
            if(!isset($devicesSessionArr[$sessionInfo['device_id']]))
            {
                $devicesSessionArr[$sessionInfo['device_id']]["device_info"] = $sessionInfo[$labelField];
                $devicesSessionArr[$sessionInfo['device_id']]["session_data"] = array();
            }
            $devicesSessionArr[$sessionInfo['device_id']]["session_data"][] = "[".(strtotime($sessionInfo[$timeField])*1000).", ".($sessionInfo[$yField])."]";
        }

        $graphSeriesData = array();
        foreach($devicesSessionArr as $deviceId => $deviceInfo)
        {
            $seriesString = "\n{\n";
            $seriesString .= "name: '".$deviceInfo['device_info']."', \n";
            $seriesString .= "data: [";
            $seriesString .= implode(',',$deviceInfo['session_data']);
            $seriesString .= "\n]}";

            $graphSeriesData[]=$seriesString;
        }

        $graphString ="<script type=\"text/javascript\">\n
                        $(function () {\n
                            $('#".$labelField."_".$yField."_timeline').highcharts({\n
                                chart: {\n
                                    type: 'spline'\n
                                },\n
                                title: {\n
                                    text: '".$graphTitle."'\n
                                },\n
                                xAxis: {\n
                                    type: 'datetime',\n
                                    dateTimeLabelFormats: { // don't display the dummy year\n
                                        month: '%e. %b',\n
                                        year: '%b'\n
                                    },\n
                                    title: {\n
                                        text: 'Date'\n
                                    }\n
                                },\n
                                yAxis: {\n
                                    title: {\n
                                        text: '".$yLabel."'\n
                                    },\n
                                    min: 0\n
                                },\n
                                tooltip: {\n
                                    headerFormat: '<b>{series.name}</b><br>',\n
                                    pointFormat: '{point.x:%e. %b}: {point.y:.2f} ".$yLabel."'\n
                                },\n
                    \n
                                series: [\n";
        $graphString .= implode(',',$graphSeriesData);
        $graphString .= "
                                     ]\n
                            });\n
                        });\n
                    </script>\n ";
        $graphString .= "<div id=\"".$labelField."_".$yField."_timeline\" style=\"min-width: 310px; height: 640px; margin: 0 auto\"></div>";
        return $graphString;
    }

    /** returns a databale which is sortable, searchable and exportable using the datatables jquery module
     * @param $tableData
     * @param $tableHeaders
     * @param $dataColumns
     * @param $tableNameId
     * @param $rowSelected
     * @param bool $checkBoxes
     * @return string
     */
    public static function ListSessionsTable($tableData,$tableHeaders,$dataColumns,$tableNameId,$rowSelected,$checkBoxes=false,$filter=true)
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
     * @param $rowSelected
     * @param bool $checkBoxes
     * @return string
     */
    public static function ListSessionsTableHiddenCols($tableData,$tableHeaders,$dataColumns,$hiddenColumns,$spreadsheetColumns,$tableNameId,$rowSelected,$checkBoxes=false,$filter=true)
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

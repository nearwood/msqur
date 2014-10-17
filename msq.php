<?php

//TODO Find better name
define("LARGE_HOT", 0x1);
define("LARGE_COLD", 0x2);

function msqAxis($el)
{
	//Why the fuck does this flag bork here on not on the table data?
	//And why don't I have to trim the table data either?
	return preg_split("/\s+/", trim($el));//, PREG_SPLIT_NO_EMPTY);
}

function msqTable($name, $data, $x, $y)
{
	$rows = count($y);
	$cols = count($x);
	
	//echo "ROWS: $rows, $cols";
	//var_dump($x, "YYYYYYYYY", $y);
	
	if ($rows * $cols != count($data))
	{
		echo '<div class="error">' . $name . ' column/row count mismatched with data count.</div>';
		return;
	}
	
	echo '<table>'; //TODO Some kind of CSS to indicate color shading?
	echo "<caption>$name</caption>";
	
	for ($r = 0; $r < $rows; $r++)
	{
		echo "<tr><th>" . $y[$r] . "</th>";
		for ($c = 0; $c < $cols; $c++)
		{
			//if ($r == 0) echo "<td>" . $data[$c] . "</td>";
			//else
			echo "<td>" . $data[$r * $rows + $c] . "</td>";
			//echo "</tr>($c, $r) ";
		}
	}
	echo "<tr><th></th>";
	for ($c = 0; $c < $cols; $c++)
	{
		echo "<th>" . $x[$c] . "</th>";
	}
	echo "</tr>";
	echo "</table>";
}

function parseMSQ($xml)
{
	//This should be json and stored somewhere else
	$msqMap = array(//xmlName => pretty name, [xAxisXmlName, yAxisXmlName]
		'veTable1' => array('name' => 'VE Table 1', 'x' => 'frpm_table1', 'y' => 'fmap_table1', 'units' => '%'),
		'advanceTable1' => array('name' => 'Timing Advance', 'x' => 'frpm_table1', 'y' => 'fmap_table1', 'units' => 'degrees'),
		'afrTable1' => array('name' => 'AFR Targets', 'x' => 'arpm_table1', 'y' => 'amap_table1'),
		'egoType' => array('name' => 'O2 Sensor Type')
	);
	
	//Strip out invalid xmlns
	//TODO This should really happen on upload...
	$xml = preg_replace('/xmlns=".*?"/', '', $xml);
	$msq = simplexml_load_string($xml);
	
	if ($msq)
	{
		/*
		 * <bibliography author="TunerStudio MS 2.0.6 - EFI Analytics, Inc." tuneComment="    &lt;br&gt;&#13;    &lt;br&gt;&#13;    &lt;br&gt;&#13;    &lt;br&gt;&#13;    &lt;br&gt;&#13;    &lt;br&gt;&#13;  " writeDate="Mon Jul 15 09:16:28 EDT 2013"/>
		 * <versionInfo fileFormat="4.0" firmwareInfo="" nPages="15" signature="MS3 Format 0262.09 "/>
		 */
		
		//var_dump($msq);
		echo "Format Version: " . $msq->versionInfo['fileFormat'] . "<br/>";
		echo "MS Signature: " . $msq->versionInfo['signature'] . "<br/>";
		echo "Tuning SW: " . $msq->bibliography['author'] . "<br/>";
		echo "Date: " . $msq->bibliography['writeDate'] . "<br/>";
		
		//if cols and rows exist it's a table (maybe 1xR)
		//otherwise it's a single value
		//looks like cols=1 is typical for single dimension
		//still need lookup table of axis
		//wtf is digits?
		
		//foreach ($msq->page as $page)
		//foreach ($page->constant as $constant)
		// //constant[@name="veTable1"]
		foreach ($msqMap as $key => $value)
		{
			$constant = $msq->xpath('//constant[@name="' . $key . '"]')[0];
			if (isset($constant['cols'])) //and >= 1?
			{//We have a table
				//See if this is one we know how to handle
				if (isset($value['x'])) //and y hopefully
				{
					$numCols = (int)$constant['cols'];
					$numRows = (int)$constant['rows'];
					$x = msqAxis($msq->xpath('//constant[@name="' . $value['x'] . '"]')[0]);
					$y = msqAxis($msq->xpath('//constant[@name="' . $value['y'] . '"]')[0]);
					
					if ((count($x) == $numCols) && (count($y) == $numRows))
					{
						$tableData = preg_split("/\s+/", trim($constant));//, PREG_SPLIT_NO_EMPTY); //, $limit);
						msqTable($value['name'], $tableData, $x, $y);
					}
					else
					{
						echo '<div class="error">' . $value['name'] . ' axis count mismatched with data count.</div>';
						echo '<div class="debug">' . count($x) . ", " . count($y) . " vs $numCols, $numRows</div>";
					}
				}
			}
		}
		
		//foreach ($movies->xpath('//settings/setting') as $setting) {
		//	echo $setting->name, 'value: ', $setting->value, PHP_EOL;
		//}
	}
	else
	{
		echo '<div class="error">No such tune dude.</div>';
	}
}

?>

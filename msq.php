<?php

//TODO Find better name
define("LARGE_HOT", 0x1);
//define("LARGE_COLD", 0x1);

function msqAxis($el)
{
	//Why the fuck does this flag bork here on not on the table data?
	//And why don't I have to trim the table data either?
	return preg_split("/\s+/", trim($el));//, PREG_SPLIT_NO_EMPTY);
}

function msqTableColor($data, $rows, $cols, $flags = LARGE_HOT)
{
	$colorTable = array();
	//TODO Use float.min/max equiv.
	$min = 99999;
	$max = -99999;
	
	if ($flags & LARGE_HOT)
	{
	}
	
	//Find min and max
	foreach ($data as $v)
	{
		if ($v < $min) $min = $v;
		else if ($v > $max) $max = $v;
	}
	
	$range = ($max - $min);
	$r = 0; $g = 0; $b = 0; $percent = 0; $intensity = 0.6;
	
	foreach ($data as $k => $v)
	{
		$percent = ($v - $min) / $range;
		
		if ($percent < 0.33)
		{
			$r = 1.0;
			$g = min(1.0, ($percent * 3));
			$b = 0.0;
		}
		else if ($percent < 0.66)
		{
			$r = min(1.0, ((0.66 - $percent) * 3));
			$g = 1.0;
			$b = 0.0;
		}
		else
		{
			$r = 0.0;
			$g = min(1.0, ((1.0 - $percent) * 3));
			$b = 1.0 - $g;
		}
		
		$r  = $r * $intensity + (1.0 - $intensity);
		$g  = $g * $intensity + (1.0 - $intensity);
		$b  = $b * $intensity + (1.0 - $intensity);
		
		$colorTable[$k] = array('r' => $r, 'g' => $g, 'b' => $b);
	}
	
	return $colorTable;
	
	//for ($r = 0; $r < $rows; $r++)
	//{
		//for ($c = 0; $c < $cols; $c++)
		//{
			//$v = $data[($r) * $rows + $c];
			//if ($v < $min) $min = $v;
			//else if ($v > $max) $max = $v;
		//}
	//}
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
	
	//$colorTable = msqTableColor($data, $rows, $cols);
	
	for ($r = 0; $r < $rows; $r++)
	{
		echo "<tr><th>" . $y[$r] . "</th>";
		for ($c = 0; $c < $cols; $c++)
		{
			//if ($r == 0) echo "<td>" . $data[$c] . "</td>";
			//else
			$r = 0; //$colorTable[$r * $rows + $c]['r'];
			$g = 1; //$colorTable[$r * $rows + $c]['g'];
			$b = 0; //firefo$colorTable[$r * $rows + $c]['b'];
			//echo "<td style=\"background:rgb($r,$g,$b)\">" . $data[$r * $rows + $c] . "</td>";
			echo "<td>" . $data[$r * $rows + $c] . "</td>";
		}
		echo "</tr>";
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
		'advanceTable1' => array('name' => 'Timing Advance', 'x' => 'arpm_table1', 'y' => 'amap_table1', 'units' => 'degrees'),
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

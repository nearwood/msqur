<?php

//This should be json and stored somewhere else
$msqMap = array(//xmlName => pretty name, [xAxisXmlName, yAxisXmlName]
	'veTable1' => array('VE Table 1', 'frpm_table1', 'fmap_table1'),
	'frpm_table1' => 'RPM',
	'fmap_table1' => 'MAP'
);

function msqLookup($xmlName)
{
	
}

function msqTable($rows, $cols, $data, $name)
{
	echo '<table class="ve">'; //TODO Some kind of CSS to indicate color shading?
	echo "<caption>$msqMap[$name][0]</caption>";
	
	for ($r = 1; $r <= $ve_rows; $r++)
	{
		echo "<tr><th>" . $map[$r] . "</th>";
		for ($c = 1; $c <= $ve_cols; $c++)
		{
			if ($r == 1) echo "<td>" . $ve[$c] . "</td>";
			else echo "<td>" . $ve[($r - 1) * $ve_rows + $c] . "</td>";
		}
		echo "</tr>";
	}
	echo "<tr><th></th>";
	for ($r = 1; $r <= $rpm_rows; $r++)
	{
		echo "<th>" . $rpm[$r] . "</th>";
	}
	echo "</tr>";
	echo "</table>";
}

function parseMSQ($xml)
{
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
		
		foreach ($msq->page as $page)
		foreach ($page->constant as $constant)
		{
			if ($constant['cols'] >= 1)
			{//We have a table
				$numCols = $constant['cols'];
				$numRows = $constant['rows'];
				$tableData = preg_split("/\s+/", $constant); //, $limit);
				msqTable($numRows, $numCols, $tableData, (string)$constant['name']);
			}
			
			// switch ((string)$constant['name'])
			// {
				// case 'veTable1':
					// $ve_rows = $constant['rows'];
					// $ve_cols = $constant['cols'];
					// $ve = preg_split("/\s+/", $constant); //, $limit);
					// break;
					
				// case 'frpm_table1':
					// $rpm_rows = $constant['rows'];
					// $rpm_cols = $constant['cols']; //1
					// $rpm = preg_split("/\s+/", $constant); //, $limit);
					// break;
					
				// case 'fmap_table1':
					// $map_rows = $constant['rows'];
					// $map_cols = $constant['cols']; //1
					// $map = preg_split("/\s+/", $constant); //, $limit);
					// break;
			// }
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
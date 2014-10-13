<html>
	<head>
<?php
$filename = null;
$msq = null;

function makeTable($msq, $data, $xaxis, $yaxis)
{
	
}

if (!empty($_GET["tune"]))
{
	$filename = htmlspecialchars($_GET["tune"]);
	echo "<title>$filename</title>";
	//TODO Need massive security here
	$msq = simplexml_load_file("files/$filename");
}
else
{
	echo "No tune file specified.";
}
?>
	</head>
	<body>
	<?php
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
		
		foreach ($msq->page as $page)
		foreach ($page->constant as $constant)
		{
			switch ((string)$constant['name'])
			{
				case 'veTable1':
					$ve_rows = $constant['rows'];
					$ve_cols = $constant['cols'];
					$ve = preg_split("/\s+/", $constant); //, $limit);
					break;
					
				case 'frpm_table1':
					$rpm_rows = $constant['rows'];
					$rpm_cols = $constant['cols']; //1
					$rpm = preg_split("/\s+/", $constant); //, $limit);
					break;
					
				case 'fmap_table1':
					$map_rows = $constant['rows'];
					$map_cols = $constant['cols']; //1
					$map = preg_split("/\s+/", $constant); //, $limit);
					break;
			}
		}
		
		echo "<br/>VE TABLE:<br/>";
		echo "<table>";
		
		for ($r = 1; $r <= $ve_rows; $r++)
		{
			echo "<tr><th>" . $map[$r] . "</th>";
			for ($c = 1; $c <= $ve_cols; $c++)
			{
				echo "<td>" . $ve[$r + $c] . "</td>";
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
		
		//foreach ($movies->xpath('//settings/setting') as $setting) {
		//	echo $setting->name, 'value: ', $setting->value, PHP_EOL;
		//}
	}
	else
	{
		echo "Unable to open: $filename";
	}
	?>
	</body>
</html>


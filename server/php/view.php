<html>
	<head>
<?php
$filename = null;
$msq = null;

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
			//echo "HEHEHE:" . $constant;
			switch ((string)$constant['name'])
			{
				case 'veTable1dozen':
					echo "VE TABLE:" . $constant;
					break;
			}
		}
		
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


<?php
require "parse.ini.php";
require "msq.format.php";
//$result = parse_ms_ini("ini/ms2/test.ini", TRUE);

//TODO Find better name
define("LARGE_HOT", 0x1);
define("LARGE_COLD", 0x2);

function getConfig($signature)
{
	//sig is 19 bytes + \0
	//"MS3 Format 0262.09 "
	//"MS3 Format 0435.14P"
	//"MS2Extra comms332m2"
	//"MS2Extra comms333e2"
	//"MS2Extra Serial321 "
	//"MS2Extra Serial310 "
	//"MSII Rev 3.83000   "
	
	//Get the signature from the MSQ
	$sig = explode(' ', $signature, 3); //limit 3 buckets
	$msVersion = $sig[0];
	if ($msVersion == "MS2Extra") $fwVersion = $sig[1];
	else $fwVersion = $sig[2];
	
	if (DEBUG) echo "<div class=\"debug\">$msVersion/$fwVersion</div>";
	
	//Parse msVersion
	switch ($msVersion)
	{
		case "MS1":
			$msDir = "ms1/";
			break;
		case "MSII":
			$msDir = "ms2/";
			break;
		case "MS2Extra":
			$msDir = "ms2extra/";
			break;
		case "MS3":
			$msDir = "ms3/";
			break;
	}
	
	//Setup firmware version for matching.
	//(explode() already trimmed the string of spaces)
	//If there's a decimal, remove any trailing zeros.
	if (strrpos($fwVersion, '.') !== FALSE)
		$fwVersion = rtrim($fwVersion, '0');
	
	$iniFile = "ini/" . $msDir . $fwVersion . ".ini";
	$msqMap = parse_ms_ini($iniFile, TRUE);

	
	return $msqMap;
}

function msqAxis($el)
{
	//Why the fuck does this flag bork here on not on the table data?
	//And why don't I have to trim the table data either?
	return preg_split("/\s+/", trim($el));//, PREG_SPLIT_NO_EMPTY);
}

function msqTable(&$output, $name, $data, $x, $y, $hot)
{
	$rows = count($y);
	$cols = count($x);
	
	//echo "ROWS: $rows, $cols";
	//var_dump($x, "YYYYYYYYY", $y);
	
	if ($rows * $cols != count($data))
	{
		$output .= '<div class="error">' . $name . ' column/row count mismatched with data count.</div>';
		return;
	}
	
	//TODO Probably there's a better way to do this (like on the front end)
	if (stripos($name, "VE") === FALSE)
	{
		$output .= '<table class="msq tablesorter" hot="' . $hot . '">';
	}
	else
	{
		$output .= '<table class="msq tablesorter ve" hot="' . $hot . '">';
	}
	
	$output .= "<caption>$name</caption>";
	
	$output .= "<thead><tr><th></th>";
	for ($c = 0; $c < $cols; $c++)
	{
		//TODO: This is not triggering tablesorter
		$output .= '<th class="{sorter: false}">' . $x[$c] . "</th>";
	}
	$output .= "</tr></thead>";
	
	for ($r = 0; $r < $rows; $r++)
	{
		$output .= "<tr><th>" . $y[$r] . "</th>";
		for ($c = 0; $c < $cols; $c++)
		{
			//If just a 2D table we ignore the $rows offset.
			if ($cols == 1) $output .= "<td>" . $data[$r] . "</td>";
			else $output .= "<td>" . $data[$r * $rows + $c] . "</td>";
		}
	}
	
	$output .= "</tr>";
	$output .= "</table>";
}

function msqConstant($constant, $value)
{
	//var_export($value);
	return '<div class="constant">' . $constant . ': ' . $value . '</div>';
}

//TODO Uh, this should be in db.php


function parseMSQ($xml, &$output)
{
	if (DEBUG) echo '<div class="debug">Parsing MSQ...</div>';
	$errorCount = 0; //Keep track of how many things go wrong.
	
	$msq = simplexml_load_string($xml);
	
	if ($msq)
	{
		/*
		 * <bibliography author="TunerStudio MS 2.0.6 - EFI Analytics, Inc." tuneComment="    &lt;br&gt;&#13;    &lt;br&gt;&#13;    &lt;br&gt;&#13;    &lt;br&gt;&#13;    &lt;br&gt;&#13;    &lt;br&gt;&#13;  " writeDate="Mon Jul 15 09:16:28 EDT 2013"/>
		 * <versionInfo fileFormat="4.0" firmwareInfo="" nPages="15" signature="MS3 Format 0262.09 "/>
		 */
		
		//var_dump($msq);
		$output .= '<div class="info">';
		$output .= "<div>Format Version: " . $msq->versionInfo['fileFormat'] . "</div>";
		$output .= "<div>MS Signature: " . $msq->versionInfo['signature'] . "</div>";
		$output .= "<div>Tuning SW: " . $msq->bibliography['author'] . "</div>";
		$output .= "<div>Date: " . $msq->bibliography['writeDate'] . "</div>";
		$output .= '</div>';
		
		$msqMap = getConfig($msq->versionInfo['signature']);
		$msqMap = $msqMap['Constants'];
		$schema = getSchema();
		
		//if (DEBUG) { echo '<div class="debug"><pre>'; var_export($msqMap); echo '</pre></div>'; }
		
		//if cols and rows exist it's a table (maybe 1xR)
		//otherwise it's a single value
		//looks like cols=1 is typical for single dimension
		//still need lookup table of axis
		//wtf is digits?
		
		//foreach ($msq->page as $page)
		//foreach ($page->constant as $constant)
		// //constant[@name="veTable1"]
		foreach ($msqMap as $key => $config)
		{
			if (DEBUG) echo "<div class=\"debug\">Searching for: $key</div>";
			$search = $msq->xpath('//constant[@name="' . $key . '"]');
			if ($search === FALSE || count($search) == 0) continue;
			
			if (DEBUG) echo "<div class=\"debug\">Found constant: $search[0]</div>";
			
			//TODO need lookup table of user-friendly names (nCylinders => Number of Cylinders, etc.).
			//TODO Use ini to know how many values?
			//TODO Still need lookup for veTableX => frpmTableX matchinghg 
			
			if (array_key_exists($key, $schema))
			{
				$format = $schema[$key];
				$constant = $search[0];
				
				if (isset($constant['cols'])) //and >= 1?
				{//We have a table
					//See if this is one we know how to handle
					if (isset($format['x']) && isset($format['y']))
					{//3D Table
						$numCols = (int)$constant['cols'];
						$numRows = (int)$constant['rows'];
						$x = msqAxis($msq->xpath('//constant[@name="' . $format['x'] . '"]')[0]);
						$y = msqAxis($msq->xpath('//constant[@name="' . $format['y'] . '"]')[0]);
						
						if ((count($x) == $numCols) && (count($y) == $numRows))
						{
							$tableData = preg_split("/\s+/", trim($constant));//, PREG_SPLIT_NO_EMPTY); //, $limit);
							msqTable($output, $format['name'], $tableData, $x, $y, $format['hot']);
						}
						else
						{
							$output .= '<div class="error">' . $format['name'] . ' axis count mismatched with data count.</div>';
							$output .= '<div class="debug">' . count($x) . ", " . count($y) . " vs $numCols, $numRows</div>";
							$errorCount += 1;
						}
					}
					else if (isset($format['y']))
					{//2D
						$numCols = (int)$constant['cols'];
						$numRows = (int)$constant['rows'];
						$x = array($format['units']);//msqAxis(trim($constant));
						$y = msqAxis($msq->xpath('//constant[@name="' . $format['y'] . '"]')[0]);
						
						if ((count($x) == $numCols) && (count($y) == $numRows))
						{
							$tableData = preg_split("/\s+/", trim($constant));//, PREG_SPLIT_NO_EMPTY); //, $limit);
							msqTable($output, $format['name'], $tableData, $x, $y, $format['hot']);
						}
						else
						{
							$output .= '<div class="error">' . $format['name'] . ' configured axis count mismatched with data count.</div>';
							$output .= '<div class="debug">' . count($x) . ", " . count($y) . " vs $numCols, $numRows</div>";
							$errorCount += 1;
						}
					}
				}
				else
				{
					$output .= msqConstant($format['name'], $search[0]);
					//TODO $format['units']
				}
			}
		}
		
		//foreach ($movies->xpath('//settings/setting') as $setting) {
		//	$output .= $setting->name, 'value: ', $setting->value, PHP_EOL;
		//}
	}
	else
	{
		$output .= '<div class="error">Unable to parse tune.</div>';
	}
	
	return $errorCount;
}

function msqError($e)
{
	echo '<div class="error">Error parsing MSQ. ';
	echo $e->getMessage();
	echo '</div>';
}

?>

<?php
require "parse.ini.php";

//$result = parse_ms_ini("ini/ms2/test.ini", TRUE);

//TODO Find better name
define("LARGE_HOT", 0x1);
define("LARGE_COLD", 0x2);

function parseSchema($signature)
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
	
	//Look for a matching INI file
	switch ($msVersion)
	{
		case "MS1":
			$msDir = "ms1/";
			$msFilePrefix = "ms1-";
			break;
		case "MSII":
			$msDir = "ms2/";
			$msFilePrefix = "ms2-";
			break;
		case "MS2Extra":
			$msDir = "ms2extra/";
			$msFilePrefix = "ms2e-";
			break;
		case "MS3":
			$msDir = "ms3/";
			$msFilePrefix = "ms3-";
			break;
	}
	
	$iniFile = "ini/" . $msDir . $msFilePrefix . $fwVersion;
	if (DEBUG) echo "<div class=\"debug\">Attempting to open: $iniFile</div>";
	$msqMap = parse_ms_ini($iniFile, TRUE);
	
	//~ if (DEBUG) echo '<div class="debug">Using default (1.3) Schema</div>';
	//~ $msqMap = array(//xmlName => pretty name, [xAxisXmlName, yAxisXmlName]
		//~ 'veTable1' => array('name' => 'VE Table 1', 'x' => 'frpm_table1', 'y' => 'fmap_table1', 'units' => '%', 'hot' => 'descending'),
		//~ 'advanceTable1' => array('name' => 'Timing Advance', 'x' => 'srpm_table1', 'y' => 'smap_table1', 'units' => 'degrees', 'hot' => 'ascending'),
		//~ 'afrTable1' => array('name' => 'AFR Targets', 'x' => 'arpm_table1', 'y' => 'amap_table1', 'hot' => 'ascending'),
		//~ 'egoType' => array('name' => 'O2 Sensor Type'),
		//~ 'nCylinders' => array('name' => 'Cylinders')
	//~ );
	
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
			//if ($r == 0) $output .= "<td>" . $data[$c] . "</td>";
			//else
			$output .= "<td>" . $data[$r * $rows + $c] . "</td>";
			//$output .= "</tr>($c, $r) ";
		}
	}
	
	$output .= "</tr>";
	$output .= "</table>";
}

function msqConstant($constant, $value)
{
	return '<div class="constant">$constant: ' . $value . '</div>';
}

//TODO Uh, this should be in db.php
function getMSQ($id)
{
	if (DEBUG) echo '<div class="debug">getMSQ()</div>';
	$db = connect();
	if ($db == null) return null;
	
	$html = null;
	
	try
	{
		$st = $db->prepare("SELECT html FROM msqs INNER JOIN metadata ON metadata.msq = msqs.id WHERE metadata.id = :id LIMIT 1");
		$st->bindParam(":id", $id);
		$st->execute();
		if ($st->rowCount() > 0)
		{
			$result = $st->fetch(PDO::FETCH_ASSOC);
			$html = $result['html'];
			if ($html === NULL || DISABLE_MSQ_CACHE)
			{//MSQ not parsed yet.
				if (DEBUG) echo '<div class="debug">no html, get xml</div>';
				$st = $db->prepare("SELECT xml FROM msqs INNER JOIN metadata ON metadata.msq = msqs.id WHERE metadata.id = :id LIMIT 1");
				$st->bindParam(":id", $id);
				$st->execute();
				$result = $st->fetch(PDO::FETCH_ASSOC);
				$html = "";
				parseMSQ($result['xml'], $html);
				
				if (DEBUG) echo '<div class="debug">put html in db</div>';
				$st = $db->prepare("UPDATE msqs ms, metadata m SET ms.html=:html WHERE m.msq = ms.id AND m.id = :id");
				//$xml = mb_convert_encoding($html, "UTF-8");
				$st->bindParam(":id", $id);
				$st->bindParam(":html", $html);
				$st->execute();
			}
			else
			{
				if (DEBUG) echo '<div class="debug">Found html</div>';
			}
		}
		else
		{
			if (DEBUG) echo '<div class="debug">0 rows for $id</div>';
			echo '<div class="error">Invalid MSQ</div>';
		}
	}
	catch(PDOException $e)
	{
		dbError($e);
	}
	
	return $html;
}

function parseMSQ($xml, &$output)
{
	if (DEBUG) echo '<div class="debug">parseXML()</div>';
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
		
		$msqMap = parseSchema($msq->versionInfo['signature']);
		
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
						msqTable($output, $value['name'], $tableData, $x, $y, $value['hot']);
					}
					else
					{
						$output .= '<div class="error">' . $value['name'] . ' axis count mismatched with data count.</div>';
						$output .= '<div class="debug">' . count($x) . ", " . count($y) . " vs $numCols, $numRows</div>";
						$errorCount += 1;
					}
				}
			}
			else
			{//regular constant?
				
				$constant = $msq->xpath('//constant[@name="' . $key . '"]')[0];
				$output .= msqConstant($constant, $value);
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

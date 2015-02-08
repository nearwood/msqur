<?php
require "parse.ini.php";
require "msq.format.php";

//TODO Find better name
define("LARGE_HOT", 0x1);
define("LARGE_COLD", 0x2);

function getConfig(&$signature)
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
	$sig = explode(' ', $signature); //, 3); limit 3 buckets
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
	//(explode() already trimmed the string of spaces) -- this isn't true a couple inis
	//If there's a decimal, remove any trailing zeros.
	if (strrpos($fwVersion, '.') !== FALSE)
		$fwVersion = rtrim($fwVersion, '0');
	
	//store all our hardwork for use in the calling function
	$signature = array($msVersion, $fwVersion);
	
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

function msqTable($name, $data, $x, $y, $hot)
{
	$output = "";
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
	
	return $output;
}

function msqConstant($constant, $value)
{
	//var_export($value);
	return '<div class="constant">' . $constant . ': ' . $value . '</div>';
}

function parseMSQ($xml, &$engine, &$metadata)
{
	$html = array();
	if (DEBUG) echo '<div class="debug">Parsing MSQ...</div>';
	$errorCount = 0; //Keep track of how many things go wrong.
	
	$msq = simplexml_load_string($xml);
	
	if ($msq)
	{
		$htmlHeader = '<div class="info">';
		$htmlHeader .= "<div>Format Version: " . $msq->versionInfo['fileFormat'] . "</div>";
		$htmlHeader .= "<div>MS Signature: " . $msq->versionInfo['signature'] . "</div>";
		$htmlHeader .= "<div>Tuning SW: " . $msq->bibliography['author'] . "</div>";
		$htmlHeader .= "<div>Date: " . $msq->bibliography['writeDate'] . "</div>";
		$htmlHeader .= '</div>';
		
		$sig = $msq->versionInfo['signature'];
		$msqMap = getConfig($sig);
		
		if ($msqMap == null)
		{
			$htmlHeader .= "<div class=\"error\">Unable to load the corresponding configuration file for that MSQ. Please file a bug requesting: $sig[0]/$sig[1].ini</div>";
		}
		
		$html['header'] = $htmlHeader;
		
		//Calling function will update
		$metadata['fileFormat'] = $msq->versionInfo['fileFormat'];
		$metadata['signature'] = $sig[1];
		$metadata['firmware'] = $sig[0];
		$metadata['author'] = $msq->bibliography['author'];
		
		$msqMap = $msqMap['Constants'];
		$schema = getSchema();
		$engineSchema = getEngineSchema();
		
		foreach ($msqMap as $key => $config)
		{
			if (DEBUG) echo "<div class=\"debug\">Searching for: $key</div>";
			$search = $msq->xpath('//constant[@name="' . $key . '"]');
			if ($search === FALSE || count($search) == 0) continue;
			$constant = $search[0];
			
			if (DEBUG) echo "<div class=\"debug\">Found constant: $search[0]</div>";
			
			if (array_key_exists($key, $schema))
			{
				$format = $schema[$key];
				
				if (isset($format['group']))
					$group = $format['group'];
				else
				{
					if (DEBUG) echo "<div class=\"debug\">No group set for: $key</div>";
					$group = "misc";
				}
				
				if (!isset($html[$group])) $html[$group] = "";
				
				if (isset($constant['cols'])) //and >= 1?
				{//We have a table
					if (isset($format['x']) && isset($format['y']))
					{//3D Table
						$numCols = (int)$constant['cols'];
						$numRows = (int)$constant['rows'];
						
						$x = $msq->xpath('//constant[@name="' . $format['x'] . '"]');
						$y = $msq->xpath('//constant[@name="' . $format['y'] . '"]');
						
						if (isset($x[0]) || isset($y[0]))
						{
							$x = msqAxis($x[0]);
							$y = msqAxis($y[0]);
							
							if ((count($x) == $numCols) && (count($y) == $numRows))
							{
								$tableData = preg_split("/\s+/", trim($constant));//, PREG_SPLIT_NO_EMPTY); //, $limit);
								$html[$group] .= msqTable($format['name'], $tableData, $x, $y, $format['hot']);
							}
							else
							{
								$html[$group] .= '<div class="error">' . $format['name'] . ' axis count mismatched with data count.</div>';
								$html[$group] .= '<div class="debug">' . count($x) . ", " . count($y) . " vs $numCols, $numRows</div>";
								$errorCount += 1;
							}
						}
					}
					else if (isset($format['y']))
					{//2D
						$numCols = (int)$constant['cols'];
						$numRows = (int)$constant['rows'];
						$x = array($format['units']);//msqAxis(trim($constant));
						
						$y = $msq->xpath('//constant[@name="' . $format['y'] . '"]');
						if (isset($y[0]))
						{
							$y = msqAxis($y[0]);
							
							if ((count($x) == $numCols) && (count($y) == $numRows))
							{
								$tableData = preg_split("/\s+/", trim($constant));//, PREG_SPLIT_NO_EMPTY); //, $limit);
								$html[$group] .= msqTable($format['name'], $tableData, $x, $y, $format['hot']);
							}
							else
							{
								$html[$group] .= '<div class="error">' . $format['name'] . ' configured axis count mismatched with data count.</div>';
								$html[$group] .= '<div class="debug">' . count($x) . ", " . count($y) . " vs $numCols, $numRows</div>";
								$errorCount += 1;
							}
						}
					}
				}
				else
				{
					$html[$group] .= msqConstant($format['name'], $constant);
					//TODO $format['units']
				}
			}
			
			//if (DEBUG) echo "<div class=\"debug\">Trying $key for engine data</div>";
			if (array_key_exists($key, $engineSchema))
			{
				if (DEBUG) echo "<div class=\"debug\">Found engine data: $key ($constant)</div>";
				$engine[$key] = trim($constant, '"');
			}
		}
	}
	else
	{
		$html['header'] = '<div class="error">Unable to parse tune.</div>';
	}
	
	return $html;
}

function msqError($e)
{
	echo '<div class="error">Error parsing MSQ. ';
	echo $e->getMessage();
	echo '</div>';
}

?>

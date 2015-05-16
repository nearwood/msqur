<?php

include "msq.format.php";

class MSQ
{
	public function getXML() {}
	public function getHTML() {}
	
	/**
	 * @brief Given a signature string, finds and parses the respective INI file.
	 * 
	 * Returns an array of the config file contents.
	 * @param $signature The signature string which will be modified into a firmware/version array.
	 */
	public function getConfig(&$signature)
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
		$msqMap = INI::parse($iniFile, TRUE); //TODO Fix
		
		return $msqMap;
	}

	/**
	 * @brief Split and format string of Axis values
	 * TODO This should be static
	 * @param $el string
	 * @returns A array of strings
	 */
	private function msqAxis($el)
	{
		//Why the fuck does this flag bork here on not on the table data?
		//And why don't I have to trim the table data either?
		return preg_split("/\s+/", trim($el));//, PREG_SPLIT_NO_EMPTY);
	}

	/**
	 * @brief Get an HTML table from data, axes, and some other stuff.
	 * TODO This should be static
	 * @param $name Friendly name to use for the table
	 * @param $data 1D array of data
	 * @param $x X axis data array
	 * @param $y Y axis data array
	 * @param $hot For colorizing on the front end, which values are "high"
	 * @returns A huge string containing a root <table> element
	 */
	private function msqTable($name, $data, $x, $y, $hot)
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
	/**
	 * @brief Format a constant to HTML
	 * @param $constant The constant name
	 * @param $value It's value
	 * @returns String HTML \<div\>
	 */
	private function msqConstant($constant, $value)
	{
		//var_export($value);
		return '<div class="constant">' . $constant . ': ' . $value . '</div>';
	}

	/**
	 * @brief Parse MSQ XML into an array of HTML 'groups'.
	 * @param $xml SimpleXML
	 * @param $engine 
	 * @param $metadata 
	 * @returns String HTML
	 */
	public function parseMSQ($xml, &$engine, &$metadata)
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
			$msqMap = $this->getConfig($sig);
			
			if ($msqMap == null)
			{
				$htmlHeader .= "<div class=\"error\">Unable to load the corresponding configuration file for that MSQ. Please file a bug requesting: $sig[0]/$sig[1]</div>";
			}
			
			$html['header'] = $htmlHeader;
			
			//Calling function will update
			$metadata['fileFormat'] = $msq->versionInfo['fileFormat'];
			$metadata['signature'] = $sig[1];
			$metadata['firmware'] = $sig[0];
			$metadata['author'] = $msq->bibliography['author'];
			
			$constants = $msqMap['Constants'];
			$curves = $msqMap['CurveEditor'];
			//$tables = $msqMap['TableEditor'];
			$engineSchema = getEngineSchema();
			
			//--foreach ($msqMap as $key => $config)
			//foreach ($tabless as $table)
			//foreach ($constants as $constant)
			$html["curves"] = "";
			foreach ($curves as $curve)
			{
				if (DEBUG) echo '<div class="debug">Curve: ' . $curve['id'] . '</div>';
				
				//id is just for menu (and our reference)
				//need to find xBin (index 0, 1 is the live meatball variable)
				//and find yBin and output those.
				//columnLabel also for labels
				//xAxis and yAxis are just for maximums?
				$help = NULL;
				if (array_key_exists('topicHelp', $curve))
					$help = $curve['topicHelp'];
				
				$xBins = $this->findConstant($msq, $curve['xBinConstant']);
				$yBins = $this->findConstant($msq, $curve['yBinConstant']);
				$xAxis = $this->msqAxis($xBins);
				$yAxis = $this->msqAxis($yBins);
				$html["curves"] .= $this->msqTable2D($curve, $curve['xMin'], $curve['xMax'], $xAxis, $curve['yMin'], $curve['yMax'], $yAxis, $help);
				
				//if (DEBUG) echo "<div class=\"debug\">Found constant: $search[0]</div>";
				
				
				
				//if (array_key_exists($key, $schema))
				//{
					//$format = $schema[$key];
					
					//if (isset($format['group']))
						//$group = $format['group'];
					//else
					//{
						//if (DEBUG) echo "<div class=\"debug\">No group set for: $key</div>";
						//$group = "misc";
					//}
					
					//if (!isset($html[$group])) $html[$group] = "";
					
					//if (isset($constant['cols'])) //and >= 1?
					//{//We have a table
						//if (isset($format['x']) && isset($format['y']))
						//{//3D Table
							//$numCols = (int)$constant['cols'];
							//$numRows = (int)$constant['rows'];
							
							//$x = $msq->xpath('//constant[@name="' . $format['x'] . '"]');
							//$y = $msq->xpath('//constant[@name="' . $format['y'] . '"]');
							
							//if (isset($x[0]) || isset($y[0]))
							//{
								//$x = $this->msqAxis($x[0]);
								//$y = $this->msqAxis($y[0]);
								
								//if ((count($x) == $numCols) && (count($y) == $numRows))
								//{
									//$tableData = preg_split("/\s+/", trim($constant));//, PREG_SPLIT_NO_EMPTY); //, $limit);
									//$html[$group] .= $this->msqTable($format['name'], $tableData, $x, $y, $format['hot']);
								//}
								//else
								//{
									//$html[$group] .= '<div class="error">' . $format['name'] . ' axis count mismatched with data count.</div>';
									//$html[$group] .= '<div class="debug">' . count($x) . ", " . count($y) . " vs $numCols, $numRows</div>";
									//$errorCount += 1;
								//}
							//}
						//}
						//else if (isset($format['y']))
						//{//2D
							//$numCols = (int)$constant['cols'];
							//$numRows = (int)$constant['rows'];
							//$x = array($format['units']);//msqAxis(trim($constant));
							
							//$y = $msq->xpath('//constant[@name="' . $format['y'] . '"]');
							//if (isset($y[0]))
							//{
								//$y = $this->msqAxis($y[0]);
								
								//if ((count($x) == $numCols) && (count($y) == $numRows))
								//{
									//$tableData = preg_split("/\s+/", trim($constant));//, PREG_SPLIT_NO_EMPTY); //, $limit);
									//$html[$group] .= $this->msqTable($format['name'], $tableData, $x, $y, $format['hot']);
								//}
								//else
								//{
									//$html[$group] .= '<div class="error">' . $format['name'] . ' configured axis count mismatched with data count.</div>';
									//$html[$group] .= '<div class="debug">' . count($x) . ", " . count($y) . " vs $numCols, $numRows</div>";
									//$errorCount += 1;
								//}
							//}
						//}
					//}
					//else
					//{
						//$html[$group] .= $this->msqConstant($format['name'], $constant);
						////TODO $format['units']
					//}
				//}
			}
			foreach ($constants as $key => $config)
			{
				//if (DEBUG) echo "<div class=\"debug\">Trying $key for engine data</div>";
				if (array_key_exists($key, $engineSchema))
				{
					if (DEBUG) echo "<div class=\"debug\">Found engine data: $key</div>";
					$constant = $this->findConstant($msq, $key);
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

	private function msqError($e)
	{
		echo '<div class="error">Error parsing MSQ. ';
		echo $e->getMessage();
		echo '</div>';
	}
	
	private function findConstant($xml, $constant)
	{
		$search = $xml->xpath('//constant[@name="' . $constant . '"]');
		if ($search === FALSE || count($search) == 0) return NULL;
		else return $search[0];
	}
	
	private function msqTable2D($curve, $xMin, $xMax, $xAxis, $yMin, $yMax, $yAxis, $helpText)
	{
		$output = "";
		$hot = 0;
		
		if (DEBUG) echo '<div class="debug">Formatting curve: ' . $curve['id'] . '</div>';
		
		$dataCount = count($xAxis);
		if ($dataCount !== count($yAxis))
		{
			$output .= '<div class="error">Axis lengths not equal for: ' . $curve['desc'] . '</div>';
			//if (DEBUG) $output .= "<div class=\"debug\">Found engine data: $key ($constant)</div>";
			return $output;
		}
		
		$output .= '<table class="msq tablesorter 2d" hot="' . $hot . '">';
		$output .= '<caption>' . $curve['desc'] . '</caption>';
		
		for ($c = 0; $c < $dataCount; $c++)
		{
			//TODO This is not triggering tablesorter
			$output .= '<tr><th class="{sorter: false}">';
			$output .= $yAxis[$c];
			$output .= "</th><td>";
			$output .= $xAxis[$c];
			$output .= "</td></tr>";
		}
		$output .= "</table>";
		return $output;
	}
}

?>

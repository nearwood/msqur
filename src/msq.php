<?php

include "msq.format.php";
include "util.php";

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
	private function msqConstant($constant, $value, $help)
	{
		//var_export($constant);
		//var_export($value);
		//var_export($help);
		return '<div class="constant">' . $constant . ' (' . $help . '): ' . $value . '</div>';
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
			$helpTexts = $msqMap['SettingContextHelp'];
			$tables = $msqMap['TableEditor'];
			$engineSchema = getEngineSchema();
			
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
				
				//var_export($curve);
				
				if (array_keys_exist($curve, 'desc', 'xBinConstant', 'yBinConstant', 'xMin', 'xMax', 'yMin', 'yMax'))
				{
					$xBins = $this->findConstant($msq, $curve['xBinConstant']);
					$yBins = $this->findConstant($msq, $curve['yBinConstant']);
					$xAxis = preg_split("/\s+/", trim($xBins));
					$yAxis = preg_split("/\s+/", trim($yBins));
					$html["curves"] .= $this->msqTable2D($curve, $curve['xMin'], $curve['xMax'], $xAxis, $curve['yMin'], $curve['yMax'], $yAxis, $help);
				}
				else if (DEBUG) echo '<div class="debug">Missing/unsupported curve information: ' . $curve['id'] . '</div>';
			}
			
			$html["tables"] = "";
			foreach ($tables as $table)
			{
				if (DEBUG) echo '<div class="debug">Table: ' . $table['id'] . '</div>';
				
				$help = NULL;
				if (array_key_exists('topicHelp', $table))
					$help = $table['topicHelp'];
				
				//var_export($table);
				
				if (array_keys_exist($table, 'desc', 'xBinConstant', 'yBinConstant', 'zBinConstant'))
				{
					$xBins = $this->findConstant($msq, $table['xBinConstant']);
					$yBins = $this->findConstant($msq, $table['yBinConstant']);
					$zBins = $this->findConstant($msq, $table['zBinConstant']);
					$xAxis = preg_split("/\s+/", trim($xBins));
					$yAxis = preg_split("/\s+/", trim($yBins));
					$zData = preg_split("/\s+/", trim($zBins));//, PREG_SPLIT_NO_EMPTY); //, $limit);
					$html["tables"] .= $this->msqTable3D($table, $xAxis, $yAxis, $zData, $help);
				}
				else if (DEBUG) echo '<div class="debug">Missing/unsupported table information: ' . $table['id'] . '</div>';
			}
			
			$html["constants"] = "";
			foreach ($constants as $key => $config)
			{
				$value = $this->findConstant($msq, $key);
				
				//if (DEBUG) echo "<div class=\"debug\">Trying $key for engine data</div>";
				if ($value !== NULL)
				{
					$value = trim($value, '"');
					if (array_key_exists($key, $engineSchema))
					{
						if (DEBUG) echo "<div class=\"debug\">Found engine data: $key => $value</div>";
						$engine[$key] = $value;
					}
					
					if (array_key_exists($key, $helpTexts))
					$help = $helpTexts[$key];
					
					$html["constants"] .= $this->msqConstant($key, $value, $help);
				}
				
				//$html[$group] .= $this->msqConstant($format['name'], $constant);
				//TODO $format['units']
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
		
		//if (DEBUG) echo '<div class="debug">Formatting curve: ' . $curve['id'] . '</div>';
		
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
	
	private function msqTable3D($table, $xAxis, $yAxis, $zBins, $helpText)
	{
		$output = "";
		$hot = 0;
		$rows = count($yAxis);
		$cols = count($xAxis);
		
		//if (DEBUG) echo '<div class="debug">Formatting table: ' . $table['id'] . '</div>';
		
		$dataCount = count($zBins);
		if ($dataCount !== $rows * $cols)
		{
			$output .= '<div class="error">Axis/data lengths not equal for: ' . $table['desc'] . '</div>';
			return $output;
		}
		
		//TODO Probably there's a better way to do this (like on the front end)
		if (stripos($table['id'], "ve") === FALSE)
		{
			$output .= '<table class="msq tablesorter 3d" hot="' . $hot . '">';
		}
		else
		{
			$output .= '<table class="msq tablesorter 3d ve" hot="' . $hot . '">';
		}
		
		$output .= '<caption>' . $table['desc'] . '</caption>';
		$output .= "<thead><tr><th></th>"; //blank cell for corner
		for ($c = 0; $c < $cols; $c++)
		{
			//TODO: This is not triggering tablesorter
			$output .= '<th class="{sorter: false}">' . $xAxis[$c] . "</th>";
		}
		$output .= "</tr></thead>";
		
		for ($r = 0; $r < $rows; $r++)
		{
			$output .= "<tr><th>" . $yAxis[$r] . "</th>";
			for ($c = 0; $c < $cols; $c++)
			{
				$output .= "<td>" . $zBins[$r * $rows + $c] . "</td>";
			}
		}
		
		$output .= "</tr>";
		$output .= "</table>";
		return $output;
		
		
	}
}

?>

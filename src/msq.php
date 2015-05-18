<?php

include "msq.format.php";
include "util.php";

/**
 * @brief MSQ Parsing class.
 * 
 */
class MSQ
{
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
		return "<div class=\"constant\" title=\"$help\"><b>$constant</b>: $value</div>";
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
			$msqMap = INI::getConfig($sig);
			
			if ($msqMap == null)
			{
				$htmlHeader .= "<div class=\"error\">Unable to load the corresponding configuration file for that MSQ. Please file a bug requesting: $sig[0]/$sig[1]</div>";
				$html['header'] = $htmlHeader;
				return $html;
			}
			
			$html['header'] = $htmlHeader;
			
			//Calling function will update
			$metadata['fileFormat'] = $msq->versionInfo['fileFormat'];
			$metadata['signature'] = $sig[1];
			$metadata['firmware'] = $sig[0];
			$metadata['author'] = $msq->bibliography['author'];
			
			$constants = array();
			$helpTexts = array();
			$curves = array();
			$tables = array();
			
			if (array_key_exists('Constants', $msqMap)) $constants = $msqMap['Constants'];
			if (array_key_exists('SettingContextHelp', $msqMap)) $helpTexts = $msqMap['SettingContextHelp'];
			if (array_key_exists('CurveEditor', $msqMap)) $curves = $msqMap['CurveEditor'];
			if (array_key_exists('TableEditor', $msqMap)) $tables = $msqMap['TableEditor'];
			
			$engineSchema = getEngineSchema();
			
			$html["curves"] = "";
			foreach ($curves as $curve)
			{
				if (in_array($curve['id'], $this->msq_curve_blacklist))
				{
					if (DEBUG) echo '<div class="debug">Skipping curve: ' . $curve['id'] . '</div>';
					continue;
				}
				else if (DEBUG) echo '<div class="debug">Curve: ' . $curve['id'] . '</div>';
				
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
				if ($config[0] == "array") continue; //TODO Skip arrays until blacklist is done
				
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
			}
		}
		else
		{
			$html['header'] = '<div class="error">Unable to parse tune.</div>';
		}
		
		return $html;
	}
	
	/**
	 * @brief Convenience function to display errors.
	 * @param $e The error to display.
	 * @returns String Error in HTML form.
	 */
	private function msqError($e)
	{
		echo '<div class="error">Error parsing MSQ. ';
		echo $e->getMessage();
		echo '</div>';
	}
	
	/**
	 * @brief Find constant value in MSQ XML.
	 * @param $xml SimpleXML
	 * @param $constant ID of constant to search for
	 * @returns String of constant value, or NULL if not found
	 */
	private function findConstant($xml, $constant)
	{
		$search = $xml->xpath('//constant[@name="' . $constant . '"]');
		if ($search === FALSE || count($search) == 0) return NULL;
		else return $search[0];
	}
	
	/**
	 * @brief Get an HTML table from 2D data.
	 * @param $curve Array of values I'm too lazy to parameterize.
	 * @param $xMin Minimum X axis value (NI)
	 * @param $xMax Maximum X axis value (NI)
	 * @param $xAxis Array of actual X set points
	 * @param $yMin Minimum Y axis value (NI)
	 * @param $yMax Maximum Y axis value (NI)
	 * @param $yAxis Array of actual Y set points
	 * @param $helpText Optional text to display for more information
	 * @returns A huge string containing a root <table> element
	 */
	private function msqTable2D($curve, $xMin, $xMax, $xAxis, $yMin, $yMax, $yAxis, $helpText = null)
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
		
		$output .= '<h3>' . $curve['desc'] . '</h3>';
		$output .= '<div class="curve"><table class="msq tablesorter 2d" hot="' . $hot . '">';
		if ($helpText != null) $output .= '<caption>' . $helpText . '</caption>';
		
		for ($c = 0; $c < $dataCount; $c++)
		{
			//TODO This is not triggering tablesorter
			$output .= '<tr><th class="{sorter: false}">';
			$output .= $yAxis[$c];
			$output .= "</th><td>";
			$output .= $xAxis[$c];
			$output .= "</td></tr>";
		}
		$output .= "</table></div>";
		return $output;
	}
	
	/**
	 * @brief Get an HTML table from 3D data.
	 * @param $table Array of values I'm too lazy to parameterize.
	 * @param $xAxis Array of actual X set points
	 * @param $yAxis Array of actual Y set points
	 * @param $zBins Array of actual Z set points
	 * @param $helpText Optional text to display for more information
	 * @returns A huge string containing a root <table> element
	 */
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
		
		$output .= '<h3>' . $table['desc'] . '</h3>';
		//TODO Probably there's a better way to do this (like on the front end)
		if (stripos($table['id'], "ve") === FALSE)
		{
			$output .= '<div class="table"><table class="msq tablesorter 3d" hot="' . $hot . '">';
		}
		else
		{
			$output .= '<div class="table"><table class="msq tablesorter 3d ve" hot="' . $hot . '">';
		}
		
		if ($helpText != null) $output .= '<caption>' . $helpText . '</caption>';
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
		$output .= "</table></div>";
		return $output;
	}
	
	private $msq_curve_blacklist = array("vmcurve", "s5curve");
	
	private $msq_constant_blacklist = array("afrTable1",
		"afrTable2",
		"veTable1",
		"veTable2",
		"veTable3"
	);
}

?>

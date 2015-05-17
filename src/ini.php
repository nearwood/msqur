<?php
/*
 * @brief INI Stuff
 * INI.getConfig
 */
class INI
{
	//private static $fileScheme = "msq/whatever";
	
	/**
	 * @brief Given a signature string, finds and parses the respective INI file.
	 * 
	 * Returns an array of the config file contents.
	 * @param $signature The signature string which will be modified into a firmware/version array.
	 */
	public static function getConfig(&$signature)
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
		$msqMap = parse($iniFile, TRUE);
		
		return $msqMap;
	}
	
	/**
	 * @brief Parse a MS INI file into sections.
	 * 
	 * Based on code from: goulven.ch@gmail.com (php.net comments) http://php.net/manual/en/function.parse-ini-file.php#78815
	 * 
	 * @param $file The path to the INI file that will be loaded and parsed.
	 * @param $something Unused
	 * @returns A huge array of arrays, starting with sections.
	 */
	public static function parse($file, $something)
	{
		if (DEBUG) echo "<div class=\"debug\">Attempting to open: $file</div>";
		try
		{
			$ini = file($file, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
		}
		catch (Exception $e)
		{
			echo "<div class=\"error\">Could not open: $file</div>";
			return null;
		}
		
		if ($ini == FALSE || count($ini) == 0) return null;
		else if (DEBUG) echo "<div class=\"debug\">File opened.</div>";
		
		$globals = array();
		$curve = array();
		$table = array();
		$currentSection = NULL;
		$values = array();
		
		foreach ($ini as $line)
		{
			$line = trim($line);
			if ($line == '' || $line[0] == ';') continue;
			if ($line[0] == '#')
			{//TODO Parse directives, each needs to be a checkbox (combobox?) on the FE
				continue;
			}
			
			//[ at the beginning of the line is the indicator of a new section.
			if ($line[0] == '[') //TODO until before ] not end of line
			{
				$currentSection = substr($line, 1, -1);
				$values[$currentSection] = array();
				if (DEBUG) echo "<div class=\"debug\">Reading section: $currentSection</div>";
				continue;
			}
			
			//We don't handle formulas/composites yet
			if (strpos($line, '{') !== FALSE) continue;
			
			//Pretty much anything left has an equals sign I think.
			//Key-value pair around equals sign
			list($key, $value) = explode('=', $line, 2);
			$key = trim($key);
			
			//Remove any line end comment
			//This could be moved somewhere else, but works fine here I guess.
			$hasComment = strpos($value, ';');
			if ($hasComment !== FALSE) $value = substr($value, 0, $hasComment);
			
			$value = trim($value);
			
			switch ($currentSection)
			{
				case "Constants": //The start of our journey. Fill in details about variables.
					$values[$currentSection][$key] = INI::defaultSectionHandler($value);
					break;
				
				case "SettingContextHelp": //Any help text for our variable
					$values[$currentSection][$key] = INI::helpSectionHandler($value);
					break;
				
				//Whenever I do menu recreation these two will be used
				case "Menu":
					break;
				case "UserDefined":
					break;
				
				case "CurveEditor": //2D Graph //curve = coldAdvance, "Cold Ignition Advance Offset"
					switch ($key)
					{
						case "curve": //start of new curve
							if (!empty($curve))
							{//save the last one, if any
								if (DEBUG) echo '<div class="debug">Parsed curve: ' . $curve['id'] . '</div>';
								//var_export($curve);
								$values[$currentSection][$curve['id']] = $curve;
							}
							
							$value = array_map('trim', explode(',', $value));
							if (count($value) == 2)
							{
								$curve = array();
								$curve['id'] = $value[0];
								$curve['desc'] = trim($value[1], '"');
							}
							else if (DEBUG) echo "<div class=\"warn\">Invalid curve: $key</div>";
							break;
						case "topicHelp":
							if (is_array($curve))
							{
								$curve[$key] = $value;
							}
							break;
						case "columnLabel":
							$value = array_map('trim', explode(',', $value));
							if (count($value) == 2)
							{
								$curve['xLabel'] = $value[0];
								$curve['yLabel'] = $value[1];
							}
							else if (DEBUG) echo "<div class=\"warn\">Invalid curve column label: $key</div>";
							break;
						case "xAxis":
							$value = array_map('trim', explode(',', $value));
							if (count($value) == 3)
							{
								$curve['xMin'] = $value[0];
								$curve['xMax'] = $value[1];
								$curve['xSomething'] = $value[2];
							}
							else if (DEBUG) echo "<div class=\"warn\">Invalid curve X axis: $key</div>";
							break;
						case "yAxis":
							$value = array_map('trim', explode(',', $value));
							if (count($value) == 3)
							{
								$curve['yMin'] = $value[0];
								$curve['yMax'] = $value[1];
								$curve['ySomething'] = $value[2];
							}
							else if (DEBUG) echo "<div class=\"warn\">Invalid curve Y axis: $key</div>";
							break;
						case "xBins":
							$value = array_map('trim', explode(',', $value));
							if (count($value) >= 1)
							{
								$curve['xBinConstant'] = $value[0];
								//$curve['xBinVar'] = $value[1]; //The value read from the ECU
								//Think they all have index 1 except bogus curves
							}
							else if (DEBUG) echo "<div class=\"warn\">Invalid curve X bins: $key</div>";
							break;
						case "yBins":
							$value = array_map('trim', explode(',', $value));
							if (count($value) == 1)
							{
								$curve['yBinConstant'] = $value[0];
							}
							else if (DEBUG) echo "<div class=\"warn\">Invalid curve Y bins: $key</div>";
							break;
						case "gauge": //not all have this
							break;
					}
				break;
				
				case "TableEditor": //3D Table/Graph
					switch ($key)
					{
						case "table": //start of new curve
							if (!empty($table))
							{//save the last one, if any
								if (DEBUG) echo '<div class="debug">Parsed table: ' . $table['id'] . '</div>';
								//var_export($curve);
								$values[$currentSection][$table['id']] = $table;
							}
							
							$value = array_map('trim', explode(',', $value));
							if (count($value) == 4)
							{
								$table = array();
								$table['id'] = $value[0];
								$table['map3d_id'] = $value[1];
								$table['desc'] = trim($value[2], '"');
								//$table['page'] = $value[3]; //Don't care for this one AFAIK.
							}
							else if (DEBUG) echo "<div class=\"warn\">Invalid table: $key</div>";
							break;
						case "topicHelp":
							if (is_array($table))
							{
								$table[$key] = $value;
							}
							break;
						case "xBins":
							$value = array_map('trim', explode(',', $value));
							if (count($value) >= 1)
							{
								$table['xBinConstant'] = $value[0];
								//$table['xBinVar'] = $value[1]; //The value read from the ECU
								//Think they all have index 1 except bogus tables
							}
							else if (DEBUG) echo "<div class=\"warn\">Invalid table X bins: $key</div>";
							break;
						case "yBins":
							$value = array_map('trim', explode(',', $value));
							if (count($value) >= 1)
							{
								$table['yBinConstant'] = $value[0];
							}
							else if (DEBUG) echo "<div class=\"warn\">Invalid table Y bins: $key</div>";
							break;
						case "zBins": //not all have this
							$value = array_map('trim', explode(',', $value));
							if (count($value) >= 1)
							{
								$table['zBinConstant'] = $value[0];
							}
							else if (DEBUG) echo "<div class=\"warn\">Invalid table Z bins: $key</div>";
							break;
					}
				break;
				
				//Don't care about these
				case "MegaTune":
				case "ReferenceTables": //misc MAF stuff
				case "SettingGroups": //misc settings
				case "ConstantsExtensions": //misc reset required fields
				case "PortEditor": //not sure
				case "GaugeConfigurations": //Not relevant
				case "FrontPage": //Not relevant
				case "RunTime": //Not relevant
				case "Tuning": //Not relevant
				case "AccelerationWizard": //Not sure
				case "BurstMode": //Not relevant
				case "OutputChannels": //These are for gauges and datalogging
				case "Datalog": //Not relevant
				default:
					break;
				case NULL:
					//Should be global values (don't think any ini's have them)
					assert($currentSection === NULL);
					$globals[$key] = defaultSectionHandler($value);
					continue; //Skip the section values assignment below
				break;
			}
		}
		
		//var_export($values);
		return $values + $globals;
	}
	
	public static function helpSectionHandler($value)
	{
		return trim($value);
	}
	
	//function constantSectionHandler($value)
	public static function defaultSectionHandler($value)
	{
		//For things like "nCylinders      = bits,    U08,      0,"
		//split CSV into an array
		if (strpos($value, ',') !== FALSE)
			return array_map('trim', explode(',', $value)); //Use trim() as a callback on elements returned from explode()
		else //otherwise just return the value
			return $value;
	}
	
	public static function curveSectionHandler($value)
	{
		return NULL;
	}
}
?>

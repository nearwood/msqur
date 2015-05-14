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
		$sections = array();
		$currentSection = NULL;
		$values = array();
		$sectionNumber = 0;
		
		foreach ($ini as $line)
		{
			$line = trim($line);
			if ($line == '' || $line[0] == ';') continue;
			if ($line[0] == '#')
			{//TODO Parse directives, each needs to be a checkbox (combobox?) on the FE
				continue;
			}
			
			//[ at the beginning of the line is the indicator of a new section.
			if ($line[0] == '[')
			{
				$sections[] = $currentSection = substr($line, 1, -1); //TODO until before ] not end of line
				$sectionNumber++;
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
				$value = INI::defaultSectionHandler($value);
				break;
				
				case "SettingContextHelp": //Any help text for our variable
				$value = INI::defaultSectionHandler($value);
				break;
				
				//Whenever I do menu recreation these two will be used
				case "Menu":
				break;
				case "UserDefined":
				break;
				
				case "CurveEditor": //2D Graph //curve = coldAdvance, "Cold Ignition Advance Offset"
				break;
				case "TableEditor": //3D Table/Graph
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
					assert($sectionNumber == 0);
					$globals[$key] = defaultSectionHandler($value);
					continue; //Skip the section values assignment below
				break;
			}
			
			$values[$sectionNumber - 1][$key] = $value;
		}
		
		for ($j = 0; $j < $sectionNumber; $j++)
		{
			$result[$sections[$j]] = $values[$j];
		}
		return $result + $globals;
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

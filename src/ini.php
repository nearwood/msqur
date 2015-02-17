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
		$i = 0;
		
		foreach ($ini as $line)
		{
			$line = trim($line);
			if ($line == '' || $line[0] == ';') continue;
			if ($line[0] == '#')
			{//TODO Parse directives, each needs to be a checkbox (combobox?) on the FE
				continue;
			}
			
			if ($line[0] == '[')
			{
				$sections[] = $currentSection = substr($line, 1, -1); //TODO until before ] not end of line
				$i++;
				continue;
			}
			
			//We don't handle formulas yet
			if (strpos($line, '{') !== FALSE) continue;
			
			// Key-value pair
			list($key, $value) = explode('=', $line, 2);
			$key = trim($key);
			
			//Remove any line end comment
			$hasComment = strpos($value, ';');
			if ($hasComment !== FALSE)
				$value = substr($value, 0, $hasComment);
				
			$value = trim($value);
			if ($i == 0)
			{// Global values (see section version for comments)
				if (strpos($value, ',') !== FALSE)
					$globals[$key] = array_map('trim', explode(',', $value));
				else $globals[$key] = $value;
			}
			else
			{// Section array values
				if (strpos($value, ',') !== FALSE)
				{
					//Use trim() as a callback on elements returned from explode()
					$values[$i - 1][$key] = array_map('trim', explode(',', $value));
				}
				else $values[$i - 1][$key] = $value;
			}
		}
		
		for ($j = 0; $j < $i; $j++)
		{
			$result[$sections[$j]] = $values[$j];
		}
		return $result + $globals;
	}
}
?>

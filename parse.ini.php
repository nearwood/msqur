<?php

//goulven.ch@gmail.com (php.net comments) http://php.net/manual/en/function.parse-ini-file.php#78815
function parse_ms_ini($file, $something)
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

?>

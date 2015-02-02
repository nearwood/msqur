<?php

$result = parse_ms_ini("ini/ms2/test.ini", TRUE);

var_dump($result);

function parse_ms_ini($file, $something)
{
	$ini = file($file, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
	if (count($ini) == 0) return array();
	
	$globals = array();
	$sections = array();
	$values = array();
	$i = 0;
	
	foreach ($ini as $line)
	{
		$line = trim($line);
		if ($line == '' || $line[0] == ';') continue;
		if ($line[0] == '[')
		{
			$sections[] = substr($line, 1, -1); //TODO until before ] not end of line
			$i++;
			continue;
		}
		// Key-value pair
		list($key, $value) = explode('=', $line, 2);
		$key = trim($key);
		
		//Remove any line end comment
		$hasComment = strpos($value, ';');
		if ($hasComment != FALSE)
			$value = substr($value, 0, $hasComment);
			
		$value = trim($value);
		if ($i == 0)
		{// Array values
			if (substr($line, -1, 2) == '[]') $globals[$key][] = $value;
			else $globals[$key] = $value;
		}
		else
		{// Array values
			if (substr($line, -1, 2) == '[]') $values[$i - 1][$key][] = $value;
			else $values[$i - 1][$key] = $value;
		}
	}
	
	for ($j = 0; $j < $i; $j++)
	{
		$result[$sections[$j]] = $values[$j];
	}
	return $result + $globals;
}


/*
[MegaTune]
   MTversion      = 2.25 ; MegaTune itself; needs to match exec version.

   queryCommand   = "Q" ; B&G embedded code version 2.0/2.98x/3.00
   signature      = 20  ; Versions above return a single byte, 20T.

;-------------------------------------------------------------------------------
 page = 1
   ;  name       = bits,   type, offset, bits
   ;  name       = array,  type, offset, shape, units,     scale, translate,    lo,      hi, digits
   ;  name       = scalar, type, offset,        units,     scale, translate,    lo,      hi, digits
	  veTable    = array,  U08,       0, [8x8], "%",          1.0,      0.0,   0.0,   255.0,      0
	  crankCold  = scalar, U08,      64,         "ms",       0.1,       0.0,   0.0,    25.5,      1
#if CELSIUS
	  egoTemp    = scalar, U08,      86,         "°C",     0.555,       -72,   -40,   102.0,      0
#else
	  egoTemp    = scalar, U08,      86,         "°F",       1.0,       -40,   -40,   215.0,      0
#endif
*/
?>

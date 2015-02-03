<html>
<body>
<?php

//$result = parse_ms_ini("ini/test.ini", TRUE);

//~ print "<pre>";
//~ var_export($result);
//~ print "</pre>";

//goulven.ch@gmail.com (php.net comments) http://php.net/manual/en/function.parse-ini-file.php#78815
function parse_ms_ini($file, $something)
{
	$ini = file($file, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
	if (count($ini) == 0) return array();
	
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
		// Key-value pair
		list($key, $value) = explode('=', $line, 2);
		$key = trim($key);
		
		//Remove any line end comment
		$hasComment = strpos($value, ';');
		if ($hasComment !== FALSE)
			$value = substr($value, 0, $hasComment);
			
		$value = trim($value);
		if ($i == 0)
		{// Global values
			//MS doesn't seem to use this syntax for arrays
			//if (substr($line, -1, 2) == '[]') $globals[$key][] = $value;
			if (strpos($value, ',') !== FALSE)
			{
				//Use trim() as a callback on elements returned from explode()
				$globals[$key] = array_map('trim', explode(',', $value));
			}
			else $globals[$key] = $value;
		}
		else
		{// Section array values
			//MS doesn't seem to use this syntax for arrays
			//if (substr($line, -1, 2) == '[]') $values[$i - 1][$key][] = $value;
			if (strpos($value, ',') !== FALSE)
			{
				$ass = array();
				$temp = array_map('trim', explode(',', $value));
				
				$ass['type'] = $temp[0];
				$ass['datatype'] = $temp[1];
				$ass['offset'] = $temp[2];
				
				//figure out what type of array we have
				switch (count($temp))
				{
					case 4: //bits
						$ass['bits'] = $temp[3];
						break;
						
					case 9: //scalar
						$ass['units']	=	$temp[3];
						$ass['scale']	=	$temp[4];
						$ass['translate'] =	$temp[5];
						$ass['lo']		= 	$temp[6];
						$ass['hi']		=	$temp[7];
						$ass['digits']	=	$temp[8];
						break;
						
					case 10: //array
						$ass['shape']	=	$temp[3];
						$ass['units']	=	$temp[4];
						$ass['scale']	=	$temp[5];
						$ass['translate'] =	$temp[6];
						$ass['lo']		= 	$temp[7];
						$ass['hi']		=	$temp[8];
						$ass['digits']	=	$temp[9];
						break;
						
					default:
						break;
				}
				
				$values[$i - 1][$key] = $ass;
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
</body>
</html>

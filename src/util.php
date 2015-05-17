<?php

/**
 * @brief Check that multiple keys are in an array.
 * @param $array The array to check for $keys
 * @param $keys They keys to check for in the $array
 * @returns TRUE if each key was found in the array, FALSE otherwise.
 */
function array_keys_exist(array &$array, ...$keys)
{
	if (!is_array($array)) return FALSE;
	
	foreach ($keys as $k)
	{
		if (!array_key_exists($k, $array)) return FALSE;
	}
	
	return TRUE;
}

?>

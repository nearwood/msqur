<?php
/* msqur - MegaSquirt .msq file viewer web application
Copyright (C) 2015 Nicholas Earwood nearwood@gmail.com http://nearwood.net

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>. */

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

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

define('DEBUG', FALSE); //Debug output is bad for API

require "msqur.php";

//TODO Check query vars, call appropriate method
echo "API ACCESS GRANTED";


/*
 * @brief Public API
 * 
 * Defines the actions taken at the user level:
 * get firmware versions (for ajax calls)
 * get tune files?
 * get individual tables?
 * 
 * @see http://www.aljtmedia.com/blog/creating-a-php-rest-routing-class-for-your-application/
 */
class API
{
	public function fwList()
	{
		$fwList = $msqur->getFirmwareVersionList('MS2Extra');
		var_export($msqur->getFirmwareVersionList('MS2Extra'));
		foreach ($fwList as $fw)
		{
			echo $fw;
		}
	}
}

?>

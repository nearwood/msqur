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

require "msqur.php";

$method = parseQueryString('method');
$auth = parseQueryString('token') || parseQueryString('auth');
$api = new API($msqur, $auth);
echo $api->call($method);

/*
 * @brief Public API
 * 
 * Defines the actions taken at the user level:
 * get firmware versions (for ajax calls)
 * get tune files?
 * get individual tables?
 * 
 * Returns JSON encoded results of msqur calls
 */
class API
{
	const VERSION = 1;
	private $isAuthenticated = false;
	private $result = "";
	private $msqur = null;
	
	public function __construct($msqur, $authToken)
	{
		$this->msqur = $msqur;
		$this->authenticate($authToken);
	}
	
	public function authenticate($authToken)
	{//TODO Proper auth steps
		$this->isAuthenticated = true;
		return $this->isAuthenticated;
	}
	
	public function call($method)
	{
		$result = array("version" => API::VERSION);
		
		if (!$this->isAuthenticated)
		{
			$result["error"] = "Invalid authentication";
			return json_encode($result);
		}
		
		//I'm sorry
		switch ($method)
		{
			case 'firmwareList':
				$result = array($method => $this->msqur->getFirmwareList());
				break;
			case 'firmwareVersions':
				$fw = parseQueryString('firmware');
				$result = array($method => $this->msqur->getFirmwareVersionList($fw));
				break;
			case 'engineMakes':
				//$a = parseQueryString('firmware'); //TODO inverse code->make is probably not frequent enough to bother
				$result = array($method => $this->msqur->getEngineMakeList());
				//SELECT DISTINCT make FROM `engines` WHERE 1
				break;
			case 'engineCodes':
				$make = parseQueryString('make'); //optional
				$result = array($method => $this->msqur->getEngineCodeList($make));
				//SELECT DISTINCT code FROM `engines` WHERE 1
				break;
			case 'cylinders':
				//SELECT DISTINCT cylinders FROM `engines` WHERE 1
				break;
			case 'displacements':
				//SELECT DISTINCT make FROM `engines` WHERE 1 //TODO Will need to make this into ranges...
				break;
			case 'compressionratios':
				//SELECT DISTINCT make FROM `engines` WHERE 1
				break;
			case 'aspirations':
				//SELECT DISTINCT make FROM `engines` WHERE 1
				break;
			//TODO upload date range?
			default:
				$result["error"] = "Invalid API call";
				break;
		}
		
		return json_encode($result);
	}
}
?>

<?php
/* msqur - MegaSquirt .msq file viewer web application
Copyright 2014-2019 Nicholas Earwood nearwood@gmail.com https://nearwood.dev

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

require_once "config.php";
require "db.php";
require "ini.php";
require "msq.php";

/*
 * @brief Defines the actions taken at the user level.
 * 
 * upload
 * browse
 * view
 * etc.
 */
class Msqur
{
	private $db;
	
	function __construct()
	{
		$this->db = new DB(); //TODO check reuse
	}
	
	public function getMSQ($id)
	{
		//TODO hrm
		return $this->db->getMSQ($id);
	}
	
	public function getMSQForDownload($id)
	{

		return $this->db->getMSQForDownload($id);
	}
	
	public function addMSQs($files, $engineid)
	{
		$fileList = array();
		
		foreach ($files as $file)
		{
			//echo 'Adding ' . $file['tmp_name'];
			//TODO if -1 failed
			$id = $this->db->addMSQ($file, $engineid);
			$fileList[$id] = htmlspecialchars($file['name']);
		}
		
		return $fileList;
	}
	
	public function header() { include "view/header.php"; }
	public function footer() { include "view/footer.php"; }
	
	public function splash()
	{
		$this->header();
		include "view/splash.php";
		$this->footer();
	}
	
	public function browse($bq)
	{
		return $this->db->browse($bq);
	}
	
	public function search($query = "")
	{
		return $this->db->search($query);
	}
	
	/*
	 * @brief Clean out empty strings and fix PDO::fetch() array
	 * @param $a an array of arrays or whatever the hell PDO:fetch(PDO:ASSOC) returns
	 * @returns A 1
	 *
	private function cleanArray($a)
	{
		$ret = array();
		foreach ($a as $l)
		{
			$fw = $l[0-?];
			if (strlen(trim($fw)) != 0) $ret[] = $fw;
		}
		return $ret;
	}*/
	
	private static function parseArray($dbResult, $key)
	{
		$ret = array();
		foreach ($dbResult as $l)
		{
			$a = $l[$key];
			if (strlen(trim($a)) != 0) $ret[] = $a;
		}
		return $ret;
	}
	
	public function getFirmwareList()
	{//TODO Cache
		return MSQUR::parseArray($this->db->getFirmwareList(), 'firmware');
	}
	
	public function getFirmwareVersionList($fw = null)
	{//TODO Cache
		return MSQUR::parseArray($this->db->getFirmwareVersionList($fw), 'signature');
	}
	
	public function getEngineMakeList()
	{
		return MSQUR::parseArray($this->db->getEngineMakeList(), 'make');
	}
	
	public function getEngineCodeList($make = null)
	{
		return MSQUR::parseArray($this->db->getEngineCodeList($make), 'code');
	}
	
	
	/**
	 * get html from md id
	 * if msq xml not cached,
	 * parse xml and update engine
	 * else if cached just return html
	 */
	public function view($id)
	{
		$this->header();
		if (DEBUG) debug('Load MSQ: ' . $id . '');
		//Get cached HTML and display it, or reparse and display (in order)
		$html = $this->getMSQ($id);
		if ($html !== null)
		{
			$this->db->updateViews($id);
			$msq = new MSQ(); //ugh
			
			if ($html == FALSE)
			{
				$engine = array();
				$metadata = array();
				$xml = $this->db->getXML($id);
				if ($xml !== null) {
					$groupedHtml = $msq->parseMSQ($xml, $engine, $metadata);
					$this->db->updateMetadata($id, $metadata);
					$this->db->updateEngine($id, $engine);
					
					$html = "";
					foreach($groupedHtml as $group => $v)
					{
						//TODO Group name as fieldset legend or sth
						$html .= "<div class=\"group-$group\">";
						$html .= $v;
						$html .= '</div>';
					}
					
					$this->db->updateCache($id, $html);
				}
			}
		}
		//TODO else show 404
		
		echo $html;
		$this->footer();
	}
	
	public function addEngine($make, $code, $displacement, $compression, $turbo)
	{
		return $this->db->addEngine($make, $code, $displacement, $compression, $turbo);
	}
}

$msqur = new Msqur();

?>

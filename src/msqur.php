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

require "config.php";
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
		$this->db = new MsqurDB(); //TODO check reuse
	}
	
	public function getMSQ($id)
	{
		//TODO hrm
		return $this->db->getMSQ($id);
	}
	
	public function addMSQs($files, $engineid)
	{
		$fileList = array();
		
		foreach ($files as $file)
		{
			//echo 'Adding ' . $file['tmp_name'];
			//TODO if -1 failed
			$fileList[] = $this->db->addMSQ($file, $engineid);
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
	
	public function browse($page = 0)
	{
		return $this->db->browse($page);
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
	
	public function getFirmwareList()
	{//TODO Cache
		$list = $this->db->getFirmwareList();
		$ret = array();
		foreach ($list as $l)
		{
			$fw = $l['firmware'];
			if (strlen(trim($fw)) != 0) $ret[] = $fw;
		}
		return $ret;
	}
	
	public function getFirmwareVersionList($fw)
	{//TODO Cache
		$list = $this->db->getFirmwareVersionList($fw);
		$ret = array();
		foreach ($list as $l)
		{
			$fw = $l['signature'];
			if (strlen(trim($fw)) != 0) $ret[] = $fw;
		}
		return $ret;
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
		if (DEBUG) echo '<div class="debug">Load MSQ: ' . $id . '</div>';
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

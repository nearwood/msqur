<?php

require "config.php";
require "db.php";
require "ini.php";
require "msq.php";

/*
 * @brief Public API here?
 * 
 * Defines the actions taken at the user level:
 * upload
 * browse
 * view
 * etc.
 * 
 * @see http://www.aljtmedia.com/blog/creating-a-php-rest-routing-class-for-your-application/
 */
class Msqur
{
	private $db;
	
	function __construct()
	{
		echo "Msqur()";
		$this->db = new MsqurDB(); //TODO check reuse
	}
	
	public function getMSQ($id)
	{
		//TODO santize $id
		
	}
	
	public function putMSQ()
	{
		
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
	
	/**
	 * get html from md id
	 * if msq xml not cached,
	 * parse xml and update engine
	 * else if cached just return html
	 */
	public function view($id)
	{
		//Get cached HTML and display it, or reparse and display (in order)
		//$id = $_GET['msq'];
		$msq = $this->getMSQ($id);
		$html = getMSQ($id);
		if ($html == null)
		{
			//$html = array(); //array of strings with group keys
			$engine = array();
			$metadata = array();
			$xml = getXML($id);
			$groupedHtml = parseMSQ($xml, $engine, $metadata);
			updateMetadata($id, $metadata);
			updateEngine($id, $engine);
			
			$html = "";
			foreach($groupedHtml as $group => $v)
			{
				//TODO Group name as fieldset legend or sth
				$html .= "<div class=\"group-$group\">";
				$html .= $v;
				$html .= '</div>';
			}
			
			updateCache($id, $html);
		}
		
		echo $html;
	}
}

$msqur = new Msqur();

?>

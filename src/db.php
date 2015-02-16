<?php
class MsqurDB
{
	private $db;
	
	private function connect()
	{
		if (isset($this->db) && $this->db instanceof PDO)
		{
			if (DEBUG) echo '<div class="debug">Reusing DB connection.</div>';
		}
		else
		{
			try
			{
				if (DEBUG) echo '<div class="debug">Connecting to DB: ' . "mysql:dbname=" . DB_NAME . ";host=" . DB_HOST . "," . DB_USERNAME . ", [****]" . '</div>';
				$this->db = new PDO("mysql:dbname=" . DB_NAME . ";host=" . DB_HOST, DB_USERNAME, DB_PASSWORD);
				//Persistent connection:
				//$db = new PDO("mysql:dbname=" . DB_NAME . ";host=" . DB_HOST, DB_USERNAME, DB_PASSWORD, array(PDO::ATTR_PERSISTENT => true);
			}
			catch (PDOException $e)
			{
				echo '<div class="error">Error connecting to database.</div>';
				$this->dbError($e);
				$this->db = null; //Redundant.
			}
		}
		
		if (DEBUG) echo '<div class="debug">Connecting to DB: ' . (($this->db != null) ? 'Connected.' : 'Connection FAILED') . '</div>';
		return ($this->db != null);
	}
	
	function __construct()
	{
		$this->connect();
	} 
	
	public function addMSQ() {}
	public function addMSQs() {}
	public function addEngine() {}
	
	public function getMSQ() {}
	
	public function browse($page)
	{
		if (!$this->connect()) return null;
		
		try
		{
			$st = $this->db->prepare("SELECT m.id as mid, numCylinders, displacement, compression, induction, firmware, signature, uploadDate FROM metadata m INNER JOIN engines e ON m.engine = e.id");
			if ($st->execute())
			{
				$result = $st->fetchAll(PDO::FETCH_ASSOC);
				return $result;
			}
			//TODO else throw error
		}
		catch (PDOException $e)
		{
			$this->dbError($e);
		}
		
		return null;
	}
	
	private function updateMSQ() {} //Cached HTML
	private function updateEngine() {}
	private function updateMetadata() {}
	
	private function dbError($e)
	{
		if (DEBUG)
		{
			echo '<div class="error">Error executing database query. ';
			echo $e->getMessage();
			echo '</div>';
		}
	}
	
	private function getXML() {}
}

?>

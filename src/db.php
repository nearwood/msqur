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
	
	/**
	 * Get MSQ HTML from metadata $id
	 */
	public function getMSQ($id)
	{
		if (DISABLE_MSQ_CACHE)
		{
			if (DEBUG) echo '<div class="debug">Cache disabled.</div>';
			return null;
		}
		
		if (!$this->connect()) return null;
		
		$html = null;
		
		try
		{
			$st = $this->db->prepare("SELECT html FROM msqs INNER JOIN metadata ON metadata.msq = msqs.id WHERE metadata.id = :id LIMIT 1");
			$st->bindParam(":id", $id);
			$st->execute();
			if ($st->rowCount() > 0)
			{
				$result = $st->fetch(PDO::FETCH_ASSOC);
				$html = $result['html'];
				if ($html === NULL)
				{
					if (DEBUG) echo '<div class="debug">No HTML cache found.</div>';
				}
				else if (DEBUG) echo '<div class="debug">Cached, returning HTML.</div>';
			}
			else
			{
				if (DEBUG) echo '<div class="debug">0 rows for $id</div>';
				echo '<div class="error">Invalid MSQ</div>';
			}
		}
		catch (PDOException $e)
		{
			$this->dbError($e);
		}
		
		return $html;
	}
	
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
	
	public function updateCache() {} //Cached MSQ HTML
	public function updateEngine() {}
	public function updateMetadata() {}
	
	private function dbError($e)
	{
		if (DEBUG)
		{
			echo '<div class="error">Error executing database query. ';
			echo $e->getMessage();
			echo '</div>';
		}
	}
	
	public function getXML($id)
	{
		if (DEBUG) echo '<div class="debug">Getting XML for id: ' . $id . '</div>';
		
		if (!$this->connect()) return null;
		
		$xml = null;
		
		try
		{
			$st = $this->db->prepare("SELECT xml FROM msqs INNER JOIN metadata ON metadata.msq = msqs.id WHERE metadata.id = :id LIMIT 1");
			$st->bindParam(":id", $id);
			if ($st->execute())
			{
				if (DEBUG) echo '<div class="debug">XML Found...</div>';
				$result = $st->fetch(PDO::FETCH_ASSOC);
				$xml = $result['xml'];
			}
			else echo '<div class="error">XML not found.</div>';
		}
		catch (PDOException $e)
		{
			$this->dbError($e);
		}
		
		return $xml;
	}
}

?>

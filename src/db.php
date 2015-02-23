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
				//$this->db = new PDO("mysql:dbname=" . DB_NAME . ";host=" . DB_HOST, DB_USERNAME, DB_PASSWORD, array(PDO::ATTR_PERSISTENT => true);
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
	
	public function addMSQ($file, $engineid)
	{
		if (!$this->connect()) return null;
		
		try
		{
			//TODO Compress?
			$st = $this->db->prepare("INSERT INTO msqs (xml) VALUES (:xml)");
			$xml = file_get_contents($file['tmp_name']);
			//Convert encoding to UTF-8
			$xml = mb_convert_encoding($xml, "UTF-8");
			//Strip out invalid xmlns
			$xml = preg_replace('/xmlns=".*?"/', '', $xml);
			$st->bindParam(":xml", $xml);
			if ($st->execute())
			{
				$id = $this->db->lastInsertId();
				$st = $this->db->prepare("INSERT INTO metadata (url,msq,engine,fileFormat,signature,uploadDate) VALUES (:url, :id, :engine, '4.0', 'unknown', :uploaded)");
				$st->bindParam(":url", $id); //could do hash but for now, just the id
				$st->bindParam(":id", $id);
				if (!is_numeric($engineid)) $engineid = null;
				$st->bindParam(":engine", $engineid);
				//TODO Make sure it's an int
				$dt = new DateTime();
				$dt = $dt->format('Y-m-d H:i:s');
				$st->bindParam(":uploaded", $dt);
				if ($st->execute()) $id = $this->db->lastInsertId();
				else $id = -1;
			}
			else $id = -1;
		}
		catch (PDOException $e)
		{
			$this->dbError($e);
			$id = -1;
		}
		
		return $id;
	}
	
	public function addEngine($displacement, $compression, $turbo)
	{
		$id = null;
		
		if (!is_numeric($displacement) || !is_numeric($compression))
			echo '<div class="error">Invalid engine configuration.</div>';
		else
		{
			if (!$this->connect()) return null;
			
			try
			{
				//TODO use any existing one before creating
				$st = $this->db->prepare("INSERT INTO engines (displacement, compression, induction) VALUES (:displacement, :compression, :induction)");
				$st->bindParam(":displacement", $displacement);
				$st->bindParam(":compression", $compression);
				
				if ($turbo == "na")
					$t = 0;
				else
					$t = 1;
				$st->bindParam(":induction", $t);
				if ($st->execute())
					$id = $this->db->lastInsertId();
			}
			catch (PDOException $e)
			{
				$this->dbError($e);
			}
		}
		
		return $id;
	}
	
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
	
	/**
	* Update HTML cache of MSQ by metadata id
	*/
	public function updateCache($id, $html)
	{
		if (!$this->connect()) return null;
		
		try
		{
			if (DEBUG) echo '<div class="debug">Updating HTML cache...</div>';
			$st = $this->db->prepare("UPDATE msqs ms, metadata m SET ms.html=:html WHERE m.msq = ms.id AND m.id = :id");
			//$xml = mb_convert_encoding($html, "UTF-8");
			$st->bindParam(":id", $id);
			$st->bindParam(":html", $html);
			if ($st->execute())
			{
				if (DEBUG) echo '<div class="debug">Cache updated.</div>';
				return true;
			}
			else
				if (DEBUG) echo '<div class="warn">Unable to update cache.</div>';
		}
		catch (PDOException $e)
		{
			$this->dbError($e);
		}
		
		return false;
	}
	
	public function updateEngine()
	{
		if (!$this->connect()) return null;
		
		try
		{
			if (DEBUG) echo '<div class="debug">Updating engine information...</div>';
			$st = $this->db->prepare("UPDATE engines e, metadata m SET e.numCylinders = :nCylinders, twoStroke = :twoStroke, injType = :injType, nInjectors = :nInjectors, engineType = :engineType WHERE e.id = m.engine AND m.id = :id");
			$st->bindParam(":id", $id);
			$st->bindParam(":nCylinders", $engine['nCylinders']);
			$st->bindParam(":twoStroke", $engine['twoStroke']);
			$st->bindParam(":injType", $engine['injType']);
			$st->bindParam(":nInjectors", $engine['nInjectors']);
			$st->bindParam(":engineType", $engine['engineType']);
			if ($st->execute())
			{
				if (DEBUG) echo '<div class="debug">Engine updated.</div>';
				return true;
			}
			else
				if (DEBUG) echo '<div class="warn">Unable to update engine.</div>';
		}
		catch (PDOException $e)
		{
			$this->dbError($e);
		}
		
		return false;
	}
	
	public function updateMetadata()
	{
		if (!$this->connect()) return null;
		
		try
		{
			if (DEBUG) echo '<div class="debug">Updating HTML cache...</div>';
			$st = $this->db->prepare("UPDATE metadata md SET md.fileFormat = :fileFormat, md.signature = :signature, md.firmware = :firmware, md.author = :author WHERE md.id = :id");
			//$xml = mb_convert_encoding($html, "UTF-8");
			$st->bindParam(":id", $id);
			$st->bindParam(":fileFormat", $metadata['fileFormat']);
			$st->bindParam(":signature", $metadata['signature']);
			$st->bindParam(":firmware", $metadata['firmware']);
			$st->bindParam(":author", $metadata['author']);
			if ($st->execute())
			{
				if (DEBUG) echo '<div class="debug">Metadata updated.</div>';
				return true;
			}
			else
				if (DEBUG) echo '<div class="warn">Unable to update metadata.</div>';
		}
		catch (PDOException $e)
		{
			$this->dbError($e);
		}
		
		return false;
	}
	
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

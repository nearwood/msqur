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
			$this->tryBind($st, ":xml", $xml);
			if ($st->execute())
			{
				$id = $this->db->lastInsertId();
				$st = $this->db->prepare("INSERT INTO metadata (url,msq,engine,fileFormat,signature,uploadDate) VALUES (:url, :id, :engine, '4.0', 'unknown', :uploaded)");
				$this->tryBind($st, ":url", $id); //could do hash but for now, just the id
				$this->tryBind($st, ":id", $id);
				if (!is_numeric($engineid)) $engineid = null;
				$this->tryBind($st, ":engine", $engineid);
				//TODO Make sure it's an int
				$dt = new DateTime();
				$dt = $dt->format('Y-m-d H:i:s');
				$this->tryBind($st, ":uploaded", $dt);
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
	
	/**
	 * @brief Add a new engine to the DB
	 * @param $make String The engine make (Nissan)
	 * @param $code String the engine code (VG30)
	 * @param $displacement decimal in liters
	 * @param $compression decimal The X from X:1 compression ratio
	 * @param $turbo boolean Forced induction
	 * @returns the ID of the new engine record, or null if unsuccessful.
	 */
	public function addEngine($make, $code, $displacement, $compression, $turbo)
	{
		$id = null;
		
		if ($make == NULL) $make = "";
		if ($code == NULL) $code = "";
		
		if (!is_numeric($displacement) || !is_numeric($compression))
			echo '<div class="error">Invalid engine configuration.</div>';
		else
		{
			if (!$this->connect()) return null;
			
			try
			{
				if (DEBUG) echo "<div class=\"debug\">Add engine: \"$make\", \"$code\", $displacement, $compression, $turbo</div>";
				//TODO use any existing one before creating
				$st = $this->db->prepare("INSERT INTO engines (make, code, displacement, compression, induction) VALUES (:make, :code, :displacement, :compression, :induction)");
				
				$this->tryBind($st, ":make", $make);
				$this->tryBind($st, ":code", $code);
				$this->tryBind($st, ":displacement", $displacement);
				$this->tryBind($st, ":compression", $compression);
				
				if ($turbo == "na")
					$t = 0;
				else
					$t = 1;
				$this->tryBind($st, ":induction", $t);
				
				if ($st->execute()) $id = $this->db->lastInsertId();
				else echo "<div class=\"error\">Error adding engine: \"$make\", \"$code\"</div>";
			}
			catch (PDOException $e)
			{
				$this->dbError($e);
			}
		}
		
		if (DEBUG) echo "<div class=\"debug\">Add engine returns: $id</div>";
		return $id;
	}
	
	/**
	 * @brief Whether the reingest flag is set or not for the given id
	 * @param $id The metadata id
	 * @returns TRUE if reingest flag is set to 1, FALSE if 0
	 */
	public function needReingest($id)
	{
		if (!$this->connect()) return FALSE;
		
		try
		{
			$st = $this->db->prepare("SELECT reingest FROM metadata WHERE metadata.id = :id LIMIT 1");
			$this->tryBind($st, ":id", $id);
			$st->execute();
			if ($st->rowCount() > 0)
			{
				$result = $st->fetch(PDO::FETCH_ASSOC);
				$reingest = $result['reingest'];
				return $reingest;
			}
			else
			{
				if (DEBUG) echo "<div class=\"debug\">No result for $id</div>";
				echo '<div class="error">Invalid MSQ</div>';
			}
		}
		catch (PDOException $e)
		{
			$this->dbError($e);
		}
		
		return FALSE;
	}
	
	/**
	 * @brief Get MSQ HTML from metadata $id
	 * @param $id The metadata id
	 * @returns FALSE if not cached, null if not found, otherwise the HTML.
	 */
	public function getMSQ($id)
	{
		if (DISABLE_MSQ_CACHE)
		{
			if (DEBUG) echo '<div class="debug warn">Cache disabled.</div>';
			return FALSE;
		}
		
		if ($this->needReingest($id))
		{
			if (DEBUG) echo '<div class="debug info">Reingest flagged.</div>';
			return FALSE;
		}
		
		if (!$this->connect()) return null;
		
		$html = FALSE;
		
		try
		{
			$st = $this->db->prepare("SELECT html FROM msqs INNER JOIN metadata ON metadata.msq = msqs.id WHERE metadata.id = :id LIMIT 1");
			$this->tryBind($st, ":id", $id);
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
				if (DEBUG) echo "<div class=\"debug\">No result for $id</div>";
				echo '<div class="error">Invalid MSQ</div>';
				return null;
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
			$st = $this->db->prepare("SELECT m.id as mid, make, code, numCylinders, displacement, compression, induction, firmware, signature, uploadDate, views FROM metadata m INNER JOIN engines e ON m.engine = e.id");
			if ($st->execute())
			{
				$result = $st->fetchAll(PDO::FETCH_ASSOC);
				return $result;
			}
			else echo '<div class="error">There was a problem constructing the browse query.</div>';
		}
		catch (PDOException $e)
		{
			$this->dbError($e);
		}
		
		return null;
	}
	
	/**
	 * @brief Update HTML cache of MSQ by metadata id
	 * @param $id integer The ID of the metadata.
	 * @param $html String HTML string of the shit to update.
	 * @returns TRUE or FALSE depending on success.
	 */
	public function updateCache($id, $html)
	{
		if (!$this->connect()) return false;
		
		try
		{
			if (DEBUG) echo '<div class="debug">Updating HTML cache...</div>';
			$st = $this->db->prepare("UPDATE msqs ms, metadata m SET ms.html=:html WHERE m.msq = ms.id AND m.id = :id");
			//$xml = mb_convert_encoding($html, "UTF-8");
			$this->tryBind($st, ":id", $id);
			$this->tryBind($st, ":html", $html);
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
	
	/**
	 * @brief Update engine with extra data.
	 * This is used after parsing a MSQ and getting additional engine information (injector size, number of cylinders, etc.)
	 * @param $id integer The ID of the engine.
	 * @param $engine array The associative array of new engine data.
	 * @returns TRUE or FALSE depending on success.
	 */
	public function updateEngine($id, $engine)
	{
		if (!$this->connect()) return false;
		
		if (!array_keys_exist($engine, 'nCylinders', 'twoStroke', 'injType', 'nInjectors', 'engineType'))
		{
			echo '<div class="warn">Incomplete engine information.</div>';
			return false;
		}
		
		try
		{
			if (DEBUG) echo '<div class="debug">Updating engine information...</div>';
			$st = $this->db->prepare("UPDATE engines e, metadata m SET e.numCylinders = :nCylinders, twoStroke = :twoStroke, injType = :injType, nInjectors = :nInjectors, engineType = :engineType WHERE e.id = m.engine AND m.id = :id");
			$this->tryBind($st, ":id", $id);
			$this->tryBind($st, ":nCylinders", $engine['nCylinders']);
			$this->tryBind($st, ":twoStroke", $engine['twoStroke']);
			$this->tryBind($st, ":injType", $engine['injType']);
			$this->tryBind($st, ":nInjectors", $engine['nInjectors']);
			$this->tryBind($st, ":engineType", $engine['engineType']);
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
	
	/**
	 * @brief Update metadata with extra information.
	 * This is used after parsing a MSQ and getting additional information (firmware version, etc.)
	 * @param $id integer The ID of the metadata.
	 * @param $metadata array The associative array of extra metadata.
	 * @returns TRUE or FALSE depending on success.
	 */
	public function updateMetadata($id, $metadata)
	{
		if (!$this->connect()) return false;
		
		try
		{
			if (DEBUG) echo '<div class="debug">Updating HTML cache...</div>';
			$st = $this->db->prepare("UPDATE metadata md SET md.fileFormat = :fileFormat, md.signature = :signature, md.firmware = :firmware, md.author = :author WHERE md.id = :id");
			//$xml = mb_convert_encoding($html, "UTF-8");
			$this->tryBind($st, ":id", $id);
			$this->tryBind($st, ":fileFormat", $metadata['fileFormat']);
			$this->tryBind($st, ":signature", $metadata['signature']);
			$this->tryBind($st, ":firmware", $metadata['firmware']);
			$this->tryBind($st, ":author", $metadata['author']);
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
	
	/**
	 * @brief Increment the view count of a metadata record.
	 * @param $id integer The ID of the metadata to update.
	 * @returns TRUE or FALSE depending on success.
	 */
	public function updateViews($id)
	{
		if (!$this->connect()) return false;
		
		try
		{
			$st = $this->db->prepare("UPDATE metadata SET views = views + 1 WHERE id = :id LIMIT 1");
			$this->tryBind($st, ":id", $id);
			return $st->execute();
		}
		catch (PDOException $e)
		{
			$this->dbError($e);
		}
		
		return false;
	}
	
	private function bindError($e)
	{
		if (DEBUG)
		{
			echo '<div class="error">Error preparing database query:<br/>';
			echo $e;
			echo '</div>';
		}
		else echo '<div class="error">Error preparing database query.</div>';
	}
	
	private function tryBind($statement, $placeholder, $value)
	{
		//TODO arg check
		if (!$statement->bindParam($placeholder, $value))
		{
			$this->bindError("Error binding: $value to $placeholder");
		}
	}
	
	private function dbError($e)
	{
		if (DEBUG)
		{
			echo '<div class="error">Error executing database query:<br/>';
			echo $e->getMessage();
			echo '</div>';
		}
		else echo '<div class="error">Error executing database query.</div>';
	}
	
	public function getXML($id)
	{
		if (DEBUG) echo '<div class="debug">Getting XML for id: ' . $id . '</div>';
		
		if (!$this->connect()) return null;
		
		$xml = null;
		
		try
		{
			$st = $this->db->prepare("SELECT xml FROM msqs INNER JOIN metadata ON metadata.msq = msqs.id WHERE metadata.id = :id LIMIT 1");
			$this->tryBind($st, ":id", $id);
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

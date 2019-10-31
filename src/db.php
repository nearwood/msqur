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

/**
 * @brief DB handling stuff.
 * 
 */
class DB
{
	private $db;
	
	private function connect()
	{
		if (isset($this->db) && $this->db instanceof PDO)
		{
			//if (DEBUG) debug("Reusing DB connection.");
		}
		else
		{
			try
			{
				//if (DEBUG) debug('Connecting to DB: ' . "mysql:dbname=" . DB_NAME . ";host=" . DB_HOST . "," . DB_USERNAME . ", [****]");
				$this->db = new PDO("mysql:dbname=" . DB_NAME . ";host=" . DB_HOST, DB_USERNAME, DB_PASSWORD);
				//Persistent connection:
				//$this->db = new PDO("mysql:dbname=" . DB_NAME . ";host=" . DB_HOST, DB_USERNAME, DB_PASSWORD, array(PDO::ATTR_PERSISTENT => true);
			}
			catch (PDOException $e)
			{
				error("Could not connect to database");
				echo '<div class="error">Error connecting to database.</div>';
				$this->dbError($e);
				$this->db = null; //Redundant.
			}
		}
		
		//if (DEBUG) debug('Connecting to DB: ' . (($this->db != null) ? 'Connected.' : 'Connection FAILED'));
		return ($this->db != null);
	}
	
	function __construct()
	{
		$this->connect();
	} 
	
	/**
	 * @brief Add a new MSQ to the DB
	 * @param $file The uploaded file
	 * @param $engineid String The ID of the engine metadata
	 * @returns the ID of the new engine record, or null if unsuccessful.
	 */
	public function addMSQ($file, $engineid)
	{
		if (!$this->connect()) return null;
		
		try
		{
			//TODO Compress?

			//TODO transaction so we can rollback (`$db->beginTransaction()`)
			$st = $this->db->prepare("INSERT INTO msqs (xml) VALUES (:xml)");
			$xml = file_get_contents($file['tmp_name']);
			//Convert encoding to UTF-8
			$xml = mb_convert_encoding($xml, "UTF-8");
			//Strip out invalid xmlns
			$xml = preg_replace('/xmlns=".*?"/', '', $xml);
			DB::tryBind($st, ":xml", $xml);
			if ($st->execute())
			{
				$id = $this->db->lastInsertId();
				$st = $this->db->prepare("INSERT INTO metadata (url,msq,engine,fileFormat,signature,uploadDate) VALUES (:url, :id, :engine, '4.0', 'unknown', :uploaded)");
				DB::tryBind($st, ":url", $id); //could do hash but for now, just the id
				DB::tryBind($st, ":id", $id);
				if (!is_numeric($engineid)) $engineid = null;
				DB::tryBind($st, ":engine", $engineid);
				//TODO Make sure it's an int
				$dt = new DateTime();
				$dt = $dt->format('Y-m-d H:i:s');
				DB::tryBind($st, ":uploaded", $dt);
				if ($st->execute()) {
					$id = $this->db->lastInsertId();
				} else {
					error("Error inserting metadata");
					if (DEBUG) {
						print_r($st->errorInfo());
					}
					$id = -1;
				}
				$st->closeCursor();
			} else {
				error("Error inserting XML data");
				if (DEBUG) {
					print_r($st->errorInfo());
				}
				$id = -1;
			}
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
				if (DEBUG) debug("<div class=\"debug\">Add engine: \"$make\", \"$code\", $displacement, $compression, $turbo</div>");
				//TODO use any existing one before creating
				$st = $this->db->prepare("INSERT INTO engines (make, code, displacement, compression, induction) VALUES (:make, :code, :displacement, :compression, :induction)");
				
				DB::tryBind($st, ":make", $make);
				DB::tryBind($st, ":code", $code);
				DB::tryBind($st, ":displacement", $displacement);
				DB::tryBind($st, ":compression", $compression);
				
				if ($turbo == "na")
					$t = 0;
				else
					$t = 1;
				DB::tryBind($st, ":induction", $t);
				
				if ($st->execute()) $id = $this->db->lastInsertId();
				else echo "<div class=\"error\">Error adding engine: \"$make\", \"$code\"</div>";
				$st->closeCursor();
			}
			catch (PDOException $e)
			{
				$this->dbError($e);
			}
		}
		
		if (DEBUG) debug("<div class=\"debug\">Add engine returns: $id</div>");
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
			DB::tryBind($st, ":id", $id);
			$st->execute();
			if ($st->rowCount() > 0)
			{
				$result = $st->fetch(PDO::FETCH_ASSOC);
				$reingest = $result['reingest'];
				$st->closeCursor();
				return $reingest;
			}
			else
			{
				if (DEBUG) debug("<div class=\"debug\">No result for $id</div>");
				echo '<div class="error">Invalid MSQ</div>';
				$st->closeCursor();
			}
		}
		catch (PDOException $e)
		{
			$this->dbError($e);
		}
		
		return FALSE;
	}
	
	/**
	 * @brief Reset regingest flag.
	 * @param $id The metadata id
	 * @returns true if successful, false otherwise
	 */
	public function resetReingest($id)
	{
		if (!$this->connect()) return false;
		
		try
		{
			if (DEBUG) debug('<div class="debug">Updating HTML cache...</div>');
			$st = $this->db->prepare("UPDATE metadata m SET m.reingest=FALSE WHERE m.id = :id");
			DB::tryBind($st, ":id", $id);
			if ($st->execute())
			{
				if (DEBUG) debug('<div class="debug">Reingest reset.</div>');
				$st->closeCursor();
				return true;
			}
			else
				if (DEBUG) debug('<div class="warn">Unable to update cache.</div>');
				
			$st->closeCursor();
		}
		catch (PDOException $e)
		{
			$this->dbError($e);
		}
		
		return false;
	}
	
	/**
	 * @brief Get MSQ HTML from metadata id
	 * @param $id The metadata id
	 * @returns FALSE if not cached, null if not found, otherwise the HTML.
	 */
	public function getMSQ($id)
	{
		if (DISABLE_MSQ_CACHE)
		{
			if (DEBUG) debug('<div class="debug warn">Cache disabled.</div>');
			return FALSE;
		}
		
		if ($this->needReingest($id))
		{
			if (DEBUG) debug('<div class="debug info">Flagged for reingest.</div>');
			$this->resetReingest($id);
			return FALSE;
		}
		
		if (!$this->connect()) return null;
		
		$html = FALSE;
		
		try
		{
			$st = $this->db->prepare("SELECT html FROM msqs INNER JOIN metadata ON metadata.msq = msqs.id WHERE metadata.id = :id LIMIT 1");
			DB::tryBind($st, ":id", $id);
			$st->execute();
			if ($st->rowCount() > 0)
			{
				$result = $st->fetch(PDO::FETCH_ASSOC);
				$st->closeCursor();
				$html = $result['html'];
				if ($html === NULL)
				{
					if (DEBUG) debug('<div class="debug">No HTML cache found.</div>');
					return FALSE;
				}
				else if (DEBUG) debug('<div class="debug">Cached, returning HTML.</div>');
			}
			else
			{
				if (DEBUG) debug("<div class=\"debug\">No result for $id</div>");
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
	
	public function getMSQForDownload($id)
	{

		if (!$this->connect()) return null;
		
		$xml = FALSE;
		
		try
		{
			$st = $this->db->prepare("SELECT xml FROM msqs INNER JOIN metadata ON metadata.msq = msqs.id WHERE metadata.id = :id LIMIT 1");
			DB::tryBind($st, ":id", $id);
			$st->execute();
			if ($st->rowCount() > 0)
			{
				$result = $st->fetch(PDO::FETCH_ASSOC);
				$st->closeCursor();
				$xml = $result['xml'];
				if (DEBUG) debug('<div class="debug">Cached, returning HTML.</div>');
			}
			else
			{
				echo "<div class=\"debug\">No result for $id</div>";
				echo '<div class="error">Invalid MSQ err 2</div>';
				return null;
			}
		}
		catch (PDOException $e)
		{
			$this->dbError($e);
		}
		
		return $xml;
	}
	
	/**
	 * @brief Get a list of MSQs
	 * @param $bq The BrowseQuery to filter results
	 * @returns A list of metadata, or null if unsuccessful
	 */
	public function browse($bq)
	{
		if (!$this->connect()) return null;
		
		try
		{
			$statement = "SELECT m.id as mid, make, code, numCylinders, displacement, compression, induction, firmware, signature, uploadDate, views FROM metadata m INNER JOIN engines e ON m.engine = e.id WHERE ";
			$where = array();
			foreach ($bq as $col => $v)
			{
				//if ($v !== null) $statement .= "$col = :$col ";
				if ($v !== null) $where[] = "$col = :$col ";
			}
			
			if (count($where) === 0) $statement .= "1";
			else
			{
				foreach ($where as $i => $w)
				{
					$statement .= $w;
				}
			}
			
			//echo $statement;
			
			$st = $this->db->prepare($statement);
			
			foreach ($bq as $col => $v)
			{
				if ($v !== null) $this->tryBind($st, ":$col", $v);
			}
			
			if ($st->execute())
			{
				$result = $st->fetchAll(PDO::FETCH_ASSOC);
				$st->closeCursor();
				return $result;
			}
			else echo '<div class="error">There was a problem constructing the browse query: </div>'; //var_export($st->errorInfo())
			
			$st->closeCursor();
		}
		catch (PDOException $e)
		{
			$this->dbError($e);
		}
		
		return null;
	}
	
	/**
	 * @brief Search metadata for any hits against a search query
	 * @param $query The string to search against
	 * @returns A list of matching metadata, or null if unsuccessful
	 */
	public function search($query)
	{
		if (!$this->connect()) return null;
		//tuneComment, uploadDate writeDate author firmware signature e.make e.code e.displacement e.compression e.numCylinders
		//firmware signature e.make e.code e.displacement e.compression e.numCylinders
		try
		{
			$st = $this->db->prepare("SELECT m.id as mid, make, code, numCylinders, displacement, compression, induction, firmware, signature, uploadDate, views FROM metadata m INNER JOIN engines e ON m.engine = e.id WHERE firmware LIKE :query");
			DB::tryBind($st, ":query", "%" . $query . "%"); //TODO exact/wildcard option
			if ($st->execute())
			{
				$result = $st->fetchAll(PDO::FETCH_ASSOC);
				$st->closeCursor();
				return $result;
			}
			else echo '<div class="error">There was a problem constructing the search query.</div>';
		}
		catch (PDOException $e)
		{
			$this->dbError($e);
		}
		
		return null;
	}
	
	/**
	 * @brief Get all unique firmware names listed in DB
	 * @returns List of strings
	 */
	public function getFirmwareList()
	{
		if (!$this->connect()) return null;
			
		try
		{
			if (DEBUG) debug("<div class=\"debug\">Getting firmware list...</div>");
			$st = $this->db->prepare("SELECT DISTINCT firmware FROM `metadata`");
			
			if ($st->execute())
			{
				$ret = $st->fetchAll(PDO::FETCH_ASSOC);
				$st->closeCursor();
				return $ret;
			}
			else echo "<div class=\"error\">Error getting firmware list</div>";
			
			$st->closeCursor();
		}
		catch (PDOException $e)
		{
			$this->dbError($e);
		}
	}
	
	/**
	 * @brief Get unique firmware versions listed in DB
	 * @param $firmware name of firmware to limit versions to
	 * @returns List of strings
	 */
	public function getFirmwareVersionList($firmware = null)
	{
		if (!$this->connect()) return null;
		
		try
		{
			if (DEBUG) debug("<div class=\"debug\">Getting firmware version list...</div>");
			if ($firmware == null)
			{
				$st = $this->db->prepare("SELECT DISTINCT signature FROM `metadata`");
			}
			else
			{
				$st = $this->db->prepare("SELECT DISTINCT signature FROM `metadata` WHERE firmware = :fw");
				DB::tryBind($st, ":fw", $firmware);
			}
			
			if ($st->execute())
			{
				$ret = $st->fetchAll(PDO::FETCH_ASSOC);
				$st->closeCursor();
				return $ret;
			}
			else echo "<div class=\"error\">Error getting firmware version list for: $firmware</div>";
			
			$st->closeCursor();
		}
		catch (PDOException $e)
		{
			$this->dbError($e);
		}
	}
	
	public function getEngineMakeList()
	{
		if (!$this->connect()) return null;
			
		try
		{
			if (DEBUG) debug("<div class=\"debug\">Getting engine make list...</div>");
			$st = $this->db->prepare("SELECT DISTINCT make FROM `engines`");
			
			if ($st->execute())
			{
				$ret = $st->fetchAll(PDO::FETCH_ASSOC);
				$st->closeCursor();
				return $ret;
			}
			else echo "<div class=\"error\">Error getting engine make list</div>";
			
			$st->closeCursor();
		}
		catch (PDOException $e)
		{
			$this->dbError($e);
		}
	}
	
	public function getEngineCodeList($make = null)
	{
		if (!$this->connect()) return null;
			
		try
		{
			if (DEBUG) debug("<div class=\"debug\">Getting engine code list...</div>");
			
			if ($make !== null && gettype($make) == "string")
			{
				$st = $this->db->prepare("SELECT DISTINCT code FROM `engines` WHERE make = :make");
				DB::tryBind($st, ":make", $make);
			}
			else
			{
				$st = $this->db->prepare("SELECT DISTINCT code FROM `engines`");
			}
			
			if ($st->execute())
			{
				$ret = $st->fetchAll(PDO::FETCH_ASSOC);
				$st->closeCursor();
				return $ret;
			}
			else echo "<div class=\"error\">Error getting engine code list</div>";
			
			$st->closeCursor();
		}
		catch (PDOException $e)
		{
			$this->dbError($e);
		}
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
			if (DEBUG) debug('<div class="debug">Updating HTML cache...</div>');
			$st = $this->db->prepare("UPDATE msqs ms, metadata m SET ms.html=:html WHERE m.msq = ms.id AND m.id = :id");
			//$xml = mb_convert_encoding($html, "UTF-8");
			DB::tryBind($st, ":id", $id);
			DB::tryBind($st, ":html", $html);
			if ($st->execute())
			{
				if (DEBUG) debug('<div class="debug">Cache updated.</div>');
				$st->closeCursor();
				return true;
			}
			else
				if (DEBUG) debug('<div class="warn">Unable to update cache.</div>');
				
			$st->closeCursor();
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
		
		if (!array_keys_exist($engine, 'nCylinders', 'engineType', 'twoStroke', 'nInjectors'))
		{//Some MSQs seem to be missing the injType
			echo '<div class="warn">Incomplete engine information. Unable to update engine metadata.</div>';
			//var_export($engine);
			return false;
		}
		
		try
		{
			if (DEBUG) debug('<div class="debug">Updating engine information...</div>');
			$st = $this->db->prepare("UPDATE engines e, metadata m SET e.numCylinders = :nCylinders, twoStroke = :twoStroke, injType = :injType, nInjectors = :nInjectors, engineType = :engineType WHERE e.id = m.engine AND m.id = :id");
			DB::tryBind($st, ":id", $id);
			DB::tryBind($st, ":nCylinders", $engine['nCylinders']);
			DB::tryBind($st, ":twoStroke", $engine['twoStroke']);
			
			if (array_key_exists('injType', $engine))
				DB::tryBind($st, ":injType", $engine['injType']);
			else
				DB::tryBind($st, ":injType", "Port Injection");
			
			DB::tryBind($st, ":nInjectors", $engine['nInjectors']);
			DB::tryBind($st, ":engineType", $engine['engineType']);
			if ($st->execute())
			{
				if (DEBUG) debug('<div class="debug">Engine updated.</div>');
				$st->closeCursor();
				return true;
			}
			else
				if (DEBUG) debug('<div class="warn">Unable to update engine metadata.</div>');
				
			$st->closeCursor();
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
		
		if (!array_keys_exist($metadata, 'fileFormat', 'signature', 'firmware', 'author'))
		{
			if (DEBUG) debug('Invalid MSQ metadata: ' . $metadata);
			echo '<div class="warn">Incomplete MSQ metadata.</div>';
			return false;
		}
		
		try
		{
			if (DEBUG) debug('<div class="debug">Updating HTML cache...</div>');
			$st = $this->db->prepare("UPDATE metadata md SET md.fileFormat = :fileFormat, md.signature = :signature, md.firmware = :firmware, md.author = :author WHERE md.id = :id");
			//$xml = mb_convert_encoding($html, "UTF-8");
			DB::tryBind($st, ":id", $id);
			DB::tryBind($st, ":fileFormat", $metadata['fileFormat']);
			DB::tryBind($st, ":signature", $metadata['signature']);
			DB::tryBind($st, ":firmware", $metadata['firmware']);
			DB::tryBind($st, ":author", $metadata['author']);
			if ($st->execute())
			{
				if (DEBUG) debug('<div class="debug">Metadata updated.</div>');
				$st->closeCursor();
				return true;
			}
			else
				if (DEBUG) debug('<div class="warn">Unable to update metadata.</div>');
				
			$st->closeCursor();
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
			DB::tryBind($st, ":id", $id);
			$ret = $st->execute();
			$st->closeCursor();
			return $ret;
		}
		catch (PDOException $e)
		{
			$this->dbError($e);
		}
		
		return false;
	}
	
	private static function bindError($e)
	{
		if (DEBUG)
		{
			echo '<div class="error">Error preparing database query:<br/>';
			echo $e;
			echo '</div>';
		}
		else echo '<div class="error">Error preparing database query.</div>';
	}
	
	private static function tryBind($statement, $placeholder, $value)
	{
		//TODO arg check
		if (!$statement->bindParam($placeholder, $value))
		{
			DB::bindError("Error binding: $value to $placeholder");
		}
	}
	
	private function dbError($e)
	{
		if (DEBUG)
		{
			error("DB Error: " . $e->getMessage());
			echo '<div class="error">Error executing database query:<br/>';
			echo $e->getMessage();
			echo '</div>';
		}
		else echo '<div class="error">Error executing database query.</div>';
	}
	
	/**
	 * @brief Get the raw XML of a MSQ
	 * @param $id The ID of the associated metadata
	 * @returns XML String or null if unsuccessful
	 */
	public function getXML($id)
	{
		if (DEBUG) debug('Getting XML for id: ' . $id);
		
		if (!$this->connect()) return null;
		
		$xml = null;
		
		try
		{
			$st = $this->db->prepare("SELECT xml FROM msqs INNER JOIN metadata ON metadata.msq = msqs.id WHERE metadata.id = :id LIMIT 1");
			DB::tryBind($st, ":id", $id);
			if ($st->execute() && $st->rowCount() === 1)
			{
				if (DEBUG) debug('XML Found.');
				$result = $st->fetch(PDO::FETCH_ASSOC);
				$st->closeCursor();
				$xml = $result['xml'];
			} else {
				//TODO Send real 404
				echo '<div class="error">404 MSQ not found.</div>';
			}
		}
		catch (PDOException $e)
		{
			$this->dbError($e);
		}
		
		return $xml;
	}
}

?>

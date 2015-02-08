<?php
require "config.php";

function connect()
{
	//TODO Reuse connection
	
	$db = null;
	try
	{
		if (DEBUG) echo '<div class="debug">' . "mysql:dbname=" . DB_NAME . ";host=" . DB_HOST . "," . DB_USERNAME . ", [****]" . '</div>';
		$db = new PDO("mysql:dbname=" . DB_NAME . ";host=" . DB_HOST, DB_USERNAME, DB_PASSWORD);
	}
	catch (PDOException $e)
	{
		echo '<div class="error">Error connecting to database.</div>';
		dbError($e);
		$db = null; //Redundant.
	}
	
	return $db;
}

/**
 * Add engine details not available from MSQ
 * 
 */
function addEngine($displacement, $compression, $turbo)
{
	$id = null;
	
	if (!is_numeric($displacement) || !is_numeric($compression))
		echo '<div class="error">Invalid engine configuration.</div>';
	else
	{
		$db = connect();
		if ($db == null) return null;
		
		try
		{
			//TODO use any existing one before creating
			$st = $db->prepare("INSERT INTO engines (displacement, compression, induction) VALUES (:displacement, :compression, :induction)");
			$st->bindParam(":displacement", $displacement);
			$st->bindParam(":compression", $compression);
			
			if ($turbo == "na")
				$t = 0;
			else
				$t = 1;
			$st->bindParam(":induction", $t);
			if ($st->execute())
				$id = $db->lastInsertId();
		}
		catch(PDOException $e)
		{
			echo '<div class="error">Error adding to the database.</div>'; echo $e->getMessage();
		}
	}
	
	return $id;
}

function addFiles($files, $engineid)
{
	$db = connect();
	if ($db == null) return null;
	
	$fileList = array();
	
	foreach ($files as $file)
	{
		//echo 'Adding ' . $file['tmp_name'];
		//TODO if -1 failed
		$fileList[] = addFile($file, $engineid);
	}
	
	return $fileList;
}

function addFile($file, $engineid, $db = null)
{
	if ($db == null)
	{
		$db = connect();
		if ($db == null) return null;
	}
	
	try
	{
		//TODO Compress?
		$st = $db->prepare("INSERT INTO msqs (xml) VALUES (:xml)");
		$xml = file_get_contents($file['tmp_name']);
		//Convert encoding to UTF-8
		$xml = mb_convert_encoding($xml, "UTF-8");
		//Strip out invalid xmlns
		$xml = preg_replace('/xmlns=".*?"/', '', $xml);
		$st->bindParam(":xml", $xml);
		if ($st->execute())
		{
			$id = $db->lastInsertId();
			$st = $db->prepare("INSERT INTO metadata (url,msq,engine,fileFormat,signature,uploadDate) VALUES (:url, :id, :engine, '4.0', 'unknown', :uploaded)");
			$st->bindParam(":url", $id); //could do hash but for now, just the id
			$st->bindParam(":id", $id);
			if (!is_numeric($engineid)) $engineid = null;
			$st->bindParam(":engine", $engineid);
			//TODO Make sure it's an int
			$dt = new DateTime();
			$dt = $dt->format('Y-m-d H:i:s');
			$st->bindParam(":uploaded", $dt);
			if ($st->execute()) $id = $db->lastInsertId();
			else $id = -1;
		}
		else $id = -1;
	}
	catch (PDOException $e)
	{
		dbError($e);
		$id = -1;
	}
	
	return $id;
}

function updateMetadata($id, $metadata)
{
	$db = connect();
	if ($db == null) return false;
	
	try
	{
		if (DEBUG) echo '<div class="debug">Updating HTML cache...</div>';
		$st = $db->prepare("UPDATE metadata md SET md.fileFormat = :fileFormat, md.signature = :signature, md.firmware = :firmware, md.author = :author WHERE md.id = :id");
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
		dbError($e);
	}
	
	return false;
}

function updateEngine($id, $engine)
{
	$db = connect();
	if ($db == null) return false;
	
	try
	{
		if (DEBUG) echo '<div class="debug">Updating engine information...</div>';
		$st = $db->prepare("UPDATE engines e, metadata m SET e.numCylinders = :nCylinders, twoStroke = :twoStroke, injType = :injType, nInjectors = :nInjectors, engineType = :engineType WHERE e.id = m.engine AND m.id = :id");
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
		dbError($e);
	}
	
	return false;
}

/**
 * Update HTML cache of MSQ by metadata id
 */
function updateCache($id, $html)
{
	$db = connect();
	if ($db == null) return false;
	
	try
	{
		if (DEBUG) echo '<div class="debug">Updating HTML cache...</div>';
		$st = $db->prepare("UPDATE msqs ms, metadata m SET ms.html=:html WHERE m.msq = ms.id AND m.id = :id");
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
		dbError($e);
	}
	
	return false;
}

function getXML($id)
{
	$db = connect();
	if ($db == null) return null;
	
	$xml = null;
	
	try
	{
		if (DEBUG) echo '<div class="debug">Getting XML for id: ' . $id . '</div>';
		$st = $db->prepare("SELECT xml FROM msqs INNER JOIN metadata ON metadata.msq = msqs.id WHERE metadata.id = :id LIMIT 1");
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
		dbError($e);
	}
	
	return $xml;
}

/**
 * Get MSQ HTML from metadata $id
 */
function getMSQ($id)
{
	if (DISABLE_MSQ_CACHE)
	{
		if (DEBUG) echo '<div class="debug">Cache disabled.</div>';
		return null;
	}
	
	$db = connect();
	if ($db == null) return null;
	
	$html = null;
	
	try
	{
		$st = $db->prepare("SELECT html FROM msqs INNER JOIN metadata ON metadata.msq = msqs.id WHERE metadata.id = :id LIMIT 1");
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
		dbError($e);
	}
	
	return $html;
}

//TODO Rename?
//TODO Pagination
function getAll()
{
	$db = connect();
	if ($db == null) return null;
	
	try
	{
		$st = $db->prepare("SELECT m.id as mid, numCylinders, displacement, compression, induction, firmware, signature, uploadDate FROM metadata m INNER JOIN engines e ON m.engine = e.id");
		if ($st->execute())
		{
			$result = $st->fetchAll(PDO::FETCH_ASSOC);
			return $result;
		}
	}
	catch (PDOException $e)
	{
		dbError($e);
	}
	
	return null;
}

function dbError($e)
{
	if (DEBUG)
	{
		echo '<div class="error">Error executing database query. ';
		echo $e->getMessage();
		echo '</div>';
	}
}

?>

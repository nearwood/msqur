<?php
require "config.php";

function connect()
{
	$db = null;
	try
	{
		//echo "mysql:dbname=" . DB_NAME . ";host=" . DB_HOST . "," . DB_USERNAME . "," . DB_PASSWORD;
		$db = new PDO("mysql:dbname=" . DB_NAME . ";host=" . DB_HOST, DB_USERNAME, DB_PASSWORD);
	}
	catch(PDOException $e)
	{
		echo '<div class="error">Error connecting to database.</div>';// echo $e->getMessage();
		$db = null; //Redundant.
	}
	
	return $db;
}

function addEngine($displacement, $cylinders, $compression, $turbo)
{
	if (!is_numeric($displacement) || !is_numeric($cylinders) || !is_numeric($compression))
		echo '<div class="error">Invalid engine configuration.</div>';
	else
	{
		$db = connect();
		if ($db == null) return null;
		
		try
		{
			//TODO use any existing one before creating
			$st = $db->prepare("INSERT INTO engines (displacement, numCylinders, compression, induction) VALUES (:displacement, :cylinders, :compression, :induction)");
			$st->bindParam(":displacement", $displacement);
			$st->bindParam(":cylinders", $cylinders);
			$st->bindParam(":compression", $compression);
			
			if ($turbo == "na")
				$t = 0;
			else
				$t = 1;
			$st->bindParam(":induction", $t);
			$st->execute();
			$id = $db->lastInsertId();
		}
		catch(PDOException $e)
		{
			echo '<div class="error">Error adding to the database.</div>'; echo $e->getMessage();
		}
		
		return $id;
	}
	
	return null;
}

function addFiles($files, $engineid)
{
	$db = connect();
	if ($db == null) return null;
	
	$fileList = array();
	
	foreach ($files as $file)
	{
		//echo 'Adding ' . $file['tmp_name'];
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
		$st->execute();
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
		$st->execute();
		$id = $db->lastInsertId();
	}
	catch(PDOException $e)
	{
		dbError($e);
	}
	
	return $id;
}

function getMSQ($id)
{
	$db = connect();
	if ($db == null) return null;
	
	try
	{
		$st = $db->prepare("SELECT msqs.xml FROM msqs INNER JOIN metadata ON metadata.msq = msqs.id WHERE metadata.id = :id");
		$st->bindParam(":id", $id);
		$st->execute();
		$result = $st->fetch(PDO::FETCH_ASSOC);
	}
	catch(PDOException $e)
	{
		dbError($e);
	}
	
	if (!$result) return null;
	else return $result['xml'];
}

//TODO Rename?
//TODO Pagination
function getAll()
{
	$db = connect();
	if ($db == null) return null;
	
	try
	{
		$st = $db->prepare("SELECT * FROM metadata INNER JOIN engines ON metadata.engine = engines.id");
		$st->execute();
		$result = $st->fetchAll(PDO::FETCH_ASSOC);
	}
	catch(PDOException $e)
	{
		dbError($e);
	}
	
	if (!$result) return null;
	else return $result;
}

function dbError($e)
{
	echo '<div class="error">Error executing database query.';
	echo $e->getMessage();
	echo '</div>';
}

?>

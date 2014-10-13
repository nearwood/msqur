<?php
function connect()
{
	$db = null;
	try
	{
		$db = new PDO("mysql:dbname=msqur;host=localhost", "msqur", "LwEYrxvUpjhnCdTc" );
		//echo "Connected";
	}
	catch(PDOException $e)
	{
		echo '<div id="dbError">Error connecting to database.</div>'; //$e->getMessage();
	}
	
	return $db;
}

function addFile($db, $file)
{
	try
	{
		//TODO Compress?
		$st = $db->prepare("INSERT INTO msqs (xml) VALUES (:xml)");
		$f = file_get_contents($file['tmp_name']);
		$st->bindParam(":xml", $f);
		$st->execute();
		$id = $db->lastInsertId();
		$st = $db->prepare("INSERT INTO metadata (url,msq,fileFormat,signature) VALUES (:url, :id, '4.0', 'unknown')");
		$st->bindParam(":url", $id); //could do hash but for now, just the id
		$st->bindParam(":id", $id);
		$st->execute();
		$id = $db->lastInsertId();
	}
	catch(PDOException $e)
	{
		echo '<div class="error">Error adding to the database.</div>'; echo $e->getMessage();
	}
	
	return $id;
}

function getMSQ($db, $id)
{
	try
	{
		$st = $db->prepare("SELECT msqs.xml FROM msqs INNER JOIN metadata ON metadata.msq = msqs.id WHERE metadata.id = :id");
		$st->bindParam(":id", $id);
		$st->execute();
		$result = $st->fetch(PDO::FETCH_ASSOC);
	}
	catch(PDOException $e)
	{
		echo '<div class="error">Error adding to the database.</div>'; echo $e->getMessage();
	}
	
	if (!$result) return null;
	else return $result['xml'];
}
?>
<?php
require "msqur.php";

/**
 * @brief Restructure file upload array
 *
 * Extended description goes here.
 * @param $file_post array
 */ 
function fixFileArray(&$file_post)
{//From php.net anonymous comment
	$file_ary = array();
	$file_count = count($file_post['name']);
	$file_keys = array_keys($file_post);
	
	for ($i=0; $i<$file_count; $i++)
	{
		foreach ($file_keys as $key)
		{
			$file_ary[$i][$key] = $file_post[$key][$i];
		}
	}
	
	return $file_ary;
}

/**
 * @brief Sanity check for uploaded files.
 * @param $files array
 * @returns $files array with bad apples removed.
 */
function checkUploads($files)
{//Expects fixed array instead of $_FILES array
	foreach ($files as $index => $file)
	{
		//Discard any with errors
		if ($file['error'] != UPLOAD_ERR_OK)
		{
			unset($files[$index]);
			continue;
		}
		
		//Check sizes against 1MiB
		if ($file['size'] > 1048576)
		{
			unset($files[$index]);
			continue;
		}
		
		//Get and check mime types (ignoring provided ones)
		$finfo = new finfo(FILEINFO_MIME_TYPE);
		if ($finfo->file($file['tmp_name']) != "application/xml")
		{
			unset($files[$index]);
			continue;
		}
	}
	
	return $files;
}

if (isset($_POST['upload']) && isset($_FILES))
{
	$msqur->header();
	//var_dump($_POST);
	//var_dump($_FILES);
	
	$files = checkUploads(fixFileArray($_FILES['files']));
	if (count($files) == 0)
	{
		//No files made it past the check
		echo '<div class="error">Your file(s) have asploded.</div>';
	}
	else
	{
		if (count($files) == 1)
			echo '<div class="info">' . count($files) . ' file was uploaded.</div>';
		else
			echo '<div class="info">' . count($files) . ' files were uploaded.</div>';
		//$motor = $validate($_POST['cylinders'])
		
		if (DEBUG) echo '<div class="debug">Adding engine: ' . $_POST['make'] . ', ' . $_POST['code'] . ', ' . $_POST['displacement'] . ', ' . $_POST['compression'] . ', ' . $_POST['aspiration'] . '</div>';
		
		$engineid = $msqur->addEngine($_POST['make'], $_POST['code'], $_POST['displacement'], $_POST['compression'], $_POST['aspiration']);
		$fileList = $msqur->addMSQs($files, $engineid);
		
		if ($fileList != null)
		{
			echo '<div class="info">Upload successful.</div>';
			echo '<div class="info"><ul id="fileList">';
			foreach ($fileList as $f)
			{
				echo '<li><a href="view.php?msq=' . $f . '">' . $f . '</a></li>';
			}
			echo '</div></ul>';
		}
		else
		{
			echo '<div class="error">Unable to store uploaded file.</div>';
		}
	}
	
	$msqur->footer();
}
else include "index.php";
?>

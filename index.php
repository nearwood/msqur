<?php
/**
*@mainpage
* msqur.com MSQ File Viewer
*/ 

//TODO if debug
error_reporting(E_ALL);
ini_set('display_errors', true);
ini_set('html_errors', false);

require('db.php');
require('msq.php');

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

require('header.php');
?>
<div id='content'>
<?php
if (isset($_GET['msq'])) {
	/**
	 * get html from md id
	 * if msq xml not cached,
	 * parse xml and update engine
	 * else if cached just return html
	 */
	
	//Get cached HTML and display it, or reparse and display (in order)
	$id = $_GET['msq'];
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
	
	
} else if (isset($_POST['upload']) && isset($_FILES)) {
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
		$engineid = addEngine($_POST['displacement'], $_POST['compression'], $_POST['aspiration']);
		$fileList = addFiles($files, $engineid);
		
		if ($fileList != null)
		{
			echo '<div class="info">Upload successful.</div>';
			echo '<div class="info"><ul id="fileList">';
			foreach ($fileList as $f)
			{
				echo '<li><a href="' . $_SERVER['REQUEST_URI'] . '?msq=' . $f . '">' . $f . '</a></li>';
			}
			echo '</div></ul>';
		}
		else
		{
			echo '<div class="error">Unable to store uploaded file.</div>';
		}
	}
}
else
{
	echo '<div class="info">Upload your .msq files to view and share them.</div>';
	echo '<div class="warn">This website is in beta. It only officially supports TunerStudio tune files, and currently is known working with MS2 and MS2-Extra firmware.</div>';
}
?>
</div>
<?php require('footer.php'); ?>

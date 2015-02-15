<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>MSQur</title>
	<meta name="description" content="Megasquirt tune file sharing site">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="view/msqur.css" />
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
	<link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.1/themes/smoothness/jquery-ui.css" />
	<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.1/jquery-ui.min.js"></script>
	<script src="view/lib/tablesorter/jquery.tablesorter.min.js"></script>
	<script src="view/msqur.js"></script>
</head>
<body>
<div id="navigation"><span><button id="btnUpload">Upload</button></span><span><a href="browse.php">Browse</a></span><span><a>Search</a></span><span><a>Stats</a></span><span id="aboutLink">About</span></div>
<div id="upload" style="display:none;">
	<form action="upload.php" method="post" enctype="multipart/form-data">
		<div id="fileDropZone">Drop files here
			<input type="file" id="fileSelect" accept=".msq" name="files[]" multiple />
		</div>
		<output id="fileList"></output>
		<div id="engineForm">
			<fieldset>
			<legend>Engine Information</legend>
			<div>Engine Make/Manufacturer: <input name="make" type="text" placeholder="e.g. GM" maxlength="32" style="width:4em;"/></div>
			<div>Engine Code: <input name="code" type="text" placeholder="LS3" maxlength="32" style="width:4em;"/></div>
			<div>Displacement (liters): <input name="displacement" type="number" min="0" step="0.01" value="3.0" style="width:4em;"/></div>
			<div>Compression (X:1) <input name="compression" type="number" min="0" step="0.1" value="9.0" style="width:4em;"/></div>
			<div>Aspiration: 
				<select name="aspiration">
					<option value="na" title="AKA: Slow">Naturally Aspirated</option>
					<option value="fi" title="The way God intended">Forced Induction</option>
				</select>
			</div>
			</fieldset>
		</div>
		<input type="hidden" name="upload" value="upload" style="display:none;">
	</form>
</div>
<div id="settings">
	<img id="settingsIcon" src="img/settings3.png"/>
	<div id="settingsPanel" style="display:none;">
		<label><input id="colorizeData" type="checkbox" />Colorize</label>
		<label><input id="normalizeData" type="checkbox" title="Recalculate VE table values to a 5-250 unit scale"/>Normalize Data</label>
		<label><input id="normalizeAxis" type="checkbox" disabled />Normalize Axis</label>
	</div>
</div>

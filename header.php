<!DOCTYPE html>
<html>
<head>
	<title>MSQur</title>
	<meta name="description" content="Megasquirt tune file sharing site">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="msqur.css" />
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
	<link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.1/themes/smoothness/jquery-ui.css" />
	<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.1/jquery-ui.min.js"></script>
	<script src="lib/tablesorter/jquery.tablesorter.min.js"></script>
	<script src="msqur.js"></script>
</head>
<body>
<div id="navigation"><span><button id="btnUpload">Upload</button></span><span><a href="browse.php">Browse</a></span><span><a href="search.php">Search</a></span><span><a href="stats.php">Stats</a></span><span id="aboutLink">About</span></div>
<div id="upload" style="display:none;">
	<form action="index.php" method="post" enctype="multipart/form-data">
		<div id="fileDropZone">Drop files here
			<input type="file" id="fileSelect" name="files[]" multiple />
		</div>
		<output id="fileList"></output>
		<input type="hidden" name="upload" value="upload" style="display:none;">
	</form>
</div>
<div id="settings">
	<img id="settingsIcon" src="img/settings3.png"/>
	<div id="settingsPanel" style="display:none;">
		<label><input type="checkbox" checked />Colorize</label>
		<label><input type="checkbox" checked />Normalize Axis</label>
		<label><input type="checkbox" disabled />Normalize Data</label>
	</div>
</div>

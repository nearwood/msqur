<!DOCTYPE html>
<html>
<head>
    <title>TuneShare</title>
	<meta name="description" content="">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="msqur.css" />
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
	<link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.1/themes/smoothness/jquery-ui.css" />
	<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.1/jquery-ui.min.js"></script>
	<script src="msqur.js"></script>
</head>
<body>
<div id="navigation"><span><button id="btnUpload">Upload</button></span><span>Stats</span><span>About</span></div>
<span>
	<div id="upload" style="display:none;">
		<form action="index.php" method="post" enctype="multipart/form-data">
			<div id="fileDropZone">Drop files here
				<input type="file" id="fileSelect" name="files[]" multiple />
			</div>
			<output id="fileList"></output>
			<input type="hidden" name="upload" value="upload" style="display:none;">
		</form>
</span>
</div>

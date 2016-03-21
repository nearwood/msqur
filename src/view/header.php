<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" ng-app="msqur">
<head>
	<title>MSQur</title>
	<meta name="description" content="Megasquirt tune file sharing site">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="view/msqur.css" />
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
	<link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.1/themes/smoothness/jquery-ui.css" />
	<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.1/jquery-ui.min.js"></script>
	<script src="//ajax.googleapis.com/ajax/libs/angularjs/1.5.2/angular.min.js"></script>
	<script src="view/lib/tablesorter/jquery.tablesorter.min.js"></script>
	<script src="view/lib/Chart.js/Chart.min.js"></script>
	<script src="view/msqur.js"></script>
</head>
<body>
<div id="navigation"><span><button id="btnUpload">Upload</button></span><span><a href="browse.php">Browse</a></span><span><a href="search.php">Search</a></span><span><a>Stats</a></span><span><a href="about.php">About</a></span></div>
<div id="upload" style="display:none;">
	<form id="engineForm" action="upload.php" method="post" enctype="multipart/form-data">
		<div id="fileDropZone"><label for="fileSelect">Drop files here</label>
			<input required type="file" id="fileSelect" accept=".msq" name="files[]" multiple />
		</div>
		<output id="fileList"></output>
		<div>
			<fieldset>
				<legend>Engine Information</legend>
				<div><span>All fields are required. Please enter accurate information to help other users.</span></div>
				<div class="formDiv">
					<label for="make">Engine Make:</label>
					<input required name="make" type="text" placeholder="e.g. GM" maxlength="32" style="width:4em;"/>
				</div>
				<div class="formDiv">
					<label for="code">Engine Code:</label>
					<input required name="code" type="text" placeholder="LS3" maxlength="32" style="width:4em;"/>
				</div>
				<div class="formDiv">
					<label for="displacement">Displacement (liters):</label>
					<input required name="displacement" type="number" min="0" step="0.1" value="3.0" style="width:4em;"/>
				</div>
				<div class="formDiv">
					<label for="compression">Compression (X:1)</label>
					<input required name="compression" type="number" min="0" step="0.1" value="9.0" style="width:4em;"/>
				</div>
				<div class="formDiv">
					<label for="aspiration">Aspiration:</label>
					<select required name="aspiration">
						<option value="na" title="Slow">Naturally Aspirated</option>
						<option value="fi" title="Fast">Forced Induction</option>
					</select>
				</div>
			</fieldset>
		</div>
	</form>
</div>

<!DOCTYPE html>
<html xmlns:og="https://ogp.me/ns#" xmlns:fb="https://www.facebook.com/2008/fbml" lang="en" ng-app="msqur">
<head>
	<title>MSQur</title>
	<meta charset="UTF-8">
	<meta name="description" content="Megasquirt tune file sharing site" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<link rel="icon" type="image/x-icon" href="view/img/favicon.ico">
	<link rel="stylesheet" href="view/msqur.css" />
	<!-- Open Graph data -->
	<meta property="og:title" content="msqur" />
	<meta property="og:type" content="page" />
	<meta property="og:url" content="https://msqur.com/" />
	<meta property="og:image" content="https://msqur.com/view/img/tutorial5.png" />
	<meta property="og:description" content="Your Description Here" />
	<meta property="og:site_name" content="msqur" />
	<!-- Twitter Card data -->
	<meta name="twitter:card" content="summary">
	<meta name="twitter:site" content="@nearwood">
	<meta name="twitter:title" content="msqur">
	<meta name="twitter:description" content="Megasquirt tune sharing">
	<meta name="twitter:creator" content="@nearwood">
	<!-- Twitter Summary card images must be at least 120x120px -->
	<meta name="twitter:image" content="https://msqur.com/view/img/tutorial5.png">
<?php
if (DEBUG) { ?>
	<script src="view/lib/jquery.min.js"></script>
	<link rel="stylesheet" href="view/lib/jquery-ui.css" />
	<script src="view/lib/jquery-ui.min.js"></script>
	<script src="view/lib/angular.min.js"></script>
	<script src="view/msqur.js"></script>
	<script src="view/lib/tablesorter/jquery.tablesorter.min.js"></script>
	<script src="view/lib/Chart.js/Chart.min.js"></script>
<?php } else { ?>
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js" integrity="sha384-UM1JrZIpBwVf5jj9dTKVvGiiZPZTLVoq4sfdvIe9SBumsvCuv6AHDNtEiIb5h1kU" crossorigin="anonymous"></script>
	<link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.1/themes/smoothness/jquery-ui.css" integrity="sha384-5L1Zwk1YapN1l4l4rYc+1fr3Z0g23LbCBztpq0LQcbDCelzqgFb96BMCFtDwjq/b" crossorigin="anonymous">
	<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.1/jquery-ui.min.js" integrity="sha384-ovZOciNc/R4uUo2fCVS1oDT0vIBuaou1d39yqL4a9xFdZAYDswCgrJ6tF8ShkqzF" crossorigin="anonymous"></script>
	<script src="//ajax.googleapis.com/ajax/libs/angularjs/1.5.2/angular.min.js" integrity="sha384-neqWoCEBO5KsP6TEzfMryfZUeh7+qMQEODngh2KGzau+pMU9csLE2azsvQFa8Oel" crossorigin="anonymous"></script>
<?php
}

if (isset($_GET['msq'])) {
?>
	<meta name="robots" content="noindex">
<?php
}
?>
</head>
<body>
<div id="navigation">
	<button id="btnUpload"><img src="view/img/upload.svg" alt="Upload" width="16" height="16"><span>Upload</span></button>
	<span><a href="browse.php" rel="preload">Browse</a></span>
	<span style="display:none;"><a href="search.php">Search</a></span>
	<span style="display:none;"><a>Stats</a></span>
	<span><a href="about.php">About</a></span></div>
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
					<input id="make" required name="make" type="text" placeholder="e.g. GM" maxlength="32" style="width:4em;"/>
				</div>
				<div class="formDiv">
					<label for="code">Engine Code:</label>
					<input id="code" required name="code" type="text" placeholder="LS3" maxlength="32" style="width:4em;"/>
				</div>
				<div class="formDiv">
					<label for="displacement">Displacement (liters):</label>
					<input id="displacement" required name="displacement" type="number" min="0" step="0.1" value="3.0" style="width:4em;"/>
				</div>
				<div class="formDiv">
					<label for="compression">Compression (X:1)</label>
					<input id="compression" required name="compression" type="number" min="0" step="0.1" value="9.0" style="width:4em;"/>
				</div>
				<div class="formDiv">
					<label for="aspiration">Aspiration:</label>
					<select id="aspiration" required name="aspiration" size="2">
						<option value="na" title="Slow" selected>Naturally Aspirated</option>
						<option value="fi" title="Fast">Forced Induction</option>
					</select>
				</div>
				<input type="hidden" name="upload" value="upload" style="display:none;">
			</fieldset>
		</div>
	</form>
</div>
<?php
if (isset($_GET['msq'])) {
?>
<div id="settings">
	<img id="settingsIcon" alt="Settings" src="view/img/settings3.png"/>
	<div id="settingsPanel" style="display:none;">
		<label><input id="colorizeData" type="checkbox" />Colorize</label>
		<label><input id="normalizeData" type="checkbox" title="Recalculate VE table values to a 5-250 unit scale"/>Normalize Data</label>
		<label><input id="normalizeAxis" type="checkbox" disabled />Normalize Axis</label>
	</div>
</div>
<?php
}
?>
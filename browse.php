<?php

require('db.php');
require('header.php');

?>
<div id='content'>
<div>
<form id="filter">
	<fieldset>
	<legend>Engine Filter</legend>
	<div>Cylinders: <input type="number" min="0" value="6" max="99" style="width:3em;"/></div>
	<div>Displacement (liters): <input type="number" min="0" step="0.01" value="3.0" style="width:4em;"/> +/- <span id="literMargin">0%<input type="number" min="0" step="1"></span></div>
	<div>Compression (X:1) <input name="compression" type="number" min="0" step="0.1" value="9.0" style="width:4em;"/> +/- <span id="literMargin">0%<input type="number" min="0" step="1"></span></div>
	<div>Aspiration: 
		<select>
			<option value="na" title="AKA: Slow">Naturally Aspirated</option>
			<option value="fi" title="The way God intended">Forced Induction</option>
		</select>
	</div>
	<div>Firmware: X</div>
	<div><button>Refresh</button></div>
	</fieldset>
</form>
</div>
<?php
$results = browseAll();
$numResults = count($results);

//echo '<div class="debug">'; var_export($results); echo '</div>';

echo '<div class="info">' . $numResults . ' results.</div>';
echo '<table>';
echo '<tr><th>ID</th><th>Cylinders</th><th>Liters</th><th>Compression</th><th>Turbo</th><th>Firmware/Version</th><th>Upload Date</th><th>Views</th></th>';
for ($c = 0; $c < $numResults; $c++)
{
	echo '<tr><td><a href="index.php?msq=' . $results[$c]['mid'] . '">' . $results[$c]['mid'] . '</a></td><td>' . $results[$c]['numCylinders'] . '</td><td>' . $results[$c]['displacement'] . '</td><td>' . $results[$c]['compression'] . ':1</td><td>' . $results[$c]['induction'] . '</td><td>' . $results[$c]['firmware'] . '/' . $results[$c]['signature'] . '</td><td>' . $results[$c]['uploadDate'] . '</td><td></td></tr>';
}
echo '</table>';
?>
</div>
<?php require('footer.php'); ?>

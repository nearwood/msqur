<div id='content'>
<?php
require "msqur.php";

if (isset($_GET['p'])) {
	$page = $_GET['p']; //TODO processing
}

$msqur->browse($page);
//$results = browseAll();
//$numResults = count($results);

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

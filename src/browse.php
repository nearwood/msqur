<?php
/* msqur - MegaSquirt .msq file viewer web application
Copyright (C) 2015 Nicholas Earwood nearwood@gmail.com http://nearwood.net

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>. */

require "msqur.php";

if (isset($_GET['p'])) {
	$page = $_GET['p']; //TODO processing
} else $page = 0;

$results = $msqur->browse($page);
$numResults = count($results);

//echo '<div class="debug">'; var_export($results); echo '</div>';

$msqur->header();

require "view/filter.php";

echo '<div id="content"><div class="info">' . $numResults . ' results.</div>';
echo '<table>';
echo '<tr><th>ID</th><th>Engine Make</th><th>Engine Code</th><th>Cylinders</th><th>Liters</th><th>Compression</th><th>Aspiration</th><th>Firmware/Version</th><th>Upload Date</th><th>Views</th></th>';
for ($c = 0; $c < $numResults; $c++)
{
	$aspiration = $results[$c]['induction'] == 1 ? "Turbo" : "NA";
	echo '<tr><td><a href="view.php?msq=' . $results[$c]['mid'] . '">' . $results[$c]['mid'] . '</a></td><td>' . $results[$c]['make'] . '</td><td>' . $results[$c]['code'] . '</td><td>' . $results[$c]['numCylinders'] . '</td><td>' . $results[$c]['displacement'] . '</td><td>' . $results[$c]['compression'] . ':1</td><td>' . $aspiration . '</td><td>' . $results[$c]['firmware'] . '/' . $results[$c]['signature'] . '</td><td>' . $results[$c]['uploadDate'] . '</td><td>' . $results[$c]['views'] . '</td></tr>';
}
echo '</table></div>';

$msqur->footer();
?>

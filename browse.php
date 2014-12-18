<?php

require('db.php');

require('header.php');

?>
Latest Additions
Most Viewed

Cylinders
Liters
Injector Size
Forced Induction | NA
Cams
Headwork
<div id='content'>
<?php
$results = getAll();
$numResults = count($results);
//echo '<div class="debug">' . var_dump($results) . '</div>';
echo '<div class="info">' . $numResults . ' results.</div>';
echo '<ul>';
for ($c = 0; $c < $numResults; $c++)
{
	echo '<li>' . $results[$c]['id'] . '</li>';
}
echo '</ul>';
?>
</div>
<?php require('footer.php'); ?>

<?php
require "msqur.php";

if (isset($_GET['msq'])) {
	$msqur->view($_GET['msq']);
}
else include "index.php";

?>

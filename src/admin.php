<?php
require "msqur.php";

$msqur->header();

//trim/concentrate/whatever engines (remove dupes)
//remove all msqs/metadata/engines (fresh slate)

//DELETE ALL DATA
//TRUNCATE metadata; TRUNCATE msqs; TRUNCATE engines;

//Flag for reingest:
//UPDATE metadata SET reingest = 1 WHERE true

//reingest
//reparse XML

//find metada and engines (browse)
//SELECT * FROM `metadata` JOIN `engines` ON engines.id = metadata.engine 

$msqur->footer();
?>

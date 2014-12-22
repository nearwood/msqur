<?php
//trim/concentrate/whatever engines (remove dupes)
//remove all msqs/metadata/engines (fresh slate)

//find metada and engines (browse)
SELECT * FROM `metadata` JOIN `engines` ON engines.id = metadata.engine 

//DELETE ALL DATA
TRUNCATE metadata
TRUNCATE engines
TRUNCATE msqs
?>

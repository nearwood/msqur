<?php
/* msqur - MegaSquirt .msq file viewer web application
Copyright (C) 2016 Nicholas Earwood nearwood@gmail.com http://nearwood.net

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

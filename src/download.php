<?php
/* msqur - MegaSquirt .msq file viewer web application
Copyright (C) 2016 Nicholas Earwood nearwood@gmail.com https://nearwood.dev

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

if (isset($_GET['msq'])) {

  header('Content-Type: application/xml');
  header('Content-Disposition: attachment; filename=' . $_GET['msq'] . '.msq');
  header('Pragma: no-cache');

  echo $msqur->getMSQForDownload($_GET['msq']);
} else {

  include "index.php";
}
?>

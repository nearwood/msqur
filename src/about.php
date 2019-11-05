<?php
/* msqur - MegaSquirt .msq file viewer web application
Copyright 2014-2019 Nicholas Earwood nearwood@gmail.com https://nearwood.dev

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

?>
<div>
	<h2>About</h2>
	<p>Created out of a need to share .MSQ files.</p>
	<p>I was tired of downloading files and having to open them in <strike>MegaTune</strike> Tuner Studio, so I created this site.</p>
	<p>It's open source, so <a href="https://github.com/nearwood/msqur">anyone can contribute</a>.</p>
	<p>Since going "live" I only add things here and there whenever I have time or the urge to add features/fixes.</p>
</div>
<div>
	<h2>FAQ</h2>
	<ul id="faq">
		<li class="q">Why is this site so ugly?</li>
		<li class="a">It's a side project and my <a href="https://www.xkcd.com/323/">Ballmer Peak</a> doesn't last all day.</li>
		<li class="q">Can you add X feature?</li>
		<li class="a">File a request for it <a href="https://github.com/nearwood/msqur/issues">here</a>.</li>
		<li class="q">What tech stack does this site run on?</li>
		<li class="a">The frontend is Javascript (jQuery and a little Angular.js).<br/>The backend that does most of the work was made with PHP and data is stored in a SQL database.</li>
	</ul>
</div>
<h6 style="float: right;">
	Version: <?php if (@readfile("VERSION") === FALSE) echo "DEV"; ?>
</h6>
<?php
$msqur->footer();
?>

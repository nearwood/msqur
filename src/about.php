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

?>
<div>
	<h2>About</h2>
	<p>Created out of a need to share .MSQ files.
	I was tired of downloading files and having to open them in <strike>MegaTune</strike> Tuner Studio.
	So, I created this site. It's open source, so <a href="https://github.com/nearwood/msqur">feel free to contribute</a>.
	Since going "live" I only add things here and there whenever I have time or the urge to add features/fixes.</p>
</div>
<div>
	<h2>FAQ</h2>
	<ul id="faq">
		<li class="q">Why is this site so ugly?</li>
		<li class="a">My Ballmer peak doesn't last all day.</li>
		<li class="q">Can you add X feature?</li>
		<li class="a">File a request for it <a href="https://github.com/nearwood/msqur/issues">here</a>.</li>
	</ul>
</div>
<?php
$msqur->footer();
?>

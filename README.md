# MSQur #

'Masker' for lack of better name.
MegaSquirt MSQ file sharing and viewing site.

Parses MSQ "XML" in tandem with an associated INI (config) file and displays it in a familiar format for viewing and comparing.

### TODO ###

* Uploader (user)/Manager (admin)
* 3D table charts
* Tests and test lib.
* Better DB update procedure.

### Installation ###

#### Needed software ####

* AMP Stack

#### Recommended software ####

* phpMyAdmin

#### Process ####

* Create database user and database itself.
* Upload & deploy files.
* Setup script.config with details.
* Run db scripts.

#### hgrc ####
To display a fancy version string, modify your .hgrc to have this hook:

```
#!bash

[hooks]
post-update = hg log -r . --template "v{latesttag}-{latesttagdistance}-{node|short}\n" > VERSION
```

### Update & Deployment Instructions ###
 * Pull updates on host.
 * Update any configuration files (config.php, script.config) if needed.
 * Run any new DB scripts.
 * Run deploy.sh to copy web files to web server.

### License ###

msqur is licensed under the GPL v3.0. A copy of this license is included in the LICENSE.md file in the source tree.

### Who do I talk to? ###

* Nicholas Earwood
* nearwood@gmail.com
* http://nearwood.net/

### Credits ###
Apache, PHP, MySQL
jQuery, jQuery UI,
jquery.tablesorter http://tablesorter.com/docs/,
Chart.js,
Tango Icon Theme
Geany/Notepad++

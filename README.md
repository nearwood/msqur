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

* AMP Stack: Apache, MySQL (MariaDB), PHP
* MySQL PDO extension for PHP:
`/etc/php/php.ini`:
`extension=pdo_mysql.so`

#### Recommended software ####

* phpMyAdmin - For managing the DB
* rsync - For the deployment script

#### Development Setup ####

1. Clone repo to dev directory
1. Copy script.config.dist to script.config
1. Copy src/config.php.dist to src/config.php
1. Create database for msqur, and assign it a user
1. Setup parameters in each config file
1. Update DB with update scripts in sequential order
1. Run deploy script
1. Hit webserver to start using it.

#### hgrc ####
To display a fancy version string, modify your .hgrc to have this hook:

```
#!bash

[hooks]
post-update = hg log -r . --template "v{latesttag}-{latesttagdistance}-{node|short}\n" > src/VERSION
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
AngularJS,
Tango Icon Theme,
Geany/Notepad++
# MSQur

'Masker' I guess? Supposed to be a play on imgur.
MegaSquirt MSQ file sharing and viewing site.

Parses MSQ "XML" in tandem with an associated INI (config) file and displays it in a familiar format for viewing and comparing.

Try it now at: https://msqur.com/

### Build Status:
* msqur.com [![Build Status](https://travis-ci.org/nearwood/msqur.svg?branch=msqur.com)](https://travis-ci.org/nearwood/msqur)
* master [![Build Status](https://travis-ci.org/nearwood/msqur.svg?branch=master)](https://travis-ci.org/nearwood/msqur)

### Installation

#### Needed software

* MariaDB, PHP
* PDO extension for PHP.

#### Recommended software

* phpMyAdmin - For managing the DB

#### Development Setup

> These steps could be improved

1. Clone repo to dev directory
1. Create database for msqur, and assign it a user
1. Copy script.config.dist to script.config and modify for use (setup DB connection information)
1. Copy src/config.php.dist to src/config.php (setup DB information again)
1. Update DB with update scripts in sequential order (patse into phpMyAdmin or piped to `sqlcmd`, etc.)
1. Hit webserver to start using it (eg. `php -S`, etc.)

### Update & Deployment Instructions

> These steps are outdated

 * Pull updates on host.
 * Update any configuration files (config.php, script.config) if needed.
 * Run any new DB scripts.
 * Run deploy.sh to copy web files to web server.

### License

msqur is licensed under the GPL v3.0. A copy of this license is included in the LICENSE.md file in the source tree.

### Who do I talk to?

* Nicholas Earwood
* nearwood@gmail.com
* https://nearwood.dev/

### Credits

[CamHenlin](https://github.com/CamHenlin)

> This section needs to be updated

* Apache
* PHP
* MariaDB
* jQuery, jQuery UI
* jquery.tablesorter http://tablesorter.com/docs/
* Chart.js
* AngularJS
* Tango Icon Theme
* Geany/Notepad++

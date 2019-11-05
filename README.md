# MSQur

MegaSquirt MSQ file sharing and viewing site.

Pronounced 'masker' I guess? Supposed to be a play on imgur.

Parses MSQ "XML" in tandem with an associated INI (config) file and displays it in a familiar format for viewing and comparing.

Try it now at: https://msqur.com

### Build Status:
* [msqur.com](https://msqur.com) [![Build Status](https://travis-ci.org/nearwood/msqur.svg?branch=msqur.com)](https://travis-ci.org/nearwood/msqur)
* master [![Build Status](https://travis-ci.org/nearwood/msqur.svg?branch=master)](https://travis-ci.org/nearwood/msqur)

## Development status

Firmware support:

 - [ ] MS1
 - [ ] MSnS-extra (partial)
 - [x] MS2
 - [x] MS2Extra
 - [x] MS3
 - [ ] Speeduino

## Contributing

This is basically a one-man operation. I welcome any contributions: code, styles, text content, or simply spelling & grammar.
If you're interesting in helping out, please first take a look at the existing [issues](issues) and see if you can offer any assistance with them.
If you don't see your issue or new idea listed there you can [create a new issue](issues/new). Please be detailed.

If you'd like to run a copy to develop yourself, read the [Installation](#Installation) section below.

### Installation

#### Needed software

- PHP 7.x with the following extensions:
  - PDO
  - OpenSSL
- MySQL/MariaDB

#### Optional software

* Web server (Apache/nginx/etc.)
* phpMyAdmin - For managing the DB

#### Development Setup

> These steps could be improved

1. Clone repo to dev directory
1. Create database for msqur, and assign it a user
1. Copy script.config.dist to script.config and modify for use (setup DB connection information)
1. Copy src/config.php.dist to src/config.php (setup DB information again)
1. Update DB with update scripts in sequential order (patse into phpMyAdmin or piped to `sqlcmd`, etc.)
1. Hit webserver to start using it (eg. `php -S`, etc.)

### Source tree description

* `db` - Database scripts
* `doxygen` - Doxygen configuration and generated code documentation
* `src` - PHP source
  * `ini` - Megasquirt configuration files
  * `view` - PHP/JS frontend source
    * `lib` - JS 3rd party libraries
    * `img` - Static images
  * `tests` - PHP Unit Tests (TODO)


### Update & Deployment Instructions

> These steps are outdated

 * Pull updates on host.
 * Update any configuration files (config.php, script.config) if needed.
 * Run any new DB scripts.
 * Run deploy.sh to copy web files to web server.

### License

msqur is licensed under the GPL v3.0. A copy of this license is included in the LICENSE.md file in the source tree.

### Who do I talk to?

* Nick
* nearwood@gmail.com
* https://nearwood.dev/

### Credits

* [CamHenlin](https://github.com/CamHenlin)

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

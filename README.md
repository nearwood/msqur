# MSQur #

'Masker' for lack of better name.
MegaSquirt MSQ file sharing and viewing site.

Parses MSQ "XML" and displays it in a familiar format for viewing and comparison.

MSQ XML is pretty bad XML, it doesn't take advantage of many XML features.

# TODO #

Uploader (user)/Manager (admin)

### How do I get set up? ###

* AMP Stack
* Arch: PHP, PHP-Apache, MariaDB/MySQL, phpMyAdmin, pdo_mysql.so in php.ini
* Create database user and tables
* Upload web files
* How to run tests

### hgrc ##
[hooks]
post-update = hg log -r . --template "v{latesttag}-{latesttagdistance}-{node|short}\n" > VERSION

### Deployment instructions ###
* Export tables (not entire DB)
* Pull updates on host (don't overwrite DB config)
* Import DB

### Contribution guidelines ###

* Writing tests
* Code review
* Other guidelines

### Who do I talk to? ###

* Repo owner or admin
* Other community or team contact

### Credits ###
Apache, PHP, MySQL
jQuery, jQuery UI
jquery.tablesorter http://tablesorter.com/docs/
Tango Icon Theme
Geany/Notepad++

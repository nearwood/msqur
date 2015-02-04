# MSQur #

'Masker' for lack of better name.
MegaSquirt MSQ file sharing and viewing site.

Parses MSQ "XML" and displays it in a familiar format for viewing and comparison.

MSQ XML is pretty bad XML, it doesn't take advantage of many XML features.

# TODO #
1. Parse engine details and update from MSQ
1. DB update management
1. Admin so things are easier
1. Strip whitespace (except in table data!)
1. Store gzipped?
1. Add Ads
1. Searching MSQ comments?
1. Allow download
1. Allow export of just fuel/spark tables (msqpart, .table)
1. Sign-in?
1. Updating/Versioning/Differential MSQ info

# Done #
1. Basic browse
1. Upload file
1. Store files in DB instead of FS
1. Re-encode as UTF-8 or degrees symbol breaks things
1. Parse File, show basic info
1. Show Fuel Table
1. Show Timing Table
1. Parse XML once! Save to DB. Allow re-ingest and stuff though.
1. Show extended info (warmup, etc.) [Loads corresponding INI file to parse.]

Uploader (user)/Manager (admin)
Parser
Displayer

### How do I get set up? ###

* AMP Stack
* Create database user and tables
* Upload web files
* How to run tests

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

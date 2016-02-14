PHP nagiosXML
=============

[![Project Status: Abandoned – Initial development has started, but there has not yet been a stable, usable release; the project has been abandoned and the author(s) do not intend on continuing development.](http://www.repostatus.org/badges/latest/abandoned.svg)](http://www.repostatus.org/#abandoned)

This is a PHP script that parses the Nagios status.dat file into an array, and
then outputs that array as XML. There is also a PHP module written in C to do
the same task, and based on the same code.

**Note** - While it seems that quite a few people are using this, XML really
  is an awful, 1990s technology. I also have not tested this with Nagios3, or
  really with any Nagios version that came out in the past *six years*. If you've
  found this, I would *highly* recommend one of the following alternatives:
* [nagios-api](https://github.com/zorkian/nagios-api) provides a read/write ReST
  API for Nagios. It can be made read-only, in which case it just provides a
  ReST API to ``status.dat``.
* [Icinga](https://www.icinga.org/), a fork of Nagios, provides a modern REST
  API built-in. It's also actively developed in the open, and welcomes community
  contributions, [unlike Nagios](http://www.freesoftwaremagazine.com/articles/nagios_and_icinga).
* [Christian Lizell's
  statusJson.php](https://github.com/lizell/php-nagios-json) does more or less
  the same thing as this script (I've not tested it), but returns nice, happy
  JSON output instead of ugly XML.

License
--------

This project is licensed under the GNU General Public License (GPL) version
3 with the *ADDITIONAL CONDITIONS* (Article 7, terms b) and c)) that existing
copyright notices and author attributions must be left intact, and that any
modifications must be clearly noted in the CHANGELOG portion of the
corresponding file.

Furthermore, it is politely requested (but not required) that any
modifications be sent back to the original author for inclusion in the latest
version of the code.

Components
-----------
statusXML.php.inc - the include file. This is just made up of functions that
do all of the heavy lifting.

statusXML.php - a script that shows how to make use of the functions in
statusXML.php.inc to output XML. You may as well replicate this simple logic
in your program.

Other Files
------------
statusXML-krzywanski.php - Artur Krzywański's patch to allow selection of what
keys you want returned. This is currently included as a separate file because
it is based off of SVN revision 4, which supports Nagios2 only. When I have
time, I should merge it into the current version.

php_module/ - This code rewritten in C as a PHP module for high
performance. Generously coded by Whitham D. Reeve II of General Communication,
Inc. <http://www.gci.com>. Also currently only supports Nagios2.

Source
-------

The current canonical source of the newest version of this project is GitHub,
specifically <https://github.com/jantman/php-nagios-xml>. Prior to March 2013,
the canonical source of this project was via SVN at
<http://svn.jasonantman.com/nagios-xml/> or via the ViewVC web interface at
<http://viewvc.jasonantman.com/cgi-bin/viewvc.cgi/nagios-xml/>. Going forward,
the only online source will be at GitHub.

Authors
--------
Jason Antman <http://www.jasonantman.com> - principal author
Artur Krzywański <http://www.krzywanski.net/> - patch for selection of returned keys
Whitham D. Reeve II of General Communication, Inc. <http://www.gci.com> - PHP module

Changelog
----------

2010-08-10 (r6) jantman:
	- added better headers to all files, updated license

2010-02-21 (r5) jantman:
	- broke out functions into statusXML.php.inc, handler/XML output in
	   statusXML.php
	- Added statusXML-krzywanski.php, modified version of r4 statusXML.php
	- Added php_module/ by Whitham D. Reeve II

2009-07-30 (r4) jantman:
	- initial creation and import into SVN.

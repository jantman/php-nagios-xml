nagiosXML - README

$LastChangedRevision$
$HeadURL$


by Jason Antman <jason@jasonantman.com>
<http://www.jasonantman.com>
<http://blog.jasonantman.com>

The canonical source for this script and related files is:
<http://svn.jasonantman.com/nagios-xml/> (via SVN or HTTP)
You can also view via ViewVC at:
<http://viewvc.jasonantman.com/cgi-bin/viewvc.cgi/nagios-xml/>

=====ABOUT=====
This is a script that parses the Nagios status.dat file into an array, and
then outputs that array as XML.

=====COMPONENTS=====
statusXML.php.inc - the include file. This is just made up of functions that
do all of the heavy lifting.

statusXML.php - a script that shows how to make use of the functions in
statusXML.php.inc to output XML. You may as well replicate this simple logic
in your program.

=====OTHER FILES=====
statusXML-krzywanski.php - Artur Krzywański's patch to allow selection of what
keys you want returned. This is currently included as a separate file because
it is based off of SVN revision 4, which supports Nagios2 only. When I have
time, I should merge it into the current version.

php_module/ - This code rewritten in C as a PHP module for high
performance. Generously coded by Whitham D. Reeve II of General Communication,
Inc. <http://www.gci.com>. Also currently only supports Nagios2.

=====AUTHORSS=====
Jason Antman <http://www.jasonantman.com> - principal author
Artur Krzywański <http://www.krzywanski.net/> - patch for selection of returned keys
Whitham D. Reeve II of General Communication, Inc. <http://www.gci.com> - PHP module

=====CHANGELOG=====

2010-02-21 (r5):
	- broke out functions into statusXML.php.inc, handler/XML output in
	   statusXML.php
	- Added statusXML-krzywanski.php, modified version of r4 statusXML.php
	- Added php_module/ by Whitham D. Reeve II

2009-07-30 (r4):
	- initial creation and import into SVN.

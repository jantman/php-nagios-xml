#
# +----------------------------------------------------------------------+
# | php_module/php_nagios.h                                              |
# |                                                                      |
# | PHP Module based on Jason Antman's statusXML.php                     |
# |                                                                      |
# | The canonical source for this project is:                            |
# |   <http://svn.jasonantman.com/nagios-xml/> (via SVN or HTTP)         |
# +----------------------------------------------------------------------+
# |                                                                      |
# | This program is free software; you can redistribute it and/or modify |
# | it under the terms of the GNU General Public License as published by |
# | the Free Software Foundation; either version 3 of the License, or    |
# | (at your option) any later version.                                  |
# |                                                                      |
# | This program is distributed in the hope that it will be useful,      |
# | but WITHOUT ANY WARRANTY; without even the implied warranty of       |
# | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the        |
# | GNU General Public License for more details.                         |
# |                                                                      |
# | You should have received a copy of the GNU General Public License    |
# | along with this program; if not, write to:                           |
# |                                                                      |
# | Free Software Foundation, Inc.                                       |
# | 59 Temple Place - Suite 330                                          |
# | Boston, MA 02111-1307, USA.                                          |
# +----------------------------------------------------------------------+
# | Authors: Whitham D. Reeve II of General Communication, Inc.          |
# |                 <http://www.gci.com>                                 |
# +----------------------------------------------------------------------+
# | CHANGELOG:                                                           |
# | 2010-08-10 (r6) jantman:                                             |
# |   - updated license, file header, changelog                          |
# | 2010-02-22 (r5) jantman:                                             |
# |   - initial import into nagios-xml SVN                               |
# +----------------------------------------------------------------------+
# | $Date::                                                            $ |
# | $LastChangedRevision::                                             $ |
# | $HeadURL::                                                         $ |
# +----------------------------------------------------------------------+ 
#

#ifndef PHP_NAGIOS_H
#define PHP_NAGIOS_H 1

#define PHP_NAGIOS_VERSION "1.0"
#define PHP_NAGIOS_EXTNAME "nagios"

PHP_FUNCTION(nagios_get_status);

extern zend_module_entry nagios_module_entry;
#define phpext_nagios_ptr &nagios_module_entry

#endif

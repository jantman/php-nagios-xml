#
# +----------------------------------------------------------------------+
# | php_module/nagios.c                                                  |
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

# by Whitham D. Reeve II of  General Communication, Inc.
# C / PHP Module implementation of Jason Antman's statusXML.php
# http://svn.jasonantman.com/nagios-xml/

#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

#ifndef _GNU_SOURCE
#define _GNU_SOURCE
#endif

#include "php.h"
#include "php_nagios.h"
#include <stdio.h>
#include <stdlib.h>
#include <string.h>


static function_entry nagios_functions[] = {
    PHP_FE(nagios_get_status, NULL)
    {NULL, NULL, NULL}
};

zend_module_entry nagios_module_entry = {
#if ZEND_MODULE_API_NO >= 20010901
    STANDARD_MODULE_HEADER,
#endif
    PHP_NAGIOS_EXTNAME,
    nagios_functions,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
#if ZEND_MODULE_API_NO >= 20010901
    PHP_NAGIOS_VERSION,
#endif
    STANDARD_MODULE_PROPERTIES
};

#ifdef COMPILE_DL_NAGIOS
ZEND_GET_MODULE(nagios)
#endif

int strstrip(char *sOut, unsigned int lenOut, char const *sIn)
{
    char const *pStart, *pEnd;
    unsigned int len;
    char *pOut;

    // if there is no room for any output, or a null pointer, return error!
    if (0 == lenOut || !sIn || !sOut)
        return -1;

    pStart = sIn;
    pEnd = sIn + strlen(sIn) - 1;

    // skip any leading whitespace
    while (*pStart && isspace(*pStart))
        ++pStart;

    // skip any trailing whitespace
    while (pEnd >= sIn && isspace(*pEnd))
        --pEnd;

    pOut = sOut;
    len = 0;

    // copy into output buffer
    while (pStart <= pEnd && len < lenOut - 1)
    {
        *pOut++ = *pStart++;
        ++len;
    }


    // ensure output buffer is properly terminated
    *pOut = '\0';
    return len;
}

int is_equal(const char *str1, const char *str2)
{
	return strcmp(str1, str2) == 0;
}

int in_array(char *s, char**array, int arraysize)
{
	int i = 0;
	for (i = 0; i<arraysize; i++)
		if (strcmp(s, array[i]) == 0)
			return 1;

	return 0;

}

PHP_FUNCTION(nagios_get_status)
{
	char *hostkeys[64] = {"host_name", "has_been_checked", "check_execution_time", "check_latency",
				"check_type", "current_state", "current_attempt", "state_type",
				"last_state_change", "last_time_up", "last_time_down", "last_time_unreachable",
				"last_notification", "next_notification", "no_more_notifications",
				"current_notification_number", "notifications_enabled", "problem_has_been_acknowledged",
				"acknowledgement_type", "active_checks_enabled", "passive_checks_enabled", "last_update"};
	int hostkeySize = 22;

	char *servicekeys[64] = {"host_name", "service_description", "has_been_checked", "check_execution_time", 
				"check_latency", "current_state", "state_type", "last_state_change", "last_time_ok", 
				"last_time_warning", "last_time_unknown", "last_time_critical", "plugin_output", 
				"last_check", "notifications_enabled", "active_checks_enabled", "passive_checks_enabled", 
				"problem_has_been_acknowledged", "acknowledgement_type", "last_update", "is_flapping"};
	int servicekeySize = 21;

	FILE *statusfile;
	
	int buffer_size = 512;
	char * lineptr = (char *) malloc(buffer_size + 1);

	int linenumber = 0;
	int line_size = 0;

	char *filename;
	int filename_len;
	
	int inSection = 0;
	char *sectionType = malloc(buffer_size);
	int sectionTypeSize = 0;

	char *linekey = malloc(buffer_size);
	int linekeySize = 0;

	char *lineval = malloc(buffer_size);
	int linevalSize = 0;

	zval * sectionData;
	
	zval * serviceStatus;
	zval * hostStatus;

	zval * serviceDescription;
	
	zval **tmp;

	char *hostname;
	char *servdesc;
	int hostnameSize = 0;
	int servdescSize = 0;

	MAKE_STD_ZVAL(serviceStatus);
	array_init(serviceStatus);
	MAKE_STD_ZVAL(hostStatus);
	array_init(hostStatus);
	MAKE_STD_ZVAL(sectionData);
	array_init(sectionData);

	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "s", &filename, &filename_len) == FAILURE) {
		RETURN_NULL();
	}
	
	statusfile = fopen(filename, "r+");
	if (statusfile == NULL)
	{
		PHPWRITE("Error opening file.", strlen("Error opening file."));
		free(lineptr);
		free(linekey);
		free(lineval);
		free(sectionType);
		RETURN_FALSE;
	}

	while(fgets(lineptr, buffer_size, statusfile) > 0)
	{
		linenumber++;

		strstrip(lineptr, buffer_size, lineptr);

		if (strlen(lineptr) == 0) continue;
		if (lineptr[0] == '#') continue;
		if (!inSection)
		{
			if (strchr(lineptr, ' ') && lineptr[strlen(lineptr) - 1] == '{')
			{
				sectionTypeSize = strchr(lineptr, ' ') - lineptr;
				strncpy(sectionType, lineptr, sectionTypeSize);
				sectionType[sectionTypeSize] = '\0';
				
				inSection = 1;
				
				MAKE_STD_ZVAL(sectionData);
				array_init(sectionData);

			}
		}
		else if (inSection && lineptr[0] == '}')
		{
			if (is_equal(sectionType, "service"))
			{
				if (zend_symtable_find(sectionData->value.ht, "host_name", strlen("host_name") + 1, (void **)&tmp) == SUCCESS)
				{
					if (Z_TYPE_PP(tmp) == IS_STRING)
					{
						hostname = Z_STRVAL_PP(tmp);
						hostnameSize = Z_STRLEN_PP(tmp);
					}
				
					if (zend_symtable_find(sectionData->value.ht, "service_description", strlen("service_description") + 1, (void **)&tmp) == SUCCESS)
					{
						if (Z_TYPE_PP(tmp) == IS_STRING)
						{
							servdesc = Z_STRVAL_PP(tmp);
							servdescSize = Z_STRLEN_PP(tmp);
						}
					}
				}
				if (hostnameSize != 0 && servdescSize != 0)
				{
					if (zend_symtable_find(serviceStatus->value.ht, hostname, hostnameSize + 1, (void **)&tmp) == SUCCESS)
					{
						add_assoc_zval_ex(*(tmp), servdesc, servdescSize + 1, sectionData);
					}
					else
					{
						MAKE_STD_ZVAL(serviceDescription);
						array_init(serviceDescription);
						add_assoc_zval_ex(serviceDescription, servdesc, servdescSize + 1, sectionData);
						add_assoc_zval_ex(serviceStatus, hostname, hostnameSize + 1, serviceDescription);
						
					}
				}
			}
			if (is_equal(sectionType, "host"))
			{
				if (zend_symtable_find(sectionData->value.ht, "host_name", strlen("host_name") + 1, (void **)&tmp) == SUCCESS)
				{
					if (Z_TYPE_PP(tmp) == IS_STRING)
					{
						hostname = Z_STRVAL_PP(tmp);
						hostnameSize = Z_STRLEN_PP(tmp);
					}
					
				}
				if (hostnameSize != 0)
				{
					add_assoc_zval_ex(hostStatus, hostname, hostnameSize + 1, sectionData);
				}
			}
			
			inSection = 0;
			strncpy(sectionType, "\0\0", buffer_size);
		}
		else
		{
			linekeySize = strchr(lineptr, '=') - lineptr;
			strncpy(linekey, lineptr, linekeySize);
			linekey[linekeySize] = '\0';

			linevalSize = (lineptr + strlen(lineptr)) - strchr(lineptr, '=');
			strncpy(lineval, strchr(lineptr, '=') + 1, linevalSize);
			lineval[linevalSize] = '\0';
			if (is_equal(sectionType, "service"))
			{
				if (in_array(linekey, servicekeys, servicekeySize))
				{
					add_assoc_string(sectionData, linekey, lineval, 1);
				}
			}
			if (is_equal(sectionType, "host"))
			{
				if (in_array(linekey, hostkeys, hostkeySize))
				{
					add_assoc_string(sectionData, linekey, lineval, 1);
				}
			}
			// else continue on, ignore this section, don't save anything
		}
	}
	free(lineptr);
	free(lineval);
	free(linekey);
	free(sectionType);
	
	fclose(statusfile);
	
	array_init(return_value);
	add_assoc_zval(return_value, "service", serviceStatus);
	add_assoc_zval(return_value, "host", hostStatus);
}

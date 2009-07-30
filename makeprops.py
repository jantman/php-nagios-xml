#!/usr/bin/env python
# Python script to set SVN keyword substitution properties on files

# bottom line - this runs a given command on every regular file (and not in .svn/) under a path

import os, os.path, sys

CMD = 'svn propset svn:keywords "Date Revision Author HeadURL Id" ' # will be postfixed by file name

# propset all files in path, recurse
def doDir(path):
    # list all files in directory
    for f in os.listdir(path):
        # skip over '.', '..', and '.svn'
        if f == "." or f == ".." or f == ".svn":
            continue
        # either do the file or recurse
        if os.path.isfile(os.path.join(path, f)):
            sys.stderr.write("Setting props on " + os.path.join(path, f) + "... ")
            os.system(CMD + os.path.join(path, f))
            sys.stderr.write(" OK.\n")
        elif os.path.isdir(os.path.join(path, f)):
            # recurse
            doDir(os.path.join(path, f))
# RUN THE FUNCTION:
doDir("./")

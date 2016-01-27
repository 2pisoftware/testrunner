#!/bin/bash
. /var/www/testrunner/setenvironment.sh /var/www/testrunner/environment.cmfive.docker.csv
/var/www/testrunner/runtests.sh

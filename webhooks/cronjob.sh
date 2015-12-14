#!/bin/bash
DIR=`dirname $0`
PROJECTSPATH=/var/www/projects
TESTRUNNERPATH=$PROJECTSPATH/testrunner/dev
RUNLOG=/tmp/testsrunlog

if  [ -e /tmp/testsrunning ] 
then
  echo >> $RUNLOG
  echo "IGNORED AT " >> $RUNLOG
  echo -n `date` >> $RUNLOG
else
  touch /tmp/testsrunning
  echo >> $RUNLOG
  echo `date` >> $RUNLOG
  #sleep 3
  for i in `ls -d -1  /tmp/test/jobs/* 2> /dev/null | sort`; do
    repo=`cat $i|cut  -f 1`
    email=`cat $i|cut  -f 2`
    rm $i
    if [ -e /var/www/projects/$repo/environment.csv ] 
    then
		echo "RUN TESTS for $repo for commiter $email" > /tmp/testrunout
		. $TESTRUNNERPATH/setenvironment.sh $PROJECTSPATH/$repo/environment.csv  > /dev/null
		cd $PROJECTSPATH/$repo/dev 
		gitOut=`git pull 2> /dev/null`
		$TESTRUNNERPATH/runtests.sh >> /tmp/testrunout
		code=`tail -1 /tmp/testrunout|cut -d' ' -f 3`
		#echo CODE:$code
		testOut=`cat /tmp/testrunout|grep -v "password="`;
		# FORCE ALL NOTIFICATIONS TO LOCAL DELIVERY
		#email=ubuntu
		#email=root@code.2pisoftware.com
		#email=stever@syntithenai.com
		#echo $testOut
		#rm /tmp/testrunout
		if [ $code -eq 0 ]
		then
		  echo "Tests Passed" > /tmp/testmail
		  echo >> /tmp/testmail
		  echo "See detailed test output at http://tests.$repo.dev.code.2pisoftware.com"  >> /tmp/testmail
		  echo >> /tmp/testmail
		  echo "PULL FROM GIT" >> /tmp/testmail
                  echo >> /tmp/testmail
		  echo "$gitOut" >>  /tmp/testmail
                  echo >> /tmp/testmail
		  echo "TEST OUTPUT"  >> /tmp/testmail
                  echo >> /tmp/testmail
		  echo "$testOut" >> /tmp/testmail
                  echo >> /tmp/testmail
		  cat /tmp/testmail | mail -s 'Your push to git passes all tests' "$email"
		  $TESTRUNNERPATH/runtests.sh coverage:1 > /dev/null
		else 
			echo "Failed Tests" > /tmp/testmail
			echo >> /tmp/testmail
			echo "See detailed test output at http://tests.$repo.dev.code.2pisoftware.com" >> /tmp/testmail
			echo >> /tmp/testmail
		        echo "PULL FROM GIT" >> /tmp/testmail
	                  echo >> /tmp/testmail
			echo "$gitOut" >>  /tmp/testmail
        	          echo >> /tmp/testmail
			echo "TEST OUTPUT "  >> /tmp/testmail
                        echo "$testOut" >> /tmp/testmail
			cat /tmp/testmail | mail -s 'Tests Failing resulting from your push to git' "$email"
		fi
             	# rm /tmp/testmail
		rm -rf  /var/www/projects/$repo/tests/*
		cp -r $TESTRUNNERPATH/output/*  $PROJECTSPATH/$repo/tests/
	fi
  done
  rm /tmp/testsrunning
fi


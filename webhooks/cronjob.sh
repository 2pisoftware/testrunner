#!/bin/bash
DIR=`dirname $0`
TESTRUNNERPATH=/var/www/tools/testrunner
PROJECTSPATH=/var/www/projects
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
  for i in `ls -d -1  $DIR/jobs/* | sort`; do
    repo=`cat $i|cut  -f 1`
    email=`cat $i|cut  -f 2`
    if [ -e /var/www/projects/$repo/environment.csv ] 
    then
		echo RUN TESTS for $repo
		. $TESTRUNNERPATH/setenvironment.sh $PROJECTSPATH/$repo/environment.csv > /tmp/testrunout
		$TESTRUNNERPATH/runtests.sh >> /tmp/testrunout
		code=`tail -1 /tmp/testrunout|cut -d' ' -f 3`
		echo CODE:$code
		testOut=`cat /tmp/testrunout`;
		#email=syntithenai@iinet.net.au
		email=ubuntu
		#echo $testOut
		#rm /tmp/testrunout
		if [ $code -eq 0 ]
		then
		  echo "Tests Passed"
		  echo "Tests Passed $testOut" | mail -s 'Your push to git passes all tests' $email
		  #$TESTRUNNERPATH/runtests.sh coverage:1
		else 
			echo "Failed Tests"
			echo "Failed Tests $testOut" | mail -s 'Tests Failing resulting from your push to git' $email
		fi
	fi
	rm $i
  done
  rm /tmp/testsrunning
fi


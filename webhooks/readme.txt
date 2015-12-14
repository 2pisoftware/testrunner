Test Runner Webhooks

The webhooks module of the test runner provides 
1. A handler for webhook requests coming from bitbucket or github
The handler logs git push requests into job files
2. A cron job that processes the job files by
- pulling the latest commits
- running tests
- sending email with test output
- in the case of successful test run, generating documentation and code coverage reports

Server Setup
- install php5-xdebug
- create crontab entry for webhooks/cronjob.sh
- install testrunner at /var/www/tools/testrunner


Site Setup
- install a site to code
	- repository checkout using ssh and keys into /var/www/projects/<reponame>/dev
	- add vhost entries as per other sites in /etc/apache2/sites-available/vhosts.conf
- create environment file in /var/www/projects/<reponame>/environment.csv. When tests are run, this file will used to install cmfive.
- copy Doxygen file (from another site) to /var/www/projects/<reponame>
- using bitbucket or github website as admin, create webhook call for project to call webhook at http://webhook.code.2pisoftware.com triggered by push requests


TEST
- run tests manually
reponame=cmfive
. /var/www/tools/testrunner/setenvironment.sh /var/www/projects/$reponame/environment.csv
/var/www/tools/testrunner/runtests.sh


alias testPush='cd /root/testrepository_bitbucket; echo "dd" >> readme.txt; git add .; git commit -m eeek; git push; cd -'
alias testRun='function _blah(){ echo -n "$1" ; echo -e -n "\t"; echo -n "$2"; };_blah > /var/www/projects/testrunner/dev/webhooks/jobs/fakejob'
alias testLogs='tail -f /var/log/apache2/other_vhosts_access.log  /var/log/apache2/error.log /root/mbox &'
alias testJobs='cat /var/www/projects/testrunner/dev/webhooks/jobs/*'

# also crontab

# also path to testrunner



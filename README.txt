!!TestRunner

This project facilitates running test suites in a code base.
Codeception underpins the test run framework.

It helps with finding and running many test folders across a code base.
It helps by iterating discovered test folders to run tests. 
It helps with managing configuration required for running tests. The TestConfig class provides access to a unified configuration tree derived from (in order) defaults, environment variables and parameters(arguments or GET). In preparation for running tests, cmFive and codeception configuration files are rewritten based on the unified configuration.
It helps by collating all the SQL necessary to install cm5 into a single file which can be run between each test to ensure the database is clean.

When the test runner executes
- It installs cmFive by rewriting the config.php file with configuration for this test run. The old file is backed up to config.old.php.
- It collates all the SQl necessary to install cm5 and all modules, writes this to $cmFivePath/cache/install.sql and then runs the sql, creating the database if required and admin access is provided.
- It cleans up garbage from previous test runs.
THEN
For each test folder
- the folder contents are copied to the $testStagingPath directory so test artifacts are not left in the main source tree.
- the codeception.yml and *.suite.yml are read and rewritten using configuration values
- some of the configuration items are set into environment variables for use in tests
- codeception support classes are built in the staging directory
- the tests matching $testSuite and $test  are run 
- the output of the test run is copied to a unique folder name inside $testOutputPath
FINALLY
Any new output on any one of the csv seperated $testLogFiles is shown in the output ie Logfiles are tailed.



!Quickstart
copy environment.sample.csv to environment.yourname.csv, edit it and set appropriate values
setenvironment.bat yourname  - will read your csv configuration into environment variables
runtests.bat   -   will run the tests for the TestRunner using the current environment
runtests.bat testPath:c:\inetpub\wwwroot\cmfive  - will look for and run tests in the cmfive source tree

runtests.bat can be added to your PATH environment variable and run from anywhere
alternatively you can install the test runner under a webserver and hit the index.php file. GET variables are used as configuration arguments.
setenvironment.bat can be used to set persistent global environment variables and restart IIS web server. This must be run in a console running as Administrator.

Parameters are tokenised by the first : to match parameter names. 
Empty values are allowed eg testSuite: to disable a default from the environment.
Allowed parameters include
'testPath'  - where to look for test suites.   
(DEFAULT testrunner tests)

'testSuite'  - which test suite to run eg acceptance/unit/??? in each of the discovered test folders  
(DEFAULT empty)

'test'    - which individual test to run eg FileSystemToolsTest:testCopyRecursive. Requires a value for testSuite 
(DEFAULT empty)


'testUrl' - url to use for webdriver config  
(REQUIRED  for acceptance tests using webdriver)  
(DEFAULT empty)

'testOutputPath'	- where output from all test folders and all test suites is collated   
(DEFAULT $testRunnerPath/output)

'testStagingPath'	- where tests are copied to build and run   
**WARNING when using concurrent sessions of the test runner app, you must make sure that the $testStaging and $testOutput paths are unique to each concurrent session.
(DEFAULT $testRunnerPath/staging)

'testIncludePath'	- path set into environment for including source code inside tests. 
					By using chdir to this path in your test, relative links within an existing code base will work.   
					(DEFAULT the same as $testPath after parameters unless set otherwise)  
					** WARNING Because this defaults to $testPath, selection of tests inside the main source tree using $testPath (perhaps as a parameter) will upset the default for this value and it will need to be set explicitly eg runtests.bat testPath:/src/stuff testIncludePath:/src.
'testSharedSupportPath' - Files in this folder are copied to the staging test support directory before copying test files. Intended for test code that is shared across multiple test folders and test suites.

'cmFivePath'		- 
(DEFAULT empty)

'testLogFiles'		- comma seperated list of files that should be compared before and after testing. any additions are shown in test output.					

'codeception'		- command to run codeception  (DEFAULT $testRunnerPath/composer/bin/codeception)

// NO LONGER RELEVANT USE DOCKER TO START WebDriver service.
'phantomjs'			- path to phantomjs binary   (DEFAULT $testRunnerPath/composer/bin/phantomjs)

'driver'			- PDO driver type for database connection (DEFAULT empty)
'hostname'			- hostname for database connection (DEFAULT empty)
'port'			- port for database connection (DEFAULT empty)
'username'			- username for database connection (can only create database if user with sufficient priveleges is provided) (DEFAULT empty)
'password'			- password for database connection (DEFAULT empty)
'database'			- database name (DEFAULT empty)

'coverage'			- a true value will enable code coverage report. Tests take considerably longer when generating coverage. Running with coverage disables debugging and other diagnostic output.


!Environment variables
The test runner can derive its configuration from environment variables.
Configuration values can be put into a csv file and loaded into environment variables.

setenvironment.bat steveDev - will load configuration values from $testRunnerPath/environment.steveDev.csv into environment variables
setenvironment.bat c:\environments\environment.steveDev.csv  will load configuration values from c:\environments\environment.steveDev.csv into environment variables

WARNING on linux run as
source setenvironment.sh  <environment>

If setenvironment.bat is run in a command prompt as Administrator, 
- the configuration values are set into global as well as local environment variables, 
- the IIS web server is restarted so it picks up the changes.



!Installing cmFive
There is a script to install cm5 which is called by runtests.bat but can be called directly. 
It responds to environment variables and CLI arguments and is configured using many of the same parameters as the test runner, particularly the database connection details. 
NOTE cmFivePath is a required configuration value.






FAQ
// NO LONGER RELEVANT USE DOCKER TO START WebDriver service.
- How do I Install Phantomjs
The windows binary for phantomjs is committed to the testrunner git repository. This is the default path so that windows users don't need to do anything.

For linux there are binary downloads and in some case package management solutions to install phantomjs.
Be sure to set the phantomjs environment variable so the testrunner can find your phantomjs installation.


- How do I create a new test folder/suites
Copy $cmFivePath/system/tests to your module and delete all the test files.

Alternatively you can use codeception to generate test suites and files in the staging folder and then manually copy them back to your test folder.


- How do I stop tests failing when requests take longer than a few seconds to process

Increase the value of the configuration option - wait in the acceptance.suite.yml file to something larger than it's default of 1.
OR
In your tests use $I->wait(3)
OR 
$I->waitForElementVisible('#cmfive-modal .savebutton',5);

-- How do I debug my tests?
codecept_debug($anyValueHere);

-- how do I stub global functions
A. Use AspectMock ??? I haven't been able to make this work. problems with aspectmock initialisation paths ???
B. add a function call to the test target class that calls the global function and replace calls to the global function.
This method can then be stubbed.

C. ???? this stopped working ????
use a namespace as follows to add the override method to a different namespace. see cmfive/system/tests/unit/WebTest.php
--------------------------
namespace WebTest {
use \Codeception\Util\Stub;

// disable header function
function header($a) {
	echo("::HEADER::".$a);
}
		
	
class WebTest extends  \Codeception\TestCase\Test {

---------------------------

-- do I need to clean up in my tests
YES
but between each test, 
the database is refreshed from cache/install.sql
in WebTest, the Web instance is renewed before each test
in WebTest, the removeTestTemplateFiles is called to cleanup files. Similar approaches may be used in other tests where files are created.


-- how do i enable coverage reporting for my test run
short answer is use the parameter coverage with some true value
eg runtests coverage:1
BUT you need the following prerequisites
1. Install xdebug  http://xdebug.org/wizard.php or https://www.jetbrains.com/phpstorm/help/configuring-xdebug.html
[Xdebug]
zend_extension="<path to php_xdebug.dll>"
xdebug.remote_enable=1
xdebug.remote_port="<the port for Xdebug to listen to>" (the default port is 9000)
xdebug.profiler_enable=1
xdebug.profiler_output_dir="<AMP home\tmp>"
2. Modify .htaccess to send c3 requests to c3.php 
ie 
RewriteRule c3 c3.php



-- functions that are called indirectly eg call_user_func do not show as executed in coverage reports
eg call_user_func_array(array($class,$functionToRun),$arguments);
I've tried the following which cause other problems and chased this no further
$class->$functionToRun($arguments);
			










!Codeception primer
http://codeception.com/docs/03-AcceptanceTests


Typically you would use webdriver in your acceptance tests, webdriver url configuration is written to the acceptance.suite.yml file when tests are staged and run.
The acceptance.suite.yml file from your test folder is used as a template so configuration values other than url can be modified there.
This allows the person writing tests for a given module to decide what helper classes they want to use.
http://codeception.com/docs/modules/WebDriver

Acceptance Tests
Acceptance test drive the user interface to validate the success of user stories against the software.
Acceptance tests are written as *Cest.php files
They provide _before and _after methods
All public functions whose name starts with 'test' are run as test cases.
Acceptance tests can provide coverage a much of the code base in broad strokes.
Acceptance tests are more vulnerable to change than API based testing as per unit or functional tests.


Unit Tests
Acceptance tests are written as *Test.php files
They provide _before and _after methods
All public functions whose name starts with 'test' are run as test cases.
Functions are scraped from the test file into a codeception parent class.
All public functions whose name starts with 'helper' are made available in the new class scope.
Unit tests are written with full knowledge of the system internals.
By looking strictly at input and postconditions of a function, unit tests can concisely fill or stub requirements.
Unit tests are typically more robust that acceptance tests in response to change in the code base.





! aliases to insert into /root/.bashrc
alias testPush='cd /root/testrepository_bitbucket; echo "dd" >> readme.txt; git add .; git commit -m eeek; git push; cd -'
alias testRun='function _blah(){ echo -n "$1" ; echo -e -n "\t"; echo -n "$2"; };_blah > /var/www/projects/testrunner/dev/webhooks/jobs/fakejob'
alias testLogs='tail -f /var/log/apache2/other_vhosts_access.log  /var/log/apache2/error.log /root/mbox &'
alias testJobs='cat /var/www/projects/testrunner/dev/webhooks/jobs/*'

#also add testrunner to path
export PATH=$PATH:/var/www/projects/testrunner/dev/

# also edit crontab and add
* * 	* * * root /var/www/projects/testrunner/dev/webhooks/cronjob.sh

!!TestRunner

This project facilitates running test suites in a code base.
Codeception underpins the test run framework.

It helps with finding and running many test folders across a code base.
It helps with managing configuration required for running tests. The TestConfig 
class provides access to a unified configuration tree derived from (in order) defaults, environment variables and parameters(arguments or GET).
It helps by iterating discovered test folders to run tests. 
For each test folder
- the folder contents are copied to the $testStagingPath directory so test artifacts are not left in the main source tree.
- the codeception.yml and *.suite.yml are read and rewritten using configuration values
- some of the configuration items are set into environment variables for use in tests
- the tests matching $testSuite and $test  are run 
- the output of the test run is copied to a unique folder name inside $testOutputPath


!Quickstart
runtests.bat   -   will run the tests for the TestRunner 
runtests.bat testPath:c:\inetpub\wwwroot\cmfive  - will look for and run tests in the cmfive source tree

runtests.bat can be added to your PATH environment variable and run from anywhere
alternatively you can install the test runner under a webserver and hit the index.php file. GET variables are used as configuration arguments.
setenvironment.bat can be used to set persistent global environment variables and restart IIS web server. This must be run in a console running as Administrator.

Parameters are tokenised by the first : to match parameter names. Allowed parameters include
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
(DEFAULT $testRunnerPath/staging)
'testIncludePath'	- path set into environment for including source code inside tests. 
					By using chdir to this path in your test, relative links within an existing code base will work.   
					(DEFAULT the same as $testPath after parameters unless set otherwise)  
					** WARNING Because this defaults to $testPath, selection of tests inside the main source tree using $testPath (perhaps as a parameter) will upset the default for this value and it will need to be set explicitly eg runtests.bat testPath:/src/stuff testIncludePath:/src.
'testLogFiles'		- comma seperated list of files that should be compared before and after testing. any additions are shown in test output.					

'codeception'		- command to run codeception  (DEFAULT $testRunnerPath/composer/bin/codeception)
'phantomjs'			- path to phantomjs binary   (DEFAULT $testRunnerPath/vendor/jakoch/phantomjs/bin)


**WARNING when using concurrent sessions of the test runner app, you must make sure that the $testStaging and $testOutput paths are unique to each concurrent session.

!Environment variables
The test runner can derive its configuration from environment variables.
Configuration values can be put into a csv file and loaded into environment variables.
Once the configuration values are set into local and global environment variables, the IIS web server is restarted so it picks up the changes.

setenvironment.bat steveDev - will load configuration values from $testRunnerPath/environment.steveDev.csv into environment variables
setenvironment.bat c:\environments\environment.steveDev.csv  will load configuration values from c:\environments\environment.steveDev.csv into environment variables



!Codeception primer


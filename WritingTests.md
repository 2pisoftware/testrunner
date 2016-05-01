# Writing Tests for CmFive with Codeception

## Quickstart
### Prerequisite: 
- Install docker
- Checkout https://github.com/2pisoftware/docker-cmfive 
- In the docker-cmfive project, edit the compose/cmfive/docker-compose.yml file to change the volume mapping for www to the cmfive installation on your local host.
`  volumes:
    - ../../web:/var/www`
- Open a Docker CLI shell and use the bin/docker-manager script (dm for short) to start a cmfive test environment
`dm up cmfive mysite`  
**The first time this command is run it will download a ~400MB image file.**
- Run tests by executing a bash shell(kitematic exec button then type `su`) in the docker testrunner instance and typing 
`/runtests.sh`

### Create a test
- Copy the example module tests folder to your module
- Rename acceptance/exampleCest.php to something suitable for your module and remove the other test files.
- Using exampleCest as a base, write a test to open the entry page to your module and click through the steps for the simplest use case.
- Run individual tests by executing a bash shell in the docker testrunner instance and typing 
`/runtests.sh testPath:<pathToYourModule> testSuite:acceptance test:<testClassName eg exampleCest>`


## Overview
To write a test for CmFive, create a php file containing tests complying with the codeception framework.

Tests live in the main cmfive git repository. 
Each module may have a tests folder containing tests. There are also tests in /system/tests.	
The testrunner package collates and runs tests for all modules.

Acceptance tests operate on the  CmFive user interface via Selenium Webdriver and describe the actions taken by a user.
In general acceptance tests provide the easiest way to test that the feature will work for a user.

Unit tests are written to check (and formalise) the behavior of a functions.
Unit tests are written with insight into the parameters, return values and conditional paths of a function.
A unit test can comprehensively validate that a function complies with pre and post conditions.

Commits to CmFive trigger automated testing with notification by email.

[Working with docker](http://codeception.com/docs/modules/WebDriver) to run a local test environment gives developers the flexibility to run individual tests.


## Codeception Framework

The easiest way to setup a module for testing is to copy the tests folder from the example module and delete all the tests.

Each tests folder contains a structure determined by the codeception framework and includes 

- unit.suite.yml - configuration for unit tests, rewritten with config by testrunner
- acceptance.suite.yml - configuration for unit tests, rewritten with config by testrunner
- unit/ - unit test files in here
- acceptance/ - acceptance test files in here
- _support - php classes providing test helpers, test runner copies global support files here 

Codeception tests files must be named according to the convention XXXXCest.php

Each test file contains a single class with the same name as the php file ie XXXCest

All public functions of the class (except those beginning with _) are run as tests.

A test file may also include _before and _after methods which are called before and after each test function.

A constructor function can be used for code to run once before all tests in a class.

Codeception allows for helper modules to be added via the yml configuration files. To start, use the configuration from the example module. These files are rewritten by our testrunner to override some configuration values based on environment.

Public test methods all pass a parameter $I which can be used to access codeception features.
eg 
`$I->click('Example');`
$name = 
`$I->assertTrue($name=='fred')`
		
		
See the [codeception documentation](http://codeception.com/docs) for details.

For acceptance testing, the [webdriver module](http://codeception.com/docs/modules/WebDriver) is most important.

## Acceptance Tests
Acceptance tests should be written to at least script a success story for using a feature.
The return on effort diminishes with trying to cover all UI outcomes depending on the importance of the business case.

Tests that drive the user interface must provide an start page using something like
`$I->amOnPage("/")`

Typically $I->click, $I->fillField and other WebDriver methods are used to script a path through a feature.

The shared CmFiveTestHelper class provides a bunch of useful shortcuts for developing tests for CmFive.

- navigateTo($I,$menu,$submenu)
- findTableRowMatching($I,$columnNumber,$matchValue)
- login/logout
- fillForm, fillXXX for filling CmFive style RTE, date selectors, autocomplete etc.
- helper functions for base objects in the sytem
  - createUser, deleteUser, setUserPermissions, createAndLoginUser, updateUser
  - createUserGroup, updateUserGroup, deleteUserGroup, addUserToUserGroup, removeUserFromUserGroup, setUserGroupPermissions
  - createTaskGroup, updateTaskGroup, deleteTaskGroup, addMemberToTaskGroup, removeMemberFromTaskGroup, updateMemberInTaskGroup, createTask
  - CRUD abstraction functions - createNewRecord, editRecord, deleteRecord
  

The database is cleared of all records except an admin user before each test.

All test data MUST be generated by driving the CmFive UI with WebDriver within a single test. A Helper method that creates a record can be run iteratively over sample data from arrays or even sample data generators.

## Acceptance Tests and Javascript

Interacting with dynamic elements of a page are beyond fillField or selectOption and may require working with the API of the dynamic element to update it's value. Typically Date selectors and RTE inputs provide this challenge.

The executeJS method allows a script to be run in the page.

The helper functions in CmFiveTestHelper offer solutions for the javascript UI widgets provided by the core system.

The executeJS method provides for 
`$I->executeJS('window.confirm = function(){return true;}');`

Bugs with selenium managing popup dialogs has led to general approach to delete confirmations of overriding the global confirm method to return true immediately.

Manipulating javascript UI widgets is potentially brittle to changes and upgrades and while sometimes necessary should be approached with caution. 


## Unit Tests

Unit tests should be written for most of the files in the models folder of a module.
In particular Service classes should be prioritised.

To write unit tests for CmFive, 

- the framework classes need to be loaded.
- some request variables need to be injected.
- some methods need to be stubbed out.

Examples in system/tests/unit including WebTest.php and DbObjectTest.php show how this is done.

Developing unit tests is an opportunity to improve encapsulation across a code base by exposing global dependancies.

It is useful to think less about the name of the function and more about input, paths and outputs.




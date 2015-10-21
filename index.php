Test Runner
<?php
if (!defined('DS'))  define('DS', DIRECTORY_SEPARATOR);

//print_r( $argv);
//die();

/**********************************
 * Determine the path to the currently running php script
 * Normalises the path to allow for the case where getcwd()
 * returns the directory the script was called from rather than
 * the actual path to the script.
 **********************************/
function getScriptFolder() {
	global $argv;
	// web server case
	$dir=getcwd();
	// cli case
	if (strlen(trim($argv[0]))>0) {
		$dir = dirname(getcwd() . DS . $argv[0]);
		$curDir = getcwd();
		chdir($dir);
		$dir = getcwd();
		chdir($curDir);
	}
	return $dir;
}
require(getScriptFolder().DS.'src'.DS.'FileSystemTools.php');
require(getScriptFolder().DS.'src'.DS.'TestConfig.php');
require(getScriptFolder().DS.'src'.DS.'TestRunner.php');
require(getScriptFolder().DS.'composer'.DS.'vendor'.DS.'autoload.php');


// overlay arguments and post variables onto configuration
TestConfig::init();
// handle CLI arguments
if (php_sapi_name() == 'cli') {
	// if the first argument matches an environment file, load that environment
	// treat additional parameters as 1. testPath 2. testSuite 3. test
	if (array_key_exists(1,$argv) &&  strlen(trim($argv[1]))>0) {
		if (is_file(getScriptFolder().DS."environment.".trim($argv[1]).".csv")) {  // load environment variables file
			if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
				exec(getScriptFolder().DS."setenvironment.bat ".$argv[1]);
				TestConfig::reload();
				echo "SET ENV";
				print_r(TestConfig::$config);
			} else {
				// TODO WRITE BASH VERSION
				// print_r(array(exec(getScriptFolder().DS."setenvironment.sh")));
			}
			if (array_key_exists(2,$argv) && strlen(trim($argv[2]))>0) {
				if (TestRunner::isTestSuiteFolder($argv[2])) {
					TestConfig::$config['testPath']=trim($argv[2]);
					if (array_key_exists(3,$argv) && strlen(trim($argv[3]))>0) TestConfig::$config['testSuite']=trim($argv[3]);
					if (array_key_exists(4,$argv) && strlen(trim($argv[4]))>0) TestConfig::$config['test']=trim($argv[4]);
				} else {
					if (array_key_exists(2,$argv) && strlen(trim($argv[2]))>0) TestConfig::$config['testSuite']=trim($argv[2]);
					if (array_key_exists(3,$argv) && strlen(trim($argv[3]))>0) TestConfig::$config['test']=trim($argv[3]);
				}
			}
		} else {
			if (TestRunner::isTestSuiteFolder($argv[1])) {
				TestConfig::$config['testPath']=trim($argv[1]);
				if (array_key_exists(2,$argv) && strlen(trim($argv[2]))>0) TestConfig::$config['testSuite']=trim($argv[2]);
				if (array_key_exists(3,$argv) && strlen(trim($argv[3]))>0) TestConfig::$config['test']=trim($argv[3]);
			} else {
				if (array_key_exists(1,$argv) && strlen(trim($argv[1]))>0) TestConfig::$config['testSuite']=trim($argv[1]);
				if (array_key_exists(2,$argv) && strlen(trim($argv[2]))>0) TestConfig::$config['test']=trim($argv[2]);
			}
		}
	}
// handle POST vars	
} else {
	if (strlen(trim($_GET['env']))>0) {
		if (is_file(getScriptFolder().DS."environment.".trim($_GET['env']).".csv")) {  // load environment variables file
			if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
				array(exec(getScriptFolder().DS."setenvironment.bat ".$_GET['env']));
				TestConfig::reload();
			} else {
				// TODO WRITE BASH VERSION
				// print_r(array(exec(getScriptFolder().DS."setenvironment.sh")));
			}
		}
	}
	if (strlen(trim($_GET['testPath']))>0) TestConfig::$config['testPath']=trim($_GET['testPath']);
	if (strlen(trim($_GET['testSuite']))>0) TestConfig::$config['testSuite']=trim($_GET['testSuite']);
	if (strlen(trim($_GET['test']))>0) TestConfig::$config['test']=trim($_GET['test']);
}

echo " in ". TestConfig::getConfig('testPath');
print_r(TestConfig::getConfigs());
//die();
// RUN TESTS
// clean combined output path
FileSystemTools::prune(TestConfig::getConfig('testOutputPath'));
// find all test folders
foreach( TestRunner::findTestFolders(TestConfig::getConfig('testPath')) as $key=>$folder) {
	// run all test suites in each test folder
	echo "<hr>TEST FOLDER ".$folder."<br>";
	TestRunner::runTests($folder);
}

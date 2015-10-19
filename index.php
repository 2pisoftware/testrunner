Test Runner
<?php
if (!defined('DS'))  define('DS', DIRECTORY_SEPARATOR);

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
require(getScriptFolder().DS.'lib'.DS.'Spyc.php');

//print_r(array(exec('pwd')));
//print_r(scandir('.'));
//die();
echo " in ". TestConfig::getConfig('testPath');
//echo "Running all tests in ". TestConfig::getConfig('testPath')."<br>";
//die();
		
try {
	//$cmd = TestConfig::getConfig('testCommand'); //$_GET['command'];
	$cmd ='run';
	if ($cmd=="something") {
		
	} else  if ($cmd=="run") {
		// RUN TESTS
		// clean combined output path
		FileSystemTools::prune(TestConfig::getConfig('testOutputPath'));
		// find all test folders
		foreach( TestRunner::findTestFolders(TestConfig::getConfig('testPath')) as $key=>$folder) {
			// run all test suites in each test folder
			echo "<hr>TEST FOLDER ".$folder."<br>";
			TestRunner::runTests($folder);
		}
	} else {
		// LIST
		print_r(TestRunner::findTests(TestConfig::getConfig('testPath')));
	}
	
	
} catch (Exception $e) {
	var_dump($e);
}

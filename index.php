<?php
echo "eek";
$output=[];
$output[]='Test Runner';

if (!defined('DS'))  define('DS', DIRECTORY_SEPARATOR);

//print_r( $argv);
//die();

/**********************************
 * Determine the path to the currently running php script
 * Normalises the path to allow for the case where getcwd()
 * returns the directory the script was called from rather than
 * the actual path to the script.
 **********************************/

$testRunnerPath=dirname(__FILE__);
require($testRunnerPath.DS.'src'.DS.'FileSystemTools.php');
require($testRunnerPath.DS.'src'.DS.'TestConfig.php');
require($testRunnerPath.DS.'src'.DS.'TestRunner.php');
require($testRunnerPath.DS.'composer'.DS.'vendor'.DS.'autoload.php');


// overlay arguments and post variables onto configuration
TestConfig::init();
TestConfig::$config['testRunnerPath']=$testRunnerPath;
// handle CLI arguments
if (php_sapi_name() == 'cli') {
	// check for environment read and do that first
	foreach($argv as $aKey =>$argument) {
		$argumentParts=explode(':',$argument);
		if ($argumentParts[0]=='env') {
			if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
				$output[]="SET FROM ENVIRONMENT ".implode(":",array_slice($argumentParts,1));
				exec($testRunnerPath.DS."setenvironment.bat ".implode(":",array_slice($argumentParts,1)));
				TestConfig::reload();
			} else {
				// TODO WRITE BASH VERSION
				// print_r(array(exec($testRunnerPath.DS."setenvironment.sh")));
			}
		}
	}
	// set any legal parameters
	foreach($argv as $aKey =>$argument) {
		$argumentParts=explode(':',$argument);
		if (in_array(trim($argumentParts[0]),TestConfig::$legalParameters)) {
			TestConfig::$config[$argumentParts[0]]=implode(':',array_slice($argumentParts,1));
		}
	}
	
// handle POST vars	
} else {
	// check environment read first
	if (array_key_exists('env',$_GET) && strlen(trim($_GET['env']))>0) {
		if (is_file($testRunnerPath.DS."environment.".trim($_GET['env']).".csv")) {  // load environment variables file
			if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
				array(exec($testRunnerPath.DS."setenvironment.bat ".$_GET['env']));
				TestConfig::reload();
			} else {
				// TODO WRITE BASH VERSION
				// print_r(array(exec($testRunnerPath.DS."setenvironment.sh")));
			}
		}
	}
	// set any legal parameters
	foreach (TestConfig::$legalParameters as $key=>$parameterName) {
		if (array_key_exists($parameterName,$_GET) && strlen(trim($_GET[$parameterName]))>0) {
			TestConfig::$config[$parameterName]=trim($_GET[$parameterName]);
		}
	}
}
// if no include path set, use source search path
if (!array_key_exists('testIncludePath',TestConfig::$config)) TestConfig::$config['testIncludePath']=TestConfig::$config['testPath'];
		
putenv('thisTestRun_testRunnerPath='.$testRunnerPath);
putenv('thisTestRun_testIncludePath='.TestConfig::getConfig('testIncludePath'));

foreach (TestConfig::$config as $k=>$v) {
	$output[]="CONF ".$k."=".$v;
}

// RUN TESTS
// clean combined output path
FileSystemTools::prune(TestConfig::getConfig('testOutputPath'));
// find all test folders
$output[]="FOUND TEST FOLDERS";
$testFolders=TestRunner::findTestFolders(TestConfig::getConfig('testPath'));

// show all test folders
foreach ($testFolders as $k=>$v) {
	$output[]=$v;
}
// run all test suites in each test folder
foreach( $testFolders as $key=>$folder) {
	// take a snapshot of all log files
	$snapshots=[];
	if (!empty(TestConfig::getConfig('testLogFiles'))) {
		foreach (explode(",",TestConfig::getConfig('testLogFiles')) as $k => $logFile) {
			$snapshots[$logFile]=FileSystemTools::tail($logFile,30);
		}
	}
	// run the test
	$output=array_merge($output,TestRunner::runTests($folder));
	// CHECK PHP LOG FILE
	if (!empty(TestConfig::getConfig('testLogFiles'))) {
		foreach (explode(",",TestConfig::getConfig('testLogFiles')) as $k => $logFile) {
			$lines=FileSystemTools::checkChangesToFile($snapshots[$logFile],$logFile);
			if (count($lines)>0)  {
				$output[]='LOG FILE '.$logFile;
				$output=array_merge($output,$lines);
			}

		}
	}
// $output=array_merge($output,
}


// FINALLY RENDER OUTPUT
if (php_sapi_name() == 'cli') {
	echo implode("\n",$output);
} else {
	echo '<pre>'.implode("\n",$output).'</pre>';
}

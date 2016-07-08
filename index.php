<?php

$output=[];
$output[]='Test Runner';

if (!defined('DS'))  define('DS', DIRECTORY_SEPARATOR);

$testRunnerPath=dirname(__FILE__);
require_once($testRunnerPath.DS.'src'.DS.'FileSystemTools.php');
require_once($testRunnerPath.DS.'src'.DS.'TestConfig.php');
require_once($testRunnerPath.DS.'src'.DS.'TestRunner.php');
require_once($testRunnerPath.DS.'src'.DS.'CmFiveInstaller.php');

 
require_once($testRunnerPath.DS.'composer'.DS.'vendor'.DS.'autoload.php');


// overlay arguments and post variables onto configuration
TestConfig::init();
TestConfig::$config['testRunnerPath']=$testRunnerPath;
// handle CLI arguments
if (php_sapi_name() == 'cli') {
	// set any legal parameters
	foreach($argv as $aKey =>$argument) {
		$argumentParts=explode(':',$argument);
		if (in_array(trim($argumentParts[0]),TestConfig::$legalParameters)) {
			TestConfig::$config[$argumentParts[0]]=implode(':',array_slice($argumentParts,1));
		}
	}
// handle POST vars	
} else {
	die();
	// set any legal parameters
	foreach (TestConfig::$legalParameters as $key=>$parameterName) {
		if (array_key_exists($parameterName,$_GET) && strlen(trim($_GET[$parameterName]))>0) {
			TestConfig::$config[$parameterName]=trim($_GET[$parameterName]);
		}
	}
}
// if no include path set, use source search path
if (!array_key_exists('testIncludePath',TestConfig::$config)) TestConfig::$config['testIncludePath']=TestConfig::$config['testPath'];
// ensure http[s]:// in testUrl
if (array_key_exists('testUrl',TestConfig::$config) && !(substr(TestConfig::$config['testUrl'],0,7)=='http://' || substr(TestConfig::$config['testUrl'],0,8)=='https://' ) ) {
	TestConfig::$config['testUrl']='http://'.TestConfig::$config['testUrl'];
}
		
putenv('thisTestRun_testRunnerPath='.$testRunnerPath);
putenv('thisTestRun_testIncludePath='.TestConfig::getConfig('testIncludePath'));

// install CM5 - config and database
$output[]="-------------------------------------------------";
$output[]="TEST CONFIGURATION ";
$output[]="-------------------------------------------------";
foreach (TestConfig::$config as $k=>$v) {
	$output[]=$k."=".$v;
}
if (
	(empty(TestConfig::$config['skipInstall']) || !$config['skipInstall'])
	 && !empty(TestConfig::$config['cmFivePath'])) {
	$output[]="-------------------------------------------------";
	$installer= new CmFiveInstaller();
	$output=array_merge($output,$installer->install(TestConfig::$config));
	$output[]="-------------------------------------------------";
	$output[]="INSTALLED CMFIVE";
	$output[]="-------------------------------------------------";
}

// DUMP OUTPUT
if (php_sapi_name() == 'cli') {
	echo implode("\n",$output);
	$output=array();
}
// clean combined output path
FileSystemTools::prune(TestConfig::getConfig('testOutputPath'));
// find all test folders
$output[]="-------------------------------------------------";
$output[]="FOUND TEST FOLDERS";
$output[]="-------------------------------------------------";
$testFolders=TestRunner::findTestFolders(TestConfig::getConfig('testPath'));
// show all test folders
foreach ($testFolders as $k=>$v) {
	$output[]=$v;
}
$output[]="-------------------------------------------------";
// DUMP OUTPUT
if (php_sapi_name() == 'cli') {
	echo "\n".implode("\n",$output)."\n";
	$output=array();
}
// run all test suites in each test folder
$passedAllTests=true;
foreach( $testFolders as $key=>$folder) {
	// take a snapshot of all log files
	$snapshots=[];
	if (!empty(TestConfig::getConfig('testLogFiles'))) {
		foreach (explode(",",TestConfig::getConfig('testLogFiles')) as $k => $logFile) {
			$snapshots[$logFile]=FileSystemTools::tail($logFile,30);
		}
	}
	// run the test
	$output[]='RUN TESTS '.$folder;
	$testResult=TestRunner::runTests($folder);
	if (!$testResult['result'])  {
		$passedAllTests=false;
		$output[] = "TEST FAILED";
	} else {
		$output[] = "TEST PASSED";
	}
	$output=array_merge($output,$testResult['output']);
	// CHECK PHP LOG FILE
	if (!empty(TestConfig::getConfig('testLogFiles'))) {
		foreach (explode(",",TestConfig::getConfig('testLogFiles')) as $k => $logFile) {
			$lines=FileSystemTools::checkChangesToFile($snapshots[$logFile],$logFile);
			if (count($lines)>0)  {
				$output[]="-------------------------------------------------";
				$output[]='LOG FILE '.$logFile;
				$output[]="-------------------------------------------------";
				$output=array_merge($output,$lines);
				$output[]="-------------------------------------------------";
			}
		}
	}
	if (php_sapi_name() == 'cli') {
		echo "\n".implode("\n",$output)."\n";
		$output=array();
	}
// $output=array_merge($output,
}


// FINALLY RENDER OUTPUT
if (php_sapi_name() == 'cli') {
	echo "\n".implode("\n",$output);
} else {
	echo '<pre>'.implode("\n",$output).'</pre>';
}

if ($passedAllTests) {
	exit(0);
} else {
	exit(1);
}

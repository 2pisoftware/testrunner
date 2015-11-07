<?php 
/*****************************
 * Tools to help find and run tests in source code
 * Tests must be organised into suites where each suite is a folder
 * containing a *.suite.yml file (usually the same name as the folder)
 * The suite.yml file allows codeception modules to be enabled per suite
 *****************************/

class TestRunner {
	
	/*****************************
	 * Recursively scan a folder for test folders containing at least 
	 * one test suite
	 * @return Array (of test file paths)
	 *****************************/
	static function runTests($testFolder) {
		$output=[];
		// clean staging
		$cmds=array();
		$staging=TestConfig::getConfig('testStagingPath');
		//FileSystemTools::setPermissionsAllowEveryone($staging);
		//FileSystemTools::setPermissionsAllowEveryone(TestConfig::getConfig('testOutputPath'));
		$passedAllTests=true;
			
		// some sanity checking before pruning
		if (strlen(trim($staging))>0) {
			// clean up staging
			FileSystemTools::prune($staging);
			//return array();
			// create staging location
			@mkdir($staging.DS.'tests',0777,true);
			@mkdir($staging.DS.'tests'.DS.'_support',0777,true);
			// copy shared test support files
			FileSystemTools::copyRecursive(TestConfig::getConfig('testSharedSupportPath'),$staging.DS.'tests'.DS.'_support');
			//return array();
			// then copy over the top, test suites etc to staging
			FileSystemTools::copyRecursive($testFolder,$staging.DS.'tests');
			// ensure required test directories
			@mkdir($staging.DS.'tests'.DS.'_support',0777,true);
			@mkdir($staging.DS.'tests'.DS.'_output',0777,true);
			@mkdir($staging.DS.'tests'.DS.'_data',0777,true);
			@mkdir($staging.DS.'tests'.DS.'_support'.DS.'Helper',0777,true);
			TestConfig::writeCodeceptionConfig();
			$objects = glob($staging.DS.'tests'.DS.'*.suite.yml');
			if( sizeof($objects) > 0 ) {
				foreach( $objects as $file ) {
					if( $file == "." || $file == ".." )
						continue;
					TestConfig::writeWebDriverConfig($file);
				}
			}
			// copy installer sql to test data directory
			if (strlen(trim(TestConfig::getConfig('cmFivePath')))>0)  {
				copy(TestConfig::getConfig('cmFivePath').DS.'cache'.DS.'install.sql',$staging.DS.'tests'.DS.'_data'.DS.'dump.sql');
			}
			// build and run
			array_push($cmds,array('CODECEPTION BUILD',TestConfig::getConfig('codeception').' build '.' -c '.$staging));
			$testParam=TestConfig::getConfig('testSuite');
			$testParam.=(strlen(trim(TestConfig::getConfig('testSuite')))>0) ? ' '.TestConfig::getConfig('test') : '';
			array_push($cmds,array('CODECEPTION RUN',TestConfig::getConfig('codeception').' run '.' -c '.$staging.' '.$testParam));
			
			foreach ($cmds as $cmd) {
				if (php_sapi_name() == 'cli') {
					echo "-------------------------------------------------\n";
					echo $cmd[0]."\n";
					echo $cmd[1]."\n";
					echo "-------------------------------------------------\n";
				} else {
					$output[]="-------------------------------------------------";
					$output[]=$cmd[0];
					$output[]=$cmd[1];
					$output[]="-------------------------------------------------";
				}
				$handle = popen($cmd[1], "r");
				$detailsTest='';
				$errorActive=false;
				$testType='';
				while(!feof($handle)) {
					$buffer = fgets($handle);
					//$buffer = trim(htmlspecialchars($buffer));
					if (php_sapi_name() == 'cli') {
						echo $buffer;
					} else {
						$output[]= trim($buffer);
					}
				}
				$exitCode=pclose($handle);
				if ($exitCode>0) $passedAllTests=false; 
			}
			// save output files
			$testSuiteName=str_replace(':','_',str_replace(DS,'_',$testFolder));
			FileSystemTools::copyRecursive($staging.DS.'_output',TestConfig::getConfig('testOutputPath').DS.$testSuiteName);
		}
		//FileSystemTools::setPermissionsAllowEveryone($staging);
		//FileSystemTools::setPermissionsAllowEveryone(TestConfig::getConfig('testOutputPath'));
		
		return array('output'=>$output,'result'=>$passedAllTests);		
	}
	
	/*****************************
	 * Recursively scan a folder for test folders containing at least 
	 * one test suite
	 * @return Array (of test file paths)
	 *****************************/
	static function findTestFolders( $path) {
		$suites=array();
		if( is_dir($path) ) {
			if (TestRunner::isTestSuiteFolder($path)) {
				$stagingSuiteName=str_replace(':','_',str_replace(DS,'_',$path));
				$suites[$stagingSuiteName]=$path;
			}	
			$objects = scandir($path);
			if( sizeof($objects) > 0 ) {
				foreach( $objects as $file ) {
					if( $file == "." || $file == ".." )
						continue;
					if( is_dir($path.DS.basename($file) ) ) {
						if (TestRunner::isTestSuiteFolder($path.DS.basename($file))) {
							$stagingSuiteName=str_replace(':','_',str_replace(DS,'_',$path.DS.basename($file)));
							// ensure absolute file references
							$suites[$stagingSuiteName]=$path.DS.basename($file);
						}
						// recurse into folder
						$suites=array_merge($suites,TestRunner::findTestFolders($path.DS.basename($file)));
					}
				}
			}
		}
		return $suites;
	}
	

	function findTests($path) {
		return array();
	}
	
	/******************************************
	 * Check if at least one *.suite.yml file exists in this folder
	 * and return true if this folder contains test suites.
	 * @return boolean
	 *****************************************/
	static function isTestSuiteFolder($path) {
		$result=false;
		if (is_dir($path)) {
			$files=glob($path.DS.'*.suite.yml');
			if (count($files)>0) {
				$result=true;
			}
		}
		
		return $result;
	}

	/******************************************
	 * Check if a filename matches that expected for a test file
	 * (Test|Cest|Cept).php
	 * @return boolean
	 *****************************************/
	static function isTestFile($fileName) {
		$isTest=false;
		$parts=explode(DS,trim($fileName));
		if (count($parts)>0) { 
			$fileParts=explode(".",$parts[count($parts)-1]);
			if (count($fileParts)>1 && $fileParts[count($fileParts)-1]==="php") {
				$namePart=$fileParts[count($fileParts)-2];
				if (strlen($namePart)>4) {
					$nameTail=substr($namePart,strlen($namePart)-4);
					if ($nameTail==="Test" || $nameTail==="Cest" || $nameTail==="Cept") {
						$isTest=true;
					}
				}
			}
		}
		return $isTest;
	}
	
}

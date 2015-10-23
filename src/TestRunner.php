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
		// some sanity checking before pruning
		if (strlen(trim($staging))>0) {
			@mkdir($staging.DS.'tests',0777,true);
			// clean up staging
			FileSystemTools::prune($staging);
			//copy(TestConfig)
			// copy test suites etc to staging
			FileSystemTools::copyRecursive($testFolder,$staging.DS.'tests');
			TestConfig::writeCodeceptionConfig();
			$objects = glob($staging.DS.'tests'.DS.'*.suite.yml');
			if( sizeof($objects) > 0 ) {
				foreach( $objects as $file ) {
					if( $file == "." || $file == ".." )
						continue;
					TestConfig::writeWebDriverConfig($file);
				}
			}
			// build and run
			array_push($cmds,TestConfig::getConfig('codeception').' build '.' -c '.$staging);
			$testParam=TestConfig::getConfig('testSuite');
			$testParam.=(strlen(trim(TestConfig::getConfig('testSuite')))>0) ? ' '.TestConfig::getConfig('test') : '';
			array_push($cmds,TestConfig::getConfig('codeception').' run '.' -c '.$staging.' '.$testParam);
			$output[]='CODECEPTION BUILD/RUN';
			$output=array_merge($output,$cmds);
			foreach ($cmds as $cmd) {
				$handle = popen($cmd, "r");
				$detailsTest='';
				$errorActive=false;
				$testType='';
				while(!feof($handle)) {
					$buffer = fgets($handle);
					//$buffer = trim(htmlspecialchars($buffer));
					$output[]= trim($buffer);
				}
			}
			// save output files
			$testSuiteName=str_replace(':','_',str_replace(DS,'_',$testFolder));
			FileSystemTools::copyRecursive($staging.DS.'_output',TestConfig::getConfig('testOutputPath').DS.$testSuiteName);
		}
		return $output;		
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

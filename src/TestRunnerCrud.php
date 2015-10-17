<?php 
/*****************************
 * Tools to help find and run tests in source code
 * Tests must be organised into suites where each suite is a folder
 * containing a *.suite.yml file (usually the same name as the folder)
 * The suite.yml file allows codeception modules to be enabled per suite
 *****************************/

class TestRunner {
	
		
	static function runTests($testFolder) {
		// clean staging
		$cmds=array();
		$staging=TestRunner::getConfig('testStagingPath');
		echo "RUNNING staging at ".$staging."<BR>";
		// some sanity checking before pruning
		if (strlen(trim($staging))>0) {
			if (!is_dir($staging)) {
				@mkdir($staging);
			}
			echo "OK<br>";
			echo "RUNNING trim then copy ".$testFolder." to ".$staging."<BR>";
			// clean up staging
			TestRunner::prune($staging);
			// copy test suite to staging
			TestRunner::copyRecursive($testFolder,$staging);
			// build and run
			array_push($cmds,TestRunner::getConfig('codeception').' build');
			array_push($cmds,TestRunner::getConfig('codeception').' run');
			foreach ($cmds as $cmd) {
				echo $cmd;
				$handle = popen($cmd, "r");
				$detailsTest='';
				$errorActive=false;
				$testType='';
				while(!feof($handle)) {
					$buffer = fgets($handle);
					$buffer = trim(htmlspecialchars($buffer));
					echo $buffer;
				}
			}
			
		}		
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
					//echo $file."<br>";
					if( $file == "." || $file == ".." )
						continue;
					if( is_dir($file ) ) {
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
	





	/*****************************
	 * Scan a folder for *.suite.yml files and treat each such file as 
	 * a test suite.
	 * For each suite, 
	 * 	munge the path into a single filename and
	 * 		copy the matching folder and suite.yml file to 
	 * 		the a subfolder of the destination named as per munged path    
	 * Also copy anything in _support to $destination/_support
	 * and copy anything in _data to $destination/_data
	 * @return Array (of test file paths)
	 *****************************/
	static function copyTestSuites($testSuitePath,$destination,$suites) {
		echo "COPY ".$testSuitePath."<br>";
		// copy it all
		$files=glob($testSuitePath.DS.'*.suite.yml');
		if (count($files)>0) {
			// copy each suite and matching configuration file,
			// renaming to reflect the full path
			foreach ($files as $file) {
				echo "QQfile".$file.'<br>';
				$fileParts=explode(".",basename($file));
				$suiteName=$fileParts[count($fileParts)-3];
				$pathParts=explode(".",$file);
				$stagingSuiteName=str_replace(':','_',str_replace(DS,'_',implode(".",array_slice($pathParts,0,count($pathParts)-2))));
				echo "SS".$stagingSuiteName.'<br>';
				@mkdir(TestRunner::getConfig('testStagingPath').DS.$stagingSuiteName.DS,0777,true);
				// copy config file and modify name
				copy($file,$destination.DS.$stagingSuiteName.".suite.yml");
				// copy test files
				//if (TestRunner::isTestFile($path.DS.$file)) {
				TestRunner::copyRecursive($testSuitePath.DS.$suiteName,$destination.DS.$stagingSuiteName.DS);
				echo "COPY FOLDER "." ".$testSuitePath.DS.$suiteName."|TO|".$destination.DS.$stagingSuiteName.DS."<br>";
				//}
				// copy test support iles
				TestRunner::copyRecursive($testSuitePath.DS.'_support',$destination.DS);
				echo "COPY support "." ".$testSuitePath.DS.'_support'."|TO|".$destination.DS."<br>";
				$suites[$stagingSuiteName]=$testSuitePath.DS.$file;
			}
			
		}
		return $suites;
	}

	/*****************************
	 * Recursively scan a folder for test files and copy them to a 
	 * staging location
	 * @return Array (of test file paths)
	 *****************************/
	static function grabSuites( $path) {
		$suites=array();
		if( is_dir($path) ) {
			if (TestRunner::isTestSuiteFolder($path)) {
				$suites=array_merge($suites,TestRunner::copyTestSuites($path,TestRunner::getConfig('testStagingPath'),$suites));
			} else {
				$objects = scandir($path);
				if( sizeof($objects) > 0 ) {
					foreach( $objects as $file ) {
						if( $file == "." || $file == ".." )
							continue;
						if( is_dir( $path.DS.$file ) ) {
							if (TestRunner::isTestSuiteFolder($path.DS.$file)) {
								TestRunner::copyTestSuites($path.DS.$file,TestRunner::stagingFolder,$suites);
							}
						} else {
							// recurse into folder
							$suites=array_merge($suites,TestRunner::grabSuites( $path.DS.$file));
						}
					}
				}
			}
		}
		return $suites;
	}
	

	/*****************************
	 * Recursively scan a folder for test files 
	 * @return Array (of test file paths)
	 *****************************/
	static function findSuites( $path) {
		$suites=array();
		if( is_dir($path) ) {
			if (TestRunner::isTestSuiteFolder($path)) {
				// copy it all
				$stagingSuiteName=str_replace(':','_',str_replace(DS,'_',$path));
				$suites[$stagingSuiteName]=$path;
			} else {
				$objects = scandir($path);
				if( sizeof($objects) > 0 ) {
					foreach( $objects as $file ) {
						if( $file == "." || $file == ".." )
							continue;
						if( is_dir( $path.DS.$file ) ) {
							if (TestRunner::isTestSuiteFolder($path.DS.$file)) {
								// copy it all
								$stagingSuiteName=str_replace(':','_',str_replace(DS,'_',$path.DS.$file));
								$suites[$stagingSuiteName]=$path.DS.$file;
								$suites=array_merge($suites,TestRunner::findSuites( $path.DS.$file));
							}
						} else {
							$suites=array_merge($suites,TestRunner::findSuites( $path.DS.$file));
						}
					}
				}
			}
		}
		return $suites;
	}
	
	/*****************************
	 * Recursively copy a folder/file to a destination path
	 * @return Array (of copied files)
	 *****************************/
	static function copyRecursive( $path,$dest) {
		if (is_dir($dest)) {
			$tests=array();
			if( is_dir($path) ) {
				@mkdir( $dest );
				$objects = scandir($path);
				if( sizeof($objects) > 0 ) {
					foreach( $objects as $file ) {
						if( $file == "." || $file == ".." )
							continue;
						// go on
						if( is_dir( $path.DS.$file ) ) {
							TestRunner::copyRecursive( $path.DS.$file ,$dest.DS.$file );
						} else {
							copy( $path.DS.$file, $dest.DS.$file );
						}
					}
				}
			} elseif( is_file($path) ) {
				@mkdir( $dest );
				copy( $path, $dest);
			} 
		} else {
			// no such destination folder
		}	
	}


	function rmdirRecursive($dir) { 
		echo "rmdirRecursive ".$dir."<br>";
		if (is_dir($dir)) { 
			foreach(glob($dir . '/*') as $file)   { 
				if(is_dir($dir.DS.basename($file))) {
					TestRunner::rmdirRecursive($dir.DS.basename($file)); 
					rmdir($dir.DS.basename($file)); 
					echo "rmdir ".$dir.DS.basename($file)."<br>";
				} else {
					unlink($dir.DS.basename($file));
					echo "unlink ".$dir.DS.basename($file)."<br>";
				}
				
		   } 
		   rmdir($dir);
		}
	}
	
	function prune($dir) { 
		echo "pruneRecursive ".$dir."<br>";
		if (is_dir($dir)) { 
			foreach(glob($dir . '/*') as $file)   { 
				if(is_dir($dir.DS.basename($file))) {
					TestRunner::rmdirRecursive($dir.DS.basename($file)); 
					echo "start rmrec"."<br>";
				} else {
					unlink($dir.DS.basename($file));
					echo "unlink ".$dir.DS.basename($file)."<br>";
				}
			} 
	   } 
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
	
	static function getCM5Config($key,$systemPath,$configPath) {
		$config='';
		// disable and need robust approach to check availability before inclusion
		return $config;
		try {
			include_once($systemPath.DS.'classes'.DS.'Config.php');
			include_once($configPath);
		// read cm5 config and check testing is enabled
			$config=config::get($key);
		} catch (Exception $e) {}
		return $config;
	}
	
	/******************************************************
	 * Return a unified configuration value sourced from 
	 * 1. Environment variables
	 * or falling back to 2. CM5 configuration
	 *****************************************************/
	function getConfig($key,$systemPath='./system',$configPath='config.php') {
		if (strlen(trim(getenv($key))) > 0) {
			return getenv($key);
		} else {
			return TestRunner::getCM5Config($key,$systemPath,$configPath);
		}
	}
	
	
	static function writeCodeceptionConfig() {
		
	}
	
	
	static $codeceptionTemplate = <<<'EOD'
Example of string
spanning multiple lines
using nowdoc syntax.
EOD;
	


/**
 * Sample CM5 source hierarchy
 * 
 
modules
	staff
		tests
			unit
				unit.suite.dist.yml
				unit.suite.yml
				*[Cest|Cept|Test].php
			acceptance
			security	

system
	modules
		tasks
			tests
				unit
					*[Cest|Cept|Test].php
				acceptance
				security	
	classes
		tests
			
	tests
		webTest.php
		configTest.php
		

 */ 
/*****************************
	 * Recursively scan a folder for test files 
	 * If $stagingPath is a string, copy all test files to that folder
	 * @return Array (of test file paths)
	 *****************************/
	static function disgrabTests( $path,$stagingPath) {
		$tests=array();
		if( is_dir($path) ) {
			$objects = scandir($path);
			if( sizeof($objects) > 0 ) {
				foreach( $objects as $file ) {
					if( $file == "." || $file == ".." )
						continue;
					// go on
					if( is_dir( $path.DS.$file ) ) {
						$tests=array_merge($tests,TestRunner::findTests( $path.DS.$file));
					} else {
						if (TestRunner::isTestFile($path.DS.$file)) {
							$tests[]=$path.DS.$file;
						}
					}
				}
			}
			return $tests;
		} elseif( is_file($path) ) {
			return $tests;
		} else {
			return $tests;
		}
	}
	
}

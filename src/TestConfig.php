<?php 
/*****************************
 * Tools to manage configuration for testing
 *****************************/


class TestConfig {
	static  $config=null;
	
	private static function init() {
		if (!is_array(self::$config)) {
			self::$config=array();
			// defaults
			self::$config['testPath']=FileSystemTools::getScriptFolder().DS.'tests';
			self::$config['testSuite']='';
			self::$config['test']='';
			self::$config['testStagingPath']=FileSystemTools::getScriptFolder().DS.'staging';
			self::$config['testOutputPath']=FileSystemTools::getScriptFolder().DS.'output';
			self::$config['codeception']=FileSystemTools::getScriptFolder().DS.'composer'.DS.'bin'.DS.'codecept';
			// from cm5 if available (AS THIRD HIGHEST PRIORITY)
			
			// from environment variables  (AS SECOND HIGHEST PRIORITY)
			// what tests to run ?
			if (strlen(trim(getenv('testPath')))>0)  self::$config['testPath']=getenv('testPath');
			if (strlen(trim(getenv('testSuite')))>0)  self::$config['testSuite']=getenv('testSuite');
			if (strlen(trim(getenv('test')))>0)  self::$config['test']=getenv('test');
			// db config
			if (strlen(trim(getenv('dbDsn')))>0)  self::$config['dbDsn']=getenv('dbDsn');
			if (strlen(trim(getenv('dbUser')))>0)  self::$config['dbUser']=getenv('dbUser');
			if (strlen(trim(getenv('dbPassword')))>0)  self::$config['dbPassword']=getenv('dbPassword');
			// acceptance testing URL
			if (strlen(trim(getenv('testUrl')))>0)  self::$config['testUrl']=getenv('testUrl');
			// other paths
			if (strlen(trim(getenv('testStagingPath')))>0)  self::$config['testStagingPath']=getenv('testStagingPath');
			if (strlen(trim(getenv('testOutputPath')))>0)  self::$config['testOutputPath']=getenv('testOutputPath');
			if (strlen(trim(getenv('codeception')))>0)  self::$config['codeception']=getenv('codeception');
			// from arguments and post vars (AS HIGHEST PRIORITY)
			global $argv;
			// test path
			$argumentOffset=0;
			if (strlen(trim($argv[$argumentOffset+1]))>0) {
				// check for commands as first argument and save
				if (trim($argv[$argumentOffset+1])=="run") {
					$argumentOffset=1;
					self::$config['testCommand']=$argv[$argumentOffset+1];
				} else {
					// default run tests
					self::$config['testCommand']='run';
				}
				self::$config['testPath']=$argv[$argumentOffset+1];
			} if (strlen(trim($_POST['testPath']))>0) {
				self::$config['testPath']=$_POST['testPath'];
			}
			// test suite
			if (strlen(trim($argv[$argumentOffset+2]))>0) {
				self::$config['testSuite']=$argv[$argumentOffset+2];
			} if (strlen(trim($_POST['testSuite']))>0) {
				self::$config['testSuite']=$_POST['testSuite'];
			}
			// test 
			if (strlen(trim($argv[$argumentOffset+3]))>0) {
				self::$config['test']=$argv[$argumentOffset+3];
			} if (strlen(trim($_POST['test']))>0) {
				self::$config['test']=$_POST['test'];
			}
			
			
			TestConfig::writeCodeceptionConfig();
			//print_r(self::$config);
		}
	}
	
	private static function getCM5Config($key,$systemPath,$configPath) {
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
	public static function getConfig($key) {
		TestConfig::init();
		return str_replace('"','',self::$config[$key]);
	}
		
	static function writeCodeceptionConfig() {
		// codeception.yml write db parameters
		$data=Spyc::YAMLLoad(FileSystemTools::getScriptFolder().DS.'codeception.template.yml');
		if (!is_array($data)) $data=array();
		if (!is_array($data['modules'])) $data['modules']=array();
		if (!is_array($data['modules']['config'])) $data['modules']['config']=array();
		if (!is_array($data['modules']['config']['Db'])) $data['modules']['config']['Db']=array();
		$data['modules']['config']['Db']['dsn']=(strlen(trim(TestConfig::getConfig('dbDsn')))>0) ? TestConfig::getConfig('dbDsn') : '';
		$data['modules']['config']['Db']['user']=(strlen(trim(TestConfig::getConfig('dbUser')))>0) ? TestConfig::getConfig('dbUser') : '';
		$data['modules']['config']['Db']['password']=(strlen(trim(TestConfig::getConfig('dbPassword')))>0) ? TestConfig::getConfig('dbPassword') : '';
		$yaml = Spyc::YAMLDump($data);
		file_put_contents(FileSystemTools::getScriptFolder().DS.'codeception.yml',$yaml);
	}
	
	static function writeWebDriverConfig($configFile) {
		// write webdriver parameters
		$data=Spyc::YAMLLoad($configFile);
		if (is_array($data) && is_array($data['modules']) && is_array($data['modules']['enabled'])&& is_array($data['modules']['enabled']['WebDriver'])) {
			$data['modules']['enabled']['WebDriver']['url']=(strlen(trim(TestConfig::getConfig('testUrl')))>0) ? TestConfig::getConfig('testUrl') : '';
			$yaml = Spyc::YAMLDump($data);
			file_put_contents($configFile,$yaml);
		}
		
	}
	
	
	
}

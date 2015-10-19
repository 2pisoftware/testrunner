<?php 
/*****************************
 * Tools to manage configuration for testing
 *****************************/



class TestConfig {
	static  $config=null;
	
	public static function getConfigs() {
		TestConfig::init();
		return self::$config;
	}
	
	public static function init() {
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
			if (array_key_exists($argumentOffset+1,$argv) && strlen(trim($argv[$argumentOffset+1]))>0) {
				self::$config['testCommand']='';
				// check for commands as first argument and save
				if (trim($argv[$argumentOffset+1])=="run") {
					$argumentOffset=1;
					self::$config['testCommand']='run';
				} else {
					// default run tests
					self::$config['testCommand']='run';
				}
				self::$config['testPath']=$argv[$argumentOffset+1];
			} else if (array_key_exists('testPath',$_POST) && strlen(trim($_POST['testPath']))>0) {
				self::$config['testPath']=$_POST['testPath'];
			}
			//echo self::$config['testCommand'];
			//echo self::$config['testPath'];
			//die();
			// test suite
			if (array_key_exists($argumentOffset+2,$argv) && strlen(trim($argv[$argumentOffset+2]))>0) {
				self::$config['testSuite']=$argv[$argumentOffset+2];
			} else if (array_key_exists('testSuite',$_POST) && strlen(trim($_POST['testSuite']))>0) {
				self::$config['testSuite']=$_POST['testSuite'];
			}
			// test 
			if (array_key_exists($argumentOffset+3,$argv) && strlen(trim($argv[$argumentOffset+3]))>0) {
				self::$config['test']=$argv[$argumentOffset+3];
			} else if (array_key_exists('test',$_POST) && strlen(trim($_POST['test']))>0) {
				self::$config['test']=$_POST['test'];
			}
			
			
			TestConfig::writeCodeceptionConfig();
			//print_r(self::$config);
			//die();
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
		if (array_key_exists($key,self::$config)) {
			return str_replace('"','',self::$config[$key]);
		}
	}
		
	static function writeCodeceptionConfig() {
		print_r(TestConfig::$config);
		return;
		// codeception.yml write db parameters
		$baseFolder=FileSystemTools::getScriptFolder();
		$data=Spyc::YAMLLoad($baseFolder.DS.'codeception.template.yml');
		if (!is_array($data)) $data=array();
		if (!array_key_exists('modules',$data)) $data['modules']=array();
		if (!array_key_exists('config',$data['modules'])) $data['modules']['config']=array();
		if (!array_key_exists('Db',$data['modules']['config'])) $data['modules']['config']['Db']=array();
		$data['modules']['config']['Db']['dsn']=(strlen(trim(TestConfig::getConfig('dbDsn')))>0) ? TestConfig::getConfig('dbDsn') : '';
		$data['modules']['config']['Db']['user']=(strlen(trim(TestConfig::getConfig('dbUser')))>0) ? TestConfig::getConfig('dbUser') : '';
		$data['modules']['config']['Db']['password']=(strlen(trim(TestConfig::getConfig('dbPassword')))>0) ? TestConfig::getConfig('dbPassword') : '';
		$yaml = Spyc::YAMLDump($data);
		echo "<hr>";
		$codeceptionFile=$baseFolder.DS.'codeception.yml';
		echo $codeceptionFile;
		echo "<hr>";
		//file_put_contents(FileSystemTools::getScriptFolder().DS.'codeception.yml',$yaml);
		//file_put_contents("C:\\inetpub\\wwwroot\\testrunner\\codeception.yml",$yaml);
		file_put_contents($codeceptionFile,$yaml);
	}
	
	static function writeWebDriverConfig($configFile) {
		// write webdriver parameters
		$data=Spyc::YAMLLoad($configFile);
		if (is_array($data) && array_key_exists('modules',$data) && array_key_exists('enabled',$data['modules'])&& array_key_exists('WebDriver',$data['modules']['enabled']) && is_array($data['modules']['enabled']['WebDriver'])) {
			$data['modules']['enabled']['WebDriver']['url']=(strlen(trim(TestConfig::getConfig('testUrl')))>0) ? TestConfig::getConfig('testUrl') : '';
			$yaml = Spyc::YAMLDump($data);
			file_put_contents($configFile,$yaml);
		}
		
	}
	
	
	
}

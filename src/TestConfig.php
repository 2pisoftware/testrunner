<?php 
/*****************************
 * Tools to manage configuration for testing
 *****************************/

use Symfony\Component\Yaml\Yaml;


class TestConfig {
	static  $config=null;
	
	static $legalParameters=array('testOutputPath','testStagingPath','testPath','testSuite','test','codeception','phantomjs','testIncludePath','testUrl','testLogFiles','cmFivePath','port','driver','hostname','username','password','database');
	
	public static function init() {
		if (!is_array(self::$config)) {
			self::$config=array();
			TestConfig::_init();
		}
	}
	
	public static function reload() {
		TestConfig::_init();
	}
	
	
	public static function _init() {
		if (!array_key_exists('testRunnerPath',self::$config) || empty(self::$config['testRunnerPath'])) self::$config['testRunnerPath']=dirname(dirname(__FILE__));
		// defaults only if there are no existing values from previous init run
		if (!array_key_exists('testPath',self::$config) || empty(self::$config['testPath'])) self::$config['testPath']=self::$config['testRunnerPath'].DS.'tests';
		if (!array_key_exists('testSuite',self::$config)) self::$config['testSuite']='';
		if (!array_key_exists('test',self::$config)) self::$config['test']='';
		if (!array_key_exists('testStagingPath',self::$config) || empty(self::$config['testStagingPath'])) self::$config['testStagingPath']=self::$config['testRunnerPath'].DS.'staging';
		if (!array_key_exists('testSharedSupportPath',self::$config) || empty(self::$config['testSharedSupportPath'])) self::$config['testSharedSupportPath']=self::$config['testRunnerPath'].DS.'support';
		if (!array_key_exists('testOutputPath',self::$config) || empty(self::$config['testOutputPath'])) self::$config['testOutputPath']=self::$config['testRunnerPath'].DS.'output';
		if (!array_key_exists('codeception',self::$config) || empty(self::$config['codeception'])) self::$config['codeception']=self::$config['testRunnerPath'].DS.'composer'.DS.'bin'.DS.'codecept';
		if (!array_key_exists('phantomjs',self::$config) || empty(self::$config['phantomjs'])) self::$config['phantomjs']=trim(self::$config['testRunnerPath'].DS.'composer'.DS.'bin'.DS.'phantomjs'); 
		// from cm5 if available (AS THIRD HIGHEST PRIORITY)
		// TODO
		// from environment variables  (AS SECOND HIGHEST PRIORITY)
		// what tests to run ?
		if (strlen(trim(getenv('testPath')))>0)  self::$config['testPath']=getenv('testPath');
		if (strlen(trim(getenv('testIncludePath')))>0)  self::$config['testIncludePath']=getenv('testIncludePath');
		if (strlen(trim(getenv('testSuite')))>0)  self::$config['testSuite']=getenv('testSuite');
		if (strlen(trim(getenv('test')))>0)  self::$config['test']=getenv('test');
		// db config
		if (strlen(trim(getenv('port')))>0)  self::$config['port']=getenv('port');
		if (strlen(trim(getenv('driver')))>0)  self::$config['driver']=getenv('driver');
		if (strlen(trim(getenv('hostname')))>0)  self::$config['hostname']=getenv('hostname');
		if (strlen(trim(getenv('username')))>0)  self::$config['username']=getenv('username');
		if (strlen(trim(getenv('password')))>0)  self::$config['password']=getenv('password');
		if (strlen(trim(getenv('database')))>0)  self::$config['database']=getenv('database');
		// acceptance testing URL
		if (strlen(trim(getenv('testUrl')))>0)  self::$config['testUrl']=getenv('testUrl');
		// other paths
		if (strlen(trim(getenv('cmFivePath')))>0)  self::$config['cmFivePath']=getenv('cmFivePath');
		if (strlen(trim(getenv('testLogFiles')))>0)  self::$config['testLogFiles']=getenv('testLogFiles');
		if (strlen(trim(getenv('testStagingPath')))>0)  self::$config['testStagingPath']=getenv('testStagingPath');
		if (strlen(trim(getenv('testSharedSupportPath')))>0)  self::$config['testSharedSupportPath']=getenv('testSharedSupportPath');
		if (strlen(trim(getenv('testOutputPath')))>0)  self::$config['testOutputPath']=getenv('testOutputPath');
		if (strlen(trim(getenv('codeception')))>0)  self::$config['codeception']=getenv('codeception');
		if (strlen(trim(getenv('phantomjs')))>0)  self::$config['phantomjs']=getenv('phantomjs');
		//print_r(self::$config);
		//die();
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
	
	/******************************************************
	 * Set a configuration value 
	 ****************************************************
	public static function setConfig($key,$value) {
		TestConfig::init();
		self::$config[$key]=$value;
	}*/
		
	static function writeCodeceptionConfig() {
		// codeception.yml write db parameters
		$baseFolder=TestConfig::getConfig('testRunnerPath');
		// load template
		$yaml=file_get_contents($baseFolder.DS.'codeception.template.yml');
		$data = Yaml::parse($yaml);
		// ensure structure in template array
		if (is_array($data)) {
			//if (!array_key_exists('modules',$data)) $data['modules']=array();
			//if (!array_key_exists('enabled',$data['modules'])) $data['modules']['enabled']=array();
			//if (!array_key_exists('Db',$data['modules']['enabled'])) $data['modules']['enabled']['Db']=array();
			// set db connection details
			$portNumber=TestConfig::getConfig('port');
			$port = isset($portNumber) && !empty($portNumber) ? ";port=".$portNumber : "";
			$url = TestConfig::getConfig('driver').":host=".TestConfig::getConfig('hostname').";dbname=".TestConfig::getConfig('database').$port;
	   
			$data['modules']['config']['Db']['dsn']=$url;
			$data['modules']['config']['Db']['user']=(strlen(trim(TestConfig::getConfig('username')))>0) ? TestConfig::getConfig('username') : '';
			$data['modules']['config']['Db']['password']=(strlen(trim(TestConfig::getConfig('password')))>0) ? TestConfig::getConfig('password') : '';
			$data['extensions']['config']['Codeception\Extension\Phantoman']['path']=TestConfig::getConfig('phantomjs');
			$yaml = Yaml::dump($data);
		}
		$codeceptionFile=TestConfig::getConfig('testStagingPath').DS.'codeception.yml';
		file_put_contents($codeceptionFile,$yaml);
	}
	
	static function writeWebDriverConfig($configFile) {
		// write webdriver parameters
		$data=Yaml::parse(file_get_contents($configFile));
		// PROBLEM IN IF STATEMENT BELOW CHECKING FOR WEBDRIVER WHICH IS ACTUALLY A KEY ONE LEVEL DEEPER INSIDE ONE OF?? NUMERIC KEYS
		if (is_array($data) && array_key_exists('modules',$data) && array_key_exists('enabled',$data['modules']) ) {
			// && is_array($data['modules']['enabled']['WebDriver']
			foreach ($data['modules']['enabled'] as $k=>$moduleName) {
				if (is_array($moduleName) && array_key_exists('WebDriver',$moduleName)) { 
					$data['modules']['enabled'][$k]['WebDriver']['url']=(strlen(trim(TestConfig::getConfig('testUrl')))>0) ? TestConfig::getConfig('testUrl') : '';
					$yaml = Yaml::dump($data);
					file_put_contents($configFile,$yaml);
					break;
				}
			}
		}
	}
	
	
	
}

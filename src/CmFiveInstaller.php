<?php

if (!defined('DS'))  define('DS', DIRECTORY_SEPARATOR);

$testRunnerPath=dirname(dirname(__FILE__));
  
if (php_sapi_name() == 'cli') {
	// now we have all required parameters
	// check if we need to automatically run the installer on inclusion of this file
	if ($argv[1]=="install") {
		$response=CmFiveInstaller::findConfig($argv);
		if (count($response['errors'])==0) {
			$config=$response['config'];
			$installer=new CmFiveInstaller();
			echo implode("\n",$installer->install($config));
		} else {
			echo implode("\n",$response['errors']);
		}
	}
}
	
class CmFiveInstaller {
	
	var $initialised=false;
	
	public function init($config) {
		if (!$this->initialised)  {
			chdir($config['cmFivePath']);
			require_once('system'.DS.'db.php');
			require_once('system'.DS.'modules'.DS.'install'.DS.'models'.DS.'InstallService.php');
			require_once('system'.DS.'modules'.DS.'auth'.DS.'models'.DS.'User.php');
			$this->pdo=InstallService::getConnection($config['port'],$config['driver'],$config['hostname'],$config['username'],$config['password'],$config['database']);
			$this->initialised=true;
		}
	}


	public static function findConfig($arguments) {
		$errors=[];
		$legalParameters=['cmFivePath','port','driver','hostname','username','password','database'];
		$requiredParameters=['cmFivePath','database'];
		$defaultParameters=['port'=>'','driver'=>'mysql','hostname'=>'localhost','username'=>'root','password'=>'','adminUsername'=>'admin','adminPassword'=>'admin','adminFirstName'=>'Admin','adminLastName'=>'User','adminEmail'=>'admin@here.com'];
		$config=$defaultParameters;
		
		// set all legal config from environment
		foreach($legalParameters as $pKey =>$parameterName) {
			if (!empty(getenv($parameterName))) {
				$config[$parameterName]=getenv($parameterName);
			}
		}
		// set any legal config from argument
		foreach($arguments as $aKey =>$argument) {
			$argumentParts=explode(':',$argument);
			if (in_array(trim($argumentParts[0]),$legalParameters)) {
				$config[$argumentParts[0]]=implode(':',array_slice($argumentParts,1));
			}
		}
		// check all required arguents are present
		foreach ($requiredParameters as $rk => $parameterName) {
			if (empty($config[$parameterName])) {
				$errors[]='Missing required argument '.$parameterName;
			}
		}
		return array('errors'=>$errors,'config'=>$config);
	}
	
	public function installConfigFile($config) {
		$this->init($config);
		// write config file and clear cache
		// map or default testing parameters
		$installConf=array();
		$installConf['application_name']='cmFive Test App';
		$installConf['company_name']='2PI Software';
		$installConf['company_url']='http://2pisoftware.com';
		$installConf['timezone']='Australia/Sydney';
		$installConf['db_hostname']=$config['hostname'];
		$installConf['db_username']=$config['username'];
		$installConf['db_password']=$config['password'];
		$installConf['db_database']=$config['database'];
		$installConf['db_driver']=$config['driver'];
		$installConf['email_layer']='smtp';
		$installConf['email_host']='';
		$installConf['email_port']='';
		$installConf['email_auth']='';
		$installConf['email_username']='';
		$installConf['email_password']='';
		$installConf['checkCSRF']=true;
		$installConf['allow_from_ip']='';
		$installConf['rest_api_key']='abcdefghijklmnopqrstuv';
		InstallService::writeConfig($installConf);
	}
	
	
	public function install($config) {
		$output=[];
		$output[]='cmFive Installer';
		$output[]='Write config.php';
		$this->installConfigFile($config);
		// write testing modules to filesystem
		$gen=new CmFiveTestModuleGenerator($config['cmFivePath']);
		$gen->createTestTemplateFiles();
		// cleanup
		register_shutdown_function(
			function($webTest) {
				$this->removeTestTemplateFiles();
			}
		,$gen);
		
		$output[]='Install SQL';
		// save combined sql file for running between tests
		$sql=$this->getInstallSql($config);
		file_put_contents('cache'.DS.'install.sql',$sql);
		$output=array_merge($output,$this->runInstallSql($sql,$config));
		return $output;
	}
	
	public function getInstallSql($config) {
		$this->init($config);
		return InstallService::getInstallSql($this->pdo,$config);
	}
	
	// run db install scripts
	public function runInstallSql($sql,$config) {
		$output=[];
		$this->init($config);
		
		$installResult=InstallService::runSql($this->pdo,$sql);
		
		//$createAdminUserResult=InstallService::createAdminUser($this->pdo,$config['adminUsername'],$config['adminPassword'],$config['adminFirstName'],$config['adminLastName'],$config['adminEmail']) ;
		
		$output=array_merge($output,$installResult['errors']);
		$output=array_merge($output,$installResult['output']);
		//$output=array_merge($output,$createAdminUserResult['errors']);
		//$output=array_merge($output,$createAdminUserResult['output']);
		return $output;
	}

}	




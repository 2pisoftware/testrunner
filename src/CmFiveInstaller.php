<?php

use Ifsnop\Mysqldump as IMysqldump;

if (!defined('DS'))  define('DS', DIRECTORY_SEPARATOR);

$testRunnerPath=dirname(dirname(__FILE__));
require($testRunnerPath.DS.'src'.DS.'CmFiveTestModuleGenerator.php');
require_once($testRunnerPath.DS.'src'.DS.'FileSystemTools.php');
require_once($testRunnerPath.DS.'composer'.DS.'vendor'.DS.'autoload.php');
  
if (php_sapi_name() == 'cli') {
	// now we have all required parameters
	// check if we need to automatically run the installer on inclusion of this file
	if (count($argv)>1 && $argv[1]=="install") {
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
	var $w ;
	var $config;
	var $pdo;
	
	public function init($config) {
		$this->config = $config;
		if (!$this->initialised)  {
			$_SERVER['DOCUMENT_ROOT']=$config['cmFivePath'];
			chdir($config['cmFivePath']);
			require_once('system'.DS.'db.php');
			require_once('system'.DS.'web.php');
			require_once('system'.DS.'modules'.DS.'admin'.DS.'models'.DS.'MigrationService.php');
			require_once('system'.DS.'modules'.DS.'admin'.DS.'models'.DS.'Migration.php');
			require_once('system'.DS.'modules'.DS.'auth'.DS.'models'.DS.'User.php');
			require_once "system/composer/vendor/autoload.php";
			
			$pdo = new PDO($config['driver'].":host=".$config['hostname'], $config['username'], $config['password']);
			$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$dbname = "`".str_replace("`","``",$config['database'])."`";
			$pdo->query("DROP DATABASE IF EXISTS ".$dbname);
			$this->w = new Web();
			$database = array(
			    "hostname"  => $config['hostname'],
			    "username"  => $config['username'],
			    "password"  => $config['password'],
			    "database"  => $config['database'],
			    "driver"    => $config['driver']
			);
			try {
				$dbname=$config['database'];
				$pdo->query("CREATE DATABASE IF NOT EXISTS $dbname");
				
				$pdo->query("use $dbname");
				$this->pdo = new DbPDO($database);
			} catch (Exception $ex) {
	    		echo "Error: Can't connect to database.".$ex->getMessage();
	    		print_r($database);
	    		die();
	    	}
	    	$this->w->db = $this->pdo;
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
		$installConf['db_hostname']=array_key_exists('hostname',$config) ? $config['hostname'] : 'localhost';
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
		$this->writeConfig($installConf);
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
		register_shutdown_function([&$gen,'removeTestTemplateFiles']);
		$output[]='Created Test Modules';
		// chmod and chown $this->config['cmFivePath']
		// mkdir /storage/logs,backups,session
		if (!file_exists($this->config['cmFivePath'].DS."storage")) {
			mkdir($this->config['cmFivePath'].DS."storage");
		}
		if (!file_exists($this->config['cmFivePath'].DS."storage".DS."logs")) {
			mkdir($this->config['cmFivePath'].DS."storage".DS."logs");
		}
		if (!file_exists($this->config['cmFivePath'].DS."storage".DS."backups")) {
			mkdir($this->config['cmFivePath'].DS."storage".DS."backups");
		}
		if (!file_exists($this->config['cmFivePath'].DS."storage".DS."session")) {
			mkdir($this->config['cmFivePath'].DS."storage".DS."session");
		}
		chmod($this->config['cmFivePath'], 0755);
		chown($this->config['cmFivePath'], "www-data");
		$output[]='Created necessary folders and set file permissions';
		$output[]='Composer generate';
		$this->updateComposerJSON();
		$output[]='Composer update';
		$this->updateComposer();
		$output[]='Composer DONE';
		$output[]='Install SQL';
		// save combined sql file for running between tests
		$sql=$this->getInstallSql($config);
		$output=array_merge($output,$this->runInstallSql($sql,$config));
		$output[]='Installed SQL';
		return $output;
	}
	/**
	 * Run the composer update script to generate composer.json for modules
	 */
	public function updateComposerJSON() {
		require_once $this->config['cmFivePath'].'/system/modules/admin/actions/composer.php';
		composer_ALL($this->w);
	}
	
	/**
	 * Run the composer update
	 */
	public function updateComposer() {
		//chdir($this->config['cmFivePath'].'/system');
		exec('cd '.$this->config['cmFivePath'].'/system; php composer.phar update');

	}
	
	/*****************************************************
	 * Generate a single string with sql from system and module sources
	 * To ensure the sql is valid, a FULL DATABASE REFRESH is run in the process of generating
	 * the sql string so that each line can be run against the database before inclusion
	 ******************************************************/
	 public function getInstallSql($config) {
		$this->init($config);
		try {
			// Run migrations
			$this->w->Migration->installInitialMigration();
			$this->w->Migration->runMigrations("all");
		} catch (\Exception $e) {
		    echo 'migrations error: ' . $e->getMessage();
		}
		try {
			// create admin user
			$contact="INSERT INTO `contact` (`id`, `firstname`, `lastname`, `othername`, `title`, `homephone`, `workphone`, `mobile`, `priv_mobile`, `fax`, `email`, `notes`, `dt_created`, `dt_modified`, `is_deleted`, `private_to_user_id`, `creator_id`) VALUES
	(1, 'Administrator', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'admin@tripleacs.com', NULL, '2012-04-27 06:31:52', '0000-00-00 00:00:00', 0, NULL, NULL);";
			$user="INSERT INTO `user` (`id`, `login`, `password`, `password_salt`, `contact_id`, `is_admin`, `is_active`, `is_deleted`, `is_group`, `dt_created`, `dt_lastlogin`) VALUES
	(1, 'admin', 'ca1e51f19afbe6e0fb51dde5bcf01ab73e52c7cd', '9b618fbc7f9509fc28ebea98becfdd58', 1, 1, 1, 0, 0, '2012-04-27 06:31:07', '2012-04-27 17:23:54');";
			$role="INSERT INTO user_role (`id`, `user_id`, `role`) VALUES (NULL, 1, 'user');";
			$output[]=$contact;
			$output[]=$user;
			$output[]=$role;
			self::runSql($this->pdo,$contact);
			self::runSql($this->pdo,$user);
			self::runSql($this->pdo,$role);
		} catch (\Exception $e) {
		    echo 'mysql user patch error: ' . $e->getMessage();
		}
		try {
			// now dump database
			$dump = new IMysqldump\Mysqldump('mysql:host='.$config['hostname'].';dbname='.$config['database'], $config['username'], $config['password'],['add-drop-table'=>'true']);
			$dump->start('cache/install.sql');
			#$exec='mysql -u '.$config['username'].' -p'.$config['password'].' -h '.$config['hostname'].' --add-drop-table '.$config['database'].' > '.$config['cmFivePath'].'/cache/install.sql';
			//echo "CONNECTsss";
			#echo $exec;
			#exec($exec);
		} catch (\Exception $e) {
		    echo 'mysqldump-php error: ' . $e->getMessage();
		}
		return file_get_contents('cache/install.sql');
	}
	
	// run db install scripts
	public function runInstallSql($sql,$config) {
		$output=[];
		$this->init($config);
		
		$installResult=$this->runSql($this->pdo,$sql);
		
		//$createAdminUserResult=InstallService::createAdminUser($this->pdo,$config['adminUsername'],$config['adminPassword'],$config['adminFirstName'],$config['adminLastName'],$config['adminEmail']) ;
		
		$output=array_merge($output,$installResult['errors']);
		$output=array_merge($output,$installResult['output']);
		//$output=array_merge($output,$createAdminUserResult['errors']);
		//$output=array_merge($output,$createAdminUserResult['output']);
		return $output;
	}


	
	/*********************************************************
	 * Execute sql from a string
	 ********************************************************/
	public static function runSql($pdo,$sqlString) {
		$errors=[];
		$output=['Install SQL for cmfive and all modules'];
		if (! preg_match_all("/('(\\\\.|.)*?'|[^;])+/s", $sqlString, $m)) return;

		foreach ($m[0] as $sql) {
			if (strlen(trim($sql))) {
				try {
					$pdo->exec($sql);
				} catch (Exception $e) {
					$errors[]="Error from SQL install: " . $e->getMessage();
				}
			}	
		}
		return array('errors'=>$errors,'output'=>$output);
	
	}

/*********************************************************
	 * Write config.php from a template and variables
	 ********************************************************/
	public static function writeConfig($config) {
		// keep a copy of the original config file before generating
		if (file_exists('config.old.php')) copy('config.old.php','config.old.'.time().'.php');
		if (file_exists('config.php')) copy('config.php','config.old.php');
		$template_path = "config.install.tpl.php";
		ob_start();
		require($template_path);
		$result_config=ob_get_contents();
		ob_end_clean();
		// clear the config cache
		if (file_exists('cache'.DIRECTORY_SEPARATOR.'config.cache')) unlink('cache'.DIRECTORY_SEPARATOR.'config.cache');
		return file_put_contents("config.php", "<?php\n\n" .$result_config);
	}
	

}	



